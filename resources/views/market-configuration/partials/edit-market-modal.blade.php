<!-- Edit Market Modal -->
<div id="edit-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-2xl transform scale-95">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Editar/Expandir Mercado</h3>
                    <p class="text-sm text-gray-600 mt-1">Modifica el nombre del mercado para todos los productos asignados</p>
                </div>
                <button id="close-edit-market-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="edit-market-form">
                @csrf
                <input type="hidden" id="edit-old-market-name" name="old_market_name">
                
                <!-- Current Market Info -->
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <span class="font-semibold text-blue-800">Mercado Actual</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-blue-600">Nombre:</p>
                            <p id="current-market-name" class="font-semibold text-blue-800"></p>
                        </div>
                        <div>
                            <p class="text-sm text-blue-600">Productos asignados:</p>
                            <p id="current-market-products-count" class="font-semibold text-blue-800"></p>
                        </div>
                    </div>
                </div>

                <!-- New Market Name -->
                <div class="mb-6">
                    <label for="new-market-name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-edit mr-2 text-green-500"></i>Nuevo Nombre del Mercado
                    </label>
                    <input type="text" id="new-market-name" name="new_market_name" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Ej: ANTIBIOTICOS_ORAL, DOLOR_TOPICO, etc."
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        Tip: Para expandir un mercado usa formato "NOMBRE_CATEGORIA" (ej: ANTIBIOTICOS_ORAL)
                    </p>
                </div>

                <!-- Examples -->
                <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center mb-3">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                        <span class="font-semibold text-gray-700">Ejemplos de Expansión</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="bg-white p-3 rounded border">
                            <p class="text-gray-600">Actual: <span class="font-semibold">ANTIBIOTICOS</span></p>
                            <p class="text-green-600">Nuevo: <span class="font-semibold">ANTIBIOTICOS_ORAL</span></p>
                        </div>
                        <div class="bg-white p-3 rounded border">
                            <p class="text-gray-600">Actual: <span class="font-semibold">DOLOR</span></p>
                            <p class="text-green-600">Nuevo: <span class="font-semibold">DOLOR_TOPICO</span></p>
                        </div>
                    </div>
                </div>

                <!-- Products Preview -->
                <div class="mb-6">
                    <button type="button" id="toggle-products-preview" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-chevron-right mr-2 transition-transform" id="preview-chevron"></i>
                        <span>Ver productos que serán afectados</span>
                    </button>
                    
                    <div id="products-preview" class="hidden mt-4 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 border-b">
                            <span class="text-sm font-medium text-gray-700">Productos Afectados</span>
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                            <div id="affected-products-list" class="p-4">
                                <!-- Products will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                        <div class="text-yellow-800 text-sm">
                            <p class="font-semibold">Importante:</p>
                            <p>Esta acción cambiará el nombre del mercado para TODOS los productos que lo tengan asignado. La acción no se puede deshacer.</p>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancel-edit-market" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="confirm-edit-market" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                        <span class="btn-text">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
