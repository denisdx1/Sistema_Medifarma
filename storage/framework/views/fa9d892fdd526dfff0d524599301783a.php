<?php $__env->startSection('page-title', 'Productos del Mercado'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <a href="<?php echo e(route('market-management.index')); ?>" 
                           class="text-purple-600 hover:text-purple-700 mr-3 transition-colors">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            Productos del Mercado
                        </h1>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-store text-purple-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-lg lg:text-xl font-medium text-gray-800"><?php echo e($market->mercado ?? 'Mercado no encontrado'); ?></p>
                            
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 min-w-[180px]">
                        <p class="text-sm text-gray-600">Total de productos</p>
                        <p class="text-2xl font-bold text-blue-600" id="total-products">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                        <h2 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-pills mr-2"></i>
                            Listado de Productos
                        </h2>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-600" id="products-info">
                                Cargando productos...
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" 
                                       id="product-search" 
                                       class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       placeholder="Buscar por código, descripción o molécula...">
                                <!-- Clear button -->
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button type="button" 
                                            id="clear-search" 
                                            class="text-gray-400 hover:text-gray-600 hidden"
                                            title="Limpiar búsqueda">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <!-- Search loading indicator -->
                                    <div id="search-loading-indicator" class="hidden">
                                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search results info -->
                        <div id="search-results-info" class="text-sm text-gray-600 hidden">
                            <span id="search-results-text"></span>
                        </div>
                        
                        <!-- Filters Toggle -->
                        <button type="button" 
                                id="toggle-filters" 
                                class="flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-sm">
                            <i class="fas fa-filter mr-2"></i>
                            <span>Mostrar Filtros</span>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                    </div>
                    
                    <!-- Advanced Filters Panel -->
                    <div id="filters-panel" class="hidden border-t border-gray-200 pt-4 mt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                            <!-- FF3 Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Forma Farmacéutica (FF3)</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionFF3" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar FF3...">
                                    <button type="button" 
                                            id="clear-filter-descripcionFF3" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- ATC4 Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Clasificación (ATC4)</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionATC4" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar ATC4...">
                                    <button type="button" 
                                            id="clear-filter-descripcionATC4" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Laboratorio Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Laboratorio</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionLaboratorio" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar laboratorio...">
                                    <button type="button" 
                                            id="clear-filter-descripcionLaboratorio" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Fuente Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Fuente</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-fuente" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar fuente...">
                                    <button type="button" 
                                            id="clear-filter-fuente" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Molécula Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Molécula</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-molecula" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar molécula...">
                                    <button type="button" 
                                            id="clear-filter-molecula" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Corporación Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Corporación</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionCorporacion" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar corporación...">
                                    <button type="button" 
                                            id="clear-filter-descripcionCorporacion" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Clear All Filters -->
                        <div class="mt-4 flex justify-between items-center">
                            <button type="button" 
                                    id="clear-all-filters" 
                                    class="px-4 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors text-sm">
                                <i class="fas fa-eraser mr-2"></i>
                                Limpiar todos los filtros
                            </button>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Los filtros se aplican automáticamente mientras escribes
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden border border-gray-200 rounded-lg bg-white">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center z-10 hidden">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-3"></div>
                        <span class="text-gray-700 font-medium text-sm" id="loading-text">Cargando productos...</span>
                        <p class="text-xs text-gray-500 mt-1" id="loading-subtext">Por favor espere</p>
                        <div class="mt-2 text-xs text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            <span id="loading-timer">0s</span>
                        </div>
                    </div>
                </div>

                <!-- Tabla compacta con Tailwind -->
                <div class="max-h-[calc(100vh-12rem)] overflow-y-auto relative">
                    <table class="min-w-full divide-y divide-gray-200 products-table text-xs">
                        <thead class="bg-gray-50 sticky top-0 z-20">
                                                    <tr class="divide-x divide-gray-200">
                            <!-- Descripción: 20% -->
                            <th class="w-[20%] px-1 py-1 text-left text-xs font-semibold text-gray-500 uppercase tracking-tight">
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
                            <!-- Acciones: 2% -->
                            <th class="w-[2%] px-1 py-1 text-center text-xs font-semibold text-gray-500 uppercase tracking-tight">
                                •
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100 divide-x divide-gray-200" id="products-table-body">
                            <!-- Products will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Loading State (backup) -->
            <div id="loading-state" class="p-8 text-center hidden">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                    <span class="text-gray-600">Cargando productos...</span>
                </div>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="p-8 text-center hidden">
                <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay productos</h3>
                <p class="text-gray-600">Este mercado no tiene productos asociados</p>
            </div>

            <!-- Pagination -->
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50" id="pagination-container">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div id="pagination-info" class="text-sm text-gray-600 text-center sm:text-left">
                        Cargando productos...
                    </div>
                    <div id="pagination-controls" class="flex items-center justify-center sm:justify-end space-x-2">
                        <!-- Pagination buttons will be generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Quitar Producto -->
<div id="remove-product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <!-- Icono de advertencia -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-trash text-red-600 text-xl"></i>
            </div>
            <!-- Título -->
            <h3 class="text-lg font-medium text-gray-900 mb-2">Quitar Producto del Mercado</h3>
            <!-- Mensaje -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-2">
                    ¿Estás seguro de que deseas quitar el siguiente producto de este mercado?
                </p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                    <p class="font-medium text-gray-900" id="remove-product-name">Nombre del producto</p>
                    <p class="text-sm text-gray-600">Código: <span id="remove-product-code"></span></p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                    <h4 class="text-sm font-medium text-red-800 mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Esta acción:
                    </h4>
                    <ul class="text-xs text-red-700 space-y-1">
                        <li>• Removerá el producto de este mercado</li>
                        <li>• El producto será asignado al categoria "RESTO"</li>
                        <li>• Esta acción es reversible</li>
                    </ul>
                </div>
            </div>
            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button onclick="closeRemoveProductModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-remove-btn" onclick="removeProductFromMarket()" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                    <span class="btn-text">Quitar Producto</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Quitando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Mercado -->
<div id="change-market-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mr-3">
                        <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Cambiar Mercado</h3>
                </div>
                <button onclick="closeChangeMarketModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Información del producto -->
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="font-medium text-gray-900" id="change-product-name">Nombre del producto</p>
                <p class="text-sm text-gray-600">Código: <span id="change-product-code"></span></p>
                <p class="text-sm text-gray-600">Mercado actual: <span id="current-market-name"></span></p>
            </div>
            
            <!-- Selector de nuevo mercado -->
            <div class="mb-6">
                <label for="market-search-input" class="block text-sm font-medium text-gray-700 mb-2">
                    Seleccionar nuevo mercado:
                </label>
                <div class="relative">
                    <!-- Input de búsqueda -->
                    <div class="relative">
                        <input type="text" 
                               id="market-search-input" 
                               class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Buscar mercado..."
                               autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <!-- Indicador de loading en el input -->
                        <div id="search-loading" class="absolute inset-y-0 right-8 flex items-center hidden">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        </div>
                    </div>
                    
                    <!-- Lista desplegable de resultados -->
                    <div id="markets-dropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                        <!-- Loading state -->
                        <div id="dropdown-loading" class="p-3 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Cargando mercados...
                        </div>
                        
                        <!-- No results -->
                        <div id="dropdown-no-results" class="p-3 text-center text-gray-500 hidden">
                            <i class="fas fa-search mr-2"></i>
                            No se encontraron mercados
                        </div>
                        
                        <!-- Markets list -->
                        <div id="markets-list" class="hidden">
                            <!-- Market items will be populated here -->
                        </div>
                    </div>
                    
                    <!-- Campo oculto para almacenar el ID del mercado seleccionado -->
                    <input type="hidden" id="selected-market-id" value="">
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Escribe para buscar mercados activos disponibles
                </p>
                
                <!-- Market seleccionado -->
                <div id="selected-market-display" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-store text-blue-600 mr-2"></i>
                            <span id="selected-market-name" class="text-sm font-medium text-blue-800"></span>
                        </div>
                        <button type="button" onclick="clearSelectedMarket()" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <button onclick="closeChangeMarketModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-change-btn" onclick="changeProductMarket()"
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="btn-text">Cambiar Mercado</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cambiando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final para Quitar Producto -->
<div id="final-confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[60] hidden flex items-center justify-center">
    <div class="relative mx-auto p-6 border w-96 shadow-xl rounded-lg bg-white">
        <div class="text-center">
            <!-- Icono de advertencia crítica -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <!-- Título -->
            <h3 class="text-xl font-bold text-gray-900 mb-3">¿Estás completamente seguro?</h3>
            <!-- Mensaje -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">
                    Esta acción quitará definitivamente el producto del mercado actual.
                </p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm font-medium text-red-800">
                        El producto será movido a la categoría "RESTO"
                    </p>
                </div>
            </div>
            <!-- Botones -->
            <div class="flex justify-center space-x-4">
                <button onclick="closeFinalConfirmationModal()" 
                        class="px-6 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    No, cancelar
                </button>
                <button id="final-confirm-btn" onclick="proceedWithRemoval()" 
                        class="px-6 py-2 bg-red-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                    <span class="btn-text">Sí, quitar producto</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Quitando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final para Cambiar Mercado -->
<div id="final-change-confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[60] hidden flex items-center justify-center">
    <div class="relative mx-auto p-6 border w-96 shadow-xl rounded-lg bg-white">
        <div class="text-center">
            <!-- Icono de cambio -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-4">
                <i class="fas fa-exchange-alt text-blue-600 text-2xl"></i>
            </div>
            <!-- Título -->
            <h3 class="text-xl font-bold text-gray-900 mb-3">¿Confirmas el cambio de mercado?</h3>
            <!-- Mensaje -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">
                    Esta acción moverá el producto al nuevo mercado seleccionado.
                </p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <p class="text-sm font-medium text-blue-800 mb-1">
                        Producto: <span id="final-change-product-name" class="font-normal"></span>
                    </p>
                    <p class="text-sm font-medium text-blue-800">
                        Nuevo mercado: <span id="final-change-market-name" class="font-normal"></span>
                    </p>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                    <p class="text-xs text-amber-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esta acción es reversible y se puede cambiar nuevamente si es necesario
                    </p>
                </div>
            </div>
            <!-- Botones -->
            <div class="flex justify-center space-x-4">
                <button onclick="closeFinalChangeConfirmationModal()" 
                        class="px-6 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    No, cancelar
                </button>
                <button id="final-change-confirm-btn" onclick="proceedWithMarketChange()" 
                        class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="btn-text">Sí, cambiar mercado</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cambiando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<!-- Debug Info: Market ID = <?php echo e($market->idMercado ?? 'NULL'); ?>, Market Name = <?php echo e($market->mercado ?? 'NULL'); ?> -->
<script>
// Configurar variables globales para el archivo externo
window.marketId = <?php echo e($market->idMercado ?? 'null'); ?>;
window.marketData = <?php echo json_encode($market ?? null, 15, 512) ?>;
</script>
<script src="<?php echo e(asset('js/market-products.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/market-products.css')); ?>">
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/market-management/products.blade.php ENDPATH**/ ?>