<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left side - Logo and Main Navigation -->
            <div class="flex items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <img src="<?php echo e(asset('images/logo-medifarma-Photoroom.png')); ?>" alt="Medifarma Logo" class="h-8 w-auto">
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:ml-8 md:flex md:space-x-6">
                    <!-- Market Management -->
                    <a href="<?php echo e(route('market-management.index')); ?>" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 <?php echo e(request()->routeIs('market-management.*') ? 'text-primary bg-secondary-light border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                        <i class="fas fa-clipboard-list mr-2 <?php echo e(request()->routeIs('market-management.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                        Marcas
                    </a>
                    
                    <!-- Productos Database -->
                    <a href="<?php echo e(route('productos.index')); ?>" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 <?php echo e(request()->routeIs('productos.*') ? 'text-primary bg-secondary-light border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                        <i class="fas fa-database mr-2 <?php echo e(request()->routeIs('productos.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                        Base de Productos
                    </a>
                    
                    <!-- Admin Section - Solo para administradores -->
                    <?php if(Auth::user()->isAdmin()): ?>
                        <a href="<?php echo e(route('usuarios.index')); ?>" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 <?php echo e(request()->routeIs('usuarios.*') ? 'text-primary bg-secondary-light border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                            <i class="fas fa-users mr-2 <?php echo e(request()->routeIs('usuarios.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                            Gestión Usuarios
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-purple text-primary">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Admin
                            </span>
                        </a>
                        
                        <a href="<?php echo e(route('logs.index')); ?>" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 <?php echo e(request()->routeIs('logs.*') ? 'text-primary bg-secondary-light border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                            <i class="fas fa-clipboard-list mr-2 <?php echo e(request()->routeIs('logs.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                            Logs de Sistema
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-purple text-primary">
                                <i class="fas fa-eye mr-1"></i>
                                Audit
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right side - User info and logout -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- User Role Info (Desktop) -->
                <div class="hidden lg:flex lg:items-center lg:space-x-4">
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900"><?php echo e(Auth::user()->usuario); ?></div>
                        <div class="text-xs text-gray-500">
                            <?php if(Auth::user()->isAdmin()): ?>
                                <span class="text-primary font-medium">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Administrador
                                </span>
                            <?php elseif(Auth::user()->isGerenteProducto()): ?>
                                <span class="text-secondary font-medium">
                                    <i class="fas fa-user-tie mr-1"></i>
                                    Gerente Producto
                                </span>
                            <?php endif; ?>
                            <?php if(Auth::user()->department): ?>
                                | <?php echo e(Auth::user()->department); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- User Avatar (Mobile) -->
                <div class="md:hidden flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
                        <?php echo e(substr(Auth::user()->usuario, 0, 1)); ?>

                    </div>
                    <div class="text-left">
                        <div class="text-xs font-medium text-gray-900 truncate max-w-20"><?php echo e(Auth::user()->usuario); ?></div>
                        <div class="text-xs text-gray-500">
                            <?php if(Auth::user()->isAdmin()): ?>
                                <span class="text-primary font-medium">Admin</span>
                            <?php elseif(Auth::user()->isGerenteProducto()): ?>
                                <span class="text-secondary font-medium">Gerente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
               
                <!-- Logout Button -->
                <form method="POST" action="<?php echo e(route('logout')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex items-center px-2 sm:px-3 py-2 text-sm font-medium text-gray-600 hover:text-primary hover:bg-primary rounded-md transition-colors duration-200">
                        <i class="fas fa-sign-out-alt sm:mr-2"></i>
                        <span class="hidden sm:inline">Salir</span>
                    </button>
                </form>
                
                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-bars text-lg" id="mobile-menu-icon"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200 bg-white shadow-lg">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <!-- Market Management -->
                <a href="<?php echo e(route('market-management.index')); ?>" 
                   class="flex items-center px-3 py-3 text-base font-medium rounded-lg transition-colors duration-200 <?php echo e(request()->routeIs('market-management.*') ? 'text-primary bg-secondary-light border-l-4 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                    <i class="fas fa-clipboard-list mr-3 text-lg <?php echo e(request()->routeIs('market-management.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                    <div>
                        <div class="font-medium">Marcas</div>
                        <div class="text-xs text-gray-500">Gestión de marcas</div>
                    </div>
                </a>
                
                <!-- Productos Database -->
                <a href="<?php echo e(route('productos.index')); ?>" 
                   class="flex items-center px-3 py-3 text-base font-medium rounded-lg transition-colors duration-200 <?php echo e(request()->routeIs('productos.*') ? 'text-primary bg-secondary-light border-l-4 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                    <i class="fas fa-database mr-3 text-lg <?php echo e(request()->routeIs('productos.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                    <div>
                        <div class="font-medium">Base de Productos</div>
                        <div class="text-xs text-gray-500">Catálogo completo</div>
                    </div>
                </a>
                
                <!-- Admin Section - Solo para administradores -->
                <?php if(Auth::user()->isAdmin()): ?>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Administración
                        </div>
                        
                        <a href="<?php echo e(route('usuarios.index')); ?>" 
                           class="flex items-center px-3 py-3 text-base font-medium rounded-lg transition-colors duration-200 <?php echo e(request()->routeIs('usuarios.*') ? 'text-primary bg-secondary-light border-l-4 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                            <i class="fas fa-users mr-3 text-lg <?php echo e(request()->routeIs('usuarios.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                            <div>
                                <div class="font-medium">Gestión Usuarios</div>
                                <div class="text-xs text-gray-500">Administrar usuarios</div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-purple text-primary mt-1">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Admin
                                </span>
                            </div>
                        </a>
                        
                        <a href="<?php echo e(route('logs.index')); ?>" 
                           class="flex items-center px-3 py-3 text-base font-medium rounded-lg transition-colors duration-200 <?php echo e(request()->routeIs('logs.*') ? 'text-primary bg-secondary-light border-l-4 border-primary' : 'text-gray-600 hover:text-primary hover:bg-secondary-lighter'); ?>">
                            <i class="fas fa-clipboard-list mr-3 text-lg <?php echo e(request()->routeIs('logs.*') ? 'text-primary' : 'text-gray-400'); ?>"></i>
                            <div>
                                <div class="font-medium">Logs de Sistema</div>
                                <div class="text-xs text-gray-500">Auditoría del sistema</div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-purple text-primary mt-1">
                                    <i class="fas fa-eye mr-1"></i>
                                    Audit
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- User info mobile -->
                <div class="border-t border-gray-200 mt-3 pt-3">
                    <div class="px-3 py-2 bg-secondary-lighter rounded-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg mr-3">
                                <?php echo e(substr(Auth::user()->usuario, 0, 1)); ?>

                            </div>
                            <div>
                                <div class="text-base font-medium text-gray-900"><?php echo e(Auth::user()->usuario); ?></div>
                                <div class="text-sm text-gray-500">
                                    <?php if(Auth::user()->isAdmin()): ?>
                                        <span class="text-primary font-medium">
                                            <i class="fas fa-shield-alt mr-1"></i>
                                            Administrador
                                        </span>
                                    <?php elseif(Auth::user()->isGerenteProducto()): ?>
                                        <span class="text-secondary font-medium">
                                            <i class="fas fa-user-tie mr-1"></i>
                                            Gerente Producto
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if(Auth::user()->department): ?>
                                    <div class="text-xs text-gray-400 mt-1"><?php echo e(Auth::user()->department); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- JavaScript para el menú móvil mejorado -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuIcon = document.getElementById('mobile-menu-icon');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const isHidden = mobileMenu.classList.contains('hidden');
            
            if (isHidden) {
                // Mostrar menú
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('animate-slideDown');
                mobileMenuIcon.className = 'fas fa-times text-lg';
            } else {
                // Ocultar menú
                mobileMenu.classList.add('animate-slideUp');
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                    mobileMenu.classList.remove('animate-slideUp');
                }, 200);
                mobileMenuIcon.className = 'fas fa-bars text-lg';
            }
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('animate-slideUp');
                    setTimeout(() => {
                        mobileMenu.classList.add('hidden');
                        mobileMenu.classList.remove('animate-slideUp');
                    }, 200);
                    mobileMenuIcon.className = 'fas fa-bars text-lg';
                }
            }
        });
        
        // Cerrar menú al cambiar de ruta
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                    mobileMenuIcon.className = 'fas fa-bars text-lg';
                }, 100);
            });
        });
    }
});
</script>

<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

.animate-slideDown {
    animation: slideDown 0.2s ease-out;
}

.animate-slideUp {
    animation: slideUp 0.2s ease-out;
}
</style>
<?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/components/navbar.blade.php ENDPATH**/ ?>