<!-- Assign Market Modal -->
<div id="assign-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Asignar Mercado</h3>
                <button id="close-assign-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">Producto:</p>
                <p id="assign-product-name" class="font-semibold text-gray-800"></p>
            </div>

            <form id="assign-market-form">
                @csrf
                <input type="hidden" id="assign-product-id" name="product_id">
                
                <div class="mb-4">
                    <label for="market-search" class="block text-sm font-medium text-gray-700 mb-2">
                        Buscar y Seleccionar Mercado
                    </label>
                    <select id="market-search" name="market_id" class="w-full select2-markets" required>
                        <option value="">Selecciona un mercado...</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="cancel-assign" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" id="confirm-assign" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg" disabled>
                        <span class="btn-text">Asignar Mercado</span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Asignando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
