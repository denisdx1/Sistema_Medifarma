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
                        <!-- Notificación tipo Facebook para Productos NUEVOS y SIN_ASIGNAR -->
                        <div onclick="redirectToProductosNuevosYSinAsignar()" 
                             class="relative cursor-pointer group">
                            <!-- Icono de notificación -->
                            <div class="w-10 h-10 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center transition-colors duration-200 shadow-lg hover:shadow-xl">
                                <i class="fa-solid fa-bell text-white text-sm"></i>
                            </div>
                            
                            <!-- Badge con el número total -->
                            <div class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-lg">
                                <span id="total-count-notification">0</span>
                            </div>
                            
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center space-x-1">
                                        <span class="text-blue-300">NUEVOS:</span>
                                        <span class="font-bold" id="tooltip-count-nuevos">0</span>
                                    </div>
                                    <div class="w-px h-3 bg-gray-600"></div>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-orange-300">SIN ASIGNAR:</span>
                                        <span class="font-bold" id="tooltip-count-sin-asignar">0</span>
                                    </div>
                                </div>
                                <div class="text-center text-gray-300 mt-1">Click para ver productos</div>
                            </div>
                        </div>
                        
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
                                    <?php if($market->mercado): ?>
                                        <button onclick="redirectToProductsWithMarket('<?php echo e($market->mercado); ?>')" 
                                                class="font-medium text-primary hover:text-secondary hover:underline transition-colors cursor-pointer"
                                                title="Ver productos de este mercado">
                                            <?php echo e($market->mercado); ?>

                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-400 italic" title="Sin mercado asignado">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Sin asignar
                                        </span>
                                    <?php endif; ?>
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
                                    <?php if($market->mercado): ?>
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
                                    <?php else: ?>
                                        <!-- Sin mercado asignado -->
                                        <span class="text-gray-400 italic text-xs">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Sin mercado asignado
                                        </span>
                                    <?php endif; ?>
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
<script>
    // Define routes for JavaScript  
window.MarketManagementRoutes = {
    index: '<?php echo e(route("market-management.index")); ?>',
    update: '<?php echo e(route("market-management.update")); ?>',
    search: '<?php echo e(route("market-management.search")); ?>',
    allMarketsApi: '<?php echo e(route("market-management.all-markets.api")); ?>'
};

window.csrfToken = '<?php echo e(csrf_token()); ?>';

// ===== FUNCIONES DE NOTIFICACIÓN =====

// Función para mostrar notificaciones de éxito
function showSuccessNotification(message) {
    console.log('SUCCESS:', message);
    showToast(message, 'success');
}

// Función para mostrar notificaciones de error
function showErrorNotification(message) {
    console.log('ERROR:', message);
    showToast(message, 'error');
}

// Función para mostrar notificaciones de advertencia
function showWarningNotification(message) {
    console.log('WARNING:', message);
    showToast(message, 'warning');
}



// Función para redirigir al módulo de productos con filtro de mercado
function redirectToProductsWithMarket(marketName) {
    // Guardar el mercado seleccionado en sessionStorage
    sessionStorage.setItem('autoSelectMarket', marketName);
    
    // Redirigir a la página de productos sin parámetros en la URL
    const productosUrl = '<?php echo e(route("productos.index")); ?>';
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtro de marca
function redirectToProductsWithBrand(brandName) {
    // Guardar la marca seleccionada en sessionStorage
    sessionStorage.setItem('autoSelectBrand', brandName);
    
    // Redirigir a la página de productos sin parámetros en la URL
    const productosUrl = '<?php echo e(route("productos.index")); ?>';
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtro de SIN_ASIGNAR y mercado pre-seleccionado
function redirectToProductsWithRestoAndMarket(marketName) {
    // Guardar el mercado seleccionado en sessionStorage
    // Usar 'SIN_ASIGNAR' para el backend
    sessionStorage.setItem('autoSelectMarket', 'SIN_ASIGNAR');
    sessionStorage.setItem('preSelectedMarket', marketName);
    
    // Redirigir a la página de productos
    const productosUrl = '<?php echo e(route("productos.index")); ?>';
    window.location.href = productosUrl;
}

// ===== FUNCIONES PARA CREAR MERCADO =====

// Abrir modal para crear mercado
function openCreateMarketModal() {
    // Limpiar formulario
    document.getElementById('new-market-name').value = '';
    document.getElementById('new-market-note').value = '';
    
    // Mostrar modal
    document.getElementById('create-market-modal').classList.remove('hidden');
    
    // Agregar validación en tiempo real para la nota
    const noteField = document.getElementById('new-market-note');
    const createBtn = document.getElementById('create-market-btn');
    const errorMsg = document.getElementById('create-note-error');
    
    // Validación inicial - deshabilitar botón hasta que se ingrese una nota
    createBtn.classList.add('opacity-50', 'cursor-not-allowed');
    createBtn.disabled = true;
    
    // Validación en tiempo real
    noteField.addEventListener('input', function() {
        const note = this.value.trim();
        
        if (!note) {
            this.classList.add('border-red-500');
            this.classList.remove('border-primary');
            createBtn.classList.add('opacity-50', 'cursor-not-allowed');
            createBtn.disabled = true;
            errorMsg.classList.remove('hidden');
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-primary');
            createBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            createBtn.disabled = false;
            errorMsg.classList.add('hidden');
        }
    });
}

// Cerrar modal para crear mercado
function closeCreateMarketModal() {
    document.getElementById('create-market-modal').classList.add('hidden');
}

// Proceder con la creación del mercado
function proceedWithMarketCreation() {
    const marketName = document.getElementById('new-market-name').value.trim();
    const marketNote = document.getElementById('new-market-note').value.trim();
    
    if (!marketName) {
        showErrorNotification('Debe ingresar el nombre del mercado');
        document.getElementById('new-market-name').focus();
        return;
    }
    
    if (!marketNote) {
        showErrorNotification('La nota es obligatoria para crear un mercado');
        document.getElementById('new-market-note').focus();
        return;
    }
    
    // Llenar modal de confirmación
    document.getElementById('confirm-create-market-name').textContent = marketName;
    document.getElementById('confirm-create-market-note').textContent = marketNote;
    document.getElementById('confirm-create-market-note-container').style.display = 'block';
    
    // Cerrar modal de creación y mostrar modal de confirmación
    closeCreateMarketModal();
    document.getElementById('create-market-confirmation-modal').classList.remove('hidden');
}

// Cerrar modal de confirmación
function closeCreateMarketConfirmationModal() {
    document.getElementById('create-market-confirmation-modal').classList.add('hidden');
}

// Proceder con la creación del mercado después de confirmación
function proceedWithMarketCreationAndAssignment() {
    const marketName = document.getElementById('confirm-create-market-name').textContent;
    const marketNote = document.getElementById('confirm-create-market-note').textContent;
    
    // Mostrar loading en el botón
    const btn = document.getElementById('final-create-assign-btn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    btn.disabled = true;
    
    // Crear mercado
    $.ajax({
        url: '/productos/create-market',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            market_name: marketName,
            market_note: marketNote
        },
        success: function(response) {
            if (response.success) {
                showSuccessNotification(`Mercado "${marketName}" creado exitosamente`);
                
                // Cerrar modal de confirmación
                closeCreateMarketConfirmationModal();
                
                // Recargar la página para mostrar el nuevo mercado
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showErrorNotification(response.message || 'Error al crear mercado');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error al crear mercado';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showErrorNotification(errorMessage);
        },
        complete: function() {
            // Restaurar botón
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            btn.disabled = false;
        }
    });
}

// Event listeners para cerrar modales al hacer clic fuera
document.addEventListener('DOMContentLoaded', function() {
    // Modal de crear mercado
    document.getElementById('create-market-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateMarketModal();
        }
    });
    
    // Modal de confirmación de crear mercado
    document.getElementById('create-market-confirmation-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateMarketConfirmationModal();
        }
    });
    
    // Cerrar modales con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            closeCreateMarketModal();
            closeCreateMarketConfirmationModal();
        }
    });
    
    // Cargar contadores de productos NUEVOS y SIN_ASIGNAR
    loadProductosNuevosYSinAsignar();
});

// Función para cargar productos NUEVOS y SIN_ASIGNAR
function loadProductosNuevosYSinAsignar() {
    $.ajax({
        url: '<?php echo e(route("market-management.productos-nuevos-sin-asignar")); ?>',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success && response.data) {
                const countNuevos = response.data.counts.nuevos;
                const countSinAsignar = response.data.counts.sin_asignar;
                const total = response.data.counts.total;
                
                // Actualizar contador total en el badge
                document.getElementById('total-count-notification').textContent = total;
                
                // Actualizar tooltip
                document.getElementById('tooltip-count-nuevos').textContent = countNuevos;
                document.getElementById('tooltip-count-sin-asignar').textContent = countSinAsignar;
                
                // Cambiar color del badge según si hay productos
                const badgeElement = document.getElementById('total-count-notification').parentElement;
                if (total > 0) {
                    badgeElement.classList.remove('bg-blue-600');
                    badgeElement.classList.add('bg-red-600');
                } else {
                    badgeElement.classList.remove('bg-red-600');
                    badgeElement.classList.add('bg-blue-600');
                }
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error al cargar productos NUEVOS y SIN_ASIGNAR:', errorThrown);
        }
    });
}

// Función para redirigir al módulo de productos con filtros de NUEVOS y SIN_ASIGNAR
function redirectToProductosNuevosYSinAsignar() {
    // Guardar en sessionStorage para que el módulo de productos sepa qué filtros aplicar
    sessionStorage.setItem('autoFilterMarkets', JSON.stringify(['NUEVOS', 'SIN_ASIGNAR']));
    
    // Redirigir al módulo de productos
    const productosUrl = '<?php echo e(route("productos.index")); ?>';
    window.location.href = productosUrl;
}
</script>
<script src="<?php echo e(asset('js/market-management.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/market-management/index.blade.php ENDPATH**/ ?>