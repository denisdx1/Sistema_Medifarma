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
                    
                    <!-- Panel de filtros con toggle -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-900">
                                <i class="fas fa-filter text-primary mr-2"></i>
                                Filtros de Búsqueda
                            </h3>
                            <button id="toggle-filters" 
                                    class="inline-flex items-center px-3 py-1.5 bg-secondary-purple border border-secondary rounded-md text-xs font-medium text-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-200">
                                <i class="fas fa-chevron-down mr-1" id="toggle-icon"></i>
                                <span id="toggle-text">Ocultar Filtros</span>
                            </button>
                        </div>
                        <div id="filters-panel" class="transition-all duration-300 ease-in-out">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                            <!-- Producto Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Marca</label>
                                                            <div class="relative">
                                <input type="text" 
                                       id="filter-descripcionProducto"
                                       placeholder="Buscar marca..."
                                       class="block w-full px-3 py-2 pr-8 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white"
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
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            
                            <!-- Ético/Popular Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Ético/Popular</label>
                                <select id="filter-eticoPopular" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            
                            <!-- Fuente Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Fuente</label>
                                <select id="filter-fuente" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white">
                                    <option value="">Todas las fuentes</option>
                                </select>
                            </div>
                            
                            <!-- Mercado Filter -->
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-700">Mercado</label>
                                <select id="filter-mercado" 
                                        class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white">
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
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white"
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
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white"
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
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary bg-white"
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
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
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
                                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary"
                                           placeholder="Buscar corporación..."
                                           autocomplete="off">
                                    <div id="descripcionCorporacion-results" class="filter-dropdown"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Clear All Filters -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button type="button" 
                                        id="clear-all-filters" 
                                        class="px-4 py-2 bg-secondary-light text-dark rounded-md hover:bg-secondary-muted transition-colors text-sm">
                                    <i class="fas fa-eraser mr-2"></i>
                                    Limpiar todos los filtros
                                </button>
                                <button type="button" 
                                        id="clear-sorting" 
                                        class="px-4 py-2 bg-secondary-purple text-primary rounded-md hover:bg-secondary transition-colors text-sm">
                                    <i class="fas fa-sort mr-2"></i>
                                    Limpiar ordenamiento
                                </button>
                            </div>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Los filtros se aplican automáticamente mientras escribes
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden border border-gray-200 rounded-lg bg-white mt-6 relative">
                <!-- Loading Overlay para la tabla de productos -->
                <div id="loading-overlay" class="absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center z-10 hidden">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                        <span class="text-gray-700 font-medium text-lg" id="loading-text">Cargando productos...</span>
                        <p class="text-sm text-gray-500 mt-2" id="loading-subtext">Por favor espere</p>
                        <div class="mt-3 text-sm text-gray-400">
                            <i class="fas fa-clock mr-2"></i>
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
                                    <input type="checkbox" id="select-all-products" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <!-- Descripción: 15% -->
                                <th class="w-[15%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="descripcionPresentacion">
                                    <div class="flex items-center justify-between">
                                        <span>Presentacion</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Marca: 14% -->
                                <th class="w-[7%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="descripcionProducto">
                                    <div class="flex items-center justify-between">
                                        <span>Marca</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Marca/Genérico: 9% -->
                                <th class="w-[12%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight border-l-2 border-gray-300 cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="marcaGenerico">
                                    <div class="flex items-center justify-center">
                                        <span>Marca/Genérico</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Ético/Popular: 9% -->  
                                <th class="w-[12%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight border-r-2 border-gray-300 cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="eticoPopular">
                                    <div class="flex items-center justify-center">
                                        <span>Ético/Popular</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Molécula: 14% -->
                                <th class="w-[14%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="molecula">
                                    <div class="flex items-center justify-between">
                                        <span>Molécula</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- FF3: 9% -->
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="descripcionFF3">
                                    <div class="flex items-center justify-between">
                                        <span>FF3</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- ATC4: 9% -->
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="descripcionATC4">
                                    <div class="flex items-center justify-between">
                                        <span>ATC4</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Laboratorio: 11% -->
                                
                                <!-- Corporación: 9% -->
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="descripcionCorporacion">
                                    <div class="flex items-center justify-between">
                                        <span>Corporación</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <th class="w-[9%] px-2 py-2 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="descripcionLaboratorio">
                                    <div class="flex items-center justify-between">
                                        <span>Laboratorio</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Mercado: 10% -->
                                <th class="w-[10%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="mercado">
                                    <div class="flex items-center justify-center">
                                        <span>Mercado</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
                                </th>
                                <!-- Fuente: 7% -->
                                <th class="w-[7%] px-2 py-2 text-center text-[10px] font-semibold text-gray-500 uppercase tracking-tight cursor-pointer hover:bg-gray-100 transition-colors sortable-header" data-sort="fuente">
                                    <div class="flex items-center justify-center">
                                        <span>Fuente</span>
                                        <i class="fas fa-sort text-gray-400 ml-1 sort-icon"></i>
                                    </div>
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



            <!-- Empty State -->
            <div id="empty-state" class="p-8 text-center hidden">
                <i class="fas fa-database text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay productos</h3>
                <p class="text-gray-600">No se encontraron productos con los criterios de búsqueda</p>
            </div>

            <!-- Pagination -->
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50" id="pagination-container">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                        <div id="pagination-info" class="text-sm text-gray-600 text-center sm:text-left">
                            Cargando productos...
                        </div>
                        <div id="sorting-info" class="text-xs text-primary hidden">
                            <i class="fas fa-sort mr-1"></i>
                            <span id="sorting-text">Ordenado por: </span>
                        </div>
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
<div id="remove-product-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-3">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Quitar Producto
                </h3>
                <button onclick="closeRemoveProductModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <p class="text-gray-600 mb-3 text-sm">
                    ¿Estás seguro de que deseas quitar este producto del mercado? 
            </p>
            
            <!-- Product Info -->
            <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-2 mb-3">
                <p class="text-xs text-primary font-medium">Producto:</p>
                <p class="text-base font-bold text-primary" id="remove-product-name">-</p>
                <p class="text-xs text-primary" id="remove-product-code">-</p>
                </div>
            
            <!-- Note Field -->
            <div class="mb-3">
                <label for="remove-product-note" class="block text-xs font-medium text-gray-700 mb-1">
                    Nota
                </label>
                <textarea id="remove-product-note" 
                          name="remove_note" 
                          rows="2"
                          class="w-full px-2 py-2 text-sm border-2 border-primary rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                          placeholder="Nota sobre por qué se quita..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Se enviará por email</p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-2">
                <button onclick="closeRemoveProductModal()" 
                        class="flex-1 px-3 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-sm">
                    Cancelar
                </button>
                <button id="confirm-remove-btn" onclick="removeProductFromMarket()" 
                        class="flex-1 px-3 py-2 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all text-sm">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-trash mr-1"></i>
                        Quitar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Quitando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Mercado -->
<div id="change-market-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-3">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white flex items-center">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Cambiar Mercado
                </h3>
                <button onclick="closeChangeMarketModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <!-- Product Info -->
            <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-2 mb-3">
                <p class="text-sm text-primary font-medium">Producto a cambiar:</p>
                <p class="text-lg font-bold text-primary" id="change-product-name">-</p>
                <p class="text-sm text-primary" id="change-product-code">-</p>
                <p class="text-sm text-gray-600 mt-1">Mercado actual: <span class="font-medium text-primary" id="current-market-name">-</span></p>
            </div>

            <!-- Market Selector -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Mercado de destino:</label>
                <div class="relative">
                    <input type="text" 
                           id="market-search-input" 
                           class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-secondary-lighter focus:bg-white"
                           placeholder="Buscar mercado..."
                           autocomplete="off">
                    
                    <!-- Dropdown de mercados -->
                    <div id="markets-dropdown" class="absolute z-10 w-full mt-1 bg-white border border-primary rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
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
                
                <!-- Selected Market Display -->
                <div id="selected-market-display" class="mt-3 p-3 bg-secondary-lighter border border-secondary-muted rounded-lg hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-primary">Mercado seleccionado:</p>
                            <p class="text-lg font-bold text-primary" id="selected-market-name">-</p>
                        </div>
                        <button onclick="clearSelectedMarket()" class="text-primary hover:text-secondary">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <input type="hidden" id="selected-market-id" value="">
            </div>

            <!-- Note Field -->
            <div class="mb-4">
                <label for="change-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                    Nota
                </label>
                <textarea id="change-market-note" 
                          name="change_note" 
                          rows="3"
                          class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                          placeholder="Nota sobre el cambio de mercado..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Se enviará por email</p>
            </div>

            <!-- Actions -->
            <div class="flex space-x-3">
                <button onclick="closeChangeMarketModal()" 
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button onclick="changeProductMarket()" 
                        id="change-market-btn"
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-exchange-alt mr-2"></i>
                        Cambiar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cambiando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final para Quitar -->
<div id="final-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Confirmación Final
                </h3>
                <button onclick="closeFinalConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-4 mb-4">
                <p class="text-primary font-medium">
                    ⚠️ Esta acción no se puede deshacer. 
                    El producto será quitado del mercado actual.
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button onclick="closeFinalConfirmationModal()" 
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button id="final-confirm-btn" onclick="proceedWithRemoval()" 
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Final para Cambiar -->
<div id="final-change-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Confirmar Cambio
                </h3>
                <button onclick="closeFinalChangeConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <div class="mb-4">
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3 mb-3">
                    <p class="text-sm text-primary font-medium">Producto:</p>
                    <p class="text-lg font-bold text-primary" id="final-change-product-name">-</p>
                </div>
                
                <div class="text-center my-2">
                    <i class="fas fa-arrow-down text-primary text-xl"></i>
                </div>
                
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3">
                    <p class="text-sm text-primary font-medium">Nuevo mercado:</p>
                    <p class="text-lg font-bold text-primary" id="final-change-market-name">-</p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button onclick="closeFinalChangeConfirmationModal()" 
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button id="final-change-confirm-btn" onclick="proceedWithMarketChange()" 
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Asignación Masiva de Productos -->
<div id="bulk-assign-modal" class="fixed inset-0  flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fas fa-layer-group mr-1"></i>
                    Asignar Productos
                </h3>
                <button onclick="closeBulkAssignModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-3">
            
            <!-- Selected Products Info -->
            <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-2 mb-2">
                <p class="text-xs font-medium text-primary mb-1">Productos seleccionados:</p>
                <p class="text-sm font-bold text-primary mb-1" id="bulk-selected-count">0 productos</p>
                
                <!-- Lista de productos seleccionados (siempre visible) -->
                <div id="selected-products-list" class="bg-white rounded-lg border border-primary p-1 max-h-16 overflow-y-auto">
                    <div id="selected-products-content"></div>
                </div>
            </div>

            <!-- Selector de mercado -->
            <div class="mt-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Seleccionar mercado de destino:</label>
                
                <!-- Tabs para mercado existente vs crear nuevo -->
                <div class="border-b border-gray-200 mb-2">
                    <nav class="-mb-px flex space-x-4">
                        <button onclick="switchToExistingMarket()" id="existing-market-tab" 
                                class="tab-button active py-1 px-1 border-b-2 border-primary font-medium text-xs text-primary">
                            Mercado Existente
                        </button>
                        <button onclick="switchToCreateMarket()" id="create-market-tab" 
                                class="tab-button py-1 px-1 border-b-2 border-transparent font-medium text-xs text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Crear Nuevo Mercado
                        </button>
                    </nav>
                </div>

                <!-- Panel para mercado existente -->
                <div id="existing-market-panel" class="space-y-2">
                    <div class="relative">
                        <input type="text" 
                               id="bulk-market-search-input" 
                               class="w-full px-2 py-2 text-sm border-2 border-primary rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-secondary-lighter focus:bg-white"
                               placeholder="Buscar mercado..."
                               autocomplete="off">
                        
                        <!-- Dropdown de mercados -->
                        <div id="bulk-markets-dropdown" class="absolute z-10 w-full mt-1 bg-white border border-primary rounded-lg shadow-lg max-h-32 overflow-y-auto hidden">
                            <div id="bulk-dropdown-loading" class="p-2 text-center text-gray-500 hidden">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                                <span class="text-xs">Cargando...</span>
                            </div>
                            <div id="bulk-markets-list"></div>
                            <div id="bulk-dropdown-no-results" class="p-2 text-center text-gray-500 hidden">
                                <span class="text-xs">No se encontraron mercados</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mercado seleccionado -->
                    <div id="bulk-selected-market-display" class="hidden p-2 bg-secondary-lighter border border-secondary-muted rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-primary">Mercado seleccionado:</p>
                                <p class="text-sm font-bold text-primary" id="bulk-selected-market-name">-</p>
                            </div>
                            <button onclick="clearBulkSelectedMarket()" class="text-primary hover:text-secondary">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel para crear nuevo mercado -->
                <div id="create-market-panel" class="hidden space-y-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nombre del nuevo mercado:</label>
                        <input type="text" 
                               id="new-market-name" 
                               class="w-full px-2 py-2 text-sm border-2 border-primary rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-secondary-lighter focus:bg-white"
                               placeholder="Nombre del nuevo mercado..."
                               maxlength="255">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nota :</label>
                        <textarea id="new-market-note" 
                                  class="w-full px-2 py-2 text-sm border-2 border-primary rounded-lg focus:ring-2 focus:ring-primary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                                  placeholder="Nota sobre la creación..."
                                  rows="1"
                                  maxlength="1000"></textarea>
                    </div>
                    <button onclick="createNewMarketAndAssign()" 
                            id="create-and-assign-btn"
                            class="w-full bg-primary hover:bg-secondary text-white px-2 py-1 rounded-md font-medium text-sm transition-colors">
                        <i class="fas fa-plus mr-1"></i>
                        Crear Mercado y Asignar
                    </button>
                </div>
                
                <input type="hidden" id="bulk-selected-market-id" value="">
            </div>

            <!-- Actions -->
            <div class="flex space-x-2 mt-2">
                <button onclick="closeBulkAssignModal()" 
                        class="flex-1 px-2 py-1 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-sm">
                    Cancelar
                </button>
                <button onclick="processBulkAssignment()" 
                        id="bulk-assign-confirm-btn"
                        class="flex-1 px-2 py-1 bg-primary hover:bg-secondary text-white rounded-lg font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-105 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-layer-group mr-1"></i>
                        Asignar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Asignando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Asignación a Mercado Existente -->
<div id="assign-confirmation-modal" class="fixed inset-0  flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Confirmar Asignación
                </h3>
                <button onclick="closeAssignConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-3">
            <div class="text-center mb-3">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary mb-2">
                    <i class="fas fa-layer-group text-black text-lg"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900">¿Confirmar asignación?</h3>
                <p class="text-xs text-gray-600 mt-1">
                    Está a punto de asignar <span id="confirm-assign-count" class="font-semibold text-primary">0</span> producto(s) 
                    al mercado <span id="confirm-assign-market" class="font-semibold text-primary"></span>
                </p>
            </div>
            
            <!-- Productos seleccionados (muestra máximo 3) -->
            <div class="bg-gray-50 rounded-md p-2 mb-3">
                <p class="text-xs font-medium text-gray-700 mb-1">Productos a asignar:</p>
                <div id="confirm-assign-products" class="text-xs text-gray-600 max-h-16 overflow-y-auto">
                    <!-- Se llena dinámicamente -->
                </div>
            </div>
            
            <!-- Campo de nota -->
            <div class="mb-3">
                <label for="assign-note" class="block text-xs font-medium text-gray-700 mb-1">
                    Nota
                </label>
                <textarea id="assign-note" 
                          placeholder="Escribe una nota para el email de notificación..."
                          class="w-full px-2 py-1 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 resize-none"
                          rows="2"></textarea>
            </div>
            
            <!-- Botones -->
            <div class="flex space-x-2">
                <button onclick="closeAssignConfirmationModal()" 
                        class="flex-1 px-2 py-1 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg font-medium text-sm transition-colors">
                    Cancelar
                </button>
                <button onclick="proceedWithAssignment()" 
                        id="final-assign-btn"
                        class="flex-1 px-2 py-1 bg-primary hover:bg-primary-600 text-white rounded-lg font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-check mr-1"></i>
                        Confirmar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Asignando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Crear Mercado y Asignar -->
<div id="create-market-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-primary p-2">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-white flex items-center">
                    <i class="fas fa-plus-circle mr-1"></i>
                    Confirmar Creación y Asignación
                </h3>
                <button onclick="closeCreateMarketConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-3">
            <!-- Layout horizontal en dos columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                <!-- Columna izquierda: Información del mercado -->
                <div class="space-y-2">
            <!-- Información del mercado a crear -->
                    <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-2">
                        <h4 class="text-xs font-medium text-primary mb-1 flex items-center">
                    <i class="fas fa-store mr-1"></i>
                    Mercado a crear:
                </h4>
                        <div class="space-y-1">
                            <div class="bg-white border border-primary rounded-lg p-1">
                                <p class="text-xs text-primary font-medium">Nombre:</p>
                                <p class="text-sm font-bold text-primary" id="confirm-market-name"></p>
                            </div>
                            <div class="bg-white border border-primary rounded-lg p-1" id="confirm-market-note-container" style="display: none;">
                                <p class="text-xs text-primary font-medium">Nota:</p>
                                <p class="text-xs text-primary" id="confirm-market-note"></p>
                            </div>
                </div>
            </div>

                    <!-- Warning -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-2">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-amber-600 mr-1 mt-0.5 flex-shrink-0 text-sm"></i>
                            <div>
                                <p class="text-xs font-medium text-amber-800">¡Atención!</p>
                                <p class="text-xs text-amber-700 mt-0.5">Se creará un nuevo mercado y se asignarán todos los productos seleccionados. Esta acción no se puede deshacer.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Lista de productos -->
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-2">
                    <h4 class="text-xs font-medium text-primary mb-1 flex items-center">
                        <i class="fas fa-list mr-1"></i>
                        Productos a asignar:
                    </h4>
                    <div class="bg-white border border-primary rounded-lg p-1 mb-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-primary font-medium">Total de productos:</span>
                            <span id="confirm-products-count" class="text-xs font-bold text-primary bg-secondary-lighter px-1 py-0.5 rounded">0</span>
                </div>
            </div>
                    <div class="bg-white border border-primary rounded-lg p-1 h-24 overflow-y-auto">
                        <div id="confirm-products-content">
                            <!-- Lista se llenará dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones en la parte inferior -->
            <div class="flex space-x-2 mt-2 pt-2 border-t border-gray-200">
                <button onclick="closeCreateMarketConfirmationModal()" 
                        class="flex-1 px-2 py-1 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium text-sm transition-colors">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button onclick="proceedWithMarketCreationAndAssignment()" 
                        id="final-create-assign-btn"
                        class="flex-1 px-2 py-1 bg-primary hover:bg-secondary text-white rounded-lg font-medium text-sm shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-check mr-1"></i>
                        Crear y Asignar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-1"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para toggle de filtros -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-filters');
    const filtersPanel = document.getElementById('filters-panel');
    const toggleIcon = document.getElementById('toggle-icon');
    const toggleText = document.getElementById('toggle-text');
    
    // Estado inicial: filtros visibles
    let filtersVisible = true;
    
    toggleButton.addEventListener('click', function() {
        if (filtersVisible) {
            // Ocultar filtros
            filtersPanel.style.maxHeight = '0';
            filtersPanel.style.overflow = 'hidden';
            filtersPanel.style.opacity = '0';
            toggleIcon.className = 'fas fa-chevron-right mr-1';
            toggleText.textContent = 'Mostrar Filtros';
            filtersVisible = false;
        } else {
            // Mostrar filtros
            filtersPanel.style.maxHeight = filtersPanel.scrollHeight + 'px';
            filtersPanel.style.overflow = 'visible';
            filtersPanel.style.opacity = '1';
            toggleIcon.className = 'fas fa-chevron-down mr-1';
            toggleText.textContent = 'Ocultar Filtros';
            filtersVisible = true;
        }
    });
    
    // Ajustar altura máxima cuando se carga la página
    setTimeout(() => {
        filtersPanel.style.maxHeight = filtersPanel.scrollHeight + 'px';
    }, 100);
});
</script>

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
