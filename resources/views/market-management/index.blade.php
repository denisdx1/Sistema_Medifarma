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

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-chart-bar text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Aprobados</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['aprobados'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pendientes'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-100 rounded-full">
                        <i class="fas fa-play text-emerald-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Activos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['activos'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-pause text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Inactivos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['inactivos'] }}</p>
                    </div>
                </div>
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
                                   placeholder="Buscar por nombre, ID, estado o solicitud..."
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
                                Solicitud
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
                                    {{ $market->solicitud == 'APROBADO' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    <i class="fas {{ $market->solicitud == 'APROBADO' ? 'fa-check' : 'fa-clock' }} mr-1"></i>
                                    {{ $market->solicitud }}
                                </span>
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
                                <button onclick="viewMarketProducts({{ $market->idMercado }}, '{{ $market->mercado }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200"
                                        title="Ver productos del mercado">
                                    <i class="fas fa-box mr-1"></i>
                                    Productos
                                </button>
                                
                                <!-- Edit Market Button (only for approved markets) -->
                                @if($market->solicitud == 'APROBADO')
                                <button onclick="openEditModal({{ $market->idMercado }}, '{{ $market->mercado }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                                        title="Editar mercado">
                                    <i class="fas fa-edit mr-1"></i>
                                    Editar
                                </button>
                                @endif
                                
                                <!-- Toggle Status Button (only for approved markets) -->
                                @if($market->solicitud == 'APROBADO')
                                <button onclick="toggleMarketStatus({{ $market->idMercado }}, '{{ $market->mercado }}', '{{ $market->estado }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm
                                        {{ $market->estado == 'ACTIVO' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} 
                                        transition-colors duration-200">
                                    <i class="fas {{ $market->estado == 'ACTIVO' ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                                    {{ $market->estado == 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                </button>
                                @else
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-100 text-gray-500">
                                    <i class="fas fa-lock mr-1"></i>
                                    Pendiente aprobación
                                </span>
                                @endif
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

@push('scripts')
<script>
// Define routes for JavaScript
window.MarketManagementRoutes = {
    create: '{{ route("market-management.create") }}',
    update: '{{ route("market-management.update") }}',
    toggleStatus: '{{ route("market-management.toggle-status") }}'
};

window.csrfToken = '{{ csrf_token() }}';

// Utility function to safely escape strings for JavaScript
function escapeForJs(str) {
    if (!str) return '';
    return str.replace(/\\/g, '\\\\')
              .replace(/'/g, "\\'")
              .replace(/"/g, '\\"')
              .replace(/\n/g, '\\n')
              .replace(/\r/g, '\\r')
              .replace(/\t/g, '\\t');
}

// Open create modal
function openCreateModal() {
    $('#market-name').val('');
    $('#create-modal').removeClass('hidden');
    $('#market-name').focus();
}

// Close create modal
function closeCreateModal() {
    $('#create-modal').addClass('hidden');
}

// Open edit modal
function openEditModal(marketId, marketName) {
    $('#edit-market-id').val(marketId);
    $('#edit-market-name').val(marketName);
    $('#edit-modal').removeClass('hidden');
    $('#edit-market-name').focus();
}

// Close edit modal
function closeEditModal() {
    $('#edit-modal').addClass('hidden');
}

// Handle create form submission
$('#create-market-form').on('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = $('#create-submit-btn');
    submitBtn.prop('disabled', true);
    submitBtn.find('.btn-text').hide();
    submitBtn.find('.btn-loading').show();
    
    $.post(window.MarketManagementRoutes.create, $(this).serialize())
    .done(function(response) {
        if (response.success) {
            showToast(response.message, 'success');
            closeCreateModal();
            // Redirect to the last page to show the new market
            setTimeout(() => {
                if (response.redirect_to_page) {
                    window.location.href = `{{ route('market-management.index') }}?page=${response.redirect_to_page}`;
                } else {
                    location.reload();
                }
            }, 1500);
        } else {
            showToast(response.message, 'error');
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        if (response && response.errors) {
            const firstError = Object.values(response.errors)[0][0];
            showToast(firstError, 'error');
        } else {
            showToast('Error al crear mercado', 'error');
        }
    })
    .always(function() {
        submitBtn.prop('disabled', false);
        submitBtn.find('.btn-text').show();
        submitBtn.find('.btn-loading').hide();
    });
});

// Handle edit form submission
$('#edit-market-form').on('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = $('#edit-submit-btn');
    submitBtn.prop('disabled', true);
    submitBtn.find('.btn-text').hide();
    submitBtn.find('.btn-loading').show();
    
    $.ajax({
        url: window.MarketManagementRoutes.update,
        method: 'PUT',
        data: $(this).serialize(),
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    })
    .done(function(response) {
        if (response.success) {
            showToast(response.message, 'success');
            closeEditModal();
            // Reload page to show changes
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showToast(response.message, 'error');
        }
    })
    .fail(function(xhr) {
        const response = xhr.responseJSON;
        if (response && response.errors) {
            const firstError = Object.values(response.errors)[0][0];
            showToast(firstError, 'error');
        } else {
            showToast('Error al actualizar mercado', 'error');
        }
    })
    .always(function() {
        submitBtn.prop('disabled', false);
        submitBtn.find('.btn-text').show();
        submitBtn.find('.btn-loading').hide();
    });
});

// Toggle market status
function toggleMarketStatus(marketId, marketName, currentStatus) {
    const action = currentStatus === 'ACTIVO' ? 'desactivar' : 'activar';
    
    if (confirm(`¿Estás seguro de que quieres ${action} el mercado "${marketName}"?`)) {
        $.post(window.MarketManagementRoutes.toggleStatus, {
            market_id: marketId,
            _token: window.csrfToken
        })
        .done(function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                // Reload page to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message, 'error');
            }
        })
        .fail(function(xhr) {
            showToast('Error al cambiar estado del mercado', 'error');
        });
    }
}

// View market products
function viewMarketProducts(marketId, marketName) {
    console.log('viewMarketProducts called with:', {
        marketId: marketId,
        marketName: marketName,
        typeof_marketId: typeof marketId,
        typeof_marketName: typeof marketName
    });
    
    // Validar que el marketId sea válido
    if (!marketId || isNaN(marketId) || marketId <= 0) {
        console.error('Invalid market ID:', marketId);
        showToast('Error: ID de mercado inválido. No se puede cargar la vista de productos.', 'error');
        return;
    }
    
    // Validar que tengamos un nombre de mercado
    if (marketName === undefined || marketName === null) {
        console.warn('Market name is undefined/null, using default');
        marketName = 'Mercado sin nombre';
    }
    
    console.log('Redirecting to products for market:', marketId, marketName);
    
    // Redirect to products view for this market
    window.location.href = `/market-management/market/${marketId}/products`;
}

// Global search functionality with AJAX
let searchTimeout;
let currentSearch = '';

$('#search-input').on('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = $(this).val().trim();
    currentSearch = searchTerm;
    
    searchTimeout = setTimeout(() => {
        if (searchTerm === '') {
            // Si la búsqueda está vacía, restablecer vista original
            resetToOriginalView();
        } else {
            performGlobalSearch(searchTerm);
        }
    }, 500); // Debounce search for 500ms
});

// Function to reset to original view
function resetToOriginalView() {
    window.location.href = '{{ route("market-management.index") }}';
}

// Function to perform global search via AJAX
function performGlobalSearch(searchTerm, page = 1) {
    const searchUrl = '{{ route("market-management.search") }}';
    const params = new URLSearchParams({
        search: searchTerm,
        page: page
    });
    
    // Show loading state
    showLoadingState();
    
    $.ajax({
        url: `${searchUrl}?${params}`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success) {
                updateTableWithResults(data.data);
                updateResultsInfo(data, searchTerm);
                updatePagination(data, searchTerm);
            } else {
                showError('Error en la búsqueda: ' + data.message);
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            showError('Error de conexión durante la búsqueda');
        },
        complete: function() {
            hideLoadingState();
        }
    });
}

// Function to show loading state
function showLoadingState() {
    const tbody = $('table tbody');
    tbody.html(`
        <tr>
            <td colspan="6" class="px-6 py-8 text-center">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600 mr-3"></div>
                    <span class="text-gray-600">Buscando...</span>
                </div>
            </td>
        </tr>
    `);
}

// Function to hide loading state
function hideLoadingState() {
    // This will be handled by updateTableWithResults
}

// Function to update table with search results
function updateTableWithResults(markets) {
    const tbody = $('table tbody');
    
    if (markets.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-search text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg">No se encontraron resultados</p>
                        <p class="text-sm">Intenta con otros términos de búsqueda</p>
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    let tableHTML = '';
    markets.forEach(market => {
        tableHTML += generateMarketRow(market);
    });
    tbody.html(tableHTML);
}

// Function to generate market row HTML
function generateMarketRow(market) {
    // Format date properly
    const formattedDate = formatDate(market.fechaRegistro);
    
    // Escape market name for safe HTML usage
    const escapedMarketName = escapeHtml(market.mercado);
    
    // Generate status badge with icon
    const statusBadge = generateStatusBadge(market.solicitud);
    const stateBadge = generateStateBadge(market.estado);
    
    // Generate action buttons based on market status
    const actionButtons = generateActionButtons(market);
    
    return `
        <tr class="hover:bg-gray-50 market-row" data-market-id="${market.idMercado}">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${market.idMercado}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-store text-purple-600 text-xs"></i>
                    </div>
                    <div class="font-medium text-gray-900">${escapedMarketName}</div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${formattedDate}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${statusBadge}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                ${stateBadge}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                ${actionButtons}
            </td>
        </tr>
    `;
}

// Function to generate status badge with icon
function generateStatusBadge(solicitud) {
    let badgeClass, icon;
    
    switch(solicitud) {
        case 'APROBADO':
            badgeClass = 'bg-green-100 text-green-800';
            icon = 'fa-check';
            break;
        case 'ESPERA':
            badgeClass = 'bg-yellow-100 text-yellow-800';
            icon = 'fa-clock';
            break;
        case 'DENEGADO':
            badgeClass = 'bg-red-100 text-red-800';
            icon = 'fa-times';
            break;
        default:
            badgeClass = 'bg-gray-100 text-gray-800';
            icon = 'fa-question';
    }
    
    return `
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
            <i class="fas ${icon} mr-1"></i>
            ${solicitud}
        </span>
    `;
}

// Function to generate state badge with icon
function generateStateBadge(estado) {
    let badgeClass, icon;
    
    switch(estado) {
        case 'ACTIVO':
            badgeClass = 'bg-emerald-100 text-emerald-800';
            icon = 'fa-play';
            break;
        case 'INACTIVO':
            badgeClass = 'bg-red-100 text-red-800';
            icon = 'fa-pause';
            break;
        default:
            badgeClass = 'bg-gray-100 text-gray-800';
            icon = 'fa-question';
    }
    
    return `
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
            <i class="fas ${icon} mr-1"></i>
            ${estado}
        </span>
    `;
}

// Function to generate action buttons based on market status
function generateActionButtons(market) {
    let buttons = '';
    const escapedMarketName = escapeHtml(market.mercado);
    
    // Ver Productos Button - Always available (con validación)
    if (market.idMercado && !isNaN(market.idMercado)) {
        // Escapar el nombre del mercado de manera segura para JavaScript
        const safeMarketName = escapeForJs(market.mercado || '');
        buttons += `
            <button onclick="viewMarketProducts(${market.idMercado}, '${safeMarketName}')"
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors duration-200"
                    title="Ver productos del mercado">
                <i class="fas fa-box mr-1"></i>
                Productos
            </button>
        `;
    } else {
        buttons += `
            <button disabled 
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-100 text-gray-400 cursor-not-allowed"
                    title="ID de mercado no válido">
                <i class="fas fa-box mr-1"></i>
                Productos
            </button>
        `;
    }
    
    if (market.solicitud === 'APROBADO') {
        // Escapar el nombre del mercado de manera segura para JavaScript
        const safeMarketName = escapeForJs(market.mercado || '');
        
        // Edit Market Button - Only for approved markets
        buttons += `
            <button onclick="openEditModal(${market.idMercado}, '${safeMarketName}')"
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors duration-200"
                    title="Editar mercado">
                <i class="fas fa-edit mr-1"></i>
                Editar
            </button>
        `;
        
        // Toggle Status Button - Only for approved markets
        const isActive = market.estado === 'ACTIVO';
        const toggleClass = isActive ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200';
        const toggleIcon = isActive ? 'fa-pause' : 'fa-play';
        const toggleText = isActive ? 'Desactivar' : 'Activar';
        
        buttons += `
            <button onclick="toggleMarketStatus(${market.idMercado}, '${safeMarketName}', '${market.estado}')"
                    class="inline-flex items-center px-3 py-1 rounded-md text-sm ${toggleClass} transition-colors duration-200">
                <i class="fas ${toggleIcon} mr-1"></i>
                ${toggleText}
            </button>
        `;
    } else {
        // Pending approval message
        buttons += `
            <span class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-100 text-gray-500">
                <i class="fas fa-lock mr-1"></i>
                Pendiente aprobación
            </span>
        `;
    }
    
    return buttons;
}

// Function to escape HTML characters
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Function to update results info
function updateResultsInfo(data, searchTerm) {
    const resultsInfo = $('#pagination-info');
    if (resultsInfo.length) {
        const start = ((data.current_page - 1) * data.per_page) + 1;
        const end = Math.min(data.current_page * data.per_page, data.total);
        
        if (searchTerm) {
            resultsInfo.html(`${start} - ${end} de ${data.total} resultados para "${searchTerm}"`);
        } else {
            resultsInfo.html(`${start} - ${end} de ${data.total} mercados`);
        }
    }
}

// Function to update pagination
function updatePagination(data, searchTerm) {
    const paginationContainer = $('#pagination-links');
    
    if (!paginationContainer.length || data.last_page <= 1) {
        // Hide pagination if only one page or container not found
        $('#pagination-container').hide();
        return;
    }
    
    // Show pagination container
    $('#pagination-container').show();
    
    let paginationHTML = '';
    
    // Previous Page Link
    if (data.current_page <= 1) {
        paginationHTML += `
            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
        `;
    } else {
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page - 1}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </a>
        `;
    }

    // Calculate page range
    const start = Math.max(data.current_page - 2, 1);
    const end = Math.min(start + 4, data.last_page);
    const adjustedStart = Math.max(end - 4, 1);

    // First page if not in range
    if (adjustedStart > 1) {
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', 1); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">1</a>
        `;
        if (adjustedStart > 2) {
            paginationHTML += '<span class="px-1 text-xs text-gray-400">...</span>';
        }
    }

    // Page Numbers
    for (let page = adjustedStart; page <= end; page++) {
        if (page == data.current_page) {
            paginationHTML += `
                <span class="px-2 py-1 text-xs text-white bg-purple-600 rounded font-medium">${page}</span>
            `;
        } else {
            paginationHTML += `
                <a href="#" onclick="performGlobalSearch('${searchTerm}', ${page}); return false;" 
                   class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">${page}</a>
            `;
        }
    }

    // Last page if not in range
    if (end < data.last_page) {
        if (end < data.last_page - 1) {
            paginationHTML += '<span class="px-1 text-xs text-gray-400">...</span>';
        }
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.last_page}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">${data.last_page}</a>
        `;
    }

    // Next Page Link
    if (data.current_page >= data.last_page) {
        paginationHTML += `
            <span class="px-2 py-1 text-xs text-gray-400 bg-gray-200 rounded cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
        `;
    } else {
        paginationHTML += `
            <a href="#" onclick="performGlobalSearch('${searchTerm}', ${data.current_page + 1}); return false;" 
               class="px-2 py-1 text-xs text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </a>
        `;
    }
    
    paginationContainer.html(paginationHTML);
}

// Function to show error messages
function showError(message) {
    alert(message); // Simple alert for now, can be improved with toast notifications
}
  

// Close modal when clicking outside
$(document).on('click', '#create-modal, #edit-modal', function(e) {
    if (e.target === this) {
        if (this.id === 'create-modal') {
            closeCreateModal();
        } else if (this.id === 'edit-modal') {
            closeEditModal();
        }
    }
});

// Close modal with Escape key
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!$('#create-modal').hasClass('hidden')) {
            closeCreateModal();
        } else if (!$('#edit-modal').hasClass('hidden')) {
            closeEditModal();
        }
    }
});

// Toast function
function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast toast-${type} p-4 mb-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300">
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `);
    
    $('#toast-container').append(toast);
    
    setTimeout(() => {
        toast.removeClass('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        toast.addClass('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}
</script>

<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    min-width: 300px;
}

.toast-success {
    background-color: #d1fae5;
    border: 1px solid #86efac;
    color: #065f46;
}

.toast-error {
    background-color: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.toast-info {
    background-color: #dbeafe;
    border: 1px solid #93c5fd;
    color: #1e40af;
}

.btn-loading {
    display: none;
}

/* Modal animations */
#create-modal {
    backdrop-filter: blur(4px);
}

#create-modal .bg-white {
    animation: modalSlideIn 0.3s ease-out;
}

#create-modal.hidden .bg-white {
    animation: modalSlideOut 0.3s ease-in;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes modalSlideOut {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
}

/* Table hover effects */
.market-row:hover {
    background-color: #f9fafb;
}

/* Button focus states */
button:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

button:focus-visible {
    outline: 2px solid #7c3aed;
    outline-offset: 2px;
}

/* Pagination styles */
.pagination-container a,
.pagination-container span {
    transition: all 0.2s ease-in-out;
    min-width: 28px;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pagination-container a:hover {
    transform: translateY(-1px);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Compact pagination */
.pagination-container .px-2 {
    min-width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Active page styling */
.pagination-container .bg-purple-600 {
    box-shadow: 0 1px 3px rgba(124, 58, 237, 0.3);
}

/* Disabled pagination buttons */
.pagination-container .cursor-not-allowed {
    opacity: 0.5;
}

/* Ellipsis styling */
.pagination-container .text-gray-400 {
    font-weight: bold;
    user-select: none;
}

/* Search input focus */
#search-input:focus {
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    transform: scale(1.02);
    transition: all 0.2s ease-in-out;
}

/* Products button special styling */
.products-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    transition: all 0.3s ease;
}

.products-btn:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

/* Statistics cards hover effect */
.stats-card {
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Loading state for buttons */
.btn-loading-state {
    position: relative;
    overflow: hidden;
}

.btn-loading-state::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: loading-shine 1.5s infinite;
}

@keyframes loading-shine {
    0% { left: -100%; }
    100% { left: 100%; }
}
</style>
</script>
@endpush
