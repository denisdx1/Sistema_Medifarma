@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="w-full px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-users text-primary mr-2"></i>
                        Gestión de Usuarios
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">Administra usuarios del sistema y sus permisos</p>
                </div>
                <div class="flex space-x-3">
                    <button type="button" 
                            onclick="abrirModalCrear()"
                            class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg shadow-md text-sm font-medium text-white hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Usuario
                    </button>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full px-4 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                        <p class="text-2xl font-bold text-primary">{{ $estadisticas['total'] }}</p>
                    </div>
                    <div class="p-3 bg-primary rounded-lg">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                        <p class="text-2xl font-bold text-secondary">{{ $estadisticas['activos'] }}</p>
                    </div>
                    <div class="p-3 bg-primary rounded-lg">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Administrators -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Administradores</p>
                        <p class="text-2xl font-bold text-primary">{{ $estadisticas['administradores'] }}</p>
                    </div>
                    <div class="p-3 bg-primary rounded-lg">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Inactivos</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $estadisticas['inactivos'] }}</p>
                    </div>
                    <div class="p-3 bg-primary rounded-lg">
                        <i class="fas fa-user-times text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-filter text-primary mr-2"></i>
                    Filtros de Búsqueda
                </h3>
            </div>
            
            <form method="GET" action="{{ route('usuarios.index') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search text-gray-400 mr-1"></i>
                            Búsqueda General
                        </label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Buscar por usuario, email o franquicia..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label for="rol" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tag text-gray-400 mr-1"></i>
                            Rol
                        </label>
                        <select id="rol" 
                                name="rol"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $idRol => $nombreRol)
                                <option value="{{ $idRol }}" {{ request('rol') == $idRol ? 'selected' : '' }}>
                                    {{ $nombreRol }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
                            Estado
                        </label>
                        <select id="estado" 
                                name="estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Activos</option>
                            <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivos</option>
                        </select>
                    </div>

                    <!-- Franchise Filter -->
                    <div>
                        <label for="franquicia" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building text-gray-400 mr-1"></i>
                            Franquicia
                        </label>
                        <select id="franquicia" 
                                name="franquicia"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                            <option value="">Todas las franquicias</option>
                            @foreach($franquicias as $idFranquicia => $nombreFranquicia)
                                <option value="{{ $idFranquicia }}" {{ request('franquicia') == $idFranquicia ? 'selected' : '' }}>
                                    {{ $nombreFranquicia }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex justify-end space-x-3 pt-4 mt-4 border-t border-gray-200">
                    <a href="{{ route('usuarios.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Limpiar
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-lg shadow-md text-sm font-medium text-white hover:from-red-700 hover:to-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 relative z-10">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-table text-green-500 mr-2"></i>
                        Lista de Usuarios
                    </h3>
                    <span class="text-sm text-gray-500">
                        Mostrando {{ $usuarios->count() }} de {{ $usuarios->total() }} usuarios
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 relative z-1">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Login
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Franquicias
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Registro
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($usuarios as $usuario)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <!-- User Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-red-500 to-blue-500 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    {{ strtoupper(substr($usuario->usuario, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $usuario->usuario }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ $usuario->idUsuario }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Login -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $usuario->login }}</div>
                                    <div class="text-sm text-gray-500">Login del usuario</div>
                                </td>

                                <!-- Email -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $usuario->email ?? 'No definido' }}</div>
                                    <div class="text-sm text-gray-500">{{ $usuario->email ? 'Email configurado' : 'Pendiente configurar' }}</div>
                                </td>

                                <!-- Role -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($usuario->idRol == 1)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-shield-alt mr-1"></i>
                                            {{ $usuario->getRoleDisplayName() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            {{ $usuario->getRoleDisplayName() }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Franchise -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $usuario->franquicias_nombres }}</div>
                                    @if($usuario->franquicias_cantidad > 0)
                                        <div class="text-xs text-gray-500">{{ $usuario->franquicias_cantidad }} franquicia{{ $usuario->franquicias_cantidad != 1 ? 's' : '' }}</div>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($usuario->idEstado == 1)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                <!-- Registration Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $usuario->fechaRegistro ? $usuario->fechaRegistro->format('d/m/Y') : 'N/A' }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- Edit Button -->
                                        <button onclick="abrirModalEditar({{ $usuario->idUsuario }})"
                                                class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                                title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Toggle Status Button -->
                                        <button type="button" 
                                                onclick="abrirModalDesactivar({{ $usuario->idUsuario }}, '{{ $usuario->usuario }}', {{ $usuario->idEstado }})"
                                                class="{{ $usuario->idEstado == 1 ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }} transition-colors duration-200"
                                                title="{{ $usuario->idEstado == 1 ? 'Desactivar' : 'Activar' }} usuario">
                                            <i class="fas fa-{{ $usuario->idEstado == 1 ? 'ban' : 'check' }}"></i>
                                        </button>


                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay usuarios</h3>
                                        <p class="text-gray-500">No se encontraron usuarios con los filtros seleccionados.</p>
                                        <button onclick="abrirModalCrear()" 
                                                class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>
                                            Crear primer usuario
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($usuarios->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $usuarios->links() }}
                </div>
            @endif
        </div>



    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
        <span class="text-gray-700">Procesando...</span>
    </div>
</div>

<!-- MODALES FUERA DEL CONTENEDOR PRINCIPAL PARA Z-INDEX CORRECTO -->

<!-- Modal Crear Usuario -->
<div id="modalCrearUsuario" class="fixed inset-0 overflow-y-auto h-full w-full hidden flex items-center justify-center p-2" style="z-index: 999999 !important;">
    <div class="relative mx-auto border w-full max-w-4xl shadow-2xl rounded-lg bg-white" style="z-index: 9999999 !important;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-3 border-b border-gray-200">
            <h3 class="text-base font-semibold text-gray-900">
                <i class="fas fa-user-plus text-primary-600 mr-2"></i>
                Crear Nuevo Usuario
            </h3>
            <button type="button" onclick="cerrarModalCrear()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="formCrearUsuario" class="p-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Nombre Completo -->
                <div>
                    <label for="crear_usuario" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-user text-primary-600 mr-1"></i>
                        Nombre Completo *
                    </label>
                    <input type="text" 
                           id="crear_usuario" 
                           name="usuario" 
                           required
                           maxlength="255"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                           placeholder="Ej: Denis Ruiz">
                    <div class="text-primary-600 text-xs mt-0.5 hidden" id="error_crear_usuario"></div>
                </div>

                <!-- Nombre de Usuario/Login -->
                <div>
                    <label for="crear_login" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-user-circle text-primary-600 mr-1"></i>
                        Usuario *
                    </label>
                    <input type="text" 
                           id="crear_login" 
                           name="login" 
                           required
                           maxlength="50"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                           placeholder="druizp">
                    <div class="text-primary-600 text-xs mt-0.5 hidden" id="error_crear_login"></div>
                </div>

                <!-- Email -->
                <div>
                    <label for="crear_email" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope text-primary-600 mr-1"></i>
                        Email (Opcional)
                    </label>
                    <input type="email" 
                           id="crear_email" 
                           name="email" 
                           maxlength="255"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                           placeholder="ejemplo@medifarma.com">
                    <div class="text-primary-600 text-xs mt-0.5 hidden" id="error_crear_email"></div>
                </div>

                <!-- Contraseña Temporal -->
                <div>
                    <label for="crear_password" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-key text-primary-600 mr-1"></i>
                        Contraseña *
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="crear_password" 
                               name="password" 
                               required
                               minlength="6"
                               class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 pr-8"
                               placeholder="Mínimo 6 caracteres">
                        <button type="button" onclick="togglePassword('crear_password')" class="absolute inset-y-0 right-0 pr-2 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600 text-sm"></i>
                        </button>
                    </div>
                    <div class="text-primary-600 text-xs mt-0.5 hidden" id="error_crear_password"></div>
                </div>

                <!-- Confirmar Contraseña -->
                <div>
                    <label for="crear_password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-key text-primary-600 mr-1"></i>
                        Confirmar *
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="crear_password_confirmation" 
                               name="password_confirmation" 
                               required
                               minlength="6"
                               class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 pr-8"
                               placeholder="Confirmar">
                        <button type="button" onclick="togglePassword('crear_password_confirmation')" class="absolute inset-y-0 right-0 pr-2 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600 text-sm"></i>
                        </button>
                    </div>
                    <div class="text-red-500 text-xs mt-0.5 hidden" id="error_crear_password_confirmation"></div>
                </div>

                <!-- Rol -->
                <div>
                    <label for="crear_idRol" class="block text-xs font-medium text-gray-700 mb-1">
                        <i class="fas fa-user-tag text-primary-600 mr-1"></i>
                        Rol *
                    </label>
                    <select id="crear_idRol" 
                            name="idRol" 
                            required
                            class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                        <option value="">Seleccione un rol</option>
                        @foreach($roles as $idRol => $nombreRol)
                            <option value="{{ $idRol }}">{{ $nombreRol }}</option>
                        @endforeach
                    </select>
                    <div class="text-primary-600 text-xs mt-0.5 hidden" id="error_crear_idRol"></div>
                </div>
            </div>

            <!-- Franquicias -->
            <div class="mt-3">
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    <i class="fas fa-building text-primary-600 mr-1"></i>
                    Franquicias *
                </label>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 max-h-24 overflow-y-auto border border-gray-300 rounded-md p-2 bg-gray-50">
                    @foreach($franquicias as $idFranquicia => $nombreFranquicia)
                        <label class="flex items-center space-x-1 text-xs">
                            <input type="checkbox" 
                                   name="idFranquicias[]" 
                                   value="{{ $idFranquicia }}"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500 w-3 h-3">
                            <span class="text-gray-700 leading-tight">{{ $nombreFranquicia }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="text-primary-600 text-xs mt-0.5 hidden" id="error_crear_idFranquicias"></div>
                <div class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Seleccione al menos una franquicia
                </div>
            </div>

            <!-- Info Message -->
            <div class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded-md">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2 text-sm"></i>
                    <div class="text-xs text-blue-700">
                        <strong>Nota:</strong> El usuario recibirá contraseña temporal y deberá cambiarla en el primer inicio de sesión.
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2 pt-3 mt-3 border-t border-gray-200">
                <button type="button" 
                        onclick="cerrarModalCrear()"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="submit" 
                        id="btnCrearUsuario"
                        class="px-3 py-1.5 text-sm bg-primary text-white rounded-md hover:from-red-700 hover:to-blue-700 transition-all duration-200">
                    <i class="fas fa-save mr-1"></i>
                    Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div id="modalEditarUsuario" class="fixed inset-0 overflow-y-auto h-full w-full hidden flex items-center justify-center p-4" style="z-index: 999999 !important;">
    <div class="relative mx-auto border w-full max-w-2xl shadow-2xl rounded-lg bg-white" style="z-index: 9999999 !important;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-user-edit text-blue-600 mr-2"></i>
                Editar Usuario
            </h3>
            <button type="button" onclick="cerrarModalEditar()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="formEditarUsuario" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre Completo -->
                <div>
                    <label for="editar_usuario" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user text-blue-500 mr-1"></i>
                        Nombre Completo *
                    </label>
                    <input type="text" 
                           id="editar_usuario" 
                           name="usuario" 
                           required
                           maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                           placeholder="Ej: Denis Ruiz">
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_usuario"></div>
                </div>

                <!-- Nombre de Usuario/Login -->
                <div>
                    <label for="editar_login" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-circle text-blue-500 mr-1"></i>
                        Nombre de Usuario *
                    </label>
                    <input type="text" 
                           id="editar_login" 
                           name="login" 
                           required
                           maxlength="50"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                           placeholder="druizp">
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_login"></div>
                </div>

                <!-- Email -->
                <div>
                    <label for="editar_email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope text-blue-500 mr-1"></i>
                        Email (Opcional)
                    </label>
                    <input type="email" 
                           id="editar_email" 
                           name="email" 
                           maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                           placeholder="ejemplo@medifarma.com">
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_email"></div>
                </div>

                <!-- Rol -->
                <div>
                    <label for="editar_idRol" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag text-blue-500 mr-1"></i>
                        Rol *
                    </label>
                    <select id="editar_idRol" 
                            name="idRol" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        <option value="">Seleccione un rol</option>
                        @foreach($roles as $idRol => $nombreRol)
                            <option value="{{ $idRol }}">{{ $nombreRol }}</option>
                        @endforeach
                    </select>
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_idRol"></div>
                </div>

                <!-- Estado -->
                <div>
                    <label for="editar_idEstado" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on text-blue-500 mr-1"></i>
                        Estado *
                    </label>
                    <select id="editar_idEstado" 
                            name="idEstado" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_idEstado"></div>
                </div>

                <!-- Franquicia -->
                <div class="md:col-span-2">
                    <label for="editar_idFranquicia" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building text-blue-500 mr-1"></i>
                        Franquicia *
                    </label>
                    <select id="editar_idFranquicia" 
                            name="idFranquicia" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                        <option value="">Seleccione una franquicia</option>
                        @foreach($franquicias as $idFranquicia => $nombreFranquicia)
                            <option value="{{ $idFranquicia }}">{{ $nombreFranquicia }}</option>
                        @endforeach
                    </select>
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_idFranquicia"></div>
                </div>
            </div>

            <!-- Password Section (Optional) -->
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center mb-3">
                    <i class="fas fa-key text-yellow-600 mr-2"></i>
                    <h4 class="text-sm font-medium text-yellow-800">Cambiar Contraseña (Opcional)</h4>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nueva Contraseña -->
                    <div>
                        <label for="editar_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Nueva Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="editar_password" 
                                   name="password" 
                                   minlength="6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors duration-200 pr-10"
                                   placeholder="Dejar vacío para mantener actual">
                            <button type="button" onclick="togglePassword('editar_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_password"></div>
                    </div>

                    <!-- Confirmar Nueva Contraseña -->
                    <div>
                        <label for="editar_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Nueva Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="editar_password_confirmation" 
                                   name="password_confirmation" 
                                   minlength="6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-colors duration-200 pr-10"
                                   placeholder="Confirmar nueva contraseña">
                            <button type="button" onclick="togglePassword('editar_password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                        <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_password_confirmation"></div>
                    </div>
                </div>
                
                <div class="mt-2 text-sm text-yellow-700">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Si cambia la contraseña, el usuario deberá cambiarla nuevamente en su próximo inicio de sesión.
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200">
                <button type="button" 
                        onclick="cerrarModalEditar()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="submit" 
                        id="btnEditarUsuario"
                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

        <!-- Modal Desactivar Usuario -->
        <div id="modalDesactivarUsuario" class="fixed inset-0 overflow-y-auto h-full w-full hidden flex items-center justify-center p-4" style="z-index: 999999 !important;" onclick="if(event.target === this) cerrarModalDesactivar()">
            <div class="relative mx-auto border w-full max-w-md shadow-2xl rounded-lg bg-white transform transition-all duration-300 ease-in-out" style="z-index: 9999999 !important;">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                        <span id="modalDesactivarTitulo">Confirmar Acción</span>
                    </h3>
                    <button type="button" onclick="cerrarModalDesactivar()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div id="modalDesactivarIcono" class="w-12 h-12 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-2xl"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 id="modalDesactivarMensaje" class="text-base font-medium text-gray-900 mb-2">
                                ¿Está seguro de realizar esta acción?
                            </h4>
                            <p id="modalDesactivarDescripcion" class="text-sm text-gray-600">
                                Esta acción afectará el acceso del usuario al sistema.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                    <button type="button" 
                            onclick="cerrarModalDesactivar()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="button" 
                            id="btnConfirmarDesactivar"
                            onclick="confirmarDesactivarUsuario()"
                            class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <i class="fas fa-check mr-2"></i>
                        <span id="btnConfirmarTexto">Confirmar</span>
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Custom styles for the module */
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Estilos para modales - Z-index máximo para estar por encima de todo */
    #modalCrearUsuario,
    #modalEditarUsuario {
        z-index: 999999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: fixed !important;
    }

    #modalCrearUsuario.hidden,
    #modalEditarUsuario.hidden {
        display: none !important;
    }

    #modalCrearUsuario .relative,
    #modalEditarUsuario .relative {
        z-index: 9999999 !important;
        position: relative !important;
        margin: auto !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        border: 1px solid #e5e7eb !important;
    }

    /* Asegurar que TODO el contenido del módulo tenga z-index muy bajo */
    .min-h-screen > div {
        position: relative;
        z-index: 1 !important;
    }

    .bg-white.rounded-xl.shadow-md {
        position: relative;
        z-index: 1 !important;
    }

    /* Tabla y contenido con z-index muy bajo */
    table, thead, tbody, tr, td, th {
        position: relative;
        z-index: 1 !important;
    }

    /* Header y filtros con z-index bajo */
    .bg-white.rounded-xl.shadow-lg {
        position: relative;
        z-index: 1 !important;
    }

    /* Botones y acciones con z-index bajo */
    button:not(#modalCrearUsuario button):not(#modalEditarUsuario button) {
        position: relative;
        z-index: 1 !important;
    }

    /* Contenedor principal */
    .min-h-screen {
        overflow: visible !important;
        position: relative;
        z-index: 1 !important;
    }

    /* Animaciones básicas para las modales */
    #modalCrearUsuario.show,
    #modalEditarUsuario.show,
    #modalDesactivarUsuario.show {
        z-index: 999999 !important;
    }

    /* Overlay básico para la modal */
    #modalDesactivarUsuario::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
    }
</style>
@endpush

@push('scripts')
<script>
// Variables globales
let usuarioEditandoId = null;
let usuarioDesactivandoId = null;
let usuarioDesactivandoEstado = null;

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    inicializarEventos();
});

// Inicializar eventos
function inicializarEventos() {
    // Eventos de formularios
    document.getElementById('formCrearUsuario').addEventListener('submit', manejarCreacionUsuario);
    document.getElementById('formEditarUsuario').addEventListener('submit', manejarEdicionUsuario);

    // Cerrar modales con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalCrear();
            cerrarModalEditar();
            cerrarModalDesactivar();
        }
    });
}

// ===== FUNCIONES DE MODALES =====

// Abrir modal crear
function abrirModalCrear() {
    limpiarFormularioCrear();
    const modal = document.getElementById('modalCrearUsuario');
    modal.classList.remove('hidden');
    modal.classList.add('show');
    // Asegurar z-index máximo por encima de todo
    modal.style.zIndex = '999999';
    modal.style.position = 'fixed';
    document.getElementById('crear_usuario').focus();
}

// Cerrar modal crear
function cerrarModalCrear() {
    const modal = document.getElementById('modalCrearUsuario');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    limpiarFormularioCrear();
}

// Abrir modal editar
async function abrirModalEditar(idUsuario) {
    try {
        mostrarLoading(true);
        
        const response = await fetch(`/usuarios/${idUsuario}/datos`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            usuarioEditandoId = idUsuario;
            cargarDatosEnFormularioEditar(data.usuario);
            const modal = document.getElementById('modalEditarUsuario');
            modal.classList.remove('hidden');
            modal.classList.add('show');
            // Asegurar z-index máximo por encima de todo
            modal.style.zIndex = '999999';
            modal.style.position = 'fixed';
            document.getElementById('editar_usuario').focus();
        } else {
            mostrarToast('Error al cargar los datos del usuario', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al cargar los datos', 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Cerrar modal editar
function cerrarModalEditar() {
    const modal = document.getElementById('modalEditarUsuario');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    limpiarFormularioEditar();
    usuarioEditandoId = null;
}

// ===== FUNCIONES DE FORMULARIOS =====

// Manejar creación de usuario
async function manejarCreacionUsuario(e) {
    e.preventDefault();
    
    // Validar que al menos una franquicia esté seleccionada
    const checkboxes = document.querySelectorAll('input[name="idFranquicias[]"]:checked');
    if (checkboxes.length === 0) {
        mostrarToast('Debe seleccionar al menos una franquicia', 'error');
        return;
    }
    
    const btn = document.getElementById('btnCrearUsuario');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
        
        limpiarErrores('crear');
        
        const formData = new FormData(e.target);
        
        const response = await fetch('/usuarios', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            cerrarModalCrear();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (response.status === 422) {
                // Errores de validación - data ya contiene la respuesta JSON
                if (data.errors) {
                    mostrarErroresValidacion(data.errors, 'crear');
                }
            } else {
                mostrarToast(data.message || 'Error al crear el usuario', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al crear el usuario', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Manejar edición de usuario
async function manejarEdicionUsuario(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnEditarUsuario');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
        
        limpiarErrores('editar');
        
        const formData = new FormData(e.target);
        
        const response = await fetch(`/usuarios/${usuarioEditandoId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            cerrarModalEditar();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (response.status === 422) {
                // Errores de validación
                const errores = await response.json();
                if (errores.errors) {
                    mostrarErroresValidacion(errores.errors, 'editar');
                }
            } else {
                mostrarToast(data.message || 'Error al actualizar el usuario', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al actualizar el usuario', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== FUNCIONES AUXILIARES =====

// Limpiar formulario crear
function limpiarFormularioCrear() {
    document.getElementById('formCrearUsuario').reset();
    // Desmarcar todos los checkboxes de franquicias
    document.querySelectorAll('input[name="idFranquicias[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    limpiarErrores('crear');
}

// Limpiar formulario editar
function limpiarFormularioEditar() {
    document.getElementById('formEditarUsuario').reset();
    limpiarErrores('editar');
}

// Cargar datos en formulario editar
function cargarDatosEnFormularioEditar(usuario) {
    document.getElementById('editar_idUsuario').value = usuario.idUsuario;
    document.getElementById('editar_usuario').value = usuario.usuario;
    document.getElementById('editar_login').value = usuario.login;
    document.getElementById('editar_email').value = usuario.email || ''; // Campo email agregado
    document.getElementById('editar_idRol').value = usuario.idRol;
    document.getElementById('editar_idEstado').value = usuario.idEstado;
    document.getElementById('editar_idFranquicia').value = usuario.idFranquicia; // Cambiado a idFranquicia
}

// Limpiar errores de validación
function limpiarErrores(prefijo) {
    const campos = ['usuario', 'login', 'email', 'password', 'password_confirmation', 'idRol', 'idFranquicias', 'idEstado'];
    campos.forEach(campo => {
        const errorDiv = document.getElementById(`error_${prefijo}_${campo}`);
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.add('hidden');
        }
        
        const input = document.getElementById(`${prefijo}_${campo}`);
        if (input) {
            input.classList.remove('border-red-500');
        }
    });
}

// Mostrar errores de validación
function mostrarErroresValidacion(errores, prefijo) {
    Object.keys(errores).forEach(campo => {
        const errorDiv = document.getElementById(`error_${prefijo}_${campo}`);
        const input = document.getElementById(`${prefijo}_${campo}`);
        
        if (errorDiv && input) {
            errorDiv.textContent = errores[campo][0];
            errorDiv.classList.remove('hidden');
            input.classList.add('border-red-500');
        }
    });
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Mostrar/ocultar loading
function mostrarLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.remove('hidden');
    } else {
        overlay.classList.add('hidden');
    }
}



// Mostrar toast notifications
function mostrarToast(mensaje, tipo = 'info') {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white z-50 transform translate-x-full transition-transform duration-300`;
    
    // Aplicar color según tipo
    switch(tipo) {
        case 'success':
            toast.classList.add('bg-green-500');
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            break;
        default:
            toast.classList.add('bg-blue-500');
    }
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${tipo === 'success' ? 'check' : tipo === 'error' ? 'times' : tipo === 'warning' ? 'exclamation' : 'info'}-circle mr-2"></i>
            <span>${mensaje}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Mostrar toast
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Ocultar después de 4 segundos
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 4000);
}



// ===== FUNCIONES PARA MODAL DE DESACTIVACIÓN =====

// Abrir modal de desactivación
function abrirModalDesactivar(idUsuario, nombreUsuario, estadoActual) {
    usuarioDesactivandoId = idUsuario;
    usuarioDesactivandoEstado = estadoActual;
    
    const modal = document.getElementById('modalDesactivarUsuario');
    const titulo = document.getElementById('modalDesactivarTitulo');
    const mensaje = document.getElementById('modalDesactivarMensaje');
    const descripcion = document.getElementById('modalDesactivarDescripcion');
    const icono = document.getElementById('modalDesactivarIcono');
    const btnTexto = document.getElementById('btnConfirmarTexto');
    const btnConfirmar = document.getElementById('btnConfirmarDesactivar');
    
    if (estadoActual == 1) {
        // Desactivar usuario
        titulo.textContent = 'Desactivar Usuario';
        mensaje.textContent = `¿Está seguro de desactivar al usuario "${nombreUsuario}"?`;
        descripcion.textContent = 'El usuario no podrá acceder al sistema hasta que sea reactivado.';
        icono.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-red-100';
        icono.innerHTML = '<i class="fas fa-user-times text-red-600 text-2xl"></i>';
        btnTexto.textContent = 'Desactivar';
        btnConfirmar.className = 'px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2';
    } else {
        // Activar usuario
        titulo.textContent = 'Activar Usuario';
        mensaje.textContent = `¿Está seguro de activar al usuario "${nombreUsuario}"?`;
        descripcion.textContent = 'El usuario podrá acceder nuevamente al sistema.';
        icono.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-red-100';
        icono.innerHTML = '<i class="fas fa-user-check text-red-600 text-2xl"></i>';
        btnTexto.textContent = 'Activar';
        btnConfirmar.className = 'px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('show');
    modal.style.zIndex = '999999';
    modal.style.position = 'fixed';
}

// Cerrar modal de desactivación
function cerrarModalDesactivar() {
    const modal = document.getElementById('modalDesactivarUsuario');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    usuarioDesactivandoId = null;
    usuarioDesactivandoEstado = null;
}

// Confirmar desactivación/activación de usuario
async function confirmarDesactivarUsuario() {
    if (!usuarioDesactivandoId) return;
    
    const btn = document.getElementById('btnConfirmarDesactivar');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const response = await fetch(`/usuarios/${usuarioDesactivandoId}/toggle-estado`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        if (response.ok) {
            const accion = usuarioDesactivandoEstado == 1 ? 'desactivado' : 'activado';
            mostrarToast(`Usuario ${accion} exitosamente`, 'success');
            cerrarModalDesactivar();
            setTimeout(() => location.reload(), 1500);
        } else {
            const data = await response.json();
            mostrarToast(data.message || 'Error al cambiar el estado del usuario', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al cambiar el estado del usuario', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Confirmación para acciones peligrosas
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}
</script>
@endpush
