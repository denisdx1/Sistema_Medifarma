

<?php $__env->startSection('title', 'Configuración de Notificaciones'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-primary to-secondary">
    <!-- Header -->
    <div class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-bell text-3xl text-primary"></i>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-3xl font-bold text-gray-900">Configuración de Notificaciones</h1>
                        <p class="text-gray-600">Gestiona los correos que recibirán notificaciones del sistema</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="openConfigModal()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                        <i class="fas fa-cog mr-2"></i>
                        Configurar Notificaciones
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Status Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Estado de las Notificaciones</h2>
                    <p class="text-gray-600">Configuración actual del sistema de notificaciones</p>
                </div>
                <div id="notification-status" class="text-right">
                    <!-- Status will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Configuration Info -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de Configuración</h2>
            <div id="configuration-info" class="space-y-4">
                <!-- Configuration details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Configuración de Notificaciones -->
<div id="configModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden flex items-center justify-center p-4" style="z-index: 999999;">
    <div class="relative mx-auto border w-full max-w-4xl shadow-2xl rounded-lg bg-white">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-bell text-primary mr-2"></i>
                Configurar Notificaciones
            </h3>
            <button onclick="closeConfigModal()" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="notificationForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="config_id" name="config_id">
                
                <!-- Estado de las notificaciones -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="notifications_active" name="notifications_active" class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700">Activar notificaciones del sistema</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Cuando esté desactivado, no se enviarán notificaciones por correo</p>
                </div>

                <!-- Correos configurados actualmente -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correos configurados actualmente
                    </label>
                    <div id="current-emails" class="space-y-2">
                        <!-- Current emails will be loaded here -->
                    </div>
                </div>

                <!-- Agregar nuevo correo -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Agregar nuevo correo
                    </label>
                    <div class="flex space-x-2">
                        <input type="email" 
                               id="new_email" 
                               name="new_email" 
                               placeholder="ejemplo@correo.com"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <button type="button" 
                                onclick="addEmail()"
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                            <i class="fas fa-plus mr-1"></i>
                            Agregar
                        </button>
                    </div>
                </div>

                <!-- Lista de correos de usuarios activos -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correos de usuarios activos del sistema
                    </label>
                    <div class="bg-gray-50 rounded-lg p-4 max-h-60 overflow-y-auto">
                        <div id="active-users-emails" class="space-y-2">
                            <!-- Active users emails will be loaded here -->
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Estos son los correos de usuarios activos que pueden recibir notificaciones</p>
                </div>

                <!-- Correos seleccionados para notificaciones -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Correos que recibirán notificaciones
                    </label>
                    <div id="selected-emails" class="space-y-2">
                        <!-- Selected emails will be shown here -->
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200">
            <button onclick="closeConfigModal()" 
                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md font-medium transition-colors duration-200">
                Cancelar
            </button>
            <button onclick="saveConfiguration()" 
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                <i class="fas fa-save mr-2"></i>
                Guardar Configuración
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
        <span class="text-gray-700">Procesando...</span>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let currentConfiguration = null;
let selectedEmails = new Set();
let activeUserEmails = [];

// Cargar configuración al iniciar la página
document.addEventListener('DOMContentLoaded', function() {
    loadConfiguration();
    loadActiveUserEmails();
});

// Cargar configuración actual
function loadConfiguration() {
    showLoading();
    
    fetch('/notificaciones/configuracion')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                currentConfiguration = data.data;
                displayConfiguration(data.data);
            } else {
                showErrorNotification('No hay configuración de notificaciones activa');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showErrorNotification('Error al cargar la configuración');
        });
}

// Cargar correos de usuarios activos
function loadActiveUserEmails() {
    fetch('/notificaciones/usuarios-activos')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                activeUserEmails = data.data;
                displayActiveUserEmails(data.data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Mostrar configuración en la interfaz
function displayConfiguration(config) {
    // Actualizar estado
    const statusDiv = document.getElementById('notification-status');
    if (config.activo) {
        statusDiv.innerHTML = `
            <div class="flex items-center">
                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                <span class="text-green-700 font-medium">Activas</span>
            </div>
        `;
    } else {
        statusDiv.innerHTML = `
            <div class="flex items-center">
                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                <span class="text-red-700 font-medium">Inactivas</span>
            </div>
        `;
    }

    // Actualizar información de configuración
    const configInfo = document.getElementById('configuration-info');
    configInfo.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium text-gray-900">Estado</h3>
                <p class="text-sm text-gray-600">${config.activo ? 'Activas' : 'Inactivas'}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium text-gray-900">Total de Correos</h3>
                <p class="text-sm text-gray-600">${config.correosDestinatarios.length}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium text-gray-900">Última Actualización</h3>
                <p class="text-sm text-gray-600">${config.fechaActualizacion ? new Date(config.fechaActualizacion).toLocaleDateString() : 'N/A'}</p>
            </div>
        </div>
        <div class="mt-4">
            <h3 class="font-medium text-gray-900 mb-2">Correos Configurados:</h3>
            <div class="flex flex-wrap gap-2">
                ${config.correosDestinatarios.map(email => `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary text-white">
                        ${email}
                    </span>
                `).join('')}
            </div>
        </div>
    `;

    // Actualizar correos seleccionados
    selectedEmails = new Set(config.correosDestinatarios);
    updateSelectedEmailsDisplay();
}

// Mostrar correos de usuarios activos
function displayActiveUserEmails(emails) {
    const container = document.getElementById('active-users-emails');
    container.innerHTML = emails.map(email => `
        <label class="flex items-center">
            <input type="checkbox" 
                   value="${email}" 
                   ${selectedEmails.has(email) ? 'checked' : ''}
                   onchange="toggleEmail('${email}', this.checked)"
                   class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
            <span class="ml-2 text-sm text-gray-700">${email}</span>
        </label>
    `).join('');
}

// Agregar nuevo correo
function addEmail() {
    const emailInput = document.getElementById('new_email');
    const email = emailInput.value.trim();
    
    if (!email) {
        showErrorNotification('Por favor ingrese un correo válido');
        return;
    }
    
    if (!isValidEmail(email)) {
        showErrorNotification('Por favor ingrese un correo válido');
        return;
    }
    
    if (selectedEmails.has(email)) {
        showErrorNotification('Este correo ya está en la lista');
        return;
    }
    
    selectedEmails.add(email);
    emailInput.value = '';
    updateSelectedEmailsDisplay();
    showSuccessNotification('Correo agregado exitosamente');
}

// Alternar correo seleccionado
function toggleEmail(email, isChecked) {
    if (isChecked) {
        selectedEmails.add(email);
    } else {
        selectedEmails.delete(email);
    }
    updateSelectedEmailsDisplay();
}

// Actualizar display de correos seleccionados
function updateSelectedEmailsDisplay() {
    const container = document.getElementById('selected-emails');
    container.innerHTML = Array.from(selectedEmails).map(email => `
        <div class="flex items-center justify-between bg-gray-50 p-2 rounded-lg">
            <span class="text-sm text-gray-700">${email}</span>
            <button onclick="removeEmail('${email}')" 
                    class="text-red-500 hover:text-red-700 transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

// Remover correo
function removeEmail(email) {
    selectedEmails.delete(email);
    updateSelectedEmailsDisplay();
    
    // Actualizar checkbox si existe
    const checkbox = document.querySelector(`input[value="${email}"]`);
    if (checkbox) {
        checkbox.checked = false;
    }
}

// Guardar configuración
function saveConfiguration() {
    if (selectedEmails.size === 0) {
        showErrorNotification('Debe seleccionar al menos un correo');
        return;
    }
    
    const isActive = document.getElementById('notifications_active').checked;
    const emails = Array.from(selectedEmails);
    
    showLoading();
    
    const url = currentConfiguration ? 
        `/notificaciones/${currentConfiguration.idConfiguracion}` : 
        '/notificaciones';
    
    const method = currentConfiguration ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            correos: emails
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showSuccessNotification(data.message);
            closeConfigModal();
            loadConfiguration(); // Recargar configuración
        } else {
            showErrorNotification(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showErrorNotification('Error al guardar la configuración');
    });
}

// Abrir modal de configuración
function openConfigModal() {
    document.getElementById('configModal').classList.remove('hidden');
    
    // Cargar configuración actual en el modal
    if (currentConfiguration) {
        document.getElementById('config_id').value = currentConfiguration.idConfiguracion;
        document.getElementById('notifications_active').checked = currentConfiguration.activo;
    }
    
    // Actualizar display de correos seleccionados
    updateSelectedEmailsDisplay();
}

// Cerrar modal de configuración
function closeConfigModal() {
    document.getElementById('configModal').classList.add('hidden');
    document.getElementById('notificationForm').reset();
    document.getElementById('config_id').value = '';
}

// Validar email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Mostrar notificación de éxito
function showSuccessNotification(message) {
    if (typeof showToast === 'function') {
        showToast(message, 'success');
    } else {
        alert('Éxito: ' + message);
    }
}

// Mostrar notificación de error
function showErrorNotification(message) {
    if (typeof showToast === 'function') {
        showToast(message, 'error');
    } else {
        alert('Error: ' + message);
    }
}

// Mostrar loading
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

// Ocultar loading
function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/notificaciones/index.blade.php ENDPATH**/ ?>