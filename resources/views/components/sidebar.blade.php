<aside id="sidebar" class="w-56 bg-white text-gray-800 flex flex-col transition-all duration-300 ease-in-out border-r border-gray-200">
    <!-- Logo and Toggle -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center">
            <img src="{{ asset('images/logo-medifarma-Photoroom.png') }}" alt="Medifarma Logo" id="sidebar-logo" class="h-8 transition-opacity duration-300">
        </div>
        <button id="toggle-sidebar" class="text-gray-500 hover:text-red-600 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 mt-6">
        <p class="sidebar-text px-4 mb-2 text-xs text-gray-400 uppercase tracking-wider">Menu Principal</p>
        <ul>
            <!-- Market Management -->
             <li>
                <a href="{{ route('market-management.index') }}" class="flex items-center px-4 py-2.5 {{ request()->routeIs('market-management.*') ? 'text-red-700 bg-red-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg mx-2 transition-all duration-200">
                    <i class="fas fa-clipboard-list w-6 text-center {{ request()->routeIs('market-management.*') ? 'text-red-600' : 'text-gray-400' }}"></i>
                    <span class="sidebar-text ml-3">Gestión Mercados</span>
                </a>
            </li>
        </ul>

        <!-- Admin Section - Solo para administradores -->
        @if(Auth::user()->isAdmin())
        <div class="mt-6">
            <p class="sidebar-text px-4 mb-2 text-xs text-gray-400 uppercase tracking-wider">Administración</p>
            <ul>
                <!-- User Management -->
                <li>
                    <a href="{{ route('usuarios.index') }}" class="flex items-center px-4 py-2.5 {{ request()->routeIs('usuarios.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }} rounded-lg mx-2 transition-all duration-200">
                        <i class="fas fa-users w-6 text-center {{ request()->routeIs('usuarios.*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="sidebar-text ml-3">Gestión Usuarios</span>
                        <!-- Badge opcional para indicar usuarios pendientes -->
                        <span class="sidebar-text ml-auto">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-shield-alt mr-1"></i>
                                Admin
                            </span>
                        </span>
                    </a>
                </li>

                
            </ul>
        </div>
        @endif
        
        <!-- Role Information -->
        <div class="sidebar-text px-4 mt-6">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Información</p>
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="flex items-center mb-2">
                    <i class="fas fa-user-tag text-red-500 text-xs"></i>
                    <span class="ml-2 text-xs font-medium text-gray-700">Rol Actual</span>
                </div>
                <p class="text-xs text-gray-600">{{ Auth::user()->getRoleDisplayName() }}</p>
                
                @if(Auth::user()->department)
                    <div class="flex items-center mt-2">
                        <i class="fas fa-building text-blue-500 text-xs"></i>
                        <span class="ml-2 text-xs text-gray-600">{{ Auth::user()->department }}</span>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- User Profile / Footer -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                {{ substr(Auth::user()->usuario, 0, 1) }}
            </div>
            <div class="sidebar-text ml-3">
                <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->usuario }}</p>
                <p class="text-xs text-gray-500">
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
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center py-2 bg-gray-100 hover:bg-red-100 rounded-lg group">
                <i class="fas fa-sign-out-alt text-gray-500 group-hover:text-red-500"></i>
                <span class="sidebar-text ml-2 text-sm text-gray-700 group-hover:text-red-500">Salir</span>
            </button>
        </form>
    </div>
</aside>



