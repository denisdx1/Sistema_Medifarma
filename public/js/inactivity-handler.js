/**
 * Inactivity Handler - Auto logout after 1 minute of inactivity
 */
class InactivityHandler {
    constructor() {
        this.timeout = 60000; // 1 minuto en milisegundos
        this.warningTime = 45000; // 45 segundos (15 segundos antes del logout)
        this.inactivityTimer = null;
        this.warningTimer = null;
        this.countdownInterval = null;
        this.isWarningShown = false;
        this.lastActivity = Date.now();

        this.init();
    }

    init() {
        // Eventos que resetean el timer
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

        events.forEach(event => {
            document.addEventListener(event, () => this.resetTimer(), true);
        });

        // Iniciar el timer
        this.startTimer();

        // Verificar actividad cada 10 segundos
        setInterval(() => this.checkActivity(), 10000);
    }

    resetTimer() {
        this.lastActivity = Date.now();

        // Limpiar timers existentes
        if (this.inactivityTimer) {
            clearTimeout(this.inactivityTimer);
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }

        // Ocultar warning si está visible
        if (this.isWarningShown) {
            this.hideWarning();
        }

        // Reiniciar timers
        this.startTimer();
    }

    startTimer() {
        // Timer de advertencia (45 segundos)
        this.warningTimer = setTimeout(() => {
            this.showWarning();
        }, this.warningTime);

        // Timer de logout (60 segundos)
        this.inactivityTimer = setTimeout(() => {
            this.logout();
        }, this.timeout);
    }

    checkActivity() {
        const now = Date.now();
        const timeSinceLastActivity = now - this.lastActivity;

        // Si han pasado más de 60 segundos sin actividad
        if (timeSinceLastActivity >= this.timeout) {
            this.logout();
        }
        // Si han pasado más de 45 segundos, mostrar advertencia
        else if (timeSinceLastActivity >= this.warningTime && !this.isWarningShown) {
            this.showWarning();
        }
    }

    showWarning() {
        this.isWarningShown = true;

        // Cambiar el indicador a amarillo
        const indicator = document.getElementById('inactivity-indicator');
        if (indicator) {
            indicator.className = 'w-2 h-2 bg-yellow-400 rounded-full mr-1 animate-pulse';
        }

        // Crear modal de advertencia
        const warningModal = document.createElement('div');
        warningModal.id = 'inactivity-warning';
        warningModal.innerHTML = `
            <div class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4 text-center">
        <div class="text-red-500 text-4xl mb-4">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Sesión por expirar
        </h3>
        <p class="text-gray-600 mb-4">
            Su sesión expirará en <span id="countdown">15</span> segundos por inactividad.
        </p>
        <div class="flex justify-center space-x-3">
            <button id="extend-session" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-secondary transition-colors">
                Mantener sesión
            </button>
            <button id="logout-now" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                Cerrar sesión
            </button>
        </div>
    </div>
</div>

        `;

        document.body.appendChild(warningModal);

        // Contador regresivo
        let countdown = 15;
        const countdownElement = document.getElementById('countdown');
        this.countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                this.clearCountdown();
                this.logout();
            }
        }, 1000);

        // Event listeners para los botones
        document.getElementById('extend-session').addEventListener('click', () => {
            this.clearCountdown();
            this.resetTimer();
        });

        document.getElementById('logout-now').addEventListener('click', () => {
            this.clearCountdown();
            this.logout();
        });
    }

    clearCountdown() {
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    }

    hideWarning() {
        this.isWarningShown = false;

        // Limpiar el contador regresivo
        this.clearCountdown();

        // Restaurar el indicador a verde
        const indicator = document.getElementById('inactivity-indicator');
        if (indicator) {
            indicator.className = 'w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse';
        }

        const warningModal = document.getElementById('inactivity-warning');
        if (warningModal) {
            warningModal.remove();
        }
    }

    logout() {
        // Limpiar timers
        if (this.inactivityTimer) {
            clearTimeout(this.inactivityTimer);
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }

        // Limpiar contador regresivo
        this.clearCountdown();

        // Ocultar warning si está visible
        this.hideWarning();

        // Mostrar mensaje de logout
        this.showLogoutMessage();

        // Hacer logout después de 2 segundos
        setTimeout(() => {
            window.location.href = '/auto-logout?inactivity=1';
        }, 2000);
    }

    showLogoutMessage() {
        const logoutModal = document.createElement('div');
        logoutModal.id = 'logout-message';
        logoutModal.innerHTML = `
            <div class="fixed inset-0 bg-white/10 backdrop-blur-sm flex items-center justify-center z-[9999]">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4 text-center">
        <div class="text-blue-500 text-4xl mb-4">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            Sesión cerrada
        </h3>
        <p class="text-gray-600">
            Su sesión ha sido cerrada por inactividad. Redirigiendo al login...
        </p>
    </div>
</div>


        `;

        document.body.appendChild(logoutModal);
    }
}

// Inicializar el handler cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    // Solo inicializar si el usuario está autenticado
    if (document.body.classList.contains('authenticated') ||
        document.querySelector('[data-authenticated="true"]') ||
        window.location.pathname !== '/login') {
        new InactivityHandler();
    }
});

// También inicializar si se carga después del DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        if (document.body.classList.contains('authenticated') ||
            document.querySelector('[data-authenticated="true"]') ||
            window.location.pathname !== '/login') {
            new InactivityHandler();
        }
    });
} else {
    if (document.body.classList.contains('authenticated') ||
        document.querySelector('[data-authenticated="true"]') ||
        window.location.pathname !== '/login') {
        new InactivityHandler();
    }
}
