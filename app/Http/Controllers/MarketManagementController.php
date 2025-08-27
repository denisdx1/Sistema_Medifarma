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

        // \Log::info('Filtrando por gerente: ' . $user->usuario . ' - Variaciones: ' . implode(', ', $posiblesNombres));

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
            'market_note' => 'required|string|max:1000'  // Nota ahora es obligatoria
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
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_MERCADO ?, ?, ?', [
                $request->market_id,  // @idMercado
                $marketName,          // @mercado
                $userId                // @idUsuario
            ]);

            // Enviar notificación por email (con nota si existe)
            try {
                \Log::info('DEBUG: Iniciando envío de notificación de actualización de mercado', [
                    'usuario' => Auth::user()->usuario ?? 'Unknown',
                    'user_id' => Auth::user()->idUsuario ?? 'Unknown',
                    'mercado_anterior' => $existingMarket->mercado,
                    'mercado_nuevo' => $marketName,
                    'nota' => $marketNote
                ]);
                
                $notificationService = new NotificationService();
                $notificationService->notifyMarketUpdate(
                    $existingMarket->mercado, // nombre anterior
                    $marketName,              // nombre nuevo
                    Auth::user(),
                    $marketNote               // nota del usuario
                );
                
                \Log::info('DEBUG: Notificación de actualización completada exitosamente');
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                \Log::error('Error enviando notificación de actualización de mercado: ' . $e->getMessage());
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

            // Query base para búsqueda
            $query = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                )
                ->where('e.estado', '!=', 'INACTIVO'); // Excluir mercados inactivos

            // TODOS LOS USUARIOS VEN TODOS LOS MERCADOS - Sin filtros por rol o franquicia

            $query->orderBy('m.fechaRegistro', 'asc');

            // Aplicar búsqueda global
            if (!empty($search)) {
                $searchTerm = '%' . $search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('m.mercado', 'LIKE', $searchTerm)
                      ->orWhere('e.estado', 'LIKE', $searchTerm)
                      ->orWhere('m.idMercado', 'LIKE', $searchTerm);
                });
            }

            // Obtener resultados paginados
            $total = $query->count();
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $results = $query->offset($offset)->limit($perPage)->get();

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
     * API endpoint to get all available markets for dropdowns/selects
     */
    public function getMarketsApi(Request $request)
    {
        try {
            $user = Auth::user(); // Usuario autenticado
            $franquiciaFiltro = $request->get('franquicia'); // Filtro de franquicia específica
            
            // Query base para obtener mercados activos
            $marketsQuery = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select(
                    'm.idMercado',
                    'm.mercado',
                    'm.fechaRegistro',
                    'e.estado'
                )
                ->where('e.estado', '!=', 'INACTIVO'); // Excluir mercados inactivos

            // TODOS LOS USUARIOS VEN TODOS LOS MERCADOS - Sin filtros por rol o franquicia

            $markets = $marketsQuery->orderBy('m.mercado', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $markets,
                'total' => $markets->count(),
                'filtered_by_role' => false, // Ya no hay filtros por rol
                'franquicia_filter' => $franquiciaFiltro,
                'user_role' => $user ? $user->idRol : null,
                'message' => 'Todos los mercados disponibles para todos los usuarios'
            ]);

        } catch (\Exception $e) {
            // Error en getMarketsApi
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de mercados: ' . $e->getMessage()
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
     * Change product market using stored procedure
     */
    public function changeProductMarket(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string',
            'nuevoMercadoId' => 'required|integer',
            'note' => 'required|string|max:1000'  // Nota ahora es obligatoria
        ]);

        try {
            DB::beginTransaction();
            
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
            $mercadoDestino = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO as m')
                ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
                ->select('m.idMercado', 'm.mercado', 'e.estado')
                ->where('m.idMercado', $validated['nuevoMercadoId'])
                ->where('e.estado', 'ACTIVO')
                ->first();
                
            if (!$mercadoDestino) {
                return response()->json([
                    'success' => false,
                    'message' => 'El mercado destino no existe o no está activo'
                ], 422);
            }

            // TODOS LOS USUARIOS PUEDEN CAMBIAR PRODUCTOS A CUALQUIER MERCADO - Sin restricciones
            
            // Verificar que no es el mismo mercado
            if ($configuracion->idMercado == $validated['nuevoMercadoId']) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto ya pertenece al mercado seleccionado'
                ], 422);
            }
            
            // Ejecutar el stored procedure ODS.SP_UPDATE_CONFIGURACION
            $userId = Auth::user()->idUsuario;
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_UPDATE_CONFIGURACION ?, ?, ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $configuracion->fuente,           // @fuente (obtenida de la configuración actual)
                $validated['nuevoMercadoId'],     // @idMercado
                $userId                           // @idUsuario
            ]);

            // Obtener el nombre del mercado anterior
            $mercadoAnterior = DB::connection('sqlsrv')
                ->table('ODS.TAB_MERCADO')
                ->where('idMercado', $configuracion->idMercado)
                ->value('mercado');

            // Obtener el nombre del producto
            $nombreProducto = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA')
                ->where('codigoPresentacion', $validated['codigoPresentacion'])
                ->value('descripcionPresentacion');

            // Enviar notificación por email
            try {
                \Log::info('DEBUG: Iniciando envío de notificación de movimiento de producto', [
                    'usuario' => Auth::user()->usuario ?? 'Unknown',
                    'user_id' => Auth::user()->idUsuario ?? 'Unknown',
                    'producto' => $validated['codigoPresentacion'],
                    'mercado_anterior' => $mercadoAnterior,
                    'mercado_nuevo' => $mercadoDestino->mercado
                ]);
                
                $notificationService = new NotificationService();
                $notificationService->notifyProductMoved(
                    $validated['codigoPresentacion'],
                    $nombreProducto ?? 'Producto no encontrado',
                    $mercadoAnterior,
                    $mercadoDestino->mercado,
                    Auth::user(),
                    $validated['note'] ?? null
                );
                
                \Log::info('DEBUG: Notificación de movimiento completada exitosamente');
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                \Log::error('Error enviando notificación de movimiento de producto: ' . $e->getMessage());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Producto cambiado exitosamente al mercado: {$mercadoDestino->mercado}",
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'mercadoAnterior' => $configuracion->idMercado,
                'mercadoNuevo' => $validated['nuevoMercadoId'],
                'nombreMercadoNuevo' => $mercadoDestino->mercado,
                'refresh_page' => true // Indicar que se debe refrescar la página
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el mercado del producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from market using stored procedure SP_ASIGNAR_RESTO
     */
    public function removeProduct(Request $request)
    {
        $validated = $request->validate([
            'codigoPresentacion' => 'required|string',
            'note' => 'required|string|max:1000'  // Nota ahora es obligatoria
        ]);

        try {
            // Aumentar el timeout para esta operación específica
            set_time_limit(60);
            
            DB::beginTransaction();
            
            // Verificar que el producto existe en la configuración actual y obtener sus datos
            $configuracionProducto = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION as c')
                ->join('ODS.TAB_MERCADO as m', 'c.idMercado', '=', 'm.idMercado')
                ->select('c.*', 'm.mercado')
                ->where('c.codigo', $validated['codigoPresentacion'])
                ->first();
                
            if (!$configuracionProducto) {
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no se encuentra en la configuración actual'
                ], 404);
            }

            // Obtener el nombre del producto ANTES del SP para evitar consultas post-SP
            $nombreProducto = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA')
                ->where('codigoPresentacion', $validated['codigoPresentacion'])
                ->value('descripcionPresentacion');

            // Ejecutar el stored procedure SP_ASIGNAR_RESTO
            $userId = Auth::user()->idUsuario;
            $executed = DB::connection('sqlsrv')->statement('EXEC ODS.SP_ASIGNAR_RESTO ?, ?', [
                $validated['codigoPresentacion'],  // @codigo
                $userId                           // @idUsuario
            ]);

            DB::commit();

            // Enviar notificación por email DESPUÉS del commit para no afectar la transacción
            try {
                $notificationService = new NotificationService();
                $notificationService->notifyProductRemoved(
                    $validated['codigoPresentacion'],
                    $nombreProducto ?? 'Producto no encontrado',
                    $configuracionProducto->mercado,
                    Auth::user(),
                    $validated['note'] ?? null
                );
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                \Log::error('Error enviando notificación de eliminación de producto: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto removido del mercado exitosamente',
                'codigoPresentacion' => $validated['codigoPresentacion'],
                'refresh_page' => true // Indicar que se debe refrescar la página
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al remover el producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products from RESTO market for assignment
     */
    public function getRestoProducts(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $search = trim($search);
            $cursor = $request->get('cursor');
            $perPage = min(max((int) $request->get('per_page', 50), 20), 100);
            $user = Auth::user(); // Usuario autenticado
            
            // Determinar si es búsqueda global (desde dropdown de mercados) o franquicia específica
            $isGlobalSearch = $request->get('global_search', false);
            $franquiciaFilter = trim($request->get('franquicia_filter', '')); // Para búsqueda global con franquicia específica

            // Obtener filtros individuales
            $filters = [
                'descripcionFF3' => trim($request->get('filter_descripcionFF3', '')),
                'descripcionATC4' => trim($request->get('filter_descripcionATC4', '')),
                'descripcionLaboratorio' => trim($request->get('filter_descripcionLaboratorio', '')),
                'fuente' => trim($request->get('filter_fuente', '')),
                'molecula' => trim($request->get('filter_molecula', '')),
                'descripcionCorporacion' => trim($request->get('filter_descripcionCorporacion', ''))
            ];

            // Query base para productos en RESTO (mercado = 'RESTO')
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->select([
                    'v.codigoPresentacion',
                    'v.descripcionPresentacion', 
                    'v.marcaGenerico',
                    'v.eticoPopular',
                    'v.molecula as descripcionMolecula',
                    'v.descripcionFF3',
                    'v.descripcionATC4',
                    'v.descripcionLaboratorio',
                    'v.descripcionCorporacion',
                    'v.MERCADO',
                    'v.Franquicia', // Agregar franquicia para filtrado
                    'v.descripcionProducto', // Nuevo campo agregado
                    DB::raw("COALESCE(c.fuente, 'IQV') as fuente") // Usar 'IQV' como fuente por defecto
                ])
                ->where('v.MERCADO', 'RESTO');

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto, filtrar productos asignados
            $this->aplicarFiltroGerenteProducto($baseQuery, $user);
            
            if ($user && $user->idRol == 2 && !empty($user->getFranquiciasIds())) {
                // Obtener las franquicias
                $franquicias = $user->getMisFranquicias();
                $nombresFranquicias = collect($franquicias)->pluck('franquicia')->toArray();

                // ESCENARIO 1: Búsqueda global con franquicia específica seleccionada
                if ($isGlobalSearch && !empty($franquiciaFilter)) {
                    $baseQuery->where('v.Franquicia', $franquiciaFilter);
                }
                // ESCENARIO 2: Búsqueda global - mostrar todos los productos RESTO del usuario
                elseif ($isGlobalSearch) {
                    $baseQuery->whereIn('v.Franquicia', $nombresFranquicias);
                }
                // ESCENARIO 3: Búsqueda desde franquicia específica (comportamiento actual)
                else {
                    $baseQuery->whereIn('v.Franquicia', $nombresFranquicias);
                }
            } else {
                // No aplicar filtro adicional para usuarios que no son gerentes de producto
            }

            // Aplicar filtros de búsqueda si existe
            if (!empty($search)) {
                $baseQuery->where(function($query) use ($search) {
                    $query->where('v.codigoPresentacion', 'LIKE', "%{$search}%")
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

            // Aplicar filtros individuales
            if (!empty($filters['descripcionFF3'])) {
                $baseQuery->where('v.descripcionFF3', $filters['descripcionFF3']);
            }
            if (!empty($filters['descripcionATC4'])) {
                $baseQuery->where('v.descripcionATC4', $filters['descripcionATC4']);
            }
            if (!empty($filters['descripcionLaboratorio'])) {
                $baseQuery->where('v.descripcionLaboratorio', $filters['descripcionLaboratorio']);
            }
            if (!empty($filters['fuente'])) {
                $baseQuery->where('c.fuente', $filters['fuente']);
            }
            if (!empty($filters['molecula'])) {
                $baseQuery->where('v.molecula', $filters['molecula']);
            }
            if (!empty($filters['descripcionCorporacion'])) {
                $baseQuery->where('v.descripcionCorporacion', $filters['descripcionCorporacion']);
            }

            // Contar total
            $totalProducts = $baseQuery->count();

            $baseQuery->orderBy('v.codigoPresentacion');

            // Aplicar cursor pagination si existe
            if ($cursor) {
                try {
                    $decodedCursor = base64_decode($cursor);
                    $cursorData = json_decode($decodedCursor, true);
                    
                    if ($cursorData && isset($cursorData['codigoPresentacion'])) {
                        $baseQuery->where('v.codigoPresentacion', '>', $cursorData['codigoPresentacion']);
                    }
                } catch (\Exception $e) {
                    // Si el cursor es inválido, ignorar
                }
            }

            // Obtener productos con límite +1 para verificar si hay más páginas
            
            $productos = $baseQuery->limit($perPage + 1)->get();
            
            // Verificar si hay más páginas
            $hasMorePages = $productos->count() > $perPage;
            if ($hasMorePages) {
                $productos = $productos->take($perPage);
            }

            // Generar cursor para la siguiente página
            $nextCursor = null;
            if ($hasMorePages && $productos->isNotEmpty()) {
                $lastItem = $productos->last();
                $nextCursor = base64_encode(json_encode([
                    'codigoPresentacion' => $lastItem->codigoPresentacion
                ]));
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $productos->toArray(),
                    'total' => $totalProducts,
                    'next_cursor' => $nextCursor,
                    'has_more_pages' => $hasMorePages
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos RESTO: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get filter options for resto products modal
     */
    public function getRestoFilterOptions(Request $request)
    {
        try {
            $user = Auth::user(); // Usuario autenticado
            
            // Determinar si es búsqueda global (desde dropdown de mercados) o franquicia específica
            $isGlobalSearch = $request->get('global_search', false);
            $franquiciaFilter = trim($request->get('franquicia_filter', '')); // Para búsqueda global con franquicia específica
            
            // Base query para RESTO products con filtro de franquicia si aplica
            $baseQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA')
                ->where('MERCADO', 'RESTO');

            // FILTRO POR FRANQUICIA Y GERENTE: Si el usuario es gerente de producto, filtrar productos asignados
            $this->aplicarFiltroGerenteProducto($baseQuery, $user);
            
            if ($user && $user->idRol == 2 && !empty($user->getFranquiciasIds())) {
                // Obtener las franquicias para logging
                $franquicias = $user->getMisFranquicias();
                $nombresFranquicias = collect($franquicias)->pluck('franquicia')->toArray();

                // ESCENARIO 1: Búsqueda global con franquicia específica seleccionada
                if ($isGlobalSearch && !empty($franquiciaFilter)) {
                    $baseQuery->where('Franquicia', $franquiciaFilter);
                }
                // ESCENARIO 2 y 3: Búsqueda global o desde franquicia específica - usar todas las franquicias del usuario
                else {
                    $baseQuery->whereIn('Franquicia', $nombresFranquicias);
                }
            }

            // Get distinct values for each filter field from RESTO products
            $filterOptions = [];

            // FF3 options
            $ff3Options = (clone $baseQuery)
                ->whereNotNull('descripcionFF3')
                ->where('descripcionFF3', '<>', '')
                ->distinct()
                ->orderBy('descripcionFF3')
                ->pluck('descripcionFF3')
                ->toArray();

            // ATC4 options
            $atc4Options = (clone $baseQuery)
                ->whereNotNull('descripcionATC4')
                ->where('descripcionATC4', '<>', '')
                ->distinct()
                ->orderBy('descripcionATC4')
                ->pluck('descripcionATC4')
                ->toArray();

            // Laboratorio options
            $laboratorioOptions = (clone $baseQuery)
                ->whereNotNull('descripcionLaboratorio')
                ->where('descripcionLaboratorio', '<>', '')
                ->distinct()
                ->orderBy('descripcionLaboratorio')
                ->pluck('descripcionLaboratorio')
                ->toArray();

            // Fuente options (con JOIN a TAB_CONFIGURACION)
            $fuenteQuery = DB::connection('sqlsrv')
                ->table('dbo.VMAE_PROD_IQVIA as v')
                ->leftJoin('ODS.TAB_CONFIGURACION as c', 'v.codigoPresentacion', '=', 'c.codigo')
                ->where('v.MERCADO', 'RESTO');
            
            // Aplicar filtro de franquicia y gerente también aquí
            $this->aplicarFiltroGerenteProducto($fuenteQuery, $user);
            
            $fuenteOptions = $fuenteQuery
                ->select(DB::raw("COALESCE(c.fuente, 'IQV') as fuente"))
                ->distinct()
                ->orderBy('fuente')
                ->pluck('fuente')
                ->toArray();

            // Molecula options
            $moleculaOptions = (clone $baseQuery)
                ->whereNotNull('molecula')
                ->where('molecula', '<>', '')
                ->distinct()
                ->orderBy('molecula')
                ->pluck('molecula')
                ->toArray();

            // Corporacion options
            $corporacionOptions = (clone $baseQuery)
                ->whereNotNull('descripcionCorporacion')
                ->where('descripcionCorporacion', '<>', '')
                ->distinct()
                ->orderBy('descripcionCorporacion')
                ->pluck('descripcionCorporacion')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'descripcionFF3' => array_values($ff3Options),
                    'descripcionATC4' => array_values($atc4Options),
                    'descripcionLaboratorio' => array_values($laboratorioOptions),
                    'fuente' => array_values($fuenteOptions),
                    'molecula' => array_values($moleculaOptions),
                    'descripcionCorporacion' => array_values($corporacionOptions)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar opciones de filtros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign multiple products to a market using stored procedure ODS.SP_INSERT_CONFIGURACION
     */
    public function assignProducts(Request $request)
    {
        $validated = $request->validate([
            'idMercado' => 'required|integer',
            'products' => 'required|array|min:1',
            'products.*.code' => 'required|string',
            'products.*.fuente' => 'required|string|min:1' // Asegurar que fuente no esté vacía
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
            $assignedProducts = []; // Para trackear productos asignados exitosamente con código y nombre

            // Procesar cada producto
            foreach ($validated['products'] as $product) {
                try {
                    // Verificar que el producto existe en VMAE_PROD_IQVIA con mercado RESTO y obtener su información
                    $productoInfo = DB::connection('sqlsrv')
                        ->table('dbo.VMAE_PROD_IQVIA')
                        ->where('codigoPresentacion', $product['code'])
                        ->where('MERCADO', 'RESTO')
                        ->select('codigoPresentacion', 'descripcionPresentacion')
                        ->first();
                        
                    if (!$productoInfo) {
                        $errors[] = "Producto {$product['code']} no encontrado en RESTO";
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

                    // PASO 1: Eliminar la configuración existente del producto (que debería estar en RESTO)
                    DB::connection('sqlsrv')
                        ->table('ODS.TAB_CONFIGURACION')
                        ->where('codigo', $product['code'])
                        ->delete();

                    // PASO 2: Ejecutar el stored procedure ODS.SP_INSERT_CONFIGURACION para el nuevo mercado
                    // Obtener el ID del usuario autenticado
                    $userId = Auth::user()->idUsuario;
                    
                    DB::connection('sqlsrv')->statement('EXEC ODS.SP_INSERT_CONFIGURACION ?, ?, ?, ?', [
                        $validated['idMercado'],  // @idMercado
                        $product['code'],         // @codigo
                        $product['fuente'],       // @fuente
                        $userId                   // @idUsuario
                    ]);

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
                try {
                    $notificationService = new NotificationService();
                    // Usar el método genérico para múltiples productos
                    $notificationService->notifyMarketAction('assign_product', [
                        'assigned_products' => $assignedProducts,
                        'market_name' => $mercado->mercado,
                        'assigned_count' => $assignedCount
                    ], Auth::user());
                } catch (\Exception $e) {
                    // Log del error pero no interrumpir el flujo
                    \Log::error('Error enviando notificación de asignación de productos: ' . $e->getMessage());
                }
            }

            DB::commit();

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

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar productos: ' . $e->getMessage()
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
    
    /**
     * Obtener auditoría de mercados
     */
    public function getMarketAudit(Request $request)
    {
        try {
            $marketId = $request->get('market_id');
            $userId = $request->get('user_id');
            $action = $request->get('action');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $limit = min(max((int) $request->get('limit', 50), 10), 200);
            
            // Query base usando la vista de auditoría
            $query = DB::connection('sqlsrv')
                ->table('ODS.VW_MERCADO_AUDIT')
                ->select([
                    'idAuditMercado',
                    'idUsuario',
                    'nombre_usuario',
                    'email_usuario',
                    'idMercado',
                    'nombre_mercado_actual',
                    'accion',
                    'fecha',
                    'mercado_anterior',
                    'mercado_nuevo',
                    'estado_anterior_desc',
                    'estado_nuevo_desc',
                    'observaciones',
                    'tiempo_transcurrido'
                ])
                ->orderBy('fecha', 'desc');
            
            // Aplicar filtros
            if ($marketId) {
                $query->where('idMercado', $marketId);
            }
            
            if ($userId) {
                $query->where('idUsuario', $userId);
            }
            
            if ($action) {
                $query->where('accion', $action);
            }
            
            if ($dateFrom) {
                $query->where('fecha', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $query->where('fecha', '<=', $dateTo . ' 23:59:59');
            }
            
            // Obtener resultados
            $auditRecords = $query->limit($limit)->get();
            
            // Obtener estadísticas adicionales
            $statsQuery = DB::connection('sqlsrv')
                ->table('ODS.VW_MERCADO_AUDIT');
                
            if ($dateFrom) {
                $statsQuery->where('fecha', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $statsQuery->where('fecha', '<=', $dateTo . ' 23:59:59');
            }
            
            $stats = [
                'total_registros' => $statsQuery->count(),
                'por_accion' => $statsQuery
                    ->select('accion')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('accion')
                    ->get()
                    ->pluck('total', 'accion'),
                'usuarios_activos' => $statsQuery
                    ->distinct()
                    ->count('idUsuario'),
                'mercados_modificados' => $statsQuery
                    ->distinct()
                    ->count('idMercado')
            ];
            
            return response()->json([
                'success' => true,
                'data' => $auditRecords->toArray(),
                'total' => $auditRecords->count(),
                'stats' => $stats,
                'filters' => [
                    'market_id' => $marketId,
                    'user_id' => $userId,
                    'action' => $action,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'limit' => $limit
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo auditoría de mercados: ' . $e->getMessage()
            ], 500);
        }
    }
}
