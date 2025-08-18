<!-- Bulk Market Assignment Modal -->
<div id="bulk-assign-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-4xl transform scale-95 max-h-[90vh] overflow-hidden">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex justify-between items-center border-b p-6 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Asignar Mercado a Múltiples Productos</h3>
                    <p class="text-sm text-gray-600 mt-1">Selecciona los productos y asigna un mercado en lote</p>
                </div>
                <button id="close-bulk-assign-market-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="bulk-assign-market-form">
                    @csrf
                    
                    <!-- Market Selection -->
                    <div class="mb-6">
                        <label for="bulk-market-selector" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2 text-blue-500"></i>Seleccionar Mercado
                        </label>
                        <div class="flex gap-3">
                            <select id="bulk-market-selector" name="market_id" class="flex-1 select2-markets" required>
                                <option value="">Selecciona un mercado...</option>
                            </select>
                            <button type="button" id="create-new-market-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>Nuevo
                            </button>
                        </div>
                    </div>

                    <!-- Products Filter -->
                    <div class="mb-4">
                        <label for="product-search-filter" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-2 text-blue-500"></i>Buscar Productos
                        </label>
                        <input type="text" id="product-search-filter" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Buscar por SKU, descripción, molécula...">
                    </div>

                    <!-- Products Selection Table -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="select-all-products" class="form-checkbox text-blue-600">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Seleccionar Todo</span>
                                </label>
                                <span id="selected-count" class="text-sm text-gray-600">0 productos seleccionados</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" id="filter-no-market" class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-3 py-1 rounded text-xs font-medium transition-colors">
                                    Solo Sin Mercado
                                </button>
                                <button type="button" id="filter-with-market" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded text-xs font-medium transition-colors">
                                    Solo Con Mercado
                                </button>
                                <button type="button" id="clear-filters" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded text-xs font-medium transition-colors">
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
                                <tbody id="products-table-body" class="bg-white divide-y divide-gray-200">
                                    <!-- Products will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Warning Alert -->
                    <div id="assignment-warning" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 hidden">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                            <div class="text-yellow-800 text-sm">
                                <p class="font-semibold">Advertencia:</p>
                                <p>Algunos productos ya tienen mercados asignados. Esta acción los reemplazará con el nuevo mercado seleccionado.</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="border-t p-6 bg-gray-50 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span id="total-products-count">0 productos disponibles</span>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="cancel-bulk-assign-market" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" form="bulk-assign-market-form" id="confirm-bulk-assign-market" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors" disabled>
                        <span class="btn-text">
                            <i class="fas fa-tags mr-2"></i>Asignar Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Asignando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
