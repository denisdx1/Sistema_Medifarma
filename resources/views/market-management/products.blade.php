@extends('layouts.app')

@section('page-title', 'Productos del Mercado')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <a href="{{ route('market-management.index') }}" 
                           class="text-purple-600 hover:text-purple-700 mr-3 transition-colors">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            Productos del Mercado
                        </h1>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-store text-purple-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-lg lg:text-xl font-medium text-gray-800">{{ $market->mercado ?? 'Mercado no encontrado' }}</p>
                            
                        </div>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 min-w-[180px]">
                        <p class="text-sm text-gray-600">Total de productos</p>
                        <p class="text-2xl font-bold text-blue-600" id="total-products">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-pills mr-2"></i>
                        Listado de Productos
                    </h2>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-600" id="products-info">
                            Cargando productos...
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto relative table-container">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="absolute inset-0 bg-white bg-opacity-95 flex items-center justify-center z-10 hidden">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <span class="text-gray-700 font-medium" id="loading-text">Cargando productos...</span>
                        <p class="text-sm text-gray-500 mt-1" id="loading-subtext">Por favor espere</p>
                        <div class="mt-3 text-xs text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            <span id="loading-timer">0s</span>
                        </div>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 products-table">
                    <thead class="bg-gray-50 sticky top-0 z-20">
                        <tr>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden sm:inline">SKU</span>
                                <span class="sm:hidden">Código</span>
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descripción
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden lg:inline">M/G</span>
                                <span class="lg:hidden hidden sm:inline">Marca</span>
                                <span class="sm:hidden">M</span>
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                <span class="hidden lg:inline">Ét/Po</span>
                                <span class="lg:hidden">Ético</span>
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Molécula
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                <span class="hidden lg:inline">Fuente</span>
                                <span class="lg:hidden">F</span>
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center hidden md:table-cell">
                                <span class="hidden lg:inline">CodFF3</span>
                                <span class="lg:hidden">FF3</span>
                            </th>
                            <th class="px-3 sm:px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center hidden md:table-cell">
                                <span class="hidden lg:inline">CodATC4</span>
                                <span class="lg:hidden">ATC4</span>
                            </th>
                            <th class="px-1 sm:px-2 lg:px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="hidden sm:inline">Acc</span>
                                <span class="sm:hidden">•••</span>
                            </th>
                                <span class="hidden sm:inline">Acc</span>
                                <span class="sm:hidden">•••</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                        <!-- Products will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Loading State (backup) -->
            <div id="loading-state" class="p-8 text-center hidden">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                    <span class="text-gray-600">Cargando productos...</span>
                </div>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="p-8 text-center hidden">
                <i class="fas fa-box-open text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay productos</h3>
                <p class="text-gray-600">Este mercado no tiene productos asociados</p>
            </div>

            <!-- Pagination -->
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50" id="pagination-container">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div id="pagination-info" class="text-sm text-gray-600 text-center sm:text-left">
                        Cargando productos...
                    </div>
                    <div id="pagination-controls" class="flex items-center justify-center sm:justify-end space-x-2">
                        <!-- Pagination buttons will be generated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Quitar Producto -->
<div id="remove-product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <!-- Icono de advertencia -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <!-- Título -->
            <h3 class="text-lg font-medium text-gray-900 mb-2">Cambiar Estado a ESPERA</h3>
            <!-- Mensaje -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-2">
                    ¿Estás seguro de que deseas cambiar el estado del siguiente producto?
                </p>
                <div class="bg-gray-50 p-3 rounded-lg mb-4">
                    <p class="font-medium text-gray-900" id="remove-product-name">Nombre del producto</p>
                    <p class="text-sm text-gray-600">Código: <span id="remove-product-code"></span></p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Esto realizará la siguiente acción:
                    </h4>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• El estado del producto cambiará a <strong>ESPERA</strong></li>
                    </ul>
                </div>
            </div>
            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button onclick="closeRemoveProductModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-remove-btn" onclick="changeStatusMarket()" 
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="btn-text" >Cambiar a ESPERA</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Mercado -->
<div id="change-market-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mr-3">
                        <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Cambiar Mercado</h3>
                </div>
                <button onclick="closeChangeMarketModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Información del producto -->
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="font-medium text-gray-900" id="change-product-name">Nombre del producto</p>
                <p class="text-sm text-gray-600">Código: <span id="change-product-code"></span></p>
                <p class="text-sm text-gray-600">Mercado actual: <span id="current-market-name"></span></p>
            </div>
            
            <!-- Selector de nuevo mercado -->
            <div class="mb-6">
                <label for="market-search-input" class="block text-sm font-medium text-gray-700 mb-2">
                    Seleccionar nuevo mercado:
                </label>
                <div class="relative">
                    <!-- Input de búsqueda -->
                    <div class="relative">
                        <input type="text" 
                               id="market-search-input" 
                               class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Buscar mercado..."
                               autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <!-- Indicador de loading en el input -->
                        <div id="search-loading" class="absolute inset-y-0 right-8 flex items-center hidden">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        </div>
                    </div>
                    
                    <!-- Lista desplegable de resultados -->
                    <div id="markets-dropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                        <!-- Loading state -->
                        <div id="dropdown-loading" class="p-3 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Cargando mercados...
                        </div>
                        
                        <!-- No results -->
                        <div id="dropdown-no-results" class="p-3 text-center text-gray-500 hidden">
                            <i class="fas fa-search mr-2"></i>
                            No se encontraron mercados
                        </div>
                        
                        <!-- Markets list -->
                        <div id="markets-list" class="hidden">
                            <!-- Market items will be populated here -->
                        </div>
                    </div>
                    
                    <!-- Campo oculto para almacenar el ID del mercado seleccionado -->
                    <input type="hidden" id="selected-market-id" value="">
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Escribe para buscar mercados activos disponibles
                </p>
                
                <!-- Market seleccionado -->
                <div id="selected-market-display" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-store text-blue-600 mr-2"></i>
                            <span id="selected-market-name" class="text-sm font-medium text-blue-800"></span>
                        </div>
                        <button type="button" onclick="clearSelectedMarket()" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-end space-x-4">
                <button onclick="closeChangeMarketModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-change-btn" onclick="changeProductMarket()"
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                    <span class="btn-text">Cambiar Mercado</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Cambiando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Debug Info: Market ID = {{ $market->idMercado ?? 'NULL' }}, Market Name = {{ $market->mercado ?? 'NULL' }} -->
<script>
// Configurar variables globales para el archivo externo
window.marketId = {{ $market->idMercado ?? 'null' }};
window.marketData = @json($market ?? null);
</script>
<script src="{{ asset('js/market-products.js') }}"></script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/market-products.css') }}">
@endpush
