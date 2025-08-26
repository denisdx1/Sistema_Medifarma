<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left side - Logo and Main Navigation -->
            <div class="flex items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <img src="{{ asset('images/logo-medifarma-Photoroom.png') }}" alt="Medifarma Logo" class="h-8 w-auto">
                    
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:ml-8 md:flex md:space-x-6">
                    <!-- Market Management -->
                    <a href="{{ route('market-management.index') }}" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('market-management.*') ? 'text-red-700 bg-red-50 border-b-2 border-red-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i class="fas fa-clipboard-list mr-2 {{ request()->routeIs('market-management.*') ? 'text-red-600' : 'text-gray-400' }}"></i>
                        Marcas
                    </a>
                    
                    <!-- Productos Database -->
                    <a href="{{ route('productos.index') }}" 
                       class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('productos.*') ? 'text-green-700 bg-green-50 border-b-2 border-green-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i class="fas fa-database mr-2 {{ request()->routeIs('productos.*') ? 'text-green-600' : 'text-gray-400' }}"></i>
                        Base de Productos
                    </a>
                    
                    <!-- Admin Section - Solo para administradores -->
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('usuarios.index') }}" 
                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200 {{ request()->routeIs('usuarios.*') ? 'text-blue-700 bg-blue-50 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            <i class="fas fa-users mr-2 {{ request()->routeIs('usuarios.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            Gestión Usuarios
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Admin
                            </span>
                        </a>
                    @endif
                </div>
            </div>
            
            <!-- Right side - User info and logout -->
            <div class="flex items-center space-x-4">
                <!-- User Role Info (Desktop) -->
                <div class="hidden lg:flex lg:items-center lg:space-x-4">
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->usuario }}</div>
                        <div class="text-xs text-gray-500">
                            @if(Auth::user()->isAdmin())
                                <span class="text-red-600 font-medium">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Administrador
                                </span>
                            @elseif(Auth::user()->isGerenteProducto())
                                <span class="text-green-600 font-medium">
                                    <i class="fas fa-user-tie mr-1"></i>
                                    Gerente Producto
                                </span>
                            @endif
                            @if(Auth::user()->department)
                                | {{ Auth::user()->department }}
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- User Avatar -->
                <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-sm">
                    {{ substr(Auth::user()->usuario, 0, 1) }}
                </div>
                
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-gray-50 rounded-md transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span class="hidden sm:inline">Salir</span>
                    </button>
                </form>
                
                <!-- Mobile menu button -->
                <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="md:hidden hidden border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <!-- Market Management -->
                <a href="{{ route('market-management.index') }}" 
                   class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('market-management.*') ? 'text-red-700 bg-red-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <i class="fas fa-clipboard-list mr-2 {{ request()->routeIs('market-management.*') ? 'text-red-600' : 'text-gray-400' }}"></i>
                    Gestión Mercados
                </a>
                
                <!-- Productos Database -->
                <a href="{{ route('productos.index') }}" 
                   class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('productos.*') ? 'text-green-700 bg-green-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                    <i class="fas fa-database mr-2 {{ request()->routeIs('productos.*') ? 'text-green-600' : 'text-gray-400' }}"></i>
                    Base de Productos
                </a>
                
                <!-- Admin Section - Solo para administradores -->
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('usuarios.index') }}" 
                       class="block px-3 py-2 text-base font-medium rounded-md {{ request()->routeIs('usuarios.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <i class="fas fa-users mr-2 {{ request()->routeIs('usuarios.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        Gestión Usuarios
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Admin
                        </span>
                    </a>
                @endif
                
                <!-- User info mobile -->
                <div class="px-3 py-2 border-t border-gray-200 mt-3">
                    <div class="text-base font-medium text-gray-900">{{ Auth::user()->usuario }}</div>
                    <div class="text-sm text-gray-500">
                        @if(Auth::user()->isAdmin())
                            <span class="text-red-600 font-medium">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Administrador
                            </span>
                        @elseif(Auth::user()->isGerenteProducto())
                            <span class="text-green-600 font-medium">
                                <i class="fas fa-user-tie mr-1"></i>
                                Gerente Producto
                            </span>
                        @endif
                        @if(Auth::user()->department)
                            <br>{{ Auth::user()->department }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- JavaScript para el menú móvil -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
