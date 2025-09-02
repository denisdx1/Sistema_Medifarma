// Productos JavaScript Functions - Optimized Version
$(document).ready(function() {
    // Verificar que estamos en el módulo de productos
    if (!window.productosModule) {
        console.error('Este script solo funciona en el módulo de productos');
        return;
    }
    

    
    let currentPage = 1;
    let totalPages = 1;
    let totalCount = 0;
    let isLoading = false;
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
        descripcionCorporacion: '',
        concentracion: ''
    };
    let filterTimeout = null;

    // Sorting variables
    let currentSortField = null;
    let currentSortDirection = 'asc'; // 'asc' or 'desc'

    // Search functionality removed
    
    // Initialize filter functionality
    initializeSearchableFilters();
    
    // Initialize normal filters (select dropdowns)
    initializeNormalFilters();
    

    
    // Initialize sorting functionality
    initializeSorting();
    
    // Sync filter state on page load
    syncFilterButtonsState();
    
    // Check for auto-select market from sessionStorage and load products
    // Si hay un mercado, marca o filtros múltiples para autoseleccionar, no cargar productos inicialmente
    const autoSelectMarket = sessionStorage.getItem('autoSelectMarket');
    const autoSelectBrand = sessionStorage.getItem('autoSelectBrand');
    const autoFilterMarkets = sessionStorage.getItem('autoFilterMarkets');
    
    if (autoSelectMarket || autoSelectBrand || autoFilterMarkets) {
        // Mostrar loading inmediatamente con mensaje específico
        if (autoSelectMarket) {
            updateLoadingText(`Cargando productos del mercado "${autoSelectMarket}"...`);
        } else if (autoSelectBrand) {
            updateLoadingText(`Cargando productos de la marca "${autoSelectBrand}"...`);
        } else if (autoFilterMarkets) {
            const markets = JSON.parse(autoFilterMarkets);
            updateLoadingText(`Cargando productos de los mercados: ${markets.join(', ')}...`);
        }
        $('#loading-subtext').text('Aplicando filtros y cargando datos...');
        showLoadingState();
        
        // Aplicar el filtro automáticamente
        checkAutoSelectMarketAndLoadProducts();
    } else {
        // Solo cargar productos si no hay autoselección
        loadProducts(1);
    }

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
        
        // Clear sorting
        clearSorting();
        
        // Reset pagination
        currentPage = 1;
        
        // Reset loading text to default
        updateLoadingText('Cargando productos...');
        
        // Reload products
        loadProducts(1);
    });

    // Clear sorting functionality
    $('#clear-sorting').on('click', function() {
        // Prevent multiple clicks during loading
        if (isLoading) {
            return;
        }
        
        // Clear sorting
        clearSorting();
        
        // Reset pagination
        currentPage = 1;
        
        // Reload products
        loadProducts(1);
    });

    // Search functionality removed

    // Initialize searchable filters (for large datasets)
    function initializeSearchableFilters() {
        const searchableFilters = ['descripcionProducto', 'molecula', 'descripcionFF3', 'descripcionATC4', 'descripcionLaboratorio', 'descripcionCorporacion', 'mercado', 'concentracion'];
        
        searchableFilters.forEach(filterKey => {
            const input = $(`#filter-${filterKey}`);
            const resultsDiv = $(`#${filterKey}-results`);
            const clearButton = $(`#clear-filter-${filterKey}`);
            let searchTimeout = null;

            if (input.length === 0) return;

            // Input event for searching
            input.on('input', function() {
                const query = $(this).val().trim();
                
                // Para el filtro de mercado, usar el valor tal como está
                let backendValue = query;
                
                // Actualizar currentFilters inmediatamente
                currentFilters[filterKey] = backendValue;
                
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
                input.val('');
                clearButton.addClass('hidden');
                resultsDiv.removeClass('show');
                currentFilters[filterKey] = '';
                
                // Reset loading text if clearing descripcionProducto filter
                if (filterKey === 'descripcionProducto') {
                    updateLoadingText('Cargando productos...');
                }
                
                // Reset loading text if clearing mercado filter
                if (filterKey === 'mercado') {
                    updateLoadingText('Cargando productos...');
                }
                
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
                
                // Reset loading text if clearing mercado filter
                if (filterKey === 'mercado') {
                    updateLoadingText('Cargando productos...');
                }

     
               
                applyFilters();
            });
        });


    }

    // Load options for normal filters (select dropdowns)
    function loadNormalFilterOptions(filterType) {
        // Use higher limit for mercado
        const limit = filterType === 'mercado' ? 500 : 100;
        
        return new Promise((resolve, reject) => {
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
                        
                        resolve(response);
                    } else {
                        console.error(`Error en respuesta de ${filterType}:`, response);
                        reject(new Error(`Error en respuesta de ${filterType}`));
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`Error loading ${filterType} options:`, error);
                    reject(new Error(`Error loading ${filterType} options: ${error}`));
                }
            });
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
            
            // Para el filtro de mercado, usar el valor tal como está
            let backendValue = value;
            
            // Set the input value (mostrar el valor amigable al usuario)
            $(`#filter-${filter}`).val(value);
            // Guardar el valor para el backend (puede ser diferente al mostrado)
            currentFilters[filter] = backendValue;
            
            // Show/hide clear button
            const clearButton = $(`#clear-filter-${filter}`);
            if (value && value.length > 0) {
                clearButton.removeClass('hidden');
            } else {
                clearButton.addClass('hidden');
            }
            
            // Hide results
            resultsDiv.removeClass('show');
            
            // Apply filters
            applyFilters();
        });
    }

    // Filter toggle functionality removed - filters are now always visible

    function applyFilters() {
        // Reset pagination when filtering
        currentPage = 1;
        
        // Hide any existing dropdown results immediately (only product filters)
        $('.products-filters .filter-dropdown').removeClass('show');
        
        // Actualizar contador de filtros activos
        updateActiveFiltersCounter();
        
        // Reset loading text to default
        updateLoadingText('Cargando productos...');
        
        // Limpiar tabla antes de cargar nuevos productos
        const tableBody = $('#products-table-body');
        tableBody.html('');
        
        // Si ya hay una petición en curso, cancelar esta aplicación de filtros
        if (isLoading) {
            return;
        }
        
        // Load products with current filters
        loadProducts(1);
    }



    // Función para aplicar filtros con retry automático cuando hay peticiones en curso
    function applyFiltersWithRetry(retryCount = 0) {
        // Máximo 5 reintentos
        if (retryCount >= 5) {
            return;
        }
        
        // Si hay una petición en curso, esperar y reintentar
        if (isLoading) {
            setTimeout(() => {
                applyFiltersWithRetry(retryCount + 1);
            }, 1000);
            return;
        }
        
        // Si no hay petición en curso, aplicar filtros normalmente
        applyFilters();
    }

    // Search functionality removed

    function loadProducts(page = 1, append = false) {
        if (isLoading) return;
        
        isLoading = true;
        
        // Ocultar solo los dropdowns de filtros de productos al iniciar cualquier carga
        $('.products-filters .filter-dropdown').removeClass('show');
        
        // Solo mostrar loading overlay para carga completa (no para "cargar más") y si no está ya visible
        if (!append && $('#loading-overlay').hasClass('hidden')) {
            // Reset loading text to default for new loads
            updateLoadingText('Cargando productos...');
            showLoadingState();
        }

        const url = `/productos/api`;
        const params = new URLSearchParams({
            per_page: 50,
            page: page
        });

        // Add sorting parameters
        if (currentSortField) {
            params.append('sort_field', currentSortField);
            params.append('sort_direction', currentSortDirection);
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
                    } else {
                        updateProductsTable(response.data);
                    }
                    
                    // Update pagination info
                    updatePaginationInfo(response);
                    
                    // Update total products counter
                    updateProductsCounter(response.pagination.total_count);
                    
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
                        const fuente = product['fuente'] || 'Sin fuente';
        const molecula = product['molecula'] || '-';
        const descripcionFF3 = formatCodeDescription(product['codigoFF3'], product['descripcionFF3']);
        const descripcionATC4 = formatCodeDescription(product['codigoATC4'], product['descripcionATC4']);
        
        // Versiones para title (sin HTML)
        const titleFF3 = formatCodeDescriptionPlain(product['codigoFF3'], product['descripcionFF3']);
        const titleATC4 = formatCodeDescriptionPlain(product['codigoATC4'], product['descripcionATC4']);
        const mercado = product['mercado'] || '-';
        const mercadoDisplay = mercado;
        const laboratorio = product['descripcionLaboratorio'] || '-';
        const corporacion = product['descripcionCorporacion'] || '-';
        
        // Determinar si es Medifarma Corp para resaltar toda la fila
        const isMedifarmaCorp = corporacion && corporacion.toUpperCase().includes('MEDIFARMA CORP');
        const rowClass = isMedifarmaCorp ? 'hover:bg-medifarma-light divide-x divide-gray-200 bg-medifarma-bg' : 'hover:bg-gray-50 divide-x divide-gray-200';
        
        return `
            <tr class="${rowClass}" data-product-code="${product['codigoPresentacion']}">
                <!-- Checkbox 3% -->
                <td class="w-[3%] px-2 py-1 text-center">
                    <input type="checkbox" class="product-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                           data-product-code="${product['codigoPresentacion']}"
                           data-product-name="${escapeForJs(product['descripcionPresentacion'])}"
                           data-fuente="${product['fuente'] || 'Sin fuente'}"
                           data-market="${mercado || 'Sin mercado'}"
                           onchange="updateSelectedProducts()">
                </td>
                <!-- Descripción 15% -->
                <td class="w-[15%] px-2 py-1 text-[10px] text-gray-900 description-cell">
                    <div class="leading-tight font-medium whitespace-normal break-words text-left">
                        ${descripcionCompleta}
                    </div>
                </td>
                <!-- Producto 14% -->
                <td class="w-[7%] px-2 py-1 text-[10px] text-gray-500 product-cell">
                    <div class="leading-tight whitespace-normal break-words text-left">
                        ${descripcionProducto}
                    </div>
                </td>
                <!-- Marca/Genérico 9% -->
                <td class="w-[7%] px-2 py-1 text-center text-[10px] text-gray-500 border-l-2 border-gray-300">
                    <span class="text-[10px] font-medium ${getBrandClass(marcaGenerico)}">
                        ${marcaGenerico}
                    </span>
                </td>
                <!-- Ético/Popular 9% -->
                <td class="w-[7%] px-2 py-1 text-center text-[10px] text-gray-500 border-r-2 border-gray-300">
                    <span class="text-[10px] font-medium ${getEthicClass(eticoPopular)}">
                        ${eticoPopular}
                    </span>
                </td>
                <!-- Molécula 14% -->
                <td class="w-[14%] px-2 py-1 text-[10px] text-gray-500 molecule-cell">
                    <div class="text-[10px] leading-tight whitespace-normal break-words">
                        ${molecula}
                    </div>
                </td>
                <!-- FF3 9% -->
                <td class="w-[12%] px-2 py-1 text-[10px] text-gray-500">
                    <div class="whitespace-normal break-words">
                        ${descripcionFF3}
                    </div>
                </td>
                <!-- ATC4 9% -->
                <td class="w-[12%] px-2 py-1 text-[10px] text-gray-500">
                    <div class="whitespace-normal break-words">
                        ${descripcionATC4}
                    </div>
                </td>
                
                <!-- Corporación 9% -->
                <td class="w-[9%] px-2 py-1 text-[10px] ${getCorporacionClass(product['descripcionCorporacion'])}">
                    <div class="whitespace-normal break-words">
                        ${product['descripcionCorporacion'] || '-'}
                    </div>
                </td>
                <!-- Laboratorio 11% -->
                <td class="w-[9%] px-2 py-1 text-[10px] ${getLaboratorioClass(laboratorio)}">
                    <div class="whitespace-normal break-words">
                        ${laboratorio}
                    </div>
                </td>
                <!-- Concentración 8% -->
                <td class="w-[5%] px-2 py-1 text-center text-[10px] text-gray-600">
                    <span class="text-[10px] font-medium">
                        -
                    </span>
                </td>
                <!-- Mercado 6% -->
                <td class="w-[6%] px-2 py-1 text-center text-[9px] text-gray-600 market-cell">
                    <div class="flex flex-col items-center justify-center">
                        <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-[9px] font-medium ${getMarketClass(mercado)} w-full">
                            <span class="text-center">
                        ${mercadoDisplay}
                    </span>
                        </span>
                    </div>
                </td>
                <!-- Size Pack 4% -->
                <td class="w-[4%] px-2 py-1 text-center text-[10px] text-gray-600">
                    <span class="text-[10px] font-medium">
                        ${product['sizePack'] || '-'}
                    </span>
                </td>
                <!-- Fuente 5% -->
                <td class="w-[5%] px-2 py-1 text-center text-[10px] text-gray-500">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[10px] font-medium ${getFuenteClass(fuente)}">
                        ${fuente}
                    </span>
                </td>
                <!-- Acciones 4% -->
                <td class="w-[4%] px-0 py-1 text-center actions-cell">
                    <div class="flex flex-col items-center justify-center space-y-0.5">
                        <button onclick="window.openRemoveProductModal('${product['codigoPresentacion']}', '${escapeForJs(product['descripcionPresentacion'])}')" 
                                class="action-button text-red-600 hover:text-red-900 transition-colors rounded hover:bg-red-50">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button onclick="window.openChangeMarketModal('${product['codigoPresentacion']}', '${escapeForJs(product['descripcionPresentacion'])}')" 
                                class="action-button text-blue-600 hover:text-blue-900 transition-colors rounded hover:bg-blue-50">
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
        return 'text-gray-700';
    }

    function getEthicClass(eticoPopular) {
        return 'text-gray-700';
    }

    function getFuenteClass(fuente) {
        // Si la fuente es null, undefined o vacía, mostrar como "Sin fuente"
        if (!fuente || fuente === null || fuente === undefined || fuente === '') {
            return 'bg-gray-100 text-gray-500';
        }
        
        switch(fuente.toUpperCase()) {
            case 'IQV':
                return 'bg-blue-100 text-blue-800';
            case 'IQVIA':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function getMarketClass(mercado) {
        if (mercado === 'SIN_ASIGNAR') {
            return 'bg-red-100 text-red-800';
        } else if (mercado && mercado !== '-') {
            return 'bg-green-100 text-green-800';
        } else {
            return 'bg-gray-100 text-gray-800';
        }
    }

    function getLaboratorioClass(laboratorio) {
        // Esta función ya no se usa para resaltar Medifarma, pero la mantenemos por compatibilidad
        return 'text-gray-500';
    }

    function getCorporacionClass(corporacion) {
        if (corporacion && corporacion.toUpperCase().includes('MEDIFARMA CORP')) {
            return 'medifarma-corp text-medifarma font-semibold';
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
        
        // Actualizar variables globales
        currentPage = response.pagination.current_page;
        totalPages = response.pagination.total_pages;
        totalCount = response.pagination.total_count;
        
        // Mostrar información de paginación
        paginationInfo.text(`Página ${currentPage} de ${totalPages} - Mostrando ${response.pagination.from} a ${response.pagination.to} de ${totalCount} productos`);
        
        // Generar controles de paginación
        let paginationHTML = '<div class="flex items-center justify-center space-x-2">';
        
        // Botón anterior
        if (response.pagination.has_previous_page) {
            paginationHTML += `
                <button onclick="goToPage(${currentPage - 1})" 
                        class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    <i class="fas fa-chevron-left mr-1"></i>
                    Anterior
                </button>
            `;
        }
        
        // Números de página
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `
                    <span class="px-3 py-1 text-sm bg-primary text-white rounded-md font-medium">
                        ${i}
                    </span>
                `;
            } else {
                paginationHTML += `
                    <button onclick="goToPage(${i})" 
                            class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                        ${i}
                    </button>
                `;
            }
        }
        
        // Botón siguiente
        if (response.pagination.has_next_page) {
            paginationHTML += `
                <button onclick="goToPage(${currentPage + 1})" 
                        class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Siguiente
                    <i class="fas fa-chevron-right ml-1"></i>
                </button>
            `;
        }
        
        paginationHTML += '</div>';
        paginationControls.html(paginationHTML);
    }

    function updateProductsCounter(count) {
        $('#total-products').text(count.toLocaleString());
    }

    // Search results functionality removed

    function showLoadingState() {
        // Ocultar solo los dropdowns de filtros de productos
        $('.products-filters .filter-dropdown').removeClass('show');
        
        // Mostrar overlay de carga solo en la tabla
        $('#loading-overlay').removeClass('hidden');
        
        // Reset loading text to default if not already set
        if (!$('#loading-text').text() || $('#loading-text').text().includes('Cargando productos del mercado')) {
            updateLoadingText('Cargando productos...');
        }
        
        // Start loading timer
        loadingStartTime = Date.now();
        loadingTimer = setInterval(() => {
            const elapsed = Math.floor((Date.now() - loadingStartTime) / 1000);
            $('#loading-timer').text(`${elapsed}s`);
        }, 1000);
    }
    
    function updateLoadingText(text) {
        $('#loading-text').text(text);
        // Solo cambiar el subtexto si no es un mensaje específico de filtro
        if (!text.includes('mercado') && !text.includes('marca')) {
            $('#loading-subtext').text('Por favor espere');
        }
    }

    function hideLoadingState() {
        $('#loading-overlay').addClass('hidden');
        
        if (loadingTimer) {
            clearInterval(loadingTimer);
            loadingTimer = null;
        }
        
        // Asegurar que el contenido sea visible
        $('#products-table-body').removeClass('hidden');
        $('.products-table').removeClass('hidden');
    }

    function showError(message) {
        // Show error in the table
        $('#products-table-body').html(`
            <tr>
                <td colspan="15" class="px-6 py-8 text-center text-red-600">
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
        // Hide dropdowns when clicking outside (only product filters, not modals)
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filter-container').length && 
                !$(e.target).closest('#bulk-assign-modal').length &&
                !$(e.target).closest('#change-market-modal').length &&
                !$(e.target).closest('#remove-product-modal').length) {
                $('.products-filters .filter-dropdown').addClass('hidden');
            }
        });
        
        // Hide dropdowns on scroll for better UX (only product filters)
        $(window).on('scroll', function() {
            $('.products-filters .filter-dropdown').addClass('hidden');
        });
        
        // Hide dropdowns when scrolling inside any container (only product filters)
        $('*').on('scroll', function() {
            $('.products-filters .filter-dropdown').addClass('hidden');
        });
    }



    // Funciones del sidebar eliminadas - usando diseño original

    // Global function for pagination
    window.goToPage = function(page) {
        if (page >= 1 && page <= totalPages && !isLoading) {
            loadProducts(page);
        }
    };

    // Variables globales para las modales
    window.currentProductCode = null;
    window.currentProductName = null;
    window.availableMarkets = [];

    // Funciones para Modal de Quitar Producto
    window.openRemoveProductModal = function(productCode, productName) {
        const modal = document.getElementById('remove-product-modal');
        
        if (!modal) {
            return;
        }
        
        window.currentProductCode = productCode;
        window.currentProductName = productName;
        
        const nameElement = document.getElementById('remove-product-name');
        const codeElement = document.getElementById('remove-product-code');
        
        if (nameElement) nameElement.textContent = productName;
        if (codeElement) codeElement.textContent = productCode;
        
        // Limpiar el campo de nota
        const noteElement = document.getElementById('remove-product-note');
        if (noteElement) noteElement.value = '';
        
        // Validación inicial - deshabilitar botón hasta que se ingrese una nota
        const confirmBtn = document.getElementById('confirm-remove-btn');
        if (confirmBtn) {
            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            confirmBtn.disabled = true;
        }
        
        modal.classList.remove('hidden');
    };

    window.closeRemoveProductModal = function() {
        document.getElementById('remove-product-modal').classList.add('hidden');
        window.currentProductCode = null;
        window.currentProductName = null;
    };

    // Función global para quitar producto del mercado
    // Implementada en productos-actions.js

    // Funciones para Modal de Cambiar Mercado
    // Implementadas en productos-actions.js

    // Funciones de modales - implementadas en productos-actions.js



    // Función para sincronizar el estado de los botones de limpiar
    function syncFilterButtonsState() {
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
                } else {
                    clearButton.addClass('hidden');
                }
            }
        });
        
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
        } else {
            indicator.addClass('hidden');
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
        
        // Actualizar botones de acción masiva
        if (typeof updateBulkActionButtons === 'function') {
            updateBulkActionButtons();
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
        
        // Ocultar todos los botones de acción masiva
        if (typeof updateBulkActionButtons === 'function') {
            updateBulkActionButtons();
        }
    };

    // ===== FUNCIONES PARA MANEJO DE MERCADOS =====
    // Implementadas en productos-actions.js

    // Función para verificar si hay un mercado o marca para seleccionar automáticamente y cargar productos
function checkAutoSelectMarketAndLoadProducts() {
    const autoSelectMarket = sessionStorage.getItem('autoSelectMarket');
    const autoSelectBrand = sessionStorage.getItem('autoSelectBrand');
    const autoFilterMarkets = sessionStorage.getItem('autoFilterMarkets');
    
    if (autoSelectMarket) {
        // Limpiar el sessionStorage inmediatamente para evitar que se aplique múltiples veces
        sessionStorage.removeItem('autoSelectMarket');
        
        // Función para verificar y aplicar el filtro de mercado
        function tryApplyMarketFilter(attempts = 0) {
            const mercadoInput = $('#filter-mercado');
            
            // Para el filtro de mercado, usar el valor tal como está
            let displayValue = autoSelectMarket;
            
            // Establecer el valor en el input de mercado
            mercadoInput.val(displayValue);
            
            // Actualizar el filtro interno con el valor real para el backend
            currentFilters.mercado = autoSelectMarket;
            
            // Mostrar botón de limpiar
            $('#clear-filter-mercado').removeClass('hidden');
            
            // Resaltar visualmente el campo de mercado para que el usuario vea que está filtrado
            mercadoInput.addClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
            
            // Quitar el resaltado después de 3 segundos
            setTimeout(() => {
                mercadoInput.removeClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
            }, 3000);
            
            // Cargar productos directamente con el filtro aplicado
            loadProducts(1);
        }
        
        // Aplicar el filtro inmediatamente
        tryApplyMarketFilter();
    } else if (autoFilterMarkets) {
        // Limpiar el sessionStorage inmediatamente para evitar que se aplique múltiples veces
        sessionStorage.removeItem('autoFilterMarkets');
        
        // Función para verificar y aplicar el filtro múltiple de mercados
        function tryApplyMultipleMarketsFilter(attempts = 0) {
            const mercadoInput = $('#filter-mercado');
            
            // Parsear los mercados del JSON
            const markets = JSON.parse(autoFilterMarkets);
            
            // Para el filtro múltiple, mostrar los mercados separados por comas
            let displayValue = markets.join(', ');
            
            // Establecer el valor en el input de mercado
            mercadoInput.val(displayValue);
            
            // Actualizar el filtro interno con el valor real para el backend (separado por comas)
            currentFilters.mercado = markets.join(',');
            
            // Mostrar botón de limpiar
            $('#clear-filter-mercado').removeClass('hidden');
            
            // Resaltar visualmente el campo de mercado para que el usuario vea que está filtrado
            mercadoInput.addClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
            
            // Quitar el resaltado después de 3 segundos
            setTimeout(() => {
                mercadoInput.removeClass('border-blue-500 ring-2 ring-blue-200 bg-blue-50');
            }, 3000);
            
            // Cargar productos directamente con el filtro aplicado
            loadProducts(1);
        }
        
        // Aplicar el filtro múltiple inmediatamente
        tryApplyMultipleMarketsFilter();
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
            
                            // Cargar productos directamente con el filtro aplicado (evitando doble carga)
                loadProducts(1);
        }
        
        // Aplicar el filtro de marca inmediatamente
        tryApplyBrandFilter();
    } else {
        // Si no hay autoselección, cargar productos normalmente
        loadProducts(1);
    }
}

    // Initialize sorting functionality
    function initializeSorting() {
        // Add click event listeners to sortable headers
        $('.sortable-header').on('click', function() {
            const sortField = $(this).data('sort');
            handleSortClick(sortField);
        });
    }

    // Handle sort header click
    function handleSortClick(sortField) {
        // Prevent sorting if loading
        if (isLoading) {
            return;
        }

        // Toggle sort direction if same field, otherwise set to asc
        if (currentSortField === sortField) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortField = sortField;
            currentSortDirection = 'asc';
        }

        // Update sort icons
        updateSortIcons(sortField, currentSortDirection);

        // Update sorting info
        updateSortingInfo();

        // Reset pagination
        currentPage = 1;

        // Reload products with new sorting
        loadProducts(1);
    }

    // Update sort icons
    function updateSortIcons(activeField, direction) {
        // Reset all sort icons and remove active class
        $('.sortable-header').removeClass('active');
        $('.sortable-header .sort-icon').removeClass('fa-sort-up fa-sort-down text-primary').addClass('fa-sort text-gray-400');
        
        // Update active sort icon and header
        if (activeField) {
            const activeHeader = $(`.sortable-header[data-sort="${activeField}"]`);
            const activeIcon = activeHeader.find('.sort-icon');
            
            activeHeader.addClass('active');
            
            if (direction === 'asc') {
                activeIcon.removeClass('fa-sort text-gray-400').addClass('fa-sort-up text-primary');
            } else {
                activeIcon.removeClass('fa-sort text-gray-400').addClass('fa-sort-down text-primary');
            }
        }
    }

    // Clear sorting
    function clearSorting() {
        currentSortField = null;
        currentSortDirection = 'asc';
        updateSortIcons(null, 'asc');
        updateSortingInfo();
    }

    // Update sorting info display
    function updateSortingInfo() {
        const sortingInfo = $('#sorting-info');
        const sortingText = $('#sorting-text');
        
        if (currentSortField) {
            // Mapear nombres de campos a nombres legibles
            const fieldNames = {
                'descripcionPresentacion': 'Presentación',
                'descripcionProducto': 'Marca',
                'marcaGenerico': 'Marca/Genérico',
                'eticoPopular': 'Ético/Popular',
                'molecula': 'Molécula',
                'descripcionFF3': 'FF3',
                'descripcionATC4': 'ATC4',
                'descripcionLaboratorio': 'Laboratorio',
                'descripcionCorporacion': 'Corporación',
                'concentracion': 'Concentración',
                'mercado': 'Mercado',
                'fuente': 'Fuente'
            };
            
            const fieldName = fieldNames[currentSortField] || currentSortField;
            const direction = currentSortDirection === 'asc' ? 'A-Z' : 'Z-A';
            
            sortingText.text(`Ordenado por: ${fieldName} (${direction})`);
            sortingInfo.removeClass('hidden');
        } else {
            sortingInfo.addClass('hidden');
        }
    }
    
    // Función para formatear la concentración
    function formatConcentracion(stghVal, stghMea) {
        let result = '';
        
        if (stghVal && stghVal !== null && stghVal !== '') {
            result += parseFloat(stghVal).toFixed(2);
        }
        
        if (stghMea && stghMea !== null && stghMea !== '') {
            if (result) result += ' ';
            result += stghMea;
        }
        
        return result || '-';
    }
});
