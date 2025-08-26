// Funcionalidad de asignación masiva de productos
$(document).ready(function() {
    // Variables para asignación masiva
    let bulkMarketSearchTimeout = null;
    let bulkAvailableMarkets = [];
    let currentTab = 'existing'; // 'existing' o 'create'

    // ===== FUNCIONES DE LA MODAL =====

    // Abrir modal de asignación masiva
    window.openBulkAssignModal = function() {
        if (window.selectedProducts.size === 0) {
            showErrorNotification('No hay productos seleccionados');
            return;
        }

        // Actualizar contador de productos
        const selectedCount = window.selectedProducts.size;
        document.getElementById('bulk-selected-count').textContent = `${selectedCount} producto${selectedCount > 1 ? 's' : ''}`;

        // Resetear formulario
        switchToExistingMarket();
        clearBulkSelectedMarket();
        document.getElementById('bulk-market-search-input').value = '';
        document.getElementById('new-market-name').value = '';
        document.getElementById('new-market-note').value = '';

        // Mostrar modal
        document.getElementById('bulk-assign-modal').classList.remove('hidden');

        // Cargar mercados disponibles
        loadBulkAvailableMarkets();
        setupBulkMarketSearch();
    };

    // Cerrar modal
    window.closeBulkAssignModal = function() {
        document.getElementById('bulk-assign-modal').classList.add('hidden');
        document.getElementById('selected-products-list').classList.add('hidden');
    };

    // ===== FUNCIONES DE TABS =====

    // Cambiar a tab de mercado existente
    window.switchToExistingMarket = function() {
        currentTab = 'existing';
        
        // Actualizar tabs
        document.getElementById('existing-market-tab').classList.add('active', 'text-green-600', 'border-green-500');
        document.getElementById('existing-market-tab').classList.remove('text-gray-500', 'border-transparent');
        
        document.getElementById('create-market-tab').classList.remove('active', 'text-green-600', 'border-green-500');
        document.getElementById('create-market-tab').classList.add('text-gray-500', 'border-transparent');

        // Mostrar/ocultar paneles
        document.getElementById('existing-market-panel').classList.remove('hidden');
        document.getElementById('create-market-panel').classList.add('hidden');

        // Habilitar/deshabilitar botón
        const confirmBtn = document.getElementById('bulk-assign-confirm-btn');
        const selectedMarketId = document.getElementById('bulk-selected-market-id').value;
        confirmBtn.disabled = !selectedMarketId;
    };

    // Cambiar a tab de crear mercado
    window.switchToCreateMarket = function() {
        currentTab = 'create';
        
        // Actualizar tabs
        document.getElementById('create-market-tab').classList.add('active', 'text-green-600', 'border-green-500');
        document.getElementById('create-market-tab').classList.remove('text-gray-500', 'border-transparent');
        
        document.getElementById('existing-market-tab').classList.remove('active', 'text-green-600', 'border-green-500');
        document.getElementById('existing-market-tab').classList.add('text-gray-500', 'border-transparent');

        // Mostrar/ocultar paneles
        document.getElementById('create-market-panel').classList.remove('hidden');
        document.getElementById('existing-market-panel').classList.add('hidden');

        // Habilitar botón (siempre habilitado en modo crear)
        document.getElementById('bulk-assign-confirm-btn').disabled = false;
    };

    // ===== FUNCIONES DE LISTA DE PRODUCTOS =====

    // Mostrar/ocultar lista de productos seleccionados
    window.showSelectedProductsList = function() {
        const listDiv = document.getElementById('selected-products-list');
        const contentDiv = document.getElementById('selected-products-content');
        
        if (listDiv.classList.contains('hidden')) {
            // Generar lista de productos
            let html = '';
            let count = 0;
            window.selectedProducts.forEach(product => {
                count++;
                html += `
                    <div class="flex items-center justify-between py-1 ${count > 1 ? 'border-t border-gray-200' : ''}">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-900 truncate">${product.name}</p>
                            <p class="text-xs text-gray-500">${product.code} • ${product.fuente}</p>
                        </div>
                    </div>
                `;
            });
            
            contentDiv.innerHTML = html;
            listDiv.classList.remove('hidden');
        } else {
            listDiv.classList.add('hidden');
        }
    };

    // ===== FUNCIONES DE MERCADOS =====

    // Configurar búsqueda de mercados
    function setupBulkMarketSearch() {
        const searchInput = document.getElementById('bulk-market-search-input');
        const dropdown = document.getElementById('bulk-markets-dropdown');
        
        // Limpiar event listeners previos
        searchInput.removeEventListener('input', handleBulkMarketSearch);
        searchInput.removeEventListener('focus', handleBulkMarketFocus);
        
        // Agregar nuevos event listeners
        searchInput.addEventListener('input', handleBulkMarketSearch);
        searchInput.addEventListener('focus', handleBulkMarketFocus);
        
        // Click fuera del dropdown
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && !searchInput.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function handleBulkMarketSearch(e) {
        const searchTerm = e.target.value.trim().toLowerCase();
        
        if (bulkMarketSearchTimeout) {
            clearTimeout(bulkMarketSearchTimeout);
        }
        
        bulkMarketSearchTimeout = setTimeout(() => {
            filterBulkMarkets(searchTerm);
        }, 300);
    }

    function handleBulkMarketFocus(e) {
        if (bulkAvailableMarkets.length > 0) {
            document.getElementById('bulk-markets-dropdown').classList.remove('hidden');
            filterBulkMarkets(e.target.value.trim().toLowerCase());
        }
    }

    // Cargar mercados disponibles
    function loadBulkAvailableMarkets() {
        const dropdown = document.getElementById('bulk-markets-dropdown');
        const loadingState = document.getElementById('bulk-dropdown-loading');
        const marketsList = document.getElementById('bulk-markets-list');
        const noResults = document.getElementById('bulk-dropdown-no-results');
        
        // Mostrar loading
        dropdown.classList.remove('hidden');
        loadingState.classList.remove('hidden');
        marketsList.classList.add('hidden');
        noResults.classList.add('hidden');
        
        $.ajax({
            url: '/productos/markets/api',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    bulkAvailableMarkets = response.data;
                    loadingState.classList.add('hidden');
                    filterBulkMarkets('');
                } else {
                    showBulkDropdownError('Error al cargar mercados');
                }
            },
            error: function() {
                showBulkDropdownError('Error de conexión');
            }
        });
    }

    // Filtrar mercados
    function filterBulkMarkets(searchTerm) {
        const marketsList = document.getElementById('bulk-markets-list');
        const noResults = document.getElementById('bulk-dropdown-no-results');
        
        const filteredMarkets = bulkAvailableMarkets.filter(market => {
            return market.estado === 'ACTIVO' &&
                   (searchTerm === '' || 
                    market.mercado.toLowerCase().includes(searchTerm) ||
                    market.idMercado.toString().includes(searchTerm));
        });
        
        marketsList.innerHTML = '';
        
        if (filteredMarkets.length === 0) {
            marketsList.classList.add('hidden');
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
            marketsList.classList.remove('hidden');
            
            filteredMarkets.forEach(market => {
                const marketItem = createBulkMarketItem(market);
                marketsList.appendChild(marketItem);
            });
        }
    }

    // Crear elemento de mercado
    function createBulkMarketItem(market) {
        const item = document.createElement('div');
        item.className = 'market-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
        item.onclick = () => selectBulkMarket(market);
        
        item.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-store text-green-600 text-xs"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">${market.mercado}</p>
                    <p class="text-xs text-gray-500">ID: ${market.idMercado}</p>
                </div>
                <div class="flex-shrink-0">
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </div>
            </div>
        `;
        
        return item;
    }

    // Seleccionar mercado
    function selectBulkMarket(market) {
        document.getElementById('bulk-selected-market-id').value = market.idMercado;
        document.getElementById('bulk-selected-market-name').textContent = market.mercado;
        document.getElementById('bulk-selected-market-display').classList.remove('hidden');
        
        document.getElementById('bulk-market-search-input').value = market.mercado;
        document.getElementById('bulk-markets-dropdown').classList.add('hidden');
        
        // Habilitar botón de confirmación
        document.getElementById('bulk-assign-confirm-btn').disabled = false;
    }

    // Limpiar selección de mercado
    window.clearBulkSelectedMarket = function() {
        document.getElementById('bulk-selected-market-id').value = '';
        document.getElementById('bulk-selected-market-name').textContent = '';
        document.getElementById('bulk-selected-market-display').classList.add('hidden');
        document.getElementById('bulk-market-search-input').value = '';
        
        // Deshabilitar botón si estamos en tab existente
        if (currentTab === 'existing') {
            document.getElementById('bulk-assign-confirm-btn').disabled = true;
        }
        
        if (bulkAvailableMarkets.length > 0) {
            document.getElementById('bulk-markets-dropdown').classList.remove('hidden');
            filterBulkMarkets('');
        }
    };

    // Mostrar error en dropdown
    function showBulkDropdownError(message) {
        const loadingState = document.getElementById('bulk-dropdown-loading');
        const marketsList = document.getElementById('bulk-markets-list');
        const noResults = document.getElementById('bulk-dropdown-no-results');
        
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

    // ===== FUNCIONES DE PROCESAMIENTO =====

    // Procesar asignación masiva
    window.processBulkAssignment = function() {
        if (currentTab === 'create') {
            createNewMarketAndAssign();
        } else {
            assignToExistingMarket();
        }
    };

    // Asignar a mercado existente
    function assignToExistingMarket() {
        const selectedMarketId = document.getElementById('bulk-selected-market-id').value;
        
        if (!selectedMarketId) {
            showErrorNotification('Debe seleccionar un mercado');
            return;
        }

        if (window.selectedProducts.size === 0) {
            showErrorNotification('No hay productos seleccionados');
            return;
        }

        // Mostrar loading
        const btn = document.getElementById('bulk-assign-confirm-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        btn.disabled = true;

        // Preparar datos
        const products = Array.from(window.selectedProducts).map(product => ({
            code: product.code,
            fuente: product.fuente
        }));

        // Enviar petición
        $.ajax({
            url: '/productos/assign-products',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                idMercado: parseInt(selectedMarketId),
                products: products
            },
            success: function(response) {
                if (response.success) {
                    showSuccessNotification(`${response.assigned_count} productos asignados exitosamente al mercado ${response.market_name}`);
                    
                    // Cerrar modal y limpiar selecciones
                    closeBulkAssignModal();
                    clearProductSelections();
                    
                    // Recargar tabla
                    loadProducts();
                } else {
                    showErrorNotification(response.message || 'Error al asignar productos');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al asignar productos';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showErrorNotification(errorMessage);
            },
            complete: function() {
                // Restaurar botón
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
                btn.disabled = false;
            }
        });
    }

    // Crear nuevo mercado y asignar
    window.createNewMarketAndAssign = function() {
        const marketName = document.getElementById('new-market-name').value.trim();
        const marketNote = document.getElementById('new-market-note').value.trim();
        
        if (!marketName) {
            showErrorNotification('Debe ingresar el nombre del mercado');
            return;
        }

        if (window.selectedProducts.size === 0) {
            showErrorNotification('No hay productos seleccionados');
            return;
        }

        // Abrir modal de confirmación con los datos
        openCreateMarketConfirmationModal(marketName, marketNote);
    };

    // Buscar el mercado recién creado y asignar productos
    function findNewMarketAndAssign(marketName) {
        $.ajax({
            url: '/productos/markets/api',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.data) {
                    const newMarket = response.data.find(market => market.mercado === marketName);
                    if (newMarket) {
                        // Asignar productos al nuevo mercado
                        assignProductsToNewMarket(newMarket.idMercado, marketName);
                    } else {
                        showErrorNotification('No se pudo encontrar el mercado recién creado');
                        resetCreateButton();
                    }
                } else {
                    showErrorNotification('Error al buscar el mercado creado');
                    resetCreateButton();
                }
            },
            error: function() {
                showErrorNotification('Error al buscar el mercado creado');
                resetCreateButton();
            }
        });
    }

    // Asignar productos al nuevo mercado
    function assignProductsToNewMarket(marketId, marketName) {
        const btn = document.getElementById('create-and-assign-btn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Asignando productos...';

        const products = Array.from(window.selectedProducts).map(product => ({
            code: product.code,
            fuente: product.fuente
        }));

        $.ajax({
            url: '/productos/assign-products',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                idMercado: parseInt(marketId),
                products: products
            },
            success: function(response) {
                if (response.success) {
                    showSuccessNotification(`Mercado "${marketName}" creado y ${response.assigned_count} productos asignados exitosamente`);
                    
                    // Cerrar ambas modales y limpiar selecciones
                    closeCreateMarketConfirmationModal();
                    closeBulkAssignModal();
                    clearProductSelections();
                    
                    // Refrescar la página después de crear y asignar
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showErrorNotification(`Mercado creado pero error al asignar productos: ${response.message}`);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Mercado creado pero error al asignar productos';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += ': ' + xhr.responseJSON.message;
                }
                showErrorNotification(errorMessage);
            },
            complete: function() {
                resetCreateButton();
            }
        });
    }

    // Resetear botón de crear
    function resetCreateButton() {
        const btn = document.getElementById('create-and-assign-btn');
        btn.innerHTML = '<i class="fas fa-plus mr-2"></i>Crear Mercado y Asignar Productos';
        btn.disabled = false;
    }

    // ===== FUNCIONES AUXILIARES =====

    // Funciones de notificación (reutilizar las existentes)
    if (typeof showSuccessNotification === 'undefined') {
        window.showSuccessNotification = function(message) {
            console.log('SUCCESS:', message);
            showToast(message, 'success');
        };
    }

    if (typeof showErrorNotification === 'undefined') {
        window.showErrorNotification = function(message) {
            console.log('ERROR:', message);
            showToast(message, 'error');
        };
    }

    // Cerrar modal con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            closeBulkAssignModal();
            closeCreateMarketConfirmationModal();
        }
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('bulk-assign-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeBulkAssignModal();
        }
    });

    // Cerrar modal de confirmación al hacer clic fuera
    document.getElementById('create-market-confirmation-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateMarketConfirmationModal();
        }
    });

    // ===== FUNCIONES PARA MODAL DE CONFIRMACIÓN =====
    
    // Abrir modal de confirmación para crear mercado
    window.openCreateMarketConfirmationModal = function(marketName, marketNote) {
        // Llenar datos del mercado
        document.getElementById('confirm-market-name').textContent = marketName;
        
        if (marketNote && marketNote.trim() !== '') {
            document.getElementById('confirm-market-note').textContent = marketNote;
            document.getElementById('confirm-market-note-container').style.display = 'block';
        } else {
            document.getElementById('confirm-market-note-container').style.display = 'none';
        }
        
        // Llenar datos de productos
        updateConfirmProductsDisplay();
        
        // Mostrar modal
        document.getElementById('create-market-confirmation-modal').classList.remove('hidden');
    };
    
    // Cerrar modal de confirmación
    window.closeCreateMarketConfirmationModal = function() {
        document.getElementById('create-market-confirmation-modal').classList.add('hidden');
    };
    
    // Alternar vista de lista de productos en confirmación
    window.toggleConfirmProductsList = function() {
        const list = document.getElementById('confirm-products-list');
        const btn = document.getElementById('toggle-confirm-products-btn');
        const icon = btn.querySelector('i');
        
        if (list.classList.contains('hidden')) {
            list.classList.remove('hidden');
            icon.className = 'fas fa-chevron-up mr-1';
            btn.innerHTML = '<i class="fas fa-chevron-up mr-1"></i>Ocultar lista';
        } else {
            list.classList.add('hidden');
            icon.className = 'fas fa-chevron-down mr-1';
            btn.innerHTML = '<i class="fas fa-chevron-down mr-1"></i>Ver lista';
        }
    };
    
    // Actualizar display de productos en modal de confirmación
    function updateConfirmProductsDisplay() {
        const count = window.selectedProducts.size;
        document.getElementById('confirm-products-count').textContent = count;
        
        const content = document.getElementById('confirm-products-content');
        content.innerHTML = '';
        
        if (count > 0) {
            Array.from(window.selectedProducts).forEach((product, index) => {
                const productDiv = document.createElement('div');
                productDiv.className = 'flex items-center justify-between py-1 px-2 ' + 
                                     (index % 2 === 0 ? 'bg-gray-50' : 'bg-white');
                productDiv.innerHTML = `
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-900">${product.name}</p>
                        <p class="text-xs text-gray-500">Código: ${product.code} | Fuente: ${product.fuente}</p>
                    </div>
                `;
                content.appendChild(productDiv);
            });
        } else {
            content.innerHTML = '<p class="text-sm text-gray-500 text-center py-2">No hay productos seleccionados</p>';
        }
    }
    
    // Proceder con la creación del mercado y asignación (lógica original movida aquí)
    window.proceedWithMarketCreationAndAssignment = function() {
        const marketName = document.getElementById('confirm-market-name').textContent;
        const marketNote = document.getElementById('confirm-market-note').textContent;
        
        // Mostrar loading en el botón de confirmación
        const btn = document.getElementById('final-create-assign-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        btn.disabled = true;

        // Crear mercado primero
        $.ajax({
            url: '/productos/create-market',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                market_name: marketName,
                market_note: marketNote || null
            },
            success: function(response) {
                if (response.success) {
                    // Mercado creado, ahora buscar su ID y asignar productos
                    findNewMarketAndAssign(marketName);
                } else {
                    showErrorNotification(response.message || 'Error al crear mercado');
                    resetConfirmationButton();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error al crear mercado';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showErrorNotification(errorMessage);
                resetConfirmationButton();
            }
        });
    };
    
    // Resetear botón de confirmación
    function resetConfirmationButton() {
        const btn = document.getElementById('final-create-assign-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        
        btnText.classList.remove('hidden');
        btnLoading.classList.add('hidden');
        btn.disabled = false;
    }
});
