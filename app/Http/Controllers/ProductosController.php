<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Services\NotificationService;

class ProductosController extends Controller
{
    /**
     * ========================================
     * CONSTANTES PARA VALIDACIONES
     * ========================================
     */
    
    private const VALIDATION_RULES = [
        'codigo_presentacion' => 'required|string',
        'note_required' => 'required|string|max:1000',
        'market_id' => 'required|integer',
        'products_array' => 'required|array|min:1',
        'product_code' => 'required|string',
        'product_fuente' => 'required|string|min:1',
        'market_name' => 'required|string|max:255',
        'market_note' => 'required|string|max:1000',
        'product_ids' => 'required|array|min:1',
        'product_ids_item' => 'required|string',
        'note_min' => 'required|string|min:3'
    ];

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
                'mercado' => trim($request->get('filter_mercado', '')),
                'Concentracion' => trim($request->get('filter_Concentracion', '')),
                'Volumen' => trim($request->get('filter_Volumen', ''))
            ]);

            // Obtener parámetros de ordenamiento
            $sortField = $request->get('sort_field');
            $sortDirection = $request->get('sort_direction', 'asc');

            // Query base con JOIN para obtener la fuente y mercado real de configuración
            // Incluir productos con y sin configuración de todas las fuentes
            // Usar TAB_PRODUCTO como base pero hacer JOIN con VMAE_PROD_IQVIA para campos específicos
            $baseQuery = $this->getBaseProductQuery()
                ->select([
                    'p.codigoPresentacion',
                    'p.descripcionPresentacion',
                    'p.descripcionProducto',
                    'p.marcaGenerico',
                    'p.eticoPopular',
                    'p.molecula',
                    DB::raw("COALESCE(c.fuente, p.fuente) as fuente"),
                    'p.codigoFF3',
                    'p.descripcionFF3',
                    'p.codigoATC4',
                    'p.descripcionATC4',
                    'p.descripcionLaboratorio',
                    'p.descripcionCorporacion',
                    'p.sizePack',
                    'p.stghVal',
                    'v.Concentracion',
                    'v.Volumen',
                    // Usar el mercado real de la configuración, si no hay configuración usar "NUEVOS"
                    DB::raw("COALESCE(m.mercado, 'NUEVOS') as mercado")
                ]);

            // Aplicar búsqueda global
            if (!empty($search)) {
                $baseQuery->where(function($query) use ($search) {
                    $query->where('p.codigoPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('p.descripcionPresentacion', 'LIKE', "%{$search}%")
                          ->orWhere('p.descripcionProducto', 'LIKE', "%{$search}%")
                          ->orWhere('p.molecula', 'LIKE', "%{$search}%")
                          ->orWhere('m.mercado', 'LIKE', "%{$search}%")
                          ->orWhereRaw("COALESCE(m.mercado, 'NUEVOS') LIKE ?", ["%{$search}%"]);
                });
            }

            // Aplicar filtros individuales
            foreach ($filters as $field => $value) {
                if (!empty($value)) {
                    switch ($field) {
                        case 'fuente':
                            // Para fuente, usar la configuración si existe, sino usar IQVIA por defecto
                            if ($value === 'IQVIA') {
                                $baseQuery->where(function($query) use ($value) {
                                    $query->where('c.fuente', '=', $value)
                                          ->orWhereNull('c.fuente');
                                });
                            } else {
                                $baseQuery->where('c.fuente', '=', $value);
                            }
                            break;
                        case 'mercado':
                            // Verificar si hay múltiples mercados separados por comas
                            if (strpos($value, ',') !== false) {
                                $mercados = array_map('trim', explode(',', $value));
                                $baseQuery->whereIn('m.mercado', $mercados);
                                
                                // Si son los mercados especiales (NUEVOS y SIN_ASIGNAR), filtrar por Gerente_Producto del usuario
                                if (in_array('NUEVOS', $mercados) || in_array('SIN_ASIGNAR', $mercados)) {
                                    $user = Auth::user();
                                    if ($user) {
                                        \Log::info('Usuario autenticado: ' . $user->usuario . ' - Aplicando filtro por Gerente_Producto para mercados: ' . implode(', ', $mercados));
                                        $baseQuery->where('v.Gerente_Producto', '=', $user->usuario);
                                    } else {
                                        \Log::warning('No hay usuario autenticado para aplicar filtro por Gerente_Producto');
                                    }
                                }
                            } else {
                                // Si el filtro es "NUEVOS", buscar productos sin configuración
                                if ($value === 'NUEVOS') {
                                    $baseQuery->whereNull('m.mercado');
                                } else {
                                    $baseQuery->where('m.mercado', '=', $value);
                                }
                            }
                            break;
                        case 'descripcionProducto':
                            // Solo este campo mantiene búsqueda parcial
                            // $baseQuery->where('p.descripcionProducto', 'LIKE', "%{$value}%");
                            $baseQuery->where('p.descripcionProducto', '=', $value);
                            break;
                        case 'descripcionFF3':
                            // Verificar si el valor contiene un guión (formato código - descripción)
                            if (strpos($value, ' - ') !== false) {
                                list($codigo, $descripcion) = explode(' - ', $value, 2);
                                $baseQuery->where(function($query) use ($codigo, $descripcion) {
                                    $query->whereRaw('UPPER(LTRIM(RTRIM(p.codigoFF3))) = ?', [strtoupper(trim($codigo))])
                                          ->whereRaw('UPPER(LTRIM(RTRIM(p.descripcionFF3))) = ?', [strtoupper(trim($descripcion))]);
                                });
                            } else {
                                // Buscar por descripción (para productos sin código como CLOSEUP)
                                // o por código si existe
                                $baseQuery->where(function($query) use ($value) {
                                    $query->where('p.descripcionFF3', '=', $value)
                                          ->orWhere('p.codigoFF3', '=', $value);
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
                                    $query->whereRaw('UPPER(LTRIM(RTRIM(p.codigoATC4))) = ?', [strtoupper($codigo)])
                                          ->whereRaw('UPPER(LTRIM(RTRIM(p.descripcionATC4))) = ?', [strtoupper($descripcion)]);
                                });
                            } else {
                                // Buscar SOLO por código ATC4 para incluir productos de todas las fuentes
                                // Esto es especialmente importante para productos CLOSEUP que no tienen descripción
                                $baseQuery->where('p.codigoATC4', '=', $value);
                            }
                            break;
                        case 'descripcionLaboratorio':
                            $baseQuery->where('p.descripcionLaboratorio', '=', $value);
                            break;
                        case 'molecula':
                            $baseQuery->where('p.molecula', '=', $value);
                            break;
                        case 'descripcionCorporacion':
                            $baseQuery->where('p.descripcionCorporacion', '=', $value);
                            break;
                        case 'marcaGenerico':
                            $baseQuery->where('p.marcaGenerico', '=', $value);
                            break;
                        case 'eticoPopular':
                            $baseQuery->where('p.eticoPopular', '=', $value);
                            break;
                        case 'Concentracion':
                            // Buscar en el campo de concentración directamente
                            $baseQuery->where('v.Concentracion', 'LIKE', "%{$value}%");
                            break;
                        case 'Volumen':
                            // Buscar en el campo de volumen directamente
                            $baseQuery->where('v.Volumen', 'LIKE', "%{$value}%");
                            break;
                    }
                }
            }



            // Aplicar ordenamiento dinámico
            if ($sortField) {
                // Mapear campos de ordenamiento a las columnas correctas
                $sortMapping = [
                    'descripcionPresentacion' => 'p.descripcionPresentacion',
                    'descripcionProducto' => 'p.descripcionProducto',
                    'marcaGenerico' => 'p.marcaGenerico',
                    'eticoPopular' => 'p.eticoPopular',
                    'molecula' => 'p.molecula',
                    'descripcionFF3' => 'p.descripcionFF3',
                    'descripcionATC4' => 'p.descripcionATC4',
                    'descripcionLaboratorio' => 'p.descripcionLaboratorio',
                    'descripcionCorporacion' => 'p.descripcionCorporacion',
                    'sizePack' => 'p.sizePack',
                    'Concentracion' => 'v.Concentracion',
                    'Volumen' => 'v.Volumen',
                    'mercado' => 'COALESCE(m.mercado, \'NUEVOS\')', // Campo calculado con COALESCE
                    'fuente' => 'fuente' // Campo calculado
                ];

                if (isset($sortMapping[$sortField])) {
                    $baseQuery->orderBy($sortMapping[$sortField], $sortDirection);
                } else {
                    // Ordenamiento por defecto si el campo no es válido
                    $baseQuery->orderBy('p.codigoPresentacion', 'asc');
                }
            } else {
                // Ordenamiento por defecto
                $baseQuery->orderBy('p.codigoPresentacion', 'asc');
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
            
            // Base query - solo agregar JOIN cuando sea necesario
            $baseQuery = $this->getSimpleProductQuery();

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
                        ->where('descripcionFF3', '<>', '');
                    
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
                            // Si no hay código, solo mostrar la descripción
                            if (empty($item->codigoFF3) || $item->codigoFF3 === '') {
                                return $item->descripcionFF3;
                            }
                            // Si hay código, mostrar código - descripción
                            return $item->codigoFF3 . ' - ' . $item->descripcionFF3;
                        })
                        ->toArray();
                    break;

                case 'descripcionATC4':
                    $query = (clone $baseQuery)
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
                            // Si no hay descripción, solo mostrar el código
                            if (empty($item->descripcionATC4) || $item->descripcionATC4 === '') {
                                return $item->codigoATC4;
                            }
                            // Si hay descripción, mostrar código - descripción
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
                    // Para mercado, incluir tanto los de configuración como "NUEVOS"
                    if ($search && strtolower($search) === 'nuevos') {
                        // Si buscan específicamente "NUEVOS", buscar productos sin configuración
                        $query = $this->getSimpleProductQuery()
                            ->leftJoin('ODS.TAB_CONFIGURACION as c', 'p.codigoPresentacion', '=', 'c.codigo')
                            ->whereNull('c.codigo') // Productos sin configuración
                            ->select(DB::raw("'NUEVOS' as mercado"));
                    } else {
                        // Para otros mercados, usar la configuración normal
                        $query = $this->getBaseProductQuery()
                            ->whereNotNull('m.mercado')
                            ->where('m.mercado', '<>', '')
                            ->select('m.mercado as mercado');
                        
                        if ($search) {
                            $query->where('m.mercado', 'LIKE', "{$search}%");
                        }
                    }
                    
                    // Para mercado, usar un límite mucho mayor para asegurar que se carguen todos
                    $mercadoLimit = $search ? $limit : 2000; // Sin búsqueda: 2000, con búsqueda: límite normal
                    
                    $results = $query->distinct()
                        ->orderBy('mercado')
                        ->limit($mercadoLimit)
                        ->pluck('mercado')
                        ->filter(function($mercado) {
                            return !empty(trim($mercado));
                        })
                        ->unique()
                        ->values()
                        ->map(function($mercado) {
                            // Mostrar el nombre del mercado tal como está en la base de datos
                            return $mercado;
                        })
                        ->toArray();
                    
                    // Si no hay búsqueda, agregar "NUEVOS" a las opciones
                    if (!$search) {
                        $results[] = 'NUEVOS';
                        $results = array_unique($results);
                        sort($results);
                    }
                    
                    break;

                case 'fuente':
                    $fuenteQuery = $this->getBaseProductQuery()
                        ->select(DB::raw("COALESCE(c.fuente, p.fuente) as fuente"));
                    
                    if ($search) {
                        $fuenteQuery->havingRaw("COALESCE(c.fuente, p.fuente) LIKE ?", ["{$search}%"]);
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

                case 'Concentracion':
                    $query = $this->getBaseProductQuery()
                        ->whereNotNull('v.Concentracion')
                        ->where('v.Concentracion', '<>', '');
                    
                    if ($search) {
                        $query->where('v.Concentracion', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('v.Concentracion')
                        ->limit($limit)
                        ->pluck('v.Concentracion')
                        ->toArray();
                    break;

                case 'Volumen':
                    $query = $this->getBaseProductQuery()
                        ->whereNotNull('v.Volumen')
                        ->where('v.Volumen', '<>', '');
                    
                    if ($search) {
                        $query->where('v.Volumen', 'LIKE', "{$search}%");
                    }
                    
                    $results = $query->distinct()
                        ->orderBy('v.Volumen')
                        ->limit($limit)
                        ->pluck('v.Volumen')
                        ->toArray();
                    break;

                default:
                    // Si no se especifica tipo, devolver opciones básicas (pequeñas)
                    $results = [
                        'marcaGenerico' => (clone $baseQuery)->whereNotNull('marcaGenerico')->where('marcaGenerico', '<>', '')->distinct()->orderBy('marcaGenerico')->limit(20)->pluck('marcaGenerico')->toArray(),
                        'eticoPopular' => (clone $baseQuery)->whereNotNull('eticoPopular')->where('eticoPopular', '<>', '')->distinct()->orderBy('eticoPopular')->limit(20)->pluck('eticoPopular')->toArray(),
                        'fuente' => ['IQVIA', 'OTRO'] // Opciones fijas pequeñas
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
        $validated = $request->validate([
            'codigoPresentacion' => self::VALIDATION_RULES['codigo_presentacion'],
            'note' => self::VALIDATION_RULES['note_required']
        ]);

        try {
            // Aumentar el timeout para esta operación específica
            set_time_limit(60);
            
            return $this->executeWithTransaction(function() use ($validated) {
            // Verificar que el producto existe en la configuración actual y obtener sus datos
                $configuracionProducto = $this->getProductConfiguration($validated['codigoPresentacion']);
                
            if (!$configuracionProducto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no se encuentra en la configuración actual'
                ], 404);
            }

            // Obtener el nombre del producto ANTES del SP para evitar consultas post-SP
                $nombreProducto = $this->getProductInfo($validated['codigoPresentacion']);

            // Ejecutar el stored procedure SP_ASIGNAR_RESTO
            $userId = Auth::user()->idUsuario;
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_ASIGNAR_RESTO ?, ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $userId,                          // @idUsuario
                $validated['note']                // @nota
            ]);

            // Enviar notificación por email DESPUÉS del commit para no afectar la transacción
                $this->sendNotification(function($notificationService) use ($validated, $nombreProducto, $configuracionProducto) {
                $notificationService->notifyProductRemoved(
                    $validated['codigoPresentacion'],
                        $nombreProducto->descripcionPresentacion ?? 'Producto no encontrado',
                    $configuracionProducto->mercado,
                    Auth::user(),
                    $validated['note'] ?? null
                );
                });

            return response()->json([
                'success' => true,
                'message' => 'Producto removido del mercado exitosamente',
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'refresh_page' => true // Indicar que se debe refrescar la página
            ]);
            });

        } catch (\Exception $e) {
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
            'codigoPresentacion' => self::VALIDATION_RULES['codigo_presentacion'],
            'nuevoMercadoId' => self::VALIDATION_RULES['market_id'],
            'note' => self::VALIDATION_RULES['note_required']
        ]);

        try {
            return $this->executeWithTransaction(function() use ($validated) {
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
                $mercadoDestino = $this->getMarketValidationQuery($validated['nuevoMercadoId'])->first();
                
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
                $nombreProducto = $this->getProductInfo($validated['codigoPresentacion']);

            // Ejecutar el stored procedure ODS.SP_UPDATE_CONFIGURACION
            // Mantener la fuente original del producto
            $userId = Auth::user()->idUsuario;
            $fuenteOriginal = $configuracion->fuente ?? 'IQVIA'; // Usar la fuente original o IQVIA por defecto
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_CONFIGURACION ?, ?, ?, ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $fuenteOriginal,                   // @fuente - Mantener la fuente original del producto
                $validated['nuevoMercadoId'],     // @idMercado
                $userId,                          // @idUsuario
                $validated['note'] ?? null        // @nota
            ]);

            // Enviar notificación por email
                $this->sendNotification(function($notificationService) use ($validated, $nombreProducto, $mercadoAnterior, $mercadoDestino) {
                $notificationService->notifyProductMoved(
                    $validated['codigoPresentacion'],
                        $nombreProducto->descripcionPresentacion ?? 'Producto no encontrado',
                    $mercadoAnterior,
                    $mercadoDestino->mercado,
                    Auth::user(),
                    $validated['note'] ?? null
                );
                });

            return response()->json([
                'success' => true,
                'message' => "Producto cambiado exitosamente al mercado: {$mercadoDestino->mercado}",
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'mercadoAnterior' => $configuracion->idMercado,
                'mercadoNuevo' => $validated['nuevoMercadoId'],
                'nombreMercadoNuevo' => $mercadoDestino->mercado,
                'refresh_page' => true // Indicar que se debe refrescar la página
            ]);
            });

        } catch (\Exception $e) {
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
            
            // Query para obtener TODOS los mercados activos usando el método privado
            $marketsQuery = $this->getActiveMarketsQuery()
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                );

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
            'market_name' => self::VALIDATION_RULES['market_name'],
            'market_note' => self::VALIDATION_RULES['market_note']
        ]);

        try {
            return $this->executeWithTransaction(function() use ($request) {
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
                $existingMarket = $this->getActiveMarketsQuery()
                ->whereRaw('UPPER(LTRIM(RTRIM(m.mercado))) = UPPER(?)', [trim($marketName)])
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
                $this->sendNotification(function($notificationService) use ($marketName, $user, $marketNote) {
                $notificationService->notifyNewMarket($marketName, $user, $marketNote);
                });
            
            // Calcular la página donde aparecerá el nuevo mercado
                $totalMarkets = $this->getActiveMarketsQuery()
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
            });
        } catch (\Exception $e) {
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
            'idMercado' => self::VALIDATION_RULES['market_id'],
            'products' => self::VALIDATION_RULES['products_array'],
            'products.*.code' => self::VALIDATION_RULES['product_code'],
            'products.*.fuente' => self::VALIDATION_RULES['product_fuente'],
            'note' => self::VALIDATION_RULES['note_required']
        ]);

        try {
            return $this->executeWithTransaction(function() use ($validated) {
            // Verificar que el mercado existe y está activo
                $mercado = $this->getMarketValidationQuery($validated['idMercado'])->first();

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
                    // Verificar que el producto existe en TAB_PRODUCTO con mercado SIN_ASIGNAR o NUEVOS y obtener su información
                    // Primero verificar si está en SIN_ASIGNAR (con configuración)
                        $productoInfo = $this->getBaseProductQuery()
                        ->where('p.codigoPresentacion', $product['code'])
                        ->where('m.mercado', 'SIN_ASIGNAR')
                        ->select('p.codigoPresentacion', 'p.descripcionPresentacion')
                        ->first();
                    
                    // Si no está en SIN_ASIGNAR, verificar si está en NUEVOS (sin configuración)
                    if (!$productoInfo) {
                            $productoInfo = $this->getSimpleProductQuery()
                            ->leftJoin('ODS.TAB_CONFIGURACION as c', 'p.codigoPresentacion', '=', 'c.codigo')
                            ->where('p.codigoPresentacion', $product['code'])
                            ->whereNull('c.codigo') // Productos sin configuración
                            ->select('p.codigoPresentacion', 'p.descripcionPresentacion')
                            ->first();
                    }
                        
                    if (!$productoInfo) {
                        $errors[] = "Producto {$product['code']} no encontrado en SIN_ASIGNAR o NUEVOS";
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

                    // PASO 1: Eliminar la configuración existente del producto (que debería estar en SIN_ASIGNAR)
                    DB::connection('sqlsrv')
                        ->table('ODS.TAB_CONFIGURACION')
                        ->where('codigo', $product['code'])
                        ->delete();

                    // PASO 2: Ejecutar el stored procedure ODS.SP_INSERT_CONFIGURACION para el nuevo mercado
                    // Obtener el ID del usuario autenticado
                    $userId = Auth::user()->idUsuario;
                    
                    // Usar la fuente original del producto (IQVIA, CLOSEUP, etc.)
                    $fuente = $product['fuente'];
                    
                    DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_CONFIGURACION ?, ?, ?, ?, ?', [
                        $validated['idMercado'],  // @idMercado
                        $product['code'],         // @codigo
                        $fuente,                  // @fuente - Mantener la fuente original del producto
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
                    $this->sendNotification(function($notificationService) use ($assignedProducts, $mercado, $assignedCount, $validated) {
                    // Usar el método genérico para múltiples productos
                    $notificationService->notifyMarketAction('assign_product', [
                        'assigned_products' => $assignedProducts,
                        'market_name' => $mercado->mercado,
                        'assigned_count' => $assignedCount
                    ], Auth::user(), $validated['note'] ?? null);
                    });
            }

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
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quitar productos del mercado masivamente usando stored procedure SP_ASIGNAR_RESTO
     */
    public function bulkRemoveMarket(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_ids' => self::VALIDATION_RULES['product_ids'],
                'product_ids.*' => self::VALIDATION_RULES['product_ids_item'],
                'note' => self::VALIDATION_RULES['note_min']
            ]);

            $productIds = $validated['product_ids'];
            $note = $validated['note'];
            $userId = Auth::user()->idUsuario;

            return $this->executeWithTransaction(function() use ($productIds, $note, $userId) {
            $removedCount = 0;
            $errors = [];
            $removedProducts = [];

            foreach ($productIds as $productCode) {
                try {
                                    // Verificar que el producto tenga configuración (esté asignado a algún mercado)
                        $configuracionProducto = $this->getProductConfiguration($productCode);

                if (!$configuracionProducto) {
                    $errors[] = "Producto con código {$productCode} no tiene configuración de mercado asignada";
                    continue;
                }

                // Obtener información del producto desde TAB_PRODUCTO
                        $productoInfo = $this->getProductInfo($productCode);

                if (!$productoInfo) {
                    $errors[] = "Producto con código {$productCode} no encontrado en TAB_PRODUCTO";
                    continue;
                }

                // Usar el mercado de la configuración
                $mercadoActual = $configuracionProducto->mercado;

                // Log para debugging
                \Log::info('Verificando producto para quitar mercado:', [
                    'codigo' => $productCode,
                    'mercado_actual' => $mercadoActual,
                    'tiene_configuracion' => $configuracionProducto ? 'SI' : 'NO'
                ]);

                    // PASO 1: Ejecutar el stored procedure SP_ASIGNAR_RESTO
                    $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_ASIGNAR_RESTO ?, ?, ?', [
                        $productCode,  // @codigo
                        $userId,       // @idUsuario
                        $note          // @nota
                    ]);

                    if (!$executed) {
                        $errors[] = "Error ejecutando SP_ASIGNAR_RESTO para producto {$productCode}";
                        continue;
                    }

                        // PASO 2: Registrar en logs usando el método privado
                        $this->logMarketAction('DELETE', "Producto {$productCode} quitado del mercado {$mercadoActual}", $note);

                    $removedCount++;
                    $removedProducts[] = [
                        'code' => $productoInfo->codigoPresentacion,
                        'name' => $productoInfo->descripcionPresentacion,
                        'previous_market' => $mercadoActual
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error quitando producto {$productCode}: " . $e->getMessage();
                }
            }

            // Enviar notificación por email si se quitaron productos exitosamente
            if ($removedCount > 0) {
                    $this->sendNotification(function($notificationService) use ($removedProducts, $removedCount, $note) {
                    $notificationService->notifyMarketAction('remove_product', [
                        'removed_products' => $removedProducts,
                        'removed_count' => $removedCount
                    ], Auth::user(), $note);
                    });
            }

            $response = [
                'success' => true,
                'removed_count' => $removedCount,
                'total_requested' => count($productIds),
                'message' => "Se quitaron {$removedCount} productos del mercado correctamente"
            ];

            if (!empty($errors)) {
                $response['warnings'] = $errors;
            }

            return response()->json($response);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al quitar productos del mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar mercado de productos masivamente usando stored procedure SP_UPDATE_CONFIGURACION
     */
    public function bulkChangeMarket(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_ids' => self::VALIDATION_RULES['product_ids'],
                'product_ids.*' => self::VALIDATION_RULES['product_ids_item'],
                'market_id' => self::VALIDATION_RULES['market_id'],
                'note' => self::VALIDATION_RULES['note_min']
            ]);

            $productIds = $validated['product_ids'];
            $marketId = $validated['market_id'];
            $note = $validated['note'];
            $userId = Auth::user()->idUsuario;
            
            // Log para debugging
            \Log::info('bulkChangeMarket - Parámetros recibidos:', [
                'product_ids' => $productIds,
                'market_id' => $marketId,
                'market_id_type' => gettype($marketId),
                'note' => $note,
                'user_id' => $userId
            ]);

            // Obtener información del mercado destino
            $mercado = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $marketId)
                ->select('idMercado', 'mercado')
                ->first();

            if (!$mercado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mercado de destino no encontrado'
                ], 404);
            }

            return $this->executeWithTransaction(function() use ($productIds, $marketId, $note, $userId, $mercado) {

                $changedCount = 0;
                $errors = [];
                $changedProducts = [];

                foreach ($productIds as $productCode) {
                    try {
                        // Verificar que el producto tenga configuración y obtener información
                        $configuracionProducto = $this->getProductConfiguration($productCode);
                        $productoInfo = $this->getProductInfo($productCode);

                        if (!$productoInfo || !$configuracionProducto) {
                            $errors[] = "Producto con código {$productCode} no encontrado o no tiene configuración de mercado";
                            continue;
                        }

                        $previousMarket = $configuracionProducto->mercado;

                        // PASO 1: Obtener la fuente original del producto desde la configuración
                        $fuenteOriginal = $configuracionProducto->fuente ?? 'IQVIA'; // Usar la fuente original o IQVIA por defecto

                        // PASO 2: Ejecutar SP_UPDATE_CONFIGURACION para cambiar mercado
                        \Log::info('Ejecutando SP_UPDATE_CONFIGURACION para cambiar mercado:', [
                            'codigo' => $productCode,
                            'mercado_actual' => $previousMarket,
                            'mercado_destino' => $mercado->mercado,
                            'fuente' => $fuenteOriginal,
                            'idMercado' => $marketId,
                            'idUsuario' => $userId,
                            'nota' => $note
                        ]);
                        
                        $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_CONFIGURACION ?, ?, ?, ?, ?', [
                            $productCode,        // @codigo
                            $fuenteOriginal,     // @fuente - Mantener la fuente original del producto
                            $marketId,           // @idMercado
                            $userId,             // @idUsuario
                            $note                // @nota
                        ]);

                        if (!$executed) {
                            $errors[] = "Error ejecutando SP_UPDATE_CONFIGURACION para producto {$productCode}";
                            continue;
                        }

                        // PASO 3: Registrar en logs usando el método privado
                        $this->logMarketAction('UPDATE', "Producto {$productCode} cambiado de mercado {$previousMarket} a {$mercado->mercado}", $note);

                        $changedCount++;
                        $changedProducts[] = [
                            'code' => $productoInfo->codigoPresentacion,
                            'name' => $productoInfo->descripcionPresentacion,
                            'previous_market' => $previousMarket,
                            'new_market' => $mercado->mercado
                        ];

                    } catch (\Exception $e) {
                        $errors[] = "Error cambiando mercado del producto {$productCode}: " . $e->getMessage();
                        
                        // Log detallado del error
                        \Log::error('Error detallado en bulkChangeMarket:', [
                            'codigo' => $productCode,
                            'error_message' => $e->getMessage(),
                            'error_file' => $e->getFile(),
                            'error_line' => $e->getLine(),
                            'error_trace' => $e->getTraceAsString()
                        ]);
                    }
                }

                // Log para debugging - resumen final
                \Log::info('Resumen de bulkChangeMarket:', [
                    'total_solicitados' => count($productIds),
                    'total_cambiados' => $changedCount,
                    'total_errores' => count($errors),
                    'productos_cambiados' => $changedProducts
                ]);

                // Enviar notificación por email si se cambiaron productos exitosamente
                if ($changedCount > 0) {
                    $this->sendNotification(function($notificationService) use ($changedProducts, $mercado, $changedCount, $note) {
                        $notificationService->notifyMarketAction('change_market', [
                            'changed_products' => $changedProducts,
                            'new_market_name' => $mercado->mercado,
                            'changed_count' => $changedCount
                        ], Auth::user(), $note);
                    });
                }

                $response = [
                    'success' => true,
                    'changed_count' => $changedCount,
                    'total_requested' => count($productIds),
                    'new_market' => $mercado->mercado,
                    'message' => "Se cambiaron {$changedCount} productos al mercado {$mercado->mercado} correctamente"
                ];

                if (!empty($errors)) {
                    $response['warnings'] = $errors;
                }

                return response()->json($response);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar mercado de productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========================================
     * MÉTODOS PRIVADOS PARA REFACTORIZACIÓN
     * ========================================
     */

    /**
     * Obtiene la consulta base para productos con todos los JOINs necesarios
     */
    private function getBaseProductQuery()
    {
        return DB::connection('sqlsrv')
            ->table('ODS.TAB_PRODUCTO as p')
            ->leftJoin('ODS.TAB_CONFIGURACION as c', 'p.codigoPresentacion', '=', 'c.codigo')
            ->leftJoin('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
            ->leftJoin('dbo.VMAE_PROD_IQVIA as v', 'p.codigoPresentacion', '=', 'v.codigoPresentacion');
    }

    /**
     * Obtiene la consulta base para productos sin JOINs adicionales
     */
    private function getSimpleProductQuery()
    {
        return DB::connection('sqlsrv')
            ->table('ODS.TAB_PRODUCTO as p');
    }

    /**
     * Obtiene la consulta para mercados activos
     */
    private function getActiveMarketsQuery()
    {
        return DB::connection('sqlsrv')
            ->table('ODS.TAB_MERCADO as m')
            ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
            ->where('e.estado', 'ACTIVO');
    }

    /**
     * Obtiene la consulta para verificar si un mercado existe y está activo
     */
    private function getMarketValidationQuery($marketId)
    {
        return $this->getActiveMarketsQuery()
            ->select('m.idMercado', 'm.mercado', 'e.estado')
            ->where('m.idMercado', $marketId);
    }

    /**
     * Obtiene información de un producto específico
     */
    private function getProductInfo($codigoPresentacion)
    {
        return DB::connection('sqlsrv')
            ->table('ODS.TAB_PRODUCTO')
            ->where('codigoPresentacion', $codigoPresentacion)
            ->select('codigoPresentacion', 'descripcionPresentacion')
            ->first();
    }

    /**
     * Obtiene la configuración de un producto
     */
    private function getProductConfiguration($codigoPresentacion)
    {
        return DB::connection('sqlsrv')
            ->table('ODS.TAB_CONFIGURACION as c')
            ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
            ->select('c.*', 'm.mercado')
            ->where('c.codigo', $codigoPresentacion)
            ->first();
    }

    /**
     * Ejecuta una operación dentro de una transacción de base de datos
     * 
     * @param callable $callback Función a ejecutar dentro de la transacción
     * @return mixed Resultado de la función callback
     * @throws \Exception Si ocurre algún error durante la transacción
     */
    private function executeWithTransaction(callable $callback)
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Registra una acción en el log de auditoría del mercado
     * 
     * @param string $action Acción realizada (INSERT, UPDATE, DELETE)
     * @param string $detail Detalle de la acción
     * @param string|null $note Nota adicional
     * @return void
     */
    private function logMarketAction(string $action, string $detail, string $note = null)
    {
        DB::connection('sqlsrv')->table('ODS.TAB_MERCADO_LOG')->insert([
            'usuario' => Auth::user()->usuario,
            'accion' => $action,
            'fecha' => now(),
            'tabla' => 'ODS.TAB_CONFIGURACION',
            'detalle' => $detail,
            'nota' => $note
        ]);
    }

    /**
     * Envía una notificación de manera segura sin interrumpir el flujo principal
     * 
     * @param callable $notificationCallback Función que recibe el NotificationService
     * @return void
     */
    private function sendNotification(callable $notificationCallback)
    {
        try {
            $notificationService = new NotificationService();
            $notificationCallback($notificationService);
        } catch (\Exception $e) {
            // Error en notificación pero no interrumpir el flujo
            \Log::warning('Error enviando notificación: ' . $e->getMessage());
        }
    }
}
