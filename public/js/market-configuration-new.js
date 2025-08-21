// Market Configuration New - JavaScript para productos IQVIA con Cursor Pagination
document.addEventListener('DOMContentLoaded', function () {
    // JavaScript cargado correctamente

    $(document).ready(function () {
        // Variables globales para cursor pagination
        let currentCursor = null;
        let nextCursor = null;
        let prevCursor = null;
        let cursorsHistory = []; // Historial de cursors para navegación
        let isLoading = false;

        // Cargar datos iniciales
        loadProducts();

        // Función principal para cargar productos con cursor pagination
        function loadProducts(cursor = null, direction = 'next') {
            if (isLoading) return;
            
            isLoading = true;
            
            // Mostrar indicador de carga
            $('#loading-indicator').removeClass('hidden');
            
            const requestData = {
                per_page: 10
            };
            
            // Agregar cursor si existe
            if (cursor) {
                requestData.cursor = cursor;
            }
            
            // Realizar petición AJAX
            $.ajax({
                url: '/market-configuration/productos',
                method: 'GET',
                data: requestData,
                success: function(response) {
                    if (response.success) {
                        displayProducts(response.data);
                        updatePaginationState(response.pagination, cursor, direction);
                        displayPagination();
                        updateTotalCount(response.data.length);
                    } else {
                        showError('Error al cargar productos: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error de conexión:', xhr);
                    showError('Error de conexión al cargar productos');
                },
                complete: function() {
                    $('#loading-indicator').addClass('hidden');
                    isLoading = false;
                }
            });
        }

        // Actualizar estado de paginación con cursor
        function updatePaginationState(pagination, requestedCursor, direction) {
            // Guardar cursors actuales del response
            nextCursor = pagination.next_cursor;
            prevCursor = pagination.prev_cursor;
            
            // Manejar historial de cursors para navegación hacia atrás
            if (direction === 'next' && requestedCursor) {
                // Si vamos hacia adelante, guardar el cursor en el historial
                if (!cursorsHistory.includes(requestedCursor)) {
                    cursorsHistory.push(requestedCursor);
                }
            } else if (direction === 'prev' && requestedCursor) {
                // Si vamos hacia atrás, remover el último cursor del historial
                if (cursorsHistory.length > 0) {
                    cursorsHistory.pop();
                }
            } else if (!requestedCursor) {
                // Primera carga, limpiar historial
                cursorsHistory = [];
            }
            
            currentCursor = requestedCursor;
        }

        // Función para mostrar productos en la tabla
        function displayProducts(products) {
            const tbody = $('#products-table-body');
            tbody.empty();
            
            if (!products || products.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                            <p class="text-lg">No se encontraron productos.</p>
                        </td>
                    </tr>
                `);
                return;
            }
            
            products.forEach(function(product) {
                const row = `
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${product['codigoPresentacion'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800 max-w-xs truncate" title="${product['descripcionPresentacion'] || '-'}">
                                ${product['descripcionPresentacion'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ${product['marcaGenerico'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getEticoPopularClass(product['eticoPopular'])}">
                                ${product['eticoPopular'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                                ${product['molecula'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                ${product['codigoFF3'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                ${product['codigoATC4'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-orange-100 text-orange-800">
                                ${product['descripcionLaboratorio'] || '-'}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getMercadoClass(product['MERCADO'])}">
                                ${product['MERCADO'] || 'Sin mercado'}
                            </span>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Función para obtener clases CSS para Ético/Popular
        function getEticoPopularClass(value) {
            switch(value) {
                case 'ÉTICO':
                    return 'bg-blue-100 text-blue-800';
                case 'POPULAR':
                    return 'bg-green-100 text-green-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        // Función para obtener clases CSS para Mercado
        function getMercadoClass(value) {
            return value ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800';
        }

        // Función para mostrar errores
        function showError(message) {
            alert(message); // Por ahora usamos alert, luego se puede mejorar con toast
        }

        // Función para actualizar contador de registros
        function updateTotalCount(count) {
            $('#total-count span').text(count ? count.toLocaleString() + ' en esta página' : '0');
        }

        // Función para mostrar paginación con cursor navigation
        function displayPagination() {
            const container = $('#pagination-container');
            container.empty();
            
            let paginationHtml = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm text-gray-700">
                        <span>Navegación por páginas - Registros por página: 10</span>
                    </div>
                    <div class="flex items-center space-x-2">
            `;
            
            // Botón Previous
            const hasPrevious = prevCursor !== null || cursorsHistory.length > 0;
            if (hasPrevious) {
                paginationHtml += `
                    <button onclick="goToPrevious()" 
                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 transition-colors">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Anterior
                    </button>
                `;
            } else {
                paginationHtml += `
                    <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-l-md cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-1"></i>
                        Anterior
                    </span>
                `;
            }
            
            // Botón para primera página (solo si no estamos en la primera)
            if (cursorsHistory.length > 0) {
                paginationHtml += `
                    <button onclick="goToFirst()" 
                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-home mr-1"></i>
                        Primera
                    </button>
                `;
            }
            
            // Indicador de página actual (estimación basada en historial)
            const currentPageEstimate = cursorsHistory.length + 1;
            paginationHtml += `
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-purple-600">
                    Página ${currentPageEstimate}
                </span>
            `;
            
            // Botón Next
            if (nextCursor) {
                paginationHtml += `
                    <button onclick="goToNext()" 
                            class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 transition-colors">
                        Siguiente
                        <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                `;
            } else {
                paginationHtml += `
                    <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-gray-100 border border-gray-300 rounded-r-md cursor-not-allowed">
                        Siguiente
                        <i class="fas fa-chevron-right ml-1"></i>
                    </span>
                `;
            }
            
            paginationHtml += `
                    </div>
                </div>
            `;
            
            container.html(paginationHtml);
        }

        // Funciones de navegación con cursor
        function goToNext() {
            if (nextCursor && !isLoading) {
                loadProducts(nextCursor, 'next');
            }
        }

        function goToPrevious() {
            if (!isLoading) {
                if (cursorsHistory.length > 0) {
                    // Usar el cursor del historial para ir hacia atrás
                    const prevCursorFromHistory = cursorsHistory[cursorsHistory.length - 1];
                    loadProducts(prevCursorFromHistory, 'prev');
                } else if (prevCursor) {
                    // Usar el cursor prev del response
                    loadProducts(prevCursor, 'prev');
                }
            }
        }

        function goToFirst() {
            if (!isLoading) {
                // Limpiar historial y cargar primera página
                cursorsHistory = [];
                loadProducts(null, 'first');
            }
        }

        // Exponer funciones globalmente para los botones de paginación
        window.goToNext = goToNext;
        window.goToPrevious = goToPrevious;
        window.goToFirst = goToFirst;
        window.loadProducts = loadProducts;
    });
});
