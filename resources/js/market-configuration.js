// Market Configuration JavaScript - Cleaned version without assignment functionality
document.addEventListener('DOMContentLoaded', function () {
    console.log('Market Configuration clean JavaScript loaded');

    $(document).ready(function () {
        // --- Sidebar Toggle Functionality ---
        initializeSidebar();

        // Initialize Select2 for all filter dropdowns
        $('.select2-filter').each(function () {
            $(this).select2({
                placeholder: $(this).find('option:first').text(),
                allowClear: true,
                width: '100%',
                dropdownParent: $('body'),
                dropdownCssClass: 'select2-dropdown-custom',
                language: {
                    noResults: function () {
                        return "No se encontraron resultados";
                    },
                    searching: function () {
                        return "Buscando...";
                    },
                    loadingMore: function () {
                        return "Cargando más resultados...";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
        });

        // Add custom styling for Select2 clear buttons and modal
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .select2-selection__clear {
                    color: #dc3545 !important;
                    font-size: 16px !important;
                    font-weight: bold !important;
                    margin-right: 10px !important;
                    padding: 4px 6px !important;
                    border-radius: 50% !important;
                    background-color: rgba(220, 53, 69, 0.1) !important;
                    transition: all 0.2s ease !important;
                    cursor: pointer !important;
                    line-height: 1 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 24px !important;
                    height: 24px !important;
                }
                .select2-selection__clear:hover {
                    background-color: #dc3545 !important;
                    color: white !important;
                    transform: scale(1.1) !important;
                }
                .select2-container--default .select2-selection--single .select2-selection__clear {
                    position: absolute !important;
                    right: 24px !important;
                    top: 50% !important;
                    transform: translateY(-50%) !important;
                    z-index: 10 !important;
                }
                .select2-container--default .select2-selection--single {
                    padding-right: 50px !important;
                }
                .select2-selection__arrow {
                    right: 8px !important;
                }
                /* Improve overall Select2 appearance */
                .select2-container--default .select2-selection--single {
                    border: 1px solid #d1d5db !important;
                    border-radius: 6px !important;
                    height: 38px !important;
                    padding-left: 12px !important;
                }
                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    line-height: 36px !important;
                    color: #374151 !important;
                }
                .select2-container--default .select2-selection--single:focus {
                    border-color: #8b5cf6 !important;
                    outline: none !important;
                    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1) !important;
                }
                /* Modal specific styles */
                #bulk-assign-modal {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    z-index: 9999 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                }
                #bulk-assign-modal.hidden {
                    display: none !important;
                }
                #bulk-assign-modal .bg-white {
                    position: relative !important;
                    z-index: 10000 !important;
                    margin: auto !important;
                }
                /* Modal de asignación individual */
                #assign-market-modal {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    z-index: 9999 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                }
                #assign-market-modal.hidden {
                    display: none !important;
                }
                #assign-market-modal .bg-white {
                    position: relative !important;
                    z-index: 10000 !important;
                    margin: auto !important;
                    max-height: 90vh !important;
                    overflow-y: auto !important;
                }
                /* Estilos específicos para Select2 en modal de asignación */
                #assign-market-modal .select2-container {
                    z-index: 10001 !important;
                }
                #assign-market-modal .select2-dropdown {
                    z-index: 99999 !important;
                    border: 1px solid #d1d5db !important;
                    border-radius: 6px !important;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
                }
                #assign-market-modal .select2-results__option {
                    padding: 8px 12px !important;
                    transition: all 0.2s ease !important;
                }
                #assign-market-modal .select2-results__option--highlighted {
                    background-color: #3b82f6 !important;
                    color: white !important;
                }
                /* Estilos específicos para Select2 en modal de editar mercado */
                #create-market-modal .select2-container {
                    z-index: 10001 !important;
                }
                #create-market-modal .select2-dropdown {
                    z-index: 99999 !important;
                    border: 1px solid #d1d5db !important;
                    border-radius: 6px !important;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
                }
                #create-market-modal .select2-results__option {
                    padding: 8px 12px !important;
                    transition: all 0.2s ease !important;
                }
                #create-market-modal .select2-results__option--highlighted {
                    background-color: #8b5cf6 !important;
                    color: white !important;
                }
                #create-market-modal .select2-selection__rendered {
                    color: #374151 !important;
                    font-weight: 500 !important;
                }
            `)
            .appendTo('head');

        // Initialize Select2 for market selectors
        $('.select2-markets').each(function () {
            $(this).select2({
                placeholder: 'Seleccionar mercado...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function () {
                        return "No se encontraron mercados";
                    },
                    searching: function () {
                        return "Buscando mercados...";
                    }
                },
                escapeMarkup: function (markup) {
                    return markup;
                }
            });
        });

        // Re-initialize Select2 clear functionality after DOM changes
        function reinitializeSelect2Clear() {
            applySelect2ClearStyling();
        }

        // Initialize clear functionality
        reinitializeSelect2Clear();

        // Re-initialize after any Ajax updates (but don't cause dropdown to open)
        $(document).ajaxComplete(function() {
            setTimeout(function() {
                applySelect2ClearStyling();
            }, 150);
        });

        // Auto-submit form when filters change with debouncing
        let searchTimeout;
        $('#search').on('input', function () {
            clearTimeout(searchTimeout);
            const $this = $(this);
            const $form = $('#filters-form');

            // Show loading indicator
            showLoadingState();

            searchTimeout = setTimeout(function () {
                submitFormWithCleanURL($form);
            }, 600); // 600ms delay for search
        });

        // Submit form immediately for select dropdowns
        $('.select2-filter').on('change', function () {
            showLoadingState();
            submitFormWithCleanURL($('#filters-form'));
        });

        // Loading state function
        function showLoadingState() {
            if (!$('.loading-overlay').length) {
                $('.filter-section').addClass('opacity-75');
                $('.filter-section').append(`
                        <div class="loading-overlay">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mr-3"></div>
                                <span class="text-purple-600 font-semibold">Aplicando filtros...</span>
                            </div>
                        </div>
                    `);
            }
        }

        // Handle clear filters button
        $('.clear-filters-btn').on('click', function (e) {
            e.preventDefault();

            // Close any open Select2 dropdowns first
            $('.select2-filter').select2('close');

            // Show loading
            showLoadingState();

            // Clear all form inputs
            $('#filters-form')[0].reset();
            $('.select2-filter').val(null).trigger('change');

            // Submit form to reload with cleared filters
            setTimeout(function () {
                submitFormWithCleanURL($('#filters-form'));
            }, 100);
        });

        // Handle individual Select2 clear events
        $('.select2-filter').on('select2:clear', function (e) {
            const $element = $(this);
            const filterName = $element.attr('name') || 'filtro';
            
            // Close the dropdown immediately if it's open
            $element.select2('close');
            
            showToast(`Filtro de ${filterName} limpiado`, 'info');
            
            // Auto-submit form after clearing with a small delay
            setTimeout(function() {
                showLoadingState();
                submitFormWithCleanURL($('#filters-form'));
            }, 200);
        });

        // Ensure Select2 clear buttons are properly styled
        function applySelect2ClearStyling() {
            setTimeout(function() {
                $('.select2-selection__clear').each(function() {
                    $(this).css({
                        'color': '#dc3545',
                        'font-size': '16px',
                        'font-weight': 'bold',
                        'margin-right': '10px',
                        'padding': '4px 6px',
                        'border-radius': '50%',
                        'background-color': 'rgba(220, 53, 69, 0.1)',
                        'transition': 'all 0.2s ease',
                        'cursor': 'pointer'
                    });
                });
            }, 100);
        }

        // Apply styling initially and after changes
        applySelect2ClearStyling();

        // Remove market button functionality
        $('.remove-market-btn').on('click', function () {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const currentMarket = $(this).data('current-market');
            
            // Usar modal de confirmación personalizada para remover mercado
            showRemoveMarketModal(productId, productName, currentMarket);
        });

        // Assign market button functionality (for products without market)
        $(document).on('click', '.assign-market-btn', function () {
            const productSku = $(this).data('product-id'); // Este es el SKU
            const productName = $(this).data('product-name');
            
            // Mostrar modal para asignar mercado individual
            showAssignMarketModal(productSku, productName);
        });

        // Market search functionality in manage markets modal
        $('#market-search').on('change', function () {
            // Future implementation for market search
        });

        // Handle modal close buttons
        $('#close-assign-modal, #cancel-assign').on('click', function () {
            // Modal close functionality
        });

        // Handle create market functionality
        $('#manage-markets-btn').on('click', function () {
            console.log('Manage markets button clicked');
            loadMarketsForSelection();
            
            // Inicializar Select2 para el select de editar mercado
            initializeEditMarketSelect();
            
            $('#create-market-modal').removeClass('hidden');
        });

        // Handle bulk assign functionality
        $('#bulk-assign-btn').on('click', function () {
            console.log('Bulk assign button clicked');
            
            // Close any other open modals first
            $('.modal-overlay').addClass('hidden');
            
            // Limpiar event listeners previos de ESC
            $(document).off('keydown.bulkassign');
            
            // Show modal with proper styling
            const modal = $('#bulk-assign-modal');
            modal.removeClass('hidden');
            
            // Configurar event listener de ESC para esta modal
            $(document).on('keydown.bulkassign', function(e) {
                if (e.key === 'Escape' && !modal.hasClass('hidden')) {
                    closeBulkAssignModal();
                }
            });
            
            // Ensure modal is properly positioned
            setTimeout(function() {
                modal.css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100vw',
                    'height': '100vh',
                    'z-index': '9999',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'center'
                });
                
                // Center the modal content
                modal.find('.bg-white').css({
                    'position': 'relative',
                    'z-index': '10000',
                    'margin': 'auto'
                });
                
                // Load markets and initialize modal
                loadMarketsForBulkAssign();
                
                // Initialize modal after a short delay to ensure DOM is ready
                setTimeout(function() {
                    initializeBulkAssignModal();
                }, 200);
                
            }, 10);
        });

        // Close create market modal
        $('#close-create-market-modal, #cancel-create-market').on('click', function () {
            $('#create-market-modal').addClass('hidden');
            resetCreateMarketForm();
        });

        // Close bulk assign modal - using event delegation to avoid conflicts
        $(document).on('click', '#close-bulk-assign-modal, #cancel-bulk-assign', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Closing bulk assign modal - button clicked:', $(this).attr('id'));
            closeBulkAssignModal();
        });
        
        // Close bulk assign modal when clicking outside (on the overlay)
        $(document).on('click', '#bulk-assign-modal', function(e) {
            if (e.target === this) {
                console.log('Closing modal - clicked outside');
                closeBulkAssignModal();
            }
        });
        
        // Global event listener for Select2 dropdowns in bulk assign modal
        $(document).on('select2:open', '.bulk-assign-select', function () {
            // Force high z-index when any Select2 in modal opens
            setTimeout(function() {
                $('.select2-dropdown').css({
                    'z-index': '99999',
                    'position': 'absolute'
                });
                $('.select2-container--open').css('z-index', '99999');
            }, 1);
        });
        
        // Handle Select2 clear events specifically for bulk assign modal
        $(document).on('select2:clear', '.bulk-assign-select', function (e) {
            const $element = $(this);
            const filterName = $element.attr('name') || 'filtro';
            
            // Close the dropdown immediately if it's open
            $element.select2('close');
            
            console.log(`Filtro ${filterName} limpiado en modal bulk assign`);
            
            // Auto-search after clearing filter with a small delay
            setTimeout(function() {
                searchProductsForAssignment();
            }, 200);
        });

        // Handle search products button
        $('#search-products-btn').on('click', function () {
            searchProductsForAssignment();
        });

        // Handle product search input
        $('#product-search').on('keyup', function(e) {
            if (e.key === 'Enter') {
                searchProductsForAssignment();
            }
        });

        // Handle current market filter change
        $('#current-market-filter').on('change', function() {
            searchProductsForAssignment();
        });

        // Handle select all products
        $('#select-all-products').on('click', function() {
            $('#products-container input[type="checkbox"]').prop('checked', true);
            updateSelectedCount();
        });

        // Handle deselect all products
        $('#deselect-all-products').on('click', function() {
            $('#products-container input[type="checkbox"]').prop('checked', false);
            updateSelectedCount();
        });

        // Handle individual product selection
        $(document).on('change', '#products-container input[type="checkbox"]', function() {
            updateSelectedCount();
        });

        // Handle bulk assign form submission
        $('#confirm-bulk-assign').on('click', function() {
            processBulkAssign();
        });

        // Handle target market change
        $(document).on('change', '#target-market', function() {
            updateSelectedCount();
        });

        // Handle edit market form submission
        $('#edit-market-form').on('submit', function (e) {
            e.preventDefault();
            
            const marketId = $('#select-market').val();
            const newMarketName = $('#edit-market-name').val().trim();
            
            if (!marketId) {
                showToast('Por favor selecciona un mercado para editar', 'error');
                return;
            }
            
            if (!newMarketName) {
                showToast('Por favor ingresa un nuevo nombre para el mercado', 'error');
                return;
            }
            
            const submitBtn = $('#confirm-edit-market');
            const originalText = submitBtn.find('.btn-text').html();
            
            submitBtn.prop('disabled', true);
            submitBtn.find('.btn-text').hide();
            submitBtn.find('.btn-loading').show();
            
            $.ajax({
                url: window.MarketConfigRoutes.updateMarket.replace(':id', marketId),
                method: 'PUT',
                data: {
                    market_name: newMarketName,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    showToast(response.message || 'Mercado actualizado exitosamente', 'success');
                    $('#create-market-modal').addClass('hidden');
                    resetCreateMarketForm();
                    
                    // Reload page to show updated market
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(response.message || 'Error al actualizar mercado', 'error');
                }
            })
            .fail(function(xhr) {
                let errorMessage = '❌ Error al actualizar mercado';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = '❌ ' + xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = '❌ Por favor verifica los datos ingresados';
                } else if (xhr.status === 500) {
                    errorMessage = '❌ Error interno del servidor';
                }
                showToast(errorMessage, 'error');
            })
            .always(function() {
                submitBtn.prop('disabled', false);
                submitBtn.find('.btn-text').show().html(originalText);
                submitBtn.find('.btn-loading').hide();
            });
        });

        // Handle create market form submission
        $('#create-market-form').on('submit', function (e) {
            e.preventDefault();
            
            const marketName = $('#market-name').val().trim();
            
            if (!marketName) {
                showToast('Por favor ingresa un nombre para el mercado', 'error');
                return;
            }
            
            const submitBtn = $('#confirm-create-market');
            const originalText = submitBtn.find('.btn-text').html();
            
            submitBtn.prop('disabled', true);
            submitBtn.find('.btn-text').hide();
            submitBtn.find('.btn-loading').show();
            
            $.post(window.MarketConfigRoutes.createMarket, {
                market_name: marketName,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(function(response) {
                if (response.success) {
                    showToast((response.message || 'Mercado creado exitosamente'), 'success');
                    $('#create-market-modal').addClass('hidden');
                    resetCreateMarketForm();
                    
                    // Reload page to show new market after a short delay to show the toast
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showToast((response.message || 'Error al crear mercado'), 'error');
                }
            })
            .fail(function(xhr) {
                let errorMessage = '❌ Error al crear mercado';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = '❌ ' + xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    errorMessage = '❌ Por favor verifica los datos ingresados';
                } else if (xhr.status === 500) {
                    errorMessage = '❌ Error interno del servidor';
                }
                showToast(errorMessage, 'error');
            })
            .always(function() {
                submitBtn.prop('disabled', false);
                submitBtn.find('.btn-text').show().html(originalText);
                submitBtn.find('.btn-loading').hide();
            });
        });

        // Close modal when clicking outside
        $('.close-modal, .modal-overlay').on('click', function (e) {
            if (e.target === this) {
                $(this).closest('.modal-overlay').addClass('hidden');
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function (e) {
            // ESC key to close modals and Select2 dropdowns
            if (e.key === 'Escape') {
                $('.modal-overlay:not(.hidden)').addClass('hidden');
                $('.select2-filter').select2('close');
                e.preventDefault();
            }
        });

        // Prevent Select2 from staying open after clear
        $(document).on('click', '.select2-selection__clear', function(e) {
            e.stopPropagation();
            const $select = $(this).closest('.select2-container').prev('select');
            setTimeout(function() {
                $select.select2('close');
            }, 50);
        });

        // Update filter results count
        function updateResultsInfo() {
            const totalResults = parseInt($('#total-products').text()) || 0;
            const resultsText = totalResults === 1 ? 'resultado' : 'resultados';
            
            if ($('#results-info').length) {
                $('#results-info').text(`${totalResults} ${resultsText} encontrados`);
            }
        }

        // Real-time filter update
        $('.select2-filter, #search').on('change input', function () {
            clearTimeout(window.filterTimeout);
            window.filterTimeout = setTimeout(function() {
                showLoadingState();
                submitFormWithCleanURL($('#filters-form'));
            }, 500);
        });

        // Initialize results info
        updateResultsInfo();
        
        
    });

    // Helper Functions
    function submitFormWithCleanURL($form) {
        // Collect form data and filter out empty values
        const formInputs = $form.serializeArray();
        const cleanParams = [];
        
        formInputs.forEach(function(input) {
            if (input.value && input.value.trim() !== '') {
                cleanParams.push(`${encodeURIComponent(input.name)}=${encodeURIComponent(input.value.trim())}`);
            }
        });
        
        // Create clean URL
        const baseUrl = window.location.pathname;
        const queryString = cleanParams.join('&');
        const cleanUrl = queryString ? `${baseUrl}?${queryString}` : baseUrl;
        
        // Update browser URL
        if (window.history && window.history.pushState) {
            window.history.pushState(null, '', cleanUrl);
        }
        
        // Redirect to clean URL
        window.location.href = cleanUrl;
    }

    function resetCreateMarketForm() {
        $('#market-name').val('');
        
        // Reset Select2 del mercado para editar
        const selectMarket = $('#select-market');
        if (selectMarket.hasClass('select2-hidden-accessible')) {
            selectMarket.val('').trigger('change');
        } else {
            selectMarket.val('');
        }
        
        $('#edit-market-name').val('');
        $('#edit-market-fields, #edit-market-actions').hide();
        
        const createBtn = $('#confirm-create-market');
        createBtn.prop('disabled', false);
        createBtn.find('.btn-text').show().html('<i class="fas fa-plus mr-2"></i>Crear Mercado');
        createBtn.find('.btn-loading').hide();
        
        const editBtn = $('#confirm-edit-market');
        editBtn.prop('disabled', false);
        editBtn.find('.btn-text').show().html('<i class="fas fa-save mr-2"></i>Guardar Cambios');
        editBtn.find('.btn-loading').hide();
    }

    function loadMarketsForSelection() {
        $.get(window.MarketConfigRoutes.getAllMarkets)
        .done(function(response) {
            if (response.success && response.markets) {
                const selectElement = $('#select-market');
                selectElement.empty();
                selectElement.append('<option value="">Seleccionar mercado para editar...</option>');
                
                response.markets.forEach(function(market) {
                    selectElement.append(`<option value="${market.id_mercado}">${market.mercado}</option>`);
                });
                
                // Trigger change para Select2
                selectElement.trigger('change');
                
                // Reinicializar Select2 si ya está configurado
                if (selectElement.hasClass('select2-hidden-accessible')) {
                    selectElement.select2('destroy');
                    initializeEditMarketSelect();
                }
            }
        })
        .fail(function(xhr) {
            console.error('Error loading markets:', xhr);
            showToast('Error al cargar mercados disponibles', 'error');
        });
    }

    function loadMarketsForBulkAssign() {
        $.get(window.MarketConfigRoutes.getAllMarkets)
        .done(function(response) {
            if (response.success && response.markets) {
                // Load target markets
                const targetSelect = $('#target-market');
                targetSelect.empty();
                targetSelect.append('<option value="">Seleccionar mercado de destino...</option>');
                
                // Load current market filters (including existing markets from materials)
                const filterSelect = $('#current-market-filter');
                const currentValue = filterSelect.val();
                filterSelect.empty();
                filterSelect.append('<option value="">Todos los mercados</option>');
                filterSelect.append('<option value="RESTO">RESTO (sin mercado asignado)</option>');
                
                response.markets.forEach(function(market) {
                    targetSelect.append(`<option value="${market.id_mercado}">${market.mercado}</option>`);
                    filterSelect.append(`<option value="${market.mercado}">${market.mercado}</option>`);
                });
                
                // Initialize Select2 for these selects first
                initializeBulkAssignSelects();
                
                // Then set the default value to RESTO
                filterSelect.val('RESTO').trigger('change');
                
                // Apply clear button styling
                setTimeout(function() {
                    applyBulkAssignClearStyling();
                }, 200);
                
                console.log('Markets loaded successfully for bulk assign');
            }
        })
        .fail(function(xhr) {
            console.error('Error loading markets for bulk assign:', xhr);
            showToast('Error al cargar mercados disponibles', 'error');
        });
    }

    function initializeBulkAssignModal() {
        console.log('Initializing bulk assign modal');
        resetBulkAssignForm();
        
        // Set default filter to RESTO after a short delay to ensure Select2 is initialized
        setTimeout(function() {
            $('#current-market-filter').val('RESTO').trigger('change');
            console.log('Set filter to RESTO');
            
            // Automatically search for products without market (RESTO) when modal opens
            setTimeout(function() {
                console.log('Searching for RESTO products automatically');
                searchProductsForAssignment();
            }, 300);
        }, 100);
    }

    function initializeBulkAssignSelects() {
        // Add CSS for Select2 dropdowns in modal
        if (!$('#bulk-modal-select2-styles').length) {
            const modalCSS = `
                <style id="bulk-modal-select2-styles">
                    /* Select2 dropdown fixes for modal */
                    .select2-container {
                        z-index: 99999 !important;
                    }
                    
                    .select2-dropdown {
                        z-index: 99999 !important;
                        position: absolute !important;
                    }
                    
                    .select2-container--open .select2-dropdown {
                        z-index: 99999 !important;
                    }
                    
                    /* Ensure Select2 selections are visible */
                    .select2-container .select2-selection {
                        z-index: 10001 !important;
                        position: relative !important;
                    }
                    
                    /* Modal positioning fixes */
                    #bulk-assign-modal.modal-overlay {
                        position: fixed !important;
                        top: 0 !important;
                        left: 0 !important;
                        width: 100vw !important;
                        height: 100vh !important;
                        z-index: 9999 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                    }
                    
                    /* Estilos para modal de confirmación */
                    #confirmation-modal {
                        animation: fadeIn 0.3s ease-out;
                    }
                    
                    #confirmation-modal .relative {
                        animation: slideInUp 0.3s ease-out;
                        transform: translateY(0);
                    }
                    
                    /* Estilos para modal principal de bulk assign */
                    #bulk-assign-modal {
                        transition: opacity 0.3s ease-out;
                    }
                    
                    #bulk-assign-modal.hidden {
                        opacity: 0;
                        pointer-events: none;
                    }
                    
                    #bulk-assign-modal:not(.hidden) {
                        opacity: 1;
                        pointer-events: auto;
                    }
                    
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    
                    @keyframes slideInUp {
                        from { 
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to { 
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    /* Botones con efectos hover mejorados */
                    #confirmation-accept:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                    }
                    
                    #confirmation-cancel:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    }
                    
                    /* Estilos para el botón X de cerrar */
                    #close-confirmation-modal {
                        transition: all 0.2s ease;
                        border-radius: 50%;
                        padding: 4px;
                    }
                    
                    #close-confirmation-modal:hover {
                        background-color: rgba(239, 68, 68, 0.1);
                        color: #dc2626;
                        transform: scale(1.1);
                    }
                    
                    /* Estilos para el botón X de la modal principal */
                    #close-bulk-assign-modal {
                        transition: all 0.2s ease;
                        border-radius: 50%;
                        padding: 4px;
                    }
                    
                    #close-bulk-assign-modal:hover {
                        background-color: rgba(239, 68, 68, 0.1);
                        color: #dc2626;
                        transform: scale(1.1);
                    }
                    
                    /* Estilos específicos para Select2 clear en modal bulk assign */
                    .bulk-assign-select + .select2-container .select2-selection__clear {
                        color: #dc3545 !important;
                        font-size: 16px !important;
                        font-weight: bold !important;
                        margin-right: 10px !important;
                        padding: 4px 6px !important;
                        border-radius: 50% !important;
                        background-color: rgba(220, 53, 69, 0.1) !important;
                        transition: all 0.2s ease !important;
                        cursor: pointer !important;
                        z-index: 10002 !important;
                        position: relative !important;
                    }
                    
                    .bulk-assign-select + .select2-container .select2-selection__clear:hover {
                        background-color: rgba(220, 53, 69, 0.2) !important;
                        transform: scale(1.1) !important;
                    }
                    
                    /* Estilos para modal de remover mercado */
                    #remove-market-modal {
                        animation: fadeIn 0.3s ease-out;
                    }
                    
                    #remove-market-modal .relative {
                        animation: slideInUp 0.3s ease-out;
                        transform: translateY(0);
                    }
                    
                    /* Estilos para el botón X de la modal de remover mercado */
                    #close-remove-market-modal {
                        transition: all 0.2s ease;
                        border-radius: 50%;
                        padding: 4px;
                    }
                    
                    #close-remove-market-modal:hover {
                        background-color: rgba(239, 68, 68, 0.1);
                        color: #dc2626;
                        transform: scale(1.1);
                    }
                    
                    /* Efectos hover para botones de la modal de remover mercado */
                    #remove-market-confirm:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
                    }
                    
                    #remove-market-cancel:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    }
                    
                    /* Estilos para modal de asignar mercado */
                    #assign-market-modal {
                        animation: fadeIn 0.3s ease-out;
                    }
                    
                    #assign-market-modal .relative {
                        animation: slideInUp 0.3s ease-out;
                        transform: translateY(0);
                    }
                    
                    /* Estilos para el botón X de la modal de asignar mercado */
                    #close-assign-market-modal {
                        transition: all 0.2s ease;
                        border-radius: 50%;
                        padding: 4px;
                    }
                    
                    #close-assign-market-modal:hover {
                        background-color: rgba(239, 68, 68, 0.1);
                        color: #dc2626;
                        transform: scale(1.1);
                    }
                    
                    /* Efectos hover para botones de la modal de asignar mercado */
                    #assign-market-confirm:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                    }
                    
                    #assign-market-cancel:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    }
                </style>
            `;
            $('head').append(modalCSS);
        }
        
        // Destroy existing Select2 instances first
        $('.bulk-assign-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        
        // Initialize Select2 for bulk assign selects
        $('.bulk-assign-select').each(function() {
            $(this).select2({
                placeholder: $(this).find('option:first').text(),
                allowClear: true,
                width: '100%',
                dropdownParent: $('body'), // Changed from modal to body to avoid z-index issues
                language: {
                    noResults: function () {
                        return "No se encontraron resultados";
                    },
                    searching: function () {
                        return "Buscando...";
                    }
                }
            }).on('select2:open', function () {
                // Force high z-index when dropdown opens
                $('.select2-dropdown').css('z-index', '99999');
                $('.select2-container--open').css('z-index', '99999');
            }).on('select2:clear', function () {
                // Close dropdown immediately after clear
                $(this).select2('close');
            });
        });
        
        // Apply custom styling to ensure dropdowns appear above modal
        setTimeout(function() {
            $('.select2-dropdown').css('z-index', '99999');
            applyBulkAssignClearStyling();
        }, 100);
    }
    
    // Apply Select2 clear button styling specifically for bulk assign modal
    function applyBulkAssignClearStyling() {
        setTimeout(function() {
            $('.bulk-assign-select').each(function() {
                const $container = $(this).next('.select2-container');
                $container.find('.select2-selection__clear').each(function() {
                    $(this).css({
                        'color': '#dc3545',
                        'font-size': '16px',
                        'font-weight': 'bold',
                        'margin-right': '10px',
                        'padding': '4px 6px',
                        'border-radius': '50%',
                        'background-color': 'rgba(220, 53, 69, 0.1)',
                        'transition': 'all 0.2s ease',
                        'cursor': 'pointer',
                        'z-index': '10002',
                        'position': 'relative'
                    });
                });
            });
        }, 100);
    }

    function searchProductsForAssignment() {
        const search = $('#product-search').val().trim();
        const currentMarket = $('#current-market-filter').val();
        
        // Show loading
        $('#products-container').html(`
            <div class="p-4 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-2"></div>
                <p class="text-gray-600">Buscando productos...</p>
            </div>
        `);
        
        $.get(window.MarketConfigRoutes.searchProductsForAssignment, {
            search: search,
            current_market: currentMarket
        })
        .done(function(response) {
            if (response.success) {
                displayProductsForAssignment(response.products);
                $('#total-found').text(response.total);
            } else {
                showToast('Error al buscar productos', 'error');
            }
        })
        .fail(function(xhr) {
            console.error('Error searching products:', xhr);
            showToast('Error al conectar con el servidor', 'error');
            $('#products-container').html(`
                <div class="p-4 text-center text-red-600">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>Error al cargar productos</p>
                </div>
            `);
        });
    }

    function displayProductsForAssignment(products) {
        if (products.length === 0) {
            $('#products-container').html(`
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-search text-3xl mb-2 text-gray-300"></i>
                    <p>No se encontraron productos con los criterios seleccionados</p>
                </div>
            `);
            return;
        }
        
        let html = '<div class="space-y-2 p-2">';
        
        products.forEach(function(product) {
            const marketBadge = product.Mercado === 'RESTO' || !product.Mercado ? 
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Sin mercado</span>' :
                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${product.Mercado}</span>`;
                
            html += `
                <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <input type="checkbox" value="${product.SKU}" class="mr-3 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">${product.SKU}</p>
                                <p class="text-xs text-gray-600 truncate" title="${product['Descripción_Presentación']}">${product['Descripción_Presentación']}</p>
                                <div class="flex items-center mt-1 space-x-2">
                                    <span class="text-xs text-gray-500">${product.LABORATORIO_C}</span>
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500">${product['Marca_Genérico']}</span>
                                </div>
                            </div>
                            <div class="ml-2 flex-shrink-0">
                                ${marketBadge}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        $('#products-container').html(html);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selectedCount = $('#products-container input[type="checkbox"]:checked').length;
        $('#selected-count').text(selectedCount);
        
        // Enable/disable assign button
        const assignBtn = $('#confirm-bulk-assign');
        const targetMarket = $('#target-market').val();
        
        if (selectedCount > 0 && targetMarket) {
            assignBtn.prop('disabled', false);
        } else {
            assignBtn.prop('disabled', true);
        }
    }

    function processBulkAssign() {
        const targetMarketId = $('#target-market').val();
        const selectedSkus = [];
        
        $('#products-container input[type="checkbox"]:checked').each(function() {
            selectedSkus.push($(this).val());
        });
        
        if (!targetMarketId) {
            showToast('Por favor selecciona un mercado de destino', 'error');
            return;
        }
        
        if (selectedSkus.length === 0) {
            showToast('Por favor selecciona al menos un producto', 'error');
            return;
        }
        
        const confirmMsg = `¿Estás seguro de asignar ${selectedSkus.length} productos al mercado seleccionado?`;
        
        // Usar modal de confirmación personalizada en lugar de confirm nativo
        showConfirmationModal(confirmMsg, function() {
            // Callback cuando el usuario confirma
            performBulkAssignment(targetMarketId, selectedSkus);
        });
    }

    // Función para realizar la asignación masiva
    function performBulkAssignment(targetMarketId, selectedSkus) {
        const assignBtn = $('#confirm-bulk-assign');
        assignBtn.prop('disabled', true);
        assignBtn.find('.btn-text').hide();
        assignBtn.find('.btn-loading').show();
        
        $.post(window.MarketConfigRoutes.bulkAssignMarkets, {
            target_market_id: targetMarketId,
            product_skus: selectedSkus,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                showToast(response.message || 'Asignación completada exitosamente', 'success');
                $('#bulk-assign-modal').addClass('hidden');
                resetBulkAssignForm();
                
                // Reload page after a short delay
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Error en la asignación', 'error');
            }
        })
        .fail(function(xhr) {
            let errorMessage = 'Error al procesar la asignación';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showToast(errorMessage, 'error');
        })
        .always(function() {
            assignBtn.prop('disabled', false);
            assignBtn.find('.btn-text').show();
            assignBtn.find('.btn-loading').hide();
        });
    }

    // Función para mostrar modal de confirmación personalizada
    function showConfirmationModal(message, onConfirm) {
        $('#confirmation-message').text(message);
        $('#confirmation-modal').removeClass('hidden');
        
        // Limpiar event listeners previos para evitar duplicados
        $('#confirmation-accept').off('click');
        $('#confirmation-cancel').off('click');
        $('#close-confirmation-modal').off('click');
        
        // Event listener para confirmar
        $('#confirmation-accept').on('click', function() {
            closeConfirmationModal();
            if (onConfirm && typeof onConfirm === 'function') {
                onConfirm();
            }
        });
        
        // Event listeners para cancelar/cerrar
        $('#confirmation-cancel, #close-confirmation-modal').on('click', function() {
            closeConfirmationModal();
        });
        
        // Cerrar modal al hacer clic fuera de ella
        $('#confirmation-modal').on('click', function(e) {
            if (e.target === this) {
                closeConfirmationModal();
            }
        });
        
        // Cerrar modal con tecla ESC
        $(document).on('keydown.confirmation', function(e) {
            if (e.key === 'Escape') {
                closeConfirmationModal();
            }
        });
    }
    
    // Función para cerrar la modal de confirmación
    function closeConfirmationModal() {
        $('#confirmation-modal').addClass('hidden');
        // Limpiar el event listener de ESC para evitar conflictos
        $(document).off('keydown.confirmation');
    }

    // Función para mostrar modal de confirmación para remover mercado
    function showRemoveMarketModal(productId, productName, currentMarket) {
        // Establecer la información del producto en la modal
        $('#remove-product-name').text(productName);
        $('#remove-current-market').text(currentMarket);
        
        // Mostrar la modal
        $('#remove-market-modal').removeClass('hidden');
        
        // Limpiar event listeners previos para evitar duplicados
        $('#remove-market-confirm').off('click');
        $('#remove-market-cancel, #close-remove-market-modal').off('click');
        
        // Event listener para confirmar eliminación
        $('#remove-market-confirm').on('click', function() {
            closeRemoveMarketModal();
            removeMarketFromProduct(productId);
        });
        
        // Event listeners para cancelar/cerrar
        $('#remove-market-cancel, #close-remove-market-modal').on('click', function() {
            closeRemoveMarketModal();
        });
        
        // Cerrar modal al hacer clic fuera de ella
        $('#remove-market-modal').on('click', function(e) {
            if (e.target === this) {
                closeRemoveMarketModal();
            }
        });
        
        // Cerrar modal con tecla ESC
        $(document).on('keydown.removemarket', function(e) {
            if (e.key === 'Escape') {
                closeRemoveMarketModal();
            }
        });
    }
    
    // Función para cerrar la modal de remover mercado
    function closeRemoveMarketModal() {
        $('#remove-market-modal').addClass('hidden');
        // Limpiar el event listener de ESC para evitar conflictos
        $(document).off('keydown.removemarket');
    }

    // Función para mostrar modal de asignación individual de mercado
    function showAssignMarketModal(productSku, productName) {
        // Establecer la información del producto en la modal
        $('#assign-product-name').text(productName);
        
        // Cargar mercados disponibles
        loadMarketsForAssignment();
        
        // Mostrar la modal
        $('#assign-market-modal').removeClass('hidden');
        
        // Limpiar event listeners previos para evitar duplicados
        $('#assign-market-confirm').off('click');
        $('#assign-market-cancel, #close-assign-market-modal').off('click');
        
        // Event listener para confirmar asignación
        $('#assign-market-confirm').on('click', function() {
            const selectedMarketId = $('#assign-market-select').val();
            if (!selectedMarketId) {
                $('#assign-market-error').text('Por favor selecciona un mercado').removeClass('hidden');
                return;
            }
            
            $('#assign-market-error').addClass('hidden');
            assignMarketToProduct(productSku, selectedMarketId); // Pasar SKU del producto
        });
        
        // Event listeners para cancelar/cerrar
        $('#assign-market-cancel, #close-assign-market-modal').on('click', function() {
            closeAssignMarketModal();
        });
        
        // Cerrar modal al hacer clic fuera de ella
        $('#assign-market-modal').on('click', function(e) {
            if (e.target === this) {
                closeAssignMarketModal();
            }
        });
        
        // Cerrar modal con tecla ESC
        $(document).on('keydown.assignmarket', function(e) {
            if (e.key === 'Escape') {
                closeAssignMarketModal();
            }
        });
    }
    
    // Función para cerrar la modal de asignar mercado
    function closeAssignMarketModal() {
        $('#assign-market-modal').addClass('hidden');
        $('#assign-market-select').val('').trigger('change');
        $('#assign-market-error').addClass('hidden');
        $('#assign-market-confirm').prop('disabled', true);
        // Limpiar el event listener de ESC para evitar conflictos
        $(document).off('keydown.assignmarket');
    }
    
    // Función para cargar mercados para asignación individual
    function loadMarketsForAssignment() {
        $.get(window.MarketConfigRoutes.getAllMarkets)
        .done(function(response) {
            if (response.success && response.markets) {
                const select = $('#assign-market-select');
                select.empty();
                select.append('<option value="">Seleccionar mercado...</option>');
                
                response.markets.forEach(function(market) {
                    select.append(`<option value="${market.id_mercado}">${market.mercado}</option>`);
                });
                
                // Initialize Select2
                initializeAssignMarketSelect();
            }
        })
        .fail(function(xhr) {
            console.error('Error loading markets for assignment:', xhr);
            showToast('Error al cargar mercados disponibles', 'error');
        });
    }
    
    // Función para inicializar Select2 en modal de asignación individual
    function initializeAssignMarketSelect() {
        if ($('#assign-market-select').hasClass('select2-hidden-accessible')) {
            $('#assign-market-select').select2('destroy');
        }
        
        $('#assign-market-select').select2({
            placeholder: 'Seleccionar mercado...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('body'), // Importante: usar body para evitar z-index issues
            language: {
                noResults: function () {
                    return "No se encontraron resultados";
                },
                searching: function () {
                    return "Buscando...";
                }
            }
        }).on('change', function() {
            const hasValue = $(this).val();
            $('#assign-market-confirm').prop('disabled', !hasValue);
            if (hasValue) {
                $('#assign-market-error').addClass('hidden');
            }
        }).on('select2:open', function () {
            // Forzar z-index alto cuando se abre el dropdown
            setTimeout(function() {
                $('.select2-dropdown').css({
                    'z-index': '99999',
                    'position': 'absolute'
                });
                $('.select2-container--open').css('z-index', '99999');
            }, 1);
        });
    }
    
    // Función para inicializar Select2 en el select de editar mercado
    function initializeEditMarketSelect() {
        // Destruir Select2 existente si está presente
        if ($('#select-market').hasClass('select2-hidden-accessible')) {
            $('#select-market').select2('destroy');
        }
        
        $('#select-market').select2({
            placeholder: 'Seleccionar mercado para editar...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#create-market-modal'), // Usar la modal como parent
            language: {
                noResults: function () {
                    return "No se encontraron mercados";
                },
                searching: function () {
                    return "Buscando mercados...";
                },
                loadingMore: function () {
                    return "Cargando más resultados...";
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        }).on('change', function() {
            const marketId = $(this).val();
            const marketText = $(this).find('option:selected').text();
            
            if (marketId) {
                $('#edit-market-name').val(marketText);
                $('#edit-market-fields, #edit-market-actions').show();
            } else {
                $('#edit-market-fields, #edit-market-actions').hide();
                $('#edit-market-name').val('');
            }
        }).on('select2:open', function () {
            // Forzar z-index alto cuando se abre el dropdown
            setTimeout(function() {
                $('.select2-dropdown').css({
                    'z-index': '99999',
                    'position': 'absolute'
                });
                $('.select2-container--open').css('z-index', '99999');
            }, 1);
        });
    }
    
    // Función para asignar mercado a un producto
    function assignMarketToProduct(productSku, marketId) {
        const $confirmBtn = $('#assign-market-confirm');
        
        // Mostrar loading
        $confirmBtn.prop('disabled', true);
        $confirmBtn.find('.btn-text').hide();
        $confirmBtn.find('.btn-loading').show();
        
        $.post(window.MarketConfigRoutes.assignMaterialToMarket, {
            material_sku: productSku, // Cambiar de product_id a material_sku
            market_id: marketId,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                showToast((response.message || 'Mercado asignado exitosamente'), 'success');
                
                // Cerrar modal
                closeAssignMarketModal();
                
                // Recargar página después de un pequeño delay
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast((response.message || 'Error al asignar mercado'), 'error');
                
                // Restaurar botón
                $confirmBtn.prop('disabled', false);
                $confirmBtn.find('.btn-text').show();
                $confirmBtn.find('.btn-loading').hide();
            }
        })
        .fail(function(xhr) {
            let errorMessage = '❌ Error al asignar mercado';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = '❌ ' + xhr.responseJSON.message;
            }
            showToast(errorMessage, 'error');
            
            // Restaurar botón
            $confirmBtn.prop('disabled', false);
            $confirmBtn.find('.btn-text').show();
            $confirmBtn.find('.btn-loading').hide();
        });
    }

    // Función para cerrar la modal de asignación masiva
    function closeBulkAssignModal() {
        console.log('closeBulkAssignModal called');
        const modal = $('#bulk-assign-modal');
        console.log('Modal visibility before close:', !modal.hasClass('hidden'));
        
        // Cerrar la modal PRIMERO
        modal.addClass('hidden');
        
        // Limpiar el event listener de ESC para la modal de bulk assign
        $(document).off('keydown.bulkassign');
        
        // Reset form DESPUÉS de un pequeño delay para que el usuario vea que la modal se cierra
        setTimeout(function() {
            resetBulkAssignForm();
        }, 100);
        
        console.log('Modal visibility after close:', !modal.hasClass('hidden'));
        console.log('Bulk assign modal closed successfully');
    }

    function resetBulkAssignForm() {
        console.log('Resetting bulk assign form');
        
        // Resetear selects
        $('#target-market').val('').trigger('change');
        $('#product-search').val('');
        $('#current-market-filter').val('RESTO').trigger('change');
        
        // Resetear contenedor de productos a estado inicial
        $('#products-container').html(`
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-search text-3xl mb-2 text-gray-300"></i>
                <p>Utiliza los filtros para buscar productos</p>
            </div>
        `);
        
        // Resetear contadores
        $('#total-found').text('0');
        $('#selected-count').text('0');
        
        // Deshabilitar botón de asignar
        $('#confirm-bulk-assign').prop('disabled', true);
        
        console.log('Form reset completed');
    }

    function loadMarketsForSelection() {
        $.get(window.MarketConfigRoutes.getAllMarkets)
        .done(function(response) {
            if (response.success && response.markets) {
                const select = $('#select-market');
                select.empty().append('<option value="">Seleccione un mercado...</option>');
                
                response.markets.forEach(function(market) {
                    select.append(`<option value="${market.id_mercado}">${market.mercado}</option>`);
                });
            } else {
                showToast('Error al cargar la lista de mercados', 'error');
            }
        })
        .fail(function() {
            showToast('Error al conectar con el servidor para cargar mercados', 'error');
        });
    }

    function removeMarketFromProduct(productId) {
        // Mostrar loading en el botón de confirmación
        const $confirmBtn = $('#remove-market-confirm');
        const originalText = $confirmBtn.html();
        
        $confirmBtn.prop('disabled', true).html(`
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Removiendo...
        `);
        
        $.post(window.MarketConfigRoutes.removeMarketFromMaterial, {
            product_id: productId,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                showToast((response.message || 'Mercado removido exitosamente'), 'success');
                
                // Cerrar modal inmediatamente
                closeRemoveMarketModal();
                
                // Recargar página después de un pequeño delay
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showToast('❌ ' + (response.message || 'Error al remover mercado'), 'error');
                
                // Restaurar botón
                $confirmBtn.prop('disabled', false).html(originalText);
            }
        })
        .fail(function(xhr) {
            let errorMessage = '❌ Error al remover mercado';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = '❌ ' + xhr.responseJSON.message;
            }
            showToast(errorMessage, 'error');
            
            // Restaurar botón
            $confirmBtn.prop('disabled', false).html(originalText);
        });
    }

    // Sidebar functionality
    function initializeSidebar() {
        const toggleButton = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');

        if (toggleButton && sidebar && mainContent) {
            // Toggle sidebar on button click
            toggleButton.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
                mainContent.classList.toggle('full-width');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                const isClickInsideSidebar = sidebar.contains(e.target);
                const isClickOnToggle = toggleButton.contains(e.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth < 1024) {
                    sidebar.classList.add('hidden');
                    mainContent.classList.add('full-width');
                }
            });
        }
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        // Remove existing toasts
        $('.toast-notification').remove();
        
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        }[type] || 'bg-blue-500';
        
        const icon = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';
        
        const toast = $(`
            <div class="toast-notification fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl z-50 flex items-center transform transition-all duration-300 ease-in-out translate-x-full">
                <i class="${icon} mr-3 text-lg"></i>
                <span class="flex-1 font-medium">${message}</span>
                <button class="ml-4 text-white hover:text-gray-200 transition-colors duration-200 p-1" onclick="$(this).closest('.toast-notification').removeClass('translate-x-0').addClass('translate-x-full').fadeOut(300, function(){ $(this).remove(); })">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        `);
        
        $('body').append(toast);
        
        // Animate in
        setTimeout(() => {
            toast.removeClass('translate-x-full').addClass('translate-x-0');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.removeClass('translate-x-0').addClass('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }

    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
        showToast('Ha ocurrido un error. Por favor recarga la página.', 'error');
    });
});
