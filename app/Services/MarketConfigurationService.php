<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Mercado;
use App\Models\ConfiguracionMercado;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MarketConfigurationService
{
    /**
     * Get filtered materials with pagination
     * NOTA: Devuelve datos vacíos ya que no existe tabla materiales
     */
    public function getFilteredProducts(array $filters, int $perPage = 10)
    {
        // Devolver paginación vacía para mantener compatibilidad
        return new LengthAwarePaginator(
            new Collection(), // items vacíos
            0, // total
            $perPage,
            1, // página actual
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Get filter options for dropdowns
     * NOTA: Devuelve opciones vacías ya que no existe tabla materiales
     */
    public function getFilterOptions()
    {
        return [
            'atc_codes' => new Collection(),
            'ff_codes' => new Collection(),
            'laboratorios' => new Collection(),
            'corporaciones' => new Collection(),
            'mercados' => Mercado::select('mercado')->distinct()->orderBy('mercado')->pluck('mercado'),
            'marcas_genericos' => new Collection(),
            'etico_popular' => new Collection(),
            'codigos_atc' => new Collection(),
            'codigos_ff' => new Collection()
        ];
    }

    /**
     * Get market statistics
     * NOTA: Devuelve estadísticas vacías ya que no existe tabla materiales
     */
    public function getMarketStatistics()
    {
        $totalMercados = Mercado::count();
        
        return [
            'total_materials' => 0,
            'materials_with_market' => 0,
            'materials_without_market' => 0,
            'sin_mercado_materials' => 0,
            'con_mercado_materials' => 0,
            'percentage_with_market' => 0,
            'marcas_genericos' => 0,
            'total_markets' => $totalMercados,
            'active_markets' => $totalMercados
        ];
    }

    /**
     * Store market configuration request
     */
    public function storeMarketConfiguration(array $data)
    {
        return ConfiguracionMercado::create($data);
    }

    /**
     * Get pending market configuration requests
     */
    public function getPendingRequests()
    {
        return ConfiguracionMercado::with(['mercado', 'usuario'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_solicitud', 'desc')
            ->get();
    }

    /**
     * Approve market configuration request
     */
    public function approveRequest(int $requestId, int $userId)
    {
        $request = ConfiguracionMercado::findOrFail($requestId);
        
        DB::beginTransaction();
        try {
            // Actualizar estado de la solicitud
            $request->update([
                'estado' => 'aprobada',
                'fecha_aprobacion' => now(),
                'id_usuario_aprobacion' => $userId
            ]);

            // NOTA: No actualizamos tabla materiales porque no existe
            // En el futuro, aquí se actualizaría la asignación del material al mercado

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Reject market configuration request
     */
    public function rejectRequest(int $requestId, int $userId, string $reason = null)
    {
        $request = ConfiguracionMercado::findOrFail($requestId);
        
        $request->update([
            'estado' => 'rechazada',
            'fecha_aprobacion' => now(),
            'id_usuario_aprobacion' => $userId,
            'comentarios' => $reason
        ]);

        return true;
    }

    /**
     * Get materials by market
     * NOTA: Devuelve colección vacía ya que no existe tabla materiales
     */
    public function getMaterialsByMarket(int $marketId)
    {
        return new Collection();
    }

    /**
     * Search materials
     * NOTA: Devuelve colección vacía ya que no existe tabla materiales
     */
    public function searchMaterials(string $term)
    {
        return new Collection();
    }

    /**
     * Get market assignment history
     */
    public function getMarketAssignmentHistory(string $sku = null)
    {
        $query = ConfiguracionMercado::with(['mercado', 'usuario']);
        
        if ($sku) {
            $query->where('id_producto', $sku);
        }
        
        return $query->orderBy('fecha_solicitud', 'desc')->get();
    }

    /**
     * Bulk assign materials to market
     * NOTA: Solo registra las solicitudes ya que no existe tabla materiales
     */
    public function bulkAssignToMarket(array $skus, int $marketId, int $userId)
    {
        $requests = [];
        
        foreach ($skus as $sku) {
            $requests[] = [
                'id_producto' => $sku,
                'id_mercado' => $marketId,
                'id_usuario' => $userId,
                'fecha_solicitud' => now(),
                'estado' => 'pendiente'
            ];
        }
        
        ConfiguracionMercado::insert($requests);
        
        return count($requests);
    }

    /**
     * Get materials export data
     * NOTA: Devuelve colección vacía ya que no existe tabla materiales
     */
    public function getExportData(array $filters = [])
    {
        return new Collection();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $totalMercados = Mercado::count();
        
        return [
            'total_materials' => 0,
            'pending_requests' => ConfiguracionMercado::where('estado', 'pendiente')->count(),
            'approved_today' => ConfiguracionMercado::where('estado', 'aprobada')
                ->whereDate('fecha_aprobacion', today())->count(),
            'total_markets' => $totalMercados,
            'materials_by_market' => []
        ];
    }
}
