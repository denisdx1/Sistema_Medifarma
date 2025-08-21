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
    $('#edit-market-name').val(marketName);
    $('#edit-modal').removeClass('hidden');
    $('#edit-market-name').focus();
}

// Close edit modal
function closeEditModal() {
    $('#edit-modal').addClass('hidden');
}

// Toggle market status
function toggleMarketStatus(marketId, marketName, currentStatus) {
    const action = currentStatus === 'ACTIVO' ? 'desactivar' : 'activar';
    
    if (confirm(`¿Estás seguro de que quieres ${action} el mercado "${marketName}"?`)) {
        $.post(window.MarketManagementRoutes.toggleStatus, {
            market_id: marketId,
            _token: window.csrfToken
        })
        .done(function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                // Reload page to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        })
        .fail(function(xhr) {
            showToast('Error al cambiar estado del mercado', 'error');
        });
    }
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
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600 mr-3"></div>
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
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${market.idMercado}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-store text-purple-600 text-xs"></i>
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
    
    // Toggle Status Button
    const isActive = market.estado === 'ACTIVO';
    const toggleClass = isActive ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200';
    const toggleIcon = isActive ? 'fa-pause' : 'fa-play';
    const toggleText = isActive ? 'Desactivar' : 'Activar';
    
    buttons += `
        <button onclick="toggleMarketStatus(${market.idMercado}, '${safeMarketName}', '${market.estado}')"
                class="inline-flex items-center px-3 py-1 rounded-md text-sm ${toggleClass} transition-colors duration-200">
            <i class="fas ${toggleIcon} mr-1"></i>
            ${toggleText}
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
                <span class="px-2 py-1 text-xs text-white bg-purple-600 rounded font-medium">${page}</span>
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

    // Handle create form submission
    $('#create-market-form').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#create-submit-btn');
        submitBtn.prop('disabled', true);
        submitBtn.find('.btn-text').hide();
        submitBtn.find('.btn-loading').show();
        
        $.post(window.MarketManagementRoutes.create, $(this).serialize())
        .done(function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                closeCreateModal();
                // Redirect to the last page to show the new market
                setTimeout(() => {
                    if (response.redirect_to_page) {
                        window.location.href = `${window.MarketManagementRoutes.index}?page=${response.redirect_to_page}`;
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                const firstError = Object.values(response.errors)[0][0];
                showToast(firstError, 'error');
            } else {
                showToast('Error al crear mercado', 'error');
            }
        })
        .always(function() {
            submitBtn.prop('disabled', false);
            submitBtn.find('.btn-text').show();
            submitBtn.find('.btn-loading').hide();
        });
    });

    // Handle edit form submission
    $('#edit-market-form').on('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = $('#edit-submit-btn');
        submitBtn.prop('disabled', true);
        submitBtn.find('.btn-text').hide();
        submitBtn.find('.btn-loading').show();
        
        $.ajax({
            url: window.MarketManagementRoutes.update,
            method: 'PUT',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            }
        })
        .done(function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                closeEditModal();
                // Reload page to show changes
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.errors) {
                const firstError = Object.values(response.errors)[0][0];
                showToast(firstError, 'error');
            } else {
                showToast('Error al actualizar mercado', 'error');
            }
        })
        .always(function() {
            submitBtn.prop('disabled', false);
            submitBtn.find('.btn-text').show();
            submitBtn.find('.btn-loading').hide();
        });
    });

    // Close modal when clicking outside
    $(document).on('click', '#create-modal, #edit-modal', function(e) {
        if (e.target === this) {
            if (this.id === 'create-modal') {
                closeCreateModal();
            } else if (this.id === 'edit-modal') {
                closeEditModal();
            }
        }
    });

    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!$('#create-modal').hasClass('hidden')) {
                closeCreateModal();
            } else if (!$('#edit-modal').hasClass('hidden')) {
                closeEditModal();
            }
        }
    });
});
