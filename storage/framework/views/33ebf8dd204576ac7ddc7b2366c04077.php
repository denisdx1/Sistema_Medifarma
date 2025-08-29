

<?php $__env->startSection('title', 'Logs de Mercados'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="w-full px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-clipboard-list text-primary mr-2"></i>
                        Logs de Mercados
                    </h1>
                    <p class="text-xs text-gray-600 mt-1">Sistema de auditoría - Solo administradores</p>
                </div>
                <div class="flex space-x-2">
                    <button onclick="exportLogs()" 
                            class="inline-flex items-center px-3 py-2 bg-primary border-transparent rounded-md shadow-sm text-xs font-medium text-white hover:from-primary hover:to-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <i class="fas fa-download mr-1"></i>
                        Exportar CSV
                    </button>
                    
                    <button onclick="location.reload()" 
                            class="inline-flex items-center px-3 py-2 bg-primary border border-transparent rounded-md shadow-sm text-xs font-medium text-white hover:from-secondary hover:to-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full px-4 py-4">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Records -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Total Registros</p>
                        <p class="text-lg font-bold text-primary"><?php echo e(number_format($totalRecords)); ?></p>
                    </div>
                    <div class="p-2 bg-primary rounded-md">
                        <i class="fas fa-list-alt text-white text-sm"></i>
                    </div>
                </div>
            </div>
            
            <!-- Active Users -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Usuarios Activos</p>
                        <p class="text-lg font-bold text-secondary"><?php echo e($usuarios->count()); ?></p>
                    </div>
                    <div class="p-2 bg-primary rounded-md">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                </div>
            </div>
            
            <!-- Action Types -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Tipos de Acción</p>
                        <p class="text-lg font-bold text-secondary"><?php echo e($acciones->count()); ?></p>
                    </div>
                    <div class="p-2 bg-primary rounded-md">
                        <i class="fas fa-tasks text-white text-sm"></i>
                    </div>
                </div>
            </div>
            
            <!-- Current Page -->
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-600">Página Actual</p>
                        <p class="text-lg font-bold text-primary"><?php echo e($page); ?> de <?php echo e($totalPages); ?></p>
                    </div>
                    <div class="p-2 bg-primary rounded-md">
                        <i class="fas fa-file-alt text-white text-sm"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6">
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">
                        <i class="fas fa-filter text-primary mr-2"></i>
                        Filtros de Búsqueda
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span id="active-filters-count" class="text-xs text-gray-500 bg-white px-2 py-1 rounded-full border">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="filters-count">0</span> filtros activos
                        </span>
                        <button type="button" onclick="clearAllFilters()" 
                                class="text-xs text-red-600 hover:text-red-800 transition-colors duration-200">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar todo
                        </button>
                    </div>
                </div>
            </div>
            
            <form method="GET" action="<?php echo e(route('logs.index')); ?>" id="filters-form" class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <!-- Fecha desde -->
                    <div class="relative">
                        <label for="date_from" class="block text-xs font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-red-400 mr-1"></i>
                            Fecha Desde
                        </label>
                        <div class="relative">
                            <input type="date" name="date_from" id="date_from" value="<?php echo e($dateFrom); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 text-xs bg-white hover:bg-gray-50">
                            <?php if($dateFrom): ?>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-green-500 text-xs">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Fecha hasta -->
                    <div class="relative">
                        <label for="date_to" class="block text-xs font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-red-400 mr-1"></i>
                            Fecha Hasta
                        </label>
                        <div class="relative">
                            <input type="date" name="date_to" id="date_to" value="<?php echo e($dateTo); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 text-xs bg-white hover:bg-gray-50">
                            <?php if($dateTo): ?>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-green-500 text-xs">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Usuario -->
                    <div class="relative">
                        <label for="usuario" class="block text-xs font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-blue-400 mr-1"></i>
                            Usuario
                        </label>
                        <div class="relative">
                            <select name="usuario" id="usuario"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-xs bg-white hover:bg-gray-50 appearance-none">
                                <option value="">Todos los usuarios</option>
                                <?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user); ?>" <?php echo e($usuario === $user ? 'selected' : ''); ?>>
                                        <?php echo e($user); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            
                            <?php if($usuario): ?>
                                <div class="absolute inset-y-0 right-6 flex items-center">
                                    <span class="text-blue-500 text-xs">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Acción -->
                    <div class="relative">
                        <label for="accion" class="block text-xs font-medium text-gray-700 mb-2">
                            <i class="fas fa-tasks text-green-400 mr-1"></i>
                            Acción
                        </label>
                        <div class="relative">
                            <select name="accion" id="accion"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-xs bg-white hover:bg-gray-50 appearance-none">
                                <option value="">Todas las acciones</option>
                                <?php $__currentLoopData = $acciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($action); ?>" <?php echo e($accion === $action ? 'selected' : ''); ?>>
                                        <?php echo e($action); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            
                            <?php if($accion): ?>
                                <div class="absolute inset-y-0 right-6 flex items-center">
                                    <span class="text-green-500 text-xs">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Búsqueda general -->
                    <div class="relative">
                        <label for="search" class="block text-xs font-medium text-gray-700 mb-2">
                            <i class="fas fa-search text-purple-400 mr-1"></i>
                            Buscar
                        </label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="<?php echo e($search); ?>" 
                                   placeholder="Buscar en detalles..."
                                   class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-xs bg-white hover:bg-gray-50">
                            
                            <?php if($search): ?>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-purple-500 text-xs">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-primary border border-transparent rounded-lg shadow-sm text-xs font-medium text-white hover:from-red-700 hover:to-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                            <i class="fas fa-search mr-2"></i>
                            Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Filtros activos -->
                <?php if($dateFrom || $dateTo || $usuario || $accion || $search): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-700">
                                <i class="fas fa-filter mr-1"></i>
                                Filtros aplicados:
                            </span>
                            <span class="text-xs text-gray-500">
                                <?php echo e($logs->count()); ?> resultados
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <?php if($dateFrom): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Desde: <?php echo e(\Carbon\Carbon::parse($dateFrom)->format('d/m/Y')); ?>

                                    <button type="button" onclick="removeFilter('date_from')" class="ml-1 text-green-600 hover:text-green-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                            <?php if($dateTo): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Hasta: <?php echo e(\Carbon\Carbon::parse($dateTo)->format('d/m/Y')); ?>

                                    <button type="button" onclick="removeFilter('date_to')" class="ml-1 text-green-600 hover:text-green-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                            <?php if($usuario): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-user mr-1"></i>
                                    Usuario: <?php echo e($usuario); ?>

                                    <button type="button" onclick="removeFilter('usuario')" class="ml-1 text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                            <?php if($accion): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-tasks mr-1"></i>
                                    Acción: <?php echo e($accion); ?>

                                    <button type="button" onclick="removeFilter('accion')" class="ml-1 text-green-600 hover:text-green-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                            <?php if($search): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <i class="fas fa-search mr-1"></i>
                                    Buscar: "<?php echo e($search); ?>"
                                    <button type="button" onclick="removeFilter('search')" class="ml-1 text-purple-600 hover:text-purple-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 relative z-10">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">
                        <i class="fas fa-table text-red-500 mr-1"></i>
                        Lista de Logs
                    </h3>
                    <span class="text-xs text-gray-500">
                        Mostrando <?php echo e($logs->count()); ?> de <?php echo e(number_format($totalRecords)); ?> registros
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acción
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tabla
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Detalle
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nota
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">
                                    <?php echo e($log->id); ?>

                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-semibold mr-2">
                                            <?php echo e(strtoupper(substr($log->usuario ?? 'U', 0, 1))); ?>

                                        </div>
                                        <?php echo e($log->usuario ?? 'Sin usuario'); ?>

                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
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
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <div class="flex flex-col">
                                        <span class="font-medium"><?php echo e(\Carbon\Carbon::parse($log->fecha)->format('d/m/Y')); ?></span>
                                        <span class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($log->fecha)->format('H:i:s')); ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-table mr-1"></i>
                                        <?php echo e($log->tabla); ?>

                                    </span>
                                </td>
                                                                 <td class="px-3 py-2 text-gray-900">
                                     <div class="log-detail log-detail-content break-words max-w-xs">
                                         <span class="text-xs text-gray-600 leading-relaxed">
                                             <?php echo e(Str::limit($log->detalle, 80)); ?>

                                             <?php if(strlen($log->detalle) > 80): ?>
                                                 <br><span class="text-gray-500"><?php echo e(Str::limit(substr($log->detalle, 80), 60)); ?></span>
                                             <?php endif; ?>
                                         </span>
                                     </div>
                                 </td>
                                <td class="px-3 py-2 text-xs text-gray-900">
                                    <div class="log-detail log-detail-content break-words max-w-xs">
                                        <?php if($log->nota): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-sticky-note mr-1"></i>
                                                <?php echo e($log->nota); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Sin nota</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                        <p class="text-sm font-medium">No se encontraron logs</p>
                                        <p class="text-xs">Intenta ajustar los filtros de búsqueda</p>
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
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-600">
                        <?php echo e(($page - 1) * $perPage + 1); ?> - <?php echo e(min($page * $perPage, $totalRecords)); ?> de <?php echo e(number_format($totalRecords)); ?> logs
                    </div>
                    <div class="flex items-center space-x-1">
                        
                        <?php if($page > 1): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $page - 1])); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        <?php endif; ?>

                        
                        <?php
                            $start = max($page - 2, 1);
                            $end = min($start + 4, $totalPages);
                            $start = max($end - 4, 1);
                        ?>

                        
                        <?php if($start > 1): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['page' => 1])); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">1</a>
                            <?php if($start > 2): ?>
                                <span class="px-1 text-xs text-gray-400">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        
                        <?php for($pageNum = $start; $pageNum <= $end; $pageNum++): ?>
                            <?php if($pageNum == $page): ?>
                                <span class="px-2 py-1 text-xs text-white bg-red-600 rounded font-medium"><?php echo e($pageNum); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $pageNum])); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"><?php echo e($pageNum); ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        
                        <?php if($end < $totalPages): ?>
                            <?php if($end < $totalPages - 1): ?>
                                <span class="px-1 text-xs text-gray-400">...</span>
                            <?php endif; ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $totalPages])); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"><?php echo e($totalPages); ?></a>
                        <?php endif; ?>

                        
                        <?php if($page < $totalPages): ?>
                            <a href="<?php echo e(request()->fullUrlWithQuery(['page' => $page + 1])); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript -->
<script>
    // Función para exportar logs
    function exportLogs() {
        const form = document.getElementById('filters-form');
        const params = new URLSearchParams(new FormData(form));
        window.location.href = '<?php echo e(route("logs.export")); ?>?' + params.toString();
    }

    // Función para contar filtros activos
    function countActiveFilters() {
        const filters = ['date_from', 'date_to', 'usuario', 'accion', 'search'];
        let count = 0;
        
        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element && element.value && element.value.trim() !== '') {
                count++;
            }
        });
        
        return count;
    }

    // Función para actualizar el contador de filtros
    function updateFiltersCount() {
        const count = countActiveFilters();
        const countElement = document.getElementById('filters-count');
        const badgeElement = document.getElementById('active-filters-count');
        
        if (countElement) {
            countElement.textContent = count;
        }
        
        // Mostrar/ocultar el badge según si hay filtros activos
        if (badgeElement) {
            if (count > 0) {
                badgeElement.style.display = 'inline-flex';
                badgeElement.style.visibility = 'visible';
            } else {
                badgeElement.style.display = 'none';
                badgeElement.style.visibility = 'hidden';
            }
        }
        
        // Debug: mostrar en consola para verificar
        console.log('Filtros activos:', count);
    }

    // Auto-submit del formulario cuando cambian los filtros
    document.addEventListener('DOMContentLoaded', function() {
        const filters = ['date_from', 'date_to', 'usuario', 'accion'];
        
        // Actualizar contador inicial con un pequeño delay para asegurar que el DOM esté listo
        setTimeout(() => {
            updateFiltersCount();
        }, 50);
        
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

        // Agregar efectos visuales a los inputs
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-blue-500', 'ring-opacity-50');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-blue-500', 'ring-opacity-50');
            });
        });
    });

    // Función para remover un filtro específico
    function removeFilter(filterId) {
        // Limpiar el campo específico
        const element = document.getElementById(filterId);
        if (element) {
            element.value = '';
        }
        
        // Actualizar el contador
        updateFiltersCount();
        
        // Redirigir con los parámetros actualizados
        const form = document.getElementById('filters-form');
        const params = new URLSearchParams(new FormData(form));
        params.delete(filterId);
        window.location.href = '<?php echo e(route("logs.index")); ?>?' + params.toString();
    }

    // Función para limpiar todos los filtros
    function clearAllFilters() {
        // Limpiar los campos del formulario
        const filters = ['date_from', 'date_to', 'usuario', 'accion', 'search'];
        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element) {
                element.value = '';
            }
        });
        
        // Actualizar el contador inmediatamente
        updateFiltersCount();
        
        // Mostrar el cambio visual antes de redirigir
        const countElement = document.getElementById('filters-count');
        const badgeElement = document.getElementById('active-filters-count');
        
        if (countElement) {
            countElement.textContent = '0';
        }
        
        if (badgeElement) {
            badgeElement.style.display = 'none';
        }
        
        // Pequeño delay para que el usuario vea el cambio
        setTimeout(() => {
            window.location.href = '<?php echo e(route("logs.index")); ?>';
        }, 100);
    }
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/logs/index.blade.php ENDPATH**/ ?>