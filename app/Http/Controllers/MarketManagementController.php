<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\VmaeProductoIqvia;
use App\Services\NotificationService;

class MarketManagementController extends Controller
{


    /**
     * Aplicar filtro de gerente de producto a una query de productos - SOLO POR GERENTE
     */
    private function aplicarFiltroGerenteProducto($query, $user)
    {
        if (!$user || $user->idRol != 2) {
            return $query;
        }

        // Construir TODAS las posibles variaciones del nombre del gerente
        $nombreCompleto = strtoupper(trim($user->usuario));
        $partesNombre = array_filter(explode(' ', $nombreCompleto)); // Filtrar espacios vacíos
        
        $posiblesNombres = [];
        
        if (count($partesNombre) >= 2) {
            // 1. Formato completo original
            $posiblesNombres[] = $nombreCompleto;
            
            // 2. Formato invertido (apellido nombre)
            $posiblesNombres[] = $partesNombre[1] . ' ' . $partesNombre[0];
            
            // 3. Solo primer parte
            $posiblesNombres[] = $partesNombre[0];
            
            // 4. Solo segunda parte
            $posiblesNombres[] = $partesNombre[1];
            
            // 5. Si hay más de 2 partes, agregar todas las combinaciones
            if (count($partesNombre) > 2) {
                // Primer + último
                $posiblesNombres[] = $partesNombre[0] . ' ' . end($partesNombre);
                // Último + primer
                $posiblesNombres[] = end($partesNombre) . ' ' . $partesNombre[0];
                // Todos los elementos individuales
                foreach ($partesNombre as $parte) {
                    if (!in_array($parte, $posiblesNombres)) {
                        $posiblesNombres[] = $parte;
                    }
                }
            }
        } else {
            $posiblesNombres[] = $nombreCompleto;
        }
        
        // Eliminar duplicados y elementos vacíos
        $posiblesNombres = array_unique(array_filter($posiblesNombres));

        // FILTRO SIMPLE: Solo por Gerente_Producto (sin franquicias)
        return $query->whereIn('v.Gerente_Producto', $posiblesNombres)
                     ->whereNotNull('v.Gerente_Producto');
    }

    /**
     * Display brands and their markets for the logged manager
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $user = Auth::user();

            // Query con DISTINCT para marca y mercado: SELECT DISTINCT descripcionProducto, MERCADO FROM VMAE_PROD_IQVIA WHERE Gerente_Producto = 'USUARIO'
            $marketsQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->select(
                    'v.descripcionProducto as marca',
                    'v.MERCADO as mercado',
                    DB::raw('MIN(v.codigoPresentacion) as codigoPresentacion'), // Tomar uno cualquiera
                    DB::raw('1 as idMercado'),
                    DB::raw('GETDATE() as fechaRegistro'),
                    DB::raw("'ACTIVO' as estado")
                )
                ->whereNotNull('v.descripcionProducto')
                ->where('v.descripcionProducto', '!=', '')
                ->whereNotNull('v.Gerente_Producto')
                ->whereNotNull('v.MERCADO')
                ->groupBy('v.descripcionProducto', 'v.MERCADO'); // Group by para hacer DISTINCT

            // FILTRAR SEGÚN EL ROL DEL USUARIO
            if ($user && $user->idRol == 2) {
                // GERENTE: Filtrar solo sus marcas
                $this->aplicarFiltroGerenteProducto($marketsQuery, $user);
            }
            // ADMIN (idRol == 1): Mostrar todas las marcas sin filtro

            // Búsqueda simple si se proporciona (compatible con GROUP BY)
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where(function($q) use ($searchTerm) {
                    $q->where('v.descripcionProducto', 'LIKE', $searchTerm)
                      ->orWhere('v.MERCADO', 'LIKE', $searchTerm);
                });
            }

            $marketsQuery->orderBy('v.descripcionProducto', 'asc');

            // Obtener el total para las estadísticas
            $totalCount = $marketsQuery->count();

            // Obtener resultados paginados
            $markets = $marketsQuery->paginate(10);
            $markets->appends($request->all());

            // Estadísticas simples
            $stats = [
                'total' => $totalCount,
                'activos' => $totalCount,
                'inactivos' => 0
            ];
            return view('market-management.index', [
                'markets' => $markets,
                'search' => $search,
                'franquiciaFiltro' => null,
                'userFranquicias' => [],
                'userFranquicia' => null,
                'isGerenteProducto' => $user ? $user->idRol == 2 : false,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cargar marcas: ' . $e->getMessage());
        }
    }

    /**
     * Update market name using stored procedure SP_UPDATE_MERCADO with audit
     */
    public function updateMarket(Request $request)
    {
        $request->validate([
            'market_id' => 'required|integer',
            'market_name' => 'required|string|max:255',
            'market_note' => 'required|string|max:1000'  // Nota obligatoria
        ]);

        try {
            DB::beginTransaction();
            
            $marketName = trim($request->market_name);
            $marketNote = $request->market_note ? trim($request->market_note) : null;
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
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_MERCADO ?, ?, ?, ?', [
                $request->market_id,  // @idMercado
                $marketName,          // @mercado
                $userId,              // @idUsuario
                $marketNote           // @nota
            ]);

            // Enviar notificación por email (con nota si existe)
            try {
                $notificationService = new NotificationService();
                $notificationService->notifyMarketUpdate(
                    $existingMarket->mercado, // nombre anterior
                    $marketName,              // nombre nuevo
                    Auth::user(),
                    $marketNote               // nota del usuario
                );
            } catch (\Exception $e) {
                // Error en notificación pero no interrumpir el flujo
            }
            
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

            // Query con DISTINCT para marca y mercado: SELECT DISTINCT descripcionProducto, MERCADO FROM VMAE_PROD_IQVIA WHERE Gerente_Producto = 'USUARIO'
            $marketsQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->select(
                    'v.descripcionProducto as marca',
                    'v.MERCADO as mercado',
                    DB::raw('MIN(v.codigoPresentacion) as codigoPresentacion'), // Tomar uno cualquiera
                    DB::raw('1 as idMercado'),
                    DB::raw('GETDATE() as fechaRegistro'),
                    DB::raw("'ACTIVO' as estado")
                )
                ->whereNotNull('v.descripcionProducto')
                ->where('v.descripcionProducto', '!=', '')
                ->whereNotNull('v.Gerente_Producto')
                ->whereNotNull('v.MERCADO')
                ->groupBy('v.descripcionProducto', 'v.MERCADO'); // Group by para hacer DISTINCT

            // FILTRAR SEGÚN EL ROL DEL USUARIO
            if ($user && $user->idRol == 2) {
                // GERENTE: Filtrar solo sus marcas
                $this->aplicarFiltroGerenteProducto($marketsQuery, $user);
            }
            // ADMIN (idRol == 1): Mostrar todas las marcas sin filtro

            // Búsqueda simple si se proporciona (compatible con GROUP BY)
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where(function($q) use ($searchTerm) {
                    $q->where('v.descripcionProducto', 'LIKE', $searchTerm)
                      ->orWhere('v.MERCADO', 'LIKE', $searchTerm);
                });
            }

            $marketsQuery->orderBy('v.descripcionProducto', 'asc');

            // Obtener resultados paginados
            $total = $marketsQuery->count();
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $results = $marketsQuery->offset($offset)->limit($perPage)->get();

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
     * API endpoint to get ALL markets (sin filtros) for assignment purposes
     */
    public function getAllMarketsApi(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            // Query para obtener TODOS los mercados activos (sin filtros por usuario/franquicia)
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
                'message' => 'Todos los mercados activos disponibles para asignación'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener todos los mercados: ' . $e->getMessage()
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
     * Aplicar filtros individuales a la consulta CON JOIN
     */
    private function applyIndividualFilters($query, $filters)
    {
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                switch ($field) {
                    case 'descripcionProducto':
                        $query->where('v.descripcionProducto', 'LIKE', "%{$value}%");
                        break;
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
                    case 'marcaGenerico':
                        $query->where('v.marcaGenerico', 'LIKE', "%{$value}%");
                        break;
                    case 'eticoPopular':
                        $query->where('v.eticoPopular', 'LIKE', "%{$value}%");
                        break;
                    case 'mercado':
                        $query->where('v.MERCADO', 'LIKE', "%{$value}%");
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
                         ->orWhere('v.descripcionCorporacion', 'LIKE', "%{$search}%")
                         ->orWhere('v.descripcionProducto', 'LIKE', "%{$search}%"); // Nuevo campo agregado
            });
        }

        // Aplicar filtros individuales (excluyendo fuente)
        foreach ($filters as $field => $value) {
            if (!empty($value) && $field !== 'fuente') {
                switch ($field) {
                    case 'descripcionProducto':
                        $query->where('v.descripcionProducto', 'LIKE', "%{$value}%");
                        break;
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
                    case 'marcaGenerico':
                        $query->where('v.marcaGenerico', 'LIKE', "%{$value}%");
                        break;
                    case 'eticoPopular':
                        $query->where('v.eticoPopular', 'LIKE', "%{$value}%");
                        break;
                    case 'mercado':
                        $query->where('v.MERCADO', 'LIKE', "%{$value}%");
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
                         ->orWhere('v.descripcionCorporacion', 'LIKE', "%{$search}%")
                         ->orWhere('v.descripcionProducto', 'LIKE', "%{$search}%"); // Nuevo campo agregado
            });
        }

        // Aplicar todos los filtros incluyendo fuente
        $this->applyIndividualFilters($query, $filters);
    }
    

    
}
