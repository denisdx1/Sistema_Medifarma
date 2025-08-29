@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-full px-4 sm:px-6 lg:px-8">
        

        <!-- Eliminado: Secciones de búsqueda global y filtros por franquicia -->

        
        <!-- Markets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col space-y-4 sm:flex-row sm:justify-between sm:items-center sm:space-y-0">
                    <div>
                        <h2 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-list mr-2"></i>
                            Listado de Marcas
                        </h2>
                        @auth
                            @if(auth()->user()->idRol == 1)
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-crown text-yellow-500 mr-1"></i>
                                    Vista de administrador - Todas las marcas del sistema
                                </p>
                            @elseif(auth()->user()->idRol == 2)
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-user text-blue-500 mr-1"></i>
                                    Vista de gerente - Solo sus marcas asignadas
                                </p>
                            @endif
                        @endauth
                    </div>
                    
                    <!-- Campo de búsqueda -->
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="search-input"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="block w-80 pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   placeholder="Buscar por marca o mercado..."
                                   autocomplete="off">
                            @if(request('search'))
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" 
                                        onclick="clearSearch()"
                                        class="text-gray-400 hover:text-gray-600"
                                        title="Limpiar búsqueda">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                        

                    </div>
                </div>
            </div>

            @if($markets->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">
                                Marca 
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                                Mercado Asignado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="markets-table-body">
                        @foreach($markets as $market)
                        <tr class="hover:bg-gray-50 market-row" data-market-id="{{ $market->idMercado }}" data-product-code="{{ $market->codigoPresentacion }}">
                            <!-- Marca (Descripción Producto) -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tags text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <button onclick="redirectToProductsWithBrand('{{ $market->marca }}')" 
                                                class="font-medium text-gray-900 hover:text-blue-600 hover:underline transition-colors cursor-pointer"
                                                title="Ver productos de esta marca">
                                            {{ $market->marca }}
                                        </button>
                                        <div class="text-sm text-gray-500">Código: {{ $market->codigoPresentacion }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- Mercado Asignado -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-6 h-6 bg-red-100 rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-store text-red-600 text-xs"></i>
                                    </div>
                                    <button onclick="redirectToProductsWithMarket('{{ $market->mercado }}')" 
                                            class="font-medium text-red-600 hover:text-red-800 hover:underline transition-colors cursor-pointer"
                                            title="Ver productos de este mercado">
                                        {{ $market->mercado }}
                                    </button>
                                </div>
                            </td>
                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $market->estado == 'ACTIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas {{ $market->estado == 'ACTIVO' ? 'fa-play' : 'fa-pause' }} mr-1"></i>
                                    {{ $market->estado }}
                                </span>
                            </td>
                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <!-- Solo Editar Nombre del Mercado -->
                                <button onclick="openEditModal({{ $market->idMercado }}, '{{ addslashes($market->mercado) }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                                        title="Editar nombre del mercado">
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar Mercado
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-3 border-t border-gray-200 bg-gray-50" id="pagination-container">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-600" id="pagination-info">
                        {{ $markets->firstItem() }} - {{ $markets->lastItem() }} de {{ $markets->total() }} marcas
                    </div>
                    <div class="flex items-center space-x-1" id="pagination-links">
                        {{-- Previous Page Link --}}
                        @if ($markets->onFirstPage())
                            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                                <i class="fas fa-chevron-left"></i>
                            </span>
                        @else
                            <a href="{{ $markets->previousPageUrl() }}" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $start = max($markets->currentPage() - 2, 1);
                            $end = min($start + 4, $markets->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        {{-- First page if not in range --}}
                        @if($start > 1)
                            <a href="{{ $markets->url(1) }}" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">1</a>
                            @if($start > 2)
                                <span class="px-1 text-xs text-gray-400">...</span>
                            @endif
                        @endif

                        {{-- Page Numbers --}}
                        @for($page = $start; $page <= $end; $page++)
                            @if ($page == $markets->currentPage())
                                <span class="px-2 py-1 text-xs text-white bg-red-600 rounded font-medium">{{ $page }}</span>
                            @else
                                <a href="{{ $markets->url($page) }}" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">{{ $page }}</a>
                            @endif
                        @endfor

                        {{-- Last page if not in range --}}
                        @if($end < $markets->lastPage())
                            @if($end < $markets->lastPage() - 1)
                                <span class="px-1 text-xs text-gray-400">...</span>
                            @endif
                            <a href="{{ $markets->url($markets->lastPage()) }}" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">{{ $markets->lastPage() }}</a>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($markets->hasMorePages())
                            <a href="{{ $markets->nextPageUrl() }}" class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                                <i class="fas fa-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <div class="p-8 text-center">
                <i class="fas fa-tags text-gray-400 text-4xl mb-4"></i>
                @auth
                    @if(auth()->user()->idRol == 1)
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No se encontraron marcas</h3>
                        <p class="text-gray-600">No hay marcas que coincidan con tu búsqueda</p>
                        <button onclick="clearSearch()" class="mt-3 text-blue-600 hover:text-blue-700 font-medium">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar búsqueda
                        </button>
                    @elseif(auth()->user()->idRol == 2)
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No hay marcas asignadas</h3>
                        <p class="text-gray-600">No hay marcas asignadas que coincidan con tu búsqueda</p>
                        <button onclick="clearSearch()" class="mt-3 text-blue-600 hover:text-blue-700 font-medium">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar búsqueda
                        </button>
                    @endif
                @endauth
            </div>
            @endif
        </div>
    </div>
</div>



<!-- Edit Market Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-red-500 p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Mercado
                </h3>
                <button onclick="closeEditModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Form Content -->
        <form id="edit-market-form" class="p-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-market-id" name="market_id">
            
            <!-- Market Name Field -->
            <div class="mb-4">
                <label for="edit-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Mercado *
                </label>
                <input type="text" 
                       id="edit-market-name" 
                       name="market_name" 
                       class="w-full px-3 py-3 border-2 border-red-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-500 bg-red-50 focus:bg-white"
                       placeholder="Nombre del mercado"
                       required>
            </div>

            <!-- Note Field -->
            <div class="mb-4">
                <label for="edit-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                    Nota *
                </label>
                <textarea id="edit-market-note" 
                          name="market_note" 
                          rows="3"
                          class="w-full px-3 py-3 border-2 border-red-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-500 bg-red-50 focus:bg-white resize-none"
                          placeholder="Nota sobre los cambios..."
                          required></textarea>
                <p class="text-xs text-gray-500 mt-1">Se enviará por email</p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeEditModal()"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button type="button"
                        onclick="openEditConfirmationModal()"
                        id="edit-submit-btn"
                        class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Guardar
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>



<!-- Edit Market Confirmation Modal -->
<div id="edit-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-red-500 p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmar Cambios
                </h3>
                <button onclick="closeEditConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <!-- Comparison -->
            <div class="mb-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                    <p class="text-sm text-red-600 font-medium">Actual:</p>
                    <p class="text-lg font-bold text-red-800" id="confirm-edit-current-name">-</p>
                </div>
                
                <div class="text-center my-2">
                    <i class="fas fa-arrow-down text-red-500 text-xl"></i>
                </div>
                
                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm text-red-600 font-medium">Nuevo:</p>
                    <p class="text-lg font-bold text-red-800" id="confirm-edit-new-name">-</p>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                    <strong>Atención:</strong> Este cambio afectará todos los productos del mercado.
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeEditConfirmationModal()"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button type="button"
                        onclick="confirmEditMarket()"
                        id="confirm-edit-btn"
                        class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i>
                        Confirmar
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Guardando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-20 right-4 z-50"></div>
@endsection



@push('scripts')
<script>
// Define routes for JavaScript  
window.MarketManagementRoutes = {
    index: '{{ route("market-management.index") }}',
    update: '{{ route("market-management.update") }}',
    search: '{{ route("market-management.search") }}',
    allMarketsApi: '{{ route("market-management.all-markets.api") }}'
};

window.csrfToken = '{{ csrf_token() }}';



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
</script>
<script src="{{ asset('js/market-management.js') }}"></script>
@endpush
