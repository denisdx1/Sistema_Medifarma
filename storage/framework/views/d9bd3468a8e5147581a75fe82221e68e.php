<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        

        <!-- Eliminado: Secciones de búsqueda global y filtros por franquicia -->

        
        <!-- Markets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-list mr-2"></i>
                            Listado de Marcas
                        </h2>
                        <?php if(auth()->guard()->check()): ?>
                            <?php if(auth()->user()->idRol == 1): ?>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-crown text-yellow-500 mr-1"></i>
                                    Vista de administrador - Todas las marcas del sistema
                                </p>
                            <?php elseif(auth()->user()->idRol == 2): ?>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-user text-blue-500 mr-1"></i>
                                    Vista de gerente - Solo sus marcas asignadas
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Campo de búsqueda -->
                    <div class="flex items-center space-x-3">
                        <!-- Botón Crear Mercado -->
                        <button onclick="openCreateMarketModal()"
                                class="inline-flex items-center px-4 py-2 rounded-md text-sm bg-primary text-white hover:bg-secondary transition-colors duration-200 shadow-sm hover:shadow-md"
                                title="Crear nuevo mercado">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Mercado
                        </button>
                        
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="search-input"
                                   name="search"
                                   value="<?php echo e(request('search')); ?>"
                                   class="block w-80 pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary text-sm"
                                   placeholder="Buscar por marca o mercado..."
                                   autocomplete="off">
                            <?php if(request('search')): ?>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" 
                                        onclick="clearSearch()"
                                        class="text-gray-400 hover:text-gray-600"
                                        title="Limpiar búsqueda">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        

                    </div>
                </div>
            </div>

            <?php if($markets->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">
                                Marca 
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                Mercado Asignado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="markets-table-body">
                        <?php $__currentLoopData = $markets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $market): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 market-row" data-market-id="<?php echo e($market->idMercado); ?>" data-product-code="<?php echo e($market->codigoPresentacion); ?>">
                            <!-- Marca (Descripción Producto) -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-secondary-light rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tags text-primary text-xs"></i>
                                    </div>
                                    <div>
                                        <button onclick="redirectToProductsWithBrand('<?php echo e($market->marca); ?>')" 
                                                class="font-medium text-gray-900 hover:text-primary hover:underline transition-colors cursor-pointer"
                                                title="Ver productos de esta marca">
                                            <?php echo e($market->marca); ?>

                                        </button>
                                        <div class="text-sm text-gray-500">Código: <?php echo e($market->codigoPresentacion); ?></div>
                                    </div>
                                </div>
                            </td>
                            <!-- Mercado Asignado -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-6 h-6 bg-secondary-purple rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-store text-primary text-xs"></i>
                                    </div>
                                    <button onclick="redirectToProductsWithMarket('<?php echo e($market->mercado); ?>')" 
                                            class="font-medium text-primary hover:text-secondary hover:underline transition-colors cursor-pointer"
                                            title="Ver productos de este mercado">
                                        <?php echo e($market->mercado); ?>

                                    </button>
                                </div>
                            </td>
                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($market->estado == 'ACTIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'); ?>">
                                    <i class="fas <?php echo e($market->estado == 'ACTIVO' ? 'fa-play' : 'fa-pause'); ?> mr-1"></i>
                                    <?php echo e($market->estado); ?>

                                </span>
                            </td>
                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <!-- Editar Nombre del Mercado -->
                                    <button onclick="openEditModal(<?php echo e($market->idMercado); ?>, '<?php echo e(addslashes($market->mercado)); ?>')"
                                            class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-secondary-light text-primary hover:bg-secondary-muted transition-colors duration-200"
                                            title="Editar nombre del mercado">
                                        <i class="fas fa-edit mr-1"></i>
                                        Editar Mercado
                                    </button>
                                    
                                    <!-- Asignar Producto -->
                                    <button onclick="redirectToProductsWithRestoAndMarket('<?php echo e($market->mercado); ?>')"
                                            class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-primary text-white hover:bg-secondary transition-colors duration-200"
                                            title="Asignar producto a este mercado">
                                        <i class="fas fa-plus mr-1"></i>
                                        Asignar Producto
                                    </button>
                                </div>
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
                        <?php echo e($markets->firstItem()); ?> - <?php echo e($markets->lastItem()); ?> de <?php echo e($markets->total()); ?> marcas
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
                                <span class="px-2 py-1 text-xs text-white bg-red-600 rounded font-medium"><?php echo e($page); ?></span>
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
                <i class="fas fa-tags text-gray-400 text-4xl mb-4"></i>
                <?php if(auth()->guard()->check()): ?>
                    <?php if(auth()->user()->idRol == 1): ?>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No se encontraron marcas</h3>
                        <p class="text-gray-600">No hay marcas que coincidan con tu búsqueda</p>
                        <button onclick="clearSearch()" class="mt-3 text-primary hover:text-secondary font-medium">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar búsqueda
                        </button>
                    <?php elseif(auth()->user()->idRol == 2): ?>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No hay marcas asignadas</h3>
                        <p class="text-gray-600">No hay marcas asignadas que coincidan con tu búsqueda</p>
                        <button onclick="clearSearch()" class="mt-3 text-primary hover:text-secondary font-medium">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar búsqueda
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Edit Market Modal -->
<div id="edit-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Mercado
                </h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Form Content -->
        <form id="edit-market-form" class="p-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <input type="hidden" id="edit-market-id" name="market_id">
            <input type="hidden" id="edit-market-original-name" name="market_original_name">
            
            <!-- Market Name Field -->
            <div class="mb-4">
                <label for="edit-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Mercado *
                </label>
                <input type="text" 
                       id="edit-market-name" 
                       name="market_name" 
                       class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white"
                       placeholder="Nombre del mercado"
                       required>
            </div>

            <!-- Note Field -->
            <div class="mb-4">
                <label for="edit-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-primary mr-1"></i>
                    Nota *
                </label>
                <textarea id="edit-market-note" 
                          name="market_note" 
                          rows="3"
                          class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                          placeholder="Nota obligatoria sobre los cambios realizados..."
                          required></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Campo obligatorio. Se enviará por email como notificación del cambio.
                </p>
                <p class="text-xs text-red-500 mt-1 hidden" id="edit-note-error">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    La nota es obligatoria
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeEditModal()"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button type="button"
                        onclick="openEditConfirmationModal()"
                        id="edit-submit-btn"
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Guardar
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>



<!-- Edit Market Confirmation Modal -->
<div id="edit-confirmation-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmar Cambios
                </h3>
                <button onclick="closeEditConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <!-- Comparison -->
            <div class="mb-4">
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3 mb-3">
                    <p class="text-sm text-primary font-medium">Actual:</p>
                    <p class="text-lg font-bold text-primary" id="confirm-edit-current-name">-</p>
                </div>
                
                <div class="text-center my-2">
                    <i class="fas fa-arrow-down text-primary text-xl"></i>
                </div>
                
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3">
                    <p class="text-sm text-primary font-medium">Nuevo:</p>
                    <p class="text-lg font-bold text-primary" id="confirm-edit-new-name">-</p>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                    <strong>Atención:</strong> Este cambio afectará todos los productos del mercado.
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeEditConfirmationModal()"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button type="button"
                        onclick="confirmEditMarket()"
                        id="confirm-edit-btn"
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Guardando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Toast Container -->
<div id="toast-container" class="fixed top-20 right-4 z-50"></div>

<!-- Modal para Crear Mercado -->
<div id="create-market-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Crear Nuevo Mercado
                </h3>
                <button onclick="closeCreateMarketModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Form Content -->
        <form id="create-market-form" class="p-6">
            <?php echo csrf_field(); ?>
            
            <!-- Market Name Field -->
            <div class="mb-4">
                <label for="new-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Mercado *
                </label>
                <input type="text" 
                       id="new-market-name"
                       name="market_name"
                       class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white"
                       placeholder="Nombre del mercado"
                       required>
            </div>

            <!-- Note Field -->
            <div class="mb-4">
                <label for="new-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-primary mr-1"></i>
                    Nota *
                </label>
                <textarea id="new-market-note"
                          name="market_note"
                          rows="3"
                          class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                          placeholder="Nota obligatoria sobre el mercado..."
                          required></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Campo obligatorio. Se enviará por email como notificación.
                </p>
                <p class="text-xs text-red-500 mt-1 hidden" id="create-note-error">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    La nota es obligatoria
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeCreateMarketModal()"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button type="button"
                        onclick="proceedWithMarketCreation()"
                        id="create-market-btn"
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Mercado
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Creando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmación para Crear Mercado -->
<div id="create-market-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmar Creación
                </h3>
                <button onclick="closeCreateMarketConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3 mb-4">
                <p class="text-sm text-primary font-medium">Nuevo Mercado:</p>
                <p class="text-lg font-bold text-primary" id="confirm-create-market-name">-</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                    <strong>Atención:</strong> ¿Está seguro de que desea crear este mercado?
                </p>
            </div>

            <div class="space-y-2" id="confirm-create-market-note-container" style="display: none;">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <h4 class="text-sm font-medium text-gray-900 mb-1">Nota:</h4>
                    <p class="text-sm text-gray-700" id="confirm-create-market-note">-</p>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="px-6 py-4 border-t border-gray-200 flex space-x-3">
            <button type="button" 
                    onclick="closeCreateMarketConfirmationModal()"
                    class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                Cancelar
            </button>
            <button type="button"
                    onclick="proceedWithMarketCreationAndAssignment()"
                    id="final-create-assign-btn"
                    class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                <span class="btn-text flex items-center justify-center">
                    <i class="fas fa-check mr-2"></i>
                    Confirmar y Crear
                </span>
                <span class="btn-loading hidden flex items-center justify-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Creando...
                </span>
            </button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/create-market.js')); ?>"></script>
<script src="<?php echo e(asset('js/market-management.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/market-management/index.blade.php ENDPATH**/ ?>