// Productos JavaScript Functions - Optimized Version
$(document).ready(function() {
    // Verificar que estamos en el módulo de productos
    if (!window.productosModule) {
        console.error('Este script solo funciona en el módulo de productos');
        return;
    }
    
    console.log('Módulo de productos inicializado correctamente');
    
    let currentCursor = null;
    let isLoading = false;
    let totalProductsLoaded = 0;
    let loadingTimer = null;
    let loadingStartTime = null;
    
    // Search variables removed - no longer needed

    // Filter variables
    let currentFilters = {
        descripcionProducto: '',
        marcaGenerico: '',
        eticoPopular: '',
        fuente: '',
        mercado: '',
        molecula: '',
        descripcionFF3: '',
        descripcionATC4: '',
        descripcionLaboratorio: '',
        descripcionCorporacion: ''
    };
    let filterTimeout = null;

    // Search functionality removed
    
    // Initialize filter functionality
    initializeSearchableFilters();
    
    // Initialize normal filters (select dropdowns)
    initializeNormalFilters();
    
    // Sync filter state on page load
    syncFilterButtonsState();
    
    // Load initial products
    loadProducts();

    // Clear all filters functionality
    $('#clear-all-filters').on('click', function() {
        // Ocultar todos los dropdowns inmediatamente
        $('.filter-dropdown').removeClass('show');
        
        // Prevent multiple clicks during loading
        if (isLoading) {
            return;
        }
        
        // Clear all filter inputs and reset currentFilters
        Object.keys(currentFilters).forEach(filterKey => {
            currentFilters[filterKey] = '';
            const input = $(`#filter-${filterKey}`);
            input.val('');
            
            // Hide dropdown results
            $(`#${filterKey}-results`).addClass('hidden');
            
            // Hide clear button
            $(`#clear-filter-${filterKey}`).addClass('hidden');
        });
        
        // Search functionality removed
        
        // Reset pagination
        currentCursor = null;
        totalProductsLoaded = 0;
        
        // Reload products
        loadProducts();
    });

    // Search functionality removed

    // Initialize searchable filters (for large datasets)
    function initializeSearchableFilters() {
        const searchableFilters = ['descripcionProducto', 'molecula', 'descripcionFF3', 'descripcionATC4', 'descripcionLaboratorio', 'descripcionCorporacion'];
        
        searchableFilters.forEach(filterKey => {
            const input = $(`#filter-${filterKey}`);
            const resultsDiv = $(`#${filterKey}-results`);
            const clearButton = $(`#clear-filter-${filterKey}`);
            let searchTimeout = null;

            if (input.length === 0) return;

            // Input event for searching
            input.on('input', function() {
                const query = $(this).val().trim();
                
                // Actualizar currentFilters inmediatamente
                currentFilters[filterKey] = query;
                
                // Show/hide clear button
                if (query.length > 0) {
                    clearButton.removeClass('hidden');
                } else {
                    clearButton.addClass('hidden');
                    resultsDiv.removeClass('show');
                }

                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Set new timeout for search
                searchTimeout = setTimeout(() => {
                    if (query.length >= 1) {
                        loadFilterOptions(filterKey, query);
                    } else {
                        resultsDiv.removeClass('show');
                        // Si no hay búsqueda, aplicar filtros para reflejar el cambio
                        applyFilters();
                    }
                }, 300);
            });

            // Focus event to show results
            input.on('focus', function() {
                const query = $(this).val().trim();
                if (query.length >= 1) {
                    loadFilterOptions(filterKey, query);
                }
            });

            // Click outside to hide results
            $(document).on('click', function(e) {
                if (!$(e.target).closest(`#filter-${filterKey}, #${filterKey}-results`).length) {
                    resultsDiv.removeClass('show');
                }
            });

            // Clear individual filter
            clearButton.on('click', function() {
                console.log(`🧹 Limpiando filtro individual: ${filterKey}`);
                input.val('');
                clearButton.addClass('hidden');
                resultsDiv.removeClass('show');
                currentFilters[filterKey] = '';
                
                // Debug: Log state after clearing
                console.log(`🧹 Filtro ${filterKey} limpiado. Estado actual:`, currentFilters);
                
                applyFilters();
            });
        });
    }

    // Initialize normal filters (select dropdowns for small datasets)
    function initializeNormalFilters() {
        const normalFilters = ['marcaGenerico', 'eticoPopular', 'fuente', 'mercado'];
        
        normalFilters.forEach(filterKey => {
            const select = $(`#filter-${filterKey}`);
            const clearButton = $(`#clear-filter-${filterKey}`);

            if (select.length === 0) return;

            // Load options for normal filters
            loadNormalFilterOptions(filterKey);

            // Handle select change
            select.on('change', function() {
                const value = $(this).val();
                currentFilters[filterKey] = value;
                
                console.log(`📋 Select ${filterKey} cambió a:`, value);
                
                // Show/hide clear button
                if (value && value.length > 0) {
                    clearButton.removeClass('hidden');
                } else {
                    clearButton.addClass('hidden');
                }

                // Apply filters with debounce
                if (filterTimeout) {
                    clearTimeout(filterTimeout);
                }
                filterTimeout = setTimeout(() => {
                    console.log(`📋 Aplicando filtros después de cambio en ${filterKey}`);
                    applyFilters();
                }, 300);
            });

            // Clear individual filter
            clearButton.on('click', function() {
                console.log(`🧹 Limpiando filtro select: ${filterKey}`);
                select.val('');
                clearButton.addClass('hidden');
                currentFilters[filterKey] = '';
                
                // Debug: Log state after clearing
                console.log(`🧹 Filtro ${filterKey} limpiado. Estado actual:`, currentFilters);
                
                applyFilters();
            });
        });


    }

    // Load options for normal filters (select dropdowns)
    function loadNormalFilterOptions(filterType) {
        // Use higher limit for mercado
        const limit = filterType === 'mercado' ? 500 : 100;
        
        $.ajax({
            url: '/productos/filter-options',
            method: 'GET',
            data: {
                filter_type: filterType,
                limit: limit
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    const select = $(`#filter-${filterType}`);
                    
                    // Debug para mercado
                    if (filterType === 'mercado') {
                        console.log('Mercado options received:', response.data);
                        console.log('Mercado options count:', response.data.length);
                    }
                    
                    // Clear existing options except first
                    select.find('option:not(:first)').remove();
                    
                    // Ensure data is an array
                    const options = Array.isArray(response.data) ? response.data : [];
                    
                    // Add new options
                    options.forEach(option => {
                        if (option && option.trim() !== '') {
                            select.append(`<option value="${option}">${option}</option>`);
                        }
                    });
                    
                    // Debug para mercado
                    if (filterType === 'mercado') {
                        console.log('Mercado options added to select:', select.find('option').length - 1);
                    }
                } else {
                    console.error(`Error en respuesta de ${filterType}:`, response);
                }
            },
            error: function(xhr, status, error) {
                console.error(`Error loading ${filterType} options:`, error);
            }
        });
    }

    // Load filter options for searchable filters
    function loadFilterOptions(filterType, query) {
        $.ajax({
            url: '/productos/filter-options',
            method: 'GET',
            data: {
                filter_type: filterType,
                search: query,
                limit: 20
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    showFilterResults(filterType, response.data, query);
                } else {
                    console.error(`Error en respuesta de ${filterType}:`, response.message || 'Respuesta inválida');
                    // Mostrar mensaje de error en el dropdown
                    const resultsDiv = $(`#${filterType}-results`);
                    resultsDiv.html(`
                        <div class="px-3 py-2 text-red-500 text-sm">
                            Error al cargar opciones
                        </div>
                    `).removeClass('hidden');
                }
            },
            error: function(xhr, status, error) {
                console.error(`Error loading ${filterType} options:`, error);
            }
        });
    }

    // Show filter results in dropdown - Simplified version
    function showFilterResults(filterType, results, query) {
        const resultsDiv = $(`#${filterType}-results`);
        
        // Ensure results is an array
        const validResults = Array.isArray(results) ? results : [];
        
        if (validResults.length === 0) {
            resultsDiv.html(`
                <div class="no-results">
                    No se encontraron resultados para "${query}"
                </div>
            `).addClass('show');
            return;
        }

        let html = '';
        validResults.forEach(option => {
            // Highlight matching text
            const highlightedText = option.replace(new RegExp(`(${query})`, 'gi'), '<mark>$1</mark>');
            
            html += `
                <div class="filter-option" 
                     data-value="${option}" 
                     data-filter="${filterType}">
                    ${highlightedText}
                </div>
            `;
        });
        
        resultsDiv.html(html).addClass('show');

        // Handle option click
        resultsDiv.find('.filter-option').on('click', function() {
            const value = $(this).data('value');
            const filter = $(this).data('filter');
            
            console.log(`✅ Seleccionando opción para ${filter}:`, value);
            
            // Set the input value
            $(`#filter-${filter}`).val(value);
            currentFilters[filter] = value;
            
            // Show/hide clear button
            const clearButton = $(`#clear-filter-${filter}`);
            if (value && value.length > 0) {
                clearButton.removeClass('hidden');
            } else {
                clearButton.addClass('hidden');
            }
            
            // Hide results
            resultsDiv.removeClass('show');
            
            console.log(`✅ Filtro ${filter} actualizado. Estado actual:`, currentFilters);
            
            // Apply filters
            applyFilters();
        });
    }

    // Filter toggle functionality removed - filters are now always visible

    function applyFilters() {
        // Reset pagination when filtering
        currentCursor = null;
        totalProductsLoaded = 0;
        
        // Hide any existing dropdown results immediately
        $('.filter-dropdown').removeClass('show');
        
        // Debug: Log current filters state
        console.log('🔍 Aplicando filtros:', currentFilters);
        console.log('🔍 Filtros activos:', Object.keys(currentFilters).filter(key => currentFilters[key] && currentFilters[key].length > 0));
        
        // Actualizar contador de filtros activos
        updateActiveFiltersCounter();
        
        // Ensure loading state is properly handled
        if (isLoading) {
            return; // Prevent multiple concurrent requests
        }
        
        // Load products with current filters
        loadProducts();
    }

    // Search functionality removed

    function loadProducts(cursor = null, append = false) {
        if (isLoading) return;
        
        isLoading = true;
        
        // Ocultar todos los dropdowns al iniciar cualquier carga
        $('.filter-dropdown').removeClass('show');
        
        // Solo mostrar loading overlay para carga completa (no para "cargar más")
        if (!append) {
            showLoadingState();
        }

        const url = `/productos/api`;
        const params = new URLSearchParams({
            per_page: 50
        });

        if (cursor) {
            params.append('cursor', cursor);
        }

        // Search functionality removed

        // Add filter parameters
        Object.keys(currentFilters).forEach(filterKey => {
            if (currentFilters[filterKey] && currentFilters[filterKey].length > 0) {
                params.append(`filter_${filterKey}`, currentFilters[filterKey]);
            }
        });

        $.ajax({
            url: `${url}?${params}`,
            method: 'GET',
            timeout: 30000,
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
                    
                    // Update pagination info
                    updatePaginationInfo(response);
                    
                    // Update total products counter
                    updateProductsCounter(totalProductsLoaded);
                    
                    // Search results info removed
                    
                } else {
                    showError(response.message || 'Error al cargar productos');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error al cargar productos';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (status === 'timeout') {
                    errorMessage = 'La consulta tardó demasiado tiempo. Intenta con filtros más específicos.';
                } else {
                    errorMessage = `Error de conexión: ${error}`;
                }
                
                showError(errorMessage);
                console.error('Error loading products:', xhr, status, error);
            },
            complete: function() {
                isLoading = false;
                hideLoadingState();
            }
        });
    }

    function updateProductsTable(products) {
        const tableBody = $('#products-table-body');
        
        // Limpiar selecciones al cargar nuevos productos
        clearProductSelections();
        
        // Asegurar que la tabla esté visible
        $('.products-table').removeClass('hidden');
        tableBody.removeClass('hidden');
        
        if (products.length === 0) {
            tableBody.html(`
                <tr>
                    <td colspan="13" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-search text-4xl mb-4 block"></i>
                        <p class="text-lg font-medium mb-2">No se encontraron productos</p>
                        <p class="text-sm">Intenta con otros criterios de búsqueda o filtros</p>
                    </td>
                </tr>
            `);
            $('#empty-state').removeClass('hidden');
            return;
        }

        $('#empty-state').addClass('hidden');
        
        let html = '';
        products.forEach(product => {
            html += generateProductRow(product);
        });
        
        tableBody.html(html);
    }

    function appendProductsToTable(products) {
        const tableBody = $('#products-table-body');
        
        let html = '';
        products.forEach(product => {
            html += generateProductRow(product);
        });
        
        tableBody.append(html);
    }

    function generateProductRow(product) {
        const descripcionCompleta = product['descripcionPresentacion'] || '-';
        const descripcionProducto = product['descripcionProducto'] || '-';
        const marcaGenerico = product['marcaGenerico'] || '-';
        const eticoPopular = product['eticoPopular'] || '-';
        const fuente = product['fuente'] || '-';
        const molecula = product['molecula'] || '-';
        const descripcionFF3 = formatCodeDescription(product['codigoFF3'], product['descripcionFF3']);
        const descripcionATC4 = formatCodeDescription(product['codigoATC4'], product['descripcionATC4']);
        
        // Versiones para title (sin HTML)
        const titleFF3 = formatCodeDescriptionPlain(product['codigoFF3'], product['descripcionFF3']);
        const titleATC4 = formatCodeDescriptionPlain(product['codigoATC4'], product['descripcionATC4']);
        const mercado = product['mercado'] || '-';
        const laboratorio = product['descripcionLaboratorio'] || '-';
        
        // Determinar si es Medifarma para resaltar toda la fila
        const isMedifarma = laboratorio && laboratorio.toUpperCase().includes('MEDIFARMA');
        const rowClass = isMedifarma ? 'hover:bg-red-50 divide-x divide-gray-200 bg-red-25' : 'hover:bg-gray-50 divide-x divide-gray-200';
        
        return `
            <tr class="${rowClass}" data-product-code="${product['codigoPresentacion']}">
                <!-- Checkbox 3% -->
                <td class="w-[3%] px-2 py-1 text-center">
                    <input type="checkbox" class="product-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                           data-product-code="${product['codigoPresentacion']}"
                           data-product-name="${escapeForJs(product['descripcionPresentacion'])}"
                           data-fuente="${product['fuente'] || 'IQV'}"
                           onchange="updateSelectedProducts()">
                </td>
                <!-- Descripción 15% -->
                <td class="w-[15%] px-2 py-1 text-[10px] text-gray-900 description-cell" title="${descripcionCompleta}">
                    <div class="leading-tight font-medium whitespace-normal break-words text-left">
                        ${descripcionCompleta}
                    </div>
                </td>
                <!-- Producto 14% -->
                <td class="w-[14%] px-2 py-1 text-[10px] text-gray-500 product-cell" title="${descripcionProducto}">
                    <div class="leading-tight whitespace-normal break-words text-left">
                        ${descripcionProducto}
                    </div>
                </td>
                <!-- Marca/Genérico 9% -->
                <td class="w-[9%] px-2 py-1 text-center text-[10px] text-gray-500 border-l-2 border-gray-300">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getBrandClass(marcaGenerico)}" title="${marcaGenerico}">
                        ${marcaGenerico}
                    </span>
                </td>
                <!-- Ético/Popular 9% -->
                <td class="w-[9%] px-2 py-1 text-center text-[10px] text-gray-500 border-r-2 border-gray-300">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getEthicClass(eticoPopular)}" title="${eticoPopular}">
                        ${eticoPopular}
                    </span>
                </td>
                <!-- Fuente 7% -->
                <td class="w-[7%] px-2 py-1 text-center text-[10px] text-gray-500">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getFuenteClass(fuente)}" title="${fuente}">
                        ${fuente}
                    </span>
                </td>
                <!-- Molécula 14% -->
                <td class="w-[14%] px-2 py-1 text-[10px] text-gray-500 molecule-cell" title="${molecula}">
                    <div class="text-[10px] leading-tight whitespace-normal break-words">
                        ${molecula}
                    </div>
                </td>
                <!-- FF3 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] text-gray-500" title="${titleFF3}">
                    <div class="whitespace-normal break-words">
                        ${descripcionFF3}
                    </div>
                </td>
                <!-- ATC4 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] text-gray-500" title="${titleATC4}">
                    <div class="whitespace-normal break-words">
                        ${descripcionATC4}
                    </div>
                </td>
                <!-- Laboratorio 11% -->
                <td class="w-[11%] px-2 py-1 text-[10px] ${getLaboratorioClass(laboratorio)}" title="${laboratorio}">
                    <div class="whitespace-normal break-words">
                        ${laboratorio}
                    </div>
                </td>
                <!-- Corporación 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] text-gray-500" title="${product['descripcionCorporacion'] || '-'}">
                    <div class="whitespace-normal break-words">
                        ${product['descripcionCorporacion'] || '-'}
                    </div>
                </td>
                <!-- Mercado 10% -->
                <td class="w-[10%] px-2 py-1 text-center text-[9px] text-gray-600 market-cell">
                    <div class="flex flex-col items-center justify-center">
                        <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-[9px] font-medium ${getMarketClass(mercado)} w-full" title="${mercado}">
                            <span class="text-center">
                        ${mercado}
                    </span>
                        </span>
                    </div>
                </td>
                <!-- Acciones 4% -->
                <td class="w-[4%] px-0 py-1 text-center actions-cell">
                    <div class="flex flex-col items-center justify-center space-y-0.5">
                        <button onclick="window.openRemoveProductModal('${product['codigoPresentacion']}', '${escapeForJs(product['descripcionPresentacion'])}')" 
                                class="action-button text-red-600 hover:text-red-900 transition-colors rounded hover:bg-red-50" 
                                title="Quitar producto del mercado">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button onclick="window.openChangeMarketModal('${product['codigoPresentacion']}', '${escapeForJs(product['descripcionPresentacion'])}')" 
                                class="action-button text-blue-600 hover:text-blue-900 transition-colors rounded hover:bg-blue-50" 
                                title="Cambiar producto a otro mercado">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
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

    // Helper functions for styling classes
    function getBrandClass(marcaGenerico) {
        switch(marcaGenerico?.toUpperCase()) {
            case 'MARCA':
                return 'bg-blue-100 text-blue-800';
            case 'GENERICO':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function getEthicClass(eticoPopular) {
        switch(eticoPopular?.toUpperCase()) {
            case 'ETICO':
                return 'bg-purple-100 text-purple-800';
            case 'POPULAR':
                return 'bg-orange-100 text-orange-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function getFuenteClass(fuente) {
        switch(fuente?.toUpperCase()) {
            case 'IQV':
                return 'bg-blue-100 text-blue-800';
            case 'IQVIA':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function getMarketClass(mercado) {
        if (mercado === 'RESTO') {
            return 'bg-red-100 text-red-800';
        } else if (mercado && mercado !== '-') {
            return 'bg-green-100 text-green-800';
        } else {
            return 'bg-gray-100 text-gray-800';
        }
    }

    function getLaboratorioClass(laboratorio) {
        if (laboratorio && laboratorio.toUpperCase().includes('MEDIFARMA')) {
            return 'medifarma-lab text-red-700 font-semibold';
        } else {
            return 'text-gray-500';
        }
    }

    function formatCodeDescription(codigo, descripcion) {
        if (!codigo && !descripcion) {
            return '-';
        }
        if (!codigo) {
            return descripcion || '-';
        }
        if (!descripcion) {
            return `<span class="code-highlight">${codigo}</span>`;
        }
        return `<span class="code-highlight">${codigo}</span> - ${descripcion}`;
    }

    function formatCodeDescriptionPlain(codigo, descripcion) {
        if (!codigo && !descripcion) {
            return '-';
        }
        if (!codigo) {
            return descripcion || '-';
        }
        if (!descripcion) {
            return codigo || '-';
        }
        return `${codigo} - ${descripcion}`;
    }

    function updatePaginationInfo(response) {
        const paginationInfo = $('#pagination-info');
        const paginationControls = $('#pagination-controls');
        
        if (response.pagination.has_more_pages) {
            // Show load more button
            paginationControls.html(`
                <button id="load-more-btn" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm"
                        onclick="loadMoreProducts()">
                    <i class="fas fa-plus mr-2"></i>
                    Cargar más productos
                </button>
            `);
            currentCursor = response.pagination.next_cursor;
        } else {
            paginationControls.html(`
                <span class="text-sm text-gray-500">
                    <i class="fas fa-check-circle mr-1"></i>
                    Todos los productos cargados
                </span>
            `);
            currentCursor = null;
        }
        
        paginationInfo.text(`Mostrando ${totalProductsLoaded} productos`);
    }

    function updateProductsCounter(count) {
        $('#total-products').text(count.toLocaleString());
    }

    // Search results functionality removed

    function showLoadingState() {
        // Ocultar todos los dropdowns inmediatamente
        $('.filter-dropdown').removeClass('show');
        
        $('#loading-overlay').removeClass('hidden');
        $('#loading-state').removeClass('hidden');
        
        // Start loading timer
        loadingStartTime = Date.now();
        loadingTimer = setInterval(() => {
            const elapsed = Math.floor((Date.now() - loadingStartTime) / 1000);
            $('#loading-timer').text(`${elapsed}s`);
        }, 1000);
    }

    function hideLoadingState() {
        $('#loading-overlay').addClass('hidden');
        $('#loading-state').addClass('hidden');
        
        if (loadingTimer) {
            clearInterval(loadingTimer);
            loadingTimer = null;
        }
        
        // Asegurar que el contenido sea visible
        $('#products-table-body').removeClass('hidden');
        $('.products-table').removeClass('hidden');
    }

    function showError(message) {
        console.error('Error:', message);
        
        // Show error in the table
        $('#products-table-body').html(`
            <tr>
                <td colspan="13" class="px-6 py-8 text-center text-red-600">
                    <i class="fas fa-exclamation-triangle text-4xl mb-4 block"></i>
                    <p class="text-lg font-medium mb-2">Error al cargar productos</p>
                    <p class="text-sm">${message}</p>
                    <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Intentar de nuevo
                    </button>
                </td>
            </tr>
        `);
        
        hideLoadingState();
    }

    // Initialize dropdown repositioning
    function initializeDropdownRepositioning() {
        // Hide dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filter-container').length) {
                $('.filter-dropdown').addClass('hidden');
            }
        });
        
        // Hide dropdowns on scroll for better UX
        $(window).on('scroll', function() {
            $('.filter-dropdown').addClass('hidden');
        });
        
        // Hide dropdowns when scrolling inside any container
        $('*').on('scroll', function() {
            $('.filter-dropdown').addClass('hidden');
        });
    }



    // Funciones del sidebar eliminadas - usando diseño original

    // Global function for load more button
    window.loadMoreProducts = function() {
        if (currentCursor && !isLoading) {
            loadProducts(currentCursor, true);
        }
    };

    // Variables globales para las modales
    window.currentProductCode = null;
    window.currentProductName = null;
    window.availableMarkets = [];

    // Funciones para Modal de Quitar Producto
    window.openRemoveProductModal = function(productCode, productName) {
        console.log('Intentando abrir modal para:', productCode, productName);
        
        const modal = document.getElementById('remove-product-modal');
        console.log('Modal encontrada:', modal);
        
        if (!modal) {
            console.error('Modal remove-product-modal no encontrada!');
            return;
        }
        
        window.currentProductCode = productCode;
        window.currentProductName = productName;
        
        const nameElement = document.getElementById('remove-product-name');
        const codeElement = document.getElementById('remove-product-code');
        
        if (nameElement) nameElement.textContent = productName;
        if (codeElement) codeElement.textContent = productCode;
        
        modal.classList.remove('hidden');
        console.log('Modal debería estar visible ahora');
    };

    window.closeRemoveProductModal = function() {
        document.getElementById('remove-product-modal').classList.add('hidden');
        window.currentProductCode = null;
        window.currentProductName = null;
    };

    // Función global para quitar producto del mercado
    window.removeProductFromMarket = function(){
        if (!window.currentProductCode) {
            return;
        }
        // Abrir modal de confirmación final
        document.getElementById('final-confirmation-modal').classList.remove('hidden');
    };

    // Funciones para Modal de Cambiar Mercado
    window.openChangeMarketModal = function(productCode, productName) {
        console.log('Intentando abrir modal de cambio para:', productCode, productName);
        
        const modal = document.getElementById('change-market-modal');
        console.log('Modal de cambio encontrada:', modal);
        
        if (!modal) {
            console.error('Modal change-market-modal no encontrada!');
            return;
        }
        
        window.currentProductCode = productCode;
        window.currentProductName = productName;
        
        const nameElement = document.getElementById('change-product-name');
        const codeElement = document.getElementById('change-product-code');
        const currentMarketElement = document.getElementById('current-market-name');
        
        if (nameElement) nameElement.textContent = productName;
        if (codeElement) codeElement.textContent = productCode;
        
        // Buscar el mercado actual del producto en la tabla
        const productRow = document.querySelector(`tr[data-product-code="${productCode}"]`);
        if (productRow && currentMarketElement) {
            const marketCell = productRow.querySelector('.market-cell span');
            const currentMarket = marketCell ? marketCell.textContent.trim() : 'RESTO';
            currentMarketElement.textContent = currentMarket;
        }
        
        // Limpiar búsqueda y selección previa
        const searchInput = document.getElementById('market-search-input');
        const selectedDisplay = document.getElementById('selected-market-display');
        const selectedMarketId = document.getElementById('selected-market-id');
        
        if (searchInput) searchInput.value = '';
        if (selectedDisplay) selectedDisplay.classList.add('hidden');
        if (selectedMarketId) selectedMarketId.value = '';
        
        // Configurar búsqueda y cargar mercados
        if (typeof setupMarketSearch === 'function') {
            setupMarketSearch();
        }
        if (typeof loadAvailableMarkets === 'function') {
            loadAvailableMarkets();
        }
        
        modal.classList.remove('hidden');
        console.log('Modal de cambio debería estar visible ahora');
    };

    window.closeChangeMarketModal = function() {
        document.getElementById('change-market-modal').classList.add('hidden');
        window.currentProductCode = null;
        window.currentProductName = null;
    };

    // Test de funciones al cargar
    console.log('Funciones de modal disponibles:');
    console.log('openRemoveProductModal:', typeof window.openRemoveProductModal);
    console.log('openChangeMarketModal:', typeof window.openChangeMarketModal);
    
    // Test simple de apertura de modal
    window.testModal = function() {
        console.log('Probando modal...');
        window.openRemoveProductModal('TEST123', 'Producto de prueba');
    };

    // Función para sincronizar el estado de los botones de limpiar
    function syncFilterButtonsState() {
        console.log('🔄 Sincronizando estado de botones de filtros...');
        
        Object.keys(currentFilters).forEach(filterKey => {
            const input = $(`#filter-${filterKey}`);
            const clearButton = $(`#clear-filter-${filterKey}`);
            
            if (input.length > 0 && clearButton.length > 0) {
                const value = input.val() || '';
                
                // Actualizar currentFilters con el valor actual del input
                currentFilters[filterKey] = value;
                
                // Mostrar/ocultar botón de limpiar según el valor
                if (value && value.length > 0) {
                    clearButton.removeClass('hidden');
                    console.log(`🔄 Filtro ${filterKey} tiene valor: "${value}"`);
                } else {
                    clearButton.addClass('hidden');
                }
            }
        });
        
        console.log('🔄 Estado final de filtros:', currentFilters);
        
        // Actualizar contador de filtros activos
        updateActiveFiltersCounter();
    }
    
    // Función para actualizar el contador de filtros activos
    function updateActiveFiltersCounter() {
        const activeFilters = Object.keys(currentFilters).filter(key => currentFilters[key] && currentFilters[key].length > 0);
        const count = activeFilters.length;
        
        // Buscar o crear el indicador de filtros activos
        let indicator = $('#active-filters-indicator');
        if (indicator.length === 0) {
            // Crear indicador si no existe
            indicator = $(`
                <div id="active-filters-indicator" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2 hidden">
                    <i class="fas fa-filter mr-1"></i>
                    <span id="filters-count">0</span> filtros activos
                </div>
            `);
            $('.header-controls').append(indicator);
        }
        
        if (count > 0) {
            indicator.removeClass('hidden');
            $('#filters-count').text(count);
            console.log(`📊 Filtros activos: ${count} (${activeFilters.join(', ')})`);
        } else {
            indicator.addClass('hidden');
            console.log('📊 No hay filtros activos');
        }
    }

    // ===== FUNCIONES PARA SELECCIÓN MÚLTIPLE ===== 
    
    // Variables globales para selección múltiple
    window.selectedProducts = new Set();

    // Función para actualizar el estado de productos seleccionados
    window.updateSelectedProducts = function() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        const selectedCount = checkboxes.length;
        
        // Actualizar contador y visibilidad
        document.getElementById('selected-count').textContent = selectedCount;
        const infoDiv = document.getElementById('selected-products-info');
        const assignBtn = document.getElementById('bulk-assign-btn');
        
        if (selectedCount > 0) {
            infoDiv.classList.remove('hidden');
            assignBtn.classList.remove('hidden');
        } else {
            infoDiv.classList.add('hidden');
            assignBtn.classList.add('hidden');
        }
        
        // Actualizar el Set de productos seleccionados
        window.selectedProducts.clear();
        checkboxes.forEach(checkbox => {
            window.selectedProducts.add({
                code: checkbox.dataset.productCode,
                name: checkbox.dataset.productName,
                fuente: checkbox.dataset.fuente
            });
        });
        
        // Actualizar estado del checkbox "Seleccionar todos"
        const allCheckboxes = document.querySelectorAll('.product-checkbox');
        const selectAllCheckbox = document.getElementById('select-all-products');
        
        if (selectedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (selectedCount === allCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    };

    // Función para seleccionar/deseleccionar todos
    window.toggleSelectAll = function() {
        const selectAllCheckbox = document.getElementById('select-all-products');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        
        updateSelectedProducts();
    };

    // Event listener para el checkbox "Seleccionar todos"
    $('#select-all-products').on('change', function() {
        toggleSelectAll();
    });

    // Función para limpiar selecciones cuando se cargan nuevos productos
    window.clearProductSelections = function() {
        window.selectedProducts.clear();
        document.getElementById('selected-products-info').classList.add('hidden');
        document.getElementById('bulk-assign-btn').classList.add('hidden');
        document.getElementById('select-all-products').checked = false;
        document.getElementById('select-all-products').indeterminate = false;
    };
});
