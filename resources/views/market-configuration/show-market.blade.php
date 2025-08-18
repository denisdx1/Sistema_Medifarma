<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $market->name }} - Configuración de Mercado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    @vite('resources/css/erp.css')
</head>
<body class="flex h-screen overflow-hidden">

    <x-sidebar/>

    <!-- Main content -->
    <main id="main-content" class="flex-1 p-6 overflow-y-auto transition-all duration-300 ease-in-out">
        <!-- Header -->
        <header class="flex justify-between items-center mb-8">
            <div>
                <nav class="text-sm mb-2">
                    <a href="{{ route('market-configuration.index') }}" class="text-purple-600 hover:text-purple-800">Configuración de Mercado</a>
                    <span class="text-gray-500 mx-2">/</span>
                    <span class="text-gray-800">{{ $market->name }}</span>
                </nav>
                <h1 class="text-2xl font-semibold text-gray-800">{{ $market->formatted_name }}</h1>
                <p class="text-gray-600 mt-1">{{ $products->total() }} productos asignados a este mercado</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('market-configuration.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </a>
            </div>
        </header>

        <!-- Market Information Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Información del Mercado</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre:</dt>
                            <dd class="text-sm text-gray-900">{{ $market->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Código:</dt>
                            <dd class="text-sm text-gray-900">{{ $market->code }}</dd>
                        </div>
                        @if($market->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Descripción:</dt>
                            <dd class="text-sm text-gray-900">{{ $market->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Estado</h3>
                    <div class="flex items-center">
                        @if($market->is_active)
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                Activo
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 text-sm font-medium px-3 py-1 rounded-full flex items-center">
                                <i class="fas fa-times-circle mr-2"></i>
                                Inactivo
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Estadísticas</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total de Productos:</dt>
                            <dd class="text-lg font-bold text-purple-600">{{ $products->total() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Creado:</dt>
                            <dd class="text-sm text-gray-900">{{ $market->created_at->format('d/m/Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Productos Asignados</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Producto</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SKU</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Marca</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Franquicia</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">U. Negocio</th>
                            <th class="p-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Asignación</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50">
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
                                    <span class="text-sm text-gray-500">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4"></i>
                                    <p class="text-lg">No hay productos asignados a este mercado.</p>
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
                {{ $products->links() }}
            </div>
        @endif
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @vite('resources/js/erp.js')
</body>
</html>
