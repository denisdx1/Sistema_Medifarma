<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos IQVIA - Sistema Medifarma</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="flex h-screen overflow-hidden">

    <x-sidebar/>

    <!-- Main content -->
    <main id="main-content" class="flex-1 p-6 overflow-y-auto transition-all duration-300 ease-in-out">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Productos IQVIA</h1>
                <p class="text-gray-600 mt-1">Visualización de datos de productos con información IQVIA</p>
            </div>
        </header>

        <!-- Loading indicator -->
        <div id="loading-indicator" class="hidden">
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
                <span class="ml-3 text-gray-600">Cargando productos...</span>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Listado de Productos</h3>
                <p id="total-count" class="text-sm text-gray-600">Registros: <span class="font-medium">-</span></p>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Navegación optimizada con cursor - Ideal para grandes volúmenes de datos
                </p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código Presentación</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción Presentación</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Marca Genérico</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ético Popular</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mólecula</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código FF 3</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Código ATC 4</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Descripción Laboratorio</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Mercado</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Los datos se cargarán aquí via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination-container" class="mt-8">
            <!-- La paginación se cargará aquí via JavaScript -->
        </div>
    </main>

    <!-- Include JS -->
    <script src="{{ asset('js/market-configuration-new.js') }}"></script>
</body>
</html>
