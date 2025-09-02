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
            } else if (campoEmail) {
                campoEmail.value = '';
            }
        });
    }

    // Evento para agregar correo con Enter en el campo de nuevo correo
    const campoNuevoCorreo = document.getElementById('nuevoCorreo');
    if (campoNuevoCorreo) {
        campoNuevoCorreo.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                agregarCorreo();
            }
        });
    }

    // Cerrar modales con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalCrear();
            cerrarModalEditar();
            cerrarModalDesactivar();
            cerrarModalConfiguracionNotificaciones();
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
    
    // Generar email automáticamente basado en el campo de login
    const campoLogin = document.getElementById('crear_login');
    const campoEmail = document.getElementById('crear_email');
    if (campoLogin && campoEmail) {
        // Generar email automáticamente si ya hay un valor en el campo de login
        if (campoLogin.value.trim() !== '') {
            campoEmail.value = campoLogin.value.trim() + '@medifarma.com.pe';
        }
        
        // Agregar evento para generar email en tiempo real
        campoLogin.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                campoEmail.value = this.value.trim() + '@medifarma.com.pe';
            } else {
                campoEmail.value = '';
            }
        });
    }
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
    
    // Limpiar el campo de email también
    const campoEmail = document.getElementById('crear_email');
    if (campoEmail) {
        campoEmail.value = '';
    }
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

// Función de loading eliminada - no se usa más



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

// ===== FUNCIONES PARA MODAL DE CONFIGURACIÓN DE NOTIFICACIONES =====

// Variables globales para configuración de notificaciones
let configuracionEditandoId = null;
let listaCorreosConfigurados = [];

// Abrir modal de configuración de notificaciones
async function abrirModalConfiguracionNotificaciones() {
    try {
        // Obtener configuración actual
        const response = await fetch('/usuarios/configuracion-notificaciones', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        
        if (data.success) {
            // Debug: mostrar información en consola
            if (data.debug) {
                console.log('=== DEBUG CONFIGURACIÓN NOTIFICACIONES ===');
                console.log('Total registros en tabla:', data.debug.total_registros);
                console.log('Total configuraciones activas:', data.debug.total_activas);
                console.log('Total correos únicos combinados:', data.debug.total_correos_unicos);
                console.log('Todos los correos combinados:', data.debug.todos_correos_combinados);
                console.log('Todas las configuraciones:', data.debug.todas);
                console.log('Configuraciones activas:', data.debug.activas);
                
                // Mostrar detalle de cada configuración
                if (data.debug.detalle_todas && data.debug.detalle_todas.length > 0) {
                    console.log('=== DETALLE DE TODAS LAS CONFIGURACIONES ===');
                    data.debug.detalle_todas.forEach((config, index) => {
                        console.log(`Configuración ${index + 1}:`, config);
                    });
                    console.log('==============================================');
                }
                
                if (data.debug.error) {
                    console.error('Error en debug:', data.debug.error);
                }
                console.log('==========================================');
            }
            
            if (data.configuracion) {
                // Editar configuración existente
                configuracionEditandoId = data.configuracion.idConfiguracion;
                document.getElementById('config_idConfiguracion').value = data.configuracion.idConfiguracion;
                document.getElementById('config_activo').checked = data.configuracion.activo == 1;
                
                console.log('Configuración encontrada:', data.configuracion);
                console.log('Correos destinatarios:', data.configuracion.correosDestinatarios);
                
                // Cargar lista de correos
                cargarListaCorreos(data.configuracion.correosDestinatarios);
                
                // Mostrar botón de eliminar
                document.getElementById('btnEliminarConfiguracion').classList.remove('hidden');
                document.getElementById('btnGuardarTexto').textContent = 'Actualizar';
                document.getElementById('modalConfiguracionTitulo').textContent = 'Editar Configuración de Notificaciones';
            } else {
                // Crear nueva configuración
                configuracionEditandoId = null;
                document.getElementById('config_idConfiguracion').value = '';
                document.getElementById('config_activo').checked = true;
                
                console.log('No se encontró configuración, creando nueva');
                
                // Limpiar lista de correos
                listaCorreosConfigurados = [];
                renderizarListaCorreos();
                
                // Ocultar botón de eliminar
                document.getElementById('btnEliminarConfiguracion').classList.add('hidden');
                document.getElementById('btnGuardarTexto').textContent = 'Guardar';
                document.getElementById('modalConfiguracionTitulo').textContent = 'Nueva Configuración de Notificaciones';
            }
        } else {
            mostrarToast('Error al cargar la configuración', 'error');
            return;
        }
        
        // Abrir modal
        const modal = document.getElementById('modalConfiguracionNotificaciones');
        modal.classList.remove('hidden');
        modal.classList.add('show');
        modal.style.zIndex = '999999';
        modal.style.position = 'fixed';
        
        // Limpiar campo de nuevo correo
        document.getElementById('nuevoCorreo').value = '';
        
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al cargar la configuración', 'error');
    }
}

// Cerrar modal de configuración de notificaciones
function cerrarModalConfiguracionNotificaciones() {
    const modal = document.getElementById('modalConfiguracionNotificaciones');
    modal.classList.add('hidden');
    modal.classList.remove('show');
    configuracionEditandoId = null;
    listaCorreosConfigurados = [];
    limpiarErroresConfiguracion();
}

// Limpiar errores de configuración
function limpiarErroresConfiguracion() {
    const errorDiv = document.getElementById('error_config_correosDestinatarios');
    if (errorDiv) {
        errorDiv.textContent = '';
        errorDiv.classList.add('hidden');
    }
}

// Agregar nuevo correo a la lista
function agregarCorreo() {
    const inputCorreo = document.getElementById('nuevoCorreo');
    const correo = inputCorreo.value.trim();
    
    // Validar que el correo no esté vacío
    if (!correo) {
        mostrarToast('Por favor ingrese un correo', 'warning');
        inputCorreo.focus();
        return;
    }
    
    // Validar formato de email
    if (!esEmailValido(correo)) {
        mostrarToast('Por favor ingrese un correo válido', 'error');
        inputCorreo.focus();
        return;
    }
    
    // Verificar que el correo no esté duplicado
    if (listaCorreosConfigurados.some(item => item.correo.toLowerCase() === correo.toLowerCase())) {
        mostrarToast('Este correo ya está en la lista', 'warning');
        inputCorreo.focus();
        return;
    }
    
    // Agregar correo a la lista
    listaCorreosConfigurados.push({
        correo: correo,
        activo: true
    });
    
    // Limpiar campo y renderizar lista
    inputCorreo.value = '';
    renderizarListaCorreos();
    
    mostrarToast('Correo agregado exitosamente', 'success');
}

// Eliminar correo de la lista
function eliminarCorreo(index) {
    if (confirm('¿Está seguro de eliminar este correo de la lista?')) {
        listaCorreosConfigurados.splice(index, 1);
        renderizarListaCorreos();
        mostrarToast('Correo eliminado de la lista', 'success');
    }
}

// Cambiar estado activo/inactivo de un correo
function cambiarEstadoCorreo(index) {
    listaCorreosConfigurados[index].activo = !listaCorreosConfigurados[index].activo;
    renderizarListaCorreos();
}

// Cargar lista de correos desde la configuración existente
function cargarListaCorreos(correosString) {
    console.log('=== CARGAR LISTA CORREOS ===');
    console.log('String recibido:', correosString);
    console.log('Tipo de dato:', typeof correosString);
    
    if (!correosString) {
        console.log('No hay string de correos, lista vacía');
        listaCorreosConfigurados = [];
        renderizarListaCorreos();
        return;
    }
    
    // Separar correos por comas y crear objetos
    const correosArray = correosString.split(',').map(c => c.trim()).filter(c => c);
    console.log('Array de correos después de split:', correosArray);
    
    listaCorreosConfigurados = correosArray.map(correo => ({
        correo: correo,
        activo: true
    }));
    
    console.log('Lista final de correos configurados:', listaCorreosConfigurados);
    console.log('=====================================');
    
    renderizarListaCorreos();
}

// Renderizar la lista de correos en el DOM
function renderizarListaCorreos() {
    console.log('=== RENDERIZAR LISTA CORREOS ===');
    console.log('Lista a renderizar:', listaCorreosConfigurados);
    console.log('Cantidad de correos:', listaCorreosConfigurados.length);
    
    const contenedor = document.getElementById('listaCorreos');
    console.log('Contenedor encontrado:', contenedor);
    
    if (listaCorreosConfigurados.length === 0) {
        console.log('Lista vacía, mostrando mensaje de "no hay correos"');
        contenedor.innerHTML = `
            <div class="text-center text-gray-500 py-4">
                <i class="fas fa-inbox text-2xl mb-2"></i>
                <p>No hay correos configurados</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    listaCorreosConfigurados.forEach((item, index) => {
        html += `
            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors duration-200">
                <div class="flex items-center space-x-3">
                    <input type="checkbox" 
                           id="correo_${index}" 
                           ${item.activo ? 'checked' : ''}
                           onchange="cambiarEstadoCorreo(${index})"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="correo_${index}" class="text-sm font-medium text-gray-700 ${item.activo ? '' : 'line-through text-gray-500'}">
                        ${item.correo}
                    </label>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs px-2 py-1 rounded-full ${item.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'}">
                        ${item.activo ? 'Activo' : 'Inactivo'}
                    </span>
                    <button type="button" 
                            onclick="eliminarCorreo(${index})"
                            class="text-red-600 hover:text-red-800 transition-colors duration-200"
                            title="Eliminar correo">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    console.log('HTML generado:', html);
    contenedor.innerHTML = html;
    console.log('Lista renderizada exitosamente');
    console.log('=====================================');
}

// Validar formato de email
function esEmailValido(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Obtener correos activos para enviar
function obtenerCorreosActivos() {
    return listaCorreosConfigurados
        .filter(item => item.activo)
        .map(item => item.correo);
}

// Manejar envío del formulario de configuración
document.addEventListener('DOMContentLoaded', function() {
    const formConfiguracion = document.getElementById('formConfiguracionNotificaciones');
    if (formConfiguracion) {
        formConfiguracion.addEventListener('submit', manejarConfiguracionNotificaciones);
    }
});

// Manejar configuración de notificaciones
async function manejarConfiguracionNotificaciones(e) {
    e.preventDefault();
    
    // Validar que haya al menos un correo configurado
    if (listaCorreosConfigurados.length === 0) {
        mostrarToast('Debe agregar al menos un correo antes de guardar', 'error');
        return;
    }
    
    // Validar que haya al menos un correo activo
    const correosActivos = obtenerCorreosActivos();
    if (correosActivos.length === 0) {
        mostrarToast('Debe tener al menos un correo activo para recibir notificaciones', 'error');
        return;
    }
    
    const btn = document.getElementById('btnGuardarConfiguracion');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        
        limpiarErroresConfiguracion();
        
        // Convertir lista de correos a string separado por comas
        const correosString = correosActivos.join(', ');
        
        const formData = new FormData();
        formData.append('correosDestinatarios', correosString);
        formData.append('activo', document.getElementById('config_activo').checked ? '1' : '0');
        
        let url = '/usuarios/configuracion-notificaciones';
        let method = 'POST';
        
        if (configuracionEditandoId) {
            // Actualizar
            url = `/usuarios/configuracion-notificaciones/${configuracionEditandoId}`;
            method = 'PUT';
            formData.append('_method', 'PUT');
        }
        
        const response = await fetch(url, {
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
            cerrarModalConfiguracionNotificaciones();
            setTimeout(() => location.reload(), 1500);
        } else {
            if (response.status === 422) {
                // Errores de validación
                if (data.errors) {
                    mostrarErroresValidacionConfiguracion(data.errors);
                }
            } else {
                mostrarToast(data.message || 'Error al guardar la configuración', 'error');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al guardar la configuración', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Mostrar errores de validación de configuración
function mostrarErroresValidacionConfiguracion(errores) {
    Object.keys(errores).forEach(campo => {
        const errorDiv = document.getElementById(`error_config_${campo}`);
        if (errorDiv) {
            errorDiv.textContent = errores[campo][0];
            errorDiv.classList.remove('hidden');
        }
    });
}

// Eliminar configuración de notificaciones
async function eliminarConfiguracionNotificaciones() {
    if (!configuracionEditandoId) return;
    
    if (!confirmarAccion('¿Está seguro de eliminar esta configuración de notificaciones? Esta acción no se puede deshacer.')) {
        return;
    }
    
    const btn = document.getElementById('btnEliminarConfiguracion');
    const originalText = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Eliminando...';
        
        const response = await fetch(`/usuarios/configuracion-notificaciones/${configuracionEditandoId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            mostrarToast(data.message, 'success');
            cerrarModalConfiguracionNotificaciones();
            setTimeout(() => location.reload(), 1500);
        } else {
            mostrarToast(data.message || 'Error al eliminar la configuración', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarToast('Error de conexión al eliminar la configuración', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}