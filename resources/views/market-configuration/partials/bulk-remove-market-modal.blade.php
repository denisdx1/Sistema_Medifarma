<!-- Bulk Market Removal Modal -->
<div id="bulk-remove-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-4xl transform scale-95 max-h-[90vh] overflow-hidden">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex justify-between items-center border-b p-6 bg-gradient-to-r from-red-50 to-pink-50">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Quitar Mercado de Múltiples Productos</h3>
                    <p class="text-sm text-gray-600 mt-1">Los productos seleccionados se asignarán a "RESTO"</p>
                </div>
                <button id="close-bulk-remove-market-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="bulk-remove-market-form">
                    @csrf
                    
                    <!-- Market Filter -->
                    <div class="mb-6">
                        <label for="market-filter-selector" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-filter mr-2 text-red-500"></i>Filtrar por Mercado (Opcional)
                        </label>
                        <select id="market-filter-selector" class="w-full select2-markets">
                            <option value="">Todos los mercados</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Filtra para mostrar solo productos de un mercado específico</p>
                    </div>

                    <!-- Products Filter -->
                    <div class="mb-4">
                        <label for="remove-product-search-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2 text-red-500"></i>Buscar Productos
                        </label>
                        <input type="text" id="remove-product-search-filter" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                               placeholder="Buscar por SKU, descripción, molécula...">
                    </div>

                    <!-- Products Selection Table -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="select-all-remove-products" class="form-checkbox text-red-600">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Seleccionar Todo</span>
                                </label>
                                <span id="selected-remove-count" class="text-sm text-gray-600">0 productos seleccionados</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="filter-only-with-market" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded text-xs font-medium transition-colors">
                                    Solo Con Mercado
                                </button>
                                <button type="button" id="clear-remove-filters" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded text-xs font-medium transition-colors">
                                    Limpiar
                                </button>
                            </div>
                        </div>
                        
                        <div class="max-h-96 overflow-y-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selección</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mercado Actual</th>
                                    </tr>
                                </thead>
                                <tbody id="remove-products-table-body" class="bg-white divide-y divide-gray-200">
                                    <!-- Products will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Warning Alert -->
                    <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-0.5"></i>
                            <div class="text-red-800 text-sm">
                                <p class="font-semibold">¡Atención!</p>
                                <p>Esta acción quitará el mercado asignado de todos los productos seleccionados y los asignará a "RESTO". Esta acción no se puede deshacer.</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="border-t p-6 bg-gray-50 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span id="total-remove-products-count">0 productos disponibles</span>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="cancel-bulk-remove-market" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" form="bulk-remove-market-form" id="confirm-bulk-remove-market" 
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition-colors" disabled>
                        <span class="btn-text">
                            <i class="fas fa-trash mr-2"></i>Quitar Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Quitando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
