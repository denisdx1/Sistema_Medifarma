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
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="search-input"
                                   name="search"
                                   value="<?php echo e(request('search')); ?>"
                                   class="block w-80 pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
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
                        
                        <?php if(request('search')): ?>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-filter mr-1"></i>
                            Filtrando por: "<?php echo e(request('search')); ?>"
                        </div>
                        <?php endif; ?>
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
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tags text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900"><?php echo e($market->marca); ?></div>
                                        <div class="text-sm text-gray-500">Código: <?php echo e($market->codigoPresentacion); ?></div>
                                    </div>
                                </div>
                            </td>
                            <!-- Mercado Asignado -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-store text-green-600 text-xs"></i>
                                    </div>
                                    <div class="font-medium text-gray-900"><?php echo e($market->mercado); ?></div>
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
                                <!-- Solo Editar Nombre del Mercado -->
                                <button onclick="openEditModal(<?php echo e($market->idMercado); ?>, '<?php echo e(addslashes($market->mercado)); ?>')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                                        title="Editar nombre del mercado">
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar Mercado
                                </button>
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
                        <?php if(request('search')): ?>
                            <span class="text-blue-600 font-medium">
                                (filtrado por "<?php echo e(request('search')); ?>")
                            </span>
                        <?php endif; ?>
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
                        <?php if(request('search')): ?>
                            <p class="text-gray-600">No hay marcas que coincidan con "<?php echo e(request('search')); ?>"</p>
                            <button onclick="clearSearch()" class="mt-3 text-blue-600 hover:text-blue-700 font-medium">
                                <i class="fas fa-times mr-1"></i>
                                Limpiar búsqueda
                            </button>
                        <?php else: ?>
                            <p class="text-gray-600">No hay marcas registradas en el sistema</p>
                        <?php endif; ?>
                    <?php elseif(auth()->user()->idRol == 2): ?>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No hay marcas asignadas</h3>
                        <?php if(request('search')): ?>
                            <p class="text-gray-600">No hay marcas asignadas que coincidan con "<?php echo e(request('search')); ?>"</p>
                            <button onclick="clearSearch()" class="mt-3 text-blue-600 hover:text-blue-700 font-medium">
                                <i class="fas fa-times mr-1"></i>
                                Limpiar búsqueda
                            </button>
                        <?php else: ?>
                            <p class="text-gray-600">No tienes marcas asignadas actualmente</p>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Edit Market Modal -->
<div id="edit-modal" class="fixed inset-0 backdrop-blur-sm bg-black bg-opacity-20 flex items-center justify-center p-4 hidden z-50">
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

                <!-- Campo de Nota -->
                <div class="mb-6">
                    <label for="edit-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note text-yellow-500 mr-1"></i>
                        Nota (opcional)
                    </label>
                    <textarea id="edit-market-note" 
                              name="market_note" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500 resize-none"
                              placeholder="Agrega una nota sobre los cambios realizados..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Esta nota será enviada por email a todos los usuarios</p>
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



<!-- Edit Market Confirmation Modal -->
<div id="edit-confirmation-modal" class="fixed inset-0 backdrop-blur-sm bg-black bg-opacity-20 flex items-center justify-center p-4 hidden z-[60]">
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
    update: '<?php echo e(route("market-management.update")); ?>'
};

window.csrfToken = '<?php echo e(csrf_token()); ?>';

// Funcionalidad de búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    
    if (searchInput) {
        // Búsqueda con Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Búsqueda automática con debounce
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 800); // Esperar 800ms después de que el usuario deje de escribir
        });
    }
});

function performSearch() {
    const searchInput = document.getElementById('search-input');
    const searchValue = searchInput.value.trim();
    
    // Construir URL con parámetro de búsqueda
    let url = window.MarketManagementRoutes.index;
    
    if (searchValue) {
        url += '?search=' + encodeURIComponent(searchValue);
    }
    
    // Redirigir con la búsqueda
    window.location.href = url;
}

function clearSearch() {
    // Redirigir sin parámetros de búsqueda
    window.location.href = window.MarketManagementRoutes.index;
}
</script>
<script src="<?php echo e(asset('js/market-management.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/market-management/index.blade.php ENDPATH**/ ?>