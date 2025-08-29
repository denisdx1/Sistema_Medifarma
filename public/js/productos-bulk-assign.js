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

        // Actualizar lista de productos seleccionados automáticamente
        updateSelectedProductsList();

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
        
        // Verificar si hay un mercado pre-seleccionado desde el módulo de marcas
        const preSelectedMarket = sessionStorage.getItem('preSelectedMarket');
        if (preSelectedMarket) {
            // Esperar a que los mercados se hayan cargado antes de intentar seleccionar
            const checkMarketsLoaded = () => {
                const marketItems = document.querySelectorAll('#bulk-markets-dropdown .market-item');
                if (marketItems.length > 0) {
                    selectPreSelectedMarket(preSelectedMarket);
                } else {
                    // Si aún no hay mercados, esperar un poco más
                    setTimeout(checkMarketsLoaded, 200);
                }
            };
            
            // Iniciar la verificación después de un delay inicial
            setTimeout(checkMarketsLoaded, 500);
        }
    };

    // Cerrar modal
    window.closeBulkAssignModal = function() {
        document.getElementById('bulk-assign-modal').classList.add('hidden');
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

    // Actualizar lista de productos seleccionados (siempre visible)
    window.updateSelectedProductsList = function() {
        const contentDiv = document.getElementById('selected-products-content');
        
        // Generar lista de productos
        let html = '';
        if (window.selectedProducts.size === 0) {
            html = '<p class="text-xs text-gray-500 text-center py-1">No hay productos seleccionados</p>';
        } else {
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
        }
        
        contentDiv.innerHTML = html;
    };

    // Mostrar/ocultar lista de productos seleccionados (función legacy para compatibilidad)
    window.showSelectedProductsList = function() {
        // Esta función ya no es necesaria ya que la lista está siempre visible
        // Pero la mantenemos para compatibilidad
        updateSelectedProductsList();
    };

    // ===== FUNCIONES DE MERCADOS =====

    // Configurar búsqueda de mercados
    function setupBulkMarketSearch() {
        const searchInput = document.getElementById('bulk-market-search-input');
        const dropdown = document.getElementById('bulk-markets-dropdown');
        
        if (!searchInput || !dropdown) return;
        
        // Limpiar event listeners previos
        searchInput.removeEventListener('input', handleBulkMarketSearch);
        searchInput.removeEventListener('focus', handleBulkMarketFocus);
        
        // Agregar nuevos event listeners
        searchInput.addEventListener('input', handleBulkMarketSearch);
        searchInput.addEventListener('focus', handleBulkMarketFocus);
        
        // Click fuera del dropdown (mejorado para evitar conflictos)
        $(document).off('click.bulkMarketDropdown').on('click.bulkMarketDropdown', function(e) {
            if (!$(e.target).closest('#bulk-markets-dropdown').length && 
                !$(e.target).closest('#bulk-market-search-input').length) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function handleBulkMarketSearch(e) {
        const searchTerm = e.target.value.trim().toLowerCase();
        const dropdown = document.getElementById('bulk-markets-dropdown');
        
        // Mostrar dropdown cuando se empiece a escribir
        if (bulkAvailableMarkets.length > 0) {
            dropdown.classList.remove('hidden');
        }
        
        if (bulkMarketSearchTimeout) {
            clearTimeout(bulkMarketSearchTimeout);
        }
        
        bulkMarketSearchTimeout = setTimeout(() => {
            filterBulkMarkets(searchTerm);
        }, 300);
    }

    function handleBulkMarketFocus(e) {
        // Usar setTimeout para evitar conflictos con otros event listeners
        setTimeout(() => {
            if (bulkAvailableMarkets.length > 0) {
                document.getElementById('bulk-markets-dropdown').classList.remove('hidden');
                filterBulkMarkets(e.target.value.trim().toLowerCase());
            } else {
                // Si no hay mercados, cargarlos
                loadBulkAvailableMarkets();
            }
        }, 100);
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
            showAssignConfirmationModal();
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

        // Obtener la nota del textarea (si la modal de confirmación está abierta)
        let note = '';
        const assignNoteField = document.getElementById('assign-note');
        if (assignNoteField && !assignNoteField.closest('.hidden')) {
            note = assignNoteField.value.trim();
        }

        // Llamar a la función con nota
        assignToExistingMarketWithNote(note);
    }

    // Asignar a mercado existente con nota específica
    function assignToExistingMarketWithNote(note) {
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
                products: products,
                note: note
            },
            success: function(response) {
                if (response.success) {
                    showSuccessNotification(`${response.assigned_count} productos asignados exitosamente al mercado ${response.market_name}`);
                    
                    // Cerrar modal y limpiar selecciones
                    closeBulkAssignModal();
                    
                    // Limpiar selecciones si la función está disponible
                    if (typeof clearProductSelections === 'function') {
                        clearProductSelections();
                    }
                    
                    // Recargar la página para mostrar los cambios
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
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

        // Validar que la nota sea obligatoria
        if (!marketNote) {
            showErrorNotification('La nota es obligatoria para crear un mercado');
            // Resaltar el campo de nota
            const noteField = document.getElementById('new-market-note');
            if (noteField) {
                noteField.focus();
                noteField.classList.add('border-red-500', 'ring-2', 'ring-red-200');
                setTimeout(() => {
                    noteField.classList.remove('border-red-500', 'ring-2', 'ring-red-200');
                }, 3000);
            }
            return;
        }

        if (window.selectedProducts.size === 0) {
            showErrorNotification('No hay productos seleccionados');
            return;
        }

        // Verificar si ya existe un mercado con el mismo nombre antes de continuar
        checkMarketExistence(marketName, marketNote);
    };

    // Verificar si ya existe un mercado con el mismo nombre
    function checkMarketExistence(marketName, marketNote) {
        console.log('Verificando si existe el mercado:', marketName);
        
        $.ajax({
            url: '/productos/markets/api',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                search: marketName
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Buscar coincidencia exacta (case-insensitive)
                    const existingMarket = response.data.find(market => 
                        market.mercado.trim().toLowerCase() === marketName.trim().toLowerCase()
                    );
                    
                    if (existingMarket) {
                        console.warn('Mercado ya existe:', existingMarket);
                        showErrorNotification('❌ Ya existe un mercado activo con ese nombre');
                        return;
                    }
                    
                    // Si no existe, proceder con el modal de confirmación
                    console.log('Mercado no existe, procediendo con confirmación');
                    openCreateMarketConfirmationModal(marketName, marketNote);
                } else {
                    console.error('Error en respuesta de verificación:', response);
                    showErrorNotification('Error al verificar mercados existentes');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al verificar mercados:', xhr, status, error);
                showErrorNotification('Error al verificar mercados existentes');
            }
        });
    }

    // Buscar el mercado recién creado y asignar productos
    function findNewMarketAndAssign(marketName) {
        console.log('Buscando mercado creado:', marketName);
        $.ajax({
            url: '/productos/markets/api',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Respuesta de markets API:', response);
                if (response.success && response.data) {
                    console.log('Mercados disponibles:', response.data.map(m => `${m.mercado} (ID: ${m.idMercado})`));
                    const newMarket = response.data.find(market => market.mercado === marketName);
                    console.log('Mercado encontrado:', newMarket);
                    if (newMarket) {
                        console.log(`Asignando productos al mercado: ${marketName} (ID: ${newMarket.idMercado})`);
                        // Asignar productos al nuevo mercado
                        assignProductsToNewMarket(newMarket.idMercado, marketName);
                    } else {
                        console.error('No se encontró el mercado:', marketName);
                        console.log('Mercados disponibles para comparar:', response.data.map(m => m.mercado));
                        showErrorNotification('No se pudo encontrar el mercado recién creado: ' + marketName);
                        resetCreateButton();
                    }
                } else {
                    console.error('Error en respuesta de markets API:', response);
                    showErrorNotification('Error al buscar el mercado creado');
                    resetCreateButton();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX markets API:', xhr, status, error);
                showErrorNotification('Error al buscar el mercado creado');
                resetCreateButton();
            }
        });
    }

    // Asignar productos al nuevo mercado
    function assignProductsToNewMarket(marketId, marketName) {
        console.log('Iniciando asignación de productos al mercado nuevo');
        console.log('Market ID:', marketId, 'Market Name:', marketName);
        
        const btn = document.getElementById('create-and-assign-btn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Asignando productos...';

        const products = Array.from(window.selectedProducts).map(product => ({
            code: product.code,
            fuente: product.fuente
        }));

        // Obtener la nota del mercado que se acaba de crear
        const marketNote = document.getElementById('new-market-note').value.trim();

        console.log('Productos a asignar:', products);
        console.log('Datos a enviar:', {
            idMercado: parseInt(marketId),
            products: products,
            note: marketNote
        });

        $.ajax({
            url: '/productos/assign-products',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                idMercado: parseInt(marketId),
                products: products,
                note: marketNote
            },
            success: function(response) {
                if (response.success) {
                    showSuccessNotification(`Mercado "${marketName}" creado y ${response.assigned_count} productos asignados exitosamente`);
                    
                    // Cerrar ambas modales y limpiar selecciones
                    closeCreateMarketConfirmationModal();
                    closeBulkAssignModal();
                    
                    // Limpiar selecciones si la función está disponible
                    if (typeof clearProductSelections === 'function') {
                        clearProductSelections();
                    }
                    
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
    
    if (typeof showWarningNotification === 'undefined') {
        window.showWarningNotification = function(message) {
            console.log('WARNING:', message);
            showToast(message, 'warning');
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
    
    // Actualizar display de productos en modal de confirmación
    function updateConfirmProductsDisplay() {
        const count = window.selectedProducts.size;
        document.getElementById('confirm-products-count').textContent = count;
        
        const content = document.getElementById('confirm-products-content');
        content.innerHTML = '';
        
        if (count > 0) {
            Array.from(window.selectedProducts).forEach((product, index) => {
                const productDiv = document.createElement('div');
                productDiv.className = 'flex items-center justify-between py-1 ' + 
                                     (index > 0 ? 'border-t border-gray-200' : '');
                productDiv.innerHTML = `
                    <div class="flex-1">
                        <p class="text-xs font-medium text-gray-900">${product.name}</p>
                        <p class="text-xs text-gray-500">${product.code} • ${product.fuente}</p>
                    </div>
                `;
                content.appendChild(productDiv);
            });
        } else {
            content.innerHTML = '<p class="text-xs text-gray-500 text-center py-2">No hay productos seleccionados</p>';
        }
    }
    
    // Proceder con la creación del mercado y asignación (lógica original movida aquí)
    window.proceedWithMarketCreationAndAssignment = function() {
        console.log('🚀 FUNCIÓN LLAMADA: proceedWithMarketCreationAndAssignment', new Date().toISOString());
        
        const marketName = document.getElementById('confirm-market-name').textContent;
        const marketNote = document.getElementById('confirm-market-note').textContent;
        
        console.log('📝 Datos obtenidos:', { marketName, marketNote });
        
        // Mostrar loading en el botón de confirmación
        const btn = document.getElementById('final-create-assign-btn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoading = btn.querySelector('.btn-loading');
        
        console.log('🔍 Estado del botón ANTES:', { 
            disabled: btn.disabled, 
            classList: btn.className,
            innerHTML: btn.innerHTML.substring(0, 100) + '...'
        });
        
        // Prevenir doble submit
        if (btn.disabled) {
            console.warn('🚫 Botón ya está deshabilitado - previniendo doble submit');
            return;
        }
        
        console.log('🔒 DESHABILITANDO BOTÓN para prevenir doble submit');
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        btn.disabled = true;
        
        console.log('🔍 Estado del botón DESPUÉS:', { 
            disabled: btn.disabled, 
            classList: btn.className
        });

        // Crear mercado primero
        console.log('📤 Enviando request para crear mercado:', {
            url: '/productos/create-market',
            market_name: marketName,
            market_note: marketNote,
            timestamp: new Date().toISOString()
        });
        
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
                
                console.error('Error al crear mercado:', xhr.responseJSON);
                
                // Mostrar error específico para mercados duplicados
                if (xhr.status === 422 && errorMessage.includes('existe')) {
                    showErrorNotification(`❌ ${errorMessage}`);
                } else {
                    showErrorNotification(errorMessage);
                }
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

    // ===== MODAL DE CONFIRMACIÓN PARA ASIGNACIÓN =====
    
    // Mostrar modal de confirmación para asignación a mercado existente
    function showAssignConfirmationModal() {
        const selectedMarketId = document.getElementById('bulk-selected-market-id').value;
        const selectedMarketName = document.querySelector('#bulk-assign-dropdown .market-item.selected')?.textContent?.trim();
        
        if (!selectedMarketId) {
            showErrorNotification('Debe seleccionar un mercado');
            return;
        }

        if (window.selectedProducts.size === 0) {
            showErrorNotification('No hay productos seleccionados');
            return;
        }

        // Llenar información de la modal
        document.getElementById('confirm-assign-count').textContent = window.selectedProducts.size;
        document.getElementById('confirm-assign-market').textContent = selectedMarketName || 'Mercado seleccionado';
        
        // Mostrar productos seleccionados (máximo 3)
        const productsContainer = document.getElementById('confirm-assign-products');
        const products = Array.from(window.selectedProducts);
        const maxToShow = 3;
        
        let productsHtml = '';
        for (let i = 0; i < Math.min(products.length, maxToShow); i++) {
            const product = products[i];
            // Los productos ya vienen como objetos con propiedades code, name, fuente
            const productName = product.name || `${product.code} (${product.fuente})`;
            productsHtml += `<div class="mb-1">• ${productName}</div>`;
        }
        
        if (products.length > maxToShow) {
            productsHtml += `<div class="text-gray-500 italic">... y ${products.length - maxToShow} más</div>`;
        }
        
        productsContainer.innerHTML = productsHtml;
        
        // Mostrar modal
        document.getElementById('assign-confirmation-modal').classList.remove('hidden');
    }

    // Cerrar modal de confirmación
    window.closeAssignConfirmationModal = function() {
        // Limpiar el campo de nota
        const assignNoteField = document.getElementById('assign-note');
        if (assignNoteField) {
            assignNoteField.value = '';
        }
        
        document.getElementById('assign-confirmation-modal').classList.add('hidden');
    };

    // Proceder con la asignación después de confirmación
    window.proceedWithAssignment = function() {
        // Validar que la nota sea obligatoria
        const noteField = document.getElementById('assign-note');
        const note = noteField ? noteField.value.trim() : '';
        
        if (!note) {
            showErrorNotification('La nota es obligatoria para asignar productos');
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

        // Cerrar modal de confirmación
        closeAssignConfirmationModal();
        
        // Ejecutar la asignación con la nota ya validada
        assignToExistingMarketWithNote(note);
    };

    // Event listener para cerrar modal haciendo clic fuera
    document.getElementById('assign-confirmation-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAssignConfirmationModal();
        }
    });
    
    // ===== FUNCIÓN PARA SELECCIONAR MERCADO PRE-SELECCIONADO =====
    
    // Función para seleccionar automáticamente un mercado pre-seleccionado
    function selectPreSelectedMarket(marketName) {
        // Buscar el mercado en la lista de mercados disponibles
        const marketItems = document.querySelectorAll('#bulk-markets-dropdown .market-item');
        
        for (let item of marketItems) {
            // Buscar el elemento que contiene el nombre del mercado
            const marketNameElement = item.querySelector('p.text-sm.font-medium.text-gray-900');
            if (marketNameElement) {
                const itemText = marketNameElement.textContent.trim();
                if (itemText === marketName) {
                    // Simular clic en el mercado
                    item.click();
                    
                    // Mostrar notificación de mercado pre-seleccionado
                    showSuccessNotification(`Mercado "${marketName}" pre-seleccionado automáticamente`);
                    
                    // Limpiar el sessionStorage después de usarlo
                    sessionStorage.removeItem('preSelectedMarket');
                    
                    return;
                }
            }
        }
        
        // Si no se encuentra el mercado, mostrar notificación y debug info
        console.log('Mercado no encontrado:', marketName);
        console.log('Mercados disponibles:', Array.from(document.querySelectorAll('#bulk-markets-dropdown .market-item')).map(item => {
            const nameElement = item.querySelector('p.text-sm.font-medium.text-gray-900');
            return nameElement ? nameElement.textContent.trim() : 'Sin nombre';
        }));
        
        showWarningNotification(`No se encontró el mercado "${marketName}" en la lista disponible`);
    }
});
