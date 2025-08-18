<!-- Remove Market Confirmation Modal -->
<div id="remove-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Remover Mercado</h3>
                <button id="close-remove-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-6">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-red-100 rounded-full mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">¿Estás seguro?</p>
                        <p class="text-sm text-gray-600">Esta acción no se puede deshacer</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Producto:</p>
                    <p id="remove-product-name" class="font-semibold text-gray-800"></p>
                    
                    <p class="text-sm text-gray-600 mt-2">Mercado actual:</p>
                    <p id="remove-current-market" class="font-semibold text-gray-800"></p>
                </div>
            </div>

            <form id="remove-market-form">
                @csrf
                <input type="hidden" id="remove-product-id" name="product_id">
                
                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancel-remove" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" id="confirm-remove" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                        <span class="btn-text">
                            <i class="fas fa-trash mr-2"></i>Remover Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Removiendo...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
