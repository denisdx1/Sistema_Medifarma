@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestión de Usuarios</h1>
                    <p class="text-sm text-gray-600 mt-1">Administra usuarios del sistema y sus permisos</p>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="window.location.href='{{ route('users.create') }}'" 
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-blue-600 border border-transparent rounded-lg shadow-md text-sm font-medium text-white hover:from-red-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalUsers }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                        <p class="text-2xl font-bold text-green-600">{{ $activeUsers }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Administradores</p>
                        <p class="text-2xl font-bold text-red-600">{{ $adminUsers }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Inactivos</p>
                        <p class="text-2xl font-bold text-red-600">{{ $inactiveUsers }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg">
                        <i class="fas fa-user-times text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h3>
                <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                            <div class="relative">
                                <input type="text" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       placeholder="Nombre o email..."
                                       class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="role_filter" class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                            <select id="role_filter" 
                                    name="role" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                                <option value="">Todos los roles</option>
                                @foreach(\App\Models\User::getRoles() as $role => $displayName)
                                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                        {{ $displayName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select id="status_filter" 
                                    name="status" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200">
                                <option value="">Todos</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <a href="{{ route('users.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            Limpiar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-gradient-to-r from-red-600 to-blue-600 text-white rounded-lg hover:from-red-700 hover:to-blue-700 transition-all duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Lista de Usuarios</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permisos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-red-500 to-blue-500 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($user->role === 'administrador') bg-red-100 text-red-800
                                        @elseif($user->role === 'gerente_producto') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $user->getRoleDisplayName() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $user->department ?? 'Sin asignar' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($user->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                        @if($user->is_active)
                                            <i class="fas fa-check-circle mr-1"></i> Activo
                                        @else
                                            <i class="fas fa-times-circle mr-1"></i> Inactivo
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($user->role === 'administrador')
                                            <span class="text-red-600 font-medium">Todos los módulos</span>
                                        @elseif($user->permissions && count($user->permissions) > 0)
                                            @foreach($user->permissions as $permission)
                                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1">
                                                    {{ \App\Models\User::getModules()[$permission] ?? $permission }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-500">Sin permisos</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('users.edit', $user) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="toggleUserStatus({{ $user->id }}, {{ $user->is_active ? 'false' : 'true' }})"
                                                class="text-{{ $user->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $user->is_active ? 'yellow' : 'green' }}-900 transition-colors duration-200">
                                            <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg">No se encontraron usuarios</p>
                                    <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- JavaScript para funciones adicionales -->
<script>
function toggleUserStatus(userId, newStatus) {
    if (confirm('¿Estás seguro de que quieres cambiar el estado de este usuario?')) {
        // Aquí puedes implementar la lógica AJAX para cambiar el estado
        fetch(`/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ is_active: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al cambiar el estado del usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cambiar el estado del usuario');
        });
    }
}
</script>
@endsection
