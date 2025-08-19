<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Mercado;
use App\Models\ConfiguracionMercado;
use App\Services\MarketConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketConfigurationController extends Controller
{
    protected $marketService;

    public function __construct(MarketConfigurationService $marketService)
    {
        $this->marketService = $marketService;
    }

    /**
     * Display the market configuration page
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'market_status', 'marca_generico', 'etico_popular', 'codigo_atc', 'codigo_ff', 'mercado', 'laboratorio', 'corporacion']);
        
        // Get materials with their relationships
        $products = $this->marketService->getFilteredProducts($filters, 10);
        // Check if user has permission to view
        if (!Auth::user()->isAdmin() && !Auth::user()->isProductManager() && !Auth::user()->isBusinessIntelligence()) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
        
        // Get filter options
        $filterOptions = $this->marketService->getFilterOptions();
        
        // Get material statistics
        $stats = $this->marketService->getMarketStatistics();
        
        // Pass user role to view
        $userRole = Auth::user()->role;

        return view('market-configuration.index', compact(
            'products',
            'filterOptions',
            'stats',
            'filters',
            'userRole'
        ));
    }

    /**
     * Create a new market (stores it in the mercados table)
     */
    public function createMarket(Request $request)
    {
        $request->validate([
            'market_name' => 'required|string|max:255|unique:mercados,mercado'
        ], [
            'market_name.unique' => 'Ya existe un mercado con ese nombre'
        ]);

        try {
            DB::beginTransaction();
            
            $marketName = trim($request->market_name);
            
            // Crear el mercado en la tabla mercados
            $mercado = Mercado::create([
                'mercado' => $marketName,
                'fecha_registro' => now(),
                'fecha_update' => now(),
                'estado' => true,
                'id_usuario' => Auth::id()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'El mercado "' . $marketName . '" ha sido creado exitosamente y está listo para usar',
                'market_name' => $marketName,
                'market_id' => $mercado->id_mercado,
                'timestamp' => now()->format('H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all unique markets from materials table
     */
    public function getMarkets()
    {
        try {
            $markets = $this->marketService->getAllAvailableMarkets();

            return response()->json([
                'success' => true,
                'markets' => $markets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove market from material
     */
    public function removeMarketFromMaterial(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string'
        ]);

        try {
            $material = Material::where('SKU', $request->product_id)->first();
            
            if (!$material) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material no encontrado'
                ], 404);
            }

            $material->Mercado = 'RESTO';
            $material->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Mercado removido correctamente, producto asignado a RESTO',
                'material' => $material
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al remover mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit/rename a market name across all materials
     */
    public function editMarketName(Request $request)
    {
        $request->validate([
            'old_market_name' => 'required|string',
            'new_market_name' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $oldMarketName = trim($request->old_market_name);
            $newMarketName = trim($request->new_market_name);

            // Verificar si el nuevo nombre ya existe (excepto el actual)
            $existingMarket = Material::where('Mercado', 'like', $newMarketName)
                                    ->where('Mercado', '!=', $oldMarketName)
                                    ->first();
            
            if ($existingMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un mercado con ese nombre'
                ], 422);
            }

            // Actualizar todos los materiales que tengan el mercado antiguo
            $updatedCount = Material::where('Mercado', $oldMarketName)
                                  ->update(['Mercado' => $newMarketName]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mercado actualizado correctamente. {$updatedCount} productos afectados",
                'updated_count' => $updatedCount,
                'old_name' => $oldMarketName,
                'new_name' => $newMarketName
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al editar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by market for bulk operations
     */
    public function getProductsByMarket(Request $request)
    {
        $request->validate([
            'market_name' => 'required|string'
        ]);

        try {
            $marketName = trim($request->market_name);
            
            $products = Material::where('Mercado', $marketName)
                               ->select('SKU', 'Descripción_Presentación', 'Mercado')
                               ->get();

            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => $products->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get markets with pagination and search
     */
    public function getMarketsPaginated(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            
            // Get unique markets from materials table
            $query = Material::select('Mercado')
                           ->whereNotNull('Mercado')
                           ->where('Mercado', '!=', '')
                           ->where('Mercado', '!=', 'N/A');
            
            // Apply search filter
            if (!empty($search)) {
                $query->where('Mercado', 'like', '%' . $search . '%');
            }
            
            // Get markets with product counts
            $markets = $query->selectRaw('Mercado, COUNT(*) as product_count')
                           ->groupBy('Mercado')
                           ->orderBy('Mercado')
                           ->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'markets' => $markets->items(),
                'pagination' => [
                    'current_page' => $markets->currentPage(),
                    'last_page' => $markets->lastPage(),
                    'per_page' => $markets->perPage(),
                    'total' => $markets->total(),
                    'from' => $markets->firstItem(),
                    'to' => $markets->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get markets for API (bulk assignment modal)
     */
    public function getMarketsAPI()
    {
        try {
            $markets = Material::select('Mercado as name')
                              ->whereNotNull('Mercado')
                              ->where('Mercado', '!=', '')
                              ->where('Mercado', '!=', 'N/A')
                              ->distinct()
                              ->orderBy('Mercado')
                              ->get()
                              ->map(function($market, $index) {
                                  return [
                                      'id' => $index + 1, // Usar index como ID temporal
                                      'name' => $market->name
                                  ];
                              });

            return response()->json($markets);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update market name from mercados table and sync with materiales
     */
    public function updateMarket(Request $request, $id)
    {
        $request->validate([
            'market_name' => 'required|string|max:255|unique:mercados,mercado,' . $id . ',id_mercado'
        ], [
            'market_name.unique' => 'Ya existe un mercado con ese nombre'
        ]);

        try {
            DB::beginTransaction();

            $mercado = Mercado::where('id_mercado', $id)->first();
            
            if (!$mercado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mercado no encontrado'
                ], 404);
            }

            $oldMarketName = $mercado->mercado;
            $newMarketName = trim($request->market_name);

            // Actualizar el mercado en la tabla mercados
            $mercado->update([
                'mercado' => $newMarketName,
                'fecha_update' => now(),
                'id_usuario' => Auth::id()
            ]);

            // Sincronizar con la tabla materiales - actualizar todos los materiales que tengan este mercado
            $updatedMaterials = Material::where('Mercado', $oldMarketName)
                                      ->update(['Mercado' => $newMarketName]);

            // Actualizar materiales que tengan el id_mercado asignado
            Material::where('id_mercado', $id)
                    ->update(['Mercado' => $newMarketName]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mercado actualizado correctamente. {$updatedMaterials} materiales sincronizados",
                'old_name' => $oldMarketName,
                'new_name' => $newMarketName,
                'materials_updated' => $updatedMaterials
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
     * Delete a market from mercados table and handle materials
     */
    public function deleteMarket($id)
    {
        try {
            DB::beginTransaction();

            $mercado = Mercado::where('id_mercado', $id)->first();
            
            if (!$mercado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mercado no encontrado'
                ], 404);
            }

            $marketName = $mercado->mercado;

            // Mover todos los materiales de este mercado a "RESTO"
            $materialsCount = Material::where('id_mercado', $id)
                                    ->orWhere('Mercado', $marketName)
                                    ->update([
                                        'Mercado' => 'RESTO',
                                        'id_mercado' => null
                                    ]);

            // Eliminar el mercado
            $mercado->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mercado '{$marketName}' eliminado correctamente. {$materialsCount} materiales movidos a RESTO",
                'materials_moved' => $materialsCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all markets from mercados table
     */
    public function getAllMarkets()
    {
        try {
            $markets = Mercado::select('id_mercado', 'mercado', 'estado', 'fecha_registro', 'fecha_update')
                             ->with('usuario:id,name')
                             ->orderBy('mercado')
                             ->get();

            // Agregar conteo de materiales por mercado
            $marketsWithCount = $markets->map(function ($market) {
                $materialsCount = Material::where('id_mercado', $market->id_mercado)
                                        ->orWhere('Mercado', $market->mercado)
                                        ->count();
                
                $market->materials_count = $materialsCount;
                return $market;
            });

            return response()->json([
                'success' => true,
                'markets' => $marketsWithCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign material to market
     */
    public function assignMaterialToMarket(Request $request)
    {
        $request->validate([
            'material_sku' => 'required|string',
            'market_id' => 'required|integer|exists:mercados,id_mercado'
        ]);

        try {
            DB::beginTransaction();

            $material = Material::where('SKU', $request->material_sku)->first();
            $mercado = Mercado::where('id_mercado', $request->market_id)->first();

            if (!$material) {
                return response()->json([
                    'success' => false,
                    'message' => 'Material no encontrado'
                ], 404);
            }

            if (!$mercado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mercado no encontrado'
                ], 404);
            }

            // Actualizar el material
            $material->update([
                'id_mercado' => $mercado->id_mercado,
                'Mercado' => $mercado->mercado
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Material '{$material->Descripción_Presentación}' asignado al mercado '{$mercado->mercado}' correctamente",
                'material' => $material,
                'market' => $mercado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar material al mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products for bulk assignment
     */
    public function searchProductsForAssignment(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $currentMarket = $request->get('current_market', '');
            
            $query = Material::select('SKU', 'Descripción_Presentación', 'Mercado', 'Marca_Genérico', 'LABORATORIO_C')
                            ->distinct();

            // Search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('SKU', 'like', "%{$search}%")
                      ->orWhere('Descripción_Presentación', 'like', "%{$search}%")
                      ->orWhere('Molécula', 'like', "%{$search}%")
                      ->orWhere('LABORATORIO_C', 'like', "%{$search}%");
                });
            }

            // Current market filter
            if (!empty($currentMarket)) {
                if ($currentMarket === 'RESTO') {
                    $query->where(function($q) {
                        $q->where('Mercado', '=', 'RESTO')
                          ->orWhereNull('Mercado')
                          ->orWhere('Mercado', '=', '');
                    });
                } else {
                    $query->where('Mercado', '=', $currentMarket);
                }
            }

            $products = $query->orderBy('SKU')
                             ->limit(100) // Limit results for performance
                             ->get();

            return response()->json([
                'success' => true,
                'products' => $products,
                'total' => $products->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign markets to multiple materials
     */
    public function bulkAssignMarkets(Request $request)
    {
        $request->validate([
            'target_market_id' => 'required|integer|exists:mercados,id_mercado',
            'product_skus' => 'required|array|min:1',
            'product_skus.*' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $targetMarket = Mercado::where('id_mercado', $request->target_market_id)->first();
            
            if (!$targetMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mercado de destino no encontrado'
                ], 404);
            }

            $skus = $request->product_skus;
            $updatedCount = 0;

            foreach ($skus as $sku) {
                $material = Material::where('SKU', $sku)->first();
                if ($material) {
                    $material->update([
                        'id_mercado' => $targetMarket->id_mercado,
                        'Mercado' => $targetMarket->mercado
                    ]);
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se asignaron {$updatedCount} productos al mercado '{$targetMarket->mercado}' exitosamente",
                'updated_count' => $updatedCount,
                'market_name' => $targetMarket->mercado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details by SKU
     */
    public function getProductDetails($sku)
    {
        try {
            $product = Material::where('SKU', $sku)->first();
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            // Get market information if assigned
            $marketName = 'Sin mercado asignado';
            $configuracion = ConfiguracionMercado::where('material_id', $product->id)->first();
            if ($configuracion && $configuracion->mercado) {
                $marketName = $configuracion->mercado->name;
            }

            return response()->json([
                'success' => true,
                'product' => [
                    'SKU' => $product->SKU,
                    'Descripción_Presentación' => $product->Descripción_Presentación,
                    'Marca_Genérico' => $product->Marca_Genérico,
                    'LABORATORIO_C' => $product->LABORATORIO_C,
                    'CORPORACION' => $product->CORPORACION,
                    'market_name' => $marketName
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting product details', [
                'sku' => $sku,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles del producto'
            ], 500);
        }
    }
}
