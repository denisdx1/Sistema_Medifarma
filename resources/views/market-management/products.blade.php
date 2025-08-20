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

    function generateProductRow(product) {
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${product['Código_Presentación'] || '-'}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    <div class="max-w-xs">
                        <p class="font-medium">${product['Descripción_Presentación'] || '-'}</p>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getBrandClass(product['Marca_Genérico'])}">
                        ${product['Marca_Genérico'] || '-'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEthicClass(product['Ético_Popular'])}">
                        ${product['Ético_Popular'] || '-'}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    <div class="max-w-xs">
                        ${product['Molécula'] || '-'}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${product['Código_FF_3'] || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${product['Código_ATC_4'] || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <i class="fas fa-store mr-1"></i>
                        ${product['MERCADO'] || '-'}
                    </span>
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
});
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
</style>
@endpush
