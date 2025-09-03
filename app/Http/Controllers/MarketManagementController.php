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

            // Query con DISTINCT para marca y mercado: Ahora usando JOIN con la tabla de configuración
            $marketsQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->leftJoin('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->select(
                    'v.descripcionProducto as marca',
                    'm.mercado as mercado',
                    DB::raw('MIN(v.codigoPresentacion) as codigoPresentacion'), // Tomar uno cualquiera
                    DB::raw('1 as idMercado'),
                    DB::raw('GETDATE() as fechaRegistro'),
                    DB::raw("'ACTIVO' as estado")
                )
                ->whereNotNull('v.descripcionProducto')
                ->where('v.descripcionProducto', '!=', '')
                ->groupBy('v.descripcionProducto', 'm.mercado'); // Group by para hacer DISTINCT

            // FILTRAR SEGÚN EL ROL DEL USUARIO
            if ($user && $user->idRol == 2) {
                // GERENTE: Filtrar solo sus marcas (excluye registros sin gerente_producto)
                $this->aplicarFiltroGerenteProducto($marketsQuery, $user);
            } else {
                // ADMIN (idRol == 1): Mostrar todas las marcas sin filtro, incluyendo:
                // - Marcas con gerente_producto null
                // - Marcas con mercado null (sin configuración)
                // - Marcas con franquicia null
                // No se aplican filtros adicionales
            }

            // Búsqueda simple si se proporciona (compatible con GROUP BY)
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where(function($q) use ($searchTerm) {
                    $q->where('v.descripcionProducto', 'LIKE', $searchTerm)
                      ->orWhere('m.mercado', 'LIKE', $searchTerm);
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
            \Log::error('Error en MarketManagementController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
            
            // Verificar si ya existe otro mercado con el mismo nombre (ignorando el actual)
            $duplicateMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->whereRaw('LTRIM(RTRIM(mercado)) = ?', [trim($marketName)])
                ->where('idMercado', '!=', $request->market_id)
                ->first();
                
            if ($duplicateMarket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro mercado con el nombre "' . $marketName . '"'
                ], 422);
            }
            
            // Verificar si el nuevo nombre es igual al actual (ignorando espacios)
            $currentMarket = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $request->market_id)
                ->first();
                
            if ($currentMarket) {
                $currentName = trim(strtolower($currentMarket->mercado));
                $newName = trim(strtolower($marketName));
                
                if ($currentName === $newName) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nuevo nombre debe ser diferente al nombre actual del mercado'
                    ], 422);
                }
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

            // Query con DISTINCT para marca y mercado: Ahora usando JOIN con la tabla de configuración
            $marketsQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->leftJoin('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->select(
                    'v.descripcionProducto as marca',
                    'm.mercado as mercado',
                    DB::raw('MIN(v.codigoPresentacion) as codigoPresentacion'), // Tomar uno cualquiera
                    DB::raw('1 as idMercado'),
                    DB::raw('GETDATE() as fechaRegistro'),
                    DB::raw("'ACTIVO' as estado")
                )
                ->whereNotNull('v.descripcionProducto')
                ->where('v.descripcionProducto', '!=', '')
                ->groupBy('v.descripcionProducto', 'm.mercado'); // Group by para hacer DISTINCT

            // FILTRAR SEGÚN EL ROL DEL USUARIO
            if ($user && $user->idRol == 2) {
                // GERENTE: Filtrar solo sus marcas (excluye registros sin gerente_producto)
                $this->aplicarFiltroGerenteProducto($marketsQuery, $user);
            } else {
                // ADMIN (idRol == 1): Mostrar todas las marcas sin filtro, incluyendo:
                // - Marcas con gerente_producto null
                // - Marcas con mercado null (sin configuración)
                // - Marcas con franquicia null
                // No se aplican filtros adicionales
            }

            // Búsqueda simple si se proporciona (compatible con GROUP BY)
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $marketsQuery->where(function($q) use ($searchTerm) {
                    $q->where('v.descripcionProducto', 'LIKE', $searchTerm)
                      ->orWhere('m.mercado', 'LIKE', $searchTerm);
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
                        $query->where('m.mercado', 'LIKE', "%{$value}%");
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
                        $query->where('m.mercado', 'LIKE', "%{$value}%");
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

    /**
     * Obtener opciones de filtro para productos del SIN_ASIGNAR y NUEVOS
     */
    public function getRestoFilterOptions(Request $request)
    {
        try {
            $fuentes = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION as c')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->join('dbo.VMAE_PROD_IQVIA as v', 'c.codigo', '=', 'v.codigoPresentacion')
                ->select(DB::raw("DISTINCT COALESCE(c.fuente, 'IQV') as fuente"))
                ->whereIn('m.mercado', ['SIN_ASIGNAR', 'NUEVOS'])
                ->whereNotNull('v.codigoPresentacion')
                ->where('v.codigoPresentacion', '!=', '')
                ->whereNotNull(DB::raw("COALESCE(c.fuente, 'IQV')"))
                ->orderBy('fuente')
                ->pluck('fuente')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'fuentes' => $fuentes
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener opciones de filtro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos del SIN_ASIGNAR y NUEVOS para asignación
     */
    public function getRestoProducts(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            // Usar la misma lógica corregida que funciona en bulkChangeMarket
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION as c')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->join('dbo.VMAE_PROD_IQVIA as v', 'c.codigo', '=', 'v.codigoPresentacion')
                ->select(
                    'v.codigoPresentacion',
                    'v.descripcionPresentacion',
                    DB::raw("COALESCE(c.fuente, 'IQV') as fuente")
                )
                ->whereIn('m.mercado', ['SIN_ASIGNAR', 'NUEVOS'])
                ->whereNotNull('v.codigoPresentacion')
                ->where('v.codigoPresentacion', '!=', '');

            // Aplicar búsqueda si se proporciona
            if (!empty($search)) {
                $query->where(function($subQuery) use ($search) {
                    $subQuery->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
                             ->orWhere('v.descripcionPresentacion', 'LIKE', "%{$search}%");
                });
            }

            // Obtener total de registros
            $totalCount = $query->count();

            // Obtener productos paginados
            $products = $query->orderBy('v.descripcionPresentacion')
                             ->offset(($page - 1) * $perPage)
                             ->limit($perPage)
                             ->get();

            return response()->json([
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalCount,
                    'total_pages' => ceil($totalCount / $perPage),
                    'has_more_pages' => $page < ceil($totalCount / $perPage)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos del SIN_ASIGNAR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar productos a un mercado específico
     */
    public function assignProducts(Request $request)
    {
        $validated = $request->validate([
            'idMercado' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.code' => 'required|string',
            'products.*.fuente' => 'required|string|min:1',
            'note' => 'required|string|max:1000'
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
            $assignedProducts = [];

            // Procesar cada producto
            foreach ($validated['products'] as $product) {
                try {
                    // Verificar que el producto existe en VMAE_PROD_IQVIA con mercado SIN_ASIGNAR o NUEVOS
                    $productoInfo = DB::connection('sqlsrv')
                        ->table('dbo.VMAE_PROD_IQVIA as v')
                        ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                        ->leftJoin('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                        ->where('v.codigoPresentacion', $product['code'])
                        ->whereIn('m.mercado', ['SIN_ASIGNAR', 'NUEVOS'])
                        ->select('v.codigoPresentacion', 'v.descripcionPresentacion')
                        ->first();
                        
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

                    // Eliminar la configuración existente del producto (que debería estar en SIN_ASIGNAR)
                    DB::connection('sqlsrv')
                        ->table('ODS.TAB_CONFIGURACION')
                        ->where('codigo', $product['code'])
                        ->delete();

                    // Ejecutar el stored procedure para el nuevo mercado
                    $userId = Auth::user()->idUsuario;
                    
                    DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_CONFIGURACION ?, ?, ?, ?, ?', [
                        $validated['idMercado'],
                        $product['code'],
                        $product['fuente'],
                        $userId,
                        $validated['note']
                    ]);

                    // Intentar actualizar la vista VMAE
                    try {
                        DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_VMAE_MERCADO ?, ?', [
                            $product['code'],
                            $mercado->mercado
                        ]);
                    } catch (\Exception $vmaeException) {
                        try {
                            DB::connection('sqlsrv')->statement("
                                UPDATE dbo.VMAE_PROD_IQVIA 
                                SET MERCADO = ? 
                                WHERE codigoPresentacion = ?
                            ", [$mercado->mercado, $product['code']]);
                        } catch (\Exception $directUpdateException) {
                            // No se pudo actualizar VMAE, pero continuamos
                        }
                    }

                    $assignedCount++;
                    $assignedProducts[] = [
                        'code' => $product['code'],
                        'name' => $productoInfo->descripcionPresentacion
                    ];

                } catch (\Exception $e) {
                    $errors[] = "Error asignando producto {$product['code']}: " . $e->getMessage();
                }
            }

            DB::commit();

            // Enviar notificación por email si hay productos asignados
            if ($assignedCount > 0) {
                try {
                    $notificationService = new NotificationService();
                    $notificationService->sendProductAssignmentNotification(
                        $mercado->mercado,
                        $assignedProducts,
                        Auth::user()->usuario,
                        $validated['note']
                    );
                } catch (\Exception $e) {
                    // Error en notificación, pero no afecta la operación principal
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se asignaron {$assignedCount} productos al mercado {$mercado->mercado}",
                'assigned_count' => $assignedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API para búsqueda de mercados (usado en acciones masivas)
     */
    public function searchMarketsApi(Request $request)
    {
        try {
            $query = trim($request->get('q', ''));
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'La búsqueda debe tener al menos 2 caracteres'
                ], 400);
            }

            $markets = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('mercado', 'LIKE', "%{$query}%")
                ->where('estado', 'ACTIVO')
                ->select('idMercado', 'mercado')
                ->orderBy('mercado')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'markets' => $markets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar mercados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos de NUEVOS y SIN_ASIGNAR filtrados por gerente de producto
     */
    public function getProductosNuevosYSinAsignar(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Obtener productos de NUEVOS y SIN_ASIGNAR filtrados por gerente de producto
            // Ahora necesitamos hacer JOIN con la tabla de configuración para obtener el mercado real
            $productos = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->leftJoin('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->whereIn('m.mercado', ['NUEVOS', 'SIN_ASIGNAR'])
                ->where('v.Gerente_Producto', $user->usuario)
                ->select('v.codigoPresentacion', 'v.descripcionPresentacion', 'm.mercado as MERCADO')
                ->orderBy('m.mercado')
                ->orderBy('v.descripcionPresentacion')
                ->get();

            // Contar por tipo de mercado
            $countNuevos = $productos->where('MERCADO', 'NUEVOS')->count();
            $countSinAsignar = $productos->where('MERCADO', 'SIN_ASIGNAR')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'counts' => [
                        'nuevos' => $countNuevos,
                        'sin_asignar' => $countSinAsignar,
                        'total' => $productos->count()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener conteo de ATC4 para el usuario autenticado con rol de Gerente_Producto
     */
    public function getAtc4Count(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Verificar que el usuario tenga rol de Gerente_Producto
            if ($user->idRol != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo gerentes de producto pueden acceder a esta información.'
                ], 403);
            }

            // Obtener ATC4 únicos para el gerente de producto
            $atc4Count = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->where('v.Gerente_Producto', $user->usuario)
                ->whereNotNull('v.codigoATC4')
                ->where('v.codigoATC4', '!=', '')
                ->distinct()
                ->count('v.codigoATC4');

            return response()->json([
                'success' => true,
                'data' => [
                    'atc4_count' => $atc4Count,
                    'gerente_producto' => $user->usuario
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conteo de ATC4: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de ATC4 para el usuario autenticado con rol de Gerente_Producto
     */
    public function getAtc4List(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Verificar que el usuario tenga rol de Gerente_Producto
            if ($user->idRol != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acceso denegado. Solo gerentes de producto pueden acceder a esta información.'
                ], 403);
            }

            // Obtener ATC4 únicos para el gerente de producto con información adicional
            $atc4List = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->where('v.Gerente_Producto', $user->usuario)
                ->whereNotNull('v.codigoATC4')
                ->where('v.codigoATC4', '!=', '')
                ->select(
                    'v.codigoATC4',
                    'v.descripcionATC4',
                    DB::raw('COUNT(*) as cantidad_productos')
                )
                ->groupBy('v.codigoATC4', 'v.descripcionATC4')
                ->orderBy('v.descripcionATC4')
                ->get();

            // Ahora obtener el conteo TOTAL de productos para cada ATC4 (sin filtrar por gerente)
            foreach ($atc4List as $atc4) {
                $totalProductos = DB::connection('sqlsrv')
                    ->table('dbo.VMAE_PROD_IQVIA as v')
                    ->where('v.codigoATC4', $atc4->codigoATC4)
                    ->whereNotNull('v.codigoATC4')
                    ->where('v.codigoATC4', '!=', '')
                    ->count();
                
                $atc4->total_productos = $totalProductos;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'atc4_list' => $atc4List,
                    'gerente_producto' => $user->usuario
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener lista de ATC4: ' . $e->getMessage()
            ], 500);
        }
    }
}
