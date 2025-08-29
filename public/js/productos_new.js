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
    
    // Search variables
    let searchTimeout = null;
    let currentSearchQuery = '';
    let isSearching = false;

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

    // Initialize search functionality
    initializeSearch();
    
    // Initialize filter functionality
    initializeSearchableFilters();
    
    // Initialize normal filters (select dropdowns)
    initializeNormalFilters();
    
    // Initialize other components
    initializeToggleFilters();
    
    // Check for auto-select market from sessionStorage and load products
    checkAutoSelectMarketAndLoadProducts();

    // Clear all filters functionality
    $('#clear-all-filters').on('click', function() {
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
        
        // Clear search
        $('#product-search').val('');
        $('#clear-search').addClass('hidden');
        currentSearchQuery = '';
        
        // Hide search results info
        $('#search-results-info').addClass('hidden');
        
        // Reload products
        loadProducts();
    });

    // Initialize search functionality
    function initializeSearch() {
        const searchInput = $('#product-search');
        const clearButton = $('#clear-search');
        const searchResults = $('#search-results-info');
        const searchLoadingIndicator = $('#search-loading-indicator');

        // Real-time search with debouncing
        searchInput.on('input', function() {
            const query = $(this).val().trim();
            
            // Show/hide clear button
            if (query.length > 0) {
                clearButton.removeClass('hidden');
            } else {
                clearButton.addClass('hidden');
            }

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Set new timeout for search
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300); // 300ms debounce
        });

        // Clear search functionality
        clearButton.on('click', function() {
            searchInput.val('');
            clearButton.addClass('hidden');
            searchResults.addClass('hidden');
            searchLoadingIndicator.addClass('hidden');
            
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            performSearch('');
        });
    }

    // Initialize searchable filters (for large datasets)
    function initializeSearchableFilters() {
        const searchableFilters = ['mercado', 'molecula', 'descripcionFF3', 'descripcionATC4', 'descripcionLaboratorio', 'descripcionCorporacion'];
        
        searchableFilters.forEach(filterKey => {
            const input = $(`#filter-${filterKey}`);
            const resultsDiv = $(`#${filterKey}-results`);
            const clearButton = $(`#clear-filter-${filterKey}`);
            let searchTimeout = null;

            if (input.length === 0) return;

            // Input event for searching
            input.on('input', function() {
                const query = $(this).val().trim();
                
                // Show/hide clear button
                if (query.length > 0) {
                    clearButton.removeClass('hidden');
                } else {
                    clearButton.addClass('hidden');
                    resultsDiv.addClass('hidden');
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
                        resultsDiv.addClass('hidden');
                    }
                }, 300);
            });

            // Focus event to show recent results
            input.on('focus', function() {
                const query = $(this).val().trim();
                if (query.length >= 1) {
                    loadFilterOptions(filterKey, query);
                }
            });

            // Click outside to hide results
            $(document).on('click', function(e) {
                if (!$(e.target).closest(`#filter-${filterKey}, #${filterKey}-results`).length) {
                    resultsDiv.addClass('hidden');
                }
            });

            // Clear individual filter
            clearButton.on('click', function() {
                input.val('');
                clearButton.addClass('hidden');
                resultsDiv.addClass('hidden');
                currentFilters[filterKey] = '';
                applyFilters();
            });
        });
    }

    // Initialize normal filters (select dropdowns for small datasets)
    function initializeNormalFilters() {
        const normalFilters = ['marcaGenerico', 'eticoPopular', 'fuente'];
        
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
                    applyFilters();
                }, 300);
            });

            // Clear individual filter
            clearButton.on('click', function() {
                select.val('');
                clearButton.addClass('hidden');
                currentFilters[filterKey] = '';
                applyFilters();
            });
        });

        // Special handling for descripcionProducto (input field)
        const productInput = $('#filter-descripcionProducto');
        const productClearButton = $('#clear-filter-descripcionProducto');
        
        if (productInput.length > 0) {
            productInput.on('input', function() {
                const value = $(this).val().trim();
                currentFilters['descripcionProducto'] = value;
                
                // Show/hide clear button
                if (value.length > 0) {
                    productClearButton.removeClass('hidden');
                } else {
                    productClearButton.addClass('hidden');
                }

                // Apply filters with debounce
                if (filterTimeout) {
                    clearTimeout(filterTimeout);
                }
                filterTimeout = setTimeout(() => {
                    applyFilters();
                }, 500); // Longer debounce for text input
            });

            productClearButton.on('click', function() {
                productInput.val('');
                productClearButton.addClass('hidden');
                currentFilters['descripcionProducto'] = '';
                applyFilters();
            });
        }
    }

    // Load options for normal filters (select dropdowns)
    function loadNormalFilterOptions(filterType) {
        $.ajax({
            url: '/productos/filter-options',
            method: 'GET',
            data: {
                type: filterType,
                limit: 100
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    const select = $(`#filter-${filterType}`);
                    
                    // Clear existing options except first
                    select.find('option:not(:first)').remove();
                    
                    // Add new options
                    response.data.forEach(option => {
                        if (option && option.trim() !== '') {
                            select.append(`<option value="${option}">${option}</option>`);
                        }
                    });
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
                type: filterType,
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
                }
            },
            error: function(xhr, status, error) {
                console.error(`Error loading ${filterType} options:`, error);
            }
        });
    }

    // Show filter results in dropdown
    function showFilterResults(filterType, results, query) {
        const resultsDiv = $(`#${filterType}-results`);
        
        if (results.length === 0) {
            resultsDiv.html(`
                <div class="px-3 py-2 text-gray-500 text-sm">
                    No se encontraron resultados para "${query}"
                </div>
            `).removeClass('hidden');
            return;
        }

        let html = '';
        results.forEach(option => {
            // Highlight matching text
            const highlightedText = option.replace(new RegExp(`(${query})`, 'gi'), '<mark class="bg-yellow-200">$1</mark>');
            
            html += `
                <div class="filter-option px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm" 
                     data-value="${option}" 
                     data-filter="${filterType}">
                    ${highlightedText}
                </div>
            `;
        });
        
        resultsDiv.html(html).removeClass('hidden');

        // Handle option click
        resultsDiv.find('.filter-option').on('click', function() {
            const value = $(this).data('value');
            const filter = $(this).data('filter');
            
            // Set the input value
            $(`#filter-${filter}`).val(value);
            currentFilters[filter] = value;
            
            // Show clear button
            $(`#clear-filter-${filter}`).removeClass('hidden');
            
            // Hide results
            resultsDiv.addClass('hidden');
            
            // Apply filters
            applyFilters();
        });
    }

    // Initialize filters toggle functionality
    function initializeToggleFilters() {
        $('#toggle-filters').on('click', function() {
            const filtersPanel = $('#filters-panel');
            const chevron = $(this).find('.fas:last-child');
            const span = $(this).find('span');
            
            filtersPanel.toggleClass('hidden');
            
            if (filtersPanel.hasClass('hidden')) {
                chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                span.text('Mostrar Filtros');
            } else {
                chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                span.text('Ocultar Filtros');
            }
        });
    }

    function applyFilters() {
        // Reset pagination when filtering
        currentCursor = null;
        
        // Load products with current filters
        loadProducts(null, false, currentSearchQuery);
    }

    // Perform search function
    function performSearch(query) {
        // Minimum search length
        if (query.length > 0 && query.length < 2) {
            return;
        }

        currentSearchQuery = query;
        
        // Show search loading indicator
        if (query.length > 0) {
            $('#search-loading-indicator').removeClass('hidden');
        }

        // Reset pagination when searching
        currentCursor = null;
        
        // Load products with search
        loadProducts(null, false, query);
    }

    function loadProducts(cursor = null, append = false, searchQuery = null) {
        if (isLoading) return;
        
        isLoading = true;
        
        // Use current search query if none provided
        const search = searchQuery !== null ? searchQuery : currentSearchQuery;
        
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

        // Add search parameter
        if (search && search.length > 0) {
            params.append('search', search);
        }

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
                    
                    // Update search results info
                    updateSearchResults(search, response);
                    
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
                $('#search-loading-indicator').addClass('hidden');
            }
        });
    }

    function updateProductsTable(products) {
        const tableBody = $('#products-table-body');
        
        if (products.length === 0) {
            // Limpiar tabla y mostrar solo el estado vacío estático
            tableBody.html('');
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
        const descripcionFF3 = product['descripcionFF3'] || '-';
        const descripcionATC4 = product['descripcionATC4'] || '-';
        const mercado = product['mercado'] || '-';
        
        return `
            <tr class="hover:bg-gray-50 divide-x divide-gray-200">
                <!-- Descripción 18% -->
                <td class="w-[18%] px-2 py-1 text-[10px] text-gray-900 description-cell">
                    <div class="leading-tight font-medium whitespace-normal break-words text-left">
                        ${descripcionCompleta}
                    </div>
                </td>
                <!-- Producto 14% -->
                <td class="w-[14%] px-2 py-1 text-[10px] text-gray-500 product-cell">
                    <div class="leading-tight whitespace-normal break-words text-left">
                        ${descripcionProducto}
                    </div>
                </td>
                <!-- Marca/Genérico 9% -->
                <td class="w-[9%] px-2 py-1 text-center text-[10px] text-gray-500 border-l-2 border-gray-300">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getBrandClass(marcaGenerico)}">
                        ${marcaGenerico}
                    </span>
                </td>
                <!-- Ético/Popular 9% -->
                <td class="w-[9%] px-2 py-1 text-center text-[10px] text-gray-500 border-r-2 border-gray-300">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getEthicClass(eticoPopular)}">
                        ${eticoPopular}
                    </span>
                </td>
                <!-- Fuente 7% -->
                <td class="w-[7%] px-2 py-1 text-center text-[10px] text-gray-500">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getFuenteClass(fuente)}">
                        ${fuente}
                    </span>
                </td>
                <!-- Molécula 14% -->
                <td class="w-[14%] px-2 py-1 text-[10px] text-gray-500 molecule-cell">
                    <div class="text-[10px] leading-tight whitespace-normal break-words">
                        ${molecula}
                    </div>
                </td>
                <!-- FF3 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] text-gray-500">
                    <div class="whitespace-normal break-words">
                        ${descripcionFF3}
                    </div>
                </td>
                <!-- ATC4 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] text-gray-500">
                    <div class="whitespace-normal break-words">
                        ${descripcionATC4}
                    </div>
                </td>
                <!-- Laboratorio 11% -->
                <td class="w-[11%] px-2 py-1 text-[10px] text-gray-500">
                    <div class="whitespace-normal break-words">
                        ${product['descripcionLaboratorio'] || '-'}
                    </div>
                </td>
                <!-- Corporación 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] text-gray-500">
                    <div class="whitespace-normal break-words">
                        ${product['descripcionCorporacion'] || '-'}
                    </div>
                </td>
                <!-- Mercado 6% -->
                <td class="w-[6%] px-2 py-1 text-center text-[10px] text-gray-600">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getMarketClass(mercado)}">
                        ${mercado}
                    </span>
                </td>
            </tr>
        `;
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

    function updateSearchResults(search, response) {
        const searchResultsInfo = $('#search-results-info');
        const searchResultsText = $('#search-results-text');
        
        if (search && search.length > 0) {
            searchResultsInfo.removeClass('hidden');
            searchResultsText.text(`${response.loaded_count} resultados para "${search}"`);
        } else {
            searchResultsInfo.addClass('hidden');
        }
    }

    function showLoadingState() {
        $('#loading-overlay').removeClass('hidden');
        
        // Start loading timer
        loadingStartTime = Date.now();
        loadingTimer = setInterval(() => {
            const elapsed = Math.floor((Date.now() - loadingStartTime) / 1000);
            $('#loading-timer').text(`${elapsed}s`);
        }, 1000);
    }

    function hideLoadingState() {
        $('#loading-overlay').addClass('hidden');
        
        if (loadingTimer) {
            clearInterval(loadingTimer);
            loadingTimer = null;
        }
    }

    function showError(message) {
        console.error('Error:', message);
        
        // Show error in the table
        $('#products-table-body').html(`
            <tr>
                <td colspan="11" class="px-6 py-8 text-center text-red-600">
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

    // Global function for load more button
    window.loadMoreProducts = function() {
        if (currentCursor && !isLoading) {
            loadProducts(currentCursor, true, currentSearchQuery);
        }
    };

    // Función para verificar si hay un mercado o marca para seleccionar automáticamente y cargar productos
    function checkAutoSelectMarketAndLoadProducts() {
        const autoSelectMarket = sessionStorage.getItem('autoSelectMarket');
        const autoSelectBrand = sessionStorage.getItem('autoSelectBrand');
        
        if (autoSelectMarket) {
            // Limpiar el sessionStorage inmediatamente para evitar que se aplique múltiples veces
            sessionStorage.removeItem('autoSelectMarket');
            
            // Función para verificar y aplicar el filtro de mercado
            function tryApplyMarketFilter(attempts = 0) {
                const mercadoSelect = $('#filter-mercado');
                const totalOptions = mercadoSelect.find('option').length;
                
                // Verificar si la opción existe en el select
                const optionExists = mercadoSelect.find(`option[value="${autoSelectMarket}"]`).length > 0;
                
                if (optionExists) {
                    // Seleccionar el mercado en el filtro
                    mercadoSelect.val(autoSelectMarket);
                    
                    // Actualizar el filtro interno
                    currentFilters.mercado = autoSelectMarket;
                    
                    // Resaltar visualmente el campo de mercado para que el usuario vea que está filtrado
                    mercadoSelect.addClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
                    
                    // Quitar el resaltado después de 3 segundos
                    setTimeout(() => {
                        mercadoSelect.removeClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
                    }, 3000);
                    
                    // Mostrar mensaje específico de carga con filtro de mercado
                    updateLoadingText(`Cargando productos del mercado "${autoSelectMarket}"...`);
                    
                    // Cargar productos directamente con el filtro aplicado (evitando doble carga)
                    loadProducts();
                    
                    return;
                } 
                
                // Si no encontró la opción y no ha hecho muchos intentos, esperar y volver a intentar
                if (attempts < 5 && totalOptions <= 1) { // Solo opciones básicas cargadas
                    setTimeout(() => tryApplyMarketFilter(attempts + 1), 1000);
                } else if (attempts >= 5) {
                    // Si no encontró el mercado después de varios intentos, cargar productos sin filtro
                    loadProducts();
                }
            }
            
            // Cargar opciones de mercado y luego aplicar el filtro
            loadNormalFilterOptions('mercado').then(() => {
                // Aplicar el filtro inmediatamente
                tryApplyMarketFilter();
            }).catch(() => {
                // Si falla cargar las opciones, cargar productos sin filtro
                loadProducts();
            });
        } else if (autoSelectBrand) {
            // Limpiar el sessionStorage inmediatamente para evitar que se aplique múltiples veces
            sessionStorage.removeItem('autoSelectBrand');
            
            // Función para verificar y aplicar el filtro de marca
            function tryApplyBrandFilter(attempts = 0) {
                const brandInput = $('#filter-descripcionProducto');
                
                // Establecer el valor directamente en el input de marca
                brandInput.val(autoSelectBrand);
                
                // Actualizar el filtro interno
                currentFilters.descripcionProducto = autoSelectBrand;
                
                // Mostrar botón de limpiar
                $('#clear-filter-descripcionProducto').removeClass('hidden');
                
                // Resaltar visualmente el campo de marca para que el usuario vea que está filtrado
                brandInput.addClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
                
                // Quitar el resaltado después de 3 segundos
                setTimeout(() => {
                    brandInput.removeClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
                }, 3000);
                
                // Mostrar mensaje específico de carga con filtro de marca
                updateLoadingText(`Cargando productos de la marca "${autoSelectBrand}"...`);
                
                // Cargar productos directamente con el filtro aplicado (evitando doble carga)
                loadProducts();
            }
            
            // Aplicar el filtro de marca inmediatamente
            tryApplyBrandFilter();
        } else {
            // Si no hay autoselección, cargar productos normalmente
            loadProducts();
        }
    }

    // Función para actualizar el texto de carga
    function updateLoadingText(text) {
        const loadingOverlay = $('#loading-overlay');
        if (loadingOverlay.length > 0) {
            const textElement = loadingOverlay.find('span');
            if (textElement.length > 0) {
                textElement.text(text);
            }
        }
    }
});
