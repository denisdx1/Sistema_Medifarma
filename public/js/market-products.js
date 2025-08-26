// Market Products JavaScript Functions
$(document).ready(function() {
    // Variables globales de contexto
    const isGeneralProductsPage = window.location.pathname.startsWith('/productos');
    const marketId = window.marketId;
    
    // Obtener parámetro de franquicia de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const franquiciaFilter = urlParams.get('franquicia');
    
    // Log de debugging
    console.log('Is general products page:', isGeneralProductsPage);
    console.log('Market object received:', window.marketData);
    console.log('Market ID extracted:', marketId);
    console.log('Franquicia filter from URL:', franquiciaFilter);
    
    // Solo verificar marketId si no estamos en página general
    if (!isGeneralProductsPage) {
        if (!marketId || isNaN(marketId)) {
            console.error('Market ID inválido:', marketId);
            showError('Error: ID de mercado inválido. Regresando a la lista de mercados...');
            setTimeout(() => {
                window.location.href = '/market-management';
            }, 3000);
            return;
        }
        console.log('Market ID cargado correctamente:', marketId);
    }
    
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
        descripcionFF3: '',
        descripcionATC4: '',
        descripcionLaboratorio: '',
        fuente: '',
        molecula: '',
        descripcionCorporacion: '',
        marcaGenerico: '',
        eticoPopular: '',
        mercado: ''
    };
    let filterTimeout = null;

    // Initialize search functionality
    initializeSearch();

    // Initialize filter functionality
    initializeFilters();
    
    // Initialize searchable filters
    initializeSearchableFilters();
    
    // Load filter options for normal selects
    loadNormalFilterOptions();
    
    // Debug function to show current filter state
    window.showFilterState = function() {
        console.log('=== CURRENT FILTER STATE ===');
        console.log('currentFilters object:', currentFilters);
        Object.keys(currentFilters).forEach(filterKey => {
            const inputElement = $(`#filter-${filterKey}`);
            const searchElement = $(`#filter-${filterKey}-search`);
            console.log(`${filterKey}:`, {
                currentFilters: currentFilters[filterKey],
                inputValue: inputElement.val(),
                searchValue: searchElement.val(),
                inputExists: inputElement.length > 0,
                searchExists: searchElement.length > 0
            });
        });
        console.log('===========================');
    };

    // Load initial products
    loadProducts();

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

            // Set new timeout for search (300ms debounce)
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        // Clear search
        clearButton.on('click', function() {
            searchInput.val('');
            clearButton.addClass('hidden');
            searchResults.addClass('hidden');
            currentSearchQuery = '';
            
            // Only reload products, keep filters intact
            loadProducts();
        });

        // Handle Enter key
        searchInput.on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                performSearch($(this).val().trim());
            }
        });
    }

    // Initialize filter functionality
    function initializeFilters() {
        // Filtros normales (selects) - solo para los que no son buscables
        const normalFilters = ['marcaGenerico', 'eticoPopular', 'fuente', 'mercado'];
        
        normalFilters.forEach(filterKey => {
            const filterInput = $(`#filter-${filterKey}`);
            const clearButton = $(`#clear-filter-${filterKey}`);

            // Real-time filter with debouncing
            filterInput.on('change', function() {
                const value = $(this).val().trim();
                currentFilters[filterKey] = value;
                
                // Show/hide clear button
                if (value.length > 0) {
                    clearButton.removeClass('hidden');
                } else {
                    clearButton.addClass('hidden');
                }

                applyFilters();
            });

            // Clear individual filter
            clearButton.on('click', function() {
                filterInput.val('');
                clearButton.addClass('hidden');
                currentFilters[filterKey] = '';
                applyFilters();
            });
        });

        // Clear all filters button
        $('#clear-all-filters').on('click', function() {
            // Clear normal filters
            normalFilters.forEach(filterKey => {
                currentFilters[filterKey] = '';
                $(`#filter-${filterKey}`).val('');
                $(`#clear-filter-${filterKey}`).addClass('hidden');
            });
            
            // Clear searchable filters
            const searchableFilters = [
                'descripcionProducto',
                'descripcionLaboratorio', 
                'descripcionCorporacion',
                'molecula',
                'descripcionFF3',
                'descripcionATC4'
            ];
            
            searchableFilters.forEach(filterKey => {
                currentFilters[filterKey] = '';
                $(`#filter-${filterKey}-search`).val('');
                $(`#filter-${filterKey}`).val('');
                $(`#clear-filter-${filterKey}`).addClass('hidden');
                $(`#filter-${filterKey}-dropdown`).addClass('hidden');
            });
            
            applyFilters();
        });

        // Toggle filters panel
        $('#toggle-filters').on('click', function() {
            const filtersPanel = $('#filters-panel');
            const icon = $(this).find('i');
            
            filtersPanel.toggleClass('hidden');
            
            if (filtersPanel.hasClass('hidden')) {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                $(this).find('span').text('Mostrar Filtros');
            } else {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                $(this).find('span').text('Ocultar Filtros');
            }
        });
    }

    // Apply filters function
    function applyFilters() {
        console.log('Applying filters with current state:', currentFilters);
        
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

        // Determinar la URL correcta según el contexto
        let url;
        if (isGeneralProductsPage) {
            url = `/productos/api`;
        } else {
            url = `/market-management/market/${marketId}/products/api`;
        }
        
        const params = new URLSearchParams({
            per_page: 10  // Aumentado para cargar más productos por página
        });

        if (cursor) {
            params.append('cursor', cursor);
        }

        // Add search parameter
        if (search && search.length > 0) {
            params.append('search', search);
        }

        // Add franquicia filter parameter if it exists (solo para páginas de mercado específico)
        if (!isGeneralProductsPage && franquiciaFilter && franquiciaFilter.length > 0) {
            params.append('franquicia', franquiciaFilter);
        }

        // Add filter parameters
        Object.keys(currentFilters).forEach(filterKey => {
            if (currentFilters[filterKey] && currentFilters[filterKey].length > 0) {
                params.append(`filter_${filterKey}`, currentFilters[filterKey]);
                console.log(`Adding filter: filter_${filterKey} = ${currentFilters[filterKey]}`);
            }
        });

        console.log('Final URL parameters:', params.toString());

        $.ajax({
            url: `${url}?${params}`,
            method: 'GET',
            timeout: 30000, // Reducido a 30 segundos para mejor UX
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
                    
                    // Update search results info
                    updateSearchResultsInfo(response.search);
                    
                    // Update total products counter - Mejorado para mercados pequeños
                    if (response.pagination.has_more_pages) {
                        $('#total-products').text(totalProductsLoaded + '+');
                    } else {
                        // Si no hay más páginas, mostrar el total exacto
                        $('#total-products').text(totalProductsLoaded);
                    }
                    
                    if (response.data.length === 0 && !append) {
                        const activeFilters = Object.values(currentFilters).filter(value => value.length > 0);
                        if (response.search && response.search.has_search) {
                            showNoSearchResults(response.search.query);
                        } else if (activeFilters.length > 0) {
                            showNoSearchResults(null); // No search but has filters
                        } else {
                            showEmptyState();
                        }
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
                // Hide search loading indicator
                $('#search-loading-indicator').addClass('hidden');
            }
        });
    }

    // Search result functions
    function updateSearchResultsInfo(searchInfo) {
        const searchResults = $('#search-results-info');
        const searchText = $('#search-results-text');
        const searchLoadingIndicator = $('#search-loading-indicator');
        
        // Hide loading indicator
        searchLoadingIndicator.addClass('hidden');
        
        // Count active filters
        const activeFilters = Object.values(currentFilters).filter(value => value.length > 0);
        const hasActiveFilters = activeFilters.length > 0;
        const hasSearch = searchInfo && searchInfo.has_search;
        
        if (hasSearch || hasActiveFilters) {
            let resultsText = '';
            
            if (hasSearch && hasActiveFilters) {
                resultsText = `${searchInfo.results_count} productos encontrados para "${searchInfo.query}" con ${activeFilters.length} filtro(s) aplicado(s)`;
            } else if (hasSearch) {
                resultsText = searchInfo.results_count === 1 
                    ? `1 producto encontrado para "${searchInfo.query}"`
                    : `${searchInfo.results_count} productos encontrados para "${searchInfo.query}"`;
            } else if (hasActiveFilters) {
                resultsText = `Productos filtrados con ${activeFilters.length} filtro(s) aplicado(s)`;
            }
            
            searchText.html(`
                <i class="fas fa-search mr-1"></i>
                ${resultsText}
            `);
            searchResults.removeClass('hidden');
        } else {
            searchResults.addClass('hidden');
        }
    }

    function showNoSearchResults(query) {
        const tbody = $('#products-table-body');
        const activeFilters = Object.values(currentFilters).filter(value => value.length > 0);
        const hasActiveFilters = activeFilters.length > 0;
        
        let message = '';
        let actionButton = '';
        
        if (query && hasActiveFilters) {
            message = `No hay productos que coincidan con la búsqueda: <strong>"${query}"</strong> y los ${activeFilters.length} filtro(s) aplicado(s)`;
            actionButton = `
                <button onclick="$('#clear-search').click()" 
                        class="mr-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar búsqueda y filtros
                </button>
                <button onclick="$('#clear-all-filters').click()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    <i class="fas fa-eraser mr-2"></i>
                    Solo limpiar filtros
                </button>
            `;
        } else if (query) {
            message = `No hay productos que coincidan con la búsqueda: <strong>"${query}"</strong>`;
            actionButton = `
                <button onclick="$('#clear-search').click()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Limpiar búsqueda
                </button>
            `;
        } else if (hasActiveFilters) {
            message = `No hay productos que coincidan con los ${activeFilters.length} filtro(s) aplicado(s)`;
            actionButton = `
                <button onclick="$('#clear-all-filters').click()" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    <i class="fas fa-eraser mr-2"></i>
                    Limpiar filtros
                </button>
            `;
        }
        
        tbody.html(`
            <tr>
                <td colspan="9" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No se encontraron productos</h3>
                        <p class="text-gray-600 mb-4">${message}</p>
                        <div class="space-x-2">
                            ${actionButton}
                        </div>
                    </div>
                </td>
            </tr>
        `);
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
        // Función helper para truncar texto
        const truncateText = (text, maxLength) => {
            if (!text) return '-';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        };
        const descripcionCompleta = product['descripcionPresentacion'] || '-';
        const descripcionProducto = product['descripcionProducto'] || '-';
        const marcaGenerico = product['marcaGenerico'] || '-';
        const eticoPopular = product['eticoPopular'] || '-';
        const fuente = product['fuente'] || '-';
        const molecula = product['molecula'] || '-';
        const descripcionFF3 = product['descripcionFF3'] || '-';
        const descripcionATC4 = product['descripcionATC4'] || '-';
        const laboratorio = truncateText(product['descripcionLaboratorio'], 20);
        const corporacion = truncateText(product['descripcionCorporacion'], 20);
        
        return `
            <tr class="hover:bg-gray-50 divide-x divide-gray-200">
                <!-- Descripción 16% -->
                <td class="w-[16%] px-1 py-1 text-xs text-gray-900 description-cell" title="${descripcionCompleta}">
                    <div class="leading-tight font-medium whitespace-normal break-words text-left">
                        ${descripcionCompleta}
                    </div>
                </td>
                <!-- Producto 12% -->
                <td class="w-[12%] px-1 py-1 text-xs text-gray-500 product-cell" title="${descripcionProducto}">
                    <div class="leading-tight whitespace-normal break-words text-left">
                        ${descripcionProducto}
                    </div>
                </td>
                <!-- M/G 6% -->
                <td class="w-[6%] px-1 py-1 text-center text-xs text-gray-500 hidden sm:table-cell border-l-2 border-gray-300">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-xs font-medium ${getBrandClass(marcaGenerico)}" title="${marcaGenerico}">
                        <span class="hidden lg:inline">${marcaGenerico}</span>
                        <span class="lg:hidden">${marcaGenerico.substring(0, 1)}</span>
                    </span>
                </td>
                <!-- É/P 6% -->
                <td class="w-[6%] px-1 py-1 text-center text-xs text-gray-500 hidden sm:table-cell border-r-2 border-gray-300">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-xs font-medium ${getEthicClass(eticoPopular)}" title="${eticoPopular}">
                        <span class="hidden lg:inline">${eticoPopular}</span>
                        <span class="lg:hidden">${eticoPopular.substring(0, 1)}</span>
                    </span>
                </td>
                <!-- Fuente 6% -->
                <td class="w-[6%] px-1 py-1 text-center text-xs text-gray-500">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-xs font-medium ${getFuenteClass(fuente)}" title="${fuente}">
                        <i class="fas fa-database text-xs mr-0.5"></i>
                        <span class="text-xs">${fuente.substring(0, 3)}</span>
                    </span>
                </td>
                <!-- Molécula 12% -->
                <td class="w-[12%] px-1 py-1 text-xs text-gray-500 hidden lg:table-cell molecule-cell" title="${molecula}">
                    <div class="text-xs leading-tight">
                        ${molecula}
                    </div>
                </td>
                <!-- FF3 8% -->
                <td class="w-[8%] px-1 py-1 text-xs text-gray-500 hidden md:table-cell single-line-cell" title="${descripcionFF3}">
                    <span class="truncate">${descripcionFF3}</span>
                </td>
                <!-- ATC4 8% -->
                <td class="w-[8%] px-1 py-1 text-xs text-gray-500 hidden md:table-cell single-line-cell" title="${descripcionATC4}">
                    <span class="truncate">${descripcionATC4}</span>
                </td>
                <!-- Laboratorio 8% -->
                <td class="w-[8%] px-1 py-1 text-xs text-gray-500 hidden lg:table-cell single-line-cell" title="${product['descripcionLaboratorio'] || '-'}">
                    <span class="truncate">${laboratorio}</span>
                </td>
                <!-- Corporación 8% -->
                <td class="w-[8%] px-1 py-1 text-xs text-gray-500 hidden xl:table-cell single-line-cell" title="${product['descripcionCorporacion'] || '-'}">
                    <span class="truncate">${corporacion}</span>
                </td>
                <!-- Acciones 2% -->
                <td class="w-[2%] px-0.5 py-1 text-center">
                    <div class="flex items-center justify-center space-x-1">
                        <button onclick="openRemoveProductModal('${product['codigoPresentacion'] || product['Código_Presentación']}', '${escapeForJs(product['descripcionPresentacion'] || product['Descripción_Presentación'])}')" 
                                class="text-red-600 hover:text-red-900 transition-colors p-1.5 rounded hover:bg-red-50" 
                                title="Quitar producto">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                        <button onclick="openChangeMarketModal('${product['codigoPresentacion'] || product['Código_Presentación']}', '${escapeForJs(product['descripcionPresentacion'] || product['Descripción_Presentación'])}')" 
                                class="text-blue-600 hover:text-blue-900 transition-colors p-1.5 rounded hover:bg-blue-50" 
                                title="Cambiar mercado">
                            <i class="fas fa-exchange-alt text-sm"></i>
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

    function getFuenteClass(fuente) {
        switch(fuente) {
            case 'IQVIA': return 'bg-teal-100 text-teal-800';
            case 'CLOSE UP': return 'bg-cyan-100 text-cyan-800';
            case 'SISMED': return 'bg-emerald-100 text-emerald-800';
            case 'NIELSEN': return 'bg-sky-100 text-sky-800';
            case null:
            case undefined:
            case '': return 'bg-gray-100 text-gray-600';
            default: return 'bg-red-100 text-red-800';
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

        // OPTIMIZACIÓN: Solo mostrar controles si hay paginación
        if (!pagination.has_more_pages && !pagination.has_previous_pages) {
            // Mercado pequeño sin paginación - ocultar controles
            controls.html('<span class="text-xs text-gray-500">No se requiere paginación</span>');
            return;
        }

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
            if (elapsed > 15) {
                $('#loading-text').text('Consultando base de datos...');
                $('#loading-subtext').text('Cargando productos del mercado');
            } else if (elapsed > 30) {
                $('#loading-text').text('Procesando datos...');
                $('#loading-subtext').text('Este mercado puede tener muchos productos');
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
        loadProducts(cursor, true, currentSearchQuery); // append = true, agrega datos
    };

    window.loadNextPage = function(cursor) {
        console.log('Loading next page with cursor:', cursor);
        currentCursor = cursor;
        loadProducts(cursor, false, currentSearchQuery); // append = false, reemplaza datos
    };

    window.loadPreviousPage = function(cursor) {
        console.log('Loading previous page with cursor:', cursor);
        currentCursor = cursor;
        loadProducts(cursor, false, currentSearchQuery); // append = false, reemplaza datos
    };

    window.loadFirstPage = function() {
        console.log('Loading first page');
        currentCursor = null;
        loadProducts(null, false, currentSearchQuery); // Primera página, reemplaza datos
    };

    // Variables globales para las modales
    window.currentProductCode = null;
    window.currentProductName = null;
    window.availableMarkets = [];

    // Función global para quitar producto del mercado usando SP_ASIGNAR_RESTO
    window.removeProductFromMarket = function(){
        if (!window.currentProductCode) {
            showErrorNotification('Error: No se ha seleccionado un producto válido');
            return;
        }

        // Abrir modal de confirmación final
        openFinalConfirmationModal();
    };

    // Funciones para Modal de Quitar Producto
    window.openRemoveProductModal = function(productCode, productName) {
        window.currentProductCode = productCode;
        window.currentProductName = productName;
        
        document.getElementById('remove-product-name').textContent = productName;
        document.getElementById('remove-product-code').textContent = productCode;
        document.getElementById('remove-product-modal').classList.remove('hidden');
    };

    window.closeRemoveProductModal = function() {
        document.getElementById('remove-product-modal').classList.add('hidden');
        window.currentProductCode = null;
        window.currentProductName = null;
    };

    // Funciones para Modal de Cambiar Mercado
    window.openChangeMarketModal = function(productCode, productName) {
        window.currentProductCode = productCode;
        window.currentProductName = productName;
        
        document.getElementById('change-product-name').textContent = productName;
        document.getElementById('change-product-code').textContent = productCode;
        document.getElementById('current-market-name').textContent = window.marketData ? window.marketData.mercado : 'Mercado actual';
        
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
        window.currentProductCode = null;
        window.currentProductName = null;
        
        // Limpiar todo el estado del modal
        clearSelectedMarket();
        document.getElementById('market-search-input').value = '';
        document.getElementById('markets-dropdown').classList.add('hidden');
        window.availableMarkets = [];
    };

    // Función global para cambiar mercado del producto
    window.changeProductMarket = function() {
        if (!window.currentProductCode) {
            showErrorNotification('Error: No se ha seleccionado un producto válido');
            return;
        }

        const selectedMarketId = document.getElementById('selected-market-id').value;
        if (!selectedMarketId) {
            showErrorNotification('Error: Debe seleccionar un mercado de destino');
            return;
        }

        // Abrir modal de confirmación final
        openFinalChangeConfirmationModal();
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
            if (searchTerm.length > 0 || window.availableMarkets.length > 0) {
                dropdown.classList.remove('hidden');
            }
            
            // Debounce search
            marketSearchTimeout = setTimeout(() => {
                filterMarkets(searchTerm);
            }, 300);
        });
        
        // Event listener para focus - mostrar dropdown
        searchInput.addEventListener('focus', function() {
            if (window.availableMarkets.length > 0) {
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
        const currentMarketId = window.marketId;
        
        // Filtrar mercados
        const filteredMarkets = window.availableMarkets.filter(market => {
            return market.idMercado !== currentMarketId && 
                   market.estado === 'ACTIVO' &&
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
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-store text-red-600 text-xs"></i>
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
        if (window.availableMarkets.length > 0) {
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
        
        // Construir URL con parámetro de franquicia si existe
        let url = '/market-management/markets/api';
        const params = new URLSearchParams();
        
        // Agregar filtro de franquicia si existe
        if (franquiciaFilter && franquiciaFilter.length > 0) {
            params.append('franquicia', franquiciaFilter);
        }
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        // Hacer petición AJAX para obtener mercados
        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    window.availableMarkets = response.data;
                    loadingState.classList.add('hidden');
                    filterMarkets(''); // Mostrar todos los mercados inicialmente
                    console.log(`Cargados ${window.availableMarkets.length} mercados disponibles para franquicia: ${franquiciaFilter || 'todas'}`);
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

// Funciones para Modal de Confirmación Final
window.openFinalConfirmationModal = function() {
    document.getElementById('final-confirmation-modal').classList.remove('hidden');
};

window.closeFinalConfirmationModal = function() {
    document.getElementById('final-confirmation-modal').classList.add('hidden');
};

// Función que realmente procede con la eliminación
window.proceedWithRemoval = function() {
    if (!window.currentProductCode) {
        showErrorNotification('Error: No se ha seleccionado un producto válido');
        return;
    }

    // Mostrar loading en el botón de confirmación final
    const btn = $('#final-confirm-btn');
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
            codigoPresentacion: window.currentProductCode
        },
        success: function(response) {
            if (response.success) {
                // Mostrar notificación de éxito
                showSuccessNotification('Producto removido del mercado exitosamente');
                
                // Cerrar ambas modales
                closeFinalConfirmationModal();
                closeRemoveProductModal();
                
                // Si la respuesta indica refresh, recargar la página
                if (response.refresh_page) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500); // Esperar 1.5 segundos para que se vea la notificación
                } else {
                    // Recargar la tabla de productos para reflejar los cambios
                    loadProducts();
                }
            } else {
                showErrorNotification('Error: ' + response.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error en proceedWithRemoval:', {xhr, textStatus, errorThrown});
            let errorMessage = 'Error al remover el producto del mercado';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 422) {
                errorMessage = 'Datos de entrada inválidos';
            } else if (xhr.status === 500) {
                errorMessage = 'Error interno del servidor';
            } else if (xhr.status === 404) {
                errorMessage = 'Producto no encontrado';
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

// Funciones para Modal de Confirmación Final de Cambio de Mercado
window.openFinalChangeConfirmationModal = function() {
    // Obtener información para mostrar en la confirmación
    const productName = window.currentProductName || 'Producto seleccionado';
    const selectedMarketName = document.getElementById('selected-market-name').textContent || 'Mercado seleccionado';
    
    // Actualizar textos en la modal de confirmación
    document.getElementById('final-change-product-name').textContent = productName;
    document.getElementById('final-change-market-name').textContent = selectedMarketName;
    
    // Mostrar modal
    document.getElementById('final-change-confirmation-modal').classList.remove('hidden');
};

window.closeFinalChangeConfirmationModal = function() {
    document.getElementById('final-change-confirmation-modal').classList.add('hidden');
};

// Función que realmente procede con el cambio de mercado
window.proceedWithMarketChange = function() {
    if (!window.currentProductCode) {
        showErrorNotification('Error: No se ha seleccionado un producto válido');
        return;
    }

    const selectedMarketId = document.getElementById('selected-market-id').value;
    if (!selectedMarketId) {
        showErrorNotification('Error: Debe seleccionar un mercado de destino');
        return;
    }

    // Mostrar loading en el botón de confirmación final
    const btn = $('#final-change-confirm-btn');
    const btnText = btn.find('.btn-text');
    const btnLoading = btn.find('.btn-loading');
    
    btnText.addClass('hidden');
    btnLoading.removeClass('hidden');
    btn.prop('disabled', true);

    $.ajax({
        url: '/market-management/products/change-market',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            codigoPresentacion: window.currentProductCode,
            nuevoMercadoId: parseInt(selectedMarketId)
        },
        success: function(response) {
            if (response.success) {
                // Mostrar notificación de éxito
                showSuccessNotification(response.message);
                
                // Cerrar ambas modales
                closeFinalChangeConfirmationModal();
                closeChangeMarketModal();
                
                // Si la respuesta indica refresh, recargar la página
                if (response.refresh_page) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500); // Esperar 1.5 segundos para que se vea la notificación
                } else {
                    // Recargar la tabla de productos para reflejar los cambios
                    loadProducts();
                }
            } else {
                showErrorNotification('Error: ' + response.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error en proceedWithMarketChange:', {xhr, textStatus, errorThrown});
            let errorMessage = 'Error al cambiar el mercado del producto';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 422) {
                errorMessage = 'Datos de entrada inválidos o mercado no válido';
            } else if (xhr.status === 404) {
                errorMessage = 'No se encontró la configuración del producto';
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

// Searchable Filter Functions
function initializeSearchableFilters() {
    const searchableFilters = [
        'descripcionProducto',
        'descripcionLaboratorio', 
        'descripcionCorporacion',
        'molecula',
        'descripcionFF3',
        'descripcionATC4'
    ];

    searchableFilters.forEach(filterType => {
        const searchInput = $(`#filter-${filterType}-search`);
        const dropdown = $(`#filter-${filterType}-dropdown`);
        const optionsContainer = $(`#filter-${filterType}-options`);
        const noResultsElement = $(`#filter-${filterType}-no-results`);
        const loadingElement = $(`#filter-${filterType}-loading`);
        const hiddenInput = $(`#filter-${filterType}`);
        const clearButton = $(`#clear-filter-${filterType}`);
        
        let searchTimeout = null;

        // Search functionality
        searchInput.on('input', function() {
            const query = $(this).val().trim();
            
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    loadFilterOptions(filterType, query, optionsContainer, dropdown, noResultsElement, loadingElement);
                }, 300);
            } else {
                dropdown.addClass('hidden');
            }
        });

        // Handle option selection
        $(document).on('click', `#filter-${filterType}-options .filter-option`, function() {
            const value = $(this).data('value');
            const text = $(this).text();
            
            searchInput.val(text);
            hiddenInput.val(value);
            dropdown.addClass('hidden');
            clearButton.removeClass('hidden');
            
            // Trigger filter application
            currentFilters[filterType] = value;
            applyFilters();
        });

        // Clear filter
        clearButton.on('click', function() {
            searchInput.val('');
            hiddenInput.val('');
            clearButton.addClass('hidden');
            dropdown.addClass('hidden');
            currentFilters[filterType] = '';
            applyFilters();
        });

        // Hide dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest(`.filter-searchable`).length) {
                dropdown.addClass('hidden');
            }
        });

        // Show dropdown on focus if there's content
        searchInput.on('focus', function() {
            if (optionsContainer.children().length > 0) {
                dropdown.removeClass('hidden');
            }
        });
    });
}

// Load filter options via AJAX
function loadFilterOptions(filterType, query, optionsContainer, dropdown, noResultsElement, loadingElement) {
    // Show loading
    loadingElement.removeClass('hidden');
    dropdown.removeClass('hidden');
    optionsContainer.empty();
    noResultsElement.addClass('hidden');

    $.ajax({
        url: `/productos/filter-options`, // Cambiado a la ruta de productos generales
        method: 'GET',
        data: {
            filter_type: filterType,
            search: query,
            limit: 20
        },
        success: function(response) {
            loadingElement.addClass('hidden');
            
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(option) {
                    const optionElement = $(`
                        <div class="filter-option px-3 py-2 text-xs hover:bg-gray-100 cursor-pointer" data-value="${option}">
                            ${option}
                        </div>
                    `);
                    optionsContainer.append(optionElement);
                });
                noResultsElement.addClass('hidden');
            } else {
                noResultsElement.removeClass('hidden');
            }
        },
        error: function(xhr) {
            loadingElement.addClass('hidden');
            console.error('Error loading filter options:', xhr);
            noResultsElement.text('Error al cargar opciones').removeClass('hidden');
        }
    });
}

// Load filter options for normal select filters
function loadNormalFilterOptions() {
    const normalFilters = ['marcaGenerico', 'eticoPopular', 'fuente', 'mercado'];
    
    console.log('Loading normal filter options for:', normalFilters);
    
    normalFilters.forEach(filterType => {
        console.log(`Loading options for filter: ${filterType}`);
        
        $.ajax({
            url: `/productos/filter-options`, // Cambiado a la ruta de productos generales
            method: 'GET',
            data: {
                filter_type: filterType,
                limit: 500 // Para los selects normales podemos cargar más opciones
            },
            success: function(response) {
                console.log(`Success loading ${filterType} options:`, response);
                const selectElement = $(`#filter-${filterType}`);
                
                if (selectElement.length === 0) {
                    console.error(`Select element #filter-${filterType} not found!`);
                    return;
                }
                
                // Clear existing options except the first one (placeholder)
                selectElement.find('option:not(:first)').remove();
                
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(option) {
                        selectElement.append(`<option value="${option}">${option}</option>`);
                    });
                    console.log(`Added ${response.data.length} options to ${filterType}`);
                } else {
                    console.warn(`No options found for ${filterType}`);
                }
            },
            error: function(xhr) {
                console.error(`Error loading ${filterType} options:`, xhr);
                console.error('Response text:', xhr.responseText);
                console.error('Status:', xhr.status);
            }
        });
    });
}
