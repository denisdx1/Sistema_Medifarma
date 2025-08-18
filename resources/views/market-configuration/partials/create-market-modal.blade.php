<!-- Create Market Modal -->
<div id="create-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-lg transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Crear Nuevo Mercado</h3>
                <button id="close-create-market-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="create-market-form">
                @csrf
                
                <div class="mb-4">
                    <label for="market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Mercado *
                    </label>
                    <input type="text" id="market-name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" required maxlength="255">
                    <div class="error-message text-red-500 text-sm mt-1 hidden" id="name-error"></div>
                </div>

                <div class="mb-4">
                    <label for="market-code" class="block text-sm font-medium text-gray-700 mb-2">
                        Código del Mercado *
                    </label>
                    <input type="text" id="market-code" name="code" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" required maxlength="10" style="text-transform: uppercase;">
                    <p class="text-xs text-gray-500 mt-1">Código único para identificar el mercado (máx. 10 caracteres)</p>
                    <div class="error-message text-red-500 text-sm mt-1 hidden" id="code-error"></div>
                </div>

                <div class="mb-4">
                    <label for="market-description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descripción
                    </label>
                    <textarea id="market-description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500" maxlength="500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Descripción opcional del mercado (máx. 500 caracteres)</p>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="market-active" name="is_active" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" checked>
                        <span class="ml-2 text-sm text-gray-700">Mercado activo</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancel-create-market" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                        Cancelar
                    </button>
                    <button type="submit" id="confirm-create-market" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg">
                        <span class="btn-text">Crear Mercado</span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Creando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
