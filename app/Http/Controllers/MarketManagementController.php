<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\VmaeProductoIqvia;

class MarketManagementController extends Controller
{
    /**
     * Obtener información de franquicia de un usuario para display/logging
     */
    private function getFranquiciaInfo($user)
    {
        if (!$user || !$user->idFranquicia) {
            return null;
        }

        return \DB::connection('sqlsrv')
            ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
            ->where('idFranquicia', $user->idFranquicia)
            ->value('franquicia');
    }

    /**
     * Aplicar filtro de gerente de producto a una query de productos
     */
    private function aplicarFiltroGerenteProducto($query, $user)
    {
        if (!$user || $user->idRol != 2 || !$user->idFranquicia) {
            return $query;
        }

        // Obtener el nombre de la franquicia del usuario
        $nombreFranquicia = \DB::connection('sqlsrv')
            ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
            ->where('idFranquicia', $user->idFranquicia)
            ->value('franquicia');

        if (!$nombreFranquicia || $nombreFranquicia === 'ADMIN') {
            return $query;
        }

        // Construir posibles variaciones del nombre del gerente
        $nombreCompleto = strtoupper($user->usuario);
        $partesNombre = explode(' ', $nombreCompleto);
        
        $posiblesNombres = [];
        if (count($partesNombre) >= 2) {
            // Formato original: "ZINGARA ROJAS"
            $posiblesNombres[] = $nombreCompleto;
            // Formato invertido: "ROJAS ZINGARA" 
            $posiblesNombres[] = $partesNombre[1] . ' ' . $partesNombre[0];
            // Solo apellido si es necesario
            $posiblesNombres[] = $partesNombre[1];
            // Solo nombre si es necesario
            $posiblesNombres[] = $partesNombre[0];
        } else {
            $posiblesNombres[] = $nombreCompleto;
        }

        return $query->where('v.Franquicia', $nombreFranquicia)
                     ->whereIn('v.Gerente_Producto', $posiblesNombres)
                     ->whereNotNull('v.Franquicia')
                     ->whereNotNull('v.Gerente_Producto');
    }

    /**
     * Obtener los mercados asignados específicamente a un gerente de producto
     */
    private function getMercadosDelGerente($user)
    {
        if (!$user || $user->idRol != 2 || !$user->idFranquicia) {
            return null;
        }

        // Obtener el nombre de la franquicia del usuario
        $nombreFranquicia = \DB::connection('sqlsrv')
            ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
            ->where('idFranquicia', $user->idFranquicia)
            ->value('franquicia');

        if (!$nombreFranquicia || $nombreFranquicia === 'ADMIN') {
            return null;
        }

        // Construir posibles variaciones del nombre del gerente
        $nombreCompleto = strtoupper($user->usuario);
        $partesNombre = explode(' ', $nombreCompleto);
        
        $posiblesNombres = [];
        if (count($partesNombre) >= 2) {
            // Formato original: "ZINGARA ROJAS"
            $posiblesNombres[] = $nombreCompleto;
            // Formato invertido: "ROJAS ZINGARA" 
            $posiblesNombres[] = $partesNombre[1] . ' ' . $partesNombre[0];
            // Solo apellido si es necesario
            $posiblesNombres[] = $partesNombre[1];
            // Solo nombre si es necesario
            $posiblesNombres[] = $partesNombre[0];
        } else {
            $posiblesNombres[] = $nombreCompleto;
        }

        // Obtener mercados que pertenecen a la franquicia del usuario Y están asignados a este gerente
        return VmaeProductoIqvia::select('MERCADO')
            ->distinct()
            ->where('Franquicia', $nombreFranquicia)
            ->whereIn('Gerente_Producto', $posiblesNombres)
            ->whereNotNull('MERCADO')
            ->whereNotNull('Gerente_Producto')
            ->pluck('MERCADO')
            ->toArray();
    }

    /**
     * Display all markets with management capabilities
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search'); // Parámetro de búsqueda
            $user = Auth::user(); // Usuario autenticado

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
                ->where('e.estado', '!=', 'INACTIVO'); // Excluir mercados inactivos

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto (rol 2), filtrar por mercados asignados
            if ($user && $user->idRol == 2 && $user->idFranquicia) {
                $mercadosDelGerente = $this->getMercadosDelGerente($user);
                
                if ($mercadosDelGerente && !empty($mercadosDelGerente)) {
                    $marketsQuery->whereIn('m.mercado', $mercadosDelGerente);
                } else {
                    // Si no hay mercados asignados a este gerente, no mostrar ninguno
                    $marketsQuery->whereRaw('1 = 0');
                }
            }

            $marketsQuery->orderBy('m.fechaRegistro', 'asc');

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
            $statsQuery = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('e.estado')
                ->where('e.estado', '!=', 'INACTIVO'); // Excluir mercados inactivos de las estadísticas

            // Aplicar el mismo filtro de franquicia y gerente a las estadísticas
            if ($user && $user->idRol == 2 && $user->idFranquicia) {
                $mercadosDelGerente = $this->getMercadosDelGerente($user);
                
                if ($mercadosDelGerente && !empty($mercadosDelGerente)) {
                    $statsQuery->whereIn('m.mercado', $mercadosDelGerente);
                } else {
                    $statsQuery->whereRaw('1 = 0');
                }
            }

            $allMarkets = $statsQuery->get();

            $stats = [
                'total' => $allMarkets->count(),
                'activos' => $allMarkets->where('estado', 'ACTIVO')->count(),
                'inactivos' => $allMarkets->where('estado', 'INACTIVO')->count()
            ];

            // Consulta de mercados ejecutada correctamente
            return view('market-management.index', [
                'markets' => $markets,
                'search' => $search,
                'userFranquicia' => $this->getFranquiciaInfo($user),
                'isGerenteProducto' => $user ? $user->idRol == 2 : false
            ]);

        } catch (\Exception $e) {
            // Error al cargar lista de mercados
            return back()->with('error', 'Error al cargar mercados: ' . $e->getMessage());
        }
    }

    /**
     * Create a new market using stored procedure ODS.SP_INSERT_MERCADO with audit
     */
    public function createMarket(Request $request)
    {
        $request->validate([
            'market_name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            
            $marketName = trim($request->market_name);
            
            // Obtener el usuario autenticado y su ID correcto
            $user = Auth::user();
            $userId = $user->idUsuario; // Usar la columna correcta para el ID
            
            // Debug: verificar que el ID sea numérico
            if (!is_numeric($userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ID de usuario no válido'
                ], 500);
            }
            
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
            
            // Llamar al stored procedure para insertar el mercado con los parámetros requeridos
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_MERCADO ?, ?', [$marketName, (int)$userId]);

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
     * Update market name using stored procedure SP_UPDATE_MERCADO with audit
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
            $userId = Auth::id();
            
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

            // Llamar al stored procedure para actualizar el mercado
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_MERCADO ?, ?', [
                $request->market_id,  // @idMercado
                $marketName          // @mercado
            ]);
            
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
            $user = Auth::user(); // Usuario autenticado

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
                ->where('e.estado', '!=', 'INACTIVO'); // Excluir mercados inactivos

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto (rol 2), filtrar por mercados asignados
            if ($user && $user->idRol == 2 && $user->idFranquicia) {
                $mercadosDelGerente = $this->getMercadosDelGerente($user);
                
                if ($mercadosDelGerente && !empty($mercadosDelGerente)) {
                    $query->whereIn('m.mercado', $mercadosDelGerente);
                } else {
                    // Si no hay mercados asignados a este gerente, no mostrar ninguno
                    $query->whereRaw('1 = 0');
                }
            }

            $query->orderBy('m.fechaRegistro', 'asc');

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
     * Get products for a specific market with cursor pagination - ULTRA OPTIMIZED
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

            // Obtener usuario autenticado para filtro de franquicia
            $user = Auth::user();

            // OPTIMIZACIÓN 1: Cache del mercado para evitar consulta repetida
            static $marketCache = [];
            if (!isset($marketCache[$marketId])) {
                $marketCache[$marketId] = DB::connection('sqlsrv')
                    ->table('ODS.TAB_MERCADO')
                    ->where('idMercado', $marketId)
                    ->first();
            }
            $market = $marketCache[$marketId];

            if (!$market) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mercado no existe'
                ], 404);
            }

            // OPTIMIZACIÓN 2: Parámetros de búsqueda y paginación optimizados
            $search = trim($request->get('search', ''));
            $perPage = min(max((int) $request->get('per_page', 30), 20), 100);
            $cursor = $request->get('cursor');

            // Obtener filtros - solo los que tienen valor
            $filters = array_filter([
                'descripcionFF3' => trim($request->get('filter_descripcionFF3', '')),
                'descripcionATC4' => trim($request->get('filter_descripcionATC4', '')),
                'descripcionLaboratorio' => trim($request->get('filter_descripcionLaboratorio', '')),
                'fuente' => trim($request->get('filter_fuente', '')),
                'molecula' => trim($request->get('filter_molecula', '')),
                'descripcionCorporacion' => trim($request->get('filter_descripcionCorporacion', ''))
            ]);

            // OPTIMIZACIÓN 3: Query base simplificado - SIN JOIN inicial para count
            $hasFilters = !empty($filters) || !empty($search);
            
            // Determinar si necesitamos JOIN para fuente
            $needsFuenteJoin = isset($filters['fuente']) || ($hasFilters && $perPage > 100);

            // OPTIMIZACIÓN 4: Count rápido sin JOIN si no es necesario
            if (!$needsFuenteJoin) {
                $countQuery = DB::connection('sqlsrv')
                    ->table('dbo.VMAE_PROD_IQVIA as v')
                    ->where('v.MERCADO', $market->mercado);

                // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto, filtrar productos asignados
                $this->aplicarFiltroGerenteProducto($countQuery, $user);
                    
                $this->applySearchAndFiltersNoJoin($countQuery, $search, $filters);
                $totalProducts = $countQuery->count();
                
                // Para mercados pequeños (<=100), cargar todo sin JOIN
                if ($totalProducts <= 100) {
                    $productos = $countQuery
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
                            DB::raw("'IQV' as fuente") // Fuente por defecto
                        ])
                        ->orderBy('v.codigoPresentacion')
                        ->get();

                    return response()->json([
                        'success' => true,
                        'data' => $productos->toArray(),
                        'market' => ['id' => $market->idMercado, 'name' => $market->mercado],
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
                        'filters' => [
                            'active_filters' => $filters,
                            'filter_count' => count($filters)
                        ],
                        'query_info' => [
                            'optimization' => 'ultra_fast_no_join',
                            'total_products' => $totalProducts,
                            'market_filter' => $market->mercado,
                            'includes_fuente' => false
                        ]
                    ]);
                }
            }

            // OPTIMIZACIÓN 5: Para mercados grandes, usar paginación con JOIN mínimo
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v');

            // Solo hacer JOIN si realmente necesitamos la fuente
            if ($needsFuenteJoin) {
                $baseQuery->leftJoin('ODS.TAB_CONFIGURACION as c', function($join) use ($market) {
                    $join->on('v.codigoPresentacion', '=', 'c.codigo')
                         ->where('c.idMercado', '=', $market->idMercado);
                });
                
                $selectFields = [
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
                    DB::raw("COALESCE(c.fuente, 'IQV') as fuente")
                ];
            } else {
                $selectFields = [
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
                    DB::raw("'IQV' as fuente")
                ];
            }

            $baseQuery->select($selectFields)->where('v.MERCADO', $market->mercado);

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto, filtrar productos asignados
            $this->aplicarFiltroGerenteProducto($baseQuery, $user);

            // Aplicar filtros optimizados
            if ($needsFuenteJoin) {
                $this->applySearchAndFiltersWithJoin($baseQuery, $search, $filters);
            } else {
                $this->applySearchAndFiltersNoJoin($baseQuery, $search, $filters);
            }

            // OPTIMIZACIÓN 6: Cursor pagination optimizado
            if ($cursor) {
                try {
                    $decodedCursor = base64_decode($cursor);
                    $cursorData = json_decode($decodedCursor, true);
                    
                    if ($cursorData && isset($cursorData['codigoPresentacion'])) {
                        $baseQuery->where('v.codigoPresentacion', '>', $cursorData['codigoPresentacion']);
                    }
                } catch (\Exception $e) {
                    // Cursor inválido, ignorar
                }
            }

            $baseQuery->orderBy('v.codigoPresentacion');

            // Obtener productos con límite +1 para verificar paginación
            $productos = $baseQuery->limit($perPage + 1)->get();
            
            $hasMorePages = $productos->count() > $perPage;
            if ($hasMorePages) {
                $productos = $productos->take($perPage);
            }

            // Generar cursor siguiente
            $nextCursor = null;
            if ($hasMorePages && $productos->isNotEmpty()) {
                $lastItem = $productos->last();
                $nextCursor = base64_encode(json_encode([
                    'codigoPresentacion' => $lastItem->codigoPresentacion
                ]));
            }

            return response()->json([
                'success' => true,
                'data' => $productos->toArray(),
                'market' => ['id' => $market->idMercado, 'name' => $market->mercado],
                'pagination' => [
                    'per_page' => $perPage,
                    'next_cursor' => $nextCursor,
                    'prev_cursor' => null,
                    'has_more_pages' => $hasMorePages,
                    'has_previous_pages' => false,
                ],
                'loaded_count' => $productos->count(),
                'search' => [
                    'query' => $search,
                    'has_search' => !empty($search),
                    'results_count' => $productos->count()
                ],
                'filters' => [
                    'active_filters' => $filters,
                    'filter_count' => count($filters)
                ],
                'query_info' => [
                    'optimization' => 'ultra_optimized_cursor',
                    'market_filter' => $market->mercado,
                    'uses_join' => $needsFuenteJoin,
                    'cursor_applied' => $cursor !== null
                ]
            ]);

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
            $user = Auth::user(); // Usuario autenticado

            // Obtener filtros individuales
            $filters = [
                'descripcionFF3' => trim($request->get('filter_descripcionFF3', '')),
                'descripcionATC4' => trim($request->get('filter_descripcionATC4', '')),
                'descripcionLaboratorio' => trim($request->get('filter_descripcionLaboratorio', '')),
                'fuente' => trim($request->get('filter_fuente', '')),
                'molecula' => trim($request->get('filter_molecula', '')),
                'descripcionCorporacion' => trim($request->get('filter_descripcionCorporacion', ''))
            ];

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
                    'v.Franquicia', // Agregar franquicia para filtrado
                    DB::raw("COALESCE(c.fuente, 'IQV') as fuente") // Usar 'IQV' como fuente por defecto
                ])
                ->where('v.MERCADO', 'RESTO');

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto, filtrar productos asignados
            $this->aplicarFiltroGerenteProducto($baseQuery, $user);
            
            if ($user && $user->idRol == 2 && $user->idFranquicia) {
                // Obtener el nombre de la franquicia para logging
                $nombreFranquicia = \DB::connection('sqlsrv')
                    ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
                    ->where('idFranquicia', $user->idFranquicia)
                    ->value('franquicia');
                    
                // Log para debugging
                \Log::info('Filtro RESTO por franquicia y gerente aplicado', [
                    'user_id' => $user->idUsuario,
                    'user_role' => $user->idRol,
                    'franquicia' => $nombreFranquicia,
                    'usuario' => $user->usuario
                ]);
            } else {
                // Log para debugging cuando no se aplica filtro
                \Log::info('Filtro RESTO por franquicia NO aplicado', [
                    'user_id' => $user ? $user->idUsuario : 'no-user',
                    'user_role' => $user ? $user->idRol : 'no-role',
                    'idFranquicia' => $user ? $user->idFranquicia : 'no-franquicia'
                ]);
            }

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

            // Aplicar filtros individuales
            $this->applyIndividualFilters($baseQuery, $filters);

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
            
            // Log para debugging - SQL query
            \Log::info('SQL Query RESTO products', [
                'sql' => $baseQuery->toSql(),
                'bindings' => $baseQuery->getBindings()
            ]);
            
            $productos = $baseQuery->limit($perPage + 1)->get();
            
            // Log para debugging - resultados
            \Log::info('RESTO products query results', [
                'count' => $productos->count(),
                'first_product_franquicia' => $productos->isNotEmpty() ? $productos->first()->Franquicia : null,
                'user_franquicia' => $this->getFranquiciaInfo($user)
            ]);
            
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
                    'has_more_pages' => $hasMorePages,
                    'debug_info' => [
                        'user_franquicia' => $this->getFranquiciaInfo($user),
                        'user_role' => $user ? $user->idRol : null,
                        'filter_applied' => $user && $user->idRol == 2 && $user->idFranquicia,
                        'total_count' => $totalProducts,
                        'returned_count' => $productos->count(),
                        'first_product_franquicia' => $productos->isNotEmpty() ? $productos->first()->Franquicia : null
                    ]
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
     * Get filter options for resto products modal
     */
    public function getRestoFilterOptions(Request $request)
    {
        try {
            $user = Auth::user(); // Usuario autenticado
            
            // Base query para RESTO products con filtro de franquicia si aplica
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA')
                ->where('MERCADO', 'RESTO');

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto, filtrar productos asignados
            $this->aplicarFiltroGerenteProducto($baseQuery, $user);
            
            if ($user && $user->idRol == 2 && $user->idFranquicia) {
                // Obtener el nombre de la franquicia para logging
                $nombreFranquicia = \DB::connection('sqlsrv')
                    ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
                    ->where('idFranquicia', $user->idFranquicia)
                    ->value('franquicia');
                    
                // Log para debugging
                \Log::info('Filtro opciones RESTO por franquicia y gerente aplicado', [
                    'user_id' => $user->idUsuario,
                    'user_role' => $user->idRol,
                    'franquicia' => $nombreFranquicia,
                    'usuario' => $user->usuario
                ]);
            }

            // Get distinct values for each filter field from RESTO products
            $filterOptions = [];

            // FF3 options
            $ff3Options = (clone $baseQuery)
                ->whereNotNull('descripcionFF3')
                ->where('descripcionFF3', '<>', '')
                ->distinct()
                ->orderBy('descripcionFF3')
                ->pluck('descripcionFF3')
                ->toArray();

            // ATC4 options
            $atc4Options = (clone $baseQuery)
                ->whereNotNull('descripcionATC4')
                ->where('descripcionATC4', '<>', '')
                ->distinct()
                ->orderBy('descripcionATC4')
                ->pluck('descripcionATC4')
                ->toArray();

            // Laboratorio options
            $laboratorioOptions = (clone $baseQuery)
                ->whereNotNull('descripcionLaboratorio')
                ->where('descripcionLaboratorio', '<>', '')
                ->distinct()
                ->orderBy('descripcionLaboratorio')
                ->pluck('descripcionLaboratorio')
                ->toArray();

            // Fuente options (con JOIN a TAB_CONFIGURACION)
            $fuenteQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->where('v.MERCADO', 'RESTO');
            
            // Aplicar filtro de franquicia y gerente también aquí
            $this->aplicarFiltroGerenteProducto($fuenteQuery, $user);
            
            $fuenteOptions = $fuenteQuery
                ->select(DB::raw("COALESCE(c.fuente, 'IQV') as fuente"))
                ->distinct()
                ->orderBy('fuente')
                ->pluck('fuente')
                ->toArray();

            // Molecula options
            $moleculaOptions = (clone $baseQuery)
                ->whereNotNull('molecula')
                ->where('molecula', '<>', '')
                ->distinct()
                ->orderBy('molecula')
                ->pluck('molecula')
                ->toArray();

            // Corporacion options
            $corporacionOptions = (clone $baseQuery)
                ->whereNotNull('descripcionCorporacion')
                ->where('descripcionCorporacion', '<>', '')
                ->distinct()
                ->orderBy('descripcionCorporacion')
                ->pluck('descripcionCorporacion')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'descripcionFF3' => array_values($ff3Options),
                    'descripcionATC4' => array_values($atc4Options),
                    'descripcionLaboratorio' => array_values($laboratorioOptions),
                    'fuente' => array_values($fuenteOptions),
                    'molecula' => array_values($moleculaOptions),
                    'descripcionCorporacion' => array_values($corporacionOptions)
                ],
                'user_info' => [
                    'franquicia' => $user ? $user->franquicia : null,
                    'filtered_by_franquicia' => $user && $user->idRol == 2 && $user->franquicia && $user->franquicia !== 'ADMIN'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar opciones de filtros: ' . $e->getMessage()
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

    /**
     * Aplicar filtros individuales a la consulta CON JOIN
     */
    private function applyIndividualFilters($query, $filters)
    {
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                switch ($field) {
                    case 'descripcionFF3':
                        $query->where('v.descripcionFF3', 'LIKE', "%{$value}%");
                        break;
                    case 'descripcionATC4':
                        $query->where('v.descripcionATC4', 'LIKE', "%{$value}%");
                        break;
                    case 'descripcionLaboratorio':
                        $query->where('v.descripcionLaboratorio', 'LIKE', "%{$value}%");
                        break;
                    case 'fuente':
                        $query->where('c.fuente', 'LIKE', "%{$value}%");
                        break;
                    case 'molecula':
                        $query->where('v.molecula', 'LIKE', "%{$value}%");
                        break;
                    case 'descripcionCorporacion':
                        $query->where('v.descripcionCorporacion', 'LIKE', "%{$value}%");
                        break;
                }
            }
        }
    }

    /**
     * Aplicar búsqueda y filtros optimizados SIN JOIN (ultra rápido)
     */
    private function applySearchAndFiltersNoJoin($query, $search, $filters)
    {
        // Aplicar búsqueda global
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

        // Aplicar filtros individuales (excluyendo fuente)
        foreach ($filters as $field => $value) {
            if (!empty($value) && $field !== 'fuente') {
                switch ($field) {
                    case 'descripcionFF3':
                        $query->where('v.descripcionFF3', 'LIKE', "%{$value}%");
                        break;
                    case 'descripcionATC4':
                        $query->where('v.descripcionATC4', 'LIKE', "%{$value}%");
                        break;
                    case 'descripcionLaboratorio':
                        $query->where('v.descripcionLaboratorio', 'LIKE', "%{$value}%");
                        break;
                    case 'molecula':
                        $query->where('v.molecula', 'LIKE', "%{$value}%");
                        break;
                    case 'descripcionCorporacion':
                        $query->where('v.descripcionCorporacion', 'LIKE', "%{$value}%");
                        break;
                }
            }
        }
    }

    /**
     * Aplicar búsqueda y filtros CON JOIN (cuando se necesita fuente)
     */
    private function applySearchAndFiltersWithJoin($query, $search, $filters)
    {
        // Aplicar búsqueda global
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

        // Aplicar todos los filtros incluyendo fuente
        $this->applyIndividualFilters($query, $filters);
    }
    
    /**
     * Obtener auditoría de mercados
     */
    public function getMarketAudit(Request $request)
    {
        try {
            $marketId = $request->get('market_id');
            $userId = $request->get('user_id');
            $action = $request->get('action');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $limit = min(max((int) $request->get('limit', 50), 10), 200);
            
            // Query base usando la vista de auditoría
            $query = DB::connection('sqlsrv')
                ->table('ODS.VW_MERCADO_AUDIT')
                ->select([
                    'idAuditMercado',
                    'idUsuario',
                    'nombre_usuario',
                    'email_usuario',
                    'idMercado',
                    'nombre_mercado_actual',
                    'accion',
                    'fecha',
                    'mercado_anterior',
                    'mercado_nuevo',
                    'estado_anterior_desc',
                    'estado_nuevo_desc',
                    'observaciones',
                    'tiempo_transcurrido'
                ])
                ->orderBy('fecha', 'desc');
            
            // Aplicar filtros
            if ($marketId) {
                $query->where('idMercado', $marketId);
            }
            
            if ($userId) {
                $query->where('idUsuario', $userId);
            }
            
            if ($action) {
                $query->where('accion', $action);
            }
            
            if ($dateFrom) {
                $query->where('fecha', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $query->where('fecha', '<=', $dateTo . ' 23:59:59');
            }
            
            // Obtener resultados
            $auditRecords = $query->limit($limit)->get();
            
            // Obtener estadísticas adicionales
            $statsQuery = DB::connection('sqlsrv')
                ->table('ODS.VW_MERCADO_AUDIT');
                
            if ($dateFrom) {
                $statsQuery->where('fecha', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $statsQuery->where('fecha', '<=', $dateTo . ' 23:59:59');
            }
            
            $stats = [
                'total_registros' => $statsQuery->count(),
                'por_accion' => $statsQuery
                    ->select('accion')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('accion')
                    ->get()
                    ->pluck('total', 'accion'),
                'usuarios_activos' => $statsQuery
                    ->distinct()
                    ->count('idUsuario'),
                'mercados_modificados' => $statsQuery
                    ->distinct()
                    ->count('idMercado')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $auditRecords->toArray(),
                'total' => $auditRecords->count(),
                'stats' => $stats,
                'filters' => [
                    'market_id' => $marketId,
                    'user_id' => $userId,
                    'action' => $action,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'limit' => $limit
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo auditoría de mercados: ' . $e->getMessage()
            ], 500);
        }
    }
}
