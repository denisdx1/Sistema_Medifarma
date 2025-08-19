<!-- Modal para asignación masiva de mercados -->
<div id="bulk-assign-modal" class="modal-overlay fixed inset-0 bg-gray-600 bg-opacity-50 hidden" style="z-index: 9999; overflow: visible;">
    <div class="relative top-5 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" style="max-height: 80vh; overflow-y: auto;">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Asignación Masiva de Mercados</h3>
                <button type="button" id="close-bulk-assign-modal" 
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="bulk-assign-form">
                    @csrf
                    
                    <!-- Market Selection Section -->
                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                        <h4 class="text-lg font-medium text-gray-800 mb-4">
                            <i class="fas fa-target text-blue-600 mr-2"></i>
                            1. Seleccionar Mercado de Destino
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="target-market" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-bullseye text-blue-500 mr-1"></i>
                                    Mercado de Destino *
                                </label>
                                <select id="target-market" name="target_market" class="bulk-assign-select w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-sm" required>
                                    <option value="">Seleccionar mercado...</option>
                                    <!-- Options will be loaded via JavaScript -->
                                </select>
                                <div class="error-message text-red-500 text-sm mt-1 hidden" id="target-market-error"></div>
                            </div>
                            
                            <div class="flex items-end">
                                <div class="bg-green-100 border border-green-200 rounded-lg p-3 w-full">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-green-600 mr-2"></i>
                                        <span class="text-sm text-green-800 font-medium">Los productos seleccionados se moverán a este mercado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Selection Section -->
                    <div class="bg-yellow-50 rounded-lg p-4 mb-6">
                        <h4 class="text-lg font-medium text-gray-800 mb-4">
                            <i class="fas fa-boxes text-yellow-600 mr-2"></i>
                            2. Filtrar y Seleccionar Productos
                        </h4>
                        
                        <!-- Filters Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="product-search" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-search text-purple-500 mr-1"></i>
                                    Buscar Producto
                                </label>
                                <input type="text" 
                                       id="product-search" 
                                       name="product_search" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-sm" 
                                       placeholder="SKU, descripción, molécula...">
                            </div>
                            
                            <div>
                                <label for="current-market-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-filter text-orange-500 mr-1"></i>
                                    Mercado Actual
                                </label>
                                <select id="current-market-filter" name="current_market_filter" class="bulk-assign-select w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 text-sm">
                                    <option value="">Todos los mercados</option>
                                    <option value="RESTO" selected>RESTO (sin mercado asignado)</option>
                                    <!-- More options will be loaded via JavaScript -->
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <button type="button" id="search-products-btn" class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium py-2 px-4 rounded-md transition-all duration-200 shadow-lg hover:shadow-xl text-sm w-full">
                                    <i class="fas fa-search mr-2"></i>
                                    Buscar Productos
                                </button>
                            </div>
                        </div>
                        
                        <!-- Results Info -->
                        <div class="flex justify-between items-center mb-4">
                            <div id="products-count" class="text-sm text-gray-600">
                                Productos encontrados: <span class="font-bold" id="total-found">0</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="select-all-products" class="bg-green-600 hover:bg-green-700 text-white text-xs py-1 px-3 rounded transition-colors">
                                    <i class="fas fa-check-double mr-1"></i>
                                    Seleccionar Todos
                                </button>
                                <button type="button" id="deselect-all-products" class="bg-gray-600 hover:bg-gray-700 text-white text-xs py-1 px-3 rounded transition-colors">
                                    <i class="fas fa-times mr-1"></i>
                                    Deseleccionar Todos
                                </button>
                            </div>
                        </div>
                        
                        <!-- Products List -->
                        <div id="products-container" class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto bg-white">
                            <div class="p-4 text-center text-gray-500">
                                <i class="fas fa-search text-3xl mb-2 text-gray-300"></i>
                                <p>Utiliza los filtros para buscar productos</p>
                            </div>
                        </div>
                        
                        <!-- Selected Count -->
                        <div class="mt-3 p-3 bg-blue-100 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-blue-800">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Productos seleccionados para asignar:
                                </span>
                                <span class="font-bold text-blue-900" id="selected-count">0</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="border-t px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center">
                    <button type="button" 
                            id="cancel-bulk-assign" 
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition-all duration-200 border border-gray-300 text-sm">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    
                    <button type="button" 
                            id="confirm-bulk-assign" 
                            class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium py-2 px-6 rounded-md transition-all duration-200 shadow-lg hover:shadow-xl text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                        <span class="btn-text">
                            <i class="fas fa-save mr-2"></i>
                            Asignar Mercados
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Personalizada -->
<div id="confirmation-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 99999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <!-- Botón X para cerrar -->
        <div class="absolute top-3 right-3">
            <button type="button" id="close-confirmation-modal" 
                    class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span class="sr-only">Cerrar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="mt-3 text-center">
            <!-- Icono -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            
            <!-- Título -->
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2" id="confirmation-title">
                Confirmar Asignación
            </h3>
            
            <!-- Mensaje -->
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="confirmation-message">
                    ¿Estás seguro de realizar esta acción?
                </p>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button id="confirmation-cancel" type="button" 
                        class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                <button id="confirmation-accept" type="button" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-lg hover:shadow-xl">
                    <i class="fas fa-check mr-2"></i>
                    Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Remover Mercado -->
<div id="remove-market-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 99999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <!-- Botón X para cerrar -->
        <div class="absolute top-3 right-3">
            <button type="button" id="close-remove-market-modal" 
                    class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <span class="sr-only">Cerrar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <div class="mt-3 text-center">
            <!-- Icono -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            
            <!-- Título -->
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
                Remover Mercado
            </h3>
            
            <!-- Información del producto -->
            <div class="mt-2 px-7 py-3">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-left space-y-2">
                        <div class="flex items-center">
                            <i class="fas fa-box text-blue-500 mr-2"></i>
                            <span class="text-sm font-medium text-gray-700">Producto:</span>
                            <span class="text-sm text-gray-900 ml-2" id="remove-product-name">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-tag text-green-500 mr-2"></i>
                            <span class="text-sm font-medium text-gray-700">Mercado Actual:</span>
                            <span class="text-sm text-red-600 ml-2 font-semibold" id="remove-current-market">-</span>
                        </div>
                    </div>
                </div>
                
                <p class="text-sm text-gray-600">
                    ¿Estás seguro de que quieres remover el mercado de este producto?
                </p>
                <p class="text-xs text-red-500 mt-2">
                    Esta acción no se puede deshacer.
                </p>
            </div>
            
            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button id="remove-market-cancel" type="button" 
                        class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                <button id="remove-market-confirm" type="button" 
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 shadow-lg hover:shadow-xl">
                    <i class="fas fa-trash mr-2"></i>
                    Remover Mercado
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Asignar Mercado Individual -->
<div id="assign-market-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" style="z-index: 9999;">
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <!-- Botón X para cerrar -->
            <div class="absolute top-3 right-3 z-50">
                <button type="button" id="close-assign-market-modal" 
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="p-6">
                <!-- Icono y Título -->
                <div class="text-center mb-4">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Asignar Mercado
                    </h3>
                </div>
                
                <!-- Información del producto -->
                <div class="mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <i class="fas fa-box text-blue-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-700">Producto:</span>
                            </div>
                            <div class="ml-6">
                                <span class="text-sm text-gray-900 font-medium" id="assign-product-name">-</span>
                            </div>
                            <div class="flex items-center mt-3">
                                <i class="fas fa-tag text-orange-500 mr-2"></i>
                                <span class="text-sm font-medium text-gray-700">Estado Actual:</span>
                                <span class="text-sm text-orange-600 ml-2 bg-orange-100 px-2 py-1 rounded">Sin mercado asignado</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Selección de mercado -->
                <div class="mb-6">
                    <label for="assign-market-select" class="block text-sm font-medium text-gray-700 mb-3">
                        <i class="fas fa-bullseye text-green-500 mr-2"></i>
                        Seleccionar Mercado *
                    </label>
                    <select id="assign-market-select" name="assign_market" class="assign-market-select w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                        <option value="">Seleccionar mercado...</option>
                        <!-- Options will be loaded via JavaScript -->
                    </select>
                    <div class="error-message text-red-500 text-sm mt-1 hidden" id="assign-market-error"></div>
                </div>
                
                <!-- Botones -->
                <div class="flex justify-between items-center">
                    <button type="button" 
                            id="assign-market-cancel" 
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md border border-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    
                    <button type="button" 
                            id="assign-market-confirm" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-lg hover:shadow-xl"
                            disabled>
                        <span class="btn-text">
                            <i class="fas fa-plus mr-2"></i>
                            Asignar Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Asignando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
