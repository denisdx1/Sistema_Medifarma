<!-- Bulk Assignment Modal -->
<div id="bulk-assign-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Asignación en Lote</h3>
                <button id="close-bulk-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Productos seleccionados:</p>
                <p id="bulk-selected-count" class="font-semibold text-gray-800 text-lg"></p>
                
                <!-- Lista de productos seleccionados -->
                <div class="mt-3 max-h-32 overflow-y-auto bg-gray-50 rounded-lg p-3">
                    <div id="selected-products-list" class="space-y-1">
                        <!-- Los productos se llenarán dinámicamente -->
                    </div>
                </div>
            </div>

            <form id="bulk-assign-form">
                @csrf
                <input type="hidden" id="bulk-product-ids" name="product_ids">
                
                <div class="mb-4">
                    <label for="bulk-market-search" class="block text-sm font-medium text-gray-700 mb-2">
                        Seleccionar Mercado para Todos
                    </label>
                    <select id="bulk-market-search" name="market_id" class="w-full select2-markets" required>
                        <option value="">Selecciona un mercado...</option>
                    </select>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-0.5"></i>
                        <div class="text-yellow-800 text-sm">
                            <p class="font-semibold">Advertencia:</p>
                            <p>Esta acción asignará el mercado seleccionado a todos los productos marcados. Los productos que ya tengan un mercado asignado serán reemplazados.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="cancel-bulk-assign" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" id="confirm-bulk-assign" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg" disabled>
                        <span class="btn-text">Asignar a Todos</span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Asignando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
