// Market Management JavaScript Functions - CLEAN VERSION
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
    // This will be handled by updateTableWithResults
}

// Function to update table with search results
function updateTableWithResults(markets) {
    const tbody = $('table tbody');
    
    if (markets.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
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
    // Escape values for safe HTML usage
    const escapedMarca = escapeHtml(market.marca || '');
    const escapedMercado = escapeHtml(market.mercado || '');
    const escapedCodigo = escapeHtml(market.codigoPresentacion || '');
    
    // Generate status badge with icon
    const statusBadge = generateStateBadge(market.estado);
    
    // Generate action buttons based on market status
    const actionButtons = generateActionButtons(market);
    
    return `
        <tr class="hover:bg-gray-50 market-row" data-market-id="${market.idMercado}" data-product-code="${escapedCodigo}">
            <!-- Marca (Descripción Producto) -->
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-tags text-blue-600 text-xs"></i>
                    </div>
                    <div>
                        <button onclick="redirectToProductsWithBrand('${escapedMarca}')" 
                                class="font-medium text-gray-900 hover:text-blue-600 hover:underline transition-colors cursor-pointer"
                                title="Ver productos de esta marca">
                            ${escapedMarca}
                        </button>
                        <div class="text-sm text-gray-500">Código: ${escapedCodigo}</div>
                    </div>
                </div>
            </td>
            <!-- Mercado Asignado -->
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center mr-2">
                        <i class="fas fa-store text-red-600 text-xs"></i>
                    </div>
                    <button onclick="redirectToProductsWithMarket('${escapedMercado}')" 
                            class="font-medium text-red-600 hover:text-red-800 hover:underline transition-colors cursor-pointer"
                            title="Ver productos de este mercado">
                        ${escapedMercado}
                    </button>
                </div>
            </td>
            <!-- Estado -->
            <td class="px-6 py-4 whitespace-nowrap">
                ${statusBadge}
            </td>
            <!-- Acciones -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
    
    // Botón de Editar Mercado (solo este botón debe aparecer)
    if (market.idMercado && !isNaN(market.idMercado)) {
        // Escapar el nombre del mercado de manera segura para JavaScript
        const safeMarketName = escapeForJs(market.mercado || market.marca || '');
        
        // Botón de Editar Mercado
        buttons += `
            <button onclick="openEditModal(${market.idMercado}, '${safeMarketName}')"
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                    title="Editar nombre del mercado">
                <i class="fas fa-edit mr-1"></i>
                Editar Mercado
            </button>
        `;
    }
    
    return buttons;
}

// Function to update results info
function updateResultsInfo(data, searchTerm) {
    const resultsInfo = $('.results-info');
    if (resultsInfo.length) {
        resultsInfo.html(`
            <span class="text-sm text-gray-600">
                Mostrando ${data.data.length} de ${data.total} resultados
            </span>
        `);
    }
}

// Function to update pagination
function updatePagination(data, searchTerm) {
    const paginationContainer = $('.pagination-container');
    if (!paginationContainer.length || data.last_page <= 1) {
        return;
    }
    
    let paginationHTML = '<nav class="flex items-center justify-between">';
    paginationHTML += '<div class="flex-1 flex justify-between sm:hidden">';
    
    // Mobile pagination
    if (data.current_page > 1) {
        paginationHTML += `<a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page - 1}); return false;" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Anterior</a>`;
    }
    if (data.current_page < data.last_page) {
        paginationHTML += `<a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page + 1}); return false;" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Siguiente</a>`;
    }
    
    paginationHTML += '</div>';
    paginationHTML += '<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">';
    
    // Desktop pagination
    paginationHTML += '<div>';
    paginationHTML += `<p class="text-sm text-gray-700">Mostrando <span class="font-medium">${(data.current_page - 1) * data.per_page + 1}</span> a <span class="font-medium">${Math.min(data.current_page * data.per_page, data.total)}</span> de <span class="font-medium">${data.total}</span> resultados</p>`;
    paginationHTML += '</div>';
    
    paginationHTML += '<div>';
    paginationHTML += '<nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">';
    
    // Previous button
    if (data.current_page > 1) {
        paginationHTML += `<a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page - 1}); return false;" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"><i class="fas fa-chevron-left"></i></a>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, data.current_page - 2);
    const endPage = Math.min(data.last_page, data.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === data.current_page) {
            paginationHTML += `<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">${i}</span>`;
        } else {
            paginationHTML += `<a href="#" onclick="performGlobalSearch('${searchTerm}', ${i}); return false;" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">${i}</a>`;
        }
    }
    
    // Next button
    if (data.current_page < data.last_page) {
        paginationHTML += `<a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page + 1}); return false;" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"><i class="fas fa-chevron-right"></i></a>`;
    }
    
    paginationHTML += '</nav>';
    paginationHTML += '</div>';
    paginationHTML += '</div>';
    paginationHTML += '</nav>';
    
    paginationContainer.html(paginationHTML);
}

// Function to show error message
function showError(message) {
    // You can implement a toast notification here
    console.error(message);
    alert(message);
}

// Function to reset to original view
function resetToOriginalView() {
    // Use AJAX to load original view instead of redirecting
    performGlobalSearch('', 1);
}

// Function to clear search (AJAX version)
function clearSearch() {
    // Clear search input
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Reset to original view via AJAX
    performGlobalSearch('', 1);
}

// ========================================
// FUNCIONES DE MODALES
// ========================================

// Open edit modal - VANILLA JS
function openEditModal(marketId, marketName) {
    const editMarketId = document.getElementById('edit-market-id');
    const editMarketName = document.getElementById('edit-market-name');
    const editMarketNote = document.getElementById('edit-market-note');
    const editModal = document.getElementById('edit-modal');
    
    if (editMarketId) editMarketId.value = marketId;
    if (editMarketName) {
        editMarketName.value = marketName;
        editMarketName.setAttribute('data-original', marketName);
    }
    if (editMarketNote) editMarketNote.value = '';
    if (editModal) editModal.classList.remove('hidden');
    if (editMarketName) editMarketName.focus();
}

// Close edit modal - VANILLA JS
function closeEditModal() {
    const editModal = document.getElementById('edit-modal');
    if (editModal) editModal.classList.add('hidden');
}

// Open edit confirmation modal
function openEditConfirmationModal() {
    const editModal = document.getElementById('edit-modal');
    const confirmationModal = document.getElementById('edit-confirmation-modal');
    
    // Get form elements
    const editMarketName = document.getElementById('edit-market-name');
    const editMarketNote = document.getElementById('edit-market-note');
    const currentNameElement = document.getElementById('confirm-edit-current-name');
    const newNameElement = document.getElementById('confirm-edit-new-name');
    
    // Validate required fields
    const marketName = editMarketName ? editMarketName.value.trim() : '';
    const marketNote = editMarketNote ? editMarketNote.value.trim() : '';
    
    // Check if name is empty
    if (!marketName) {
        showToast('El nombre del mercado es obligatorio', 'error');
        if (editMarketName) editMarketName.focus();
        return;
    }
    
    // Check if note is empty (obligatory)
    if (!marketNote) {
        showToast('La nota es obligatoria para realizar cambios', 'error');
        if (editMarketNote) editMarketNote.focus();
        return;
    }
    
    if (editMarketName) {
        const originalName = editMarketName.getAttribute('data-original') || '';
        const newName = editMarketName.value || '';
        
        // Fill the confirmation modal with the values
        if (currentNameElement) currentNameElement.textContent = originalName || '-';
        if (newNameElement) newNameElement.textContent = newName || '-';
    }
    
    if (editModal) editModal.classList.add('hidden');
    if (confirmationModal) confirmationModal.classList.remove('hidden');
}

// Close edit confirmation modal
function closeEditConfirmationModal() {
    const confirmationModal = document.getElementById('edit-confirmation-modal');
    if (confirmationModal) confirmationModal.classList.add('hidden');
}

// Confirm edit market function
function confirmEditMarket() {
    const editMarketId = document.getElementById('edit-market-id');
    const editMarketName = document.getElementById('edit-market-name');
    const editMarketNote = document.getElementById('edit-market-note');
    const confirmBtn = document.getElementById('confirm-edit-btn');
    const btnText = confirmBtn.querySelector('.btn-text');
    const btnLoading = confirmBtn.querySelector('.btn-loading');
    
    if (!editMarketId || !editMarketName) {
        showToast('Error: Datos del mercado no encontrados', 'error');
        return;
    }
    
    const marketId = editMarketId.value;
    const marketName = editMarketName.value.trim();
    const marketNote = editMarketNote ? editMarketNote.value.trim() : '';
    
    if (!marketName) {
        showToast('El nombre del mercado es obligatorio', 'error');
        return;
    }
    
    // Show loading state
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    confirmBtn.disabled = true;
    
    // Prepare data
    const formData = new FormData();
    formData.append('market_id', marketId);
    formData.append('market_name', marketName);
    formData.append('market_note', marketNote);
    formData.append('_token', window.csrfToken);
    formData.append('_method', 'PUT'); // Laravel method spoofing for PUT requests
    
    // Send AJAX request
    fetch(window.MarketManagementRoutes.update, {
        method: 'POST', // Laravel method spoofing requires POST
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeEditConfirmationModal();
            closeEditModal();
            
            // Reload the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Error al actualizar el mercado', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error de conexión al actualizar el mercado', 'error');
    })
    .finally(() => {
        // Hide loading state
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        confirmBtn.disabled = false;
    });
}

// ========================================
// FUNCIONES DE TOAST
// ========================================

// Toast function mejorada
function showToast(message, type = 'info') {
    // Crear el toast con JavaScript puro
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Aplicar colores según el tipo usando el branding primario
    if (type === 'success') {
        toast.style.backgroundColor = 'var(--primary)';
    } else if (type === 'error') {
        toast.style.backgroundColor = '#ef4444';
    } else if (type === 'warning') {
        toast.style.backgroundColor = '#f59e0b';
    } else {
        toast.style.backgroundColor = 'var(--secondary)';
    }
    
    // Contenido del toast
    const icon = type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
    
    toast.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas ${icon} mr-2"></i>
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
