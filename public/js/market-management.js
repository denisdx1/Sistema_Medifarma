// Market Management JavaScript Functions
// Define routes for JavaScript
window.MarketManagementRoutes = window.MarketManagementRoutes || {};
window.csrfToken = window.csrfToken || '';

// Global search functionality with AJAX
let searchTimeout;
let currentSearch = '';

// Utility function to safely escape strings for JavaScript
function escapeForJs(str) {
    if (!str) return '';
    return str.replace(/\\/g, '\\\\')
              .replace(/'/g, "\\'")
              .replace(/"/g, '\\"')
              .replace(/\n/g, '\\n')
              .replace(/\r/g, '\\r')
              .replace(/\t/g, '\\t');
}

// Open create modal
function openCreateModal() {
    $('#market-name').val('');
    $('#create-modal').removeClass('hidden');
    $('#market-name').focus();
}

// Close create modal
function closeCreateModal() {
    $('#create-modal').addClass('hidden');
}

// Open edit modal
function openEditModal(marketId, marketName) {
    $('#edit-market-id').val(marketId);
    $('#edit-market-name').val(marketName).data('original', marketName);
    $('#edit-modal').removeClass('hidden');
    $('#edit-market-name').focus();
}

// Close edit modal
function closeEditModal() {
    $('#edit-modal').addClass('hidden');
}

// Open create confirmation modal
function openCreateConfirmationModal() {
    const marketName = $('#market-name').val().trim();
    
    if (!marketName) {
        showToast('Por favor ingresa un nombre para el mercado', 'error');
        $('#market-name').focus();
        return;
    }
    
    // Update confirmation modal with market name
    $('#confirm-create-market-name').text(marketName);
    
    // Show confirmation modal
    $('#create-confirmation-modal').removeClass('hidden');
}

// Close create confirmation modal
function closeCreateConfirmationModal() {
    $('#create-confirmation-modal').addClass('hidden');
}

// Confirm create market
function confirmCreateMarket() {
    const marketName = $('#market-name').val().trim();
    const submitBtn = $('#confirm-create-btn');
    const btnText = submitBtn.find('.btn-text');
    const btnLoading = submitBtn.find('.btn-loading');
    
    // Show loading state
    btnText.addClass('hidden');
    btnLoading.removeClass('hidden');
    submitBtn.prop('disabled', true);
    
    // Submit form
    $.post(window.MarketManagementRoutes.create, {
        market_name: marketName,
        _token: window.csrfToken
    })
    .done(function(response) {
        if (response.success) {
            showToast(response.message, 'success');
            // Close both modals
            closeCreateConfirmationModal();
            closeCreateModal();
            // Reload page to show new market
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(response.message, 'error');
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            if (errors.market_name) {
                showToast(errors.market_name[0], 'error');
            } else {
                showToast('Error de validación', 'error');
            }
        } else {
            showToast('Error al crear mercado', 'error');
        }
    })
    .always(function() {
        // Reset loading state
        btnText.removeClass('hidden');
        btnLoading.addClass('hidden');
        submitBtn.prop('disabled', false);
    });
}

// Open edit confirmation modal
function openEditConfirmationModal() {
    const currentName = $('#edit-market-name').data('original') || $('#edit-market-name').val();
    const newName = $('#edit-market-name').val().trim();
    
    if (!newName) {
        showToast('Por favor ingresa un nombre para el mercado', 'error');
        $('#edit-market-name').focus();
        return;
    }
    
    if (currentName === newName) {
        showToast('No hay cambios para guardar', 'warning');
        return;
    }
    
    // Update confirmation modal with names
    $('#confirm-edit-current-name').text(currentName);
    $('#confirm-edit-new-name').text(newName);
    
    // Show confirmation modal
    $('#edit-confirmation-modal').removeClass('hidden');
}

// Close edit confirmation modal
function closeEditConfirmationModal() {
    $('#edit-confirmation-modal').addClass('hidden');
}

// Confirm edit market
function confirmEditMarket() {
    const marketId = $('#edit-market-id').val();
    const marketName = $('#edit-market-name').val().trim();
    const submitBtn = $('#confirm-edit-btn');
    const btnText = submitBtn.find('.btn-text');
    const btnLoading = submitBtn.find('.btn-loading');
    
    // Show loading state
    btnText.addClass('hidden');
    btnLoading.removeClass('hidden');
    submitBtn.prop('disabled', true);
    
    // Submit form
    $.ajax({
        url: window.MarketManagementRoutes.update,
        method: 'POST',
        data: {
            market_id: marketId,
            market_name: marketName,
            _token: window.csrfToken,
            _method: 'PUT'
        }
    })
    .done(function(response) {
        if (response.success) {
            showToast(response.message, 'success');
            // Close both modals
            closeEditConfirmationModal();
            closeEditModal();
            // Reload page to show updated market
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(response.message, 'error');
        }
    })
    .fail(function(xhr) {
        if (xhr.status === 422) {
            const errors = xhr.responseJSON.errors;
            if (errors.market_name) {
                showToast(errors.market_name[0], 'error');
            } else {
                showToast('Error de validación', 'error');
            }
        } else {
            showToast('Error al actualizar mercado', 'error');
        }
    })
    .always(function() {
        // Reset loading state
        btnText.removeClass('hidden');
        btnLoading.addClass('hidden');
        submitBtn.prop('disabled', false);
    });
}

// View market products
function viewMarketProducts(marketId, marketName) {
    console.log('viewMarketProducts called with:', {
        marketId: marketId,
        marketName: marketName,
        typeof_marketId: typeof marketId,
        typeof_marketName: typeof marketName
    });
    
    // Validar que el marketId sea válido
    if (!marketId || isNaN(marketId) || marketId <= 0) {
        console.error('Invalid market ID:', marketId);
        showToast('Error: ID de mercado inválido. No se puede cargar la vista de productos.', 'error');
        return;
    }
    
    // Validar que tengamos un nombre de mercado
    if (marketName === undefined || marketName === null) {
        console.warn('Market name is undefined/null, using default');
        marketName = 'Mercado sin nombre';
    }
    
    console.log('Redirecting to products for market:', marketId, marketName);
    
    // Redirect to products view for this market
    window.location.href = `/market-management/market/${marketId}/products`;
}

// Function to reset to original view
function resetToOriginalView() {
    window.location.href = window.MarketManagementRoutes.index || '/market-management';
}

// Function to perform global search via AJAX
function performGlobalSearch(searchTerm, page = 1) {
    const searchUrl = window.MarketManagementRoutes.search;
    const params = new URLSearchParams({
        search: searchTerm,
        page: page
    });
    
    // Show loading state
    showLoadingState();
    
    $.ajax({
        url: `${searchUrl}?${params}`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': window.csrfToken
        },
        success: function(data) {
            if (data.success) {
                updateTableWithResults(data.data);
                updateResultsInfo(data, searchTerm);
                updatePagination(data, searchTerm);
            } else {
                showError('Error en la búsqueda: ' + data.message);
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            showError('Error de conexión durante la búsqueda');
        },
        complete: function() {
            hideLoadingState();
        }
    });
}

// Function to show loading state
function showLoadingState() {
    const tbody = $('table tbody');
    tbody.html(`
        <tr>
            <td colspan="6" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600 mr-3"></div>
                    <span class="text-gray-600">Buscando...</span>
                </div>
            </td>
        </tr>
    `);
}

// Function to hide loading state
function hideLoadingState() {
    // This will be handled by updateTableWithResults
}

// Function to update table with search results
function updateTableWithResults(markets) {
    const tbody = $('table tbody');
    
    if (markets.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-search text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg">No se encontraron resultados</p>
                        <p class="text-sm">Intenta con otros términos de búsqueda</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    let tableHTML = '';
    markets.forEach(market => {
        tableHTML += generateMarketRow(market);
    });
    tbody.html(tableHTML);
}

// Function to generate market row HTML
function generateMarketRow(market) {
    // Format date properly
    const formattedDate = formatDate(market.fechaRegistro);
    
    // Escape market name for safe HTML usage
    const escapedMarketName = escapeHtml(market.mercado);
    
    // Generate status badge with icon
    const statusBadge = generateStateBadge(market.estado);
    
    // Generate action buttons based on market status
    const actionButtons = generateActionButtons(market);
    
    return `
        <tr class="hover:bg-gray-50 market-row" data-market-id="${market.idMercado}">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-store text-red-600 text-xs"></i>
                    </div>
                    <div class="font-medium text-gray-900">${escapedMarketName}</div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formattedDate}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${statusBadge}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                ${actionButtons}
            </td>
        </tr>
    `;
}

// Function to generate state badge with icon
function generateStateBadge(estado) {
    let badgeClass, icon;
    
    switch(estado) {
        case 'ACTIVO':
            badgeClass = 'bg-emerald-100 text-emerald-800';
            icon = 'fa-play';
            break;
        case 'INACTIVO':
            badgeClass = 'bg-red-100 text-red-800';
            icon = 'fa-pause';
            break;
        default:
            badgeClass = 'bg-gray-100 text-gray-800';
            icon = 'fa-question';
    }
    
    return `
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
            <i class="fas ${icon} mr-1"></i>
            ${estado}
        </span>
    `;
}

// Function to generate action buttons based on market status
function generateActionButtons(market) {
    let buttons = '';
    
    // Ver Productos Button - Always available (con validación)
    if (market.idMercado && !isNaN(market.idMercado)) {
        // Escapar el nombre del mercado de manera segura para JavaScript
        const safeMarketName = escapeForJs(market.mercado || '');
        buttons += `
            <button onclick="viewMarketProducts(${market.idMercado}, '${safeMarketName}')"
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200"
                    title="Ver productos del mercado">
                <i class="fas fa-box mr-1"></i>
                Productos
            </button>
        `;
        
        // Asignar Productos Button
        buttons += `
            <button onclick="openAssignProductsModal(${market.idMercado}, '${safeMarketName}')"
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-red-100 text-red-700 hover:bg-red-200 transition-colors duration-200"
                    title="Asignar productos desde RESTO">
                <i class="fas fa-plus-circle mr-1"></i>
                Asignar
            </button>
        `;
    } else {
        buttons += `
            <button disabled 
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-100 text-gray-400 cursor-not-allowed"
                    title="ID de mercado no válido">
                <i class="fas fa-box mr-1"></i>
                Productos
            </button>
        `;
    }
    
    // Escapar el nombre del mercado de manera segura para JavaScript
    const safeMarketName = escapeForJs(market.mercado || '');
    
    // Edit Market Button
    buttons += `
        <button onclick="openEditModal(${market.idMercado}, '${safeMarketName}')"
                class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                title="Editar mercado">
            <i class="fas fa-edit mr-1"></i>
            Editar
        </button>
    `;
    
    return buttons;
}

// Function to escape HTML characters
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Function to update results info
function updateResultsInfo(data, searchTerm) {
    const resultsInfo = $('#pagination-info');
    if (resultsInfo.length) {
        const start = ((data.current_page - 1) * data.per_page) + 1;
        const end = Math.min(data.current_page * data.per_page, data.total);
        
        if (searchTerm) {
            resultsInfo.html(`${start} - ${end} de ${data.total} resultados para "${searchTerm}"`);
        } else {
            resultsInfo.html(`${start} - ${end} de ${data.total} mercados`);
        }
    }
}

// Function to update pagination
function updatePagination(data, searchTerm) {
    const paginationContainer = $('#pagination-links');
    
    if (!paginationContainer.length || data.last_page <= 1) {
        // Hide pagination if only one page or container not found
        $('#pagination-container').hide();
        return;
    }
    
    // Show pagination container
    $('#pagination-container').show();
    
    let paginationHTML = '';
    
    // Previous Page Link
    if (data.current_page <= 1) {
        paginationHTML += `
            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
        `;
    } else {
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page - 1}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
    }

    // Calculate page range
    const start = Math.max(data.current_page - 2, 1);
    const end = Math.min(start + 4, data.last_page);
    const adjustedStart = Math.max(end - 4, 1);

    // First page if not in range
    if (adjustedStart > 1) {
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', 1); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">1</a>
        `;
        if (adjustedStart > 2) {
            paginationHTML += '<span class="px-1 text-xs text-gray-400">...</span>';
        }
    }

    // Page Numbers
    for (let page = adjustedStart; page <= end; page++) {
        if (page == data.current_page) {
            paginationHTML += `
                <span class="px-2 py-1 text-xs text-white bg-red-600 rounded font-medium">${page}</span>
            `;
        } else {
            paginationHTML += `
                <a href="#" onclick="performGlobalSearch('${searchTerm}', ${page}); return false;" 
                   class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">${page}</a>
            `;
        }
    }

    // Last page if not in range
    if (end < data.last_page) {
        if (end < data.last_page - 1) {
            paginationHTML += '<span class="px-1 text-xs text-gray-400">...</span>';
        }
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.last_page}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">${data.last_page}</a>
        `;
    }

    // Next Page Link
    if (data.current_page >= data.last_page) {
        paginationHTML += `
            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
        `;
    } else {
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page + 1}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
    }
    
    paginationContainer.html(paginationHTML);
}

// Function to show error messages
function showError(message) {
    showToast(message, 'error');
}

// Toast function
function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast toast-${type} p-4 mb-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300">
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `);
    
    $('#toast-container').append(toast);
    
    setTimeout(() => {
        toast.removeClass('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        toast.addClass('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Document ready functions
$(document).ready(function() {
    // Search input functionality
    $('#search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().trim();
        currentSearch = searchTerm;
        
        searchTimeout = setTimeout(() => {
            if (searchTerm === '') {
                // Si la búsqueda está vacía, restablecer vista original
                resetToOriginalView();
            } else {
                performGlobalSearch(searchTerm);
            }
        }, 500); // Debounce search for 500ms
    });

    // Close modal when clicking outside
    $(document).on('click', '#create-modal, #edit-modal, #create-confirmation-modal, #edit-confirmation-modal', function(e) {
        if (e.target === this) {
            if (this.id === 'create-modal') {
                closeCreateModal();
            } else if (this.id === 'edit-modal') {
                closeEditModal();
            } else if (this.id === 'create-confirmation-modal') {
                closeCreateConfirmationModal();
            } else if (this.id === 'edit-confirmation-modal') {
                closeEditConfirmationModal();
            }
        }
    });

    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!$('#create-confirmation-modal').hasClass('hidden')) {
                closeCreateConfirmationModal();
            } else if (!$('#edit-confirmation-modal').hasClass('hidden')) {
                closeEditConfirmationModal();
            } else if (!$('#create-modal').hasClass('hidden')) {
                closeCreateModal();
            } else if (!$('#edit-modal').hasClass('hidden')) {
                closeEditModal();
            }
        }
    });
});

// Variables globales para asignación de productos
let currentAssignMarketId = null;
let currentAssignMarketName = null;
let selectedProducts = [];
let restoProducts = [];
let currentRestoCursor = null;
let isLoadingResto = false;

// Filter variables for resto products modal
let currentRestoFilters = {
    descripcionFF3: '',
    descripcionATC4: '',
    descripcionLaboratorio: '',
    fuente: '',
    molecula: '',
    descripcionCorporacion: ''
};
let restoFilterOptions = {
    descripcionFF3: [],
    descripcionATC4: [],
    descripcionLaboratorio: [],
    fuente: [],
    molecula: [],
    descripcionCorporacion: []
};
let restoFilterTimeout = null;

// Función para abrir modal de asignación de productos
function openAssignProductsModal(marketId, marketName) {
    currentAssignMarketId = marketId;
    currentAssignMarketName = marketName;
    selectedProducts = [];
    
    // Actualizar título
    document.getElementById('assign-market-name').textContent = marketName;
    
    // Limpiar búsqueda
    document.getElementById('resto-product-search').value = '';
    document.getElementById('clear-resto-search').classList.add('hidden');
    
    // Limpiar filtros
    clearAllRestoFilters();
    
    // Resetear estado del panel de filtros (siempre colapsado al abrir)
    const filtersPanel = $('#resto-filters-panel');
    const toggleButton = $('#toggle-resto-filters');
    const toggleIcon = toggleButton.find('i').last();
    const toggleText = toggleButton.find('span');
    
    filtersPanel.addClass('hidden');
    toggleIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    toggleText.text('Mostrar Filtros');
    
    // Mostrar modal
    document.getElementById('assign-products-modal').classList.remove('hidden');
    
    // Cargar productos RESTO
    loadRestoProducts();
    
    // Configurar event listeners
    setupRestoProductSearch();
    setupRestoFilters();
    
    // Cargar opciones de filtros
    loadRestoFilterOptions();
}

// Función para cerrar modal
function closeAssignProductsModal() {
    document.getElementById('assign-products-modal').classList.add('hidden');
    currentAssignMarketId = null;
    currentAssignMarketName = null;
    selectedProducts = [];
    restoProducts = [];
    currentRestoCursor = null;
}

// Configurar búsqueda de productos RESTO
function setupRestoProductSearch() {
    const searchInput = document.getElementById('resto-product-search');
    const clearButton = document.getElementById('clear-resto-search');
    
    // Event listener para búsqueda
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length > 0) {
            clearButton.classList.remove('hidden');
        } else {
            clearButton.classList.add('hidden');
        }
        
        // Debounce de 300ms
        clearTimeout(window.restoSearchTimeout);
        window.restoSearchTimeout = setTimeout(() => {
            searchRestoProducts(query);
        }, 300);
    });
    
    // Event listener para limpiar búsqueda
    clearButton.addEventListener('click', function() {
        searchInput.value = '';
        clearButton.classList.add('hidden');
        
        // Also clear all filters when clearing search
        clearAllRestoFilters();
        
        loadRestoProducts();
    });
}

// Setup resto filters functionality
function setupRestoFilters() {
    // Remove existing event listeners to prevent duplicates
    $('#toggle-resto-filters').off('click');
    $('#clear-all-resto-filters').off('click');
    
    // Initialize each filter select
    Object.keys(currentRestoFilters).forEach(filterKey => {
        const filterSelect = $(`#resto-filter-${filterKey}`);
        const clearButton = $(`#clear-resto-filter-${filterKey}`);

        // Remove existing listeners
        filterSelect.off('change');
        clearButton.off('click');

        // Filter change event
        filterSelect.on('change', function() {
            const value = $(this).val();
            currentRestoFilters[filterKey] = value;
            
            // Show/hide clear button
            if (value) {
                clearButton.removeClass('hidden');
            } else {
                clearButton.addClass('hidden');
            }

            // Clear previous timeout
            if (restoFilterTimeout) {
                clearTimeout(restoFilterTimeout);
            }

            // Set new timeout for filter (300ms debounce)
            restoFilterTimeout = setTimeout(() => {
                applyRestoFilters();
            }, 300);
        });

        // Clear individual filter
        clearButton.on('click', function() {
            filterSelect.val('');
            clearButton.addClass('hidden');
            currentRestoFilters[filterKey] = '';
            applyRestoFilters();
        });
    });

    // Clear all filters button
    $('#clear-all-resto-filters').on('click', function() {
        clearAllRestoFilters();
        applyRestoFilters();
    });

    // Toggle filters panel
    $('#toggle-resto-filters').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Toggle filters clicked'); // Debug log
        
        const filtersPanel = $('#resto-filters-panel');
        const icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
        const textSpan = $(this).find('span');
        
        filtersPanel.toggleClass('hidden');
        
        if (filtersPanel.hasClass('hidden')) {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            textSpan.text('Mostrar Filtros');
            console.log('Filters collapsed'); // Debug log
        } else {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            textSpan.text('Ocultar Filtros');
            console.log('Filters expanded'); // Debug log
        }
    });
}

// Clear all resto filters
function clearAllRestoFilters() {
    Object.keys(currentRestoFilters).forEach(filterKey => {
        currentRestoFilters[filterKey] = '';
        $(`#resto-filter-${filterKey}`).val('');
        $(`#clear-resto-filter-${filterKey}`).addClass('hidden');
    });
    updateRestoFilterStatus();
}

// Apply resto filters
function applyRestoFilters() {
    // Reset pagination when filtering
    currentRestoCursor = null;
    
    // Update filter status
    updateRestoFilterStatus();
    
    // Load products with current filters
    const searchQuery = document.getElementById('resto-product-search').value.trim();
    loadRestoProducts(null, false, searchQuery);
}

// Update filter status display
function updateRestoFilterStatus() {
    const activeFilters = Object.values(currentRestoFilters).filter(value => value.length > 0);
    const statusDiv = $('#resto-filter-status');
    const countSpan = $('#resto-filter-count');
    const clearAllBtn = $('#clear-all-resto-filters');
    
    if (activeFilters.length > 0) {
        countSpan.text(activeFilters.length);
        statusDiv.removeClass('hidden');
        clearAllBtn.removeClass('hidden');
    } else {
        statusDiv.addClass('hidden');
        clearAllBtn.addClass('hidden');
    }
}

// Load filter options from API
function loadRestoFilterOptions() {
    $.ajax({
        url: '/market-management/resto-filter-options',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                restoFilterOptions = response.data;
                populateRestoFilterSelects();
            }
        },
        error: function(xhr) {
            console.error('Error loading filter options:', xhr);
        }
    });
}

// Populate filter select elements
function populateRestoFilterSelects() {
    Object.keys(restoFilterOptions).forEach(filterKey => {
        const select = $(`#resto-filter-${filterKey}`);
        const options = restoFilterOptions[filterKey];
        
        // Clear existing options except the first one
        select.find('option:not(:first)').remove();
        
        // Add new options
        options.forEach(option => {
            if (option && option.trim()) {
                select.append(`<option value="${option}">${option}</option>`);
            }
        });
    });
}

// Cargar productos del mercado RESTO
function loadRestoProducts(cursor = null, append = false, searchQuery = '') {
    if (isLoadingResto) return;
    
    isLoadingResto = true;
    showRestoLoadingState();
    
    const data = {
        cursor: cursor,
        search: searchQuery
    };
    
    // Add filter parameters
    Object.keys(currentRestoFilters).forEach(filterKey => {
        if (currentRestoFilters[filterKey] && currentRestoFilters[filterKey].length > 0) {
            data[`filter_${filterKey}`] = currentRestoFilters[filterKey];
        }
    });
    
    $.ajax({
        url: '/market-management/resto-products',
        method: 'GET',
        data: data,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                if (!append) {
                    updateRestoProductsTable(response.data.products);
                    currentRestoCursor = response.data.next_cursor;
                } else {
                    appendRestoProductsToTable(response.data.products);
                    currentRestoCursor = response.data.next_cursor;
                }
                
                updateRestoPaginationInfo(response.data);
                
                if (response.data.products.length === 0 && !append) {
                    showRestoEmptyState();
                }
            } else {
                showErrorNotification('Error al cargar productos RESTO: ' + response.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error loading resto products:', {xhr, textStatus, errorThrown});
            showErrorNotification('Error al cargar productos RESTO');
        },
        complete: function() {
            isLoadingResto = false;
            hideRestoLoadingState();
        }
    });
}

// Buscar productos RESTO
function searchRestoProducts(query) {
    selectedProducts = []; // Limpiar selección en nueva búsqueda
    updateSelectedCount();
    currentRestoCursor = null;
    loadRestoProducts(null, false, query);
}

// Mostrar estado de carga
function showRestoLoadingState() {
    document.getElementById('resto-loading-overlay').classList.remove('hidden');
    document.getElementById('resto-search-loading').classList.remove('hidden');
}

// Ocultar estado de carga
function hideRestoLoadingState() {
    document.getElementById('resto-loading-overlay').classList.add('hidden');
    document.getElementById('resto-search-loading').classList.add('hidden');
}

// Mostrar estado vacío
function showRestoEmptyState() {
    document.getElementById('resto-empty-state').classList.remove('hidden');
    document.getElementById('resto-products-table-body').innerHTML = '';
}

// Actualizar tabla de productos RESTO
function updateRestoProductsTable(products) {
    const tbody = document.getElementById('resto-products-table-body');
    tbody.innerHTML = '';
    
    document.getElementById('resto-empty-state').classList.add('hidden');
    
    products.forEach(product => {
        tbody.appendChild(generateRestoProductRow(product));
    });
}

// Añadir productos a la tabla
function appendRestoProductsToTable(products) {
    const tbody = document.getElementById('resto-products-table-body');
    
    products.forEach(product => {
        tbody.appendChild(generateRestoProductRow(product));
    });
}

// Generar fila de producto RESTO
function generateRestoProductRow(product) {
    const row = document.createElement('tr');
    row.className = 'divide-x divide-gray-200 hover:bg-gray-50';
    
    const isSelected = selectedProducts.includes(product.codigoPresentacion);
    
    const marcaGenerico = product.marcaGenerico || '-';
    const eticoPopular = product.eticoPopular || '-';
    
    row.innerHTML = `
        <!-- Selección 4% -->
        <td class="w-[4%] px-1 py-1 text-center">
            <input type="checkbox" 
                   class="product-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500"
                   data-product-code="${product.codigoPresentacion}"
                   data-product-fuente="${product.fuente || 'IQV'}"
                   onchange="toggleProductSelection('${product.codigoPresentacion}', '${product.fuente || 'IQV'}')"
                   ${isSelected ? 'checked' : ''}>
        </td>
        <!-- Descripción 18% -->
        <td class="w-[18%] px-1 py-1 text-xs text-gray-900 description-cell" title="${product.descripcionPresentacion || '-'}">
            <div class="truncate font-medium" style="white-space: pre-wrap;">${product.descripcionPresentacion || '-'}</div>
        </td>
        <!-- M/G 8% -->
        <td class="w-[8%] px-1 py-1 text-center text-xs hidden sm:table-cell">
            <span class="lg:inline hidden">${marcaGenerico === 'MARCA' ? 'MARCA' : 'GENÉRICO'}</span>
            <span class="lg:hidden">${marcaGenerico === 'MARCA' ? 'M' : 'G'}</span>
        </td>
        <!-- É/P 8% -->
        <td class="w-[8%] px-1 py-1 text-center text-xs hidden sm:table-cell">
            <span class="lg:inline hidden">${eticoPopular === 'ÉTICO' ? 'ÉTICO' : 'POPULAR'}</span>
            <span class="lg:hidden">${eticoPopular === 'ÉTICO' ? 'É' : 'P'}</span>
        </td>
        <!-- Fuente 6% -->
        <td class="w-[6%] px-1 py-1 text-center text-xs text-gray-500 single-line-cell">
            <span class="text-blue-600 font-medium">${product.fuente || 'IQV'}</span>
        </td>
        <!-- Molécula 14% -->
        <td class="w-[14%] px-1 py-1 text-xs text-gray-500 hidden lg:table-cell molecule-cell" title="${product.descripcionMolecula || '-'}">
            <span class="text-gray-700">${product.descripcionMolecula || '-'}</span>
        </td>
        <!-- FF3 10% -->
        <td class="w-[10%] px-1 py-1 text-xs text-gray-500 hidden md:table-cell single-line-cell" title="${product.descripcionFF3 || '-'}">
            <span class="truncate">${product.descripcionFF3 || '-'}</span>
        </td>
        <!-- ATC4 10% -->
        <td class="w-[10%] px-1 py-1 text-xs text-gray-500 hidden md:table-cell single-line-cell" title="${product.descripcionATC4 || '-'}">
            <span class="truncate">${product.descripcionATC4 || '-'}</span>
        </td>
        <!-- Laboratorio 8% -->
        <td class="w-[8%] px-1 py-1 text-xs text-gray-500 hidden lg:table-cell single-line-cell" title="${product.descripcionLaboratorio || '-'}">
            <span class="truncate">${product.descripcionLaboratorio || '-'}</span>
        </td>
        <!-- Corporación 8% -->
        <td class="w-[8%] px-1 py-1 text-xs text-gray-500 hidden xl:table-cell single-line-cell" title="${product.descripcionCorporacion || '-'}">
            <span class="truncate">${product.descripcionCorporacion || '-'}</span>
        </td>
    `;
    
    return row;
}

// Toggle selección de producto
function toggleProductSelection(productCode, fuente) {
    // Asegurar que la fuente tenga un valor por defecto
    const safeFuente = fuente || 'IQV';
    
    const index = selectedProducts.findIndex(p => p.code === productCode);
    
    if (index > -1) {
        selectedProducts.splice(index, 1);
    } else {
        selectedProducts.push({
            code: productCode,
            fuente: safeFuente
        });
    }
    
    updateSelectedCount();
    updateSelectAllCheckbox();
}

// Toggle todos los productos
function toggleAllProducts() {
    const selectAllCheckbox = document.getElementById('select-all-products');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    if (selectAllCheckbox.checked) {
        // Seleccionar todos
        productCheckboxes.forEach(checkbox => {
            const productCode = checkbox.dataset.productCode;
            const fuente = checkbox.dataset.productFuente || 'IQV'; // Fuente por defecto
            
            if (!selectedProducts.find(p => p.code === productCode)) {
                selectedProducts.push({
                    code: productCode,
                    fuente: fuente
                });
                checkbox.checked = true;
            }
        });
    } else {
        // Deseleccionar todos
        productCheckboxes.forEach(checkbox => {
            const productCode = checkbox.dataset.productCode;
            const index = selectedProducts.findIndex(p => p.code === productCode);
            
            if (index > -1) {
                selectedProducts.splice(index, 1);
                checkbox.checked = false;
            }
        });
    }
    
    updateSelectedCount();
}

// Actualizar checkbox "Seleccionar todos"
function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('select-all-products');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    
    if (productCheckboxes.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCheckboxes.length === productCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else if (checkedCheckboxes.length > 0) {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    } else {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    }
}

// Actualizar contador de seleccionados
function updateSelectedCount() {
    document.getElementById('selected-count').textContent = selectedProducts.length;
    
    const assignBtn = document.getElementById('assign-selected-btn');
    if (selectedProducts.length > 0) {
        assignBtn.disabled = false;
    } else {
        assignBtn.disabled = true;
    }
}

// Actualizar información de paginación
function updateRestoPaginationInfo(data) {
    const info = document.getElementById('resto-pagination-info');
    const controls = document.getElementById('resto-pagination-controls');
    
    // Información de productos
    info.textContent = `${data.products.length} de ${data.total} productos en RESTO`;
    
    // Limpiar controles anteriores
    controls.innerHTML = '';
    
    // Solo mostrar paginación si hay más páginas o si no es la primera carga
    if (data.has_more_pages || currentRestoCursor) {
        let paginationHTML = '';
        
        // Botón "Cargar más" si hay más páginas
        if (data.has_more_pages) {
            paginationHTML += `
                <button onclick="loadMoreRestoProducts()" 
                        id="load-more-resto-btn"
                        class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors duration-200 flex items-center">
                    <i class="fas fa-chevron-down mr-1"></i>
                    Cargar más productos
                </button>
            `;
        }
        
        // Botón "Mostrar todo" si hay pocas páginas restantes
        if (data.total > 0 && data.total <= 200) {
            paginationHTML += `
                <button onclick="loadAllRestoProducts()" 
                        id="load-all-resto-btn"
                        class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors duration-200 flex items-center ml-2">
                    <i class="fas fa-list mr-1"></i>
                    Mostrar todos
                </button>
            `;
        }
        
        controls.innerHTML = paginationHTML;
    } else if (data.total === 0) {
        info.textContent = 'No se encontraron productos en RESTO';
    }
}

// Cargar más productos RESTO
function loadMoreRestoProducts() {
    if (!currentRestoCursor || isLoadingResto) return;
    
    const searchQuery = document.getElementById('resto-product-search').value.trim();
    loadRestoProducts(currentRestoCursor, true, searchQuery);
}

// Cargar todos los productos RESTO
function loadAllRestoProducts() {
    if (isLoadingResto) return;
    
    const searchQuery = document.getElementById('resto-product-search').value.trim();
    
    // Mostrar loading
    isLoadingResto = true;
    showRestoLoadingState();
    
    // Hacer petición para obtener todos los productos
    const data = {
        per_page: 200, // Máximo permitido
        search: searchQuery
    };
    
    $.ajax({
        url: '/market-management/resto-products',
        method: 'GET',
        data: data,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                updateRestoProductsTable(response.data.products);
                currentRestoCursor = response.data.next_cursor;
                updateRestoPaginationInfo(response.data);
                
                if (response.data.products.length === 0) {
                    showRestoEmptyState();
                }
            } else {
                showErrorNotification('Error al cargar todos los productos: ' + response.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error loading all resto products:', {xhr, textStatus, errorThrown});
            showErrorNotification('Error al cargar todos los productos');
        },
        complete: function() {
            isLoadingResto = false;
            hideRestoLoadingState();
        }
    });
}

// Asignar productos seleccionados
function assignSelectedProducts() {
    if (selectedProducts.length === 0) {
        showErrorNotification('No hay productos seleccionados');
        return;
    }
    
    if (!currentAssignMarketId) {
        showErrorNotification('Error: No se ha seleccionado un mercado');
        return;
    }
    
    // Mostrar loading en botón
    const btn = document.getElementById('assign-selected-btn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    btn.disabled = true;
    
    $.ajax({
        url: '/market-management/assign-products',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            idMercado: currentAssignMarketId,
            products: selectedProducts
        },
        success: function(response) {
            if (response.success) {
                // Mensaje detallado de éxito
                let successMessage = '';
                if (response.assigned_count === 1) {
                    successMessage = `1 producto asignado correctamente al mercado "${response.market_name}"`;
                } else {
                    successMessage = `${response.assigned_count} productos asignados correctamente al mercado "${response.market_name}"`;
                }
                
                // Mostrar advertencias si las hay
                if (response.warnings && response.warnings.length > 0) {
                    successMessage += `\n\nAdvertencias:\n${response.warnings.join('\n')}`;
                }
                
                showSuccessNotification(successMessage);
                
                // Cerrar modal y recargar productos RESTO
                closeAssignProductsModal();
                
                // Opcional: Recargar la lista de productos RESTO para reflejar los cambios
                setTimeout(() => {
                    if (document.getElementById('assign-products-modal').classList.contains('hidden') === false) {
                        loadRestoProducts();
                    }
                }, 1000);
                
            } else {
                showErrorNotification('Error: ' + response.message);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error assigning products:', {xhr, textStatus, errorThrown});
            let errorMessage = 'Error al asignar productos';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showErrorNotification('Error: ' + errorMessage);
        },
        complete: function() {
            // Restaurar botón
            btnText.classList.remove('hidden');
            btnLoading.classList.add('hidden');
            btn.disabled = false;
        }
    });
}

// Funciones de notificación
function showSuccessNotification(message) {
    showNotification(message, 'success');
}

function showErrorNotification(message) {
    showNotification(message, 'error');
}

function showNotification(message, type = 'info') {
    const container = document.getElementById('toast-container') || document.body;
    
    // Crear el toast
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 max-w-sm w-full bg-white border border-gray-200 rounded-lg shadow-lg transform translate-x-full opacity-0 transition-all duration-300 ease-in-out z-50`;
    
    // Colores según el tipo
    let iconClass, bgClass, borderClass, textClass;
    
    switch(type) {
        case 'success':
            iconClass = 'fas fa-check-circle text-green-600';
            bgClass = 'bg-green-50';
            borderClass = 'border-green-200';
            textClass = 'text-green-800';
            break;
        case 'error':
            iconClass = 'fas fa-exclamation-circle text-red-600';
            bgClass = 'bg-red-50';
            borderClass = 'border-red-200';
            textClass = 'text-red-800';
            break;
        default:
            iconClass = 'fas fa-info-circle text-blue-600';
            bgClass = 'bg-blue-50';
            borderClass = 'border-blue-200';
            textClass = 'text-blue-800';
    }
    
    toast.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${iconClass}"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium ${textClass}">
                        ${message}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.style.transform='translateX(100%)'; setTimeout(() => this.parentElement.parentElement.parentElement.parentElement.remove(), 300);" 
                            class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Añadir clases específicas del tipo
    toast.classList.add(bgClass, borderClass);
    
    // Añadir al DOM
    container.appendChild(toast);
    
    // Animación de entrada
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}
