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
                        <!-- Notificación tipo Facebook para Productos NUEVOS y SIN_ASIGNAR -->
                        <div onclick="redirectToProductosNuevosYSinAsignar()" 
                             class="relative cursor-pointer group">
                            <!-- Icono de notificación -->
                            <div class="w-10 h-10 bg-red-500 hover:bg-red-600 rounded-full flex items-center justify-center transition-colors duration-200 shadow-lg hover:shadow-xl">
                                <i class="fa-solid fa-bell text-white text-sm"></i>
                            </div>
                            
                            <!-- Badge con el número total -->
                            <div class="absolute -top-2 -right-2 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-lg">
                                <span id="total-count-notification">0</span>
                            </div>
                            
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center space-x-1">
                                        <span class="text-blue-300">NUEVOS:</span>
                                        <span class="font-bold" id="tooltip-count-nuevos">0</span>
                                    </div>
                                    <div class="w-px h-3 bg-gray-600"></div>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-orange-300">SIN ASIGNAR:</span>
                                        <span class="font-bold" id="tooltip-count-sin-asignar">0</span>
                                    </div>
                                </div>
                                <div class="text-center text-gray-300 mt-1">Click para ver productos</div>
                            </div>
                        </div>
                        
                        <!-- Contador de ATC4 para Gerentes de Producto -->
                        @if(auth()->user()->idRol == 2)
                        <div onclick="openAtc4ListModal()" class="relative cursor-pointer group">
                            <!-- Icono de ATC4 -->
                            <div class="w-10 h-10 bg-purple-500 hover:bg-purple-600 rounded-full flex items-center justify-center transition-colors duration-200 shadow-lg hover:shadow-xl">
                                <i class="fa-solid fa-layer-group text-white text-sm"></i>
                            </div>
                            
                            <!-- Badge con el número de ATC4 -->
                            <div class="absolute -top-2 -right-2 bg-purple-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center shadow-lg">
                                <span id="atc4-count-badge">0</span>
                            </div>
                            
                            <!-- Tooltip -->
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                                <div class="text-center">
                                    <span class="text-purple-300">ATC4:</span>
                                    <span class="font-bold text-white" id="tooltip-count-atc4">0</span>
                                </div>
                                <div class="text-center text-gray-300 mt-1">Click para ver ATC4 del gerente</div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Botón Crear Mercado -->
                        <button onclick="openCreateMarketModal()"
                                class="inline-flex items-center px-4 py-2 rounded-md text-sm bg-primary text-white hover:bg-secondary transition-colors duration-200 shadow-sm hover:shadow-md"
                                title="Crear nuevo mercado">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Mercado
                        </button>
                        
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="search-input"
                                   name="search"
                                   value="{{ request('search') }}"
                                   class="block w-80 pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary text-sm"
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
                                    <div class="flex-shrink-0 w-8 h-8 bg-secondary-purple rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-tags text-primary text-xs"></i>
                                    </div>
                                    <div>
                                        <button onclick="redirectToProductsWithBrand('{{ $market->marca }}')" 
                                                class="font-medium text-gray-900 hover:text-primary hover:underline transition-colors cursor-pointer"
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
                                    <div class="flex-shrink-0 w-6 h-6 bg-secondary-purple rounded-full flex items-center justify-center mr-2">
                                        <i class="fas fa-store text-primary text-xs"></i>
                                    </div>
                                    @if($market->mercado)
                                        <button onclick="redirectToProductsWithMarket('{{ $market->mercado }}')" 
                                                class="font-medium text-primary hover:text-secondary hover:underline transition-colors cursor-pointer"
                                                title="Ver productos de este mercado">
                                            {{ $market->mercado }}
                                        </button>
                                    @else
                                        <span class="text-gray-400 italic" title="Sin mercado asignado">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Sin asignar
                                        </span>
                                    @endif
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
                                <div class="flex space-x-2">
                                    @if($market->mercado)
                                        <!-- Editar Nombre del Mercado -->
                                        <button onclick="openEditModal({{ $market->idMercado }}, '{{ addslashes($market->mercado) }}')"
                                                class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-secondary-lighter text-primary hover:bg-secondary-muted transition-colors duration-200"
                                                title="Editar nombre del mercado">
                                            <i class="fas fa-edit mr-1"></i>
                                            Editar Mercado
                                        </button>
                                        
                                        <!-- Asignar Producto -->
                                        <button onclick="redirectToProductsWithRestoAndMarket('{{ $market->mercado }}')"
                                                class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-primary text-white hover:bg-secondary transition-colors duration-200"
                                                title="Asignar producto a este mercado">
                                            <i class="fas fa-plus mr-1"></i>
                                            Asignar Producto
                                        </button>
                                    @else
                                        <!-- Sin mercado asignado -->
                                        <span class="text-gray-400 italic text-xs">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Sin mercado asignado
                                        </span>
                                    @endif
                                </div>
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
                        <button onclick="clearSearch()" class="mt-3 text-primary hover:text-secondary font-medium">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar búsqueda
                        </button>
                    @elseif(auth()->user()->idRol == 2)
                        <h3 class="text-lg font-medium text-gray-800 mb-2">No hay marcas asignadas</h3>
                        <p class="text-gray-600">No hay marcas asignadas que coincidan con tu búsqueda</p>
                        <button onclick="clearSearch()" class="mt-3 text-primary hover:text-secondary font-medium">
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
<div id="edit-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
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
            <input type="hidden" id="edit-market-original-name" name="market_original_name">
            
            <!-- Market Name Field -->
            <div class="mb-4">
                <label for="edit-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Mercado *
                </label>
                <input type="text" 
                       id="edit-market-name" 
                       name="market_name" 
                       class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white"
                       placeholder="Nombre del mercado"
                       required>
            </div>

            <!-- Note Field -->
            <div class="mb-4">
                <label for="edit-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-primary mr-1"></i>
                    Nota *
                </label>
                <textarea id="edit-market-note" 
                          name="market_note" 
                          rows="3"
                          class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                          placeholder="Nota obligatoria sobre los cambios realizados..."
                          required></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Campo obligatorio. Se enviará por email como notificación del cambio.
                </p>
                <p class="text-xs text-red-500 mt-1 hidden" id="edit-note-error">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    La nota es obligatoria
                </p>
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
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
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
<div id="edit-confirmation-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
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
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3 mb-3">
                    <p class="text-sm text-primary font-medium">Actual:</p>
                    <p class="text-lg font-bold text-primary" id="confirm-edit-current-name">-</p>
                </div>
                
                <div class="text-center my-2">
                    <i class="fas fa-arrow-down text-primary text-xl"></i>
                </div>
                
                <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3">
                    <p class="text-sm text-primary font-medium">Nuevo:</p>
                    <p class="text-lg font-bold text-primary" id="confirm-edit-new-name">-</p>
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
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
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

<!-- Modal para Crear Mercado -->
<div id="create-market-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Crear Nuevo Mercado
                </h3>
                <button onclick="closeCreateMarketModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Form Content -->
        <form id="create-market-form" class="p-6">
            @csrf
            
            <!-- Market Name Field -->
            <div class="mb-4">
                <label for="new-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre del Mercado *
                </label>
                <input type="text" 
                       id="new-market-name"
                       name="market_name"
                       class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white"
                       placeholder="Nombre del mercado"
                       required>
            </div>

            <!-- Note Field -->
            <div class="mb-4">
                <label for="new-market-note" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-primary mr-1"></i>
                    Nota *
                </label>
                <textarea id="new-market-note"
                          name="market_note"
                          rows="3"
                          class="w-full px-3 py-3 border-2 border-primary rounded-lg focus:ring-2 focus:ring-secondary focus:border-primary bg-secondary-lighter focus:bg-white resize-none"
                          placeholder="Nota obligatoria sobre el mercado..."
                          required></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Campo obligatorio. Se enviará por email como notificación.
                </p>
                <p class="text-xs text-red-500 mt-1 hidden" id="create-note-error">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    La nota es obligatoria
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="closeCreateMarketModal()"
                        class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                    Cancelar
                </button>
                <button type="button"
                        onclick="proceedWithMarketCreation()"
                        id="create-market-btn"
                        class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                    <span class="btn-text flex items-center justify-center">
                        <i class="fas fa-plus mr-2"></i>
                        Crear Mercado
                    </span>
                    <span class="btn-loading hidden flex items-center justify-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Creando...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmación para Crear Mercado -->
<div id="create-market-confirmation-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-[60]">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100">
        <!-- Header -->
        <div class="bg-primary p-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirmar Creación
                </h3>
                <button onclick="closeCreateMarketConfirmationModal()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-6">
            <div class="bg-secondary-lighter border border-secondary-muted rounded-lg p-3 mb-4">
                <p class="text-sm text-primary font-medium">Nuevo Mercado:</p>
                <p class="text-lg font-bold text-primary" id="confirm-create-market-name">-</p>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                <p class="text-yellow-800 text-sm">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-1"></i>
                    <strong>Atención:</strong> ¿Está seguro de que desea crear este mercado?
                </p>
            </div>

            <div class="space-y-2" id="confirm-create-market-note-container" style="display: none;">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <h4 class="text-sm font-medium text-gray-900 mb-1">Nota:</h4>
                    <p class="text-sm text-gray-700" id="confirm-create-market-note">-</p>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="px-6 py-4 border-t border-gray-200 flex space-x-3">
            <button type="button" 
                    onclick="closeCreateMarketConfirmationModal()"
                    class="flex-1 px-4 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">
                Cancelar
            </button>
            <button type="button"
                    onclick="proceedWithMarketCreationAndAssignment()"
                    id="final-create-assign-btn"
                    class="flex-1 px-4 py-3 bg-primary hover:bg-secondary text-white rounded-lg font-medium shadow-lg hover:shadow-xl transform hover:scale-105 transition-all">
                <span class="btn-text flex items-center justify-center">
                    <i class="fas fa-check mr-2"></i>
                    Confirmar y Crear
                </span>
                <span class="btn-loading hidden flex items-center justify-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Creando...
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Modal para Mostrar Lista de ATC4 -->
<div id="atc4-list-modal" class="fixed inset-0 flex items-center justify-center p-4 hidden z-50">
    <!-- Overlay con animación -->
    <div class="absolute inset-0 bg-opacity-50 transition-opacity duration-300 opacity-0" id="atc4-overlay"></div>
    
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[80vh] overflow-hidden transform transition-all duration-300 ease-out scale-95 opacity-0 relative z-10">
        <!-- Header -->
        <div class="bg-primary from-primary to-secondary p-3">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-layer-group mr-2"></i>
                    Lista de ATC4 - <span id="atc4-modal-gerente">Gerente</span>
                </h3>
                <button onclick="closeAtc4ListModal()" class="text-white hover:text-secondary-purple transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <!-- Content -->
        <div class="px-6 pt-4 pb-6 overflow-y-auto max-h-[calc(80vh-130px)]">
            <!-- Loading State -->
            <div id="atc4-loading" class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span class="ml-2 text-primary">Cargando ATC4...</span>
            </div>
            
            <!-- Error State -->
            <div id="atc4-error" class="hidden text-center py-8">
                <i class="fas fa-exclamation-triangle text-danger text-4xl mb-4"></i>
                <p class="text-danger font-medium" id="atc4-error-message">Error al cargar los datos</p>
                <button onclick="loadAtc4List()" class="mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                    Reintentar
                </button>
            </div>
            
            <!-- Content -->
            <div id="atc4-content" class="hidden">
                <!-- Summary -->
                <div class="bg-primary  border border-primary rounded-lg p-3 mb-4 transform translate-y-4 opacity-0 transition-all duration-500" id="atc4-summary">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-white font-medium">Resumen</h4>
                            <p class="text-white text-sm opacity-90">Categorías ATC4 del gerente con conteo total</p>
                        </div>
                        <div class="text-right">
                            <div class="text-center">
                                <span class="text-2xl font-bold text-white" id="atc4-total-count">0</span>
                                <p class="text-white text-xs opacity-90">categorías</p>
                                                        
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- ATC4 List -->
                <div class="space-y-2" id="atc4-list-container">
                    <!-- Los elementos ATC4 se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-3 border-t border-primary bg-primary">
            <div class="flex justify-between items-center">
                <div class="text-sm text-white">
                    <i class="fas fa-info-circle mr-1"></i>
                    Haga clic en una categoría para ver todos los productos con ese ATC4
                </div>
                <button onclick="closeAtc4ListModal()" 
                        class="px-4 py-2 bg-white text-primary rounded-lg hover:bg-secondary-light transition-colors font-medium">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
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





// Variables globales para JavaScript
window.atc4ListRoute = '{{ route("market-management.atc4-list") }}';
window.atc4CountRoute = '{{ route("market-management.atc4-count") }}';
window.productosIndexRoute = '{{ route("productos.index") }}';
window.productosNuevosSinAsignarRoute = '{{ route("market-management.productos-nuevos-sin-asignar") }}';
window.createMarketRoute = '{{ route("productos.create-market") }}';
window.currentUser = '{{ auth()->user()->usuario }}';
window.isGerenteProducto = {{ auth()->user()->idRol == 2 ? 'true' : 'false' }};
</script>
<script src="{{ asset('js/market-management.js') }}"></script>
<script src="{{ asset('js/market-management-main.js') }}"></script>
<script src="{{ asset('js/atc4-modal.js') }}"></script>
@endpush
