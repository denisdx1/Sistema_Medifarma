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
            return response()->json([
                'success' => true,
                'usuario' => [
                    'idUsuario' => $usuario->idUsuario,
                    'usuario' => $usuario->usuario,
                    'login' => $usuario->login,
                    'email' => $usuario->email, // Campo email agregado
                    'idRol' => $usuario->idRol,
                    'idFranquicia' => $usuario->idFranquicia, // Cambiado de franquicia a idFranquicia
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
            'email' => 'nullable|email|max:255', // Email opcional por ahora
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
            'usuario' => 'required|string|max:255', // Aumentado para nombre completo
            'login' => 'required|string|max:50', // Cambiado de email a string
            'email' => 'nullable|email|max:255', // Campo email opcional
            'password' => 'nullable|string|min:6|confirmed',
            'idRol' => ['required', 'integer', Rule::in(array_keys(User::getRoles()))],
            'idFranquicia' => [
                'required',
                'integer',
                Rule::in($franquiciasDisponibles)
            ],
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
            // Ejecutar stored procedure para actualizar usuario
            if ($request->filled('password')) {
                // Si se proporciona nueva contraseña, hashearla en PHP
                $passwordHashHex = hash('sha256', $request->password); // Formato hexadecimal
                
                DB::connection('sqlsrv')->statement('
                    DECLARE @passwordBinary VARBINARY(32) = CONVERT(VARBINARY(32), ?, 2);
                    EXEC ODS.SP_UPDATE_USUARIO ?, ?, ?, ?, ?, @passwordBinary, ?;
                ', [
                    $passwordHashHex,
                    $id,
                    $request->idRol,
                    $request->idFranquicia,
                    $request->usuario,
                    $request->login,
                    $request->email
                ]);
            } else {
                // Si no se cambia la contraseña, usar la actual
                DB::connection('sqlsrv')->statement('
                    EXEC ODS.SP_UPDATE_USUARIO ?, ?, ?, ?, ?, 
                    (SELECT password FROM ODS.TAB_USUARIO WHERE idUsuario = ?), ?', [
                    $id,
                    $request->idRol,
                    $request->idFranquicia,
                    $request->usuario,
                    $request->login,
                    $id,
                    $request->email
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente.'
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
}
