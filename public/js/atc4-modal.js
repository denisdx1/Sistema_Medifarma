// ATC4 Modal JavaScript Functions
$(document).ready(function() {
    // Event listeners para cerrar modales al hacer clic fuera
    document.getElementById('atc4-list-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAtc4ListModal();
        }
    });
    
    // Cerrar modales con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            closeAtc4ListModal();
        }
    });
});

// ===== FUNCIONES PARA LA MODAL DE ATC4 =====

// Función para abrir la modal de ATC4
function openAtc4ListModal() {
    const modal = document.getElementById('atc4-list-modal');
    const modalContent = modal.querySelector('.bg-white');
    const overlay = document.getElementById('atc4-overlay');
    
    // Mostrar la modal
    modal.classList.remove('hidden');
    
    // Animar entrada
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
    });
    
    // Cargar la lista de ATC4
    loadAtc4List();
}

// Función para cerrar la modal de ATC4
function closeAtc4ListModal() {
    const modal = document.getElementById('atc4-list-modal');
    const modalContent = modal.querySelector('.bg-white');
    const overlay = document.getElementById('atc4-overlay');
    
    // Animar salida
    overlay.style.opacity = '0';
    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';
    
    // Ocultar modal después de la animación
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Función para cargar la lista de ATC4
function loadAtc4List() {
    // Mostrar loading
    document.getElementById('atc4-loading').classList.remove('hidden');
    document.getElementById('atc4-error').classList.add('hidden');
    document.getElementById('atc4-content').classList.add('hidden');
    
    // Obtener el nombre del gerente del usuario actual
    const gerenteElement = document.getElementById('atc4-modal-gerente');
    if (gerenteElement) {
        gerenteElement.textContent = window.currentUser || 'Gerente';
    }
    
    $.ajax({
        url: window.atc4ListRoute,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success && response.data) {
                displayAtc4List(response.data.atc4_list);
            } else {
                showAtc4Error(response.message || 'Error al cargar la lista de ATC4');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            let errorMessage = 'Error al cargar la lista de ATC4';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showAtc4Error(errorMessage);
        }
    });
}

// Función para mostrar la lista de ATC4
function displayAtc4List(atc4List) {
    const container = document.getElementById('atc4-list-container');
    const totalCount = document.getElementById('atc4-total-count');
    
    // Actualizar contador total
    totalCount.textContent = atc4List.length;
    
    // Limpiar contenedor
    container.innerHTML = '';
    
    if (atc4List.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-secondary">
                <i class="fas fa-info-circle text-3xl mb-3"></i>
                <p>No se encontraron categorías ATC4 para este gerente de producto.</p>
            </div>
        `;
    } else {
        // Crear elementos para cada ATC4
        atc4List.forEach((atc4, index) => {
            const atc4Element = document.createElement('div');
            atc4Element.className = 'bg-white border border-gray-200 rounded-lg p-3 hover:shadow-lg hover:scale-[1.02] hover:border-primary hover:bg-secondary transition-all duration-300 cursor-pointer transform translate-y-4 opacity-0';
            atc4Element.onclick = () => showAtc4Details(atc4);
            
            atc4Element.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">${index + 1}</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">${atc4.descripcionATC4 || 'Sin descripción'}</h4>
                                <p class="text-sm text-gray-500">Código: ${atc4.codigoATC4}</p>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-center">
                            <div class="bg-primary text-white px-2 py-1 rounded-full text-sm font-medium mb-1">
                                ${atc4.cantidad_productos} PRODUCTOS MEDIFARMA
                            </div>
                            <div class="bg-secondary text-white px-3 py-1 rounded-full text-xs font-medium">
                                ${atc4.total_productos} PRODUCTOS MERCADO
                            </div>
                        </div>
                        <div class="mt-2 text-primary text-xs flex items-center justify-center">
                            <i class="fas fa-arrow-right mr-1"></i>
                            Click para ver productos
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(atc4Element);
            
            // Animar entrada escalonada
            setTimeout(() => {
                atc4Element.style.transform = 'translateY(0)';
                atc4Element.style.opacity = '1';
            }, index * 100);
        });
    }
    
    // Ocultar loading y mostrar contenido
    document.getElementById('atc4-loading').classList.add('hidden');
    document.getElementById('atc4-content').classList.remove('hidden');
    
    // Animar entrada del resumen
    setTimeout(() => {
        const summary = document.getElementById('atc4-summary');
        if (summary) {
            summary.style.transform = 'translateY(0)';
            summary.style.opacity = '1';
        }
    }, 100);
}

// Función para mostrar error en la modal de ATC4
function showAtc4Error(message) {
    document.getElementById('atc4-loading').classList.add('hidden');
    document.getElementById('atc4-error-message').textContent = message;
    document.getElementById('atc4-error').classList.remove('hidden');
}

// Función para mostrar detalles de un ATC4 específico
function showAtc4Details(atc4) {
    // Cerrar la modal de ATC4
    closeAtc4ListModal();
    
    // Guardar en sessionStorage para que el módulo de productos sepa que debe filtrar por ATC4
    // Formato: "CÓDIGO - DESCRIPCIÓN" (como está en el módulo de productos)
    const atc4FilterValue = `${atc4.codigoATC4} - ${atc4.descripcionATC4 || 'Sin descripción'}`;
    sessionStorage.setItem('autoSelectAtc4', atc4FilterValue);
    
    // Redirigir inmediatamente al módulo de productos (igual que con mercados)
    const productosUrl = window.productosIndexRoute;
    window.location.href = productosUrl;
}
