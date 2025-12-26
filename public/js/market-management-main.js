// Market Management Main JavaScript Functions
$(document).ready(function() {
    // Event listeners para cerrar modales al hacer clic fuera
    document.getElementById('create-market-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateMarketModal();
        }
    });
    
    // Modal de confirmación de crear mercado
    document.getElementById('create-market-confirmation-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCreateMarketConfirmationModal();
        }
    });
    
    // Cerrar modales con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            closeCreateMarketModal();
            closeCreateMarketConfirmationModal();
        }
    });
    
    // Cargar contadores de productos NUEVOS y SIN_ASIGNAR
    loadProductosNuevosYSinAsignar();
    
    // Cargar contador de ATC4 si el usuario es gerente de producto
    if (window.isGerenteProducto) {
        loadAtc4Count();
    }
});

// ===== FUNCIONES DE NOTIFICACIÓN =====

// Función para mostrar notificaciones de éxito
function showSuccessNotification(message) {
    console.log('SUCCESS:', message);
    showToast(message, 'success');
}

// Función para mostrar notificaciones de error
function showErrorNotification(message) {
    console.log('ERROR:', message);
    showToast(message, 'error');
}

// Función para mostrar notificaciones de advertencia
function showWarningNotification(message) {
    console.log('WARNING:', message);
    showToast(message, 'warning');
}

// ===== FUNCIONES DE REDIRECCIÓN =====

// Función para redirigir al módulo de productos con filtro de mercado
function redirectToProductsWithMarket(marketName) {
    // Guardar el mercado seleccionado en sessionStorage
    sessionStorage.setItem('autoSelectMarket', marketName);
    
    // Redirigir a la página de productos sin parámetros en la URL
    const productosUrl = window.productosIndexRoute;
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtro de marca
function redirectToProductsWithBrand(brandName) {
    // Guardar la marca seleccionada en sessionStorage
    sessionStorage.setItem('autoSelectBrand', brandName);
    
    // Redirigir a la página de productos sin parámetros en la URL
    const productosUrl = window.productosIndexRoute;
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtro de SIN_ASIGNAR y mercado pre-seleccionado
function redirectToProductsWithRestoAndMarket(marketName) {
    // Guardar el mercado seleccionado en sessionStorage
    // Usar 'SIN_ASIGNAR' para el backend
    sessionStorage.setItem('autoSelectMarket', 'SIN_ASIGNAR');
    sessionStorage.setItem('preSelectedMarket', marketName);
    
    // Redirigir a la página de productos
    const productosUrl = window.productosIndexRoute;
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtros de NUEVOS y SIN_ASIGNAR
function redirectToProductosNuevosYSinAsignar() {
    // Guardar en sessionStorage para que el módulo de productos sepa qué filtros aplicar
    sessionStorage.setItem('autoFilterMarkets', JSON.stringify(['NUEVOS', 'SIN_ASIGNAR']));
    
    // Redirigir al módulo de productos
    const productosUrl = window.productosIndexRoute;
    window.location.href = productosUrl;
}

// ===== FUNCIONES PARA CREAR MERCADO =====

// Abrir modal para crear mercado
function openCreateMarketModal() {
    // Limpiar formulario
    document.getElementById('new-market-name').value = '';
    document.getElementById('new-market-note').value = '';
    
    // Mostrar modal
    document.getElementById('create-market-modal').classList.remove('hidden');
    
    // Agregar validación en tiempo real para la nota
    const noteField = document.getElementById('new-market-note');
    const createBtn = document.getElementById('create-market-btn');
    const errorMsg = document.getElementById('create-note-error');
    
    // Validación inicial - deshabilitar botón hasta que se ingrese una nota
    createBtn.classList.add('opacity-50', 'cursor-not-allowed');
    createBtn.disabled = true;
    
    // Validación en tiempo real
    noteField.addEventListener('input', function() {
        const note = this.value.trim();
        
        if (!note) {
            this.classList.add('border-red-500');
            this.classList.remove('border-primary');
            createBtn.classList.add('opacity-50', 'cursor-not-allowed');
            createBtn.disabled = true;
            errorMsg.classList.remove('hidden');
        } else {
            this.classList.remove('border-red-500');
            this.classList.add('border-primary');
            createBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            createBtn.disabled = false;
            errorMsg.classList.add('hidden');
        }
    });
}

// Cerrar modal para crear mercado
function closeCreateMarketModal() {
    document.getElementById('create-market-modal').classList.add('hidden');
}

// Proceder con la creación del mercado
function proceedWithMarketCreation() {
    const marketName = document.getElementById('new-market-name').value.trim();
    const marketNote = document.getElementById('new-market-note').value.trim();
    
    if (!marketName) {
        showErrorNotification('Debe ingresar el nombre del mercado');
        document.getElementById('new-market-name').focus();
        return;
    }
    
    if (!marketNote) {
        showErrorNotification('La nota es obligatoria para crear un mercado');
        document.getElementById('new-market-note').focus();
        return;
    }
    
    // Llenar modal de confirmación
    document.getElementById('confirm-create-market-name').textContent = marketName;
    document.getElementById('confirm-create-market-note').textContent = marketNote;
    document.getElementById('confirm-create-market-note-container').style.display = 'block';
    
    // Cerrar modal de creación y mostrar modal de confirmación
    closeCreateMarketModal();
    document.getElementById('create-market-confirmation-modal').classList.remove('hidden');
}

// Cerrar modal de confirmación
function closeCreateMarketConfirmationModal() {
    document.getElementById('create-market-confirmation-modal').classList.add('hidden');
}

// Proceder con la creación del mercado después de confirmación
function proceedWithMarketCreationAndAssignment() {
    const marketName = document.getElementById('confirm-create-market-name').textContent;
    const marketNote = document.getElementById('confirm-create-market-note').textContent;
    
    // Mostrar loading en el botón
    const btn = document.getElementById('final-create-assign-btn');
    const btnText = btn.querySelector('.btn-text');
    const btnLoading = btn.querySelector('.btn-loading');
    
    btnText.classList.add('hidden');
    btnLoading.classList.remove('hidden');
    btn.disabled = true;
    
    // Crear mercado
    $.ajax({
        url: window.createMarketRoute,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            market_name: marketName,
            market_note: marketNote
        },
        success: function(response) {
            if (response.success) {
                showSuccessNotification(`Mercado "${marketName}" creado exitosamente`);
                
                // Cerrar modal de confirmación
                closeCreateMarketConfirmationModal();
                
                // Recargar la página para mostrar el nuevo mercado
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showErrorNotification(response.message || 'Error al crear mercado');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error al crear mercado';
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

// ===== FUNCIONES DE CARGA DE DATOS =====

// Función para cargar productos NUEVOS y SIN_ASIGNAR
function loadProductosNuevosYSinAsignar() {
    $.ajax({
        url: window.productosNuevosSinAsignarRoute,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success && response.data) {
                const countNuevos = response.data.counts.nuevos;
                const countSinAsignar = response.data.counts.sin_asignar;
                const total = response.data.counts.total;
                
                // Actualizar contador total en el badge (desktop y mobile)
                const desktopBadge = document.getElementById('total-count-notification');
                const mobileBadge = document.getElementById('total-count-notification-mobile');
                
                if (desktopBadge) desktopBadge.textContent = total;
                if (mobileBadge) mobileBadge.textContent = total;
                
                // Actualizar tooltip
                document.getElementById('tooltip-count-nuevos').textContent = countNuevos;
                document.getElementById('tooltip-count-sin-asignar').textContent = countSinAsignar;
                
                // Cambiar color del badge según si hay productos (desktop y mobile)
                const desktopBadgeElement = document.getElementById('total-count-notification')?.parentElement;
                const mobileBadgeElement = document.getElementById('total-count-notification-mobile')?.parentElement;
                
                if (total > 0) {
                    if (desktopBadgeElement) {
                        desktopBadgeElement.classList.remove('bg-blue-600');
                        desktopBadgeElement.classList.add('bg-red-600');
                    }
                    if (mobileBadgeElement) {
                        mobileBadgeElement.classList.remove('bg-blue-600');
                        mobileBadgeElement.classList.add('bg-red-600');
                    }
                } else {
                    if (desktopBadgeElement) {
                        desktopBadgeElement.classList.remove('bg-red-600');
                        desktopBadgeElement.classList.add('bg-blue-600');
                    }
                    if (mobileBadgeElement) {
                        mobileBadgeElement.classList.remove('bg-red-600');
                        mobileBadgeElement.classList.add('bg-blue-600');
                    }
                }
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error al cargar productos NUEVOS y SIN_ASIGNAR:', errorThrown);
        }
    });
}

// Función para cargar el conteo de ATC4
function loadAtc4Count() {
    $.ajax({
        url: window.atc4CountRoute,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success && response.data) {
                const atc4Count = response.data.atc4_count;
                
                // Actualizar contador en el badge (desktop y mobile)
                const desktopAtc4Badge = document.getElementById('atc4-count-badge');
                const mobileAtc4Badge = document.getElementById('atc4-count-badge-mobile');
                
                if (desktopAtc4Badge) desktopAtc4Badge.textContent = atc4Count;
                if (mobileAtc4Badge) mobileAtc4Badge.textContent = atc4Count;
                
                // Actualizar tooltip
                document.getElementById('tooltip-count-atc4').textContent = atc4Count;
                
                // Cambiar color del badge según si hay ATC4 (desktop y mobile)
                const desktopBadgeElement = document.getElementById('atc4-count-badge')?.parentElement;
                const mobileBadgeElement = document.getElementById('atc4-count-badge-mobile')?.parentElement;
                
                if (atc4Count > 0) {
                    if (desktopBadgeElement) {
                        desktopBadgeElement.classList.remove('bg-purple-600');
                        desktopBadgeElement.classList.add('bg-green-600');
                    }
                    if (mobileBadgeElement) {
                        mobileBadgeElement.classList.remove('bg-purple-600');
                        mobileBadgeElement.classList.add('bg-green-600');
                    }
                } else {
                    if (desktopBadgeElement) {
                        desktopBadgeElement.classList.remove('bg-green-600');
                        desktopBadgeElement.classList.add('bg-purple-600');
                    }
                    if (mobileBadgeElement) {
                        mobileBadgeElement.classList.remove('bg-green-600');
                        mobileBadgeElement.classList.add('bg-purple-600');
                    }
                }
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error('Error al cargar conteo de ATC4:', errorThrown);
        }
    });
}

// ===== FUNCIONES DE BÚSQUEDA =====

// Función para limpiar búsqueda
function clearSearch() {
    document.getElementById('search-input').value = '';
    const mobileSearchInput = document.getElementById('search-input-mobile');
    if (mobileSearchInput) mobileSearchInput.value = '';
    // Aquí puedes agregar lógica adicional para limpiar resultados de búsqueda
}
