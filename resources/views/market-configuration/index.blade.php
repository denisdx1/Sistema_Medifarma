<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Mercado - Medifarma</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite('resources/css/erp.css')
</head>
<body class="flex h-screen overflow-hidden">

    <x-sidebar/>

    <!-- Main content -->
    <main id="main-content" class="flex-1 p-6 overflow-y-auto transition-all duration-300 ease-in-out">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Configuración de Mercado</h1>
                <p class="text-gray-600 mt-1">Gestiona la asignación de mercados a los productos</p>
            </div>
            <div class="flex space-x-3">
                <button id="export-unassigned-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-download mr-2"></i>
                    Exportar Sin Mercado
                </button>
                <button id="create-market-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Mercado
                </button>
            </div>
        </header>

        <!-- Session Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error') || $errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <div class="text-red-800">
                        @if(session('error'))
                            <p>{{ session('error') }}</p>
                        @endif
                        @if($errors->any())
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg border stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-boxes text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Productos</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_products']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Con Mercado</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['products_with_market']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Sin Mercado</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['products_without_market']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-percentage text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">% Asignado</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $stats['assignment_percentage'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 p-6 rounded-lg mb-8">
            <form action="{{ route('market-configuration.index') }}" method="GET" id="filters-form">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                    <!-- Global Search -->
                    <div class="relative">
                        <label for="search" class="text-sm font-medium text-gray-700">Búsqueda Global</label>
                        <input type="text" name="search" id="search" placeholder="Nombre, SKU..." value="{{ request('search') }}" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <div id="search-loading" class="absolute right-3 top-9 hidden">
                            <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Market Status Filter -->
                    <div>
                        <label for="market_status" class="text-sm font-medium text-gray-700">Estado de Mercado</label>
                        <select name="market_status" id="market_status" class="w-full mt-1 select2">
                            <option value="">Todos</option>
                            <option value="with_market" @if(request('market_status') == 'with_market') selected @endif>Con Mercado</option>
                            <option value="without_market" @if(request('market_status') == 'without_market') selected @endif>Sin Mercado</option>
                        </select>
                    </div>

                    <!-- Brand Filter -->
                    <div>
                        <label for="brand_id" class="text-sm font-medium text-gray-700">Marca</label>
                        <select name="brand_id" id="brand_id" class="w-full mt-1 select2">
                            <option value="">Todas</option>
                            @foreach ($filterOptions['brands'] as $brand)
                                <option value="{{ $brand->id }}" @if(request('brand_id') == $brand->id) selected @endif>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Franchise Filter -->
                    <div>
                        <label for="franchise_id" class="text-sm font-medium text-gray-700">Franquicia</label>
                        <select name="franchise_id" id="franchise_id" class="w-full mt-1 select2">
                            <option value="">Todas</option>
                            @foreach ($filterOptions['franchises'] as $franchise)
                                <option value="{{ $franchise->id }}" @if(request('franchise_id') == $franchise->id) selected @endif>{{ $franchise->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Business Unit Filter -->
                    <div>
                        <label for="business_unit_id" class="text-sm font-medium text-gray-700">Unidad de Negocio</label>
                        <select name="business_unit_id" id="business_unit_id" class="w-full mt-1 select2">
                            <option value="">Todas</option>
                            @foreach ($filterOptions['businessUnits'] as $businessUnit)
                                <option value="{{ $businessUnit->id }}" @if(request('business_unit_id') == $businessUnit->id) selected @endif>{{ $businessUnit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('market-configuration.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">Limpiar</a>
                        <button type="button" id="bulk-assign-btn" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg flex items-center" disabled>
                            <i class="fas fa-layer-group mr-2"></i>
                            Asignar en Lote (<span id="selected-count">0</span>)
                        </button>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span id="products-count">{{ $products->total() }}</span> productos encontrados
                    </div>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-left">
                                <input type="checkbox" id="select-all" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            </th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Marca</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Franquicia</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">U. Negocio</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mercado</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50 product-row" data-product-id="{{ $product->id }}">
                                <td class="p-4">
                                    <input type="checkbox" class="product-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500" value="{{ $product->id }}">
                                </td>
                                <td class="p-4">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                                        @if($product->description)
                                            <p class="text-sm text-gray-500">{{ Str::limit($product->description, 50) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($product->sku)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ $product->sku }}</span>
                                    @else
                                        <span class="text-gray-400 text-sm">Sin SKU</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-gray-900">{{ $product->brand->name ?? 'Sin Marca' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-gray-900">{{ $product->franchise->name ?? 'Sin Franquicia' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-gray-900">{{ $product->businessUnit->name ?? 'Sin U. Negocio' }}</span>
                                </td>
                                <td class="p-4">
                                    @if($product->market)
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            {{ $product->market->name }}
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Sin Mercado
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex space-x-2">
                                        @if($product->market)
                                            <button class="change-market-btn bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-1 px-2 rounded" 
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                data-current-market="{{ $product->market->name }}">
                                                <i class="fas fa-edit mr-1"></i>
                                                Cambiar
                                            </button>
                                            <button class="remove-market-btn bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-1 px-2 rounded" 
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}">
                                                <i class="fas fa-times mr-1"></i>
                                                Remover
                                            </button>
                                        @else
                                            <button class="assign-market-btn bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold py-1 px-2 rounded" 
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}">
                                                <i class="fas fa-plus mr-1"></i>
                                                Asignar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-search text-4xl mb-4"></i>
                                    <p class="text-lg">No se encontraron productos que coincidan con los filtros aplicados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="mt-8">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    </main>

    <!-- Market Assignment Modal -->
    @include('market-configuration.partials.assign-market-modal')

    <!-- Create Market Modal -->
    @include('market-configuration.partials.create-market-modal')

    <!-- Bulk Assignment Modal -->
    @include('market-configuration.partials.bulk-assign-modal')

    <!-- Remove Market Confirmation Modal -->
    @include('market-configuration.partials.remove-market-modal')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @vite('resources/js/market-configuration.js')
</body>
</html>
