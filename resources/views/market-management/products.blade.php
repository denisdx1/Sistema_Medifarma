@extends('layouts.app')

@section('page-title', 'Productos del Mercado')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <div class="flex items-center mb-2">
                    <a href="{{ route('market-management.index') }}" 
                       class="text-purple-600 hover:text-purple-700 mr-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-box text-blue-600 mr-2"></i>
                        Productos del Mercado
                    </h1>
                </div>
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-store text-purple-600 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-lg font-medium text-gray-800">{{ $market->mercado ?? 'Mercado no encontrado' }}</p>
                        @if(config('app.debug'))
                            <p class="text-xs text-gray-500">Debug - Market ID: {{ $market->idMercado ?? 'NULL' }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Total de productos</p>
                    <p class="text-2xl font-bold text-blue-600" id="total-products">-</p>
                </div>
            </div>
        </div>
    </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
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
            </div>

            <div class="overflow-x-auto relative">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center z-10 hidden">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <span class="text-gray-700 font-medium" id="loading-text">Cargando productos...</span>
                        <p class="text-sm text-gray-500 mt-1" id="loading-subtext">Por favor espere</p>
                        <div class="mt-3 text-xs text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            <span id="loading-timer">0s</span>
                        </div>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código Presentación
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descripción
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Marca/Genérico
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ético/Popular
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Molécula
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código FF 3
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código ATC 4
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mercado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                        <!-- Products will be loaded here via JavaScript -->
                    </tbody>
                </table>
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
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" id="pagination-container">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div id="pagination-info" class="text-sm text-gray-600">
                        Cargando productos...
                    </div>
                    <div id="pagination-controls" class="flex items-center justify-center sm:justify-end">
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
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <!-- Título -->
            <h3 class="text-lg font-medium text-gray-900 mb-2">Cambiar Estado a ESPERA</h3>
            <!-- Mensaje -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-2">
                    ¿Estás seguro de que deseas cambiar el estado del siguiente producto?
                </p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                    <p class="font-medium text-gray-900" id="remove-product-name">Nombre del producto</p>
                    <p class="text-sm text-gray-600">Código: <span id="remove-product-code"></span></p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esto realizará la siguiente acción:
                    </h4>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• El estado del producto cambiará a <strong>ESPERA</strong></li>
                    </ul>
                </div>
            </div>
            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button onclick="closeRemoveProductModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-remove-btn" onclick="changeStatusMarket()" 
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="btn-text" >Cambiar a ESPERA</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
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
                <button id="confirm-change-btn" 
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

@endsection

@push('scripts')
<!-- Debug Info: Market ID = {{ $market->idMercado ?? 'NULL' }}, Market Name = {{ $market->mercado ?? 'NULL' }} -->
<script>
$(document).ready(function() {
    // Validación robusta del marketId
    const marketId = {{ $market->idMercado ?? 'null' }};
    
    // Log de debugging
    console.log('Market object received:', @json($market ?? null));
    console.log('Market ID extracted:', marketId);
    
    // Verificar que tenemos un marketId válido
    if (!marketId || isNaN(marketId)) {
        console.error('Market ID inválido:', marketId);
        showError('Error: ID de mercado inválido. Regresando a la lista de mercados...');
        setTimeout(() => {
            window.location.href = '{{ route("market-management.index") }}';
        }, 3000);
        return;
    }
    
    console.log('Market ID cargado correctamente:', marketId);
    
    let currentCursor = null;
    let isLoading = false;
    let totalProductsLoaded = 0;
    let loadingTimer = null;
    let loadingStartTime = null;

    // Load initial products
    loadProducts();

    function loadProducts(cursor = null, append = false) {
        if (isLoading) return;
        
        isLoading = true;
        
        // Solo mostrar loading overlay para carga completa (no para "cargar más")
        if (!append) {
            showLoadingState();
        }

        const url = `/market-management/market/${marketId}/products/api`;
        const params = new URLSearchParams({
            per_page: 10  // Aumentado para cargar más productos por página
        });

        if (cursor) {
            params.append('cursor', cursor);
        }

        $.ajax({
            url: `${url}?${params}`,
            method: 'GET',
            timeout: 120000, // 2 minutos (120 segundos) en lugar de 30 segundos por defecto
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    if (append) {
                        appendProductsToTable(response.data);
                        totalProductsLoaded += response.data.length;
                    } else {
                        updateProductsTable(response.data);
                        totalProductsLoaded = response.data.length;
                    }
                    
                    updatePaginationControls(response.pagination);
                    updateProductsInfo(response.loaded_count, append);
                    
                    // Update total products counter with loaded count
                    $('#total-products').text(totalProductsLoaded + (response.pagination.has_more_pages ? '+' : ''));
                    
                    if (response.data.length === 0 && !append) {
                        showEmptyState();
                    } else {
                        hideEmptyState();
                    }
                } else {
                    showError('Error: ' + response.message);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('AJAX Error:', {xhr, textStatus, errorThrown});
                
                let errorMessage = 'Error al cargar productos del mercado';
                
                if (textStatus === 'timeout') {
                    errorMessage = 'La consulta tardó más de lo esperado. Este mercado tiene muchos productos. Por favor, inténtalo nuevamente.';
                } else if (xhr.status === 404) {
                    errorMessage = 'No se pudo encontrar la información del mercado';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor. Por favor, contacta al administrador';
                } else if (xhr.status === 0) {
                    errorMessage = 'Sin conexión al servidor. Verifica tu conexión a internet';
                }
                
                showError(errorMessage);
            },
            complete: function() {
                isLoading = false;
                hideLoadingState();
            }
        });
    }

    function updateProductsTable(products) {
        console.log('Updating products table, replacing content with', products.length, 'products');
        const tbody = $('#products-table-body');
        
        // Limpiar completamente la tabla
        tbody.empty();
        
        // Agregar los nuevos productos
        products.forEach(product => {
            tbody.append(generateProductRow(product));
        });
        
        // Actualizar el cursor actual
        if (products.length > 0) {
            // El cursor se maneja a nivel de paginación
        }
    }

    function appendProductsToTable(products) {
        console.log('Appending', products.length, 'products to table');
        const tbody = $('#products-table-body');
        
        products.forEach(product => {
            tbody.append(generateProductRow(product));
        });
    }

    // Función para escapar strings de JavaScript
    function escapeForJs(str) {
        if (!str) return '';
        return str.replace(/\\/g, '\\\\')
                  .replace(/'/g, "\\'")
                  .replace(/"/g, '\\"')
                  .replace(/\n/g, '\\n')
                  .replace(/\r/g, '\\r')
                  .replace(/\t/g, '\\t');
    }

    function generateProductRow(product) {
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${product['codigoPresentacion'] || '-'}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    <div class="max-w-xs">
                        <p class="font-medium">${product['descripcionPresentacion'] || '-'}</p>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBrandClass(product['marcaGenerico'])}">
                        ${product['marcaGenerico'] || '-'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEthicClass(product['eticoPopular'])}">
                        ${product['eticoPopular'] || '-'}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    <div class="max-w-xs">
                        ${product['molecula'] || '-'}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${product['codigoFF3'] || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${product['codigoATC4'] || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-store mr-1"></i>
                        ${product['MERCADO'] || '-'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                        <button onclick="openRemoveProductModal('${product['codigoPresentacion'] || product['Código_Presentación']}', '${escapeForJs(product['descripcionPresentacion'] || product['Descripción_Presentación'])}')" 
                                class="text-red-600 hover:text-red-900 transition-colors duration-200 p-2 rounded hover:bg-red-50" 
                                title="Quitar producto">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button onclick="openChangeMarketModal('${product['codigoPresentacion'] || product['Código_Presentación']}', '${escapeForJs(product['descripcionPresentacion'] || product['Descripción_Presentación'])}')" 
                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200 p-2 rounded hover:bg-blue-50" 
                                title="Cambiar mercado">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    function getBrandClass(brand) {
        switch(brand) {
            case 'MARCA': return 'bg-blue-100 text-blue-800';
            case 'GENÉRICO': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function getEthicClass(ethic) {
        switch(ethic) {
            case 'ÉTICO': return 'bg-indigo-100 text-indigo-800';
            case 'POPULAR': return 'bg-orange-100 text-orange-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    function updatePaginationControls(pagination) {
        const controls = $('#pagination-controls');
        const info = $('#pagination-info');
        controls.empty();

        // Información de la página actual
        info.html(`
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    <i class="fas fa-list-ol mr-1"></i>
                    ${totalProductsLoaded} productos cargados
                </span>
                ${pagination.has_more_pages ? '<span class="text-xs text-blue-600 font-medium">Más datos disponibles</span>' : '<span class="text-xs text-green-600 font-medium">Todos los datos cargados</span>'}
            </div>
        `);

        // Contenedor para los botones
        const buttonContainer = $('<div class="flex items-center space-x-2"></div>');

        // Primera página / Anterior
        if (pagination.has_previous_pages && pagination.prev_cursor) {
            buttonContainer.append(`
                <button onclick="loadFirstPage()" 
                        class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors"
                        title="Primera página">
                    <i class="fas fa-angles-left"></i>
                </button>
                <button onclick="loadPreviousPage('${pagination.prev_cursor}')" 
                        class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-left mr-1"></i>
                    Anterior
                </button>
            `);
        } else {
            buttonContainer.append(`
                <button disabled class="px-3 py-1 text-sm bg-gray-100 text-gray-400 border border-gray-200 rounded cursor-not-allowed"
                        title="Primera página">
                    <i class="fas fa-angles-left"></i>
                </button>
                <button disabled class="px-3 py-1 text-sm bg-gray-100 text-gray-400 border border-gray-200 rounded cursor-not-allowed">
                    <i class="fas fa-chevron-left mr-1"></i>
                    Anterior
                </button>
            `);
        }

        // Separador
        buttonContainer.append('<div class="border-l border-gray-300 h-8"></div>');

        // Cargar más (mantiene los datos existentes)
        if (pagination.has_more_pages && pagination.next_cursor) {
            buttonContainer.append(`
                <button onclick="loadMoreProducts('${pagination.next_cursor}')" 
                        class="px-4 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-1"></i>
                    Cargar Más
                </button>
            `);
        } else {
            buttonContainer.append(`
                <button disabled class="px-4 py-1 text-sm bg-gray-100 text-gray-400 border border-gray-200 rounded cursor-not-allowed">
                    <i class="fas fa-plus mr-1"></i>
                    Cargar Más
                </button>
            `);
        }

        // Separador
        buttonContainer.append('<div class="border-l border-gray-300 h-8"></div>');

        // Siguiente página (reemplaza los datos)
        if (pagination.has_more_pages && pagination.next_cursor) {
            buttonContainer.append(`
                <button onclick="loadNextPage('${pagination.next_cursor}')" 
                        class="px-3 py-1 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                    Siguiente
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            `);
        } else {
            buttonContainer.append(`
                <button disabled class="px-3 py-1 text-sm bg-gray-100 text-gray-400 border border-gray-200 rounded cursor-not-allowed">
                    Siguiente
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            `);
        }

        controls.append(buttonContainer);
    }

    function updateProductsInfo(count, append) {
        const info = $('#products-info');
        if (append) {
            info.html(`
                <div class="flex items-center space-x-2">
                    <i class="fas fa-layer-group text-blue-600"></i>
                    <span class="text-sm text-gray-700">${totalProductsLoaded} productos cargados</span>
                </div>
            `);
        } else {
            info.html(`
                <div class="flex items-center space-x-2">
                    <i class="fas fa-table text-green-600"></i>
                    <span class="text-sm text-gray-700">Mostrando ${count} productos</span>
                </div>
            `);
        }
    }

    function showLoadingState() {
        // Mostrar overlay principal sobre la tabla
        $('#loading-overlay').removeClass('hidden');
        // Ocultar el loading state de backup
        $('#loading-state').addClass('hidden');
        $('#empty-state').addClass('hidden');
        
        // Iniciar contador de tiempo
        loadingStartTime = Date.now();
        $('#loading-timer').text('0s');
        
        loadingTimer = setInterval(() => {
            const elapsed = Math.floor((Date.now() - loadingStartTime) / 1000);
            $('#loading-timer').text(elapsed + 's');
            
            // Actualizar mensaje basado en tiempo transcurrido
            if (elapsed > 30) {
                $('#loading-text').text('Consultando base de datos...');
                $('#loading-subtext').text('Este mercado tiene muchos productos, por favor espere');
            } else if (elapsed > 60) {
                $('#loading-text').text('Procesando datos...');
                $('#loading-subtext').text('La consulta está tomando más tiempo de lo normal');
            }
        }, 1000);
    }

    function hideLoadingState() {
        // Ocultar ambos loading states
        $('#loading-overlay').addClass('hidden');
        $('#loading-state').addClass('hidden');
        
        // Limpiar timer
        if (loadingTimer) {
            clearInterval(loadingTimer);
            loadingTimer = null;
        }
        
        // Resetear textos
        $('#loading-text').text('Cargando productos...');
        $('#loading-subtext').text('Por favor espere');
    }

    function showEmptyState() {
        $('#empty-state').removeClass('hidden');
        $('#pagination-container').addClass('hidden');
    }

    function hideEmptyState() {
        $('#empty-state').addClass('hidden');
        $('#pagination-container').removeClass('hidden');
    }

    function showError(message) {
        // Crear toast de error más elegante
        const toast = $(`
            <div class="error-toast fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle mr-3 mt-0.5 text-red-200"></i>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm mb-1">Error de Carga</h4>
                        <p class="text-sm opacity-90">${message}</p>
                    </div>
                    <button onclick="$(this).parent().parent().fadeOut()" class="ml-2 text-red-200 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `);
        
        $('body').append(toast);
        
        // Auto-remove after 8 seconds
        setTimeout(() => {
            toast.fadeOut(() => toast.remove());
        }, 8000);
    }

    // Global functions for pagination buttons
    window.loadMoreProducts = function(cursor) {
        console.log('Loading more products with cursor:', cursor);
        loadProducts(cursor, true); // append = true, agrega datos
    };

    window.loadNextPage = function(cursor) {
        console.log('Loading next page with cursor:', cursor);
        currentCursor = cursor;
        loadProducts(cursor, false); // append = false, reemplaza datos
    };

    window.loadPreviousPage = function(cursor) {
        console.log('Loading previous page with cursor:', cursor);
        currentCursor = cursor;
        loadProducts(cursor, false); // append = false, reemplaza datos
    };

    window.loadFirstPage = function() {
        console.log('Loading first page');
        currentCursor = null;
        loadProducts(null, false); // Primera página, reemplaza datos
    };

    // Variables globales para las modales
    let currentProductCode = null;
    let currentProductName = null;
    let availableMarkets = [];

    // Función global para cambiar estado del producto
    window.changeStatusMarket = function(){
        if (!currentProductCode) {
            showErrorNotification('Error: No se ha seleccionado un producto válido');
            return;
        }

        // Mostrar loading en el botón
        const btn = $('#confirm-remove-btn');
        const btnText = btn.find('.btn-text');
        const btnLoading = btn.find('.btn-loading');
        
        btnText.addClass('hidden');
        btnLoading.removeClass('hidden');
        btn.prop('disabled', true);

        $.ajax({
            url: '/market-management/products/remove',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                codigoPresentacion: currentProductCode
            },
            success: function(response) {
                if (response.success) {
                    // Mostrar notificación de éxito simple
                    showSuccessNotification('Estado del producto cambiado a ESPERA exitosamente');
                    
                    // Cerrar modal
                    closeRemoveProductModal();
                    
                    // Recargar la tabla de productos para reflejar los cambios
                    loadProducts();
                } else {
                    showErrorNotification('Error: ' + response.message);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error en removeProduct:', {xhr, textStatus, errorThrown});
                let errorMessage = 'Error al procesar la solicitud';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = 'Datos de entrada inválidos';
                } else if (xhr.status === 500) {
                    errorMessage = 'Error interno del servidor';
                }
                
                showErrorNotification('Error: ' + errorMessage);
            },
            complete: function() {
                // Restaurar botón
                btnText.removeClass('hidden');
                btnLoading.addClass('hidden');
                btn.prop('disabled', false);
            }
        });
    };

    // Funciones para Modal de Quitar Producto
    window.openRemoveProductModal = function(productCode, productName) {
        currentProductCode = productCode;
        currentProductName = productName;
        
        document.getElementById('remove-product-name').textContent = productName;
        document.getElementById('remove-product-code').textContent = productCode;
        document.getElementById('remove-product-modal').classList.remove('hidden');
    };

    window.closeRemoveProductModal = function() {
        document.getElementById('remove-product-modal').classList.add('hidden');
        currentProductCode = null;
        currentProductName = null;
    };

    // Funciones para Modal de Cambiar Mercado
    window.openChangeMarketModal = function(productCode, productName) {
        currentProductCode = productCode;
        currentProductName = productName;
        
        document.getElementById('change-product-name').textContent = productName;
        document.getElementById('change-product-code').textContent = productCode;
        document.getElementById('current-market-name').textContent = '{{ $market->mercado ?? "Mercado actual" }}';
        
        // Limpiar estados anteriores
        clearSelectedMarket();
        document.getElementById('market-search-input').value = '';
        document.getElementById('markets-dropdown').classList.add('hidden');
        
        // Mostrar modal
        document.getElementById('change-market-modal').classList.remove('hidden');
        
        // Cargar mercados disponibles
        loadAvailableMarkets();
        
        // Configurar event listeners para el search
        setupMarketSearch();
    };

    window.closeChangeMarketModal = function() {
        document.getElementById('change-market-modal').classList.add('hidden');
        currentProductCode = null;
        currentProductName = null;
        
        // Limpiar todo el estado del modal
        clearSelectedMarket();
        document.getElementById('market-search-input').value = '';
        document.getElementById('markets-dropdown').classList.add('hidden');
        availableMarkets = [];
    };

    // Variables para el search
    let marketSearchTimeout = null;
    let searchEventListenersAttached = false;

    // Configurar event listeners para búsqueda de mercados
    function setupMarketSearch() {
        if (searchEventListenersAttached) return;
        
        const searchInput = document.getElementById('market-search-input');
        const dropdown = document.getElementById('markets-dropdown');
        
        // Event listener para escribir en el input
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            
            // Clear previous timeout
            if (marketSearchTimeout) {
                clearTimeout(marketSearchTimeout);
            }
            
            // Mostrar dropdown si hay texto o mercados cargados
            if (searchTerm.length > 0 || availableMarkets.length > 0) {
                dropdown.classList.remove('hidden');
            }
            
            // Debounce search
            marketSearchTimeout = setTimeout(() => {
                filterMarkets(searchTerm);
            }, 300);
        });
        
        // Event listener para focus - mostrar dropdown
        searchInput.addEventListener('focus', function() {
            if (availableMarkets.length > 0) {
                dropdown.classList.remove('hidden');
                filterMarkets(this.value.trim().toLowerCase());
            }
        });
        
        // Event listener para click fuera del dropdown
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && !searchInput.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        searchEventListenersAttached = true;
    }

    // Función para filtrar mercados
    function filterMarkets(searchTerm) {
        const marketsList = document.getElementById('markets-list');
        const noResults = document.getElementById('dropdown-no-results');
        const currentMarketId = {{ $market->idMercado ?? 'null' }};
        
        // Filtrar mercados
        const filteredMarkets = availableMarkets.filter(market => {
            return market.idMercado !== currentMarketId && 
                   market.estado === 'ACTIVO' && 
                   market.solicitud === 'APROBADO' &&
                   (searchTerm === '' || 
                    market.mercado.toLowerCase().includes(searchTerm) ||
                    market.idMercado.toString().includes(searchTerm));
        });
        
        // Limpiar lista
        marketsList.innerHTML = '';
        
        if (filteredMarkets.length === 0) {
            marketsList.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
            marketsList.classList.remove('hidden');
            
            // Agregar mercados filtrados
            filteredMarkets.forEach(market => {
                const marketItem = createMarketItem(market);
                marketsList.appendChild(marketItem);
            });
        }
    }

    // Crear elemento de mercado para la lista
    function createMarketItem(market) {
        const item = document.createElement('div');
        item.className = 'market-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
        item.onclick = () => selectMarket(market);
        
        item.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-store text-purple-600 text-xs"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">${market.mercado}</p>
                    
                </div>
                <div class="flex-shrink-0">
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </div>
            </div>
        `;
        
        return item;
    }

    // Seleccionar un mercado
    function selectMarket(market) {
        // Actualizar campos ocultos y display
        document.getElementById('selected-market-id').value = market.idMercado;
        document.getElementById('selected-market-name').textContent = market.mercado;
        document.getElementById('selected-market-display').classList.remove('hidden');
        
        // Actualizar input y ocultar dropdown
        document.getElementById('market-search-input').value = market.mercado;
        document.getElementById('markets-dropdown').classList.add('hidden');
        
        console.log('Market selected:', market);
    }

    // Limpiar selección de mercado
    window.clearSelectedMarket = function() {
        document.getElementById('selected-market-id').value = '';
        document.getElementById('selected-market-name').textContent = '';
        document.getElementById('selected-market-display').classList.add('hidden');
        document.getElementById('market-search-input').value = '';
        
        // Mostrar dropdown nuevamente si hay mercados
        if (availableMarkets.length > 0) {
            document.getElementById('markets-dropdown').classList.remove('hidden');
            filterMarkets('');
        }
    };

    // Función para cargar mercados disponibles (actualizada)
    function loadAvailableMarkets() {
        const dropdown = document.getElementById('markets-dropdown');
        const loadingState = document.getElementById('dropdown-loading');
        const marketsList = document.getElementById('markets-list');
        const noResults = document.getElementById('dropdown-no-results');
        
        // Mostrar dropdown con loading
        dropdown.classList.remove('hidden');
        loadingState.classList.remove('hidden');
        marketsList.classList.add('hidden');
        noResults.classList.add('hidden');
        
        // Hacer petición AJAX para obtener mercados
        $.ajax({
            url: '/market-management/markets/api',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    availableMarkets = response.data;
                    loadingState.classList.add('hidden');
                    filterMarkets(''); // Mostrar todos los mercados inicialmente
                    console.log(`Cargados ${availableMarkets.length} mercados disponibles`);
                } else {
                    showError('Error al cargar los mercados disponibles');
                    showDropdownError('Error al cargar mercados');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error loading markets:', {xhr, textStatus, errorThrown});
                showError('Error al conectar con el servidor para cargar mercados');
                showDropdownError('Error de conexión');
            }
        });
    }

    // Mostrar error en el dropdown
    function showDropdownError(message) {
        const dropdown = document.getElementById('markets-dropdown');
        const loadingState = document.getElementById('dropdown-loading');
        const marketsList = document.getElementById('markets-list');
        const noResults = document.getElementById('dropdown-no-results');
        
        loadingState.classList.add('hidden');
        marketsList.classList.add('hidden');
        noResults.classList.remove('hidden');
        noResults.innerHTML = `
            <div class="p-3 text-center text-red-500">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                ${message}
            </div>
        `;
    }

    // Cerrar modales con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            closeRemoveProductModal();
            closeChangeMarketModal();
        }
    });

    // Cerrar modales al hacer clic fuera
    document.getElementById('remove-product-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeRemoveProductModal();
        }
    });

    document.getElementById('change-market-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeChangeMarketModal();
        }
    });
});

// Funciones de notificación elegantes
function showSuccessNotification(message) {
    const notification = $(`
        <div class="notification-toast fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md transform translate-x-full opacity-0 transition-all duration-300 ease-out">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-200"></i>
                <div class="flex-1">
                    <h4 class="font-bold text-sm mb-1">¡Éxito!</h4>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
                <button onclick="$(this).parent().parent().fadeOut(300, function() { $(this).remove(); })" class="ml-3 text-green-200 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    // Animación de entrada
    setTimeout(() => {
        notification.removeClass('translate-x-full opacity-0');
    }, 100);
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        notification.addClass('translate-x-full opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

function showErrorNotification(message) {
    const notification = $(`
        <div class="notification-toast fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md transform translate-x-full opacity-0 transition-all duration-300 ease-out">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-200"></i>
                <div class="flex-1">
                    <h4 class="font-bold text-sm mb-1">Error</h4>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
                <button onclick="$(this).parent().parent().fadeOut(300, function() { $(this).remove(); })" class="ml-3 text-red-200 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    // Animación de entrada
    setTimeout(() => {
        notification.removeClass('translate-x-full opacity-0');
    }, 100);
    
    // Auto-remove después de 7 segundos
    setTimeout(() => {
        notification.addClass('translate-x-full opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 7000);
}
</script>
@endpush

@push('styles')
<style>
/* Custom styles for the products table */
.table-container {
    max-height: 600px;
    overflow-y: auto;
}

.product-row:hover {
    background-color: #f9fafb;
}

/* Loading overlay styles */
#loading-overlay {
    min-height: 400px;
    backdrop-filter: blur(2px);
}

/* Loading animation */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Improved spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Badge styles */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Responsive table */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table-responsive th,
    .table-responsive td {
        padding: 0.5rem;
    }
    
    #loading-overlay {
        min-height: 300px;
    }
}

/* Modal styles */
.modal {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.modal-content {
    max-height: 90vh;
    overflow-y: auto;
}

/* Centrado perfecto de modales */
#remove-product-modal,
#change-market-modal {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

#remove-product-modal.hidden,
#change-market-modal.hidden {
    display: none !important;
}

/* Action buttons hover effects */
.action-btn {
    transition: all 0.2s ease-in-out;
}

.action-btn:hover {
    transform: scale(1.1);
}

/* Select dropdown styling */
#new-market-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

#new-market-select:disabled {
    background-color: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}

/* Market search dropdown styles */
#markets-dropdown {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    background: white;
    z-index: 1000;
}

.market-item {
    transition: background-color 0.15s ease-in-out;
}

.market-item:hover {
    background-color: #f9fafb;
}

.market-item:active {
    background-color: #f3f4f6;
}

/* Search input with icon */
#market-search-input {
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#market-search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Selected market display */
#selected-market-display {
    animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Dropdown scrollbar styling */
#markets-dropdown::-webkit-scrollbar {
    width: 6px;
}

#markets-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

#markets-dropdown::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

#markets-dropdown::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Loading states */
.markets-loading {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Responsive adjustments for dropdown */
@media (max-width: 640px) {
    #markets-dropdown {
        max-height: 200px;
    }
    
    .market-item {
        padding: 0.75rem;
    }
}

/* Toast notifications positioning */
.toast {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 60;
    transition: all 0.3s ease-in-out;
}

/* Notification toast animations */
.notification-toast {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(8px);
}

.notification-toast:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
}

/* Responsive notifications */
@media (max-width: 640px) {
    .notification-toast {
        left: 1rem;
        right: 1rem;
        max-width: none;
    }
}
</style>
@endpush
