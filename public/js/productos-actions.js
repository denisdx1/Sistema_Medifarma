// Funciones adicionales para acciones de productos (Quitar y Cambiar)

// Variables para el search
let marketSearchTimeout = null;
let searchEventListenersAttached = false;

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
    
    modal.classList.remove('hidden');
};

window.closeRemoveProductModal = function() {
    document.getElementById('remove-product-modal').classList.add('hidden');
    window.currentProductCode = null;
    window.currentProductName = null;
};

// Funciones para Modal de Cambiar Mercado
window.openChangeMarketModal = function(productCode, productName) {
    const modal = document.getElementById('change-market-modal');
    
    if (!modal) {
        return;
    }
    
    window.currentProductCode = productCode;
    window.currentProductName = productName;
    
    const nameElement = document.getElementById('change-product-name');
    const codeElement = document.getElementById('change-product-code');
    const currentMarketElement = document.getElementById('current-market-name');
    
    if (nameElement) nameElement.textContent = productName;
    if (codeElement) codeElement.textContent = productCode;
    
    // Buscar el mercado actual del producto en la tabla
    const productRow = document.querySelector(`tr[data-product-code="${productCode}"]`);
    if (productRow && currentMarketElement) {
        const marketCell = productRow.querySelector('.market-cell span');
        const currentMarket = marketCell ? marketCell.textContent.trim() : 'RESTO';
        currentMarketElement.textContent = currentMarket;
    }
    
    // Limpiar búsqueda y selección previa
    const searchInput = document.getElementById('market-search-input');
    const selectedDisplay = document.getElementById('selected-market-display');
    const selectedMarketId = document.getElementById('selected-market-id');
    
    if (searchInput) searchInput.value = '';
    if (selectedDisplay) selectedDisplay.classList.add('hidden');
    if (selectedMarketId) selectedMarketId.value = '';
    
    // Limpiar el campo de nota
    const noteElement = document.getElementById('change-market-note');
    if (noteElement) noteElement.value = '';
    
    // Validación inicial - deshabilitar botón hasta que se ingrese una nota
    const changeBtn = document.getElementById('change-market-btn');
    if (changeBtn) {
        changeBtn.classList.add('opacity-50', 'cursor-not-allowed');
        changeBtn.disabled = true;
    }
    
    // Configurar búsqueda y cargar mercados
    if (typeof setupMarketSearch === 'function') {
        setupMarketSearch();
    }
    if (typeof loadAvailableMarkets === 'function') {
        loadAvailableMarkets();
    }
    
    modal.classList.remove('hidden');
};

window.closeChangeMarketModal = function() {
    document.getElementById('change-market-modal').classList.add('hidden');
    window.currentProductCode = null;
    window.currentProductName = null;
};

// Configurar event listeners para búsqueda de mercados
function setupMarketSearch() {
    if (searchEventListenersAttached) return;
    
    const searchInput = document.getElementById('market-search-input');
    const dropdown = document.getElementById('markets-dropdown');
    
    if (!searchInput || !dropdown) return;
    
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
    
    if (!marketsList || !noResults) return;
    
    // Obtener mercado actual del producto para excluirlo
    const currentMarketName = document.getElementById('current-market-name') ? 
                              document.getElementById('current-market-name').textContent : '';
    
    // Filtrar mercados (excluir el mercado actual del producto)
    const filteredMarkets = window.availableMarkets.filter(market => {
        return market.estado === 'ACTIVO' &&
               market.mercado !== currentMarketName &&
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

// Función para cargar mercados disponibles
function loadAvailableMarkets() {
    const dropdown = document.getElementById('markets-dropdown');
    const loadingState = document.getElementById('dropdown-loading');
    const marketsList = document.getElementById('markets-list');
    const noResults = document.getElementById('dropdown-no-results');
    
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
                window.availableMarkets = response.data;
                loadingState.classList.add('hidden');
                filterMarkets(''); // Mostrar todos los mercados inicialmente
                console.log(`Cargados ${window.availableMarkets.length} mercados disponibles`);
            } else {
                showDropdownError('Error al cargar mercados');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error loading markets:', {xhr, textStatus, errorThrown});
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

// Función global para quitar producto del mercado
window.removeProductFromMarket = function() {
    if (!window.currentProductCode) {
        showErrorNotification('Error: No se ha seleccionado un producto válido');
        return;
    }

    // Validar que la nota no esté vacía
    const noteElement = document.getElementById('remove-product-note');
    const note = noteElement ? noteElement.value.trim() : '';
    
    if (!note) {
        showErrorNotification('La nota es obligatoria para quitar un producto');
        // Resaltar el campo de nota
        if (noteElement) {
            noteElement.focus();
            noteElement.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            setTimeout(() => {
                noteElement.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            }, 3000);
        }
        return;
    }

    // Abrir modal de confirmación final
    openFinalConfirmationModal();
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

    // Validar que la nota sea obligatoria
    const noteField = document.getElementById('remove-product-note');
    const note = noteField ? noteField.value.trim() : '';
    
    if (!note) {
        showErrorNotification('La nota es obligatoria para remover el producto');
        // Resaltar el campo de nota
        if (noteField) {
            noteField.focus();
            noteField.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            setTimeout(() => {
                noteField.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            }, 3000);
        }
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
        url: '/productos/remove',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            codigoPresentacion: window.currentProductCode,
            note: $('#remove-product-note').val().trim()
        },
        success: function(response) {
            if (response.success) {
                // Mostrar notificación de éxito
                showSuccessNotification('Producto removido del mercado exitosamente');
                
                // Cerrar ambas modales
                closeFinalConfirmationModal();
                closeRemoveProductModal();
                
                // Siempre recargar la página después de remover
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
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

    // Validar que la nota sea obligatoria
    const noteField = document.getElementById('change-market-note');
    const note = noteField ? noteField.value.trim() : '';
    
    if (!note) {
        showErrorNotification('La nota es obligatoria para cambiar el mercado del producto');
        // Resaltar el campo de nota
        if (noteField) {
            noteField.focus();
            noteField.classList.add('border-red-500', 'ring-2', 'ring-red-200');
            setTimeout(() => {
                noteField.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
            }, 3000);
        }
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
        url: '/productos/change-market',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            codigoPresentacion: window.currentProductCode,
            nuevoMercadoId: parseInt(selectedMarketId),
            note: $('#change-market-note').val().trim()
        },
        success: function(response) {
            if (response.success) {
                // Mostrar notificación de éxito
                showSuccessNotification(response.message);
                
                // Cerrar ambas modales
                closeFinalChangeConfirmationModal();
                closeChangeMarketModal();
                
                // Siempre recargar la página después de cambiar mercado
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
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

// Event listeners para cerrar modales
$(document).ready(function() {
    // Cerrar modales con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            if (window.closeRemoveProductModal) window.closeRemoveProductModal();
            if (window.closeChangeMarketModal) window.closeChangeMarketModal();
            if (window.closeFinalConfirmationModal) window.closeFinalConfirmationModal();
            if (window.closeFinalChangeConfirmationModal) window.closeFinalChangeConfirmationModal();
        }
    });

    // Cerrar modales al hacer clic fuera
    if (document.getElementById('remove-product-modal')) {
        document.getElementById('remove-product-modal').addEventListener('click', function(e) {
            if (e.target === this && window.closeRemoveProductModal) {
                window.closeRemoveProductModal();
            }
        });
    }

    if (document.getElementById('change-market-modal')) {
        document.getElementById('change-market-modal').addEventListener('click', function(e) {
            if (e.target === this && window.closeChangeMarketModal) {
                window.closeChangeMarketModal();
            }
        });
    }

    if (document.getElementById('final-confirmation-modal')) {
        document.getElementById('final-confirmation-modal').addEventListener('click', function(e) {
            if (e.target === this && window.closeFinalConfirmationModal) {
                window.closeFinalConfirmationModal();
            }
        });
    }

    if (document.getElementById('final-change-confirmation-modal')) {
        document.getElementById('final-change-confirmation-modal').addEventListener('click', function(e) {
            if (e.target === this && window.closeFinalChangeConfirmationModal) {
                window.closeFinalChangeConfirmationModal();
            }
        });
    }
    
    // Agregar validación en tiempo real para las notas
    setupNoteValidation();
});

// ===== VALIDACIÓN DE NOTAS EN TIEMPO REAL =====

// Función para configurar la validación de notas en tiempo real
function setupNoteValidation() {
    // Validación para la nota de quitar producto
    const removeNoteField = document.getElementById('remove-product-note');
    if (removeNoteField) {
        removeNoteField.addEventListener('input', function() {
            validateNoteField(this, 'remove-note-error');
        });
    }
    
    // Validación para la nota de cambiar mercado
    const changeNoteField = document.getElementById('change-market-note');
    if (changeNoteField) {
        changeNoteField.addEventListener('input', function() {
            validateNoteField(this, 'change-note-error');
        });
    }
}

// Función para validar un campo de nota
function validateNoteField(noteField, errorId) {
    const note = noteField.value.trim();
    const errorMsg = document.getElementById(errorId);
    const submitBtn = getSubmitButtonForNoteField(noteField.id);
    
    if (!note) {
        noteField.classList.add('border-red-500');
        noteField.classList.remove('border-primary');
        if (errorMsg) errorMsg.classList.remove('hidden');
        if (submitBtn) {
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.disabled = true;
        }
    } else {
        noteField.classList.remove('border-red-500');
        noteField.classList.add('border-primary');
        if (errorMsg) errorMsg.classList.add('hidden');
        if (submitBtn) {
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.disabled = false;
        }
    }
}

// Función para obtener el botón de envío correspondiente a un campo de nota
function getSubmitButtonForNoteField(noteFieldId) {
    switch (noteFieldId) {
        case 'remove-product-note':
            return document.getElementById('confirm-remove-btn');
        case 'change-market-note':
            return document.getElementById('change-market-btn');
        default:
            return null;
    }
}
