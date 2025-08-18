<!-- Assign Market Modal -->
<div id="assign-market-modal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-2xl transform scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center border-b pb-3 mb-6">
                <h3 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
                    Asignar Mercado
                </h3>
                <button id="close-assign-modal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Datos del Producto -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    Información del Producto
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-gray-500">SKU</label>
                        <p id="modal-product-sku" class="text-sm font-semibold text-gray-800"></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Mercado Actual</label>
                        <p id="modal-current-market" class="text-sm font-semibold text-red-600"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-medium text-gray-500">Descripción</label>
                        <p id="modal-product-description" class="text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Código ATC</label>
                        <p id="modal-product-atc" class="text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Forma Farmacéutica</label>
                        <p id="modal-product-ff" class="text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Molécula</label>
                        <p id="modal-product-molecule" class="text-sm text-gray-800"></p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Tipo</label>
                        <p id="modal-product-type" class="text-sm text-gray-800"></p>
                    </div>
                </div>
            </div>

            <form id="assign-market-form">
                @csrf
                <input type="hidden" id="assign-product-id" name="product_id">
                
                <div class="mb-6">
                    <label for="market-search" class="block text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-search mr-2 text-purple-500"></i>
                        Seleccionar Nuevo Mercado
                    </label>
                    <select id="market-search" name="market_id" class="w-full select2-markets" required>
                        <option value="">Buscar y seleccionar mercado...</option>
                        @if(isset($filterOptions['mercados']) && count($filterOptions['mercados']) > 0)
                            @foreach($filterOptions['mercados'] as $mercado)
                                <option value="{{ $mercado }}">{{ strtoupper($mercado) }}</option>
                            @endforeach
                        @else
                            <option disabled>No hay mercados disponibles</option>
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Puedes buscar escribiendo el nombre del mercado
                    </p>
                </div>

                <div class="flex justify-end space-x-4 mt-6 pt-4 border-t">
                    <button type="button" id="cancel-assign" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2.5 px-6 rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="submit" id="confirm-assign" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors" disabled>
                        <span class="btn-text">
                            <i class="fas fa-check mr-2"></i>
                            Asignar Mercado
                        </span>
                        <span class="btn-loading hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Asignando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
