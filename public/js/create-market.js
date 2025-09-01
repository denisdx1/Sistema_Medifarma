// Define routes for JavaScript  
window.MarketManagementRoutes = {
    index: '{{ route("market-management.index") }}',
    update: '{{ route("market-management.update") }}',
    search: '{{ route("market-management.search") }}',
    allMarketsApi: '{{ route("market-management.all-markets.api") }}'
};

window.csrfToken = '{{ csrf_token() }}';

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



// Función para redirigir al módulo de productos con filtro de mercado
function redirectToProductsWithMarket(marketName) {
    // Guardar el mercado seleccionado en sessionStorage
    sessionStorage.setItem('autoSelectMarket', marketName);
    
    // Redirigir a la página de productos sin parámetros en la URL
    const productosUrl = '{{ route("productos.index") }}';
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtro de marca
function redirectToProductsWithBrand(brandName) {
    // Guardar la marca seleccionada en sessionStorage
    sessionStorage.setItem('autoSelectBrand', brandName);
    
    // Redirigir a la página de productos sin parámetros en la URL
    const productosUrl = '{{ route("productos.index") }}';
    window.location.href = productosUrl;
}

// Función para redirigir al módulo de productos con filtro de RESTO y mercado pre-seleccionado
function redirectToProductsWithRestoAndMarket(marketName) {
    // Guardar el mercado seleccionado en sessionStorage
    // Usar 'RESTO' para el backend pero se mostrará como 'SIN ASIGNAR' en la interfaz
    sessionStorage.setItem('autoSelectMarket', 'RESTO');
    sessionStorage.setItem('preSelectedMarket', marketName);
    
    // Redirigir a la página de productos
    const productosUrl = '{{ route("productos.index") }}';
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
        url: '/productos/create-market',
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

// Event listeners para cerrar modales al hacer clic fuera
document.addEventListener('DOMContentLoaded', function() {
    // Modal de crear mercado
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
});