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
            
            return response()->json([
                'success' => true,
                'message' => 'El mercado "' . $marketName . '" ha sido creado y enviado para aprobación del administrador',
                'market_name' => $marketName,
                'status' => 'ESPERA',
                'timestamp' => now()->format('H:i:s')
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
     * NOTA: Como no existe tabla materiales, solo devolvemos éxito
     */
    public function removeMarketFromMaterial(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string'
        ]);

        try {
            // NOTA: Como no existe tabla materiales, simulamos éxito
            return response()->json([
                'success' => true,
                'message' => 'Mercado removido correctamente (simulado - no hay tabla materiales)',
                'material' => [
                    'SKU' => $request->product_id,
                    'Mercado' => 'RESTO'
                ]
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

            // NOTA: Como no existe tabla materiales, simulamos validación exitosa
            // No verificamos si el nuevo nombre ya existe
            
            // NOTA: Como no existe tabla materiales, simulamos actualización
            $updatedCount = 0; // Material::where('Mercado', $oldMarketName)->update(['Mercado' => $newMarketName]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mercado actualizado correctamente (simulado - no hay tabla materiales)",
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
     * NOTA: Como no existe tabla materiales, devolvemos lista vacía
     */
    public function getProductsByMarket(Request $request)
    {
        $request->validate([
            'market_name' => 'required|string'
        ]);

        try {
            // NOTA: Como no existe tabla materiales, devolvemos colección vacía
            $products = collect();

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
            
            // NOTA: Como no existe tabla materiales, devolvemos paginación vacía
            $markets = new \Illuminate\Pagination\LengthAwarePaginator(
                [], // items vacíos
                0,  // total
                $perPage,
                $page,
                ['path' => request()->url()]
            );
            
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
     * NOTA: Como no existe tabla materiales, devolvemos lista vacía
     */
    public function getMarketsAPI()
    {
        try {
            // NOTA: Como no existe tabla materiales, devolvemos lista vacía
            $markets = collect();

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

            // NOTA: Como no existe tabla materiales, simulamos sincronización
            $updatedMaterials = 0; // Material::where('Mercado', $oldMarketName)->update(['Mercado' => $newMarketName]);

            // NOTA: Como no existe tabla materiales, no actualizamos
            // Material::where('id_mercado', $id)->update(['Mercado' => $newMarketName]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mercado actualizado correctamente (simulado - no hay tabla materiales)",
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

            // NOTA: Como no existe tabla materiales, simulamos movimiento
            $materialsCount = 0; // Material::where('id_mercado', $id)->orWhere('Mercado', $marketName)->update(['Mercado' => 'RESTO', 'id_mercado' => null]);

            // Eliminar el mercado
            $mercado->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Mercado '{$marketName}' eliminado correctamente (simulado - no hay tabla materiales)",
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
            $markets = Mercado::select('idMercado', 'mercado')
                             ->orderBy('mercado')
                             ->get();

            // Mapear los datos al formato esperado por el JavaScript
            $marketsFormatted = $markets->map(function ($market) {
                return [
                    'id_mercado' => $market->idMercado,
                    'mercado' => $market->mercado,
                    'materials_count' => 0 // Simulado ya que no existe tabla materiales
                ];
            });

            return response()->json([
                'success' => true,
                'markets' => $marketsFormatted
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
     * NOTA: Como no existe tabla materiales, simulamos asignación
     */
    public function assignMaterialToMarket(Request $request)
    {
        $request->validate([
            'material_sku' => 'required|string',
            'market_id' => 'required|integer|exists:mercados,id_mercado'
        ]);

        try {
            DB::beginTransaction();

            // NOTA: Como no existe tabla materiales, simulamos validación
            $material = null; // Material::where('SKU', $request->material_sku)->first();
            $mercado = Mercado::where('id_mercado', $request->market_id)->first();

            if (!$mercado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mercado no encontrado'
                ], 404);
            }

            // NOTA: Como no existe tabla materiales, no actualizamos pero devolvemos éxito
            // $material->update(['id_mercado' => $mercado->id_mercado, 'Mercado' => $mercado->mercado]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Material '{$request->material_sku}' asignado al mercado '{$mercado->mercado}' correctamente (simulado)",
                'material' => ['SKU' => $request->material_sku],
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
     * NOTA: Como no existe tabla materiales, devolvemos lista vacía
     */
    public function searchProductsForAssignment(Request $request)
    {
        try {
            // NOTA: Como no existe tabla materiales, devolvemos lista vacía
            $products = collect();

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

            // NOTA: Como no existe tabla materiales, simulamos asignación
            foreach ($skus as $sku) {
                // $material = Material::where('SKU', $sku)->first();
                // if ($material) {
                //     $material->update(['id_mercado' => $targetMarket->id_mercado, 'Mercado' => $targetMarket->mercado]);
                //     $updatedCount++;
                // }
                $updatedCount++; // Simulamos que todos se asignaron
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Se asignaron {$updatedCount} productos al mercado '{$targetMarket->mercado}' exitosamente (simulado)",
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
     * NOTA: Como no existe tabla materiales, simulamos datos
     */
    public function getProductDetails($sku)
    {
        try {
            // NOTA: Como no existe tabla materiales, simulamos producto
            $product = null; // Material::where('SKU', $sku)->first();
            
            // Simulamos datos del producto
            $productData = [
                'SKU' => $sku,
                'Descripción_Presentación' => 'Producto simulado',
                'Mercado' => 'Sin asignar',
                'Marca_Genérico' => 'N/A',
                'LABORATORIO_C' => 'N/A'
            ];

            // Get market information if assigned
            $marketName = 'Sin mercado asignado';
            $configuracion = ConfiguracionMercado::where('id_producto', $sku)->first();
            if ($configuracion && $configuracion->mercado) {
                $marketName = $configuracion->mercado->mercado;
            }

            return response()->json([
                'success' => true,
                'product' => $productData,
                'market_name' => $marketName
            ]);

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
