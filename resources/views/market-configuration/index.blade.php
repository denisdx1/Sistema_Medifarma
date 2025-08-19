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
    <style>
        /* Enhanced Select2 Styling */
        .select2-container--default .select2-selection--single {
            height: 36px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 6px !important;
            background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%) !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .select2-container--default .select2-selection--single:hover {
            border-color: #c7d2fe !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #8b5cf6 !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.12), 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            outline: none !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1f2937 !important;
            line-height: 32px !important;
            padding-left: 12px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af !important;
            font-weight: 400 !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px !important;
            right: 10px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #6b7280 transparent transparent transparent !important;
            border-width: 6px 5px 0 5px !important;
        }
        
        .select2-dropdown {
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
            background: white !important;
            margin-top: 4px !important;
            z-index: 9999 !important;
        }
        
        .select2-container--default .select2-results__option {
            padding: 12px 16px !important;
            font-size: 14px !important;
            transition: all 0.15s ease-in-out !important;
            border-bottom: 1px solid #f3f4f6 !important;
        }
        
        .select2-container--default .select2-results__option:last-child {
            border-bottom: none !important;
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
            color: white !important;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db !important;
            border-radius: 6px !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            margin: 12px !important;
            width: calc(100% - 24px) !important;
            background: #f9fafb !important;
            transition: all 0.15s ease-in-out !important;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #8b5cf6 !important;
            background: white !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1) !important;
        }
        
        /* Ensure Select2 dropdown appears above everything */
        .select2-container {
            z-index: 9998 !important;
        }
        
        .select2-container--open {
            z-index: 9999 !important;
        }
        
        .select2-dropdown {
            z-index: 10000 !important;
        }
        
        /* Prevent dropdown from being cut off */
        .select2-container--open .select2-dropdown--below {
            border-top: none !important;
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }
        
        .select2-container--open .select2-dropdown--above {
            border-bottom: none !important;
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }
        
        /* Custom dropdown class for enhanced z-index */
        .select2-dropdown-custom {
            z-index: 10001 !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.08) !important;
        }
        
        /* Enhanced filter section styling */
        .filter-section {
            position: relative;
            z-index: 1;
        }
        
        .filter-item {
            position: relative;
            z-index: 2;
        }
        .filter-section {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }
        
        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #8b5cf6, #7c3aed, #6d28d9);
        }
        
        .filter-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-title i {
            color: #8b5cf6;
            font-size: 16px;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .filter-item {
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .filter-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .filter-label i {
            font-size: 14px;
        }
        
        /* Search input styling */
        #search {
            height: 36px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 500;
            background: linear-gradient(145deg, #ffffff 0%, #f9fafb 100%);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        #search:hover {
            border-color: #c7d2fe;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        #search:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.12), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            outline: none;
            background: white;
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
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-badge {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 6px 14px;
            border-radius: 24px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px -1px rgba(139, 92, 246, 0.4);
            animation: slideInUp 0.3s ease-out;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .clear-filters-btn {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .clear-filters-btn:hover {
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(75, 85, 99, 0.4);
        }
        
        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6b7280;
            z-index: 10;
        }
        
        /* Animation for filter changes */
        .filter-item {
            animation: fadeInScale 0.3s ease-out;
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        /* Stats card enhancements */
        .stats-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            margin-bottom: 12px;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            min-width: 300px;
            max-width: 400px;
            overflow: hidden;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.hide {
            transform: translateX(400px);
            opacity: 0;
        }

        .toast-success {
            border-left: 4px solid #10b981;
        }

        .toast-error {
            border-left: 4px solid #ef4444;
        }

        .toast-warning {
            border-left: 4px solid #f59e0b;
        }

        .toast-info {
            border-left: 4px solid #3b82f6;
        }

        .toast-content {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toast-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .toast-success .toast-icon {
            background: #dcfce7;
            color: #166534;
        }

        .toast-error .toast-icon {
            background: #fee2e2;
            color: #991b1b;
        }

        .toast-warning .toast-icon {
            background: #fef3c7;
            color: #92400e;
        }

        .toast-info .toast-icon {
            background: #dbeafe;
            color: #1e40af;
        }

        .toast-message {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            line-height: 1.4;
        }

        .toast-close {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            border: none;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6b7280;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .toast-close:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, rgba(0,0,0,0.1), rgba(0,0,0,0.2));
            transition: width linear;
        }

        .toast-success .toast-progress {
            background: linear-gradient(90deg, #10b981, #059669);
        }

        .toast-error .toast-progress {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .toast-warning .toast-progress {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .toast-info .toast-progress {
            background: linear-gradient(90deg, #3b82f6, #2563eb);
        }

        /* Create Market Modal Styles */
        #create-market-modal .modal-container {
            animation: modalSlideIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        #create-market-modal input:focus,
        #create-market-modal textarea:focus {
            transform: translateY(-1px);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1), 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        #create-market-modal .bg-blue-50 {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        #create-market-modal button:hover {
            transform: translateY(-1px);
        }

        #create-market-modal .error-message {
            animation: errorShake 0.3s ease-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }

        .toast {
            min-width: 300px;
            transition: transform 0.3s ease-in-out;
        }

        .toast.translate-x-full {
            transform: translateX(100%);
        }
        
        /* Quick search styling */
        #search-quick {
            height: 36px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 500;
            background: white;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        #search-quick:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.12);
            outline: none;
        }
        
        /* Filter content animation */
        #filter-content {
            transition: all 0.3s ease-in-out;
        }
        
        /* Active filters count badge */
        #active-filters-count {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <x-sidebar/>

    <!-- Main content -->
    <main id="main-content" class="flex-1 p-6 overflow-y-auto transition-all duration-300 ease-in-out">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Configuración de Mercado - Materiales</h1>
                <p class="text-gray-600 mt-1">Gestiona la asignación de mercados a los materiales farmacéuticos</p>
            </div>
                        <div class="flex flex-wrap gap-2">
                <!-- Export button - available for all roles -->
                <button id="export-unassigned-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-3 rounded-md text-sm flex items-center">
                    <i class="fas fa-download mr-1.5"></i>
                    Exportar Sin Mercado
                </button>
                
                <!-- Create/Edit buttons - only for Admin and Product Manager -->
                @if(Auth::user()->isAdmin() || Auth::user()->isProductManager())
                    <button id="manage-markets-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-3 rounded-md text-sm flex items-center">
                        <i class="fas fa-cog mr-1.5"></i>
                        Gestionar Mercados
                    </button>
                    
                    <!-- New bulk operations buttons -->
                    <div class="border-l border-gray-300 pl-2 flex gap-2">
                        <!-- Bulk operations removed -->
                    </div>
                @else
                    <!-- Read-only indicators for BI role -->
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        <span class="text-sm font-medium">Modo Solo Lectura</span>
                    </div>
                @endif
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg border stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-capsules text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Materiales</p>
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['total_materials']) }}</p>
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
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['sin_mercado_materials']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg border stats-card">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-map-marker-alt text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Con Mercado</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['con_mercado_materials']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        

        <!-- Compact Collapsible Filters Section -->
        <div class="filter-section p-4 mb-6">
            <!-- Filter Header with Toggle -->
            <div class="flex justify-between items-center cursor-pointer" onclick="toggleFilters()">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filtros de Búsqueda
                    <span id="active-filters-count" class="hidden ml-2 px-2 py-1 bg-purple-100 text-purple-700 text-xs rounded-full font-semibold"></span>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Quick Search - Always Visible -->
                    <div class="flex-1 min-w-64">
                        <div class="relative">
                            <input type="text" name="search" id="search-quick" 
                                   placeholder="Búsqueda rápida..." 
                                   value="{{ request('search') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 transition-all duration-150 text-sm">
                            <div id="search-loading-quick" class="absolute right-3 top-2.5 hidden">
                                <svg class="animate-spin h-4 w-4 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Toggle Button -->
                    <button type="button" class="flex items-center gap-2 px-3 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-sm font-medium">
                        <span id="filter-toggle-text">Más filtros</span>
                        <i id="filter-toggle-icon" class="fas fa-chevron-down transition-transform duration-200"></i>
                    </button>
                </div>
            </div>
            
            <!-- Collapsible Filter Content -->
            <div id="filter-content" class="hidden mt-4 pt-4 border-t border-gray-200">
                <form action="{{ route('market-configuration.index') }}" method="GET" id="filters-form">
                    <!-- Hidden search input to sync with quick search -->
                    <input type="hidden" name="search" id="search-hidden" value="{{ request('search') }}">
                    
                    <div class="filter-grid">
                    <!-- Market Status Filter -->
                    <div class="filter-item">
                        <label for="market_status" class="filter-label">
                            <i class="fas fa-globe text-gray-400"></i>
                            Estado de Mercado
                        </label>
                        <select name="market_status" id="market_status" class="select2-filter">
                            <option value="">Todos los estados</option>
                            <option value="with_market" @if(request('market_status') == 'with_market') selected @endif>Con Mercado Asignado</option>
                            <option value="without_market" @if(request('market_status') == 'without_market') selected @endif>Sin Mercado Asignado</option>
                        </select>
                    </div>

                    <!-- Marca/Genérico Filter -->
                    <div class="filter-item">
                        <label for="marca_generico" class="filter-label">
                            <i class="fas fa-certificate text-gray-400"></i>
                            Marca/Genérico
                        </label>
                        <select name="marca_generico" id="marca_generico" class="select2-filter">
                            <option value="">Todos los tipos</option>
                            @foreach ($filterOptions['marcas_genericos'] as $option)
                                <option value="{{ $option['value'] }}" @if(request('marca_generico') == $option['value']) selected @endif>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ético/Popular Filter -->
                    <div class="filter-item">
                        <label for="etico_popular" class="filter-label">
                            <i class="fas fa-prescription-bottle-alt text-gray-400"></i>
                            Ético/Popular
                        </label>
                        <select name="etico_popular" id="etico_popular" class="select2-filter">
                            <option value="">Todos los tipos</option>
                            @foreach ($filterOptions['etico_popular'] as $option)
                                <option value="{{ $option['value'] }}" @if(request('etico_popular') == $option['value']) selected @endif>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mercado Filter -->
                    <div class="filter-item">
                        <label for="mercado" class="filter-label">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                            Mercado
                        </label>
                        <select name="mercado" id="mercado" class="select2-filter">
                            <option value="">Todos los mercados</option>
                            <option value="sin_asignar" @if(request('mercado') == 'sin_asignar') selected @endif>
                                Sin Asignar
                            </option>
                            @foreach ($filterOptions['mercados'] as $mercado)
                                <option value="{{ $mercado }}" @if(request('mercado') == $mercado) selected @endif>
                                    {{ strtoupper($mercado) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Código ATC Filter -->
                    <div class="filter-item">
                        <label for="codigo_atc" class="filter-label">
                            <i class="fas fa-dna text-gray-400"></i>
                            Código ATC
                        </label>
                        <select name="codigo_atc" id="codigo_atc" class="select2-filter">
                            <option value="">Todos los códigos ATC</option>
                            @foreach ($filterOptions['codigos_atc'] as $atc)
                                <option value="{{ $atc->Código_ATC_4 }}" @if(request('codigo_atc') == $atc->Código_ATC_4) selected @endif>
                                    {{ $atc->Código_ATC_4 }} - {{ $atc->Descripción_ATC_4 }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Código FF Filter -->
                    <div class="filter-item">
                        <label for="codigo_ff" class="filter-label">
                            <i class="fas fa-pills text-gray-400"></i>
                            Forma Farmacéutica
                        </label>
                        <select name="codigo_ff" id="codigo_ff" class="select2-filter">
                            <option value="">Todas las formas</option>
                            @foreach ($filterOptions['codigos_ff'] as $ff)
                                <option value="{{ $ff->Código_FF_3 }}" @if(request('codigo_ff') == $ff->Código_FF_3) selected @endif>
                                    {{ $ff->Código_FF_3 }} - {{ $ff->Descripción_FF_3 }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Laboratorio Filter -->
                    <div class="filter-item">
                        <label for="laboratorio" class="filter-label">
                            <i class="fas fa-industry text-gray-400"></i>
                            Laboratorio
                        </label>
                        <select name="laboratorio" id="laboratorio" class="select2-filter">
                            <option value="">Todos los laboratorios</option>
                            @foreach ($filterOptions['laboratorios'] as $laboratorio)
                                <option value="{{ $laboratorio }}" @if(request('laboratorio') == $laboratorio) selected @endif>
                                    {{ $laboratorio }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Corporación Filter -->
                    <div class="filter-item">
                        <label for="corporacion" class="filter-label">
                            <i class="fas fa-building text-gray-400"></i>
                            Corporación
                        </label>
                        <select name="corporacion" id="corporacion" class="select2-filter">
                            <option value="">Todas las corporaciones</option>
                            @foreach ($filterOptions['corporaciones'] as $corporacion)
                                <option value="{{ $corporacion }}" @if(request('corporacion') == $corporacion) selected @endif>
                                    {{ $corporacion }}
                                </option>
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
                        @if(request('market_status'))
                            <span class="filter-badge">
                                <i class="fas fa-globe"></i>
                                {{ request('market_status') == 'with_market' ? 'CON MERCADO' : 'SIN MERCADO' }}
                            </span>
                        @endif
                        @if(request('marca_generico'))
                            <span class="filter-badge">
                                <i class="fas fa-certificate"></i>
                                {{ strtoupper(request('marca_generico')) }}
                            </span>
                        @endif
                        @if(request('etico_popular'))
                            <span class="filter-badge">
                                <i class="fas fa-prescription-bottle-alt"></i>
                                {{ strtoupper(request('etico_popular')) }}
                            </span>
                        @endif
                        @if(request('codigo_atc'))
                            <span class="filter-badge">
                                <i class="fas fa-dna"></i>
                                ATC: {{ request('codigo_atc') }}
                            </span>
                        @endif
                        @if(request('codigo_ff'))
                            <span class="filter-badge">
                                <i class="fas fa-pills"></i>
                                FF: {{ request('codigo_ff') }}
                            </span>
                        @endif
                        @if(request('mercado'))
                            <span class="filter-badge">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ request('mercado') == 'sin_asignar' ? 'SIN ASIGNAR' : strtoupper(request('mercado')) }}
                            </span>
                        @endif
                        @if(request('laboratorio'))
                            <span class="filter-badge">
                                <i class="fas fa-flask"></i>
                                LAB: {{ strtoupper(request('laboratorio')) }}
                            </span>
                        @endif
                        @if(request('corporacion'))
                            <span class="filter-badge">
                                <i class="fas fa-building"></i>
                                CORP: {{ strtoupper(request('corporacion')) }}
                            </span>
                        @endif
                    </div>
                    
                    @if(request()->hasAny(['search', 'market_status', 'marca_generico', 'etico_popular', 'codigo_atc', 'codigo_ff', 'mercado', 'laboratorio', 'corporacion']))
                        <a href="{{ route('market-configuration.index') }}" class="clear-filters-btn">
                            <i class="fas fa-times mr-2"></i>
                            Limpiar Filtros
                        </a>
                    @endif
                </div>
                </form>
            </div>
        </div>

        <!-- Products Table Section -->
        <div class="bg-white rounded-lg border mb-8">
            <!-- Table Actions -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        
                    </div>
                    <div class="text-sm text-gray-600 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span id="products-count">{{ $products->total() }}</span> productos encontrados
                    </div>
                </div>
            </div>

        <!-- Products Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código ATC</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código FF</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Molécula</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mercado</th>
                            <th class="p-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $material)
                            <tr class="hover:bg-gray-50 product-row" data-product-id="{{ $material->SKU }}">
                                <td class="p-2">
                                    @if($material->SKU)
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded">{{ $material->SKU }}</span>
                                    @else
                                        <span class="text-gray-400 text-xs">Sin SKU</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    <div>
                                        <p class="font-medium text-gray-900 text-xs">{{ $material->Descripción_Presentación }}</p>
                                    </div>
                                </td>
                                <td class="p-2">
                                    <div>
                                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-1.5 py-0.5 rounded block mb-1">{{ $material->Código_ATC_4 ?? 'N/A' }}</span>
                                        @if($material->Descripción_ATC_4)
                                            <p class="text-xs text-gray-500">{{ Str::limit($material->Descripción_ATC_4, 25) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-2">
                                    <div>
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-1.5 py-0.5 rounded block mb-1">{{ $material->Código_FF_3 ?? 'N/A' }}</span>
                                        @if($material->Descripción_FF_3)
                                            <p class="text-xs text-gray-500">{{ Str::limit($material->Descripción_FF_3, 25) }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-2">
                                    <span class="text-xs text-gray-900">{{ $material->Molécula ?? 'N/A' }}</span>
                                </td>
                                <td class="p-2">
                                    @if(strtoupper($material->Marca_Genérico) == 'MARCA')
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-1.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-certificate mr-1 text-xs"></i>
                                            <span class="text-xs">Marca</span>
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-1.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-clone mr-1 text-xs"></i>
                                            <span class="text-xs">Genérico</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if(strtoupper($material->Ético_Popular) == 'ÉTICO' || strtoupper($material->Ético_Popular) == 'ETICO')
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-1.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-prescription-bottle-alt mr-1 text-xs"></i>
                                            <span class="text-xs">Ético</span>
                                        </span>
                                    @else
                                        <span class="bg-orange-100 text-orange-800 text-xs font-medium px-1.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-pills mr-1 text-xs"></i>
                                            <span class="text-xs">Popular</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if(!empty($material->Mercado) && $material->Mercado !== 'null' && $material->Mercado !== 'NULL' && $material->Mercado !== 'RESTO')
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-1.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-map-marker-alt mr-1 text-xs"></i>
                                            <span class="text-xs">{{ $material->Mercado }}</span>
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-1.5 py-0.5 rounded flex items-center w-fit">
                                            <i class="fas fa-exclamation-triangle mr-1 text-xs"></i>
                                            <span class="text-xs">RESTO</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    @if(Auth::user()->isAdmin() || Auth::user()->isProductManager())
                                        <!-- Full access for Admin and Product Manager -->
                                        <div class="flex space-x-1">
                                            @if(!empty($material->Mercado) && $material->Mercado !== 'null' && $material->Mercado !== 'NULL' && $material->Mercado !== 'RESTO')
                                                <button class="remove-market-btn bg-red-600 hover:bg-red-700 text-white text-xs font-medium py-1 px-2 rounded" 
                                                    data-product-id="{{ $material->SKU }}"
                                                    data-product-name="{{ $material->Descripción_Presentación }}"
                                                    data-current-market="{{ $material->Mercado }}"
                                                    title="Quitar mercado">
                                                    <i class="fas fa-times mr-1 text-xs"></i>
                                                    <span class="text-xs">Quitar</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Read-only for Business Intelligence -->
                                        <div class="flex items-center">
                                            <span class="bg-gray-100 text-gray-500 text-xs font-medium py-1 px-2 rounded">
                                                <i class="fas fa-eye mr-1 text-xs"></i>
                                                Solo lectura
                                            </span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->isAdmin() || Auth::user()->isProductManager() ? '10' : '9' }}" class="p-6 text-center text-gray-500">
                                    <i class="fas fa-search text-3xl mb-3"></i>
                                    <p class="text-sm">No se encontraron materiales que coincidan con los filtros aplicados.</p>
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

    <!-- Create Market Modal -->
    @include('market-configuration.partials.create-market-modal')

    <!-- Remove Market Confirmation Modal -->
    @include('market-configuration.partials.remove-market-modal')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Laravel Routes Configuration for JavaScript -->
    <script>
        // Define all Laravel routes for JavaScript access
        window.MarketConfigRoutes = {
            createMarket: '{{ route("market-configuration.create-market") }}',
            getMarkets: '{{ route("market-configuration.get-markets") }}',
            getMarketsPaginated: '{{ route("market-configuration.get-markets-paginated") }}',
            removeMarketFromMaterial: '{{ route("market-configuration.remove-market-from-material") }}',
            productsByMarket: '{{ route("market-configuration.products-by-market") }}',
            editMarketName: '{{ route("market-configuration.edit-market-name") }}'
        };
        
        // CSRF Token for AJAX requests
        window.csrfToken = '{{ csrf_token() }}';
        
        // User role for JavaScript access
        window.userRole = '{{ Auth::user()->role }}';
        window.canEdit = {{ Auth::user()->isAdmin() || Auth::user()->isProductManager() ? 'true' : 'false' }};
    </script>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>
    
    <script>
        // Toggle filters functionality
        function toggleFilters() {
            const content = document.getElementById('filter-content');
            const icon = document.getElementById('filter-toggle-icon');
            const text = document.getElementById('filter-toggle-text');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('fa-chevron-up');
                icon.classList.remove('fa-chevron-down');
                text.textContent = 'Ocultar filtros';
            } else {
                content.classList.add('hidden');
                icon.classList.add('fa-chevron-down');
                icon.classList.remove('fa-chevron-up');
                text.textContent = 'Más filtros';
            }
        }
        
        // Sync quick search with hidden search input
        document.addEventListener('DOMContentLoaded', function() {
            const quickSearch = document.getElementById('search-quick');
            const hiddenSearch = document.getElementById('search-hidden');
            
            // Update hidden input when quick search changes
            quickSearch.addEventListener('input', function() {
                hiddenSearch.value = this.value;
                
                // Auto-submit after a delay (debounce)
                clearTimeout(window.searchTimeout);
                window.searchTimeout = setTimeout(() => {
                    // Show loading
                    document.getElementById('search-loading-quick').classList.remove('hidden');
                    
                    // Submit form
                    const form = document.getElementById('filters-form');
                    const formData = new FormData(form);
                    const searchParams = new URLSearchParams(formData);
                    
                    // Update URL and reload
                    window.location.href = '{{ route("market-configuration.index") }}?' + searchParams.toString();
                }, 500);
            });
            
            // Count active filters
            updateActiveFiltersCount();
        });
        
        function updateActiveFiltersCount() {
            const params = new URLSearchParams(window.location.search);
            let count = 0;
            
            // Count non-empty parameters (excluding page)
            for (const [key, value] of params) {
                if (key !== 'page' && value.trim() !== '') {
                    count++;
                }
            }
            
            const countElement = document.getElementById('active-filters-count');
            if (count > 0) {
                countElement.textContent = count;
                countElement.classList.remove('hidden');
            } else {
                countElement.classList.add('hidden');
            }
        }
    </script>
    
    @vite('resources/js/market-configuration.js')
</body>
</html>