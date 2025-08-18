<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Market;
use App\Models\Brand;
use App\Models\Franchise;
use App\Models\BusinessUnit;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketConfigurationService
{
    /**
     * Get filtered products with pagination
     */
    public function getFilteredProducts(array $filters, int $perPage = 15)
    {
        $query = Product::with(['brand', 'franchise', 'businessUnit', 'market']);

        // Global search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Market status filter
        if (!empty($filters['market_status'])) {
            switch ($filters['market_status']) {
                case 'with_market':
                    $query->whereNotNull('market_id');
                    break;
                case 'without_market':
                    $query->whereNull('market_id');
                    break;
            }
        }

        // Brand filter
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Franchise filter
        if (!empty($filters['franchise_id'])) {
            $query->where('franchise_id', $filters['franchise_id']);
        }

        // Business Unit filter
        if (!empty($filters['business_unit_id'])) {
            $query->where('business_unit_id', $filters['business_unit_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get filter options for dropdowns
     */
    public function getFilterOptions(): array
    {
        return [
            'brands' => Brand::orderBy('name')->get(),
            'franchises' => Franchise::orderBy('name')->get(),
            'businessUnits' => BusinessUnit::orderBy('name')->get(),
            'markets' => Market::where('is_active', true)->orderBy('name')->get()
        ];
    }

    /**
     * Get market assignment statistics
     */
    public function getMarketStatistics(): array
    {
        $totalProducts = Product::count();
        $productsWithMarket = Product::whereNotNull('market_id')->count();
        $productsWithoutMarket = $totalProducts - $productsWithMarket;
        $totalMarkets = Market::where('is_active', true)->count();

        return [
            'total_products' => $totalProducts,
            'products_with_market' => $productsWithMarket,
            'products_without_market' => $productsWithoutMarket,
            'total_markets' => $totalMarkets,
            'assignment_percentage' => $totalProducts > 0 ? round(($productsWithMarket / $totalProducts) * 100, 2) : 0
        ];
    }

    /**
     * Get available markets for assignment
     */
    public function getAvailableMarkets(?string $search = ''): \Illuminate\Database\Eloquent\Collection
    {
        $search = $search ?? '';
        $query = Market::where('is_active', true);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->take(20)->get();
    }

    /**
     * Assign market to a product
     */
    public function assignMarketToProduct(Product $product, int $marketId, int $userId): Product
    {
        DB::beginTransaction();
        
        try {
            $oldMarketId = $product->market_id;
            $market = Market::findOrFail($marketId);
            
            $product->update(['market_id' => $marketId]);
            
            // Log the action
            $this->logMarketAssignment($product, $oldMarketId, $marketId, $userId, 'assigned');
            
            DB::commit();
            
            return $product->load('market');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove market assignment from product
     */
    public function removeMarketFromProduct(Product $product, int $userId): Product
    {
        DB::beginTransaction();
        
        try {
            $oldMarketId = $product->market_id;
            
            $product->update(['market_id' => null]);
            
            // Log the action
            $this->logMarketAssignment($product, $oldMarketId, null, $userId, 'removed');
            
            DB::commit();
            
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create new market
     */
    public function createMarket(array $data, int $userId): Market
    {
        DB::beginTransaction();
        
        try {
            $market = Market::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'code' => strtoupper($data['code']),
                'is_active' => $data['is_active'] ?? true
            ]);
            
            // Log market creation
            Log::create([
                'user_id' => $userId,
                'action' => 'market_created',
                'model_type' => Market::class,
                'model_id' => $market->id,
                'new_value' => json_encode($market->toArray()),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            DB::commit();
            
            return $market;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk assign market to multiple products
     */
    public function bulkAssignMarket(array $productIds, int $marketId, int $userId): array
    {
        DB::beginTransaction();
        
        try {
            $market = Market::findOrFail($marketId);
            $products = Product::whereIn('id', $productIds)->get();
            
            $assignedCount = 0;
            
            foreach ($products as $product) {
                if ($product->market_id !== $marketId) {
                    $oldMarketId = $product->market_id;
                    $product->update(['market_id' => $marketId]);
                    
                    // Log each assignment
                    $this->logMarketAssignment($product, $oldMarketId, $marketId, $userId, 'bulk_assigned');
                    
                    $assignedCount++;
                }
            }
            
            DB::commit();
            
            return [
                'assigned' => $assignedCount,
                'total' => count($productIds)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Export products without market assignment
     */
    public function exportUnassignedProducts()
    {
        $products = Product::with(['brand', 'franchise', 'businessUnit'])
            ->whereNull('market_id')
            ->get();

        $csvData = [];
        $csvData[] = ['ID', 'Nombre', 'SKU', 'Marca', 'Franquicia', 'Unidad de Negocio', 'Descripción'];

        foreach ($products as $product) {
            $csvData[] = [
                $product->id,
                $product->name,
                $product->sku ?? 'Sin SKU',
                $product->brand->name ?? 'Sin Marca',
                $product->franchise->name ?? 'Sin Franquicia',
                $product->businessUnit->name ?? 'Sin Unidad',
                $product->description ?? ''
            ];
        }

        $filename = 'productos_sin_mercado_' . date('Y_m_d_H_i_s') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_shift($csvData)); // Headers
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Log market assignment actions
     */
    private function logMarketAssignment(Product $product, ?int $oldMarketId, ?int $newMarketId, int $userId, string $action): void
    {
        $oldValue = null;
        $newValue = null;

        if ($oldMarketId) {
            $oldMarket = Market::find($oldMarketId);
            $oldValue = $oldMarket ? $oldMarket->name : null;
        }

        if ($newMarketId) {
            $newMarket = Market::find($newMarketId);
            $newValue = $newMarket ? $newMarket->name : null;
        }

        Log::create([
            'user_id' => $userId,
            'action' => "market_{$action}",
            'model_type' => Product::class,
            'model_id' => $product->id,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Get products assigned to a specific market
     */
    public function getMarketProducts(Market $market, int $perPage = 15)
    {
        return $market->products()
            ->with(['brand', 'franchise', 'businessUnit'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get market usage statistics
     */
    public function getMarketUsageStats(): array
    {
        return Market::withCount('products')
            ->where('is_active', true)
            ->orderBy('products_count', 'desc')
            ->get()
            ->map(function ($market) {
                return [
                    'market' => $market,
                    'products_count' => $market->products_count,
                    'usage_percentage' => $this->calculateMarketUsagePercentage($market->products_count)
                ];
            })
            ->toArray();
    }

    /**
     * Calculate market usage percentage
     */
    private function calculateMarketUsagePercentage(int $productCount): float
    {
        $totalProducts = Product::count();
        return $totalProducts > 0 ? round(($productCount / $totalProducts) * 100, 2) : 0;
    }
}
