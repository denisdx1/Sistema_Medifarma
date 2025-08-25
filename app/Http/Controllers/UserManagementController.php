<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('usuario', 'like', '%' . $request->search . '%')
                  ->orWhere('login', 'like', '%' . $request->search . '%')
                  ->orWhere('franquicia', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('idRol', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('idEstado', $request->status);
        }

        if ($request->filled('franquicia')) {
            $query->where('franquicia', $request->franquicia);
        }

        $users = $query->orderBy('fechaRegistro', 'desc')->paginate(15);

        // Estadísticas
        $totalUsers = User::count();
        $activeUsers = User::where('idEstado', 1)->count(); // 1 = ACTIVO
        $inactiveUsers = User::where('idEstado', 2)->count(); // 2 = INACTIVO
        $adminUsers = User::where('idRol', 1)->count(); // 1 = ADMINISTRADOR

        $estadisticas = [
            'total' => $totalUsers,
            'activos' => $activeUsers,
            'inactivos' => $inactiveUsers,
            'administradores' => $adminUsers
        ];

        // Obtener roles y franquicias para filtros
        $roles = User::getRoles();
        $franquicias = User::select('franquicia')
            ->distinct()
            ->whereNotNull('franquicia')
            ->orderBy('franquicia')
            ->pluck('franquicia');

        return view('usuarios.index', compact('users', 'estadisticas', 'roles', 'franquicias'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = User::getRoles();
        
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|max:255',
            'login' => 'required|string|max:50|unique:ODS.TAB_USUARIO,login',
            'email' => 'nullable|string|email|max:255', // Campo opcional por ahora
            'password' => 'required|string|min:6|confirmed',
            'idRol' => ['required', Rule::in(array_keys(User::getRoles()))],
            'franquicia' => 'required|string|max:50',
            'nueva_franquicia' => 'nullable|string|max:50',
        ]);

        // Si se especificó una nueva franquicia, usarla
        $franquicia = $request->franquicia === 'NUEVA' && $request->nueva_franquicia 
            ? $request->nueva_franquicia 
            : $request->franquicia;

        User::create([
            'usuario' => $request->usuario,
            'login' => $request->login,
            'email' => $request->email, // Campo para futura implementación
            'password' => Hash::make($request->password),
            'idRol' => $request->idRol,
            'franquicia' => $franquicia,
            'fechaRegistro' => now(),
            'idEstado' => 1, // Estado activo por defecto
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente.'
        ]);
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        $roles = User::getRoles();
        
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'usuario' => 'required|string|max:255',
            'login' => ['required', 'string', 'max:50', Rule::unique('ODS.TAB_USUARIO', 'login')->ignore($user->idUsuario, 'idUsuario')],
            'email' => 'nullable|string|email|max:255', // Campo opcional por ahora
            'password' => 'nullable|string|min:6|confirmed',
            'idRol' => ['required', Rule::in(array_keys(User::getRoles()))],
            'franquicia' => 'required|string|max:50',
            'nueva_franquicia' => 'nullable|string|max:50',
            'idEstado' => 'boolean',
        ]);

        // Si se especificó una nueva franquicia, usarla
        $franquicia = $request->franquicia === 'NUEVA' && $request->nueva_franquicia 
            ? $request->nueva_franquicia 
            : $request->franquicia;

        $data = [
            'usuario' => $request->usuario,
            'login' => $request->login,
            'email' => $request->email, // Campo para futura implementación
            'idRol' => $request->idRol,
            'franquicia' => $franquicia,
            'idEstado' => $request->boolean('idEstado', true) ? 1 : 2, // 1=ACTIVO, 2=INACTIVO
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente.'
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->idEstado == 1 ? 2 : 1; // 1=ACTIVO, 2=INACTIVO
        $user->update(['idEstado' => $newStatus]);
        
        $status = $newStatus == 1 ? 'activado' : 'desactivado';
        
        return response()->json([
            'success' => true,
            'message' => "Usuario {$status} exitosamente.",
            'new_status' => $newStatus
        ]);
    }

    /**
     * Get user stats for dashboard
     */
    public function getStats()
    {
        $stats = [
            'total' => User::count(),
            'active' => User::where('idEstado', 1)->count(),
            'inactive' => User::where('idEstado', 2)->count(),
            'admins' => User::where('idRol', User::ROLE_ADMIN)->count(),
            'product_managers' => User::where('idRol', User::ROLE_GERENTE_PRODUCTO)->count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Get a specific user for editing
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'usuario' => $user
        ]);
    }
}
