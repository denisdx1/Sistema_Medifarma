<!-- Create Market Modal -->
<div id="create-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-lg transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-plus-circle text-purple-600 mr-2"></i>
                    Crear Nuevo Mercado
                </h3>
                <button id="close-create-market-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            Los mercados se crean automáticamente cuando los asignas a productos en la tabla de materiales.
                        </p>
                    </div>
                </div>
            </div>

            <form id="create-market-form">
                @csrf
                
                <div class="mb-6">
                    <label for="market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag text-purple-500 mr-1"></i>
                        Nombre del Mercado *
                    </label>
                    <input type="text" 
                           id="market-name" 
                           name="market_name" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" 
                           required 
                           maxlength="255"
                           placeholder="Ej: Mercado Premium">
                    <div class="error-message text-red-500 text-sm mt-1 hidden" id="name-error"></div>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-lightbulb text-yellow-400 mr-1"></i>
                        Este será el nombre que aparecerá en la columna "Mercado" de los materiales
                    </p>
                </div>

                <div class="mb-6">
                    <label for="market-description" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-alt text-purple-500 mr-1"></i>
                        Descripción
                    </label>
                    <textarea id="market-description" 
                              name="description" 
                              rows="3" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200" 
                              maxlength="500"
                              placeholder="Descripción opcional del mercado..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Descripción opcional (máx. 500 caracteres)</p>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" 
                            id="cancel-create-market" 
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-6 rounded-lg transition-all duration-200 border border-gray-300">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" 
                            id="confirm-create-market" 
                            class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                        <span class="btn-text">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Creando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
