<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\VmaeProductoIqvia;

class MarketManagementController extends Controller
{
    /**
     * Display all markets with management capabilities
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search'); // Parámetro de búsqueda

            // Query base para mercados activos
            $marketsQuery = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                )
                ->where('e.estado', '!=', 'INACTIVO') // Excluir mercados inactivos
                ->orderBy('m.fechaRegistro', 'asc');

            // Aplicar búsqueda global si se proporciona un término
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where(function($q) use ($searchTerm) {
                    $q->where('m.mercado', 'LIKE', $searchTerm)
                      ->orWhere('e.estado', 'LIKE', $searchTerm)
                      ->orWhere('e.estado', 'LIKE', $searchTerm)
                      ->orWhere('m.idMercado', 'LIKE', $searchTerm);
                });
            }

            // Obtener mercados paginados
            $markets = $marketsQuery->paginate(10);
            $markets->appends($request->all()); // Mantener parámetros en paginación

            // Obtener estadísticas (sin paginación para el total real)
            $allMarkets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('e.estado')
                ->where('e.estado', '!=', 'INACTIVO') // Excluir mercados inactivos de las estadísticas
                ->get();

            $stats = [
                'total' => $allMarkets->count(),
                'activos' => $allMarkets->where('estado', 'ACTIVO')->count(),
                'inactivos' => $allMarkets->where('estado', 'INACTIVO')->count()
            ];

            // Consulta de mercados ejecutada correctamente
            return view('market-management.index', [
                'markets' => $markets,
                'search' => $search
            ]);

        } catch (\Exception $e) {
            // Error al cargar lista de mercados
            return back()->with('error', 'Error al cargar mercados: ' . $e->getMessage());
        }
    }

    /**
     * Create a new market using stored procedure ODS.SP_INSERT_MERCADO
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
            
            // Calcular la página donde aparecerá el nuevo mercado
            $totalMarkets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->where('e.estado', '!=', 'INACTIVO') // Excluir mercados inactivos del conteo
                ->count() + 1; // +1 por el que acabamos de crear
            
            $itemsPerPage = 10;
            $lastPage = ceil($totalMarkets / $itemsPerPage);
            
            return response()->json([
                'success' => true,
                'message' => 'El mercado "' . $marketName . '" ha sido creado exitosamente',
                'market_name' => $marketName,
                'status' => 'ACTIVO',
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

            // Query base para búsqueda
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                )
                ->where('e.estado', '!=', 'INACTIVO') // Excluir mercados inactivos
                ->orderBy('m.fechaRegistro', 'asc');

            // Aplicar búsqueda global
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('m.mercado', 'LIKE', $searchTerm)
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
            // Acceso a productos del mercado
            
            // Validar que marketId sea un número válido
            if (!is_numeric($marketId) || $marketId <= 0) {
                // ID de mercado inválido
                return redirect()->route('market-management.index')
                    ->with('error', 'ID de mercado inválido: ' . $marketId);
            }

            // Verificar que el mercado existe
            $market = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $marketId)
                ->first();

            if (!$market) {
                // Mercado no encontrado
                return redirect()->route('market-management.index')
                    ->with('error', 'El mercado con ID ' . $marketId . ' no existe');
            }

            // Mercado encontrado correctamente
            return view('market-management.products', compact('market'));

        } catch (\Exception $e) {
            // Error al cargar productos del mercado
            return redirect()->route('market-management.index')
                ->with('error', 'Error al cargar productos del mercado: ' . $e->getMessage());
        }
    }

    /**
     * Get products for a specific market with cursor pagination - OPTIMIZED
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

            // OPTIMIZACIÓN: Obtener parámetro de búsqueda
            $search = $request->get('search', '');
            $search = trim($search);

            // Construir query base con filtros de búsqueda
            $baseQueryForCount = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->where('v.MERCADO', $market->mercado);

            // Aplicar filtros de búsqueda si existe
            if (!empty($search)) {
                $baseQueryForCount->where(function($query) use ($search) {
                    $query->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('v.marcaGenerico', 'LIKE', "%{$search}%")
                          ->orWhere('v.molecula', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionFF3', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionATC4', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionLaboratorio', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionCorporacion', 'LIKE', "%{$search}%");
                });
            }

            $totalProducts = $baseQueryForCount->count();

            // Si el mercado tiene 50 productos o menos (después de filtro), cargar todos de una vez
            if ($totalProducts <= 50) {
                $query = DB::connection('sqlsrv')
                    ->table('dbo.VMAE_PROD_IQVIA as v')
                    ->leftJoin('ODS.TAB_CONFIGURACION as c', function($join) use ($market) {
                        $join->on('v.codigoPresentacion', '=', 'c.codigo')
                             ->where('c.idMercado', '=', $market->idMercado);
                    })
                    ->select([
                        'v.codigoPresentacion',
                        'v.descripcionPresentacion', 
                        'v.marcaGenerico',
                        'v.eticoPopular',
                        'v.molecula',
                        'v.descripcionFF3',
                        'v.descripcionATC4',
                        'v.descripcionLaboratorio',
                        'v.descripcionCorporacion',
                        'v.MERCADO',
                        'c.fuente'
                    ])
                    ->where('v.MERCADO', $market->mercado);

                // Aplicar filtros de búsqueda
                if (!empty($search)) {
                    $query->where(function($subQuery) use ($search) {
                        $subQuery->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
                                 ->orWhere('v.descripcionPresentacion', 'LIKE', "%{$search}%")
                                 ->orWhere('v.marcaGenerico', 'LIKE', "%{$search}%")
                                 ->orWhere('v.molecula', 'LIKE', "%{$search}%")
                                 ->orWhere('v.descripcionFF3', 'LIKE', "%{$search}%")
                                 ->orWhere('v.descripcionATC4', 'LIKE', "%{$search}%")
                                 ->orWhere('v.descripcionLaboratorio', 'LIKE', "%{$search}%")
                                 ->orWhere('v.descripcionCorporacion', 'LIKE', "%{$search}%");
                    });
                }

                $productos = $query->orderBy('v.codigoPresentacion')->get();

                return response()->json([
                    'success' => true,
                    'data' => $productos->toArray(),
                    'market' => [
                        'id' => $market->idMercado,
                        'name' => $market->mercado
                    ],
                    'pagination' => [
                        'per_page' => $totalProducts,
                        'next_cursor' => null,
                        'prev_cursor' => null,
                        'has_more_pages' => false,
                        'has_previous_pages' => false,
                    ],
                    'loaded_count' => $productos->count(),
                    'search' => [
                        'query' => $search,
                        'has_search' => !empty($search),
                        'results_count' => $productos->count()
                    ],
                    'query_info' => [
                        'optimization' => 'small_market_full_load',
                        'total_products' => $totalProducts,
                        'using_view' => 'dbo.VMAE_PROD_IQVIA',
                        'joined_with' => 'ODS.TAB_CONFIGURACION',
                        'market_filter' => $market->mercado,
                        'market_id' => $market->idMercado,
                        'includes_fuente' => true
                    ]
                ]);
            }

            // Para mercados grandes, usar paginación cursor
            $perPage = min(max((int) $request->get('per_page', 25), 20), 100);
            $cursor = $request->get('cursor');

            // Query OPTIMIZADA con JOIN a TAB_CONFIGURACION para obtener la fuente
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', function($join) use ($market) {
                    $join->on('v.codigoPresentacion', '=', 'c.codigo')
                         ->where('c.idMercado', '=', $market->idMercado);
                })
                ->select([
                    'v.codigoPresentacion',
                    'v.descripcionPresentacion', 
                    'v.marcaGenerico',
                    'v.eticoPopular',
                    'v.molecula',
                    'v.descripcionFF3',
                    'v.descripcionATC4',
                    'v.descripcionLaboratorio',
                    'v.descripcionCorporacion',
                    'v.MERCADO',
                    'c.fuente'
                ])
                ->where('v.MERCADO', $market->mercado);

            // Aplicar filtros de búsqueda también para mercados grandes
            if (!empty($search)) {
                $baseQuery->where(function($subQuery) use ($search) {
                    $subQuery->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
                             ->orWhere('v.descripcionPresentacion', 'LIKE', "%{$search}%")
                             ->orWhere('v.marcaGenerico', 'LIKE', "%{$search}%")
                             ->orWhere('v.molecula', 'LIKE', "%{$search}%")
                             ->orWhere('v.descripcionFF3', 'LIKE', "%{$search}%")
                             ->orWhere('v.descripcionATC4', 'LIKE', "%{$search}%")
                             ->orWhere('v.descripcionLaboratorio', 'LIKE', "%{$search}%")
                             ->orWhere('v.descripcionCorporacion', 'LIKE', "%{$search}%");
                });
            }

            $baseQuery->orderBy('v.codigoPresentacion'); // Ordenado por campo con posible índice

            // Aplicar cursor pagination manualmente para mejor control
            if ($cursor) {
                // Decodificar cursor
                try {
                    $decodedCursor = base64_decode($cursor);
                    $cursorData = json_decode($decodedCursor, true);
                    
                    if ($cursorData && isset($cursorData['codigoPresentacion'])) {
                        $baseQuery->where('codigoPresentacion', '>', $cursorData['codigoPresentacion']);
                    }
                } catch (\Exception $e) {
                    // Si el cursor es inválido, ignorar y comenzar desde el principio
                }
            }

            // Obtener productos con límite +1 para verificar si hay más páginas
            $productos = $baseQuery->limit($perPage + 1)->get();
            
            // Verificar si hay más páginas
            $hasMorePages = $productos->count() > $perPage;
            if ($hasMorePages) {
                $productos = $productos->take($perPage); // Remover el elemento extra
            }

            // Generar cursor para la siguiente página
            $nextCursor = null;
            if ($hasMorePages && $productos->isNotEmpty()) {
                $lastItem = $productos->last();
                $nextCursor = base64_encode(json_encode([
                    'codigoPresentacion' => $lastItem->codigoPresentacion
                ]));
            }

            // Generar cursor para la página anterior (simplificado para performance)
            $prevCursor = null;
            $hasPreviousPages = false;
            
            if ($cursor && $productos->isNotEmpty()) {
                $firstItem = $productos->first();
                
                // Verificar si hay elementos anteriores con una consulta rápida
                $hasPreviousPages = DB::connection('sqlsrv')
                    ->table('dbo.VMAE_PROD_IQVIA as v')
                    ->leftJoin('ODS.TAB_CONFIGURACION as c', function($join) use ($market) {
                        $join->on('v.codigoPresentacion', '=', 'c.codigo')
                             ->where('c.idMercado', '=', $market->idMercado);
                    })
                    ->where('v.MERCADO', $market->mercado)
                    ->where('v.codigoPresentacion', '<', $firstItem->codigoPresentacion)
                    ->exists();
                    
                if ($hasPreviousPages) {
                    // Para simplificar, usar un cursor genérico para página anterior
                    $prevCursor = base64_encode(json_encode([
                        'codigoPresentacion' => $firstItem->codigoPresentacion,
                        'direction' => 'prev'
                    ]));
                }
            }

            $response = [
                'success' => true,
                'data' => $productos->toArray(),
                'market' => [
                    'id' => $market->idMercado,
                    'name' => $market->mercado
                ],
                'pagination' => [
                    'per_page' => $perPage,
                    'next_cursor' => $nextCursor,
                    'prev_cursor' => $prevCursor,
                    'has_more_pages' => $hasMorePages,
                    'has_previous_pages' => $hasPreviousPages,
                ],
                'loaded_count' => $productos->count(),
                'search' => [
                    'query' => $search,
                    'has_search' => !empty($search),
                    'results_count' => $productos->count()
                ],
                'query_info' => [
                    'using_view' => 'dbo.VMAE_PROD_IQVIA',
                    'joined_with' => 'ODS.TAB_CONFIGURACION',
                    'market_filter' => $market->mercado,
                    'market_id' => $market->idMercado,
                    'cursor_applied' => $cursor !== null,
                    'includes_fuente' => true
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos del mercado: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    /**
     * API endpoint to get all available markets for dropdowns/selects
     */
    public function getMarketsApi(Request $request)
    {
        try {
            // Obtener todos los mercados activos con su estado
            $markets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                )
                ->where('e.estado', '!=', 'INACTIVO') // Excluir mercados inactivos
                ->orderBy('m.mercado', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $markets,
                'total' => $markets->count()
            ]);

        } catch (\Exception $e) {
            // Error en getMarketsApi
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
            // Error en changeStatusMarket
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change product market using stored procedure
     */
    public function changeProductMarket(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string',
            'nuevoMercadoId' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            
            // Buscar la configuración actual del producto para obtener la fuente
            $configuracion = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION')
                ->where('codigo', $validated['codigoPresentacion'])
                ->first();
                
            if (!$configuracion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la configuración del producto con código: ' . $validated['codigoPresentacion']
                ], 404);
            }
            
            // Verificar que el mercado destino existe y está activo
            $mercadoDestino = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.idMercado', 'm.mercado', 'e.estado')
                ->where('m.idMercado', $validated['nuevoMercadoId'])
                ->where('e.estado', 'ACTIVO')
                ->first();
                
            if (!$mercadoDestino) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mercado destino no existe o no está activo'
                ], 422);
            }
            
            // Verificar que no es el mismo mercado
            if ($configuracion->idMercado == $validated['nuevoMercadoId']) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto ya pertenece al mercado seleccionado'
                ], 422);
            }
            
            // Ejecutar el stored procedure ODS.SP_UPDATE_CONFIGURACION
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_CONFIGURACION ?, ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $configuracion->fuente,           // @fuente (obtenida de la configuración actual)
                $validated['nuevoMercadoId']      // @idMercado
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Producto cambiado exitosamente al mercado: {$mercadoDestino->mercado}",
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'mercadoAnterior' => $configuracion->idMercado,
                'mercadoNuevo' => $validated['nuevoMercadoId'],
                'nombreMercadoNuevo' => $mercadoDestino->mercado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el mercado del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from market using stored procedure SP_ASIGNAR_RESTO
     */
    public function removeProduct(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string'
        ]);

        try {
            DB::beginTransaction();
            
            // Verificar que el producto existe en la configuración actual
            $productoExiste = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION')
                ->where('codigo', $validated['codigoPresentacion'])
                ->exists();
                
            if (!$productoExiste) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no se encuentra en la configuración actual'
                ], 404);
            }

            // Ejecutar el stored procedure SP_ASIGNAR_RESTO - solo necesita el código del producto
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_ASIGNAR_RESTO ?', [
                $validated['codigoPresentacion']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto removido del mercado exitosamente',
                'codigoPresentacion' => $validated['codigoPresentacion']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al remover el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products from RESTO market for assignment
     */
    public function getRestoProducts(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $search = trim($search);
            $cursor = $request->get('cursor');
            $perPage = min(max((int) $request->get('per_page', 50), 20), 100);

            // Query base para productos en RESTO (mercado = 'RESTO')
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->select([
                    'v.codigoPresentacion',
                    'v.descripcionPresentacion', 
                    'v.marcaGenerico',
                    'v.eticoPopular',
                    'v.molecula as descripcionMolecula',
                    'v.descripcionFF3',
                    'v.descripcionATC4',
                    'v.descripcionLaboratorio',
                    'v.descripcionCorporacion',
                    'v.MERCADO',
                    DB::raw("COALESCE(c.fuente, 'IQV') as fuente") // Usar 'IQV' como fuente por defecto
                ])
                ->where('v.MERCADO', 'RESTO');

            // Aplicar filtros de búsqueda si existe
            if (!empty($search)) {
                $baseQuery->where(function($query) use ($search) {
                    $query->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('v.marcaGenerico', 'LIKE', "%{$search}%")
                          ->orWhere('v.molecula', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionFF3', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionATC4', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionLaboratorio', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionCorporacion', 'LIKE', "%{$search}%");
                });
            }

            // Contar total
            $totalProducts = $baseQuery->count();

            $baseQuery->orderBy('v.codigoPresentacion');

            // Aplicar cursor pagination si existe
            if ($cursor) {
                try {
                    $decodedCursor = base64_decode($cursor);
                    $cursorData = json_decode($decodedCursor, true);
                    
                    if ($cursorData && isset($cursorData['codigoPresentacion'])) {
                        $baseQuery->where('v.codigoPresentacion', '>', $cursorData['codigoPresentacion']);
                    }
                } catch (\Exception $e) {
                    // Si el cursor es inválido, ignorar
                }
            }

            // Obtener productos con límite +1 para verificar si hay más páginas
            $productos = $baseQuery->limit($perPage + 1)->get();
            
            // Verificar si hay más páginas
            $hasMorePages = $productos->count() > $perPage;
            if ($hasMorePages) {
                $productos = $productos->take($perPage);
            }

            // Generar cursor para la siguiente página
            $nextCursor = null;
            if ($hasMorePages && $productos->isNotEmpty()) {
                $lastItem = $productos->last();
                $nextCursor = base64_encode(json_encode([
                    'codigoPresentacion' => $lastItem->codigoPresentacion
                ]));
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $productos->toArray(),
                    'total' => $totalProducts,
                    'next_cursor' => $nextCursor,
                    'has_more_pages' => $hasMorePages
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos RESTO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign multiple products to a market using stored procedure ODS.SP_INSERT_CONFIGURACION
     */
    public function assignProducts(Request $request)
    {
        $validated = $request->validate([
            'idMercado' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.code' => 'required|string',
            'products.*.fuente' => 'required|string|min:1' // Asegurar que fuente no esté vacía
        ]);

        try {
            DB::beginTransaction();
            
            // Verificar que el mercado existe y está activo
            $mercado = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.idMercado', 'm.mercado', 'e.estado')
                ->where('m.idMercado', $validated['idMercado'])
                ->where('e.estado', 'ACTIVO')
                ->first();
                
            if (!$mercado) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mercado no existe o no está activo'
                ], 422);
            }

            $assignedCount = 0;
            $errors = [];

            // Procesar cada producto
            foreach ($validated['products'] as $product) {
                try {
                    // Verificar que el producto existe en VMAE_PROD_IQVIA con mercado RESTO
                    $productoExiste = DB::connection('sqlsrv')
                        ->table('dbo.VMAE_PROD_IQVIA')
                        ->where('codigoPresentacion', $product['code'])
                        ->where('MERCADO', 'RESTO')
                        ->exists();
                        
                    if (!$productoExiste) {
                        $errors[] = "Producto {$product['code']} no encontrado en RESTO";
                        continue;
                    }

                    // Verificar que el producto no esté ya asignado al mercado destino
                    $yaAsignado = DB::connection('sqlsrv')
                        ->table('ODS.TAB_CONFIGURACION')
                        ->where('codigo', $product['code'])
                        ->where('idMercado', $validated['idMercado'])
                        ->exists();
                        
                    if ($yaAsignado) {
                        $errors[] = "Producto {$product['code']} ya está asignado al mercado";
                        continue;
                    }

                    // PASO 1: Eliminar la configuración existente del producto (que debería estar en RESTO)
                    DB::connection('sqlsrv')
                        ->table('ODS.TAB_CONFIGURACION')
                        ->where('codigo', $product['code'])
                        ->delete();

                    // PASO 2: Ejecutar el stored procedure ODS.SP_INSERT_CONFIGURACION para el nuevo mercado
                    DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_CONFIGURACION ?, ?, ?', [
                        $validated['idMercado'],  // @idMercado
                        $product['code'],         // @codigo
                        $product['fuente']        // @fuente
                    ]);

                    $assignedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Error asignando producto {$product['code']}: " . $e->getMessage();
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'assigned_count' => $assignedCount,
                'total_requested' => count($validated['products']),
                'market_name' => $mercado->mercado
            ];

            if (!empty($errors)) {
                $response['warnings'] = $errors;
                $response['message'] = "Se asignaron {$assignedCount} productos correctamente. " . count($errors) . " productos tuvieron errores.";
            } else {
                $response['message'] = "Todos los productos fueron asignados correctamente al mercado {$mercado->mercado}";
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar productos: ' . $e->getMessage()
            ], 500);
        }
    }
}
