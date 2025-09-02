// Funciones para acciones masivas de productos
// Quitar y cambiar mercado masivamente

// Verificar que jQuery esté disponible
if (typeof $ === 'undefined') {
    console.error('jQuery no está disponible. Este script requiere jQuery.');
}

// Variables globales
let selectedProducts = [];
let bulkRemoveNote = '';
let bulkChangeNote = '';
let bulkChangeSelectedMarket = null;
let bulkChangeMarketSearchTimeout = null;
let isInputFocused = false;

// Función para abrir modal de quitar mercado masivamente
window.openBulkRemoveModal = function() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        showWarningNotification('Debes seleccionar al menos un producto');
        return;
    }

    // Obtener productos seleccionados
    selectedProducts = Array.from(selectedCheckboxes).map(checkbox => {
        return {
            id: checkbox.dataset.productCode,
            name: checkbox.dataset.productName || 'Producto',
            code: checkbox.dataset.productCode || 'Código',
            market: checkbox.dataset.market || 'Sin mercado'
        };
    });

    // Actualizar información en la modal
    document.getElementById('bulk-remove-count').textContent = `${selectedProducts.length} producto${selectedProducts.length > 1 ? 's' : ''}`;
    
    // Limpiar y llenar lista de productos
    const productsList = document.getElementById('bulk-remove-products-list');
    productsList.innerHTML = selectedProducts.map(product => `
        <div class="text-xs text-gray-700 py-1 border-b border-gray-100 last:border-b-0">
            <div class="font-medium">${product.name}</div>
            <div class="text-gray-500">${product.code} - ${product.market}</div>
        </div>
    `).join('');

    // Limpiar nota y errores
    document.getElementById('bulk-remove-note').value = '';
    document.getElementById('bulk-remove-note-error').classList.add('hidden');
    
    // Deshabilitar botón de confirmación inicialmente
    const confirmBtn = document.getElementById('bulk-remove-confirm-btn');
    confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
    confirmBtn.disabled = true;

    // Mostrar modal
    document.getElementById('bulk-remove-modal').classList.remove('hidden');
    
    // Configurar validación de nota
    setupBulkRemoveNoteValidation();
};

// Función para cerrar modal de quitar mercado masivamente
window.closeBulkRemoveModal = function() {
    document.getElementById('bulk-remove-modal').classList.add('hidden');
    selectedProducts = [];
    bulkRemoveNote = '';
};

// Función para mostrar modal de confirmación de quitar mercado
window.showBulkRemoveConfirmationModal = function() {
    const note = document.getElementById('bulk-remove-note').value.trim();
    
    if (!note) {
        showErrorNotification('La nota es obligatoria');
        return;
    }

    // Actualizar información en la modal de confirmación
    document.getElementById('confirm-bulk-remove-count').textContent = selectedProducts.length;
    document.getElementById('confirm-bulk-remove-note').textContent = note;
    
    // Ocultar modal principal y mostrar confirmación
    document.getElementById('bulk-remove-modal').classList.add('hidden');
    document.getElementById('bulk-remove-confirmation-modal').classList.remove('hidden');
};

// Función para proceder con la quita masiva de mercado
window.proceedWithBulkRemove = function() {
    const note = document.getElementById('bulk-remove-note').value.trim();
    const productIds = selectedProducts.map(p => p.id);
    
    // Mostrar estado de carga
    const confirmBtn = document.querySelector('#bulk-remove-confirmation-modal .btn-loading');
    const btnText = document.querySelector('#bulk-remove-confirmation-modal .btn-text');
    confirmBtn.classList.remove('hidden');
    btnText.classList.add('hidden');
    
    // Llamada AJAX para quitar productos del mercado
    fetch('/productos/bulk-remove-market', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_ids: productIds,
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessNotification(data.message || 'Productos quitados del mercado exitosamente');
            closeBulkRemoveConfirmationModal();
            closeBulkRemoveModal();
            
            // Recargar productos
            if (typeof loadProducts === 'function') {
                loadProducts();
            }
            
            // Limpiar selecciones
            clearProductSelections();
        } else {
            showErrorNotification(data.message || 'Error al quitar productos del mercado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorNotification('Error de conexión al quitar productos del mercado');
    })
    .finally(() => {
        // Restaurar botón
        confirmBtn.classList.add('hidden');
        btnText.classList.remove('hidden');
    });
};

// Función para cerrar modal de confirmación de quitar mercado
window.closeBulkRemoveConfirmationModal = function() {
    document.getElementById('bulk-remove-confirmation-modal').classList.add('hidden');
    // Volver a mostrar modal principal
    document.getElementById('bulk-remove-modal').classList.remove('hidden');
};

// Función para mostrar modal de confirmación de cambiar mercado
window.showBulkChangeConfirmationModal = function() {
    const note = document.getElementById('bulk-change-note').value.trim();
    const marketId = document.getElementById('bulk-change-selected-market-id').value;
    
    if (!note) {
        showErrorNotification('La nota es obligatoria');
        return;
    }
    
    if (!marketId) {
        showErrorNotification('Debe seleccionar un mercado de destino');
        return;
    }

    // Actualizar información en la modal de confirmación
    document.getElementById('confirm-bulk-change-count').textContent = selectedProducts.length;
    document.getElementById('confirm-bulk-change-note').textContent = note;
    document.getElementById('confirm-bulk-change-market').textContent = document.getElementById('bulk-change-selected-market-name').textContent;
    
    // Ocultar modal principal y mostrar confirmación
    document.getElementById('bulk-change-modal').classList.add('hidden');
    document.getElementById('bulk-change-confirmation-modal').classList.remove('hidden');
};

// Función para cerrar modal de confirmación de cambiar mercado
window.closeBulkChangeConfirmationModal = function() {
    document.getElementById('bulk-change-confirmation-modal').classList.add('hidden');
    // Volver a mostrar modal principal
    document.getElementById('bulk-change-modal').classList.remove('hidden');
};

// Función para proceder con el cambio masivo de mercado
window.proceedWithBulkChange = function() {
    const note = document.getElementById('bulk-change-note').value.trim();
    const marketId = document.getElementById('bulk-change-selected-market-id').value;
    const productIds = selectedProducts.map(p => p.id);
    
    if (!note) {
        showErrorNotification('La nota es obligatoria');
        return;
    }
    
    if (!marketId) {
        showErrorNotification('Debe seleccionar un mercado de destino');
        return;
    }
    
    // Mostrar estado de carga
    const confirmBtn = document.querySelector('#bulk-change-confirmation-modal .btn-loading');
    const btnText = document.querySelector('#bulk-change-confirmation-modal .btn-text');
    confirmBtn.classList.remove('hidden');
    btnText.classList.add('hidden');
    
    // Log para debugging
    console.log('Enviando datos para cambio masivo:', {
        product_ids: productIds,
        market_id: marketId,
        market_id_type: typeof marketId,
        market_id_parsed: parseInt(marketId),
        note: note
    });
    
    // Llamada AJAX para cambiar mercado de productos
    fetch('/productos/bulk-change-market', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            product_ids: productIds,
            market_id: parseInt(marketId),
            note: note
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessNotification(data.message || 'Productos cambiados de mercado exitosamente');
            closeBulkChangeConfirmationModal();
            closeBulkChangeModal();
            
            // Recargar productos
            if (typeof loadProducts === 'function') {
                loadProducts();
            }
            
            // Limpiar selecciones
            clearProductSelections();
        } else {
            showErrorNotification(data.message || 'Error al cambiar mercado de productos');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorNotification('Error de conexión al cambiar mercado de productos');
    })
    .finally(() => {
        // Restaurar botón
        confirmBtn.classList.add('hidden');
        btnText.classList.remove('hidden');
    });
};

// Función para abrir modal de cambiar mercado masivamente
window.openBulkChangeModal = function() {
    const selectedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        showWarningNotification('Debes seleccionar al menos un producto');
        return;
    }

    // Obtener productos seleccionados
    selectedProducts = Array.from(selectedCheckboxes).map(checkbox => {
        return {
            id: checkbox.dataset.productCode,
            name: checkbox.dataset.productName || 'Producto',
            code: checkbox.dataset.productCode || 'Código',
            market: checkbox.dataset.market || 'Sin mercado'
        };
    });

    // Actualizar información en la modal
    document.getElementById('bulk-change-count').textContent = `${selectedProducts.length} producto${selectedProducts.length > 1 ? 's' : ''}`;
    
    // Limpiar y llenar lista de productos
    const productsList = document.getElementById('bulk-change-products-list');
    productsList.innerHTML = selectedProducts.map(product => `
        <div class="text-xs text-gray-700 py-1 border-b border-gray-100 last:border-b-0">
            <div class="font-medium">${product.name}</div>
            <div class="text-gray-500">${product.code} - ${product.market}</div>
        </div>
    `).join('');

    // Limpiar selección de mercado y nota
    clearBulkChangeSelectedMarket();
    document.getElementById('bulk-change-note').value = '';
    document.getElementById('bulk-change-note-error').classList.add('hidden');
    
    // Limpiar búsqueda previa
    const searchInput = document.getElementById('bulk-change-market-search');
    if (searchInput) searchInput.value = '';
    
    // Deshabilitar botón de confirmación inicialmente
    const confirmBtn = document.getElementById('bulk-change-confirm-btn');
    confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
    confirmBtn.disabled = true;

    // Mostrar modal
    document.getElementById('bulk-change-modal').classList.remove('hidden');
    
    // Configurar validación de nota y búsqueda de mercados
    setupBulkChangeNoteValidation();
    setupBulkChangeMarketSearch();
    
    // Resetear estado de focus
    isInputFocused = false;
    
    // Cargar mercados disponibles
    if (typeof loadBulkChangeAvailableMarkets === 'function') {
        loadBulkChangeAvailableMarkets();
    }
};

// Función para cerrar modal de cambiar mercado masivamente
window.closeBulkChangeModal = function() {
    document.getElementById('bulk-change-modal').classList.add('hidden');
    selectedProducts = [];
    bulkChangeNote = '';
    bulkChangeSelectedMarket = null;
    
    // Limpiar selección de mercado
    clearBulkChangeSelectedMarket();
    
    // Resetear estado de focus
    isInputFocused = false;
};

// Función para limpiar selección de mercado en cambio masivo
window.clearBulkChangeSelectedMarket = function() {
    document.getElementById('bulk-change-selected-market').classList.add('hidden');
    document.getElementById('bulk-change-selected-market-id').value = '';
    document.getElementById('bulk-change-market-search').value = '';
    bulkChangeSelectedMarket = null;
    
    // Deshabilitar botón de confirmación
    const confirmBtn = document.getElementById('bulk-change-confirm-btn');
    confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
    confirmBtn.disabled = true;
    
    // Mostrar dropdown nuevamente si hay mercados
    if (window.bulkChangeAvailableMarkets && window.bulkChangeAvailableMarkets.length > 0) {
        document.getElementById('bulk-change-markets-dropdown').classList.remove('hidden');
        filterBulkChangeMarkets('');
    }
    
    // Resetear estado de focus
    isInputFocused = false;
};

// Configurar búsqueda de mercados para cambio masivo
function setupBulkChangeMarketSearch() {
    const searchInput = document.getElementById('bulk-change-market-search');
    const dropdown = document.getElementById('bulk-change-markets-dropdown');
    
    if (!searchInput || !dropdown) return;
    
    // Limpiar event listeners previos
    searchInput.removeEventListener('input', handleBulkChangeMarketInput);
    searchInput.removeEventListener('focus', handleBulkChangeMarketFocus);
    searchInput.removeEventListener('blur', handleBulkChangeMarketBlur);
    
    // Agregar nuevos event listeners
    searchInput.addEventListener('input', handleBulkChangeMarketInput);
    searchInput.addEventListener('focus', handleBulkChangeMarketFocus);
    searchInput.addEventListener('blur', handleBulkChangeMarketBlur);
    
    // Configurar click fuera del dropdown
    setupClickOutsideHandler();
    
    // Cargar mercados disponibles
    loadBulkChangeAvailableMarkets();
}

// Handler para input en búsqueda de mercados
function handleBulkChangeMarketInput(e) {
    const searchTerm = e.target.value.trim().toLowerCase();
    const dropdown = document.getElementById('bulk-change-markets-dropdown');
    
    // Mostrar dropdown si hay texto o mercados cargados
    if (searchTerm.length > 0 || window.bulkChangeAvailableMarkets.length > 0) {
        dropdown.classList.remove('hidden');
    }
    
    // Debounce search
    if (bulkChangeMarketSearchTimeout) {
        clearTimeout(bulkChangeMarketSearchTimeout);
    }
    
    bulkChangeMarketSearchTimeout = setTimeout(() => {
        filterBulkChangeMarkets(searchTerm);
    }, 300);
}

// Handler para focus en búsqueda de mercados
function handleBulkChangeMarketFocus(e) {
    const dropdown = document.getElementById('bulk-change-markets-dropdown');
    
    // Marcar que el input está enfocado
    isInputFocused = true;
    
    // Siempre mostrar el dropdown cuando se hace focus
    if (window.bulkChangeAvailableMarkets && window.bulkChangeAvailableMarkets.length > 0) {
        dropdown.classList.remove('hidden');
        filterBulkChangeMarkets(e.target.value.trim().toLowerCase());
    } else {
        // Si no hay mercados, cargarlos
        loadBulkChangeAvailableMarkets();
    }
}

// Handler para blur en búsqueda de mercados
function handleBulkChangeMarketBlur(e) {
    // Marcar que el input perdió el focus
    isInputFocused = false;
    
    // Ocultar el dropdown después de un pequeño delay
    setTimeout(() => {
        if (!isInputFocused) {
            const dropdown = document.getElementById('bulk-change-markets-dropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }
    }, 150);
}

// Función para filtrar mercados en cambio masivo
function filterBulkChangeMarkets(searchTerm) {
    const marketsList = document.getElementById('bulk-change-markets-list');
    const noResults = document.getElementById('bulk-change-dropdown-no-results');
    
    if (!marketsList || !noResults) return;
    
    // Verificar que hay mercados disponibles
    if (!window.bulkChangeAvailableMarkets || window.bulkChangeAvailableMarkets.length === 0) {
        return;
    }
    
    // Filtrar mercados
    const filteredMarkets = window.bulkChangeAvailableMarkets.filter(market => {
        return market.estado === 'ACTIVO' &&
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
            const marketItem = createBulkChangeMarketItem(market);
            marketsList.appendChild(marketItem);
        });
    }
}

// Crear elemento de mercado para la lista de cambio masivo
function createBulkChangeMarketItem(market) {
    const item = document.createElement('div');
    item.className = 'market-item p-3 hover:bg-secondary-50 cursor-pointer border-b border-gray-100 last:border-b-0';
    item.onclick = () => selectBulkChangeMarket(market.idMercado, market.mercado);
    
    item.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-secondary-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-store text-secondary-600 text-xs"></i>
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

// Función para cargar mercados disponibles para cambio masivo
function loadBulkChangeAvailableMarkets() {
    const dropdown = document.getElementById('bulk-change-markets-dropdown');
    const loadingState = document.getElementById('bulk-change-dropdown-loading');
    const marketsList = document.getElementById('bulk-change-markets-list');
    const noResults = document.getElementById('bulk-change-dropdown-no-results');
    
    if (!dropdown || !loadingState || !marketsList || !noResults) return;
    
    // Mostrar dropdown con loading
    dropdown.classList.remove('hidden');
    loadingState.classList.remove('hidden');
    marketsList.classList.add('hidden');
    noResults.classList.add('hidden');
    
    // Hacer petición AJAX para obtener mercados
    $.ajax({
        url: '/productos/markets/api',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.data) {
                window.bulkChangeAvailableMarkets = response.data;
                loadingState.classList.add('hidden');
                filterBulkChangeMarkets(''); // Mostrar todos los mercados inicialmente
                console.log(`Cargados ${window.bulkChangeAvailableMarkets.length} mercados disponibles`);
            } else {
                showBulkChangeDropdownError('Error al cargar mercados');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error loading markets:', {xhr, textStatus, errorThrown});
            showBulkChangeDropdownError('Error de conexión');
        }
    });
}

// Configurar click fuera del dropdown
function setupClickOutsideHandler() {
    // Remover handler previo si existe
    document.removeEventListener('click', handleClickOutside);
    
    // Agregar nuevo handler nativo
    document.addEventListener('click', handleClickOutside);
}

// Handler para click fuera del dropdown
function handleClickOutside(e) {
    const searchInput = document.getElementById('bulk-change-market-search');
    const dropdown = document.getElementById('bulk-change-markets-dropdown');
    
    if (!searchInput || !dropdown) return;
    
    // Solo procesar si el dropdown está visible
    if (dropdown.classList.contains('hidden')) {
        return;
    }
    
    // Verificar si el click es en el input o en el dropdown
    const isClickInInput = searchInput.contains(e.target);
    const isClickInDropdown = dropdown.contains(e.target);
    
    // Si el click es fuera del input y del dropdown, ocultar el dropdown
    if (!isClickInInput && !isClickInDropdown) {
        // Solo ocultar si el input no está enfocado
        if (!isInputFocused) {
            dropdown.classList.add('hidden');
        }
    }
}

// Mostrar error en el dropdown de cambio masivo
function showBulkChangeDropdownError(message) {
    const dropdown = document.getElementById('bulk-change-markets-dropdown');
    const loadingState = document.getElementById('bulk-change-dropdown-loading');
    const marketsList = document.getElementById('bulk-change-markets-list');
    const noResults = document.getElementById('bulk-change-dropdown-no-results');
    
    if (!dropdown || !loadingState || !marketsList || !noResults) return;
    
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

// Función para seleccionar mercado en cambio masivo
window.selectBulkChangeMarket = function(marketId, marketName) {
    bulkChangeSelectedMarket = { id: marketId, name: marketName };
    
    // Log para debugging
    console.log('selectBulkChangeMarket - Valores recibidos:', {
        marketId: marketId,
        marketId_type: typeof marketId,
        marketName: marketName
    });
    
    document.getElementById('bulk-change-selected-market-id').value = marketId;
    document.getElementById('bulk-change-selected-market-name').textContent = marketName;
    document.getElementById('bulk-change-selected-market').classList.remove('hidden');
    
    // Log para debugging - verificar que se guardó correctamente
    console.log('Campo oculto actualizado:', {
        field_value: document.getElementById('bulk-change-selected-market-id').value,
        field_type: typeof document.getElementById('bulk-change-selected-market-id').value
    });
    
    // Actualizar input y ocultar dropdown
    document.getElementById('bulk-change-market-search').value = marketName;
    document.getElementById('bulk-change-markets-dropdown').classList.add('hidden');
    
    // Verificar si se puede habilitar el botón de confirmación
    const note = document.getElementById('bulk-change-note').value.trim();
    const confirmBtn = document.getElementById('bulk-change-confirm-btn');
    
    if (note && marketId) {
        confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        confirmBtn.disabled = false;
    }
    
    // Resetear estado de focus
    isInputFocused = false;
    
    console.log('Market selected:', { id: marketId, name: marketName });
};

// Configurar validación de nota para quitar mercado masivamente
function setupBulkRemoveNoteValidation() {
    const noteField = document.getElementById('bulk-remove-note');
    const errorElement = document.getElementById('bulk-remove-note-error');
    const confirmBtn = document.getElementById('bulk-remove-confirm-btn');
    
    noteField.addEventListener('input', function() {
        const note = this.value.trim();
        
        if (!note) {
            this.classList.add('border-red-500');
            this.classList.remove('border-red-300');
            errorElement.classList.remove('hidden');
            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            confirmBtn.disabled = true;
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-red-300');
            errorElement.classList.add('hidden');
            confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            confirmBtn.disabled = false;
        }
    });
}

// Configurar validación de nota para cambiar mercado masivamente
function setupBulkChangeNoteValidation() {
    const noteField = document.getElementById('bulk-change-note');
    const errorElement = document.getElementById('bulk-change-note-error');
    const confirmBtn = document.getElementById('bulk-change-confirm-btn');
    
    noteField.addEventListener('input', function() {
        const note = this.value.trim();
        const marketId = document.getElementById('bulk-change-selected-market-id').value;
        
        if (!note || !marketId) {
            this.classList.add('border-red-500');
            this.classList.remove('border-secondary-300');
            errorElement.classList.remove('hidden');
            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            confirmBtn.disabled = true;
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-secondary-300');
            errorElement.classList.add('hidden');
            confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            confirmBtn.disabled = false;
        }
    });
}

// Función para limpiar selecciones de productos
function clearProductSelections() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    
    // Actualizar contador
    if (typeof updateSelectedCount === 'function') {
        updateSelectedCount();
    }
    
    // Ocultar botones de acción masiva
    document.getElementById('bulk-assign-btn').classList.add('hidden');
    document.getElementById('bulk-remove-btn').classList.add('hidden');
    document.getElementById('bulk-change-btn').classList.add('hidden');
}

// Función para actualizar botones de acción masiva
window.updateBulkActionButtons = function() {
    const selectedCount = document.querySelectorAll('.product-checkbox:checked').length;
    const bulkAssignBtn = document.getElementById('bulk-assign-btn');
    const bulkRemoveBtn = document.getElementById('bulk-remove-btn');
    const bulkChangeBtn = document.getElementById('bulk-change-btn');
    
    if (selectedCount > 0) {
        if (bulkAssignBtn) bulkAssignBtn.classList.remove('hidden');
        if (bulkRemoveBtn) bulkRemoveBtn.classList.remove('hidden');
        if (bulkChangeBtn) bulkChangeBtn.classList.remove('hidden');
    } else {
        if (bulkAssignBtn) bulkAssignBtn.classList.add('hidden');
        if (bulkRemoveBtn) bulkRemoveBtn.classList.add('hidden');
        if (bulkChangeBtn) bulkChangeBtn.classList.add('hidden');
    }
};

// Configurar eventos cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    // Configurar eventos para checkboxes de productos
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-checkbox')) {
            updateBulkActionButtons();
        }
    });
    
    // Configurar evento para checkbox "seleccionar todos"
    const selectAllCheckbox = document.getElementById('select-all-products');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButtons();
        });
    }
});

// Funciones de notificación (si no están definidas globalmente)
if (typeof showSuccessNotification === 'undefined') {
    window.showSuccessNotification = function(message) {
        alert('Éxito: ' + message);
    };
}

if (typeof showErrorNotification === 'undefined') {
    window.showErrorNotification = function(message) {
        alert('Error: ' + message);
    };
}

if (typeof showWarningNotification === 'undefined') {
    window.showWarningNotification = function(message) {
        alert('Advertencia: ' + message);
    };
}
