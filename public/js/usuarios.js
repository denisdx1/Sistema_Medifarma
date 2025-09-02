// Variables globales
let usuarioEditandoId = null;
let usuarioDesactivandoId = null;
let usuarioDesactivandoEstado = null;

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    inicializarEventos();
});

// Inicializar eventos
function inicializarEventos() {
    // Eventos de formularios
    document.getElementById('formCrearUsuario').addEventListener('submit', manejarCreacionUsuario);
    document.getElementById('formEditarUsuario').addEventListener('submit', manejarEdicionUsuario);

    // Evento para auto-rellenar email cuando se escribe en el campo usuario
    const campoLogin = document.getElementById('crear_login');
    if (campoLogin) {
        campoLogin.addEventListener('input', function() {
            const campoEmail = document.getElementById('crear_email');
            if (campoEmail && this.value.trim() !== '') {
                campoEmail.value = this.value.trim() + '@medifarma.com.pe';
            }
        });
    }

    // Cerrar modales con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalCrear();
            cerrarModalEditar();
            cerrarModalDesactivar();
        }
    });
}

// ===== FUNCIONES DE MODALES =====

// Abrir modal crear
function abrirModalCrear() {
    limpiarFormularioCrear();
    const modal = document.getElementById('modalCrearUsuario');
    modal.classList.remove('hidden');
    modal.classList.add('show');
    // Asegurar z-index máximo por encima de todo
    modal.style.zIndex = '999999';
    modal.style.position = 'fixed';
    document.getElementById('crear_usuario').focus();
}

// Cerrar modal crear
function cerrarModalCrear() {
    const modal = document.getElementById('modalCrearUsuario');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    limpiarFormularioCrear();
}

// Abrir modal editar
async function abrirModalEditar(idUsuario) {
    try {
        mostrarLoading(true);
        
        const response = await fetch(`/usuarios/${idUsuario}/datos`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            usuarioEditandoId = idUsuario;
            cargarDatosEnFormularioEditar(data.usuario);
            const modal = document.getElementById('modalEditarUsuario');
            modal.classList.remove('hidden');
            modal.classList.add('show');
            // Asegurar z-index máximo por encima de todo
            modal.style.zIndex = '999999';
            modal.style.position = 'fixed';
            document.getElementById('editar_usuario').focus();
        } else {
            mostrarToast('Error al cargar los datos del usuario', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al cargar los datos', 'error');
    } finally {
        mostrarLoading(false);
    }
}

// Cerrar modal editar
function cerrarModalEditar() {
    const modal = document.getElementById('modalEditarUsuario');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    limpiarFormularioEditar();
    usuarioEditandoId = null;
}

// ===== FUNCIONES DE FORMULARIOS =====

// Manejar creación de usuario
async function manejarCreacionUsuario(e) {
    e.preventDefault();
    
    // Validar que al menos una franquicia esté seleccionada
    const checkboxes = document.querySelectorAll('input[name="idFranquicias[]"]:checked');
    if (checkboxes.length === 0) {
        mostrarToast('Debe seleccionar al menos una franquicia', 'error');
        return;
    }
    
    const btn = document.getElementById('btnCrearUsuario');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';
        
        limpiarErrores('crear');
        
        const formData = new FormData(e.target);
        
        const response = await fetch('/usuarios', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            cerrarModalCrear();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (response.status === 422) {
                // Errores de validación - data ya contiene la respuesta JSON
                if (data.errors) {
                    mostrarErroresValidacion(data.errors, 'crear');
                }
            } else {
                mostrarToast(data.message || 'Error al crear el usuario', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al crear el usuario', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Manejar edición de usuario
async function manejarEdicionUsuario(e) {
    e.preventDefault();
    
    const btn = document.getElementById('btnEditarUsuario');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
        
        limpiarErrores('editar');
        
        // Validar que al menos una franquicia esté seleccionada
        const franquiciasSeleccionadas = document.querySelectorAll('input[name="idFranquicias[]"]:checked');
        if (franquiciasSeleccionadas.length === 0) {
            mostrarToast('Debe seleccionar al menos una franquicia', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }
        
        const formData = new FormData(e.target);
        
        const response = await fetch(`/usuarios/${usuarioEditandoId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            cerrarModalEditar();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (response.status === 422) {
                // Errores de validación
                const errores = await response.json();
                if (errores.errors) {
                    mostrarErroresValidacion(errores.errors, 'editar');
                }
            } else {
                mostrarToast(data.message || 'Error al actualizar el usuario', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al actualizar el usuario', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// ===== FUNCIONES AUXILIARES =====

// Limpiar formulario crear
function limpiarFormularioCrear() {
    document.getElementById('formCrearUsuario').reset();
    // Desmarcar todos los checkboxes de franquicias
    document.querySelectorAll('input[name="idFranquicias[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    limpiarErrores('crear');
}

// Limpiar formulario editar
function limpiarFormularioEditar() {
    document.getElementById('formEditarUsuario').reset();
    limpiarErrores('editar');
}

// Cargar datos en formulario editar
function cargarDatosEnFormularioEditar(usuario) {
    // Función auxiliar para establecer valor de forma segura
    function setValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value || '';
        } else {
            console.warn(`Elemento con ID '${elementId}' no encontrado`);
        }
    }
    
    setValue('editar_idUsuario', usuario.idUsuario);
    setValue('editar_usuario', usuario.usuario);
    setValue('editar_login', usuario.login);
    setValue('editar_email', usuario.email);
    setValue('editar_idRol', usuario.idRol);
    setValue('editar_idEstado', usuario.idEstado);
    
    // Limpiar todas las franquicias primero
    const checkboxes = document.querySelectorAll('input[name="idFranquicias[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Marcar las franquicias del usuario
    if (usuario.idFranquicias && Array.isArray(usuario.idFranquicias)) {
        usuario.idFranquicias.forEach(idFranquicia => {
            const checkbox = document.getElementById(`editar_franquicia_${idFranquicia}`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
}

// Limpiar errores de validación
function limpiarErrores(prefijo) {
    const campos = ['usuario', 'login', 'email', 'password', 'password_confirmation', 'idRol', 'idFranquicias', 'idEstado'];
    campos.forEach(campo => {
        const errorDiv = document.getElementById(`error_${prefijo}_${campo}`);
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.add('hidden');
        }
        
        // Para checkboxes múltiples, buscar todos los elementos con ese nombre
        if (campo === 'idFranquicias') {
            const checkboxes = document.querySelectorAll(`input[name="${campo}[]"]`);
            checkboxes.forEach(checkbox => {
                checkbox.classList.remove('border-red-500');
            });
        } else {
            const input = document.getElementById(`${prefijo}_${campo}`);
            if (input) {
                input.classList.remove('border-red-500');
            }
        }
    });
}

// Mostrar errores de validación
function mostrarErroresValidacion(errores, prefijo) {
    Object.keys(errores).forEach(campo => {
        const errorDiv = document.getElementById(`error_${prefijo}_${campo}`);
        
        if (errorDiv) {
            errorDiv.textContent = errores[campo][0];
            errorDiv.classList.remove('hidden');
            
            // Para checkboxes múltiples, aplicar estilo a todos los checkboxes
            if (campo === 'idFranquicias') {
                const checkboxes = document.querySelectorAll(`input[name="${campo}[]"]`);
                checkboxes.forEach(checkbox => {
                    checkbox.classList.add('border-red-500');
                });
            } else {
                const input = document.getElementById(`${prefijo}_${campo}`);
                if (input) {
                    input.classList.add('border-red-500');
                }
            }
        }
    });
}

// Toggle password visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Mostrar/ocultar loading
function mostrarLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.remove('hidden');
    } else {
        overlay.classList.add('hidden');
    }
}



// Mostrar toast notifications
function mostrarToast(mensaje, tipo = 'info') {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg text-white z-50 transform translate-x-full transition-transform duration-300`;
    
    // Aplicar color según tipo
    switch(tipo) {
        case 'success':
            toast.classList.add('bg-green-500');
            break;
        case 'error':
            toast.classList.add('bg-red-500');
            break;
        case 'warning':
            toast.classList.add('bg-yellow-500');
            break;
        default:
            toast.classList.add('bg-blue-500');
    }
    
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${tipo === 'success' ? 'check' : tipo === 'error' ? 'times' : tipo === 'warning' ? 'exclamation' : 'info'}-circle mr-2"></i>
            <span>${mensaje}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Mostrar toast
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Ocultar después de 4 segundos
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 4000);
}



// ===== FUNCIONES PARA MODAL DE DESACTIVACIÓN =====

// Abrir modal de desactivación
function abrirModalDesactivar(idUsuario, nombreUsuario, estadoActual) {
    usuarioDesactivandoId = idUsuario;
    usuarioDesactivandoEstado = estadoActual;
    
    const modal = document.getElementById('modalDesactivarUsuario');
    const titulo = document.getElementById('modalDesactivarTitulo');
    const mensaje = document.getElementById('modalDesactivarMensaje');
    const descripcion = document.getElementById('modalDesactivarDescripcion');
    const icono = document.getElementById('modalDesactivarIcono');
    const btnTexto = document.getElementById('btnConfirmarTexto');
    const btnConfirmar = document.getElementById('btnConfirmarDesactivar');
    
    if (estadoActual == 1) {
        // Desactivar usuario
        titulo.textContent = 'Desactivar Usuario';
        mensaje.textContent = `¿Está seguro de desactivar al usuario "${nombreUsuario}"?`;
        descripcion.textContent = 'El usuario no podrá acceder al sistema hasta que sea reactivado.';
        icono.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-red-100';
        icono.innerHTML = '<i class="fas fa-user-times text-red-600 text-2xl"></i>';
        btnTexto.textContent = 'Desactivar';
        btnConfirmar.className = 'px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2';
    } else {
        // Activar usuario
        titulo.textContent = 'Activar Usuario';
        mensaje.textContent = `¿Está seguro de activar al usuario "${nombreUsuario}"?`;
        descripcion.textContent = 'El usuario podrá acceder nuevamente al sistema.';
        icono.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-red-100';
        icono.innerHTML = '<i class="fas fa-user-check text-red-600 text-2xl"></i>';
        btnTexto.textContent = 'Activar';
        btnConfirmar.className = 'px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2';
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('show');
    modal.style.zIndex = '999999';
    modal.style.position = 'fixed';
}

// Cerrar modal de desactivación
function cerrarModalDesactivar() {
    const modal = document.getElementById('modalDesactivarUsuario');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    usuarioDesactivandoId = null;
    usuarioDesactivandoEstado = null;
}

// Confirmar desactivación/activación de usuario
async function confirmarDesactivarUsuario() {
    if (!usuarioDesactivandoId) return;
    
    const btn = document.getElementById('btnConfirmarDesactivar');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        const response = await fetch(`/usuarios/${usuarioDesactivandoId}/toggle-estado`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        if (response.ok) {
            const accion = usuarioDesactivandoEstado == 1 ? 'desactivado' : 'activado';
            mostrarToast(`Usuario ${accion} exitosamente`, 'success');
            cerrarModalDesactivar();
            setTimeout(() => location.reload(), 1500);
        } else {
            const data = await response.json();
            mostrarToast(data.message || 'Error al cambiar el estado del usuario', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al cambiar el estado del usuario', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Confirmación para acciones peligrosas
function confirmarAccion(mensaje) {
    return confirm(mensaje);
}