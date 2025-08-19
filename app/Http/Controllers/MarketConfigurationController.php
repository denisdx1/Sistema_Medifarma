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
     * Create a new market (stores it by creating a dummy entry in materials table)
     */
    public function createMarket(Request $request)
    {
        $request->validate([
            'market_name' => 'required|string|max:255'
        ]);

        try {
            $marketName = strtoupper(trim($request->market_name));
            
            // Verificar si el mercado ya existe en la tabla de materiales
            $existingMarket = Material::where('Mercado', 'like', $marketName)->first();
            
            if ($existingMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un mercado con ese nombre'
                ], 422);
            }

            // Crear una entrada "placeholder" en la tabla de materiales para el nuevo mercado
            // Esto permite que el mercado aparezca en las listas sin tener que asignarlo inmediatamente
            $uniqueSku = 'MARKET_' . strtoupper(str_replace(' ', '_', $marketName)) . '_' . time();
            
            Material::create([
                'SKU' => $uniqueSku,
                'Descripción_Presentación' => 'MERCADO CREADO: ' . $marketName,
                'Mercado' => $marketName,
                'Marca_Genérico' => 'SISTEMA',
                'Ético_Popular' => 'SISTEMA',
                'Código_ATC_4' => 'SYS',
                'Descripción_ATC_4' => 'Entrada del sistema para mercado',
                'Código_FF_3' => 'SYS',
                'Descripción_FF_3' => 'Sistema',
                'Molécula' => 'N/A',
                'LABORATORIO_C' => 'SISTEMA',
                'CORPORACION' => 'SISTEMA'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Mercado "' . $marketName . '" creado correctamente',
                'market_name' => $marketName
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
