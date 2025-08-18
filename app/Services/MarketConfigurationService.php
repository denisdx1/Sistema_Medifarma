<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Mercado;
use App\Models\ConfiguracionMercado;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketConfigurationService
{
    /**
     * Get filtered materials with pagination
     */
    public function getFilteredProducts(array $filters, int $perPage = 10)
    {
        // Usar exactamente la misma consulta base que funciona en getFilterOptions
        $query = Material::select(
            'SKU',
            'Descripción_Presentación', 
            'Código_ATC_4',
            'Descripción_ATC_4',
            'Código_FF_3', 
            'Descripción_FF_3',
            'Molécula',
            'Marca_Genérico',
            'Ético_Popular',
            'Mercado'
        );

        // Global search - search across multiple fields
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('SKU', 'like', "%{$searchTerm}%")
                  ->orWhere('Descripción_Presentación', 'like', "%{$searchTerm}%")
                  ->orWhere('Código_ATC_4', 'like', "%{$searchTerm}%")
                  ->orWhere('Descripción_ATC_4', 'like', "%{$searchTerm}%")
                  ->orWhere('Código_FF_3', 'like', "%{$searchTerm}%")
                  ->orWhere('Descripción_FF_3', 'like', "%{$searchTerm}%")
                  ->orWhere('Molécula', 'like', "%{$searchTerm}%")
                  ->orWhere('Mercado', 'like', "%{$searchTerm}%");
            });
        }

        // Marca Genérico filter
        if (!empty($filters['marca_generico'])) {
            if ($filters['marca_generico'] === '1') {
                $query->where(function($q) {
                    $q->where('Marca_Genérico', 'like', '%MARCA%')
                      ->orWhere('Marca_Genérico', 'like', '%marca%');
                });
            } else {
                $query->where(function($q) {
                    $q->where('Marca_Genérico', 'like', '%GENÉRICO%')
                      ->orWhere('Marca_Genérico', 'like', '%GENERICO%')
                      ->orWhere('Marca_Genérico', 'like', '%genérico%')
                      ->orWhere('Marca_Genérico', 'like', '%generico%');
                });
            }
        }

        // Ético Popular filter
        if (!empty($filters['etico_popular'])) {
            if ($filters['etico_popular'] === '1') {
                $query->where(function($q) {
                    $q->where('Ético_Popular', 'like', '%ÉTICO%')
                      ->orWhere('Ético_Popular', 'like', '%ETICO%')
                      ->orWhere('Ético_Popular', 'like', '%ético%')
                      ->orWhere('Ético_Popular', 'like', '%etico%');
                });
            } else {
                $query->where(function($q) {
                    $q->where('Ético_Popular', 'like', '%POPULAR%')
                      ->orWhere('Ético_Popular', 'like', '%popular%');
                });
            }
        }

        // ATC Code filter
        if (!empty($filters['codigo_atc'])) {
            $query->where('Código_ATC_4', $filters['codigo_atc']);
        }

        // Form Code filter
        if (!empty($filters['codigo_ff'])) {
            $query->where('Código_FF_3', $filters['codigo_ff']);
        }

        // Laboratorio filter
        if (!empty($filters['laboratorio'])) {
            $query->where('LABORATORIO_C', $filters['laboratorio']);
        }

        // Corporación filter
        if (!empty($filters['corporacion'])) {
            $query->where('CORPORACION', $filters['corporacion']);
        }

        // Market Status filter (with_market / without_market)
        if (!empty($filters['market_status'])) {
            if ($filters['market_status'] === 'with_market') {
                $query->whereNotNull('Mercado')
                      ->where('Mercado', '!=', '')
                      ->where('Mercado', '!=', 'null')
                      ->where('Mercado', '!=', 'NULL')
                      ->where('Mercado', '!=', 'RESTO');
            } elseif ($filters['market_status'] === 'without_market') {
                $query->where(function($q) {
                    $q->whereNull('Mercado')
                      ->orWhere('Mercado', '')
                      ->orWhere('Mercado', 'null')
                      ->orWhere('Mercado', 'NULL')
                      ->orWhere('Mercado', 'RESTO');
                });
            }
        }

        // Mercado filter
        if (!empty($filters['mercado'])) {
            if ($filters['mercado'] === 'sin_asignar') {
                $query->where(function($q) {
                    $q->whereNull('Mercado')
                      ->orWhere('Mercado', '')
                      ->orWhere('Mercado', 'null')
                      ->orWhere('Mercado', 'NULL')
                      ->orWhere('Mercado', 'RESTO');
                });
            } else {
                // Filtrar por mercado específico - exactamente igual que en getFilterOptions
                $query->where('Mercado', $filters['mercado']);
            }
        }

        return $query->orderBy('SKU', 'asc')->paginate($perPage);
    }

    /**
     * Get filter options for dropdowns
     */
    public function getFilterOptions(): array
    {
        return [
            'marcas_genericos' => Material::select('Marca_Genérico')
                ->distinct()
                ->whereNotNull('Marca_Genérico')
                ->orderBy('Marca_Genérico')
                ->get()
                ->map(function($item) {
                    $value = $item->Marca_Genérico;
                    return [
                        'value' => $value,
                        'label' => strtoupper($value)
                    ];
                }),
            'etico_popular' => Material::select('Ético_Popular')
                ->distinct()
                ->whereNotNull('Ético_Popular')
                ->orderBy('Ético_Popular')
                ->get()
                ->map(function($item) {
                    $value = $item->Ético_Popular;
                    return [
                        'value' => $value,
                        'label' => strtoupper($value)
                    ];
                }),
            'codigos_atc' => Material::select('Código_ATC_4', 'Descripción_ATC_4')
                ->distinct()
                ->whereNotNull('Código_ATC_4')
                ->orderBy('Código_ATC_4')
                ->limit(50) // Limitar para no sobrecargar
                ->get(),
            'codigos_ff' => Material::select('Código_FF_3', 'Descripción_FF_3')
                ->distinct()
                ->whereNotNull('Código_FF_3')
                ->orderBy('Código_FF_3')
                ->limit(50) // Limitar para no sobrecargar
                ->get(),
            'laboratorios' => Material::select('LABORATORIO_C')
                ->distinct()
                ->whereNotNull('LABORATORIO_C')
                ->where('LABORATORIO_C', '!=', '')
                ->orderBy('LABORATORIO_C')
                ->pluck('LABORATORIO_C')
                ->filter(function($laboratorio) {
                    return !empty(trim($laboratorio));
                })
                ->unique()
                ->values()
                ->toArray(),
            'corporaciones' => Material::select('CORPORACION')
                ->distinct()
                ->whereNotNull('CORPORACION')
                ->where('CORPORACION', '!=', '')
                ->orderBy('CORPORACION')
                ->pluck('CORPORACION')
                ->filter(function($corporacion) {
                    return !empty(trim($corporacion));
                })
                ->unique()
                ->values()
                ->toArray(),
            'mercados' => Material::select('Mercado')
                ->distinct()
                ->whereNotNull('Mercado')
                ->where('Mercado', '!=', '')
                ->where('Mercado', '!=', 'null')
                ->where('Mercado', '!=', 'NULL')
                ->orderBy('Mercado')
                ->pluck('Mercado')
                ->filter(function($mercado) {
                    return !empty(trim($mercado)) && strtolower(trim($mercado)) !== 'null';
                })
                ->unique()
                ->values()
                ->toArray()
        ];
    }

    /**
     * Get all available markets for assignment (excluding RESTO and invalid values)
     */
    public function getAllAvailableMarkets(): array
    {
        return Material::select('Mercado')
            ->distinct()
            ->whereNotNull('Mercado')
            ->where('Mercado', '!=', '')
            ->where('Mercado', '!=', 'null')
            ->where('Mercado', '!=', 'NULL')
            ->where('Mercado', '!=', 'RESTO')
            ->orderBy('Mercado')
            ->pluck('Mercado')
            ->filter(function($mercado) {
                return !empty(trim($mercado)) && strtolower(trim($mercado)) !== 'null';
            })
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get material statistics
     */
    public function getMarketStatistics(): array
    {
        $totalMaterials = Material::count();
        $marcaMaterials = Material::where('Marca_Genérico', 'MARCA')->count();
        $genericoMaterials = Material::where('Marca_Genérico', 'GENÉRICO')->count();
        $eticoMaterials = Material::where('Ético_Popular', 'ÉTICO')->count();
        $popularMaterials = Material::where('Ético_Popular', 'POPULAR')->count();
        $sinMercadoMaterials = Material::sinMercadoAsignado()->count();
        $conMercadoMaterials = $totalMaterials - $sinMercadoMaterials;

        return [
            'total_materials' => $totalMaterials,
            'marca_materials' => $marcaMaterials,
            'generico_materials' => $genericoMaterials,
            'etico_materials' => $eticoMaterials,
            'popular_materials' => $popularMaterials,
            'sin_mercado_materials' => $sinMercadoMaterials,
            'con_mercado_materials' => $conMercadoMaterials,
            'marca_percentage' => $totalMaterials > 0 ? round(($marcaMaterials / $totalMaterials) * 100, 2) : 0,
            'etico_percentage' => $totalMaterials > 0 ? round(($eticoMaterials / $totalMaterials) * 100, 2) : 0,
            'mercado_percentage' => $totalMaterials > 0 ? round(($conMercadoMaterials / $totalMaterials) * 100, 2) : 0
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

    /**
     * Crear solicitud de configuración de mercado
     */
    public function crearSolicitudMercado(string $sku, int $idMercado, int $idUsuario, string $comentario = null): ConfiguracionMercado
    {
        return ConfiguracionMercado::create([
            'id_producto' => $sku,
            'id_mercado' => $idMercado,
            'id_usuario' => $idUsuario,
            'fecha_solicitud' => now(),
            'estado' => ConfiguracionMercado::ESTADO_PENDIENTE,
            'aprobacion' => $comentario
        ]);
    }

    /**
     * Obtener configuraciones pendientes de aprobación
     */
    public function getConfiguracionesPendientes(int $perPage = 10)
    {
        return ConfiguracionMercado::with(['material', 'mercado', 'usuario'])
            ->pendientes()
            ->orderBy('fecha_solicitud', 'desc')
            ->paginate($perPage);
    }

    /**
     * Obtener configuraciones por usuario
     */
    public function getConfiguracionesPorUsuario(int $userId, int $perPage = 10)
    {
        return ConfiguracionMercado::with(['material', 'mercado'])
            ->porUsuario($userId)
            ->orderBy('fecha_solicitud', 'desc')
            ->paginate($perPage);
    }

    /**
     * Aprobar configuración de mercado
     */
    public function aprobarConfiguracion(int $configId, string $comentario = null): bool
    {
        $config = ConfiguracionMercado::findOrFail($configId);
        
        DB::beginTransaction();
        try {
            // Aprobar la configuración
            $config->aprobar($comentario);
            
            // Actualizar el material con el mercado asignado
            Material::where('SKU', $config->id_producto)
                ->update(['id_mercado' => $config->id_mercado]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rechazar configuración de mercado
     */
    public function rechazarConfiguracion(int $configId, string $comentario = null): bool
    {
        $config = ConfiguracionMercado::findOrFail($configId);
        return $config->rechazar($comentario);
    }

    /**
     * Obtener estadísticas de configuraciones
     */
    public function getEstadisticasConfiguraciones(): array
    {
        return [
            'pendientes' => ConfiguracionMercado::pendientes()->count(),
            'aprobadas' => ConfiguracionMercado::aprobadas()->count(),
            'rechazadas' => ConfiguracionMercado::rechazadas()->count(),
            'total' => ConfiguracionMercado::count()
        ];
    }

    /**
     * Obtener mercados disponibles desde la tabla mercados
     */
    public function getMercadosDisponibles(): \Illuminate\Database\Eloquent\Collection
    {
        return Mercado::activos()->orderBy('mercado')->get();
    }
}
