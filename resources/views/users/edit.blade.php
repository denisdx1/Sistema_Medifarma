@extends('layouts.app')

@section('title', 'Editar Usuario')
@section('page-title', 'Editar Usuario')

@section('content')
<div class="container mx-auto px-6 py-8">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
            <i class="fas fa-arrow-left text-xl"></i>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-user-edit text-red-600 mr-3"></i>
                Editar Usuario
            </h1>
            <p class="text-gray-600 mt-2">Modifica la información de {{ $user->name }}</p>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-red-500 mr-2"></i>
                        Nombre Completo *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('name') border-red-500 @enderror" 
                           placeholder="Ingresa el nombre completo"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-red-500 mr-2"></i>
                        Correo Electrónico *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('email') border-red-500 @enderror" 
                           placeholder="usuario@example.com"
                           required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock text-red-500 mr-2"></i>
                        Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('password') border-red-500 @enderror" 
                           placeholder="Dejar en blanco para mantener la actual">
                    <p class="text-sm text-gray-500 mt-1">Dejar en blanco si no deseas cambiar la contraseña</p>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock text-red-500 mr-2"></i>
                        Confirmar Nueva Contraseña
                    </label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                           placeholder="Confirmar nueva contraseña">
                </div>

                <!-- Role -->
                <div class="mb-6">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag text-red-500 mr-2"></i>
                        Rol *
                    </label>
                    <select id="role" 
                            name="role" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('role') border-red-500 @enderror"
                            required>
                        @foreach($roles as $roleKey => $roleName)
                            <option value="{{ $roleKey }}" {{ old('role', $user->role) == $roleKey ? 'selected' : '' }}>
                                @switch($roleKey)
                                    @case('administrador')
                                        👑 Administrador
                                        @break
                                    @case('gerente_producto')
                                        📦 Gerente de Producto
                                        @break
                                    @case('business_intelligence')
                                        📊 Business Intelligence
                                        @break
                                    @default
                                        👤 {{ $roleName }}
                                @endswitch
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div class="mb-6">
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building text-red-500 mr-2"></i>
                        Departamento
                    </label>
                    <input type="text" 
                           id="department" 
                           name="department" 
                           value="{{ old('department', $user->department) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('department') border-red-500 @enderror" 
                           placeholder="Ej: TI, Ventas, Marketing">
                    @error('department')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Permisos de Módulos -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-key text-red-500 mr-2"></i>
                        Permisos de Módulos
                    </label>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-xs text-gray-500 mb-4">Selecciona los módulos a los que el usuario tendrá acceso. Los administradores tienen acceso a todos los módulos por defecto.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach(\App\Models\User::getModules() as $module => $displayName)
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           id="permission_{{ $module }}" 
                                           name="permissions[]" 
                                           value="{{ $module }}"
                                           {{ in_array($module, $user->permissions ?? []) ? 'checked' : '' }}
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <label for="permission_{{ $module }}" class="ml-2 text-sm text-gray-700">
                                        {{ $displayName }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" onclick="toggleAllPermissions()" 
                                    class="text-sm text-red-600 hover:text-red-800">
                                Seleccionar/Deseleccionar Todos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Active Status -->
                <div class="mb-8">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            <i class="fas fa-user-check text-green-500 mr-1"></i>
                            Usuario activo (puede iniciar sesión)
                        </label>
                    </div>
                </div>

                <!-- User Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Información del Usuario</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-medium">Creado:</span> 
                            {{ $user->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <span class="font-medium">Último acceso:</span> 
                            {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-between">
                    <a href="{{ route('users.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </a>
                    
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });
}

// Deshabilitar permisos cuando se selecciona administrador
document.getElementById('role').addEventListener('change', function() {
    const permissionCheckboxes = document.querySelectorAll('input[name="permissions[]"]');
    const isAdmin = this.value === 'administrador';
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.disabled = isAdmin;
        if (isAdmin) {
            checkbox.checked = false;
        }
    });
    
    // Mostrar mensaje informativo
    const permissionSection = document.querySelector('.mb-6:has(input[name="permissions[]"])');
    const infoMessage = permissionSection.querySelector('.admin-info');
    
    if (isAdmin && !infoMessage) {
        const info = document.createElement('div');
        info.className = 'admin-info text-sm text-blue-600 mt-2 p-2 bg-blue-50 rounded';
        info.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Los administradores tienen acceso automático a todos los módulos.';
        permissionSection.appendChild(info);
    } else if (!isAdmin && infoMessage) {
        infoMessage.remove();
    }
});

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect.value === 'administrador') {
        roleSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
