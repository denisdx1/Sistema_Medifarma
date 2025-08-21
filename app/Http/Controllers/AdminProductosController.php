<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illum        } catch (\Exception $e) {
            // Error al cargar productos pendientes de aprobación
            
            return redirect()->back()->with('error', 'Error al cargar los productos: ' . $e->getMessage());port\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminProductosController extends Controller
{
    /**
     * Display productos pending approval (solicitud = 0)
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = $request->get('per_page', 15);

            // Query para productos pendientes de aprobación
            $query = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->join('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->join('ODS.TAB_ESTADO as e', 'c.idEstado', '=', 'e.idEstado')
                ->select([
                    'v.codigoPresentacion as codigoPresentacion',
                    'v.descripcionPresentacion as descripcionPresentacion',
                    'v.marcaGenerico as marcaGenerico',
                    'v.eticoPopular as eticoPopular',
                    'v.molecula as molecula',
                    'v.codigoFF3 as codigoFF3',
                    'v.codigoATC4 as codigoATC4',
                    'v.descripcionLaboratorio as laboratorio',
                    'm.MERCADO as mercadoActual',
                    'm.idMercado',
                    'e.estado as estadoGeneral',
                    'c.fechaRegistro',
                    'c.idConfiguracion'
                ])
                ->where('e.estado', 'ESPERA') // Solo productos en estado ESPERA
                ->orderBy('c.fechaRegistro', 'desc');

            // Aplicar búsqueda si se proporciona
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('v.codigoPresentacion', 'LIKE', $searchTerm)
                      ->orWhere('v.descripcionPresentacion', 'LIKE', $searchTerm)
                      ->orWhere('v.molecula', 'LIKE', $searchTerm)
                      ->orWhere('m.MERCADO', 'LIKE', $searchTerm)
                      ->orWhere('v.descripcionLaboratorio', 'LIKE', $searchTerm);
                });
            }

            $productos = $query->paginate($perPage);

            // Estadísticas
            $stats = [
                'total_pendientes' => DB::connection('sqlsrv')
                    ->table('ODS.TAB_CONFIGURACION as c')
                    ->join('ODS.TAB_SOLICITUD as s', 'c.idSolicitud', '=', 's.idSolicitud')
                    ->where('s.solicitud', 'ESPERA')
                    ->count(),
                'total_por_mercado' => DB::connection('sqlsrv')
                    ->table('ODS.TAB_CONFIGURACION as c')
                    ->join('ODS.TAB_SOLICITUD as s', 'c.idSolicitud', '=', 's.idSolicitud')
                    ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                    ->where('s.solicitud', 'ESPERA')
                    ->groupBy('m.mercado')
                    ->selectRaw('m.mercado, COUNT(*) as total')
                    ->get()
            ];

            return view('admin-productos.index', compact('productos', 'stats', 'search'));

        } catch (\Exception $e) {
            // Error al cargar productos pendientes de aprobación
            
            return redirect()->back()->with('error', 'Error al cargar los productos: ' . $e->getMessage());
        }
    }

    /**
     * Approve product removal - moves to RESTO market
     */
    public function aprobar(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string',
            'idConfiguracion' => 'required|integer'
        ]);

        try {
            DB::connection('sqlsrv')->beginTransaction();

            // Obtener información actual del producto ANTES de cualquier cambio
            $configuracionAntes = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION as c')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->join('ODS.TAB_SOLICITUD as s', 'c.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'c.idEstado', '=', 'e.idEstado')
                ->where('c.idConfiguracion', $validated['idConfiguracion'])
                ->select([
                    'm.MERCADO as mercadoAnterior',
                    's.solicitud as solicitudAnterior', 
                    'e.estado as estadoAnterior',
                    'c.*'
                ])
                ->first();

            if (!$configuracionAntes) {
                throw new \Exception('No se encontró la configuración del producto');
            }

            // 1. Ejecutar SP para cambio de estado solamente
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_APROBAR_QUITAR_PRODUCTO ?', [
                $validated['codigoPresentacion']
            ]);

            if (!$executed) {
                throw new \Exception('Error al ejecutar el stored procedure');
            }

            // 2. Buscar o crear mercado RESTO
            $restoMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('MERCADO', 'RESTO')
                ->first();

            if (!$restoMarket) {
                // Obtener IDs por defecto para crear el mercado RESTO
                $defaultSolicitud = DB::connection('sqlsrv')
                    ->table('ODS.TAB_SOLICITUD')
                    ->where('solicitud', 'APROBADO')
                    ->first();
                
                $defaultEstado = DB::connection('sqlsrv')
                    ->table('ODS.TAB_ESTADO')
                    ->where('estado', 'ACTIVO')
                    ->first();

                // Crear mercado RESTO
                $restoMarketId = DB::connection('sqlsrv')
                    ->table('ODS.TAB_MERCADO')
                    ->insertGetId([
                        'MERCADO' => 'RESTO',
                        'fechaRegistro' => now(),
                        'idSolicitud' => $defaultSolicitud->idSolicitud ?? 1,
                        'idEstado' => $defaultEstado->idEstado ?? 1
                    ]);

                // Mercado RESTO creado con ID: $restoMarketId
            } else {
                $restoMarketId = $restoMarket->idMercado;
            }

            // 3. Cambiar manualmente el mercado a RESTO
            $updated = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION')
                ->where('idConfiguracion', $validated['idConfiguracion'])
                ->update([
                    'idMercado' => $restoMarketId,
                    'fechaRegistro' => now()
                ]);

            if (!$updated) {
                throw new \Exception('Error al actualizar el mercado a RESTO');
            }

            // 4. Verificar el resultado final
            $configuracionDespues = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION as c')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->join('ODS.TAB_SOLICITUD as s', 'c.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'c.idEstado', '=', 'e.idEstado')
                ->where('c.idConfiguracion', $validated['idConfiguracion'])
                ->select([
                    'm.MERCADO as mercadoNuevo',
                    's.solicitud as solicitudNueva',
                    'e.estado as estadoNuevo',
                    'c.*'
                ])
                ->first();

            DB::connection('sqlsrv')->commit();

            DB::connection('sqlsrv')->commit();

            // Producto aprobado exitosamente
            return response()->json([
                'success' => true,
                'message' => 'Producto aprobado exitosamente. Estado actualizado por SP y mercado cambiado a RESTO.',
                'detalles' => [
                    'mercadoAnterior' => $configuracionAntes->mercadoAnterior,
                    'mercadoNuevo' => 'RESTO',
                    'solicitudAnterior' => $configuracionAntes->solicitudAnterior,
                    'solicitudNueva' => $configuracionDespues->solicitudNueva ?? 'Error al obtener',
                    'estadoAnterior' => $configuracionAntes->estadoAnterior,
                    'estadoNuevo' => $configuracionDespues->estadoNuevo ?? 'Error al obtener'
                ]
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            
            // Error al aprobar remoción de producto

        } catch (\Exception $e) {
            DB::connection('sqlsrv')->rollBack();
            
            // Error al aprobar remoción de producto
            
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deny product removal - returns to approved status
     */
    public function denegar(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string',
            'idConfiguracion' => 'required|integer',
            'motivo' => 'nullable|string|max:500'
        ]);

        try {
            // Obtener ID de solicitud APROBADO
            $solicitudAprobado = DB::connection('sqlsrv')
                ->table('ODS.TAB_SOLICITUD')
                ->where('solicitud', 'APROBADO')
                ->first();

            if (!$solicitudAprobado) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el estado APROBADO en el sistema'
                ], 400);
            }

            // Actualizar configuración para denegar (volver a APROBADO)
            $updated = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION')
                ->where('idConfiguracion', $validated['idConfiguracion'])
                ->update([
                    'idSolicitud' => $solicitudAprobado->idSolicitud,
                    'fechaRegistro' => now()
                ]);

            // Solicitud denegada exitosamente
            return response()->json([
                'success' => true,
                'message' => 'Solicitud denegada. El producto permanece en su mercado actual.',
                'motivo' => $validated['motivo'] ?? null
            ]);

        } catch (\Exception $e) {
            // Error al denegar remoción de producto
            
            return response()->json([
                'success' => false,
                'message' => 'Error al denegar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get productos data for API
     */
    public function getProductosApi(Request $request)
    {
        try {
            // Parámetro para solo obtener el conteo
            if ($request->has('count_only') && $request->boolean('count_only')) {
                $total = DB::connection('sqlsrv')
                    ->table('ODS.TAB_CONFIGURACION as c')
                    ->join('ODS.TAB_SOLICITUD as s', 'c.idSolicitud', '=', 's.idSolicitud')
                    ->where('s.solicitud', 'ESPERA')
                    ->count();

                return response()->json([
                    'success' => true,
                    'total' => $total
                ]);
            }

            $search = $request->get('search');
            $perPage = $request->get('per_page', 15);

            $query = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->join('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->join('ODS.TAB_SOLICITUD as s', 'c.idSolicitud', '=', 's.idSolicitud')
                ->select([
                    'v.codigoPresentacion as codigoPresentacion',
                    'v.descripcionPresentacion as descripcionPresentacion',
                    'v.marcaGenerico as marcaGenerico',
                    'v.eticoPopular as eticoPopular',
                    'v.molecula as molecula',
                    'v.descripcionLaboratorio as laboratorio',
                    'm.MERCADO as mercadoActual',
                    'c.fechaRegistro as fechaSolicitud',
                    'c.idConfiguracion'
                ])
                ->where('s.solicitud', 'ESPERA')
                ->orderBy('c.fechaRegistro', 'desc');

            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('v.codigoPresentacion', 'LIKE', $searchTerm)
                      ->orWhere('v.descripcionPresentacion', 'LIKE', $searchTerm)
                      ->orWhere('v.descripcionLaboratorio', 'LIKE', $searchTerm)
                      ->orWhere('v.molecula', 'LIKE', $searchTerm)
                      ->orWhere('m.MERCADO', 'LIKE', $searchTerm);
                });
            }

            $productos = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $productos->items(),
                'pagination' => [
                    'current_page' => $productos->currentPage(),
                    'last_page' => $productos->lastPage(),
                    'per_page' => $productos->perPage(),
                    'total' => $productos->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }
}
