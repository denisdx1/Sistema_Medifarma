<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class MarketAdministrationController extends Controller
{
    /**
     * Display markets for administration with filters
     */
    public function index(Request $request)
    {
        // TEMPORAL: Verificación de autenticación comentada
        // Verificar que el usuario sea administrador
        if (!Auth::user()->isAdmin()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        try {
            $filter = $request->get('filter', 'pending'); // Filtro por defecto: pendientes
            $search = $request->get('search'); // Parámetro de búsqueda
            
            $perPage = 10; // Número de items por página

            // Query base
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.*', 's.solicitud', 'e.estado')
                ->orderBy('m.fechaRegistro', 'desc');
            
            // Aplicar búsqueda global si se proporciona un término
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('m.mercado', 'LIKE', $searchTerm)
                      ->orWhere('s.solicitud', 'LIKE', $searchTerm)
                      ->orWhere('e.estado', 'LIKE', $searchTerm)
                      ->orWhere('m.idMercado', 'LIKE', $searchTerm);
                });
            }
            
            // Aplicar filtros según el tipo seleccionado (solo si no hay búsqueda activa)
            if (empty($search)) {
                switch ($filter) {
                    case 'approved':
                        $query->where('s.solicitud', 'APROBADO');
                        break;
                    case 'denied':
                        $query->where('s.solicitud', 'DENEGADO');
                        break;
                    case 'all':
                        // No aplicar filtro, mostrar todos
                        break;
                    case 'pending':
                    default:
                        $query->where('s.solicitud', 'ESPERA');
                        break;
                }
            }

            // Obtener el total de registros para la paginación manual
            $total = $query->count();
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            // Aplicar paginación
            $marketsData = $query->offset($offset)->limit($perPage)->get();

            // Crear un objeto de paginación manual
            $markets = new LengthAwarePaginator(
                $marketsData,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
            
            // Agregar parámetros de consulta al paginador
            $markets->appends($request->all());

            // Obtener todos los estados disponibles
            $estados = DB::connection('sqlsrv')
                ->table('ODS.TAB_ESTADO')
                ->get();

            // Obtener todas las solicitudes disponibles
            $solicitudes = DB::connection('sqlsrv')
                ->table('ODS.TAB_SOLICITUD')
                ->get();

            // Contar mercados por estado para el dashboard
            $counts = [
                'pending' => DB::connection('sqlsrv')->table('ODS.TAB_MERCADO as m')
                    ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                    ->where('s.solicitud', 'ESPERA')->count(),
                'approved' => DB::connection('sqlsrv')->table('ODS.TAB_MERCADO as m')
                    ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                    ->where('s.solicitud', 'APROBADO')->count(),
                'denied' => DB::connection('sqlsrv')->table('ODS.TAB_MERCADO as m')
                    ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                    ->where('s.solicitud', 'DENEGADO')->count(),
            ];

            return view('market-administration.index', compact('markets', 'estados', 'solicitudes', 'filter', 'counts'));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar mercados: ' . $e->getMessage());
        }
    }

    /**
     * Approve a market
     */
    public function approve(Request $request)
    {
        // TEMPORAL: Verificación de autenticación comentada
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para aprobar mercados'
            ], 403);
        }

        $request->validate([
            'market_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            // Obtener el ID de solicitud para "APROBADO"
            $aprobadoSolicitud = DB::connection('sqlsrv')
                ->table('ODS.TAB_SOLICITUD')
                ->where('solicitud', 'APROBADO')
                ->first();

            if (!$aprobadoSolicitud) {
                throw new \Exception('No se encontró el estado APROBADO en la tabla de solicitudes');
            }

            // Obtener el mercado antes de actualizarlo
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();

            // Actualizar el mercado para cambiar idSolicitud de ESPERA a APROBADO
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update([
                    'idSolicitud' => $aprobadoSolicitud->idSolicitud
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "El mercado '{$market->mercado}' ha sido aprobado exitosamente",
                'market_name' => $market->mercado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deny a market using stored procedure
     */
    public function deny(Request $request)
    {
        // TEMPORAL: Verificación de autenticación comentada
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para denegar mercados'
            ], 403);
        }

        $request->validate([
            'market_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            // Obtener el mercado antes de denegarlo
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();

            if (!$market) {
                throw new \Exception('El mercado especificado no existe');
            }

            // Obtener el ID de solicitud para "DENEGADO"
            $denegadoSolicitud = DB::connection('sqlsrv')
                ->table('ODS.TAB_SOLICITUD')
                ->where('solicitud', 'DENEGADO')
                ->first();

            if (!$denegadoSolicitud) {
                throw new \Exception('No se encontró el estado DENEGADO en la tabla de solicitudes');
            }

            // Actualizar el mercado para cambiar idSolicitud a DENEGADO
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update([
                    'idSolicitud' => $denegadoSolicitud->idSolicitud
                ]);

            // Opcional: También ejecutar el stored procedure si existe y hace otras operaciones
            try {
                DB::connection('sqlsrv')->statement('EXEC ODS.SP_DENEGAR_MERCADO ?', [
                    $request->market_id
                ]);
            } catch (\Exception $spError) {
                // Si el SP falla, continuamos porque ya actualizamos manualmente
                \Log::warning('Stored procedure SP_DENEGAR_MERCADO falló: ' . $spError->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "El mercado '{$market->mercado}' ha sido denegado exitosamente",
                'market_name' => $market->mercado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al denegar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending markets count for dashboard
     */
    public function getPendingCount()
    {
        try {
            $count = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->where('s.solicitud', 'ESPERA')
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conteo de mercados pendientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change market status (active/inactive)
     */
    public function changeStatus(Request $request)
    {
        // TEMPORAL: Verificación de autenticación comentada
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cambiar el estado de mercados'
            ], 403);
        }

        $request->validate([
            'market_id' => 'required|integer',
            'status_id' => 'required|integer|exists:sqlsrv.ODS.TAB_ESTADO,idEstado'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar el estado del mercado
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update([
                    'idEstado' => $request->status_id,
                    'fechaUpdate' => now(),
                    'idUsuario' => Auth::id()
                ]);

            // Obtener el nombre del mercado y el nuevo estado
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();

            $estado = DB::connection('sqlsrv')
                ->table('ODS.TAB_ESTADO')
                ->where('idEstado', $request->status_id)
                ->first();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "El estado del mercado '{$market->mercado}' ha sido cambiado a '{$estado->estado}'",
                'market_name' => $market->mercado,
                'new_status' => $estado->estado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda global AJAX para Market Administration
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('search');
            $filter = $request->get('filter', 'all');
            $perPage = 10;

            // Query base para búsqueda
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.*', 's.solicitud', 'e.estado')
                ->orderBy('m.fechaRegistro', 'desc');

            // Aplicar búsqueda global
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('m.mercado', 'LIKE', $searchTerm)
                      ->orWhere('s.solicitud', 'LIKE', $searchTerm)
                      ->orWhere('e.estado', 'LIKE', $searchTerm)
                      ->orWhere('m.idMercado', 'LIKE', $searchTerm);
                });
            }

            // Aplicar filtro si se especifica
            if ($filter !== 'all' && !empty($search)) {
                switch ($filter) {
                    case 'approved':
                        $query->where('s.solicitud', 'APROBADO');
                        break;
                    case 'denied':
                        $query->where('s.solicitud', 'DENEGADO');
                        break;
                    case 'pending':
                        $query->where('s.solicitud', 'ESPERA');
                        break;
                }
            }

            // Obtener resultados paginados
            $total = $query->count();
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $results = $query->offset($offset)->limit($perPage)->get();

            return response()->json([
                'success' => true,
                'data' => $results,
                'total' => $total,
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }
}
