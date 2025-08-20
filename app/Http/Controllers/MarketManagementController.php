<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HasAuditTrail;
use App\Services\StoredProcedureAuditService;
use App\Models\VmaeProductoIqvia;

class MarketManagementController extends Controller
{
    use HasAuditTrail;
    /**
     * Display all markets with management capabilities
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search'); // Parámetro de búsqueda

            // Query base para mercados (excluir mercados DENEGADOS)
            $marketsQuery = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    's.solicitud',
                    'e.estado'
                )
                ->where('s.solicitud', '!=', 'DENEGADO') // Excluir mercados denegados
                ->orderBy('m.fechaRegistro', 'asc');

            // Aplicar búsqueda global si se proporciona un término
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where(function($q) use ($searchTerm) {
                    $q->where('m.mercado', 'LIKE', $searchTerm)
                      ->orWhere('s.solicitud', 'LIKE', $searchTerm)
                      ->orWhere('e.estado', 'LIKE', $searchTerm)
                      ->orWhere('m.idMercado', 'LIKE', $searchTerm);
                });
            }

            // Obtener mercados paginados
            $markets = $marketsQuery->paginate(10);
            $markets->appends($request->all()); // Mantener parámetros en paginación

            // Obtener estadísticas (sin paginación para el total real, excluyendo denegados)
            $allMarkets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('s.solicitud', 'e.estado')
                ->where('s.solicitud', '!=', 'DENEGADO') // Excluir mercados denegados de las estadísticas
                ->get();

            $stats = [
                'total' => $allMarkets->count(),
                'aprobados' => $allMarkets->where('solicitud', 'APROBADO')->count(),
                'pendientes' => $allMarkets->where('solicitud', 'ESPERA')->count(),
                'activos' => $allMarkets->where('estado', 'ACTIVO')->count(),
                'inactivos' => $allMarkets->where('estado', 'INACTIVO')->count()
            ];

            // Log de consulta de mercados
            $this->auditView('TAB_MERCADO', 'Consulta de lista de mercados', [
                'total_mercados' => $stats['total'],
                'page' => $request->get('page', 1)
            ]);

            return view('market-management.index', compact('markets', 'stats'));

        } catch (\Exception $e) {
            // Log del error
            $this->auditError('VIEW', 'TAB_MERCADO', $e->getMessage(), 'Error al cargar lista de mercados');
            return back()->with('error', 'Error al cargar mercados: ' . $e->getMessage());
        }
    }

    /**
     * Create a new market using stored procedure ODS.SP_INSERT_MERCADO
     * Crea el mercado con solicitud "ESPERA" para aprobación del administrador
     */
    public function createMarket(Request $request)
    {
        $request->validate([
            'market_name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            
            $marketName = trim($request->market_name);
            
            // Verificar si ya existe un mercado con el mismo nombre
            $existingMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('mercado', $marketName)
                ->first();
                
            if ($existingMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un mercado con ese nombre'
                ], 422);
            }
            
            // Llamar al stored procedure para insertar el mercado
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_MERCADO ?', [$marketName]);
            
            DB::commit();
            
            // Calcular la página donde aparecerá el nuevo mercado (al final, excluyendo denegados)
            $totalMarkets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->where('s.solicitud', '!=', 'DENEGADO') // Excluir mercados denegados del conteo
                ->count() + 1; // +1 por el que acabamos de crear
            
            $itemsPerPage = 10;
            $lastPage = ceil($totalMarkets / $itemsPerPage);
            
            return response()->json([
                'success' => true,
                'message' => 'El mercado "' . $marketName . '" ha sido creado y enviado para aprobación del administrador',
                'market_name' => $marketName,
                'status' => 'ESPERA',
                'timestamp' => now()->format('H:i:s'),
                'redirect_to_page' => $lastPage
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update market name
     */
    public function updateMarket(Request $request)
    {
        $request->validate([
            'market_id' => 'required|integer',
            'market_name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            
            $marketName = trim($request->market_name);
            
            // Verificar que el mercado existe
            $existingMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();
                
            if (!$existingMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mercado no existe'
                ], 404);
            }
            
            // Verificar si ya existe otro mercado con el mismo nombre
            $duplicateMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('mercado', $marketName)
                ->where('idMercado', '!=', $request->market_id)
                ->first();
                
            if ($duplicateMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro mercado con ese nombre'
                ], 422);
            }
            
            // Actualizar el nombre del mercado
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update(['mercado' => $marketName]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'El mercado ha sido actualizado exitosamente',
                'market_name' => $marketName
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle market status (active/inactive)
     */
    public function toggleStatus(Request $request)
    {
        $request->validate([
            'market_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            // Obtener el mercado actual
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.*', 'e.estado')
                ->where('m.idMercado', $request->market_id)
                ->first();

            if (!$market) {
                throw new \Exception('Mercado no encontrado');
            }

            // Determinar el nuevo estado
            $newStatusName = $market->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
            
            // Obtener el ID del nuevo estado
            $newStatus = DB::connection('sqlsrv')
                ->table('ODS.TAB_ESTADO')
                ->where('estado', $newStatusName)
                ->first();

            if (!$newStatus) {
                throw new \Exception('Estado no encontrado');
            }

            // Actualizar el estado del mercado
            DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->update(['idEstado' => $newStatus->idEstado]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "El mercado '{$market->mercado}' ha sido cambiado a {$newStatusName}",
                'new_status' => $newStatusName
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda global AJAX para Market Management
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = 10;

            // Query base para búsqueda (excluir mercados DENEGADOS)
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    's.solicitud',
                    'e.estado'
                )
                ->where('s.solicitud', '!=', 'DENEGADO') // Excluir mercados denegados
                ->orderBy('m.fechaRegistro', 'asc');

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

    /**
     * Show products associated with a specific market
     */
    public function showProducts($marketId)
    {
        try {
            // Log de acceso
            Log::info('Acceso a productos del mercado', ['market_id' => $marketId]);
            
            // Validar que marketId sea un número válido
            if (!is_numeric($marketId) || $marketId <= 0) {
                Log::warning('ID de mercado inválido', ['market_id' => $marketId]);
                return redirect()->route('market-management.index')
                    ->with('error', 'ID de mercado inválido: ' . $marketId);
            }

            // Verificar que el mercado existe
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $marketId)
                ->first();

            if (!$market) {
                Log::warning('Mercado no encontrado', ['market_id' => $marketId]);
                return redirect()->route('market-management.index')
                    ->with('error', 'El mercado con ID ' . $marketId . ' no existe');
            }

            Log::info('Mercado encontrado', ['market' => $market]);
            return view('market-management.products', compact('market'));

        } catch (\Exception $e) {
            Log::error('Error al cargar productos del mercado', [
                'market_id' => $marketId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('market-management.index')
                ->with('error', 'Error al cargar productos del mercado: ' . $e->getMessage());
        }
    }

    /**
     * Get products for a specific market with cursor pagination
     */
    public function getMarketProducts(Request $request, $marketId)
    {
        try {
            // Validar que marketId sea un número
            if (!is_numeric($marketId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de mercado inválido'
                ], 400);
            }

            // Verificar que el mercado existe
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $marketId)
                ->first();

            if (!$market) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mercado no existe'
                ], 404);
            }

            // Tamaño de página (máximo 50, mínimo 5)
            $perPage = min(max((int) $request->get('per_page', 20), 5), 50);
            
            // Obtener cursor de la request
            $cursor = $request->get('cursor');

            // Query base con filtro por mercado - optimizado con índices
            $query = VmaeProductoIqvia::select([
                'codigoPresentacion',
                'descripcionPresentacion',
                'marcaGenerico',
                'eticoPopular',
                'molecula',
                'codigoFF3',
                'codigoATC4',
                'MERCADO'
            ])
            ->where('MERCADO', $market->mercado)
            ->orderBy('codigoPresentacion'); // Cambiado a un campo que probablemente tenga índice

            // NO calculamos el total count para evitar timeouts
            // El frontend manejará la paginación sin conocer el total exacto
            
            // Aplicar cursor pagination
            if ($cursor) {
                $productos = $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
            } else {
                $productos = $query->cursorPaginate($perPage);
            }

            $response = [
                'success' => true,
                'data' => $productos->items(),
                'market' => [
                    'id' => $market->idMercado,
                    'name' => $market->mercado
                ],
                'pagination' => [
                    'per_page'           => $productos->perPage(),
                    'next_cursor'        => $productos->nextCursor()?->encode(),
                    'prev_cursor'        => $productos->previousCursor()?->encode(),
                    'has_more_pages'     => $productos->hasMorePages(),
                    'has_previous_pages' => $productos->previousCursor() !== null,
                ],
                'loaded_count' => count($productos->items())
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos del mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get all available markets for dropdowns/selects
     */
    public function getMarketsApi(Request $request)
    {
        try {
            // Obtener todos los mercados con su estado y solicitud (excluir DENEGADOS)
            $markets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    's.solicitud',
                    'e.estado'
                )
                ->where('s.solicitud', '!=', 'DENEGADO') // Excluir mercados denegados
                ->orderBy('m.mercado', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $markets,
                'total' => $markets->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getMarketsApi: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    //solicitar cambiar el estado del producto
    public function changeStatusMarket (Request $request){
        $validated = $request->validate([
            'id' => 'required|exists:vmae_producto_iqvia,id',
            'estado' => 'required|in:ACTIVO,INACTIVO'
        ]);

        try {
            $product = VmaeProductoIqvia::findOrFail($validated['id']);
            $product->estado = $validated['estado'];
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Estado del producto actualizado exitosamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en changeStatusMarket: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from market using stored procedure
     */
    public function removeProduct(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string'
        ]);

        try {
            // Ejecutar el stored procedure para solicitar quitar producto (cambia estado a ESPERA)
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_SOLICITAR_QUITAR_PRODUCTO ?', [
                $validated['codigoPresentacion']
            ]);

            // Log de la operación para auditoría
            Log::info('Solicitud para quitar producto ejecutada', [
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'usuario' => Auth::user()->name ?? 'Usuario no identificado',
                'timestamp' => now(),
                'executed' => $executed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'El estado del producto ha sido cambiado a ESPERA exitosamente.',
                'codigoPresentacion' => $validated['codigoPresentacion']
            ]);

        } catch (\Exception $e) {
            Log::error('Error al solicitar quitar producto: ' . $e->getMessage(), [
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}
