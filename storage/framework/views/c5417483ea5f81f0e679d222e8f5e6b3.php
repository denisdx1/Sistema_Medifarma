

<?php $__env->startSection('title', 'Gestión de Usuarios'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-users text-red-600 mr-2"></i>
                        Gestión de Usuarios
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">Administra usuarios del sistema y sus permisos</p>
                </div>
                <div class="flex space-x-3">
                    <button type="button" 
                            onclick="abrirModalCrear()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-red-800 border border-transparent rounded-lg shadow-md text-sm font-medium text-white hover:from-red-700 hover:to-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
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
            <!-- Total Users -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo e($estadisticas['total']); ?></p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo e($estadisticas['activos']); ?></p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Administrators -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Administradores</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo e($estadisticas['administradores']); ?></p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-red-500 to-red-600 rounded-lg">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Usuarios Inactivos</p>
                        <p class="text-2xl font-bold text-gray-600"><?php echo e($estadisticas['inactivos']); ?></p>
                    </div>
                    <div class="p-3 bg-gradient-to-r from-gray-500 to-gray-600 rounded-lg">
                        <i class="fas fa-user-times text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filtros de Búsqueda
                </h3>
            </div>
            
            <form method="GET" action="<?php echo e(route('usuarios.index')); ?>" class="p-6">
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
                               value="<?php echo e(request('search')); ?>"
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
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idRol => $nombreRol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($idRol); ?>" <?php echo e(request('rol') == $idRol ? 'selected' : ''); ?>>
                                    <?php echo e($nombreRol); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                            <option value="1" <?php echo e(request('estado') == '1' ? 'selected' : ''); ?>>Activos</option>
                            <option value="0" <?php echo e(request('estado') == '0' ? 'selected' : ''); ?>>Inactivos</option>
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
                            <?php $__currentLoopData = $franquicias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idFranquicia => $nombreFranquicia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($idFranquicia); ?>" <?php echo e(request('franquicia') == $idFranquicia ? 'selected' : ''); ?>>
                                    <?php echo e($nombreFranquicia); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex justify-end space-x-3 pt-4 mt-4 border-t border-gray-200">
                    <a href="<?php echo e(route('usuarios.index')); ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i>
                        Limpiar
                    </a>
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-600 to-red-800 border border-transparent rounded-lg shadow-md text-sm font-medium text-white hover:from-red-700 hover:to-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                        <i class="fas fa-search mr-2"></i>
                        Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-table text-green-500 mr-2"></i>
                        Lista de Usuarios
                    </h3>
                    <span class="text-sm text-gray-500">
                        Mostrando <?php echo e($usuarios->count()); ?> de <?php echo e($usuarios->total()); ?> usuarios
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
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
                                Franquicia
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
                        <?php $__empty_1 = true; $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <!-- User Info -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-red-500 to-blue-500 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    <?php echo e(strtoupper(substr($usuario->usuario, 0, 2))); ?>

                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo e($usuario->usuario); ?></div>
                                            <div class="text-sm text-gray-500">ID: <?php echo e($usuario->idUsuario); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Login -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo e($usuario->login); ?></div>
                                    <div class="text-sm text-gray-500">Login del usuario</div>
                                </td>

                                <!-- Email -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo e($usuario->email ?? 'No definido'); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($usuario->email ? 'Email configurado' : 'Pendiente configurar'); ?></div>
                                </td>

                                <!-- Role -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($usuario->idRol == 1): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-shield-alt mr-1"></i>
                                            <?php echo e($usuario->getRoleDisplayName()); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            <?php echo e($usuario->getRoleDisplayName()); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Franchise -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo e($usuario->getFranquiciaNombre()); ?></div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($usuario->idEstado == 1): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Registration Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($usuario->fechaRegistro ? $usuario->fechaRegistro->format('d/m/Y') : 'N/A'); ?>

                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- Edit Button -->
                                        <button onclick="abrirModalEditar(<?php echo e($usuario->idUsuario); ?>)"
                                                class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                                title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Toggle Status Button -->
                                        <form method="POST" action="<?php echo e(route('usuarios.toggle-estado', $usuario->idUsuario)); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" 
                                                    class="<?php echo e($usuario->idEstado == 1 ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'); ?> transition-colors duration-200"
                                                    title="<?php echo e($usuario->idEstado == 1 ? 'Desactivar' : 'Activar'); ?> usuario"
                                                    onclick="return confirm('¿Está seguro de <?php echo e($usuario->idEstado == 1 ? 'desactivar' : 'activar'); ?> este usuario?')">
                                                <i class="fas fa-<?php echo e($usuario->idEstado == 1 ? 'ban' : 'check'); ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Button -->
                                        <?php if(auth()->user()->idUsuario != $usuario->idUsuario): ?>
                                        <button onclick="eliminarUsuario(<?php echo e($usuario->idUsuario); ?>, '<?php echo e($usuario->usuario); ?>')"
                                                class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                title="Eliminar usuario">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay usuarios</h3>
                                        <p class="text-gray-500">No se encontraron usuarios con los filtros seleccionados.</p>
                                        <button onclick="abrirModalCrear()" 
                                                class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>
                                            Crear primer usuario
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($usuarios->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($usuarios->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        <!-- Modal Crear Usuario -->
        <div id="modalCrearUsuario" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-user-plus text-red-600 mr-2"></i>
                        Crear Nuevo Usuario
                    </h3>
                    <button type="button" onclick="cerrarModalCrear()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="formCrearUsuario" class="p-6">
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre Completo -->
                        <div>
                            <label for="crear_usuario" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user text-red-500 mr-1"></i>
                                Nombre Completo *
                            </label>
                            <input type="text" 
                                   id="crear_usuario" 
                                   name="usuario" 
                                   required
                                   maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                                   placeholder="Ej: Denis Ruiz">
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_usuario"></div>
                        </div>

                        <!-- Nombre de Usuario/Login -->
                        <div>
                            <label for="crear_login" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-circle text-red-500 mr-1"></i>
                                Nombre de Usuario *
                            </label>
                            <input type="text" 
                                   id="crear_login" 
                                   name="login" 
                                   required
                                   maxlength="50"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                                   placeholder="Ej: druizp">
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_login"></div>
                        </div>

                        <!-- Email (para futuro uso) -->
                        <div>
                            <label for="crear_email" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope text-red-500 mr-1"></i>
                                Email (Opcional)
                            </label>
                            <input type="email" 
                                   id="crear_email" 
                                   name="email" 
                                   maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                                   placeholder="ejemplo@medifarma.com">
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_email"></div>
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label for="crear_password" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock text-red-500 mr-1"></i>
                                Contraseña Temporal *
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="crear_password" 
                                       name="password" 
                                       required
                                       minlength="6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 pr-10"
                                       placeholder="Mínimo 6 caracteres">
                                <button type="button" onclick="togglePassword('crear_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_password"></div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="crear_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock text-red-500 mr-1"></i>
                                Confirmar Contraseña *
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="crear_password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       minlength="6"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200 pr-10"
                                       placeholder="Repita la contraseña">
                                <button type="button" onclick="togglePassword('crear_password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_password_confirmation"></div>
                        </div>

                        <!-- Rol -->
                        <div>
                            <label for="crear_idRol" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-tag text-red-500 mr-1"></i>
                                Rol *
                            </label>
                            <select id="crear_idRol" 
                                    name="idRol" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                                <option value="">Seleccione un rol</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idRol => $nombreRol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($idRol); ?>"><?php echo e($nombreRol); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_idRol"></div>
                        </div>

                        <!-- Franquicia -->
                        <div>
                            <label for="crear_idFranquicia" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-red-500 mr-1"></i>
                                Franquicia *
                            </label>
                            <select id="crear_idFranquicia" 
                                    name="idFranquicia" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200">
                                <option value="">Seleccione una franquicia</option>
                                <?php $__currentLoopData = $franquicias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idFranquicia => $nombreFranquicia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($idFranquicia); ?>"><?php echo e($nombreFranquicia); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_crear_idFranquicia"></div>
                        </div>
                    </div>

                    <!-- Info Message -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                            <div class="text-sm text-blue-700">
                                <strong>Nota importante:</strong> El usuario recibirá una contraseña temporal y será obligado a cambiarla en su primer inicio de sesión.
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200">
                        <button type="button" 
                                onclick="cerrarModalCrear()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" 
                                id="btnCrearUsuario"
                                class="px-4 py-2 bg-gradient-to-r from-red-600 to-blue-600 text-white rounded-lg hover:from-red-700 hover:to-blue-700 transition-all duration-200">
                            <i class="fas fa-save mr-2"></i>
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editar Usuario -->
        <div id="modalEditarUsuario" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
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
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
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
                                   placeholder="Ej: druizp">
                            <div class="text-red-500 text-sm mt-1 hidden" id="error_editar_login"></div>
                        </div>

                        <!-- Email (para futuro uso) -->
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
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idRol => $nombreRol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($idRol); ?>"><?php echo e($nombreRol); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <?php $__currentLoopData = $franquicias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idFranquicia => $nombreFranquicia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($idFranquicia); ?>"><?php echo e($nombreFranquicia); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
        <span class="text-gray-700">Procesando...</span>
    </div>
</div>
        
    </div>
</div>

<!-- TODO: Aquí continuaremos con los modales en partes posteriores -->

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Custom styles for the module */
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Variables globales
let usuarioEditandoId = null;

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
        }
    });

    // Cerrar modales al hacer clic fuera
    document.getElementById('modalCrearUsuario').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalCrear();
    });

    document.getElementById('modalEditarUsuario').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEditar();
    });
}

// ===== FUNCIONES DE MODALES =====

// Abrir modal crear
function abrirModalCrear() {
    limpiarFormularioCrear();
    document.getElementById('modalCrearUsuario').classList.remove('hidden');
    document.getElementById('crear_usuario').focus();
}

// Cerrar modal crear
function cerrarModalCrear() {
    document.getElementById('modalCrearUsuario').classList.add('hidden');
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
            document.getElementById('modalEditarUsuario').classList.remove('hidden');
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
    document.getElementById('modalEditarUsuario').classList.add('hidden');
    limpiarFormularioEditar();
    usuarioEditandoId = null;
}

// ===== FUNCIONES DE FORMULARIOS =====

// Manejar creación de usuario
async function manejarCreacionUsuario(e) {
    e.preventDefault();
    
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
                // Errores de validación
                const errores = await response.json();
                if (errores.errors) {
                    mostrarErroresValidacion(errores.errors, 'crear');
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
    const campos = ['usuario', 'login', 'email', 'password', 'password_confirmation', 'idRol', 'idFranquicia', 'idEstado'];
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

// Función para eliminar usuario
async function eliminarUsuario(idUsuario, nombreUsuario) {
    if (!confirm(`¿Está seguro de eliminar al usuario "${nombreUsuario}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }

    mostrarLoading(true);

    try {
        const response = await fetch(`/usuarios/${idUsuario}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            // Recargar la página después de 1 segundo
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(data.message || 'Error al eliminar el usuario', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al eliminar el usuario', 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Confirmación para acciones peligrosas
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/usuarios/index.blade.php ENDPATH**/ ?>