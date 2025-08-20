@extends('layouts.app')

@section('title', 'Administración de Mercados')

@section('content')
<!-- Meta tags para pasar datos a JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="approve-route" content="{{ route('market-administration.approve') }}">
<meta name="deny-route" content="{{ route('market-administration.deny') }}">
<meta name="change-status-route" content="{{ route('market-administration.change-status') }}">
<meta name="search-route" content="{{ route('market-administration.search') }}">
<meta name="current-filter" content="{{ $filter ?? 'pending' }}">

<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-shield-alt text-purple-600 mr-3"></i>
                        Administración de Mercados
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">Gestiona la aprobación y estado de mercados</p>
                </div>

                
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl px-6 py-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-500 rounded-lg">
                                <i class="fas fa-clock text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-yellow-800">{{ $counts['pending'] ?? 0 }}</p>
                                <p class="text-yellow-600 text-sm font-medium">Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl px-6 py-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-500 rounded-lg">
                                <i class="fas fa-check text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-green-800">{{ $counts['approved'] ?? 0 }}</p>
                                <p class="text-green-600 text-sm font-medium">Aprobados</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl px-6 py-4 shadow-sm">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-500 rounded-lg">
                                <i class="fas fa-times text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-red-800">{{ $counts['denied'] ?? 0 }}</p>
                                <p class="text-red-600 text-sm font-medium">Denegados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6">
                <!-- Search Bar -->
                <div class="flex flex-col sm:flex-row gap-4 mb-6">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="search-input" 
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" 
                                   placeholder="Buscar por nombre de mercado, estado o solicitud...">
                        </div>
                    </div>
                    <button id="clear-search" 
                            class="px-4 py-3 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 font-medium hidden">
                        <i class="fas fa-times mr-2"></i>
                        Limpiar
                    </button>
                </div>

                <!-- Filter Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a href="{{ route('market-administration.index', ['filter' => 'pending'] + request()->except('page')) }}" 
                           class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                                  {{ $filter === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-clock mr-2"></i>
                            Pendientes
                            @if(($counts['pending'] ?? 0) > 0)
                                <span class="ml-2 bg-yellow-100 text-yellow-800 py-0.5 px-2 rounded-full text-xs font-medium">
                                    {{ $counts['pending'] }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('market-administration.index', ['filter' => 'approved'] + request()->except('page')) }}" 
                           class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                                  {{ $filter === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-check mr-2"></i>
                            Aprobados
                            @if(($counts['approved'] ?? 0) > 0)
                                <span class="ml-2 bg-green-100 text-green-800 py-0.5 px-2 rounded-full text-xs font-medium">
                                    {{ $counts['approved'] }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('market-administration.index', ['filter' => 'denied'] + request()->except('page')) }}" 
                           class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                                  {{ $filter === 'denied' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-ban mr-2"></i>
                            Denegados
                            @if(($counts['denied'] ?? 0) > 0)
                                <span class="ml-2 bg-red-100 text-red-800 py-0.5 px-2 rounded-full text-xs font-medium">
                                    {{ $counts['denied'] }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('market-administration.index', ['filter' => 'all'] + request()->except('page')) }}" 
                           class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                                  {{ $filter === 'all' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-list mr-2"></i>
                            Todos
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        @if($markets->count() > 0)
        <!-- Markets Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">
                        <i class="fas fa-list text-purple-600 mr-3"></i>
                        @switch($filter)
                            @case('approved')
                                Mercados Aprobados
                                @break
                            @case('denied')
                                Mercados Denegados
                                @break
                            @case('all')
                                Todos los Mercados
                                @break
                            @default
                                Mercados Pendientes de Aprobación
                        @endswitch
                    </h2>
                    <div class="text-sm text-gray-500">
                        <span id="visible-count">{{ $markets->count() }}</span> de {{ $markets->total() }} mercados
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="markets-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag mr-2"></i>
                                    ID
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-store mr-2"></i>
                                    Nombre del Mercado
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar mr-2"></i>
                                    Fecha de Solicitud
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-toggle-on mr-2"></i>
                                    Estado Actual
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    Solicitud
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-cogs mr-2"></i>
                                    Acciones
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="markets-tbody">
                        @foreach($markets as $market)
                        <tr class="hover:bg-gray-50 transition-colors duration-200 market-row" data-market-id="{{ $market->idMercado }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                                    #{{ $market->idMercado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap market-name">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center">
                                            <i class="fas fa-store text-white text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $market->mercado }}</div>
                                        
                                    </div>
                                </div>
                            </td>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($market->fechaRegistro)->format('d/m/Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($market->fechaRegistro)->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap market-status">
                                @php
                                    $estado = $estados->firstWhere('idEstado', $market->idEstado);
                                @endphp
                                @if($estado && $estado->estado == 'ACTIVO')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        {{ $estado->estado }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                        <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                        {{ $estado->estado ?? 'INACTIVO' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap market-request">
                                @switch($market->solicitud)
                                    @case('ESPERA')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <i class="fas fa-clock mr-2"></i>
                                            PENDIENTE
                                        </span>
                                        @break
                                    @case('APROBADO')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                            <i class="fas fa-check mr-2"></i>
                                            APROBADO
                                        </span>
                                        @break
                                    @case('DENEGADO')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                            <i class="fas fa-ban mr-2"></i>
                                            DENEGADO
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                            <i class="fas fa-question mr-2"></i>
                                            {{ $market->solicitud }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($market->solicitud === 'ESPERA')
                                    <div class="flex items-center space-x-2">
                                        <button onclick="openApproveModal({{ $market->idMercado }}, '{{ $market->mercado }}')"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                            <i class="fas fa-check mr-1"></i>
                                            Aprobar
                                        </button>
                                        <button onclick="openDenyModal({{ $market->idMercado }}, '{{ $market->mercado }}')"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                            <i class="fas fa-ban mr-1"></i>
                                            Denegar
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center text-gray-500">
                                        @if($market->solicitud === 'APROBADO')
                                            <div class="flex items-center px-3 py-2 bg-green-50 rounded-md">
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                <span class="text-sm font-medium text-green-700">Ya procesado</span>
                                            </div>
                                        @elseif($market->solicitud === 'DENEGADO')
                                            <div class="flex items-center px-3 py-2 bg-red-50 rounded-md">
                                                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                                <span class="text-sm font-medium text-red-700">Ya procesado</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($markets->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Mostrando</span>
                        <span class="font-semibold mx-1">{{ $markets->firstItem() ?? 0 }}</span>
                        <span>a</span>
                        <span class="font-semibold mx-1">{{ $markets->lastItem() ?? 0 }}</span>
                        <span>de</span>
                        <span class="font-semibold mx-1">{{ $markets->total() }}</span>
                        <span>resultados</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        {{-- Previous Page Link --}}
                        @if ($markets->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Anterior
                            </span>
                        @else
                            <a href="{{ $markets->appends(request()->query())->previousPageUrl() }}" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i>
                                Anterior
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($markets->currentPage() - 2, 1);
                            $end = min($start + 4, $markets->lastPage());
                        @endphp

                        @for($page = $start; $page <= $end; $page++)
                            @if ($page == $markets->currentPage())
                                <span class="px-3 py-2 text-sm font-medium text-white bg-purple-600 border border-purple-600 rounded-md">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $markets->appends(request()->query())->url($page) }}" 
                                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        {{-- Next Page Link --}}
                        @if ($markets->hasMorePages())
                            <a href="{{ $markets->appends(request()->query())->nextPageUrl() }}" 
                               class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                Siguiente
                                <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 rounded-md cursor-not-allowed">
                                Siguiente
                                <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
        @else
        <!-- No Markets Found -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                @switch($filter)
                    @case('approved')
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay mercados aprobados</h3>
                        <p class="text-gray-600">Aún no se han aprobado mercados en el sistema.</p>
                        @break
                    @case('denied')
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-ban text-red-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay mercados denegados</h3>
                        <p class="text-gray-600">No se han denegado mercados en el sistema.</p>
                        @break
                    @case('pending')
                        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-clock text-yellow-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay mercados pendientes</h3>
                        <p class="text-gray-600">Todos los mercados han sido procesados.</p>
                        @break
                    @default
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-inbox text-gray-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No hay mercados</h3>
                        <p class="text-gray-600">No se encontraron mercados que coincidan con los filtros aplicados.</p>
                @endswitch
            </div>
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

<!-- Deny Modal -->
<div id="deny-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-ban text-red-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-semibold text-gray-900">Denegar Mercado</h3>
                        <p class="text-sm text-gray-500">Confirma la denegación del mercado</p>
                    </div>
                </div>
                <button onclick="closeDenyModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Content -->
            <div class="mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-600 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-red-800 font-medium">¿Estás seguro de que quieres denegar este mercado?</p>
                            <p class="text-red-700 mt-1">
                                Mercado: <strong id="deny-market-name" class="text-red-900"></strong>
                            </p>
                            <p class="text-red-600 text-sm mt-2">
                                Una vez denegado, el mercado no estará disponible para su uso y cambiará su estado a denegado.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeDenyModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                <button id="confirm-deny-btn"
                        onclick="confirmDenial()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors duration-200 font-medium">
                    <span class="btn-text">
                        <i class="fas fa-ban mr-2"></i>
                        Denegar Mercado
                    </span>
                    <span class="btn-loading hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Denegando...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>
@endsection

<!-- Scripts -->
@push('scripts')
    @vite('resources/js/market-administration.js')
@endpush

<!-- Styles -->
@push('styles')
    @vite('resources/css/market-administration.css')
@endpush
