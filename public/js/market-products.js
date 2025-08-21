// Market Products JavaScript Functions
$(document).ready(function() {
    // Validación robusta del marketId
    const marketId = window.marketId;
    
    // Log de debugging
    console.log('Market object received:', window.marketData);
    console.log('Market ID extracted:', marketId);
    
    // Verificar que tenemos un marketId válido
    if (!marketId || isNaN(marketId)) {
        console.error('Market ID inválido:', marketId);
        showError('Error: ID de mercado inválido. Regresando a la lista de mercados...');
        setTimeout(() => {
            window.location.href = '/market-management';
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
        // Función helper para truncar texto
        const truncateText = (text, maxLength) => {
            if (!text) return '-';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        };

        const codigoCorto = truncateText(product['codigoPresentacion'], 12);
        const descripcionCorta = truncateText(product['descripcionPresentacion'], 60);
        // NO truncar molécula - mostrar texto completo
        const moleculaCompleta = product['molecula'] || '-';
        
        return `
            <tr class="hover:bg-gray-50 product-row">
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm font-medium text-gray-900" title="${product['codigoPresentacion'] || '-'}">
                    <span class="font-mono block truncate">${codigoCorto}</span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-900" title="${product['descripcionPresentacion'] || '-'}">
                    <div class="block truncate">
                        <p class="font-medium truncate">${descripcionCorta}</p>
                    </div>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-500">
                    <span class="inline-flex items-center justify-center px-1 sm:px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium ${getBrandClass(product['marcaGenerico'])}">
                        <span class="hidden sm:inline">${product['marcaGenerico'] || '-'}</span>
                        <span class="sm:hidden">${(product['marcaGenerico'] || '-').charAt(0)}</span>
                    </span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-500 hidden sm:table-cell">
                    <span class="inline-flex items-center justify-center px-1 sm:px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium ${getEthicClass(product['eticoPopular'])}">
                        <span class="hidden lg:inline">${product['eticoPopular'] || '-'}</span>
                        <span class="lg:hidden">${(product['eticoPopular'] || '-').charAt(0)}</span>
                    </span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-500 molecule-cell">
                    <div class="molecule-text">
                        <span class="block leading-tight">${moleculaCompleta}</span>
                    </div>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-500 text-center">
                    <span class="inline-flex items-center justify-center px-1 py-0.5 rounded-full text-xs font-medium ${getFuenteClass(product['fuente'])}" title="${product['fuente'] || 'Sin fuente'}">
                        <i class="fas fa-database text-xs"></i>
                        <span class="hidden lg:inline ml-1">${(product['fuente'] || 'N/A').substring(0, 4)}</span>
                    </span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-500 text-center hidden md:table-cell" title="${product['codigoFF3'] || '-'}">
                    <span class="font-mono text-xs block truncate">${product['codigoFF3'] || '-'}</span>
                </td>
                <td class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 text-xs sm:text-sm text-gray-500 text-center hidden md:table-cell" title="${product['codigoATC4'] || '-'}">
                    <span class="font-mono text-xs block truncate">${product['codigoATC4'] || '-'}</span>
                </td>
                <td class="px-1 sm:px-2 lg:px-3 py-2 sm:py-3 lg:py-4 text-right">
                    <div class="flex items-center justify-center space-x-0.5 sm:space-x-1 action-buttons">
                        <button onclick="openRemoveProductModal('${product['codigoPresentacion'] || product['Código_Presentación']}', '${escapeForJs(product['descripcionPresentacion'] || product['Descripción_Presentación'])}')" 
                                class="text-red-600 hover:text-red-900 transition-colors duration-200 p-1 rounded hover:bg-red-50 action-btn" 
                                title="Quitar producto">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                        <button onclick="openChangeMarketModal('${product['codigoPresentacion'] || product['Código_Presentación']}', '${escapeForJs(product['descripcionPresentacion'] || product['Descripción_Presentación'])}')" 
                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200 p-1 rounded hover:bg-blue-50 action-btn" 
                                title="Cambiar mercado">
                            <i class="fas fa-exchange-alt text-xs"></i>
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
            default: return 'bg-purple-100 text-purple-800';
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
                let errorMessage = 'Error al procesar la operación';
                
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
        currentProductCode = null;
        currentProductName = null;
        
        // Limpiar todo el estado del modal
        clearSelectedMarket();
        document.getElementById('market-search-input').value = '';
        document.getElementById('markets-dropdown').classList.add('hidden');
        availableMarkets = [];
    };

    // Función global para cambiar mercado del producto
    window.changeProductMarket = function() {
        if (!currentProductCode) {
            showErrorNotification('Error: No se ha seleccionado un producto válido');
            return;
        }

        const selectedMarketId = document.getElementById('selected-market-id').value;
        if (!selectedMarketId) {
            showErrorNotification('Error: Debe seleccionar un mercado de destino');
            return;
        }

        // Mostrar loading en el botón
        const btn = $('#confirm-change-btn');
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
                codigoPresentacion: currentProductCode,
                nuevoMercadoId: parseInt(selectedMarketId)
            },
            success: function(response) {
                if (response.success) {
                    // Mostrar notificación de éxito
                    showSuccessNotification(response.message);
                    
                    // Cerrar modal
                    closeChangeMarketModal();
                    
                    // Recargar la tabla de productos para reflejar los cambios
                    loadProducts();
                } else {
                    showErrorNotification('Error: ' + response.message);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error en changeProductMarket:', {xhr, textStatus, errorThrown});
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
        const currentMarketId = window.marketId;
        
        // Filtrar mercados
        const filteredMarkets = availableMarkets.filter(market => {
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
