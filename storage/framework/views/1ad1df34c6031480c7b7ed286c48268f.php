<aside id="sidebar" class="w-56 bg-white text-gray-800 flex flex-col transition-all duration-300 ease-in-out border-r border-gray-200">
    <!-- Logo and Toggle -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center">
            <img src="<?php echo e(asset('images/logo-medifarma-Photoroom.png')); ?>" alt="Medifarma Logo" id="sidebar-logo" class="h-8 transition-opacity duration-300">
        </div>
        <button id="toggle-sidebar" class="text-gray-500 hover:text-purple-600 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 mt-6">
        <p class="sidebar-text px-4 mb-2 text-xs text-gray-400 uppercase tracking-wider">Menu</p>
        <ul>
            <!-- Market Management -->
            <li>
                <a href="<?php echo e(route('market-management.index')); ?>" class="flex items-center px-4 py-2.5 <?php echo e(request()->routeIs('market-management.*') ? 'text-purple-700 bg-purple-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'); ?> rounded-lg mx-2">
                    <i class="fas fa-clipboard-list w-6 text-center <?php echo e(request()->routeIs('market-management.*') ? 'text-purple-600' : 'text-gray-400'); ?>"></i>
                    <span class="sidebar-text ml-3">Gestión Mercados</span>
                    <?php if(Auth::user()->isBusinessIntelligence()): ?>
                        <span class="ml-auto">
                            <i class="fas fa-eye text-xs text-blue-500" title="Solo lectura"></i>
                        </span>
                    <?php endif; ?>
                </a>
            </li>

            
        </ul>
        
        <!-- Role Information -->
        <div class="sidebar-text px-4 mt-6">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Información</p>
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="flex items-center mb-2">
                    <i class="fas fa-user-tag text-purple-500 text-xs"></i>
                    <span class="ml-2 text-xs font-medium text-gray-700">Rol Actual</span>
                </div>
                <p class="text-xs text-gray-600"><?php echo e(Auth::user()->getRoleDisplayName()); ?></p>
                
                <?php if(Auth::user()->department): ?>
                    <div class="flex items-center mt-2">
                        <i class="fas fa-building text-blue-500 text-xs"></i>
                        <span class="ml-2 text-xs text-gray-600"><?php echo e(Auth::user()->department); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- User Profile / Footer -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                <?php echo e(substr(Auth::user()->name, 0, 1)); ?>

            </div>
            <div class="sidebar-text ml-3">
                <p class="text-sm font-semibold text-gray-800"><?php echo e(Auth::user()->name); ?></p>
                <p class="text-xs text-gray-500">
                    <?php if(Auth::user()->isAdmin()): ?>
                        <span class="text-red-600 font-medium">Admin</span>
                    <?php elseif(Auth::user()->isProductManager()): ?>
                        <span class="text-green-600 font-medium">GP</span>
                    <?php elseif(Auth::user()->isBusinessIntelligence()): ?>
                        <span class="text-blue-600 font-medium">BI</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <form method="POST" action="<?php echo e(route('logout')); ?>" class="mt-4">
            <?php echo csrf_field(); ?>
            <button type="submit" class="w-full flex items-center justify-center py-2 bg-gray-100 hover:bg-red-100 rounded-lg group">
                <i class="fas fa-sign-out-alt text-gray-500 group-hover:text-red-500"></i>
                <span class="sidebar-text ml-2 text-sm text-gray-700 group-hover:text-red-500">Salir</span>
            </button>
        </form>
    </div>
</aside>

<?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/components/sidebar.blade.php ENDPATH**/ ?>