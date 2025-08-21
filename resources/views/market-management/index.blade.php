@extends('layouts.app')

@section('title', 'Gestión de Mercados')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-store text-purple-600 mr-2"></i>
                        Gestión de Mercados
                    </h1>
                    <p class="text-gray-600 mt-1">Administra todos los mercados del sistema</p>
                </div>
                <button onclick="openCreateModal()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Mercado
                </button>
            </div>
        </div>

        
        <!-- Markets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-list mr-2"></i>
                        Listado de Mercados
                    </h2>
                    <div class="flex items-center space-x-4">
                        <!-- Búsqueda de texto -->
                        <div class="relative">
                            <input type="text" 
                                   id="search-input"
                                   placeholder="Buscar por nombre, ID o estado..."
                                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 w-80">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button id="clear-search" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 hidden">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if($markets->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre del Mercado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Registro
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="markets-table-body">
                        @foreach($markets as $market)
                        <tr class="hover:bg-gray-50 market-row" data-market-id="{{ $market->idMercado }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $market->idMercado }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-store text-purple-600 text-xs"></i>
                                    </div>
                                    <div class="font-medium text-gray-900">{{ $market->mercado }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($market->fechaRegistro)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $market->estado == 'ACTIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas {{ $market->estado == 'ACTIVO' ? 'fa-play' : 'fa-pause' }} mr-1"></i>
                                    {{ $market->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <!-- Ver Productos Button -->
                                <button onclick="viewMarketProducts({{ $market->idMercado }}, '{{ addslashes($market->mercado) }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200"
                                        title="Ver productos del mercado">
                                    <i class="fas fa-box mr-1"></i>
                                    Productos
                                </button>
                                
                                <!-- Edit Market Button -->
                                <button onclick="openEditModal({{ $market->idMercado }}, '{{ addslashes($market->mercado) }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                                        title="Editar mercado">
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar
                                </button>
                                
                                <!-- Toggle Status Button -->
                                <button onclick="toggleMarketStatus({{ $market->idMercado }}, '{{ addslashes($market->mercado) }}', '{{ $market->estado }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm
                                        {{ $market->estado == 'ACTIVO' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} 
                                        transition-colors duration-200">
                                    <i class="fas {{ $market->estado == 'ACTIVO' ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                                    {{ $market->estado == 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                </button>
                                </span>
                                
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
                        {{ $markets->firstItem() }} - {{ $markets->lastItem() }} de {{ $markets->total() }} mercados
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
                                <span class="px-2 py-1 text-xs text-white bg-purple-600 rounded font-medium">{{ $page }}</span>
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
                <i class="fas fa-store text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay mercados registrados</h3>
                <p class="text-gray-600 mb-4">Comienza creando tu primer mercado</p>
                <button onclick="openCreateModal()" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Mercado
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Market Modal -->
<div id="create-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-plus text-purple-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Crear Nuevo Mercado</h3>
                        <p class="text-sm text-gray-500">Agrega un nuevo mercado al sistema</p>
                    </div>
                </div>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Form -->
            <form id="create-market-form">
                @csrf
                <div class="mb-6">
                    <label for="market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Mercado *
                    </label>
                    <input type="text" 
                           id="market-name" 
                           name="market_name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500"
                           placeholder="Ingresa el nombre del mercado"
                           required>
                    <p class="text-xs text-gray-500 mt-1">El nombre debe ser único en el sistema</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-blue-800 text-sm">
                                <strong>Información:</strong> El mercado será creado en estado "ESPERA" y deberá ser aprobado por un administrador antes de estar disponible.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeCreateModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit"
                            id="create-submit-btn"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200 font-medium">
                        <span class="btn-text">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Creando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Market Modal -->
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-edit text-amber-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Editar Mercado</h3>
                        <p class="text-sm text-gray-500">Modifica el nombre del mercado</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Form -->
            <form id="edit-market-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-market-id" name="market_id">
                
                <div class="mb-6">
                    <label for="edit-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Mercado *
                    </label>
                    <input type="text" 
                           id="edit-market-name" 
                           name="market_name" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500"
                           placeholder="Ingresa el nuevo nombre del mercado"
                           required>
                    <p class="text-xs text-gray-500 mt-1">El nombre debe ser único en el sistema</p>
                </div>
                
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-amber-800 text-sm">
                                <strong>Atención:</strong> El cambio de nombre del mercado afectará todas las referencias existentes en el sistema.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeEditModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit"
                            id="edit-submit-btn"
                            class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition-colors duration-200 font-medium">
                        <span class="btn-text">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/market-management.css') }}">
@endpush

@push('scripts')
<script>
// Define routes for JavaScript
window.MarketManagementRoutes = {
    index: '{{ route("market-management.index") }}',
    create: '{{ route("market-management.create") }}',
    update: '{{ route("market-management.update") }}',
    toggleStatus: '{{ route("market-management.toggle-status") }}',
    search: '{{ route("market-management.search") }}'
};

window.csrfToken = '{{ csrf_token() }}';
</script>
<script src="{{ asset('js/market-management.js') }}"></script>
@endpush
