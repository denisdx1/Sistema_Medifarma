// Market Management JavaScript Functions - CLEAN VERSION
// Define routes for JavaScript
window.MarketManagementRoutes = window.MarketManagementRoutes || {};
window.csrfToken = window.csrfToken || '';

// Global search functionality with AJAX
let searchTimeout;
let currentSearch = '';
let productSearchTimeout = null; // Para búsqueda de productos

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

// Utility function to escape HTML
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Utility function to format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES');
    } catch (e) {
        return 'N/A';
    }
}

// ========================================
// FUNCIONES PARA BÚSQUEDA DE MARCAS
// ========================================

// Function to perform global search via AJAX
function performGlobalSearch(searchTerm, page = 1) {
    const searchUrl = window.MarketManagementRoutes.search;
    const params = new URLSearchParams({
        search: searchTerm,
        page: page
    });
    
    // Debug log
    console.log('Search URL:', searchUrl);
    console.log('Search params:', params.toString());
    
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
            console.log('Search response:', data);
            if (data.success) {
                updateTableWithResults(data.data);
                updateResultsInfo(data, searchTerm);
                updatePagination(data, searchTerm);
            } else {
                showError('Error en la búsqueda: ' + data.message);
            }
        },
        error: function(xhr) {
            console.error('Search error:', xhr);
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
            <td colspan="4" class="px-6 py-8 text-center">
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
    // Loading state is handled by the search function
}

// Function to update table with search results
function updateTableWithResults(markets) {
    const tbody = $('table tbody');
    
    if (markets.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-search mr-2"></i>
                    No se encontraron resultados
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    markets.forEach(market => {
        html += `
            <tr class="hover:bg-gray-50 market-row" data-market-id="${market.idMercado}" data-product-code="${market.codigoPresentacion}">
                <!-- Marca (Descripción Producto) -->
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-secondary-light rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-tags text-primary text-xs"></i>
                        </div>
                        <div>
                            <button onclick="redirectToProductsWithBrand('${escapeForJs(market.marca)}')" 
                                    class="font-medium text-gray-900 hover:text-primary hover:underline transition-colors cursor-pointer"
                                    title="Ver productos de esta marca">
                                ${escapeHtml(market.marca)}
                            </button>
                            <div class="text-sm text-gray-500">Código: ${escapeHtml(market.codigoPresentacion)}</div>
                        </div>
                    </div>
                </td>
                <!-- Mercado Asignado -->
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-6 h-6 bg-secondary-purple rounded-full flex items-center justify-center mr-2">
                            <i class="fas fa-store text-primary text-xs"></i>
                        </div>
                        ${market.mercado ? 
                            `<button onclick="redirectToProductsWithMarket('${escapeForJs(market.mercado)}')" 
                                    class="font-medium text-primary hover:text-secondary hover:underline transition-colors cursor-pointer"
                                    title="Ver productos de este mercado">
                                ${escapeHtml(market.mercado)}
                            </button>` :
                            `<span class="text-gray-400 italic" title="Sin mercado asignado">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Sin asignar
                            </span>`
                        }
                    </div>
                </td>
                <!-- Estado -->
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        ${market.estado == 'ACTIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'}">
                        <i class="fas ${market.estado == 'ACTIVO' ? 'fa-play' : 'fa-pause'} mr-1"></i>
                        ${escapeHtml(market.estado)}
                    </span>
                </td>
                <!-- Acciones -->
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        ${market.mercado ? 
                            `<!-- Editar Nombre del Mercado -->
                            <button onclick="openEditModal(${market.idMercado}, '${escapeForJs(market.mercado)}')"
                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-secondary-light text-primary hover:bg-secondary-muted transition-colors duration-200"
                                    title="Editar nombre del mercado">
                                <i class="fas fa-edit mr-1"></i>
                                Editar Mercado
                            </button>
                            
                            <!-- Asignar Producto -->
                            <button onclick="redirectToProductsWithRestoAndMarket('${escapeForJs(market.mercado)}')"
                                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-primary text-white hover:bg-secondary transition-colors duration-200"
                                    title="Asignar producto a este mercado">
                                <i class="fas fa-plus mr-1"></i>
                                Asignar Producto
                            </button>` :
                            `<!-- Sin mercado asignado -->
                            <span class="text-gray-400 italic text-xs">
                                <i class="fas fa-info-circle mr-1"></i>
                                Sin mercado asignado
                            </span>`
                        }
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.html(html);
}

// Function to update results info
function updateResultsInfo(data, searchTerm) {
    const infoElement = $('#pagination-info');
    if (infoElement.length) {
        const total = data.total || 0;
        const from = data.from || 0;
        const to = data.to || 0;
        
        if (total > 0) {
            infoElement.text(`${from} - ${to} de ${total} marcas encontradas para "${searchTerm}"`);
        } else {
            infoElement.text(`No se encontraron marcas para "${searchTerm}"`);
        }
    }
}

// Function to update pagination
function updatePagination(data, searchTerm) {
    const paginationContainer = $('#pagination-links');
    if (!paginationContainer.length) return;
    
    const currentPage = data.current_page || 1;
    const lastPage = data.last_page || 1;
    const total = data.total || 0;
    
    if (total === 0) {
        paginationContainer.html('');
        return;
    }
    
    let paginationHtml = '';
    
    // Previous button
    if (currentPage > 1) {
        paginationHtml += `
            <a href="#" onclick="performGlobalSearch('${escapeForJs(searchTerm)}', ${currentPage - 1}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
    } else {
        paginationHtml += `
            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
        `;
    }
    
    // Page numbers
    const start = Math.max(1, currentPage - 2);
    const end = Math.min(lastPage, currentPage + 2);
    
    if (start > 1) {
        paginationHtml += `
            <a href="#" onclick="performGlobalSearch('${escapeForJs(searchTerm)}', 1); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">1</a>
        `;
        if (start > 2) {
            paginationHtml += '<span class="px-1 text-xs text-gray-400">...</span>';
        }
    }
    
    for (let i = start; i <= end; i++) {
        if (i === currentPage) {
            paginationHtml += `
                <span class="px-2 py-1 text-xs text-white bg-red-600 rounded font-medium">${i}</span>
            `;
        } else {
            paginationHtml += `
                <a href="#" onclick="performGlobalSearch('${escapeForJs(searchTerm)}', ${i}); return false;" 
                   class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">${i}</a>
            `;
        }
    }
    
    if (end < lastPage) {
        if (end < lastPage - 1) {
            paginationHtml += '<span class="px-1 text-xs text-gray-400">...</span>';
        }
        paginationHtml += `
            <a href="#" onclick="performGlobalSearch('${escapeForJs(searchTerm)}', ${lastPage}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">${lastPage}</a>
        `;
    }
    
    // Next button
    if (currentPage < lastPage) {
        paginationHtml += `
            <a href="#" onclick="performGlobalSearch('${escapeForJs(searchTerm)}', ${currentPage + 1}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
    } else {
        paginationHtml += `
            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
        `;
    }
    
    paginationContainer.html(paginationHtml);
}

// Function to reset to original view
function resetToOriginalView() {
    window.location.href = window.MarketManagementRoutes.index;
}

// Function to clear search
window.clearSearch = function() {
    $('#search-input').val('');
    resetToOriginalView();
}

// ========================================
// FUNCIONES PARA EDICIÓN DE MERCADOS
// ========================================

// Function to open edit modal
window.openEditModal = function(marketId, currentName) {
    $('#edit-market-id').val(marketId);
    $('#edit-market-original-name').val(currentName); // Guardar el nombre original
    $('#edit-market-name').val(currentName);
    $('#edit-market-note').val(''); // Limpiar la nota
    $('#edit-modal').removeClass('hidden');
    
    // Agregar validación en tiempo real para la nota
    $('#edit-market-note').on('input', function() {
        const note = $(this).val().trim();
        const submitBtn = $('#edit-submit-btn');
        const errorMsg = $('#edit-note-error');
        
        if (!note) {
            $(this).addClass('border-red-500').removeClass('border-primary');
            submitBtn.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
            errorMsg.removeClass('hidden');
        } else {
            $(this).removeClass('border-red-500').addClass('border-primary');
            submitBtn.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
            errorMsg.addClass('hidden');
        }
    });
    
    // Validación inicial - deshabilitar botón hasta que se ingrese una nota
    $('#edit-submit-btn').addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
}

// Function to close edit modal
window.closeEditModal = function() {
    $('#edit-modal').addClass('hidden');
    $('#edit-market-name').val('');
    $('#edit-market-id').val('');
}

// Function to open edit confirmation modal
window.openEditConfirmationModal = function() {
    const marketId = $('#edit-market-id').val();
    const currentName = $('#edit-market-original-name').val(); // Usar el nombre original guardado
    const newName = $('#edit-market-name').val().trim();
    const note = $('#edit-market-note').val().trim();
    
    if (!newName) {
        showToast('El nombre del mercado no puede estar vacío', 'error');
        return;
    }
    
    if (newName === currentName) {
        showToast('El nuevo nombre debe ser diferente al actual', 'warning');
        return;
    }
    
    if (!note) {
        showToast('La nota es obligatoria', 'error');
        return;
    }
    
    $('#confirm-edit-current-name').text(currentName);
    $('#confirm-edit-new-name').text(newName);
    $('#edit-confirmation-modal').removeClass('hidden');
}

// Function to close edit confirmation modal
window.closeEditConfirmationModal = function() {
    $('#edit-confirmation-modal').addClass('hidden');
}

// Function to confirm edit market
window.confirmEditMarket = function() {
    const marketId = $('#edit-market-id').val();
    const newName = $('#edit-market-name').val().trim();
    const note = $('#edit-market-note').val().trim();
    
    if (!newName) {
        showToast('El nombre del mercado no puede estar vacío', 'error');
        return;
    }
    
    if (!note) {
        showToast('La nota es obligatoria', 'error');
        return;
    }
    
    // Show loading state
    const confirmButton = $('#confirm-edit-btn');
    const btnText = confirmButton.find('.btn-text');
    const btnLoading = confirmButton.find('.btn-loading');
    
    btnText.addClass('hidden');
    btnLoading.removeClass('hidden');
    confirmButton.prop('disabled', true);
    
    // Send AJAX request
    $.ajax({
        url: window.MarketManagementRoutes.update,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json'
        },
        data: JSON.stringify({
            market_id: marketId,
            market_name: newName,
            market_note: $('#edit-market-note').val().trim()
        }),
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                closeEditConfirmationModal();
                closeEditModal();
                
                // Reload page to show changes
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Error al actualizar el mercado', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error al actualizar el mercado';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showToast(errorMessage, 'error');
        },
        complete: function() {
            // Restore button state
            btnText.removeClass('hidden');
            btnLoading.addClass('hidden');
            confirmButton.prop('disabled', false);
        }
    });
}

// ========================================
// FUNCIONES DE UTILIDAD
// ========================================

// Function to show error message
function showError(message) {
    showToast(message, 'error');
}

// Function to show toast notification
window.showToast = function(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : 
                  type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' :
                  type === 'warning' ? '<i class="fas fa-exclamation-triangle"></i>' :
                  '<i class="fas fa-info-circle"></i>'}
            </div>
            <div class="ml-3">
                <p class="text-sm">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button type="button" class="inline-flex text-white hover:opacity-75 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // Agregar al contenedor
    const container = document.getElementById('toast-container');
    if (container) {
        container.appendChild(toast);
    } else {
        // Si no existe el contenedor, crear uno
        const newContainer = document.createElement('div');
        newContainer.id = 'toast-container';
        newContainer.className = 'fixed top-20 right-4 z-50';
        document.body.appendChild(newContainer);
        newContainer.appendChild(toast);
    }
    
    // Mostrar el toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Auto remover después de 5 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 5000);
}

// ========================================
// DOCUMENT READY
// ========================================

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
    
    // Search on Enter key
    $('#search-input').on('keypress', function(e) {
        if (e.key === 'Enter') {
            clearTimeout(searchTimeout);
            const searchTerm = $(this).val().trim();
            currentSearch = searchTerm;
            
            if (searchTerm === '') {
                resetToOriginalView();
            } else {
                performGlobalSearch(searchTerm);
            }
        }
    });

    // Close modal when clicking outside
    $(document).on('click', '#edit-modal, #edit-confirmation-modal', function(e) {
        if (e.target === this) {
            if (this.id === 'edit-modal') {
                closeEditModal();
            } else if (this.id === 'edit-confirmation-modal') {
                closeEditConfirmationModal();
            }
        }
    });

    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!$('#edit-confirmation-modal').hasClass('hidden')) {
                closeEditConfirmationModal();
            } else if (!$('#edit-modal').hasClass('hidden')) {
                closeEditModal();
            }
        }
    });

    
});

