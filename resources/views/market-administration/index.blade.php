@extends('layouts.app')

@section('title', 'Administración de Mercados')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">
                        <i class="fas fa-shield-alt text-purple-600 mr-2"></i>
                        Administración de Mercados
                    </h1>
                    <p class="text-gray-600 mt-1">Gestiona la aprobación y estado de mercados pendientes</p>
                </div>
                
                <!-- Stats Cards -->
                <div class="flex space-x-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-yellow-600 mr-2"></i>
                            <div>
                                <p class="text-yellow-800 font-medium">{{ $pendingMarkets->count() }}</p>
                                <p class="text-yellow-600 text-sm">Pendientes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        @if($pendingMarkets->count() > 0)
        <!-- Pending Markets Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">
                    <i class="fas fa-list text-purple-600 mr-2"></i>
                    Mercados Pendientes de Aprobación
                </h2>
            </div>
            
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
                                Fecha de Solicitud
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado Actual
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Solicitud
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingMarkets as $market)
                        <tr class="hover:bg-gray-50" data-market-id="{{ $market->idMercado }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $market->idMercado }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $market->mercado }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($market->fechaRegistro)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $estado = $estados->firstWhere('idEstado', $market->idEstado);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $estado && $estado->estado == 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $estado->estado ?? 'Sin Estado' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $market->solicitud }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="openApproveModal({{ $market->idMercado }}, '{{ $market->mercado }}')"
                                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md transition-colors duration-200">
                                    <i class="fas fa-check mr-1"></i>
                                    Aprobar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <!-- No Pending Markets -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <i class="fas fa-check-circle text-green-500 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No hay mercados pendientes</h3>
            <p class="text-gray-600">Todos los mercados han sido procesados.</p>
        </div>
        @endif
    </div>
</div>

<!-- Approve Modal -->
<div id="approve-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Aprobar Mercado</h3>
                        <p class="text-sm text-gray-500">Confirma la aprobación del mercado</p>
                    </div>
                </div>
                <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="mb-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-green-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-green-800 font-medium">¿Estás seguro de que quieres aprobar este mercado?</p>
                            <p class="text-green-700 mt-1">
                                Mercado: <strong id="approve-market-name" class="text-green-900"></strong>
                            </p>
                            <p class="text-green-600 text-sm mt-2">
                                Una vez aprobado, el mercado estará disponible para su uso en el sistema.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeApproveModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                <button id="confirm-approve-btn"
                        onclick="confirmApproval()"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors duration-200 font-medium">
                    <span class="btn-text">
                        <i class="fas fa-check mr-2"></i>
                        Aprobar Mercado
                    </span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Aprobando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<script>
// Define routes for JavaScript
window.MarketAdminRoutes = {
    approve: '{{ route("market-administration.approve") }}',
    changeStatus: '{{ route("market-administration.change-status") }}'
};

window.csrfToken = '{{ csrf_token() }}';

// Variables globales para el modal
let currentMarketId = null;
let currentMarketName = null;

// Open approve modal
function openApproveModal(marketId, marketName) {
    currentMarketId = marketId;
    currentMarketName = marketName;
    $('#approve-market-name').text(marketName);
    $('#approve-modal').removeClass('hidden');
}

// Close approve modal
function closeApproveModal() {
    $('#approve-modal').addClass('hidden');
    currentMarketId = null;
    currentMarketName = null;
}

// Confirm approval
function confirmApproval() {
    if (!currentMarketId) return;
    
    const submitBtn = $('#confirm-approve-btn');
    submitBtn.prop('disabled', true);
    submitBtn.find('.btn-text').hide();
    submitBtn.find('.btn-loading').show();
    
    $.post(window.MarketAdminRoutes.approve, {
        market_id: currentMarketId,
        _token: window.csrfToken
    })
    .done(function(response) {
        if (response.success) {
            showToast(response.message, 'success');
            closeApproveModal();
            // Remove the row from table
            $(`tr[data-market-id="${currentMarketId}"]`).fadeOut(300, function() {
                $(this).remove();
                // Check if no more pending markets
                if ($('tbody tr').length === 0) {
                    location.reload();
                }
            });
        } else {
            showToast(response.message, 'error');
        }
    })
    .fail(function(xhr) {
        showToast('Error al aprobar mercado', 'error');
    })
    .always(function() {
        submitBtn.prop('disabled', false);
        submitBtn.find('.btn-text').show();
        submitBtn.find('.btn-loading').hide();
    });
}

// Legacy function for backwards compatibility (remove the old function)
function approveMarket(marketId, marketName) {
    openApproveModal(marketId, marketName);
}

// Close modal when clicking outside
$(document).on('click', '#approve-modal', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

// Close modal with Escape key
$(document).on('keydown', function(e) {
    if (e.key === 'Escape' && !$('#approve-modal').hasClass('hidden')) {
        closeApproveModal();
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
#approve-modal {
    backdrop-filter: blur(4px);
}

#approve-modal .bg-white {
    animation: modalSlideIn 0.3s ease-out;
}

#approve-modal.hidden .bg-white {
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

/* Hover effects */
.bg-green-100:hover {
    background-color: #bbf7d0;
}

/* Button focus states */
button:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
}

button:focus-visible {
    outline: 2px solid #10b981;
    outline-offset: 2px;
}
</style>
@endsection
