<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Services\NotificationService;

class ProductosController extends Controller
{
    /**
     * Display the main productos view
     */
    public function index(Request $request)
    {
        try {
            return view('productos.index');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar la vista de productos: ' . $e->getMessage());
        }
    }

    /**
     * Get products with traditional pagination - OPTIMIZED
     */
    public function getProductsApi(Request $request)
    {
        try {
            // Parámetros de búsqueda y paginación
            $search = trim($request->get('search', ''));
            $perPage = min(max((int) $request->get('per_page', 50), 20), 100);
            $page = max((int) $request->get('page', 1), 1);
            $offset = ($page - 1) * $perPage;

            // Obtener filtros - solo los que tienen valor
            $filters = array_filter([
                'descripcionProducto' => trim($request->get('filter_descripcionProducto', '')),
                'descripcionFF3' => trim($request->get('filter_descripcionFF3', '')),
                'descripcionATC4' => trim($request->get('filter_descripcionATC4', '')),
                'descripcionLaboratorio' => trim($request->get('filter_descripcionLaboratorio', '')),
                'fuente' => trim($request->get('filter_fuente', '')),
                'molecula' => trim($request->get('filter_molecula', '')),
                'descripcionCorporacion' => trim($request->get('filter_descripcionCorporacion', '')),
                'marcaGenerico' => trim($request->get('filter_marcaGenerico', '')),
                'eticoPopular' => trim($request->get('filter_eticoPopular', '')),
                'mercado' => trim($request->get('filter_mercado', ''))
            ]);

            // Obtener parámetros de ordenamiento
            $sortField = $request->get('sort_field');
            $sortDirection = $request->get('sort_direction', 'asc');

            // Query base con JOIN para obtener la fuente y mercado real de configuración
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->leftJoin('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->select([
                    'v.codigoPresentacion',
                    'v.descripcionPresentacion',
                    'v.descripcionProducto',
                    'v.marcaGenerico',
                    'v.eticoPopular',
                    'v.molecula',
                    DB::raw("CASE 
                        WHEN v.MERCADO = 'NUEVOS' THEN NULL 
                        ELSE COALESCE(c.fuente, 'IQV') 
                    END as fuente"),
                    'v.codigoFF3',
                    'v.descripcionFF3',
                    'v.codigoATC4',
                    'v.descripcionATC4',
                    'v.descripcionLaboratorio',
                    'v.descripcionCorporacion',
                    // Usar el mercado de la configuración si existe, sino el de la vista VMAE
                    DB::raw("COALESCE(m.mercado, v.MERCADO) as mercado")
                ]);

            // Aplicar búsqueda global
            if (!empty($search)) {
                $baseQuery->where(function($query) use ($search) {
                    $query->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('v.descripcionProducto', 'LIKE', "%{$search}%")
                          ->orWhere('v.molecula', 'LIKE', "%{$search}%")
                          ->orWhere('v.MERCADO', 'LIKE', "%{$search}%");
                });
            }

            // Aplicar filtros individuales
            foreach ($filters as $field => $value) {
                if (!empty($value)) {
                    switch ($field) {
                        case 'fuente':
                            $baseQuery->where('c.fuente', '=', $value);
                            break;
                        case 'mercado':
                            $baseQuery->where('v.MERCADO', '=', $value);
                            break;
                        case 'descripcionProducto':
                            // Solo este campo mantiene búsqueda parcial
                            // $baseQuery->where('v.descripcionProducto', 'LIKE', "%{$value}%");
                            $baseQuery->where('v.descripcionProducto', '=', $value);
                            break;
                        case 'descripcionFF3':
                            // Verificar si el valor contiene un guión (formato código - descripción)
                            if (strpos($value, ' - ') !== false) {
                                list($codigo, $descripcion) = explode(' - ', $value, 2);
                                $baseQuery->where(function($query) use ($codigo, $descripcion) {
                                    $query->whereRaw('UPPER(LTRIM(RTRIM(v.codigoFF3))) = ?', [strtoupper(trim($codigo))])
                                          ->whereRaw('UPPER(LTRIM(RTRIM(v.descripcionFF3))) = ?', [strtoupper(trim($descripcion))]);
                                });
                            } else {
                                // Buscar tanto por código como por descripción individual
                                $baseQuery->where(function($query) use ($value) {
                                    $query->where('v.descripcionFF3', '=', $value)
                                          ->orWhere('v.codigoFF3', '=', $value);
                                });
                            }
                            break;
                        case 'descripcionATC4':
                            // Verificar si el valor contiene un guión (formato código - descripción)
                            if (strpos($value, ' - ') !== false) {
                                list($codigo, $descripcion) = explode(' - ', $value, 2);
                                $codigo = trim($codigo);
                                $descripcion = trim($descripcion);
                                
                                $baseQuery->where(function($query) use ($codigo, $descripcion) {
                                    $query->whereRaw('UPPER(LTRIM(RTRIM(v.codigoATC4))) = ?', [strtoupper($codigo)])
                                          ->whereRaw('UPPER(LTRIM(RTRIM(v.descripcionATC4))) = ?', [strtoupper($descripcion)]);
                                });
                            } else {
                                // Buscar tanto por código como por descripción individual
                                $baseQuery->where(function($query) use ($value) {
                                    $query->where('v.descripcionATC4', '=', $value)
                                          ->orWhere('v.codigoATC4', '=', $value);
                                });
                            }
                            break;
                        case 'descripcionLaboratorio':
                            $baseQuery->where('v.descripcionLaboratorio', '=', $value);
                            break;
                        case 'molecula':
                            $baseQuery->where('v.molecula', '=', $value);
                            break;
                        case 'descripcionCorporacion':
                            $baseQuery->where('v.descripcionCorporacion', '=', $value);
                            break;
                        case 'marcaGenerico':
                            $baseQuery->where('v.marcaGenerico', '=', $value);
                            break;
                        case 'eticoPopular':
                            $baseQuery->where('v.eticoPopular', '=', $value);
                            break;
                    }
                }
            }



            // Aplicar ordenamiento dinámico
            if ($sortField) {
                // Mapear campos de ordenamiento a las columnas correctas
                $sortMapping = [
                    'descripcionPresentacion' => 'v.descripcionPresentacion',
                    'descripcionProducto' => 'v.descripcionProducto',
                    'marcaGenerico' => 'v.marcaGenerico',
                    'eticoPopular' => 'v.eticoPopular',
                    'molecula' => 'v.molecula',
                    'descripcionFF3' => 'v.descripcionFF3',
                    'descripcionATC4' => 'v.descripcionATC4',
                    'descripcionLaboratorio' => 'v.descripcionLaboratorio',
                    'descripcionCorporacion' => 'v.descripcionCorporacion',
                    'mercado' => 'mercado', // Campo calculado
                    'fuente' => 'fuente' // Campo calculado
                ];

                if (isset($sortMapping[$sortField])) {
                    $baseQuery->orderBy($sortMapping[$sortField], $sortDirection);
                } else {
                    // Ordenamiento por defecto si el campo no es válido
                    $baseQuery->orderBy('v.codigoPresentacion', 'asc');
                }
            } else {
                // Ordenamiento por defecto
                $baseQuery->orderBy('v.codigoPresentacion', 'asc');
            }

            // Obtener el total de registros para la paginación
            $totalCount = $baseQuery->count();
            
            // Obtener productos con paginación tradicional
            $productos = $baseQuery->offset($offset)->limit($perPage)->get();
            
            // Calcular información de paginación
            $totalPages = ceil($totalCount / $perPage);
            $hasNextPage = $page < $totalPages;
            $hasPreviousPage = $page > 1;

            return response()->json([
                'success' => true,
                'data' => $productos->toArray(),
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'per_page' => $perPage,
                    'has_next_page' => $hasNextPage,
                    'has_previous_page' => $hasPreviousPage,
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $totalCount)
                ],
                'loaded_count' => $productos->count(),
                'search' => [
                    'query' => $search,
                    'results_count' => $productos->count()
                ],
                'filters' => [
                    'active_filters' => $filters,
                    'filter_count' => count($filters)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    /**
     * Get filter options for productos
     */
    /**
     * Get filter options with search and pagination - OPTIMIZED
     */
    public function getFilterOptions(Request $request)
    {
        try {
            $filterType = $request->get('filter_type'); // Qué filtro específico
            $search = trim($request->get('search', '')); // Término de búsqueda
            $limit = min(max((int) $request->get('limit', 100), 20), 500); // Límite de resultados
            
            // Base query
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v');

            $results = [];

            switch ($filterType) {
                case 'descripcionProducto':
                    $query = (clone $baseQuery)
                        ->whereNotNull('descripcionProducto')
                        ->where('descripcionProducto', '<>', '');
                    
                    if ($search) {
                        $query->where('descripcionProducto', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('descripcionProducto')
                        ->limit($limit)
                        ->pluck('descripcionProducto')
                        ->toArray();
                    break;

                case 'marcaGenerico':
                    $query = (clone $baseQuery)
                        ->whereNotNull('marcaGenerico')
                        ->where('marcaGenerico', '<>', '');
                    
                    if ($search) {
                        $query->where('marcaGenerico', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('marcaGenerico')
                        ->limit($limit)
                        ->pluck('marcaGenerico')
                        ->toArray();
                    break;

                case 'eticoPopular':
                    $query = (clone $baseQuery)
                        ->whereNotNull('eticoPopular')
                        ->where('eticoPopular', '<>', '');
                    
                    if ($search) {
                        $query->where('eticoPopular', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('eticoPopular')
                        ->limit($limit)
                        ->pluck('eticoPopular')
                        ->toArray();
                    break;

                case 'descripcionFF3':
                    $query = (clone $baseQuery)
                        ->whereNotNull('descripcionFF3')
                        ->where('descripcionFF3', '<>', '')
                        ->whereNotNull('codigoFF3')
                        ->where('codigoFF3', '<>', '');
                    
                    if ($search) {
                        $query->where(function($q) use ($search) {
                            $q->where('descripcionFF3', 'LIKE', "{$search}%")
                              ->orWhere('codigoFF3', 'LIKE', "{$search}%");
                        });
                    }
                    
                    $results = $query->distinct()
                        ->select('codigoFF3', 'descripcionFF3')
                        ->orderBy('descripcionFF3')
                        ->limit($limit)
                        ->get()
                        ->map(function($item) {
                            return $item->codigoFF3 . ' - ' . $item->descripcionFF3;
                        })
                        ->toArray();
                    break;

                case 'descripcionATC4':
                    $query = (clone $baseQuery)
                        ->whereNotNull('descripcionATC4')
                        ->where('descripcionATC4', '<>', '')
                        ->whereNotNull('codigoATC4')
                        ->where('codigoATC4', '<>', '');
                    
                    if ($search) {
                        $query->where(function($q) use ($search) {
                            $q->where('descripcionATC4', 'LIKE', "{$search}%")
                              ->orWhere('codigoATC4', 'LIKE', "{$search}%");
                        });
                    }
                    
                    $results = $query->distinct()
                        ->select('codigoATC4', 'descripcionATC4')
                        ->orderBy('descripcionATC4')
                        ->limit($limit)
                        ->get()
                        ->map(function($item) {
                            return $item->codigoATC4 . ' - ' . $item->descripcionATC4;
                        })
                        ->toArray();
                    break;

                case 'descripcionLaboratorio':
                    $query = (clone $baseQuery)
                        ->whereNotNull('descripcionLaboratorio')
                        ->where('descripcionLaboratorio', '<>', '');
                    
                    if ($search) {
                        $query->where('descripcionLaboratorio', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('descripcionLaboratorio')
                        ->limit($limit)
                        ->pluck('descripcionLaboratorio')
                        ->toArray();
                    break;

                case 'descripcionCorporacion':
                    $query = (clone $baseQuery)
                        ->whereNotNull('descripcionCorporacion')
                        ->where('descripcionCorporacion', '<>', '');
                    
                    if ($search) {
                        $query->where('descripcionCorporacion', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('descripcionCorporacion')
                        ->limit($limit)
                        ->pluck('descripcionCorporacion')
                        ->toArray();
                    break;

                case 'molecula':
                    $query = (clone $baseQuery)
                        ->whereNotNull('molecula')
                        ->where('molecula', '<>', '');
                    
                    if ($search) {
                        $query->where('molecula', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('molecula')
                        ->limit($limit)
                        ->pluck('molecula')
                        ->toArray();
                    break;

                case 'mercado':
                    $query = (clone $baseQuery)
                        ->whereNotNull('MERCADO')
                        ->where('MERCADO', '<>', '');
                    
                    if ($search) {
                        $query->where('MERCADO', 'LIKE', "{$search}%");
                    }
                    
                    // Para mercado, usar un límite mucho mayor para asegurar que se carguen todos
                    $mercadoLimit = $search ? $limit : 2000; // Sin búsqueda: 2000, con búsqueda: límite normal
                    
                    $results = $query->distinct()
                        ->orderBy('MERCADO')
                        ->limit($mercadoLimit)
                        ->pluck('MERCADO')
                        ->filter(function($mercado) {
                            return !empty(trim($mercado));
                        })
                        ->unique()
                        ->values()
                        ->map(function($mercado) {
                            // Convertir RESTO a SIN ASIGNAR para mostrar en el filtro
                            return $mercado === 'RESTO' ? 'SIN ASIGNAR' : $mercado;
                        })
                        ->toArray();
                    
                    break;

                case 'fuente':
                    $fuenteQuery = DB::connection('sqlsrv')
                        ->table('dbo.VMAE_PROD_IQVIA as v')
                        ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                        ->select(DB::raw("CASE 
                            WHEN v.MERCADO = 'NUEVOS' THEN NULL 
                            ELSE COALESCE(c.fuente, 'IQV') 
                        END as fuente"));
                    
                    if ($search) {
                        $fuenteQuery->havingRaw("CASE 
                            WHEN v.MERCADO = 'NUEVOS' THEN NULL 
                            ELSE COALESCE(c.fuente, 'IQV') 
                        END LIKE ?", ["{$search}%"]);
                    }
                    
                    $results = $fuenteQuery->distinct()
                        ->orderBy('fuente')
                        ->limit($limit)
                        ->pluck('fuente')
                        ->filter(function($fuente) {
                            return $fuente !== null && $fuente !== '';
                        })
                        ->toArray();
                    break;

                default:
                    // Si no se especifica tipo, devolver opciones básicas (pequeñas)
                    $results = [
                        'marcaGenerico' => (clone $baseQuery)->whereNotNull('marcaGenerico')->where('marcaGenerico', '<>', '')->distinct()->orderBy('marcaGenerico')->limit(20)->pluck('marcaGenerico')->toArray(),
                        'eticoPopular' => (clone $baseQuery)->whereNotNull('eticoPopular')->where('eticoPopular', '<>', '')->distinct()->orderBy('eticoPopular')->limit(20)->pluck('eticoPopular')->toArray(),
                        'fuente' => ['IQV', 'IQVIA', 'OTRO'] // Opciones fijas pequeñas
                    ];
                    
                    return response()->json([
                        'success' => true,
                        'data' => $results
                    ]);
            }

            return response()->json([
                'success' => true,
                'data' => array_values($results),
                'count' => count($results),
                'has_more' => count($results) >= $limit,
                'search' => $search
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar opciones de filtros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from current market using stored procedure SP_ASIGNAR_RESTO
     */
    public function removeProduct(Request $request)
    {
        $validated =         $request->validate([
            'codigoPresentacion' => 'required|string',
            'note' => 'required|string|max:1000'  // Nota ahora es obligatoria
        ]);

        try {
            // Aumentar el timeout para esta operación específica
            set_time_limit(60);
            
            DB::beginTransaction();
            
            // Verificar que el producto existe en la configuración actual y obtener sus datos
            $configuracionProducto = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION as c')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->select('c.*', 'm.mercado')
                ->where('c.codigo', $validated['codigoPresentacion'])
                ->first();
                
            if (!$configuracionProducto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no se encuentra en la configuración actual'
                ], 404);
            }

            // Obtener el nombre del producto ANTES del SP para evitar consultas post-SP
            $nombreProducto = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA')
                ->where('codigoPresentacion', $validated['codigoPresentacion'])
                ->value('descripcionPresentacion');

            // Ejecutar el stored procedure SP_ASIGNAR_RESTO
            $userId = Auth::user()->idUsuario;
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_ASIGNAR_RESTO ?, ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $userId,                          // @idUsuario
                $validated['note']                // @nota
            ]);

            DB::commit();

            // Enviar notificación por email DESPUÉS del commit para no afectar la transacción
            try {
                $notificationService = new NotificationService();
                $notificationService->notifyProductRemoved(
                    $validated['codigoPresentacion'],
                    $nombreProducto ?? 'Producto no encontrado',
                    $configuracionProducto->mercado,
                    Auth::user(),
                    $validated['note'] ?? null
                );
            } catch (\Exception $e) {
                // Error en notificación pero no interrumpir el flujo
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto removido del mercado exitosamente',
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'refresh_page' => true // Indicar que se debe refrescar la página
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
     * Change product market using stored procedure
     */
    public function changeProductMarket(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string',
            'nuevoMercadoId' => 'required|integer',
            'note' => 'required|string|max:1000'  // Nota ahora es obligatoria
        ]);

        try {
            DB::beginTransaction();
            
            $user = Auth::user(); // Usuario autenticado
            
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
            
            // Obtener el nombre del mercado anterior
            $mercadoAnterior = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $configuracion->idMercado)
                ->value('mercado');

            // Obtener el nombre del producto
            $nombreProducto = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA')
                ->where('codigoPresentacion', $validated['codigoPresentacion'])
                ->value('descripcionPresentacion');

            // Ejecutar el stored procedure ODS.SP_UPDATE_CONFIGURACION
            $userId = Auth::user()->idUsuario;
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_CONFIGURACION ?, ?, ?, ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $configuracion->fuente,           // @fuente (obtenida de la configuración actual)
                $validated['nuevoMercadoId'],     // @idMercado
                $userId,                          // @idUsuario
                $validated['note'] ?? null        // @nota
            ]);

            // Enviar notificación por email
            try {
                $notificationService = new NotificationService();
                $notificationService->notifyProductMoved(
                    $validated['codigoPresentacion'],
                    $nombreProducto ?? 'Producto no encontrado',
                    $mercadoAnterior,
                    $mercadoDestino->mercado,
                    Auth::user(),
                    $validated['note'] ?? null
                );
            } catch (\Exception $e) {
                // Error en notificación pero no interrumpir el flujo
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Producto cambiado exitosamente al mercado: {$mercadoDestino->mercado}",
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'mercadoAnterior' => $configuracion->idMercado,
                'mercadoNuevo' => $validated['nuevoMercadoId'],
                'nombreMercadoNuevo' => $mercadoDestino->mercado,
                'refresh_page' => true // Indicar que se debe refrescar la página
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
     * Get all available markets for dropdown
     */
    public function getMarketsApi(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            // Query para obtener TODOS los mercados activos
            $marketsQuery = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                )
                ->where('e.estado', 'ACTIVO'); // Solo mercados activos

            // Aplicar búsqueda si se proporciona
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where('m.mercado', 'LIKE', $searchTerm);
            }

            $markets = $marketsQuery->orderBy('m.mercado', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $markets,
                'total' => $markets->count(),
                'search' => $search,
                'message' => 'Todos los mercados activos disponibles'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener todos los mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new market using stored procedure ODS.SP_INSERT_MERCADO with audit
     * (Copiado del MarketManagementController)
     */
    public function createMarket(Request $request)
    {

        $request->validate([
            'market_name' => 'required|string|max:255',
            'market_note' => 'required|string|max:1000'  // Nota ahora es obligatoria
        ]);

        try {
            DB::beginTransaction();
            
            $marketName = trim($request->market_name);
            $marketNote = $request->market_note ? trim($request->market_note) : null;
            
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
            
            // Verificar si ya existe un mercado con el mismo nombre (solo mercados activos)
            // Usar UPPER para comparación case-insensitive y LTRIM/RTRIM para eliminar espacios
            $existingMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->whereRaw('UPPER(LTRIM(RTRIM(m.mercado))) = UPPER(?)', [trim($marketName)])
                ->where('e.estado', 'ACTIVO')
                ->select('m.idMercado', 'm.mercado', 'e.estado')
                ->first();

            if ($existingMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un mercado activo con ese nombre'
                ], 422);
            }
            
            // Llamar al stored procedure para insertar el mercado con los parámetros requeridos
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_MERCADO ?, ?, ?', [$marketName, (int)$userId, $marketNote]);

            // Enviar notificación por correo (con nota si existe)
            try {
                $notificationService = new NotificationService();
                $notificationService->notifyNewMarket($marketName, $user, $marketNote);
            } catch (\Exception $emailException) {
                // Error en notificación pero no fallar la creación del mercado
            }

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
     * Assign multiple products to a market using stored procedure ODS.SP_INSERT_CONFIGURACION
     * (Copiado del MarketManagementController)
     */
    public function assignProducts(Request $request)
    {

        $validated = $request->validate([
            'idMercado' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.code' => 'required|string',
            'products.*.fuente' => 'required|string|min:1', // Asegurar que fuente no esté vacía
            'note' => 'required|string|max:1000' // Nota ahora es obligatoria
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
            $assignedProducts = []; // Para trackear productos asignados exitosamente con código y nombre

            // Procesar cada producto
            foreach ($validated['products'] as $product) {
                try {
                    // Verificar que el producto existe en VMAE_PROD_IQVIA con mercado RESTO y obtener su información
                    $productoInfo = DB::connection('sqlsrv')
                        ->table('dbo.VMAE_PROD_IQVIA')
                        ->where('codigoPresentacion', $product['code'])
                        ->where('MERCADO', 'RESTO')
                        ->select('codigoPresentacion', 'descripcionPresentacion')
                        ->first();
                        
                    if (!$productoInfo) {
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
                    // Obtener el ID del usuario autenticado
                    $userId = Auth::user()->idUsuario;
                    
                    DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_CONFIGURACION ?, ?, ?, ?, ?', [
                        $validated['idMercado'],  // @idMercado
                        $product['code'],         // @codigo
                        $product['fuente'],       // @fuente
                        $userId,                  // @idUsuario
                        $validated['note']        // @nota
                    ]);

                    // PASO 3: Intentar actualizar la vista VMAE (si existe un SP para eso)
                    try {
                        // Verificar si existe SP para actualizar VMAE
                        DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_VMAE_MERCADO ?, ?', [
                            $product['code'],         // @codigo
                            $mercado->mercado        // @mercado
                        ]);
                    } catch (\Exception $vmaeException) {
                        // Intentar actualización directa de la vista si es una tabla materializada
                        try {
                            DB::connection('sqlsrv')->statement("
                                UPDATE dbo.VMAE_PROD_IQVIA 
                                SET MERCADO = ? 
                                WHERE codigoPresentacion = ?
                            ", [$mercado->mercado, $product['code']]);
                        } catch (\Exception $directUpdateException) {
                            // No se pudo actualizar VMAE
                        }
                    }

                    // PASO 4: Verificar que la asignación fue exitosa
                    $verificacion = DB::connection('sqlsrv')
                        ->table('ODS.TAB_CONFIGURACION as c')
                        ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                        ->where('c.codigo', $product['code'])
                        ->select('c.codigo', 'm.mercado', 'c.idMercado')
                        ->first();
                    


                    $assignedCount++;
                    // Guardar tanto el código como el nombre del producto
                    $assignedProducts[] = [
                        'code' => $productoInfo->codigoPresentacion,
                        'name' => $productoInfo->descripcionPresentacion
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error asignando producto {$product['code']}: " . $e->getMessage();
                }
            }

            // Enviar notificación por email si se asignaron productos exitosamente
            if ($assignedCount > 0) {
                try {
                    $notificationService = new NotificationService();
                    // Usar el método genérico para múltiples productos
                    $notificationService->notifyMarketAction('assign_product', [
                        'assigned_products' => $assignedProducts,
                        'market_name' => $mercado->mercado,
                        'assigned_count' => $assignedCount,
                        'user_note' => $validated['note'] ?? null // Incluir la nota del usuario
                    ], Auth::user());
                } catch (\Exception $e) {
                    // Error en notificación pero no interrumpir el flujo
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
