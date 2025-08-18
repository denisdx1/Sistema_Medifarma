<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Medifarma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Enhanced Select2 Styling */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            background-color: white !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.15s ease-in-out !important;
        }
        
        .select2-container--default .select2-selection--single:hover {
            border-color: #9ca3af !important;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #8b5cf6 !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1) !important;
            outline: none !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151 !important;
            line-height: 40px !important;
            padding-left: 12px !important;
            font-size: 14px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        
        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        }
        
        .select2-container--default .select2-results__option {
            padding: 10px 12px !important;
            font-size: 14px !important;
            transition: all 0.15s ease-in-out !important;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #8b5cf6 !important;
            color: white !important;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            padding: 8px 12px !important;
            font-size: 14px !important;
            margin: 8px !important;
            width: calc(100% - 16px) !important;
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.15s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Enhanced filter section styling */
        .filter-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .filter-title {
            color: #475569;
            font-weight: 600;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-title i {
            color: #8b5cf6;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .filter-item {
            display: flex;
            flex-direction: column;
        }
        
        .filter-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .filter-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }
        
        .active-filters {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-badge {
            background-color: #8b5cf6;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .clear-filters-btn {
            background-color: #6b7280;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
        }
        
        .clear-filters-btn:hover {
            background-color: #4b5563;
        }
    </style>
    @vite('resources/css/erp.css')
</head>
<body class="flex h-screen overflow-hidden">

    <x-sidebar/>

    <!-- Main content -->
    <main id="main-content" class="flex-1 p-6 overflow-y-auto transition-all duration-300 ease-in-out">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Sistema de Gestión - Medifarma</h1>
            </div>
        </header>

        <!-- Session Messages -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-md" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Content -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-4 rounded-lg border flex justify-between items-center">
                    <div>
                        <p class="text-gray-500">Total Productos</p>
                        <p class="text-3xl font-bold">{{ $totalProducts }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg border flex justify-between items-center">
                    <div>
                        <p class="text-gray-500">Sin Código</p>
                        <p class="text-3xl font-bold text-red-500">{{ $productsWithoutCode }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg border flex justify-between items-center">
                    <div>
                        <p class="text-gray-500">Con Código</p>
                        <p class="text-3xl font-bold">{{ $productsWithCode }}</p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filters Section -->
            <div class="filter-section p-6 mb-8">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filtros de Búsqueda
                </div>
                
                <form action="{{ route('erp') }}" method="GET" id="filters-form">
                    <div class="filter-grid">
                        <!-- Global Search -->
                        <div class="filter-item">
                            <label for="search" class="filter-label">
                                <i class="fas fa-search text-gray-400"></i>
                                Búsqueda Global
                            </label>
                            <div class="relative">
                                <input type="text" name="search" id="search" 
                                       placeholder="Nombre, SKU, descripción..." 
                                       value="{{ request('search') }}" 
                                       class="w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 transition-all duration-150">
                                <div id="search-loading" class="absolute right-3 top-3 hidden">
                                    <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="filter-item">
                            <label for="status" class="filter-label">
                                <i class="fas fa-code text-gray-400"></i>
                                Estado del Código
                            </label>
                            <select name="status" id="status" class="select2-filter">
                                <option value="">Todos los estados</option>
                                <option value="with_code" @if(request('status') == 'with_code') selected @endif>Con Código SKU</option>
                                <option value="without_code" @if(request('status') == 'without_code') selected @endif>Sin Código SKU</option>
                            </select>
                        </div>

                        <!-- Franchise Filter -->
                        <div class="filter-item">
                            <label for="franchise_id" class="filter-label">
                                <i class="fas fa-store text-gray-400"></i>
                                Franquicia
                            </label>
                            <select name="franchise_id" id="franchise_id" class="select2-filter">
                                <option value="">Todas las franquicias</option>
                                @foreach ($franchises as $franchise)
                                    <option value="{{ $franchise->id }}" @if(request('franchise_id') == $franchise->id) selected @endif>{{ $franchise->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand Filter -->
                        <div class="filter-item">
                            <label for="brand_id" class="filter-label">
                                <i class="fas fa-tag text-gray-400"></i>
                                Marca
                            </label>
                            <select name="brand_id" id="brand_id" class="select2-filter">
                                <option value="">Todas las marcas</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @if(request('brand_id') == $brand->id) selected @endif>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Business Unit Filter -->
                        <div class="filter-item">
                            <label for="business_unit_id" class="filter-label">
                                <i class="fas fa-building text-gray-400"></i>
                                Unidad de Negocio
                            </label>
                            <select name="business_unit_id" id="business_unit_id" class="select2-filter">
                                <option value="">Todas las unidades</option>
                                @foreach ($businessUnits as $businessUnit)
                                    <option value="{{ $businessUnit->id }}" @if(request('business_unit_id') == $businessUnit->id) selected @endif>{{ $businessUnit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Market Filter -->
                        <div class="filter-item">
                            <label for="market_id" class="filter-label">
                                <i class="fas fa-globe text-gray-400"></i>
                                Mercado
                            </label>
                            <select name="market_id" id="market_id" class="select2-filter">
                                <option value="">Todos los mercados</option>
                                @foreach ($markets as $market)
                                    <option value="{{ $market->id }}" @if(request('market_id') == $market->id) selected @endif>{{ $market->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <div class="active-filters">
                            @if(request('search'))
                                <span class="filter-badge">
                                    <i class="fas fa-search"></i>
                                    "{{ request('search') }}"
                                </span>
                            @endif
                            @if(request('status'))
                                <span class="filter-badge">
                                    <i class="fas fa-code"></i>
                                    {{ request('status') == 'with_code' ? 'Con Código' : 'Sin Código' }}
                                </span>
                            @endif
                            @if(request('franchise_id'))
                                @php
                                    $selectedFranchise = $franchises->find(request('franchise_id'));
                                @endphp
                                @if($selectedFranchise)
                                    <span class="filter-badge">
                                        <i class="fas fa-store"></i>
                                        {{ $selectedFranchise->name }}
                                    </span>
                                @endif
                            @endif
                            @if(request('brand_id'))
                                @php
                                    $selectedBrand = $brands->find(request('brand_id'));
                                @endphp
                                @if($selectedBrand)
                                    <span class="filter-badge">
                                        <i class="fas fa-tag"></i>
                                        {{ $selectedBrand->name }}
                                    </span>
                                @endif
                            @endif
                            @if(request('business_unit_id'))
                                @php
                                    $selectedBusinessUnit = $businessUnits->find(request('business_unit_id'));
                                @endphp
                                @if($selectedBusinessUnit)
                                    <span class="filter-badge">
                                        <i class="fas fa-building"></i>
                                        {{ $selectedBusinessUnit->name }}
                                    </span>
                                @endif
                            @endif
                            @if(request('market_id'))
                                @php
                                    $selectedMarket = $markets->find(request('market_id'));
                                @endphp
                                @if($selectedMarket)
                                    <span class="filter-badge">
                                        <i class="fas fa-globe"></i>
                                        {{ $selectedMarket->name }}
                                    </span>
                                @endif
                            @endif
                        </div>
                        
                        @if(request()->hasAny(['search', 'status', 'franchise_id', 'brand_id', 'business_unit_id', 'market_id']))
                            <a href="{{ route('erp') }}" class="clear-filters-btn">
                                <i class="fas fa-times mr-2"></i>
                                Limpiar Filtros
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Products Table -->
            <div class="mt-8">
                <div class="overflow-x-auto">
                    <table class="w-full bg-white shadow-md rounded-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Marca</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Franquicia</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Unidad de Negocio</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mercado</th>
                                <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku ?? 'N/A' }}</td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">{{ $product->brand->name ?? 'Sin Marca' }}</td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">{{ $product->franchise->name ?? 'Sin Franquicia' }}</td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">{{ $product->businessUnit->name ?? 'Sin U. Negocio' }}</td>
                                    <td class="p-4 whitespace-nowrap text-sm text-gray-500">{{ $product->market->name ?? 'Sin Mercado' }}</td>
                                    <td class="p-4 whitespace-nowrap text-sm font-medium">
                                        @if ($product->sku)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                {{ $product->sku }}
                                            </span>
                                        @else
                                            <button
                                                class="open-generate-sku-modal-button bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg text-xs"
                                                data-action="{{ route('products.generateSku', $product) }}"
                                                data-product-name="{{ $product->name }}"
                                                data-franchise="{{ $product->franchise->name }}"
                                                data-brand="{{ $product->brand->name }}"
                                                data-business-unit="{{ $product->businessUnit->name }}"
                                                data-market="{{ $product->market->name }}">
                                                Generar Código
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-gray-500">No se encontraron productos que coincidan con los filtros aplicados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination Links -->
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        </div>
    </main>

    <!-- Generate SKU Confirmation Modal -->
    <div id="generate-sku-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95">
            <div class="p-6">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-xl font-semibold text-gray-800">Confirmar Generación de Código</h3>
                    <button id="close-generate-modal-button" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-600">¿Estás seguro de que quieres generar un SKU para el siguiente producto?</p>
                    <p id="modal-generate-product-name" class="font-bold text-lg text-gray-800 mt-2"></p>
                    <div class="mt-4 grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-lg">
                        <div>
                            <span class="font-semibold text-gray-500">Franquicia:</span>
                            <p id="modal-generate-franchise" class="text-gray-800"></p>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-500">Marca:</span>
                            <p id="modal-generate-brand" class="text-gray-800"></p>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-500">U. de Negocio:</span>
                            <p id="modal-generate-business-unit" class="text-gray-800"></p>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-500">Mercado:</span>
                            <p id="modal-generate-market" class="text-gray-800"></p>
                        </div>
                    </div>
                    <form id="generate-sku-form" method="POST" action="" class="mt-6">
                        @csrf
                        <div class="mt-6 flex justify-end space-x-4">
                            <button type="button" id="cancel-generate-button" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg">
                                Confirmar Generación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@vite('resources/js/erp.js')
</body>
</html>