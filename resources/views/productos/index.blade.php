@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                        <h2 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-pills mr-2"></i>
                            Catálogo de Productos
                        </h2>
                        <div class="flex items-center space-x-2 header-controls">
                            <!-- Contador de productos seleccionados -->
                            <div id="selected-products-info" class="hidden bg-blue-50 text-blue-700 px-3 py-1 rounded-md text-sm">
                                <i class="fas fa-check-square mr-1"></i>
                                <span id="selected-count">0</span> producto(s) seleccionado(s)
                            </div>
                            <!-- Botón de asignación masiva -->
                            <button id="bulk-assign-btn" 
                                    class="hidden bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                                    onclick="openBulkAssignModal()">
                                <i class="fas fa-layer-group mr-2"></i>
                                Asignar al Mercado
                            </button>
                        </div>
                    </div>
                    
                    <!-- Panel de filtros original mejorado -->
                    <div id="filters-panel" class="border-t border-gray-200 pt-4 mt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                            <!-- Producto Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Marca</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionProducto"
                                           placeholder="Buscar marca..."
                                           class="block w-full px-3 py-2 pr-8 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                           autocomplete="off">
                                    <button type="button" 
                                            id="clear-filter-descripcionProducto" 
                                            class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                    <div id="descripcionProducto-results" class="filter-dropdown"></div>
                                </div>
                            </div>
                            
                            <!-- Marca/Genérico Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Marca/Genérico</label>
                                <select id="filter-marcaGenerico" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            
                            <!-- Ético/Popular Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Ético/Popular</label>
                                <select id="filter-eticoPopular" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            
                            <!-- Fuente Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Fuente</label>
                                <select id="filter-fuente" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Todas las fuentes</option>
                                </select>
                            </div>
                            
                            <!-- Mercado Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Mercado</label>
                                <select id="filter-mercado" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Todos los mercados</option>
                                </select>
                            </div>
                            
                            <!-- Molécula Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Molécula</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-molecula"
                                           placeholder="Buscar molécula..."
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                           autocomplete="off">
                                    <div id="molecula-results" class="filter-dropdown"></div>
                                </div>
                            </div>
                            
                            <!-- FF3 Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Forma Farmacéutica (FF3)</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionFF3"
                                           placeholder="Buscar forma farmacéutica..."
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                           autocomplete="off">
                                    <div id="descripcionFF3-results" class="filter-dropdown"></div>
                                </div>
                            </div>
                            
                            <!-- ATC4 Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Clasificación ATC4</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionATC4"
                                           placeholder="Buscar clasificación ATC4..."
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                           autocomplete="off">
                                    <div id="descripcionATC4-results" class="filter-dropdown"></div>
                                </div>
                            </div>
                            
                            <!-- Laboratorio Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Laboratorio</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionLaboratorio" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar laboratorio..."
                                           autocomplete="off">
                                    <div id="descripcionLaboratorio-results" class="filter-dropdown"></div>
                                </div>
                            </div>
                            
                            <!-- Corporación Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Corporación</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="filter-descripcionCorporacion" 
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Buscar corporación..."
                                           autocomplete="off">
                                    <div id="descripcionCorporacion-results" class="filter-dropdown"></div>
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

                <!-- Tabla con texto más pequeño para mostrar todo completo -->
                <div class="max-h-[calc(100vh-12rem)] overflow-y-auto relative">
                    <table class="min-w-full divide-y divide-gray-200 products-table">
                        <thead class="bg-gray-50 sticky top-0 z-20">
                            <tr class="divide-x divide-gray-200">
                                <!-- Checkbox: 3% -->
                                <th class="w-[3%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    <input type="checkbox" id="select-all-products" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" title="Seleccionar todos">
                                </th>
                                <!-- Descripción: 15% -->
                                <th class="w-[15%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Presentacion
                                </th>
                                <!-- Marca: 14% -->
                                <th class="w-[14%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Marca
                                </th>
                                <!-- Marca/Genérico: 9% -->
                                <th class="w-[9%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight border-l-2 border-gray-300">
                                    Marca/Genérico
                                </th>
                                <!-- Ético/Popular: 9% -->  
                                <th class="w-[9%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight border-r-2 border-gray-300">
                                    Ético/Popular
                                </th>
                                <!-- Fuente: 7% -->
                                <th class="w-[7%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Fuente
                                </th>
                                <!-- Molécula: 14% -->
                                <th class="w-[14%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Molécula
                                </th>
                                <!-- FF3: 9% -->
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    FF3
                                </th>
                                <!-- ATC4: 9% -->
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    ATC4
                                </th>
                                <!-- Laboratorio: 11% -->
                                <th class="w-[11%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Laboratorio
                                </th>
                                <!-- Corporación: 9% -->
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Corporación
                                </th>
                                <!-- Mercado: 10% -->
                                <th class="w-[10%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Mercado
                                </th>
                                <!-- Acciones: 4% -->
                                <th class="w-[4%] px-1 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight">
                                    Acciones
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
                <i class="fas fa-database text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay productos</h3>
                <p class="text-gray-600">No se encontraron productos con los criterios de búsqueda</p>
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
<div id="remove-product-modal" class="fixed inset-0 overflow-y-auto h-full w-full hidden backdrop-blur-sm bg-black bg-opacity-20">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-trash text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Quitar Producto del Mercado</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Estás seguro de que deseas quitar este producto del mercado? 
                    El producto será movido al mercado "RESTO".
                </p>
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-700">Producto:</p>
                    <p class="text-sm text-gray-900 font-medium" id="remove-product-name"></p>
                    <p class="text-xs text-gray-500" id="remove-product-code"></p>
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirm-remove-btn" onclick="removeProductFromMarket()" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 mb-2">
                    <span class="btn-text">Sí, quitar producto</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Quitando...
                    </span>
                </button>
                <button onclick="closeRemoveProductModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Mercado -->
<div id="change-market-modal" class="fixed inset-0 overflow-y-auto h-full w-full hidden backdrop-blur-sm bg-black bg-opacity-20">
    <div class="relative top-10 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mx-auto">
                <i class="fas fa-exchange-alt text-blue-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4 text-center">Cambiar Producto a Otro Mercado</h3>
            
            <!-- Información del Producto -->
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <p class="text-xs font-medium text-gray-700">Producto a cambiar:</p>
                <p class="text-sm text-gray-900 font-medium" id="change-product-name"></p>
                <p class="text-xs text-gray-500" id="change-product-code"></p>
                <p class="text-xs text-gray-600 mt-1">Mercado actual: <span class="font-medium" id="current-market-name"></span></p>
            </div>

            <!-- Selector de Mercado -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar mercado de destino:</label>
                <div class="relative">
                    <input type="text" 
                           id="market-search-input" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Buscar mercado..."
                           autocomplete="off">
                    
                    <!-- Dropdown de mercados -->
                    <div id="markets-dropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden">
                        <div id="dropdown-loading" class="p-3 text-center text-gray-500 hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Cargando mercados...
                        </div>
                        <div id="markets-list"></div>
                        <div id="dropdown-no-results" class="p-3 text-center text-gray-500 hidden">
                            No se encontraron mercados
                        </div>
                    </div>
                </div>
                
                <!-- Mercado seleccionado -->
                <div id="selected-market-display" class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded-md hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-900">Mercado seleccionado:</p>
                            <p class="text-sm text-blue-700" id="selected-market-name"></p>
                        </div>
                        <button onclick="clearSelectedMarket()" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <input type="hidden" id="selected-market-id" value="">
            </div>

            <!-- Botones -->
            <div class="mt-6 flex space-x-3">
                <button onclick="changeProductMarket()" 
                        id="change-market-btn"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span class="btn-text">Cambiar mercado</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cambiando...
                    </span>
                </button>
                <button onclick="closeChangeMarketModal()" 
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final para Quitar -->
<div id="final-confirmation-modal" class="fixed inset-0 overflow-y-auto h-full w-full hidden backdrop-blur-sm bg-black bg-opacity-20">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmación Final</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Esta acción no se puede deshacer. El producto será movido al mercado "RESTO".
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="final-confirm-btn" onclick="proceedWithRemoval()" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 mb-2">
                    <span class="btn-text">Confirmar eliminación</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
                <button onclick="closeFinalConfirmationModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final para Cambiar -->
<div id="final-change-confirmation-modal" class="fixed inset-0 overflow-y-auto h-full w-full hidden backdrop-blur-sm bg-black bg-opacity-20">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <i class="fas fa-exchange-alt text-blue-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmación Final</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Confirmas que deseas cambiar el producto <strong id="final-change-product-name"></strong> al mercado <strong id="final-change-market-name"></strong>?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="final-change-confirm-btn" onclick="proceedWithMarketChange()" 
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 mb-2">
                    <span class="btn-text">Confirmar cambio</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
                <button onclick="closeFinalChangeConfirmationModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Asignación Masiva de Productos -->
<div id="bulk-assign-modal" class="fixed inset-0 overflow-y-auto h-full w-full hidden backdrop-blur-sm bg-black bg-opacity-20">
    <div class="relative top-10 mx-auto p-5 border w-[700px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mx-auto">
                <i class="fas fa-layer-group text-green-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4 text-center">Asignar Productos al Mercado</h3>
            
            <!-- Información de productos seleccionados -->
            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-900">Productos seleccionados:</p>
                        <p class="text-sm text-blue-700" id="bulk-selected-count">0 productos</p>
                    </div>
                    <button onclick="showSelectedProductsList()" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-list mr-1"></i>
                        Ver lista
                    </button>
                </div>
            </div>

            <!-- Lista de productos seleccionados (colapsible) -->
            <div id="selected-products-list" class="hidden mt-3 p-3 bg-gray-50 rounded-lg max-h-32 overflow-y-auto">
                <div id="selected-products-content"></div>
            </div>

            <!-- Selector de mercado -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar mercado de destino:</label>
                
                <!-- Tabs para mercado existente vs crear nuevo -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8">
                        <button onclick="switchToExistingMarket()" id="existing-market-tab" 
                                class="tab-button active py-2 px-1 border-b-2 border-green-500 font-medium text-sm text-green-600">
                            Mercado Existente
                        </button>
                        <button onclick="switchToCreateMarket()" id="create-market-tab" 
                                class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Crear Nuevo Mercado
                        </button>
                    </nav>
                </div>

                <!-- Panel para mercado existente -->
                <div id="existing-market-panel" class="space-y-3">
                    <div class="relative">
                        <input type="text" 
                               id="bulk-market-search-input" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Buscar mercado existente..."
                               autocomplete="off">
                        
                        <!-- Dropdown de mercados -->
                        <div id="bulk-markets-dropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto hidden">
                            <div id="bulk-dropdown-loading" class="p-3 text-center text-gray-500 hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Cargando mercados...
                            </div>
                            <div id="bulk-markets-list"></div>
                            <div id="bulk-dropdown-no-results" class="p-3 text-center text-gray-500 hidden">
                                No se encontraron mercados
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mercado seleccionado -->
                    <div id="bulk-selected-market-display" class="hidden p-2 bg-green-50 border border-green-200 rounded-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-900">Mercado seleccionado:</p>
                                <p class="text-sm text-green-700" id="bulk-selected-market-name"></p>
                            </div>
                            <button onclick="clearBulkSelectedMarket()" class="text-green-600 hover:text-green-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel para crear nuevo mercado -->
                <div id="create-market-panel" class="hidden space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del nuevo mercado:</label>
                        <input type="text" 
                               id="new-market-name" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Ingrese el nombre del mercado..."
                               maxlength="255">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nota (opcional):</label>
                        <textarea id="new-market-note" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                  placeholder="Nota o descripción del mercado..."
                                  rows="2"
                                  maxlength="1000"></textarea>
                    </div>
                    <button onclick="createNewMarketAndAssign()" 
                            id="create-and-assign-btn"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Mercado y Asignar Productos
                    </button>
                </div>
                
                <input type="hidden" id="bulk-selected-market-id" value="">
            </div>

            <!-- Botones -->
            <div class="mt-6 flex space-x-3">
                <button onclick="processBulkAssignment()" 
                        id="bulk-assign-confirm-btn"
                        class="flex-1 px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="btn-text">Asignar Productos</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Asignando...
                    </span>
                </button>
                <button onclick="closeBulkAssignModal()" 
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Crear Mercado y Asignar -->
<div id="create-market-confirmation-modal" class="fixed inset-0 overflow-y-auto h-full w-full hidden backdrop-blur-sm bg-black bg-opacity-20">
    <div class="relative top-10 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mx-auto">
                <i class="fas fa-plus-circle text-green-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4 text-center">Confirmar Creación de Mercado y Asignación</h3>
            
            <!-- Información del mercado a crear -->
            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                <h4 class="text-sm font-medium text-green-900 mb-2">
                    <i class="fas fa-store mr-2"></i>
                    Mercado a crear:
                </h4>
                <div class="space-y-1">
                    <p class="text-sm text-green-800">
                        <span class="font-medium">Nombre:</span> 
                        <span id="confirm-market-name" class="font-bold"></span>
                    </p>
                    <p class="text-sm text-green-700" id="confirm-market-note-container" style="display: none;">
                        <span class="font-medium">Nota:</span> 
                        <span id="confirm-market-note"></span>
                    </p>
                </div>
            </div>

            <!-- Lista de productos a asignar -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="text-sm font-medium text-blue-900 mb-2">
                    <i class="fas fa-list mr-2"></i>
                    Productos a asignar al nuevo mercado:
                </h4>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-blue-700">
                        <span class="font-medium">Total:</span> 
                        <span id="confirm-products-count" class="font-bold">0</span> producto(s)
                    </span>
                    <button onclick="toggleConfirmProductsList()" 
                            id="toggle-confirm-products-btn"
                            class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-chevron-down mr-1"></i>
                        Ver lista
                    </button>
                </div>
                <div id="confirm-products-list" class="hidden mt-3 max-h-32 overflow-y-auto bg-white rounded border border-blue-200 p-2">
                    <div id="confirm-products-content"></div>
                </div>
            </div>

            <!-- Advertencia -->
            <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                    <div class="text-sm text-yellow-800">
                        <p class="font-medium">¿Confirma la acción?</p>
                        <p class="mt-1">Se creará el nuevo mercado y se asignarán todos los productos seleccionados automáticamente.</p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="mt-6 flex space-x-3">
                <button onclick="proceedWithMarketCreationAndAssignment()" 
                        id="final-create-assign-btn"
                        class="flex-1 px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300">
                    <span class="btn-text">
                        <i class="fas fa-check mr-2"></i>
                        Sí, Crear y Asignar
                    </span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
                <button onclick="closeCreateMarketConfirmationModal()" 
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-900 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Configurar variables globales para el archivo JavaScript
window.productosModule = true;
</script>
<script src="{{ asset('js/productos.js') }}"></script>
<script src="{{ asset('js/productos-actions.js') }}"></script>
<script src="{{ asset('js/productos-bulk-assign.js') }}"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/market-products.css') }}">
@endpush
