

<?php $__env->startSection('title', 'Logs de Mercados'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Logs de Mercados</h1>
            <p class="text-gray-600 mt-1">Sistema de auditoría - Solo administradores</p>
        </div>
        
        <div class="flex space-x-3">
            <!-- Botón de exportar -->
            <button onclick="exportLogs()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                <i class="fas fa-download mr-2"></i>
                Exportar CSV
            </button>
            
            <!-- Botón de actualizar -->
            <button onclick="location.reload()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                <i class="fas fa-sync-alt mr-2"></i>
                Actualizar
            </button>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Total Registros</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($totalRecords)); ?></p>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-list-alt text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Usuarios Activos</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e($usuarios->count()); ?></p>
                </div>
                <div class="text-green-500">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Tipos de Acción</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e($acciones->count()); ?></p>
                </div>
                <div class="text-yellow-500">
                    <i class="fas fa-tasks text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600">Página Actual</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e($page); ?> de <?php echo e($totalPages); ?></p>
                </div>
                <div class="text-purple-500">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <form method="GET" action="<?php echo e(route('logs.index')); ?>" id="filters-form">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Fecha desde -->
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo e($dateFrom); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Fecha hasta -->
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo e($dateTo); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Usuario -->
                <div>
                    <label for="usuario" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                    <select name="usuario" id="usuario"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos los usuarios</option>
                        <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user); ?>" <?php echo e($usuario === $user ? 'selected' : ''); ?>>
                                <?php echo e($user); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Acción -->
                <div>
                    <label for="accion" class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                    <select name="accion" id="accion"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todas las acciones</option>
                        <?php $__currentLoopData = $acciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($action); ?>" <?php echo e($accion === $action ? 'selected' : ''); ?>>
                                <?php echo e($action); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Búsqueda general -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" id="search" value="<?php echo e($search); ?>" 
                           placeholder="Buscar en detalles..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Botones -->
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200 flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Filtrar
                    </button>
                    <a href="<?php echo e(route('logs.index')); ?>" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200 flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de logs -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tabla
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Detalle
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo e($log->id); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-3">
                                        <?php echo e(strtoupper(substr($log->usuario ?? 'U', 0, 1))); ?>

                                    </div>
                                    <?php echo e($log->usuario ?? 'Sin usuario'); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php switch($log->accion):
                                        case ('INSERT'): ?>
                                            bg-green-100 text-green-800
                                            <?php break; ?>
                                        <?php case ('UPDATE'): ?>
                                            bg-blue-100 text-blue-800
                                            <?php break; ?>
                                        <?php case ('DELETE'): ?>
                                            bg-red-100 text-red-800
                                            <?php break; ?>
                                        <?php default: ?>
                                            bg-gray-100 text-gray-800
                                    <?php endswitch; ?>
                                ">
                                    <i class="
                                        <?php switch($log->accion):
                                            case ('INSERT'): ?>
                                                fas fa-plus
                                                <?php break; ?>
                                            <?php case ('UPDATE'): ?>
                                                fas fa-edit
                                                <?php break; ?>
                                            <?php case ('DELETE'): ?>
                                                fas fa-trash
                                                <?php break; ?>
                                            <?php default: ?>
                                                fas fa-question
                                        <?php endswitch; ?>
                                        mr-1
                                    "></i>
                                    <?php echo e($log->accion); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span class="font-medium"><?php echo e(\Carbon\Carbon::parse($log->fecha)->format('d/m/Y')); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($log->fecha)->format('H:i:s')); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-table mr-1"></i>
                                    <?php echo e($log->tabla); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="whitespace-pre-wrap break-words max-w-lg">
                                    <?php echo e($log->detalle); ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-medium">No se encontraron logs</p>
                                    <p class="text-sm">Intenta ajustar los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    <?php if($totalPages > 1): ?>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 mt-4 rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if($page > 1): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $page - 1])); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior
                        </a>
                    <?php endif; ?>
                    <?php if($page < $totalPages): ?>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $page + 1])); ?>" 
                           class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Siguiente
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando 
                            <span class="font-medium"><?php echo e(($page - 1) * $perPage + 1); ?></span>
                            a 
                            <span class="font-medium"><?php echo e(min($page * $perPage, $totalRecords)); ?></span>
                            de 
                            <span class="font-medium"><?php echo e(number_format($totalRecords)); ?></span>
                            registros
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            
                            <?php if($page > 1): ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['page' => 1])); ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $page - 1])); ?>" 
                                   class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>

                            
                            <?php for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <?php if($i == $page): ?>
                                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                                        <?php echo e($i); ?>

                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $i])); ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        <?php echo e($i); ?>

                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            
                            <?php if($page < $totalPages): ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $page + 1])); ?>" 
                                   class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $totalPages])); ?>" 
                                   class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>



<!-- JavaScript -->
<script>
    // Función para exportar logs
    function exportLogs() {
        const form = document.getElementById('filters-form');
        const params = new URLSearchParams(new FormData(form));
        window.location.href = '<?php echo e(route("logs.export")); ?>?' + params.toString();
    }

    // Auto-submit del formulario cuando cambian los filtros
    document.addEventListener('DOMContentLoaded', function() {
        const filters = ['date_from', 'date_to', 'usuario', 'accion'];
        
        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element) {
                element.addEventListener('change', function() {
                    document.getElementById('filters-form').submit();
                });
            }
        });

        // Búsqueda con debounce
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filters-form').submit();
            }, 500);
        });
    });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/logs/index.blade.php ENDPATH**/ ?>