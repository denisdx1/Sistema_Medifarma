<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Market;
use App\Models\Brand;
use App\Models\Franchise;
use App\Models\BusinessUnit;
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
        $filters = $request->only(['search', 'market_status', 'brand_id', 'franchise_id', 'business_unit_id']);
        
        // Get products with their relationships
        $products = $this->marketService->getFilteredProducts($filters, 15);
        
        // Get filter options
        $filterOptions = $this->marketService->getFilterOptions();
        
        // Get market assignment statistics
        $stats = $this->marketService->getMarketStatistics();

        return view('market-configuration.index', compact(
            'products',
            'filterOptions',
            'stats',
            'filters'
        ));
    }

    /**
     * Show available markets for assignment
     */
    public function showAvailableMarkets(Request $request)
    {
        $search = $request->get('search', '') ?? '';
        $markets = $this->marketService->getAvailableMarkets($search);
        
        \Log::info('Available markets API called', [
            'search' => $search,
            'markets_count' => $markets->count(),
            'markets' => $markets->toArray()
        ]);
        
        return response()->json([
            'markets' => $markets
        ]);
    }

    /**
     * Assign market to product
     */
    public function assignMarket(Request $request, Product $product)
    {
        $request->validate([
            'market_id' => 'required|exists:markets,id'
        ]);

        try {
            $this->marketService->assignMarketToProduct($product, $request->market_id, Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Mercado asignado correctamente',
                'product' => $product->load('market')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove market assignment from product
     */
    public function removeMarket(Product $product)
    {
        try {
            $this->marketService->removeMarketFromProduct($product, Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Mercado removido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al remover mercado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show form to create new market
     */
    public function createMarket()
    {
        return view('market-configuration.create-market');
    }

    /**
     * Store new market
     */
    public function storeMarket(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:markets,name',
            'description' => 'nullable|string|max:500',
            'code' => 'required|string|max:10|unique:markets,code',
            'is_active' => 'boolean'
        ]);

        try {
            $market = $this->marketService->createMarket($request->all(), Auth::id());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mercado creado correctamente',
                    'market' => $market
                ]);
            }
            
            return redirect()->route('market-configuration.index')
                ->with('success', 'Mercado creado correctamente');
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear mercado: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Error al crear mercado: ' . $e->getMessage()]);
        }
    }

    /**
     * Show market details with assigned products
     */
    public function showMarket(Market $market)
    {
        $products = $market->products()
            ->with(['brand', 'franchise', 'businessUnit'])
            ->paginate(15);
            
        return view('market-configuration.show-market', compact('market', 'products'));
    }

    /**
     * Bulk assign markets to multiple products
     */
    public function bulkAssignMarket(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'market_id' => 'required|exists:markets,id'
        ]);

        try {
            $result = $this->marketService->bulkAssignMarket(
                $request->product_ids, 
                $request->market_id, 
                Auth::id()
            );
            
            return response()->json([
                'success' => true,
                'message' => "Se asignaron {$result['assigned']} productos al mercado correctamente",
                'assigned_count' => $result['assigned'],
                'total_count' => $result['total']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en asignación masiva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products without market assignment
     */
    public function exportUnassigned()
    {
        try {
            return $this->marketService->exportUnassignedProducts();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al exportar: ' . $e->getMessage()]);
        }
    }
}
