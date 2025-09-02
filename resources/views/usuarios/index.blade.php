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
                    
                    <button type="button" 
                            onclick="abrirModalConfiguracionNotificaciones()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg shadow-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-bell mr-2"></i>
                        Configurar Notificaciones
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
                        Email *
                    </label>
                    <input type="email" 
                           id="crear_email" 
                           name="email" 
                           required
                           readonly
                           maxlength="255"
                           class="w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed focus:ring-1 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
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
            <input type="hidden" id="editar_idUsuario" name="idUsuario">
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

                <!-- Franquicias -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building text-blue-500 mr-1"></i>
                        Franquicias *
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-gray-50">
                        @foreach($franquicias as $idFranquicia => $nombreFranquicia)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="editar_franquicia_{{ $idFranquicia }}" 
                                       name="idFranquicias[]" 
                                       value="{{ $idFranquicia }}"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="editar_franquicia_{{ $idFranquicia }}" class="ml-2 text-sm text-gray-700">
                                    {{ $nombreFranquicia }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_idFranquicias"></div>
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
        
        <!-- Modal Configuración de Notificaciones -->
        <div id="modalConfiguracionNotificaciones" class="fixed inset-0 overflow-y-auto h-full w-full hidden flex items-center justify-center p-4" style="z-index: 999999 !important;" onclick="if(event.target === this) cerrarModalConfiguracionNotificaciones()">
            <div class="relative mx-auto border w-full max-w-3xl shadow-2xl rounded-lg bg-white transform transition-all duration-300 ease-in-out" style="z-index: 9999999 !important;">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-bell text-blue-600 mr-2"></i>
                        <span id="modalConfiguracionTitulo">Configuración de Notificaciones</span>
                    </h3>
                    <button type="button" onclick="cerrarModalConfiguracionNotificaciones()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form id="formConfiguracionNotificaciones">
                        @csrf
                        <input type="hidden" id="config_idConfiguracion" name="idConfiguracion">
                        
                        <!-- Sección para agregar nuevos correos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-plus text-green-600 mr-2"></i>
                                Agregar Nuevo Correo
                            </label>
                            
                            <div class="flex space-x-3">
                                <div class="flex-1">
                                    <input type="email" 
                                           id="nuevoCorreo" 
                                           placeholder="ejemplo@medifarma.com.pe"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                                </div>
                                <button type="button" 
                                        onclick="agregarCorreo()"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Agregar
                                </button>
                            </div>
                            
                            <div class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Ingrese un correo válido y haga clic en "Agregar" para incluirlo en la lista
                            </div>
                        </div>
                        
                        <!-- Lista de correos configurados -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-list text-blue-600 mr-2"></i>
                                Correos Destinatarios Configurados
                            </label>
                            
                            <div id="listaCorreos" class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <!-- Los correos se cargarán dinámicamente aquí -->
                                <div class="text-center text-gray-500 py-4">
                                    <i class="fas fa-inbox text-2xl mb-2"></i>
                                    <p>No hay correos configurados</p>
                                </div>
                            </div>
                            
                            <div class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Marque/desmarque los correos que desea que reciban notificaciones
                            </div>
                        </div>
                        
                        <!-- Estado general de notificaciones -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       id="config_activo" 
                                       name="activo" 
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">
                                    <i class="fas fa-toggle-on text-blue-600 mr-1"></i>
                                    Activar sistema de notificaciones
                                </span>
                            </label>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2 text-sm"></i>
                                <div class="text-sm text-blue-700">
                                    <strong>Nota:</strong> Solo los correos marcados recibirán notificaciones. Las notificaciones también se enviarán al correo del usuario que realiza la acción en el sistema.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                    <button type="button" 
                            onclick="cerrarModalConfiguracionNotificaciones()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>
                    <button type="button" 
                            id="btnEliminarConfiguracion"
                            onclick="eliminarConfiguracionNotificaciones()"
                            class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 hidden">
                        <i class="fas fa-trash mr-1"></i>
                        Eliminar
                    </button>
                    <button type="submit" 
                            form="formConfiguracionNotificaciones"
                            id="btnGuardarConfiguracion"
                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200">
                        <i class="fas fa-save mr-2"></i>
                        <span id="btnGuardarTexto">Guardar</span>
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

@endsection

@push('styles')
<style src="{{ asset('css/usuarios.css') }}"></style>
@endpush

@push('scripts')
<script src="{{ asset('js/usuarios.js') }}"></script>
@endpush
