@extends('layouts.app')

@section('page-title', 'Admin Productos de Mercado')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-clipboard-check text-orange-600 mr-2"></i>
                    Admin Productos de Mercado
                </h1>
                <p class="text-gray-600 mt-1">Gestión de productos pendientes de aprobación para remoción</p>
            </div>
            <div class="text-right">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Productos pendientes</p>
                    <p class="text-2xl font-bold text-orange-600" id="total-pendientes">{{ $stats['total_pendientes'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Pendientes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Pendientes</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $stats['total_pendientes'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Por aprobar hoy -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Acciones Disponibles</p>
                    <p class="text-lg font-semibold text-gray-900">Aprobar / Denegar</p>
                </div>
            </div>
        </div>

        <!-- Mercados afectados -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-store text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Mercados Afectados</p>
                    <p class="text-lg font-semibold text-gray-900">{{ count($stats['total_por_mercado'] ?? []) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                <h2 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-search mr-2"></i>
                    Filtros y Búsqueda
                </h2>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshData()" class="text-blue-600 hover:text-blue-700 flex items-center">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('admin-productos.index') }}" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar productos</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ $search }}"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Código, descripción, molécula, mercado...">
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-search mr-1"></i>
                        Buscar
                    </button>
                    @if($search)
                        <a href="{{ route('admin-productos.index') }}" 
                           class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-list mr-2"></i>
                    Productos Pendientes de Aprobación
                </h2>
                <div class="text-sm text-gray-600">
                    Mostrando {{ $productos->firstItem() ?? 0 }} - {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }} resultados
                </div>
            </div>
        </div>

        @if($productos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Código Presentación
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descripción
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mercado Actual
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Marca/Genérico
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Molécula
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha Solicitud
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($productos as $producto)
                            <tr class="hover:bg-gray-50" id="producto-row-{{ $producto->idConfiguracion }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $producto->codigoPresentacion }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs">
                                        <p class="font-medium">{{ $producto->descripcionPresentacion }}</p>
                                        @if($producto->laboratorio)
                                            <p class="text-xs text-gray-500">Lab: {{ $producto->laboratorio }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-store mr-1"></i>
                                        {{ $producto->mercadoActual }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ getBrandClass($producto->marcaGenerico) }}">
                                        {{ $producto->marcaGenerico }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="max-w-xs">
                                        {{ $producto->molecula }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                                        {{ \Carbon\Carbon::parse($producto->fechaSolicitud)->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="aprobarProducto('{{ $producto->codigoPresentacion }}', {{ $producto->idConfiguracion }}, '{{ addslashes($producto->descripcionPresentacion) }}', '{{ $producto->mercadoActual }}')" 
                                                class="text-green-600 hover:text-green-900 transition-colors duration-200 p-2 rounded hover:bg-green-50" 
                                                title="Aprobar remoción">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="denegarProducto('{{ $producto->codigoPresentacion }}', {{ $producto->idConfiguracion }}, '{{ addslashes($producto->descripcionPresentacion) }}', '{{ $producto->mercadoActual }}')" 
                                                class="text-red-600 hover:text-red-900 transition-colors duration-200 p-2 rounded hover:bg-red-50" 
                                                title="Denegar remoción">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $productos->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-8 text-center">
                <i class="fas fa-clipboard-check text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 mb-2">No hay productos pendientes</h3>
                <p class="text-gray-600">
                    @if($search)
                        No se encontraron productos que coincidan con la búsqueda "{{ $search }}"
                    @else
                        Actualmente no hay productos pendientes de aprobación
                    @endif
                </p>
                @if($search)
                    <a href="{{ route('admin-productos.index') }}" 
                       class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Ver todos los productos
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<!-- Modal para Aprobar -->
<div id="aprobar-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mr-3">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Aprobar Remoción</h3>
                </div>
                <button onclick="closeAprobarModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="font-medium text-gray-900" id="aprobar-producto-name">Nombre del producto</p>
                <p class="text-sm text-gray-600">Código: <span id="aprobar-producto-code"></span></p>
                <p class="text-sm text-gray-600">Mercado actual: <span id="aprobar-mercado-actual"></span></p>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                <h4 class="text-sm font-medium text-green-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Al aprobar esta solicitud:
                </h4>
                <ul class="text-xs text-green-700 space-y-1">
                    <li>• El producto será removido del mercado actual</li>
                    <li>• Se moverá automáticamente al mercado <strong>RESTO</strong></li>
                    <li>• El estado cambiará a <strong>APROBADO</strong></li>
                    <li>• Los cambios se reflejarán en la base de datos</li>
                </ul>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button onclick="closeAprobarModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-aprobar-btn" onclick="confirmarAprobar()" 
                        class="px-4 py-2 bg-green-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                    <span class="btn-text">Aprobar Remoción</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Denegar -->
<div id="denegar-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden flex items-center justify-center">
    <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mr-3">
                        <i class="fas fa-times text-red-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Denegar Remoción</h3>
                </div>
                <button onclick="closeDenegarModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="font-medium text-gray-900" id="denegar-producto-name">Nombre del producto</p>
                <p class="text-sm text-gray-600">Código: <span id="denegar-producto-code"></span></p>
                <p class="text-sm text-gray-600">Mercado actual: <span id="denegar-mercado-actual"></span></p>
            </div>
            
            <div class="mb-4">
                <label for="motivo-denegacion" class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo de denegación (opcional):
                </label>
                <textarea id="motivo-denegacion" 
                          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"
                          rows="3"
                          placeholder="Explica por qué se deniega la solicitud..."></textarea>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <h4 class="text-sm font-medium text-red-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Al denegar esta solicitud:
                </h4>
                <ul class="text-xs text-red-700 space-y-1">
                    <li>• El producto permanecerá en su mercado actual</li>
                    <li>• El estado volverá a <strong>APROBADO</strong></li>
                    <li>• La solicitud de remoción será cancelada</li>
                </ul>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button onclick="closeDenegarModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="confirm-denegar-btn" onclick="confirmarDenegar()" 
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                    <span class="btn-text">Denegar Solicitud</span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Variables globales para los modales
    let currentCodigoPresentacion = null;
    let currentIdConfiguracion = null;
    let currentProductName = null;
    let currentMercado = null;

    // Función para refrescar datos
    window.refreshData = function() {
        window.location.reload();
    };

    // Funciones para modal de aprobar
    window.aprobarProducto = function(codigoPresentacion, idConfiguracion, productName, mercado) {
        currentCodigoPresentacion = codigoPresentacion;
        currentIdConfiguracion = idConfiguracion;
        currentProductName = productName;
        currentMercado = mercado;
        
        document.getElementById('aprobar-producto-name').textContent = productName;
        document.getElementById('aprobar-producto-code').textContent = codigoPresentacion;
        document.getElementById('aprobar-mercado-actual').textContent = mercado;
        document.getElementById('aprobar-modal').classList.remove('hidden');
    };

    window.closeAprobarModal = function() {
        document.getElementById('aprobar-modal').classList.add('hidden');
        resetModalState('aprobar');
    };

    window.confirmarAprobar = function() {
        if (!currentCodigoPresentacion || !currentIdConfiguracion) {
            showErrorNotification('Error: Datos de producto inválidos');
            return;
        }

        const btn = $('#confirm-aprobar-btn');
        const btnText = btn.find('.btn-text');
        const btnLoading = btn.find('.btn-loading');
        
        btnText.addClass('hidden');
        btnLoading.removeClass('hidden');
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin-productos.aprobar") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                codigoPresentacion: currentCodigoPresentacion,
                idConfiguracion: currentIdConfiguracion
            },
            success: function(response) {
                if (response.success) {
                    showSuccessNotification('Producto aprobado exitosamente y movido al mercado RESTO');
                    closeAprobarModal();
                    
                    // Remover fila de la tabla
                    $(`#producto-row-${currentIdConfiguracion}`).fadeOut(300, function() {
                        $(this).remove();
                        updateTotalCount();
                    });
                } else {
                    showErrorNotification('Error: ' + response.message);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error al aprobar:', {xhr, textStatus, errorThrown});
                let errorMessage = 'Error al procesar la aprobación';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showErrorNotification('Error: ' + errorMessage);
            },
            complete: function() {
                btnText.removeClass('hidden');
                btnLoading.addClass('hidden');
                btn.prop('disabled', false);
            }
        });
    };

    // Funciones para modal de denegar
    window.denegarProducto = function(codigoPresentacion, idConfiguracion, productName, mercado) {
        currentCodigoPresentacion = codigoPresentacion;
        currentIdConfiguracion = idConfiguracion;
        currentProductName = productName;
        currentMercado = mercado;
        
        document.getElementById('denegar-producto-name').textContent = productName;
        document.getElementById('denegar-producto-code').textContent = codigoPresentacion;
        document.getElementById('denegar-mercado-actual').textContent = mercado;
        document.getElementById('motivo-denegacion').value = '';
        document.getElementById('denegar-modal').classList.remove('hidden');
    };

    window.closeDenegarModal = function() {
        document.getElementById('denegar-modal').classList.add('hidden');
        resetModalState('denegar');
    };

    window.confirmarDenegar = function() {
        if (!currentCodigoPresentacion || !currentIdConfiguracion) {
            showErrorNotification('Error: Datos de producto inválidos');
            return;
        }

        const motivo = document.getElementById('motivo-denegacion').value.trim();
        const btn = $('#confirm-denegar-btn');
        const btnText = btn.find('.btn-text');
        const btnLoading = btn.find('.btn-loading');
        
        btnText.addClass('hidden');
        btnLoading.removeClass('hidden');
        btn.prop('disabled', true);

        $.ajax({
            url: '{{ route("admin-productos.denegar") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                codigoPresentacion: currentCodigoPresentacion,
                idConfiguracion: currentIdConfiguracion,
                motivo: motivo
            },
            success: function(response) {
                if (response.success) {
                    showSuccessNotification('Solicitud denegada exitosamente. El producto permanece en su mercado actual');
                    closeDenegarModal();
                    
                    // Remover fila de la tabla
                    $(`#producto-row-${currentIdConfiguracion}`).fadeOut(300, function() {
                        $(this).remove();
                        updateTotalCount();
                    });
                } else {
                    showErrorNotification('Error: ' + response.message);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error al denegar:', {xhr, textStatus, errorThrown});
                let errorMessage = 'Error al procesar la denegación';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showErrorNotification('Error: ' + errorMessage);
            },
            complete: function() {
                btnText.removeClass('hidden');
                btnLoading.addClass('hidden');
                btn.prop('disabled', false);
            }
        });
    };

    // Función para resetear estados de modales
    function resetModalState(type) {
        currentCodigoPresentacion = null;
        currentIdConfiguracion = null;
        currentProductName = null;
        currentMercado = null;
        
        if (type === 'denegar') {
            document.getElementById('motivo-denegacion').value = '';
        }
    }

    // Función para actualizar contador
    function updateTotalCount() {
        const currentCount = parseInt($('#total-pendientes').text()) || 0;
        const newCount = Math.max(0, currentCount - 1);
        $('#total-pendientes').text(newCount);
        
        // Si no quedan productos, mostrar mensaje
        if (newCount === 0 && $('tbody tr').length === 0) {
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
    }

    // Cerrar modales con ESC
    document.addEventListener('keyup', function(e) {
        if (e.key === "Escape") {
            closeAprobarModal();
            closeDenegarModal();
        }
    });

    // Cerrar modales al hacer clic fuera
    $('#aprobar-modal').on('click', function(e) {
        if (e.target === this) {
            closeAprobarModal();
        }
    });

    $('#denegar-modal').on('click', function(e) {
        if (e.target === this) {
            closeDenegarModal();
        }
    });
});

// Funciones de notificación (reutilizar las del módulo anterior)
function showSuccessNotification(message) {
    const notification = $(`
        <div class="notification-toast fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md transform translate-x-full opacity-0 transition-all duration-300 ease-out">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-green-200"></i>
                <div class="flex-1">
                    <h4 class="font-bold text-sm mb-1">¡Éxito!</h4>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
                <button onclick="$(this).parent().parent().fadeOut(300, function() { $(this).remove(); })" class="ml-3 text-green-200 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    setTimeout(() => notification.removeClass('translate-x-full opacity-0'), 100);
    setTimeout(() => {
        notification.addClass('translate-x-full opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function showErrorNotification(message) {
    const notification = $(`
        <div class="notification-toast fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md transform translate-x-full opacity-0 transition-all duration-300 ease-out">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-200"></i>
                <div class="flex-1">
                    <h4 class="font-bold text-sm mb-1">Error</h4>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
                <button onclick="$(this).parent().parent().fadeOut(300, function() { $(this).remove(); })" class="ml-3 text-red-200 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    setTimeout(() => notification.removeClass('translate-x-full opacity-0'), 100);
    setTimeout(() => {
        notification.addClass('translate-x-full opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 7000);
}

// Función helper para clases de marca
function getBrandClass(brand) {
    switch(brand) {
        case 'MARCA': return 'bg-blue-100 text-blue-800';
        case 'GENÉRICO': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
</script>
@endpush

@push('styles')
<style>
/* Modal styles */
.modal {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Centrado perfecto de modales */
#aprobar-modal,
#denegar-modal {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

#aprobar-modal.hidden,
#denegar-modal.hidden {
    display: none !important;
}

/* Action buttons hover effects */
.action-btn {
    transition: all 0.2s ease-in-out;
}

.action-btn:hover {
    transform: scale(1.1);
}

/* Notification toast animations */
.notification-toast {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(8px);
}

.notification-toast:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
}

/* Responsive notifications */
@media (max-width: 640px) {
    .notification-toast {
        left: 1rem;
        right: 1rem;
        max-width: none;
    }
}

/* Table hover effects */
tbody tr:hover {
    background-color: #f9fafb;
}

/* Loading states */
.btn-loading {
    display: inline-flex;
    align-items: center;
}

/* Stats cards hover */
.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>
@endpush

@php
function getBrandClass($brand) {
    switch($brand) {
        case 'MARCA': return 'bg-blue-100 text-blue-800';
        case 'GENÉRICO': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}
@endphp
