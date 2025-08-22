<?php $__env->startSection('title', 'Gestión de Mercados'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-store text-purple-600 mr-2"></i>
                        Gestión de Mercados
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Administra todos los mercados del sistema
                        <?php if($isGerenteProducto && $userFranquicia && $userFranquicia !== 'ADMIN'): ?>
                            <span class="text-purple-600 font-medium"> - Franquicia: <?php echo e($userFranquicia); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <button onclick="openCreateModal()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Mercado
                </button>
            </div>
        </div>

        <!-- Notificación de filtrado por franquicia -->
        <?php if($isGerenteProducto && $userFranquicia && $userFranquicia !== 'ADMIN'): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <span class="font-medium">Vista filtrada por franquicia:</span> 
                        Estás viendo únicamente los mercados asociados a tu franquicia 
                        <span class="font-semibold"><?php echo e($userFranquicia); ?></span>.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <!-- Markets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-list mr-2"></i>
                        Listado de Mercados
                    </h2>
                    <div class="flex items-center space-x-4">
                        <!-- Búsqueda de texto -->
                        <div class="relative">
                            <input type="text" 
                                   id="search-input"
                                   placeholder="Buscar por nombre, ID o estado..."
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 w-80">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button id="clear-search" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <?php if($markets->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre del Mercado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Registro
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="markets-table-body">
                        <?php $__currentLoopData = $markets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 market-row" data-market-id="<?php echo e($market->idMercado); ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($market->idMercado); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-store text-purple-600 text-xs"></i>
                                    </div>
                                    <div class="font-medium text-gray-900"><?php echo e($market->mercado); ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e(\Carbon\Carbon::parse($market->fechaRegistro)->format('d/m/Y')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($market->estado == 'ACTIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'); ?>">
                                    <i class="fas <?php echo e($market->estado == 'ACTIVO' ? 'fa-play' : 'fa-pause'); ?> mr-1"></i>
                                    <?php echo e($market->estado); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <!-- Ver Productos Button -->
                                <button onclick="viewMarketProducts(<?php echo e($market->idMercado); ?>, '<?php echo e(addslashes($market->mercado)); ?>')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200"
                                        title="Ver productos del mercado">
                                    <i class="fas fa-box mr-1"></i>
                                    Productos
                                </button>
                                
                                <!-- Asignar Productos Button -->
                                <button onclick="openAssignProductsModal(<?php echo e($market->idMercado); ?>, '<?php echo e(addslashes($market->mercado)); ?>')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-purple-100 text-purple-700 hover:bg-purple-200 transition-colors duration-200"
                                        title="Asignar productos desde RESTO">
                                    <i class="fas fa-plus-circle mr-1"></i>
                                    Asignar
                                </button>
                                
                                <!-- Edit Market Button -->
                                <button onclick="openEditModal(<?php echo e($market->idMercado); ?>, '<?php echo e(addslashes($market->mercado)); ?>')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                                        title="Editar mercado">
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar
                                </button>
                                </span>
                                
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50" id="pagination-container">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-600" id="pagination-info">
                        <?php echo e($markets->firstItem()); ?> - <?php echo e($markets->lastItem()); ?> de <?php echo e($markets->total()); ?> mercados
                    </div>
                    <div class="flex items-center space-x-1" id="pagination-links">
                        
                        <?php if($markets->onFirstPage()): ?>
                            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($markets->previousPageUrl()); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        
                        <?php
                            $start = max($markets->currentPage() - 2, 1);
                            $end = min($start + 4, $markets->lastPage());
                            $start = max($end - 4, 1);
                        ?>

                        
                        <?php if($start > 1): ?>
                            <a href="<?php echo e($markets->url(1)); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">1</a>
                            <?php if($start > 2): ?>
                                <span class="px-1 text-xs text-gray-400">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        
                        <?php for($page = $start; $page <= $end; $page++): ?>
                            <?php if($page == $markets->currentPage()): ?>
                                <span class="px-2 py-1 text-xs text-white bg-purple-600 rounded font-medium"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($markets->url($page)); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        
                        <?php if($end < $markets->lastPage()): ?>
                            <?php if($end < $markets->lastPage() - 1): ?>
                                <span class="px-1 text-xs text-gray-400">...</span>
                            <?php endif; ?>
                            <a href="<?php echo e($markets->url($markets->lastPage())); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"><?php echo e($markets->lastPage()); ?></a>
                        <?php endif; ?>

                        
                        <?php if($markets->hasMorePages()): ?>
                            <a href="<?php echo e($markets->nextPageUrl()); ?>" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
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
            <?php else: ?>
            <div class="p-8 text-center">
                <i class="fas fa-store text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay mercados registrados</h3>
                <p class="text-gray-600 mb-4">Comienza creando tu primer mercado</p>
                <button onclick="openCreateModal()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Mercado
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Market Modal -->
<div id="create-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-plus text-purple-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Crear Nuevo Mercado</h3>
                        <p class="text-sm text-gray-500">Agrega un nuevo mercado al sistema</p>
                    </div>
                </div>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Form -->
            <form id="create-market-form">
                <?php echo csrf_field(); ?>
                <div class="mb-6">
                    <label for="market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Mercado *
                    </label>
                    <input type="text" 
                           id="market-name" 
                           name="market_name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Ingresa el nombre del mercado"
                           required>
                    <p class="text-xs text-gray-500 mt-1">El nombre debe ser único en el sistema</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-blue-800 text-sm">
                                <strong>Información:</strong> El mercado será creado en estado "ESPERA" y deberá ser aprobado por un administrador antes de estar disponible.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeCreateModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="button"
                            onclick="openCreateConfirmationModal()"
                            id="create-submit-btn"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200 font-medium">
                        <span class="btn-text">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Mercado
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Market Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-edit text-amber-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Editar Mercado</h3>
                        <p class="text-sm text-gray-500">Modifica el nombre del mercado</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Form -->
            <form id="edit-market-form">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" id="edit-market-id" name="market_id">
                
                <div class="mb-6">
                    <label for="edit-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Mercado *
                    </label>
                    <input type="text" 
                           id="edit-market-name" 
                           name="market_name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500"
                           placeholder="Ingresa el nuevo nombre del mercado"
                           required>
                    <p class="text-xs text-gray-500 mt-1">El nombre debe ser único en el sistema</p>
                </div>
                
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-amber-800 text-sm">
                                <strong>Atención:</strong> El cambio de nombre del mercado afectará todas las referencias existentes en el sistema.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeEditModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="button"
                            onclick="openEditConfirmationModal()"
                            id="edit-submit-btn"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition-colors duration-200 font-medium">
                        <span class="btn-text">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Market Confirmation Modal -->
<div id="create-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar Creación</h3>
                        <p class="text-sm text-gray-500">¿Estás seguro de crear este mercado?</p>
                    </div>
                </div>
                <button onclick="closeCreateConfirmationModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-store text-blue-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-blue-800 text-sm font-medium">Mercado a crear:</p>
                            <p class="text-blue-900 text-base font-semibold" id="confirm-create-market-name">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-yellow-800 text-sm">
                                Una vez creado, el mercado estará disponible en el sistema para asignar productos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeCreateConfirmationModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                <button type="button"
                        onclick="confirmCreateMarket()"
                        id="confirm-create-btn"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors duration-200 font-medium">
                    <span class="btn-text ">
                        <i class="fas fa-check mr-2"></i>
                        Sí, Crear Mercado
                    </span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Creando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Market Confirmation Modal -->
<div id="edit-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-edit text-amber-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar Edición</h3>
                        <p class="text-sm text-gray-500">¿Estás seguro de guardar los cambios?</p>
                    </div>
                </div>
                <button onclick="closeEditConfirmationModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="space-y-2">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-store text-blue-600 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-blue-800 text-sm font-medium">Nombre actual:</p>
                                <p class="text-blue-900 text-base" id="confirm-edit-current-name">-</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-arrow-right text-green-600 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-green-800 text-sm font-medium">Nuevo nombre:</p>
                                <p class="text-green-900 text-base font-semibold" id="confirm-edit-new-name">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-yellow-800 text-sm">
                                Este cambio afectará todos los productos asignados a este mercado.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeEditConfirmationModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                <button type="button"
                        onclick="confirmEditMarket()"
                        id="confirm-edit-btn"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition-colors duration-200 font-medium">
                    <span class="btn-text">
                        <i class="fas fa-save mr-2"></i>
                        Sí, Guardar Cambios
                    </span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Guardando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Asignar Productos -->
<div id="assign-products-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-lg shadow-xl border border-gray-200 w-full max-w-7xl max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-purple-50 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-plus-circle text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Asignar Productos al Mercado</h3>
                            <p class="text-sm text-gray-600">Mercado: <span id="assign-market-name" class="font-medium"></span></p>
                        </div>
                    </div>
                    <button onclick="closeAssignProductsModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 flex-1 overflow-hidden flex flex-col">
                <!-- Search Bar -->
                <div class="mb-6 flex-shrink-0">
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       id="resto-product-search" 
                                       class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-purple-500 focus:border-purple-500 text-sm"
                                       placeholder="Buscar en productos RESTO...">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" 
                                            id="clear-resto-search" 
                                            class="text-gray-400 hover:text-gray-600 hidden"
                                            title="Limpiar búsqueda">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div id="resto-search-loading" class="hidden">
                                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Selected products count -->
                        <div class="text-sm text-gray-600">
                            <span id="selected-count">0</span> productos seleccionados
                        </div>
                        
                        <!-- Assign button -->
                        <button id="assign-selected-btn" 
                                onclick="assignSelectedProducts()"
                                class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <span class="btn-text">
                                <i class="fas fa-plus mr-2"></i>
                                Asignar Seleccionados
                            </span>
                            <span class="btn-loading hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Asignando...
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Filters Panel -->
                <div class="mb-4 flex-shrink-0">
                    <div class="flex items-center justify-between mb-3">
                        <button type="button" 
                                id="toggle-resto-filters" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-filter mr-2 text-purple-600"></i>
                            <span>Mostrar Filtros</span>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                        <button type="button" 
                                id="clear-all-resto-filters" 
                                class="text-sm text-red-600 hover:text-red-700 font-medium hidden">
                            <i class="fas fa-eraser mr-1"></i>
                            Limpiar todos los filtros
                        </button>
                    </div>

                    <!-- Filters Grid (Hidden by default) -->
                    <div id="resto-filters-panel" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- FF3 Filter -->
                            <div class="relative">
                                <label for="resto-filter-descripcionFF3" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-pills mr-1 text-blue-500"></i>
                                    Forma Farmacéutica (FF3)
                                </label>
                                <select id="resto-filter-descripcionFF3" 
                                        class="block w-full text-xs border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Todas las formas</option>
                                </select>
                                <button type="button" 
                                        id="clear-resto-filter-descripcionFF3" 
                                        class="absolute right-8 top-6 text-gray-400 hover:text-gray-600 hidden"
                                        title="Limpiar filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            <!-- ATC4 Filter -->
                            <div class="relative">
                                <label for="resto-filter-descripcionATC4" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-tags mr-1 text-green-500"></i>
                                    Clasificación (ATC4)
                                </label>
                                <select id="resto-filter-descripcionATC4" 
                                        class="block w-full text-xs border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Todas las clasificaciones</option>
                                </select>
                                <button type="button" 
                                        id="clear-resto-filter-descripcionATC4" 
                                        class="absolute right-8 top-6 text-gray-400 hover:text-gray-600 hidden"
                                        title="Limpiar filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            <!-- Laboratorio Filter -->
                            <div class="relative">
                                <label for="resto-filter-descripcionLaboratorio" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-building mr-1 text-orange-500"></i>
                                    Laboratorio
                                </label>
                                <select id="resto-filter-descripcionLaboratorio" 
                                        class="block w-full text-xs border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Todos los laboratorios</option>
                                </select>
                                <button type="button" 
                                        id="clear-resto-filter-descripcionLaboratorio" 
                                        class="absolute right-8 top-6 text-gray-400 hover:text-gray-600 hidden"
                                        title="Limpiar filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            <!-- Fuente Filter -->
                            <div class="relative">
                                <label for="resto-filter-fuente" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-database mr-1 text-teal-500"></i>
                                    Fuente
                                </label>
                                <select id="resto-filter-fuente" 
                                        class="block w-full text-xs border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Todas las fuentes</option>
                                </select>
                                <button type="button" 
                                        id="clear-resto-filter-fuente" 
                                        class="absolute right-8 top-6 text-gray-400 hover:text-gray-600 hidden"
                                        title="Limpiar filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            <!-- Molécula Filter -->
                            <div class="relative">
                                <label for="resto-filter-molecula" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-atom mr-1 text-purple-500"></i>
                                    Molécula
                                </label>
                                <select id="resto-filter-molecula" 
                                        class="block w-full text-xs border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Todas las moléculas</option>
                                </select>
                                <button type="button" 
                                        id="clear-resto-filter-molecula" 
                                        class="absolute right-8 top-6 text-gray-400 hover:text-gray-600 hidden"
                                        title="Limpiar filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>

                            <!-- Corporación Filter -->
                            <div class="relative">
                                <label for="resto-filter-descripcionCorporacion" class="block text-xs font-medium text-gray-700 mb-1">
                                    <i class="fas fa-industry mr-1 text-red-500"></i>
                                    Corporación
                                </label>
                                <select id="resto-filter-descripcionCorporacion" 
                                        class="block w-full text-xs border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500">
                                    <option value="">Todas las corporaciones</option>
                                </select>
                                <button type="button" 
                                        id="clear-resto-filter-descripcionCorporacion" 
                                        class="absolute right-8 top-6 text-gray-400 hover:text-gray-600 hidden"
                                        title="Limpiar filtro">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filter Status -->
                        <div id="resto-filter-status" class="mt-3 text-xs text-gray-600 hidden">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="resto-filter-count">0</span> filtro(s) aplicado(s)
                        </div>
                    </div>
                </div>
                
                <!-- Products Table Container -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex-1 flex flex-col relative">
                    <!-- Loading Overlay -->
                    <div id="resto-loading-overlay" class="absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center z-10 hidden">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-3"></div>
                            <span class="text-gray-700 font-medium text-sm">Cargando productos RESTO...</span>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="flex-1 overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200 products-table text-xs">
                            <thead class="bg-gray-50 sticky top-0 z-20">
                                <tr class="divide-x divide-gray-200">
                                    <!-- Selección: 4% -->
                                    <th class="w-[4%] px-1 py-1 text-center text-xs font-semibold text-gray-500 uppercase tracking-tight">
                                        <input type="checkbox" id="select-all-products" onchange="toggleAllProducts()" 
                                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    </th>
                                    <!-- Descripción: 18% -->
                                    <th class="w-[18%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight">
                                        Descripción
                                    </th>
                                    <!-- M/G: 8% -->
                                    <th class="w-[8%] px-1 py-1 text-center text-xs font-semibold text-gray-500 uppercase tracking-tight hidden sm:table-cell border-l-2 border-gray-300" title="Marca/Genérico">
                                        <span class="hidden lg:inline">M/G</span>
                                        <span class="lg:hidden">M</span>
                                    </th>
                                    <!-- É/P: 8% -->  
                                    <th class="w-[8%] px-1 py-1 text-center text-xs font-semibold text-gray-500 uppercase tracking-tight hidden sm:table-cell border-r-2 border-gray-300" title="Ético/Popular">
                                        <span class="hidden lg:inline">É/P</span>
                                        <span class="lg:hidden">É</span>
                                    </th>
                                    <!-- Fuente: 6% -->
                                    <th class="w-[6%] px-1 py-1 text-center text-xs font-semibold text-gray-500 uppercase tracking-tight">
                                        Fuente
                                    </th>
                                    <!-- Molécula: 14% -->
                                    <th class="w-[14%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight hidden lg:table-cell">
                                        Molécula
                                    </th>
                                    <!-- FF3: 10% -->
                                    <th class="w-[10%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight hidden md:table-cell">
                                        <span class="hidden lg:inline">FF3</span>
                                        <span class="lg:hidden">FF</span>
                                    </th>
                                    <!-- ATC4: 10% -->
                                    <th class="w-[10%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight hidden md:table-cell">
                                        <span class="hidden lg:inline">ATC4</span>
                                        <span class="lg:hidden">AT</span>
                                    </th>
                                    <!-- Laboratorio: 8% -->
                                    <th class="w-[8%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight hidden lg:table-cell">
                                        Laboratorio
                                    </th>
                                    <!-- Corporación: 8% -->
                                    <th class="w-[8%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight hidden xl:table-cell">
                                        Corporación
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 divide-x divide-gray-200" id="resto-products-table-body">
                                <!-- Products will be loaded here via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="resto-empty-state" class="p-8 text-center hidden">
                        <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No hay productos en RESTO</h3>
                        <p class="text-gray-600">Todos los productos están asignados a mercados específicos</p>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 flex-shrink-0" id="resto-pagination-container">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                            <div id="resto-pagination-info" class="text-sm text-gray-600 text-center sm:text-left">
                                Cargando productos...
                            </div>
                            <div id="resto-pagination-controls" class="flex items-center justify-center sm:justify-end space-x-2">
                                <!-- Pagination buttons will be generated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/market-management.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Define routes for JavaScript
window.MarketManagementRoutes = {
    index: '<?php echo e(route("market-management.index")); ?>',
    create: '<?php echo e(route("market-management.create")); ?>',
    update: '<?php echo e(route("market-management.update")); ?>',
    toggleStatus: '<?php echo e(route("market-management.toggle-status")); ?>',
    search: '<?php echo e(route("market-management.search")); ?>'
};

window.csrfToken = '<?php echo e(csrf_token()); ?>';
</script>
<script src="<?php echo e(asset('js/market-management.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/market-management/index.blade.php ENDPATH**/ ?>