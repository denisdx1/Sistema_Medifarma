// Get routes and token from meta tags
function getMetaContent(name) {
    const metaTag = document.querySelector(`meta[name="${name}"]`);
    return metaTag ? metaTag.getAttribute('content') : null;
}

// Variables globales para el modal y búsqueda
let currentMarketId = null;
let currentMarketName = null;
let searchTimeout = null;
let currentSearch = '';
let currentFilter = 'pending';

// Variables que se inicializarán cuando el DOM esté listo
let MarketAdminRoutes = {};
let csrfToken = '';

// Search functionality - Global search with AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar variables con datos del DOM
    currentFilter = getMetaContent('current-filter') || 'pending';
    
    // Define routes for JavaScript
    MarketAdminRoutes = {
        approve: getMetaContent('approve-route'),
        deny: getMetaContent('deny-route'),
        changeStatus: getMetaContent('change-status-route'),
        search: getMetaContent('search-route')
    };
    
    csrfToken = getMetaContent('csrf-token');
    
    // Para compatibilidad global
    window.MarketAdminRoutes = MarketAdminRoutes;
    window.csrfToken = csrfToken;
    
    console.log('Market Administration JS loaded');
    console.log('Routes:', MarketAdminRoutes);
    console.log('CSRF Token:', csrfToken);
    console.log('Current Filter:', currentFilter);
    
    const searchInput = document.getElementById('search-input');
    const clearButton = document.getElementById('clear-search');
    const marketsTableBody = document.getElementById('markets-tbody');
    const paginationContainer = document.querySelector('.pagination-container');
    
    console.log('Elements found:', {
        searchInput: !!searchInput,
        clearButton: !!clearButton,
        marketsTableBody: !!marketsTableBody,
        paginationContainer: !!paginationContainer
    });
    
    if (searchInput) {
        // Global search with debounce
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            currentSearch = searchTerm;
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Show/hide clear button
            if (searchTerm.length > 0) {
                clearButton?.classList.remove('hidden');
            } else {
                clearButton?.classList.add('hidden');
            }
            
            // Debounce search (wait 500ms after user stops typing)
            searchTimeout = setTimeout(() => {
                performGlobalSearch(searchTerm);
            }, 500);
        });
        
        // Clear search functionality
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                currentSearch = '';
                this.classList.add('hidden');
                performGlobalSearch(''); // Load normal filtered results
            });
        }
    }
    
    // Function to perform global search via AJAX
    function performGlobalSearch(searchTerm) {
        console.log('Performing global search:', searchTerm, 'Filter:', currentFilter);
        
        const searchUrl = MarketAdminRoutes.search;
        console.log('Search URL:', searchUrl);
        
        if (!searchUrl) {
            console.error('Search URL not found');
            showError('Error: Ruta de búsqueda no configurada');
            return;
        }
        
        const params = new URLSearchParams({
            search: searchTerm,
            filter: currentFilter,
            page: 1
        });
        
        console.log('Search params:', params.toString());
        
        // Show loading state
        showLoadingState();
        
        fetch(`${searchUrl}?${params}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Search response data:', data);
            if (data.success) {
                updateTableWithResults(data.data);
                updatePagination(data);
                updateResultsInfo(data, searchTerm);
            } else {
                showError('Error en la búsqueda: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showError('Error de conexión durante la búsqueda');
        })
        .finally(() => {
            hideLoadingState();
        });
    }
    
    // Function to show loading state
    function showLoadingState() {
        if (marketsTableBody) {
            marketsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center">
                        <div class="flex items-center justify-center">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600 mr-3"></div>
                            <span class="text-gray-600">Buscando...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
    
    // Function to hide loading state
    function hideLoadingState() {
        // This will be handled by updateTableWithResults
    }
    
    // Function to update table with search results
    function updateTableWithResults(markets) {
        if (!marketsTableBody) return;
        
        if (markets.length === 0) {
            marketsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-search text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg">No se encontraron resultados</p>
                            <p class="text-sm">Intenta con otros términos de búsqueda</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        let tableHTML = '';
        markets.forEach(market => {
            tableHTML += generateMarketRow(market);
        });
        marketsTableBody.innerHTML = tableHTML;
        
        // Re-attach event listeners to new rows
        attachRowEventListeners();
    }
    
    // Function to generate market row HTML
    function generateMarketRow(market) {
        const statusClass = getStatusClass(market.solicitud);
        const stateClass = getStateClass(market.estado);
        
        return `
            <tr class="market-row hover:bg-gray-50 transition-colors duration-200" data-market-id="${market.idMercado}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-store text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 market-name">${market.mercado}</div>
                            <div class="text-sm text-gray-500">ID: ${market.idMercado}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium market-request ${statusClass}">
                        ${market.solicitud}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium market-status ${stateClass}">
                        ${market.estado}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${formatDate(market.fechaRegistro)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                        ${generateActionButtons(market)}
                    </div>
                </td>
            </tr>
        `;
    }
    
    // Helper functions for styling
    function getStatusClass(status) {
        switch(status) {
            case 'APROBADO': return 'bg-green-100 text-green-800';
            case 'DENEGADO': return 'bg-red-100 text-red-800';
            case 'ESPERA': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getStateClass(state) {
        switch(state) {
            case 'ACTIVO': return 'bg-green-100 text-green-800';
            case 'INACTIVO': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    function generateActionButtons(market) {
        let buttons = '';
        
        if (market.solicitud === 'ESPERA') {
            buttons += `
                <button onclick="approveMarket(${market.idMercado}, '${market.mercado}')" 
                        class="text-green-600 hover:text-green-900 transition-colors duration-200 p-2 rounded hover:bg-green-50">
                    <i class="fas fa-check" title="Aprobar"></i>
                </button>
                <button onclick="denyMarket(${market.idMercado}, '${market.mercado}')" 
                        class="text-red-600 hover:text-red-900 transition-colors duration-200 p-2 rounded hover:bg-red-50">
                    <i class="fas fa-times" title="Denegar"></i>
                </button>
            `;
        }
        
        buttons += `
            <button onclick="changeStatus(${market.idMercado}, '${market.mercado}', '${market.estado}')" 
                    class="text-purple-600 hover:text-purple-900 transition-colors duration-200 p-2 rounded hover:bg-purple-50">
                <i class="fas fa-cog" title="Cambiar Estado"></i>
            </button>
        `;
        
        return buttons;
    }
    
    // Function to re-attach event listeners
    function attachRowEventListeners() {
        // This function would re-attach any specific row event listeners if needed
        // For now, the onclick handlers are inline in the HTML
    }
    
    // Function to update pagination (placeholder)
    function updatePagination(data) {
        // Implementation for pagination update if needed
        // This would update the pagination links based on search results
    }
    
    // Function to update results info
    function updateResultsInfo(data, searchTerm) {
        const resultsInfo = document.querySelector('.results-info');
        if (resultsInfo) {
            if (searchTerm) {
                resultsInfo.textContent = `Mostrando ${data.data.length} de ${data.total} resultados para "${searchTerm}"`;
            } else {
                resultsInfo.textContent = `Mostrando ${data.data.length} de ${data.total} mercados`;
            }
        }
    }
    
    // Function to show error messages
    function showError(message) {
        console.error('Error:', message);
        // Simple alert for now, can be improved with toast notifications
        alert(message);
    }
    
    function showNoResultsMessage(show) {
        let noResultsRow = document.getElementById('no-results-message');
        
        if (show && !noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'no-results-message';
            noResultsRow.innerHTML = `
                <td colspan="6" class="px-6 py-16 text-center">
                    <div class="max-w-md mx-auto">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-search text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No se encontraron resultados</h3>
                        <p class="text-gray-600">No hay mercados que coincidan con tu búsqueda. Intenta con otros términos.</p>
                        <button onclick="document.getElementById('search-input').value = ''; document.getElementById('search-input').dispatchEvent(new Event('input')); document.getElementById('search-input').focus();" 
                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Limpiar búsqueda
                        </button>
                    </div>
                </td>
            `;
            document.getElementById('markets-tbody').appendChild(noResultsRow);
        } else if (!show && noResultsRow) {
            noResultsRow.remove();
        }
    }
});

// Open approve modal
function openApproveModal(marketId, marketName) {
    currentMarketId = marketId;
    currentMarketName = marketName;
    document.getElementById('approve-market-name').textContent = marketName;
    document.getElementById('approve-modal').classList.remove('hidden');
}

// Close approve modal
function closeApproveModal() {
    document.getElementById('approve-modal').classList.add('hidden');
    currentMarketId = null;
    currentMarketName = null;
}

// Open deny modal
function openDenyModal(marketId, marketName) {
    currentMarketId = marketId;
    currentMarketName = marketName;
    document.getElementById('deny-market-name').textContent = marketName;
    document.getElementById('deny-modal').classList.remove('hidden');
}

// Close deny modal
function closeDenyModal() {
    document.getElementById('deny-modal').classList.add('hidden');
    currentMarketId = null;
    currentMarketName = null;
}

// Confirm approval
function confirmApproval() {
    if (!currentMarketId) return;
    
    const submitBtn = document.getElementById('confirm-approve-btn');
    submitBtn.disabled = true;
    submitBtn.querySelector('.btn-text').style.display = 'none';
    submitBtn.querySelector('.btn-loading').style.display = 'inline';
    
    fetch(MarketAdminRoutes.approve, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            market_id: currentMarketId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeApproveModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error al aprobar mercado', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.querySelector('.btn-text').style.display = 'inline';
        submitBtn.querySelector('.btn-loading').style.display = 'none';
    });
}

// Confirm denial
function confirmDenial() {
    if (!currentMarketId) return;
    
    const submitBtn = document.getElementById('confirm-deny-btn');
    submitBtn.disabled = true;
    submitBtn.querySelector('.btn-text').style.display = 'none';
    submitBtn.querySelector('.btn-loading').style.display = 'inline';
    
    fetch(MarketAdminRoutes.deny, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            market_id: currentMarketId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeDenyModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error al denegar mercado', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.querySelector('.btn-text').style.display = 'inline';
        submitBtn.querySelector('.btn-loading').style.display = 'none';
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    const toastId = 'toast-' + Date.now();
    
    const iconMap = {
        success: 'fas fa-check-circle text-green-500',
        error: 'fas fa-exclamation-circle text-red-500',
        warning: 'fas fa-exclamation-triangle text-yellow-500',
        info: 'fas fa-info-circle text-blue-500'
    };
    
    const bgMap = {
        success: 'bg-green-50 border-green-200',
        error: 'bg-red-50 border-red-200',
        warning: 'bg-yellow-50 border-yellow-200',
        info: 'bg-blue-50 border-blue-200'
    };
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast transform translate-x-full opacity-0 mb-4 p-4 rounded-lg border ${bgMap[type]} flex items-center justify-between min-w-80 max-w-md`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="${iconMap[type]} mr-3 text-lg"></i>
            <span class="text-gray-800 font-medium">${message}</span>
        </div>
        <button onclick="removeToast('${toastId}')" class="text-gray-500 hover:text-gray-700 ml-4">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full', 'opacity-0');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeToast(toastId);
    }, 5000);
}

function removeToast(toastId) {
    const toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
}

// Close modals on ESC key
document.addEventListener('keyup', function(e) {
    if (e.key === "Escape") {
        closeApproveModal();
        closeDenyModal();
    }
});

// Close modals when clicking outside
document.getElementById('approve-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

document.getElementById('deny-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDenyModal();
    }
});