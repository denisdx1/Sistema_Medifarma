<!-- Manage Markets Modal -->
<div id="create-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-4xl transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-cog text-purple-600 mr-2"></i>
                    Gestionar Mercados
                </h3>
                <button id="close-create-market-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left side: Create New Market -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">
                        <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                        Crear Nuevo Mercado
                    </h4>

                    <form id="create-market-form">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="market-name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag text-purple-500 mr-1"></i>
                                Nombre del Mercado *
                            </label>
                            <input type="text" 
                                   id="market-name" 
                                   name="market_name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-sm" 
                                   required 
                                   maxlength="255"
                                   placeholder="Ej: Mercado Premium">
                            <div class="error-message text-red-500 text-sm mt-1 hidden" id="name-error"></div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    id="confirm-create-market" 
                                    class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-medium py-2 px-4 rounded-md transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
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

                <!-- Right side: Edit Existing Market -->
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="text-lg font-medium text-gray-800 mb-4">
                        <i class="fas fa-edit text-green-600 mr-2"></i>
                        Editar Mercado Existente
                    </h4>

                    <form id="edit-market-form">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="select-market" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-purple-500 mr-1"></i>
                                Seleccionar Mercado
                            </label>
                            <select id="select-market" name="market_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-sm">
                                <option value="">Seleccione un mercado...</option>
                                <!-- Options will be loaded via JavaScript -->
                            </select>
                        </div>

                        <div class="mb-4" id="edit-market-fields" style="display: none;">
                            <label for="edit-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag text-purple-500 mr-1"></i>
                                Nombre del Mercado *
                            </label>
                            <input type="text" 
                                   id="edit-market-name" 
                                   name="edit_market_name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 text-sm" 
                                   maxlength="255"
                                   placeholder="Nuevo nombre del mercado">
                            <div class="error-message text-red-500 text-sm mt-1 hidden" id="edit-name-error"></div>
                        </div>

                        <div class="flex justify-end" id="edit-market-actions" style="display: none;">
                            <button type="submit" 
                                    id="confirm-edit-market" 
                                    class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium py-2 px-4 rounded-md transition-all duration-200 shadow-lg hover:shadow-xl text-sm">
                                <span class="btn-text">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar Cambios
                                </span>
                                <span class="btn-loading hidden">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" 
                        id="cancel-create-market" 
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition-all duration-200 border border-gray-300 text-sm">
                    <i class="fas fa-times mr-2"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
