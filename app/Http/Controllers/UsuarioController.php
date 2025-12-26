<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Display a listing of users with enhanced functionality
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Aplicar filtros avanzados
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('usuario', 'like', '%' . $request->search . '%')
                  ->orWhere('login', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('rol')) {
            $query->where('idRol', $request->rol);
        }

        if ($request->filled('estado')) {
            $query->where('idEstado', $request->estado);
        }

        if ($request->filled('franquicia')) {
            $query->where('idFranquicia', $request->franquicia);
        }

        // Ordenación
        $query->orderBy('fechaRegistro', 'desc');

        $usuarios = $query->paginate(15);

        // Procesar franquicias para cada usuario
        $usuarios->getCollection()->transform(function ($usuario) {
            $franquicias = $usuario->getMisFranquicias();
            $nombres = [];
            
            if (is_array($franquicias)) {
                foreach($franquicias as $franquicia) {
                    if (is_array($franquicia) && isset($franquicia['franquicia'])) {
                        $nombre = (string) $franquicia['franquicia'];
                        if (!empty($nombre) && $nombre !== 'N/A') {
                            $nombres[] = $nombre;
                        }
                    }
                }
            }
            
            // Agregar propiedades procesadas al usuario
            $usuario->franquicias_nombres = empty($nombres) ? 'Sin franquicias' : implode(', ', $nombres);
            $usuario->franquicias_cantidad = count($nombres);
            
            return $usuario;
        });

        // Estadísticas mejoradas
        $estadisticas = $this->obtenerEstadisticas();

        // Obtener listas para filtros y modales
        $roles = User::getRoles();
        $franquicias = $this->obtenerFranquicias();

        return view('usuarios.index', compact(
            'usuarios', 
            'estadisticas', 
            'roles', 
            'franquicias'
        ));
    }

    /**
     * Get user data for editing modal
     */
    public function getDatos($id)
    {
        try {
            $usuario = User::findOrFail($id);
            
            // Obtener todas las franquicias del usuario
            $franquiciasUsuario = $usuario->getMisFranquicias();
            $idFranquicias = [];
            
            if (is_array($franquiciasUsuario)) {
                foreach($franquiciasUsuario as $franquicia) {
                    if (is_array($franquicia) && isset($franquicia['idFranquicia'])) {
                        $idFranquicias[] = (int) $franquicia['idFranquicia'];
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'usuario' => [
                    'idUsuario' => $usuario->idUsuario,
                    'usuario' => $usuario->usuario,
                    'login' => $usuario->login,
                    'email' => $usuario->email,
                    'idRol' => $usuario->idRol,
                    'idFranquicias' => $idFranquicias, // Array de franquicias
                    'idEstado' => $usuario->idEstado,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los datos del usuario.'
            ], 404);
        }
    }

    /**
     * Store a newly created user (preparado para SP) - Para modal
     */
    public function store(Request $request)
    {
        // Validaciones
        $request->validate([
            'usuario' => 'required|string|max:255', // Nombre completo
            'login' => 'required|string|max:50',
            'email' => 'required|email|max:255', // Email obligatorio
            'password' => 'required|string|min:6|confirmed',
            'idRol' => ['required', 'integer', Rule::in(array_keys(User::getRoles()))],
            'idFranquicias' => 'required|array|min:1',
            'idFranquicias.*' => 'required|integer',
        ]);

        // Validación personalizada para login único
        $existeLogin = DB::connection('sqlsrv')
            ->table('ODS.TAB_USUARIO')
            ->where('login', $request->login)
            ->exists();
            
        if ($existeLogin) {
            return response()->json([
                'success' => false,
                'message' => 'El login ya está en uso por otro usuario.'
            ], 422);
        }

        try {
            // Convertir array de franquicias a string separado por comas
            $franquiciasIds = implode(',', $request->idFranquicias);
            
            // Hashear la contraseña en PHP usando SHA2_256
            $passwordHashHex = hash('sha256', $request->password); // Formato hexadecimal
            
            // Ejecutar stored procedure con query más simple
            $query = "
                DECLARE @passwordBinary VARBINARY(32) = CONVERT(VARBINARY(32), '{$passwordHashHex}', 2);
                EXEC ODS.SP_INSERT_USUARIO 
                    @idRol = {$request->idRol},
                    @usuario = '{$request->usuario}',
                    @login = '{$request->login}',
                    @email = '{$request->email}',
                    @idFranquicia = '{$franquiciasIds}',
                    @password = @passwordBinary;
            ";
            
            DB::connection('sqlsrv')->statement($query);

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente con ' . count($request->idFranquicias) . ' franquicia(s) asignada(s). El usuario deberá cambiar su contraseña en el primer inicio de sesión.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified user (preparado para SP) - Para modal
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $franquiciasDisponibles = array_keys(User::getFranquicias()); // Obtener solo los IDs

        $request->validate([
            'usuario' => 'required|string|max:255',
            'login' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'idRol' => ['required', 'integer', Rule::in(array_keys(User::getRoles()))],
            'idFranquicias' => 'required|array|min:1',
            'idFranquicias.*' => 'required|integer|in:' . implode(',', $franquiciasDisponibles),
            'idEstado' => 'required|integer|in:1,0',
        ]);

        // Validaciones personalizadas para duplicados
        $existeUsuario = DB::connection('sqlsrv')
            ->table('ODS.TAB_USUARIO')
            ->where('usuario', $request->usuario)
            ->where('idUsuario', '!=', $id)
            ->exists();
            
        if ($existeUsuario) {
            return response()->json([
                'success' => false,
                'message' => 'El nombre de usuario ya está en uso por otro usuario.'
            ], 422);
        }

        $existeLogin = DB::connection('sqlsrv')
            ->table('ODS.TAB_USUARIO')
            ->where('login', $request->login)
            ->where('idUsuario', '!=', $id)
            ->exists();
            
        if ($existeLogin) {
            return response()->json([
                'success' => false,
                'message' => 'El login ya está en uso por otro usuario.'
            ], 422);
        }

        try {
            // Convertir array de franquicias a string separado por comas
            $franquiciasIds = implode(',', $request->idFranquicias);
            
            // Escapar caracteres especiales para evitar problemas de SQL
            $usuario = str_replace("'", "''", $request->usuario);
            $login = str_replace("'", "''", $request->login);
            $email = $request->email ? str_replace("'", "''", $request->email) : '';
            
            // Log para debugging
            \Log::info('Actualizando usuario', [
                'id' => $id,
                'usuario' => $usuario,
                'login' => $login,
                'email' => $email,
                'franquicias' => $franquiciasIds,
                'rol' => $request->idRol
            ]);
            
            // Ejecutar stored procedure para actualizar usuario
            if ($request->filled('password')) {
                // Si se proporciona nueva contraseña, hashearla en PHP
                $passwordHashHex = hash('sha256', $request->password);
                
                // Usar parámetros nombrados para mayor claridad
                $query = "
                    DECLARE @passwordBinary VARBINARY(32) = CONVERT(VARBINARY(32), '{$passwordHashHex}', 2);
                    EXEC ODS.SP_UPDATE_USUARIO 
                        @idUsuario = {$id},
                        @idRol = {$request->idRol},
                        @idFranquicia = '{$franquiciasIds}',
                        @usuario = '{$usuario}',
                        @login = '{$login}',
                        @password = @passwordBinary,
                        @email = '{$email}';
                ";
                
                // Log de la query para debugging
                \Log::info('Query con password', ['query' => $query]);
                
                // Ejecutar la query
                try {
                    DB::connection('sqlsrv')->statement($query);
                    \Log::info('Query ejecutada exitosamente');
                } catch (\Exception $e) {
                    \Log::error('Error ejecutando query', [
                        'error' => $e->getMessage(),
                        'query' => $query
                    ]);
                    throw $e;
                }
            } else {
                // Si no se cambia la contraseña, usar la actual
                $query = "
                    DECLARE @currentPassword VARBINARY(32) = (SELECT password FROM ODS.TAB_USUARIO WHERE idUsuario = {$id});
                    EXEC ODS.SP_UPDATE_USUARIO 
                        @idUsuario = {$id},
                        @idRol = {$request->idRol},
                        @idFranquicia = '{$franquiciasIds}',
                        @usuario = '{$usuario}',
                        @login = '{$login}',
                        @password = @currentPassword,
                        @email = '{$email}';
                ";
                
                // Log de la query para debugging
                \Log::info('Query sin password', ['query' => $query]);
                
                // Ejecutar la query
                try {
                    DB::connection('sqlsrv')->statement($query);
                    \Log::info('Query ejecutada exitosamente');
                } catch (\Exception $e) {
                    \Log::error('Error ejecutando query', [
                        'error' => $e->getMessage(),
                        'query' => $query
                    ]);
                    throw $e;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente con ' . count($request->idFranquicias) . ' franquicia(s) asignada(s).'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Toggle user status (preparado para SP)
     */
    public function toggleEstado($id)
    {
        try {
            $usuario = User::findOrFail($id);
            $nuevoEstado = $usuario->idEstado == 1 ? 0 : 1;

            if ($nuevoEstado == 0) {
                // Si se está desactivando, usar el SP de eliminación (soft delete)
                DB::connection('sqlsrv')->statement('EXEC ODS.SP_DELETE_USUARIO ?', [$id]);
            } else {
                // Si se está activando, actualizar directamente el estado
                DB::connection('sqlsrv')->statement('UPDATE ODS.TAB_USUARIO SET idEstado = ? WHERE idUsuario = ?', [1, $id]);
            }

            $estadoTexto = $nuevoEstado == 1 ? 'activado' : 'desactivado';
            
            // Verificar si es una petición AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Usuario {$estadoTexto} exitosamente."
                ]);
            }
            
            return back()->with('success', "Usuario {$estadoTexto} exitosamente.");

        } catch (\Exception $e) {
            $errorMessage = 'Error al cambiar el estado del usuario: ' . $e->getMessage();
            
            // Verificar si es una petición AJAX
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Delete user using stored procedure (soft delete)
     */
    

    /**
     * Show change password form for first login (vista completa estilo login)
     */
    public function mostrarCambioPassword()
    {
        // Verificar que el usuario tenga una sesión temporal o token especial para cambio de contraseña
        // TODO: Implementar verificación de token temporal
        
        return view('usuarios.primer-cambio-password');
    }

    /**
     * Handle password change for first login (preparado para SP)
     */
    public function cambiarPasswordObligatorio(Request $request)
    {
        $request->validate([
            'password_temporal' => 'required',
            'password' => 'required|string|min:6|confirmed|different:password_temporal',
        ], [
            'password_temporal.required' => 'La contraseña temporal es requerida.',
            'password.required' => 'La nueva contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.different' => 'La nueva contraseña debe ser diferente a la temporal.',
        ]);

        try {
            $usuario = auth()->user(); // Usar el usuario autenticado actual

            // Verificar contraseña temporal usando el método del modelo
            if (!$usuario->verificarPassword($request->password_temporal)) {
                return back()->with('error', 'La contraseña temporal no es correcta.');
            }

            // Hashear la nueva contraseña
            $passwordHashHex = hash('sha256', $request->password); // Formato hexadecimal
            
            // Obtener las franquicias del usuario como string separado por comas
            $franquiciasIds = implode(',', $usuario->getFranquiciasIds());
            
            // Actualizar usando stored procedure con todos los parámetros requeridos
            $query = "
                DECLARE @passwordBinary VARBINARY(32) = CONVERT(VARBINARY(32), '{$passwordHashHex}', 2);
                EXEC ODS.SP_UPDATE_USUARIO 
                    @idUsuario = {$usuario->idUsuario},
                    @idRol = {$usuario->idRol},
                    @usuario = '{$usuario->usuario}',
                    @login = '{$usuario->login}',
                    @email = '{$usuario->email}',
                    @idFranquicia = '{$franquiciasIds}',
                    @password = @passwordBinary,
                    @idEstado = {$usuario->idEstado};
            ";
            
            DB::connection('sqlsrv')->statement($query);

            return redirect()->route('market-management.index')
                ->with('success', 'Contraseña actualizada exitosamente. ¡Bienvenido al sistema!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar la contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Get user statistics
     */
    private function obtenerEstadisticas()
    {
        return [
            'total' => User::count(),
            'activos' => User::where('idEstado', 1)->count(),
            'inactivos' => User::where('idEstado', 0)->count(),
            'administradores' => User::where('idRol', User::ROLE_ADMIN)->count(),
            'gerentes_producto' => User::where('idRol', User::ROLE_GERENTE_PRODUCTO)->count(),
        ];
    }

    /**
     * Get available franchises from DTM_VENTAS.ODS.TAB_FRANQUICIA
     */
    private function obtenerFranquicias()
    {
        return User::getFranquicias();
    }

    /**
     * Export users data (preparado para futuras implementaciones)
     */
    public function exportar(Request $request)
    {
        // TODO: Implementar exportación de usuarios
        return back()->with('info', 'Funcionalidad de exportación en desarrollo.');
    }

    /**
     * Middleware para verificar si requiere cambio de contraseña
     * Este método será llamado desde el middleware correspondiente
     */
    public static function requiereCambioPassword($usuario)
    {
        // TODO: Verificar campo en BD que indique si requiere cambio de contraseña
        // Por ahora retorna false para evitar bucles infinitos
        return false;
    }
    
    // ===== MÉTODOS PARA CONFIGURACIÓN DE NOTIFICACIONES =====
    
    /**
     * Obtener configuración de notificaciones
     */
    public function getConfiguracionNotificaciones()
    {
        try {
            // Obtener configuración con TODOS los correos combinados
            $configuracion = \App\Models\ConfiguracionNotificacion::getConfiguracionConTodosLosCorreos();
            
            // Debug: obtener información de la tabla
            $debug = \App\Models\ConfiguracionNotificacion::debugTabla();
            
            return response()->json([
                'success' => true,
                'configuracion' => $configuracion,
                'debug' => $debug
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la configuración: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear nueva configuración de notificaciones
     */
    public function storeConfiguracionNotificaciones(Request $request)
    {
        $request->validate([
            'correosDestinatarios' => 'required|string|max:1000',
            'activo' => 'required|boolean'
        ], [
            'correosDestinatarios.required' => 'Los correos destinatarios son requeridos.',
            'correosDestinatarios.max' => 'Los correos destinatarios no pueden exceder 1000 caracteres.'
        ]);
        
        try {
            $resultado = \App\Models\ConfiguracionNotificacion::crearConfiguracion($request->correosDestinatarios);
            
            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la configuración: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar configuración de notificaciones
     */
    public function updateConfiguracionNotificaciones(Request $request, $id)
    {
        $request->validate([
            'correosDestinatarios' => 'required|string|max:1000',
            'activo' => 'required|boolean'
        ], [
            'correosDestinatarios.required' => 'Los correos destinatarios son requeridos.',
            'correosDestinatarios.max' => 'Los correos destinatarios no pueden exceder 1000 caracteres.'
        ]);
        
        try {
            $resultado = \App\Models\ConfiguracionNotificacion::actualizarConfiguracion($id, $request->correosDestinatarios);
            
            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar configuración de notificaciones
     */
    public function destroyConfiguracionNotificaciones($id)
    {
        try {
            $resultado = \App\Models\ConfiguracionNotificacion::eliminarConfiguracion($id);
            
            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la configuración: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configurar control de acceso al sistema
     */
    public function configurarAcceso(Request $request)
    {
        try {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio'
            ]);

            // Guardar configuración en archivo JSON
            $config = [
                'habilitado' => false,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'configurado_por' => auth()->user()->usuario,
                'configurado_en' => now()->toISOString()
            ];

            $configPath = storage_path('app/access_control.json');
            file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Control de acceso configurado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al configurar el control de acceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deshabilitar control de acceso al sistema
     */
    public function deshabilitarAcceso(Request $request)
    {
        try {
            // Eliminar archivo de configuración
            $configPath = storage_path('app/access_control.json');
            if (file_exists($configPath)) {
                unlink($configPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Control de acceso deshabilitado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al deshabilitar el control de acceso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estado actual del control de acceso
     */
    public function estadoAcceso(Request $request)
    {
        try {
            $configPath = storage_path('app/access_control.json');
            
            if (!file_exists($configPath)) {
                return response()->json([
                    'habilitado' => true,
                    'fecha_inicio' => null,
                    'fecha_fin' => null
                ]);
            }

            $config = json_decode(file_get_contents($configPath), true);
            $now = now();

            // Verificar si estamos dentro del período de deshabilitación
            $fechaInicio = \Carbon\Carbon::parse($config['fecha_inicio']);
            $fechaFin = \Carbon\Carbon::parse($config['fecha_fin']);

            $deshabilitado = $now->between($fechaInicio, $fechaFin);

            return response()->json([
                'habilitado' => !$deshabilitado,
                'fecha_inicio' => $config['fecha_inicio'],
                'fecha_fin' => $config['fecha_fin'],
                'configurado_por' => $config['configurado_por'] ?? null,
                'configurado_en' => $config['configurado_en'] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'habilitado' => true,
                'fecha_inicio' => null,
                'fecha_fin' => null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener lista de usuarios para excepciones
     */
    public function obtenerUsuariosExcepcionales(Request $request)
    {
        try {
            // Obtener todos los usuarios (incluyendo administradores para debug)
            $usuarios = User::select('idUsuario', 'login', 'email', 'idRol')
                ->get();

            // Filtrar usuarios que no son administradores
            $usuariosNoAdmin = $usuarios->filter(function($usuario) {
                return $usuario->idRol !== 1; // Asumiendo que 1 es el ID del rol administrador
            })->values();

            // Obtener usuarios excepcionales actuales
            $exceptionsPath = storage_path('app/user_exceptions.json');
            $usuariosExcepcionales = [];
            
            if (file_exists($exceptionsPath)) {
                $exceptions = json_decode(file_get_contents($exceptionsPath), true);
                $usuariosExcepcionales = $exceptions['users'] ?? [];
            }

            // Log para debug
            \Log::info('Usuarios encontrados:', [
                'total' => $usuarios->count(),
                'no_admin' => $usuariosNoAdmin->count(),
                'roles' => $usuarios->pluck('role')->unique()->toArray()
            ]);

            return response()->json([
                'usuarios' => $usuariosNoAdmin,
                'usuariosExcepcionales' => $usuariosExcepcionales,
                'debug' => [
                    'total_usuarios' => $usuarios->count(),
                    'usuarios_no_admin' => $usuariosNoAdmin->count(),
                    'roles_encontrados' => $usuarios->pluck('role')->unique()->toArray()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener usuarios excepcionales: ' . $e->getMessage());
            return response()->json([
                'usuarios' => [],
                'usuariosExcepcionales' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar usuarios excepcionales
     */
    public function guardarUsuariosExcepcionales(Request $request)
    {
        try {
            $usuariosSeleccionados = $request->input('usuarios', []);
            
            $exceptionsPath = storage_path('app/user_exceptions.json');
            
            $data = [
                'users' => $usuariosSeleccionados,
                'configurado_por' => auth()->user()->login,
                'configurado_en' => now()->toISOString()
            ];
            
            file_put_contents($exceptionsPath, json_encode($data, JSON_PRETTY_PRINT));
            
            return response()->json([
                'success' => true,
                'message' => 'Usuarios excepcionales guardados correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar usuarios excepcionales: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener información de configuración automática
     */
    public function obtenerInfoAutoConfig(Request $request)
    {
        try {
            $autoService = new \App\Services\AutoAccessControlService();
            $info = $autoService->getAutoConfigInfo();
            
            return response()->json([
                'success' => true,
                'data' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de configuración automática: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Procesar configuración automática manualmente
     */
    public function procesarAutoConfig(Request $request)
    {
        try {
            $autoService = new \App\Services\AutoAccessControlService();
            $result = $autoService->processAutoAccessControl();
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar configuración automática: ' . $e->getMessage()
            ]);
        }
    }
}
