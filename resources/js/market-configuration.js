

// Market Configuration JavaScript - Additional functionality
document.addEventListener('DOMContentLoaded', function () {
    console.log('Market Configuration additional JavaScript loaded');

    // Additional functionality can be added here if needed
    // The main functionality is in the inline script in the blade template
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

        // Auto-submit form when filters change with debouncing
        let searchTimeout;
        $('#search').on('input', function () {
            clearTimeout(searchTimeout);
            const $this = $(this);
            const $form = $('#filters-form');

            // Show loading indicator
            showLoadingState();

            searchTimeout = setTimeout(function () {
                $form.submit();
            }, 600); // 600ms delay for search
        });

        // Submit form immediately for select dropdowns
        $('.select2-filter').on('change', function () {
            showLoadingState();
            $('#filters-form').submit();
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

            // Show loading
            showLoadingState();

            // Clear all form inputs
            $('#search').val('');

            // Clear Select2 selections
            $('.select2-filter').val(null).trigger('change.select2');

            // Navigate to clean URL
            setTimeout(function () {
                window.location.href = $(e.target).closest('a').attr('href');
            }, 200);
        });

        // Bulk selection functionality
        $('#select-all').on('change', function () {
            const isChecked = this.checked;
            $('.product-checkbox').prop('checked', isChecked);
            updateBulkActions();

            // Visual feedback
            if (isChecked) {
                $('.product-row').addClass('bg-purple-50');
            } else {
                $('.product-row').removeClass('bg-purple-50');
            }
        });

        $('.product-checkbox').on('change', function () {
            updateBulkActions();

            // Update individual row styling
            const $row = $(this).closest('.product-row');
            if (this.checked) {
                $row.addClass('bg-purple-50');
            } else {
                $row.removeClass('bg-purple-50');
            }

            // Update select-all checkbox
            const totalCheckboxes = $('.product-checkbox').length;
            const checkedCheckboxes = $('.product-checkbox:checked').length;

            if (checkedCheckboxes === 0) {
                $('#select-all').prop('indeterminate', false).prop('checked', false);
            } else if (checkedCheckboxes === totalCheckboxes) {
                $('#select-all').prop('indeterminate', false).prop('checked', true);
            } else {
                $('#select-all').prop('indeterminate', true);
            }
        });

        function updateBulkActions() {
            const checkedCount = $('.product-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#bulk-actions').removeClass('hidden').addClass('animate-fadeIn');
                $('#selected-count').text(checkedCount);
            } else {
                $('#bulk-actions').addClass('hidden').removeClass('animate-fadeIn');
            }
        }

        // Market assignment modal
        $('.assign-market-btn').on('click', function () {
            const productId = $(this).data('product-id');
            const $row = $(this).closest('tr');

            // Extraer todos los datos del producto de la fila de la tabla
            const productData = {
                sku: productId,
                description: $row.find('td:eq(2) p').text().trim(),
                atc: $row.find('td:eq(3) span:first').text().trim(),
                atc_desc: $row.find('td:eq(3) p').text().trim(),
                ff: $row.find('td:eq(4) span:first').text().trim(),
                ff_desc: $row.find('td:eq(4) p').text().trim(),
                molecule: $row.find('td:eq(5) span').text().trim(),
                category_type: $row.find('td:eq(6) span span').text().trim(),
                category: $row.find('td:eq(7) span span').text().trim(),
                currentMarket: $row.find('td:eq(8) span span').text().trim()
            };

            // Llenar el modal con los datos
            $('#assign-product-id').val(productData.sku);
            $('#modal-product-sku').text(productData.sku);
            $('#modal-product-description').text(productData.description);
            $('#modal-product-atc').text(productData.atc + (productData.atc_desc ? ' - ' + productData.atc_desc : ''));
            $('#modal-product-ff').text(productData.ff + (productData.ff_desc ? ' - ' + productData.ff_desc : ''));
            $('#modal-product-molecule').text(productData.molecule || 'N/A');
            $('#modal-product-type').text(productData.category_type);
            $('#modal-current-market').text(productData.currentMarket || 'SIN ASIGNAR');

            // Mostrar el modal
            $('#assign-market-modal').removeClass('hidden');

            // Reinicializar Select2 para mercados
            const $marketSelect = $('#market-search');
            
            // Destruir Select2 existente si existe
            if ($marketSelect.hasClass('select2-hidden-accessible')) {
                $marketSelect.select2('destroy');
            }

            // Inicializar Select2 de nuevo
            $marketSelect.select2({
                placeholder: 'Buscar y seleccionar mercado...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#assign-market-modal'),
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

            // Limpiar selección anterior
            $marketSelect.val(null).trigger('change');
        });

        // Habilitar/deshabilitar botón de asignar según selección
        $('#market-search').on('change', function () {
            const hasSelection = $(this).val() !== null && $(this).val() !== '';
            $('#confirm-assign').prop('disabled', !hasSelection);
        });

        // Cerrar modal
        $('#close-assign-modal, #cancel-assign').on('click', function () {
            $('#assign-market-modal').addClass('hidden');
            $('#market-search').val(null).trigger('change');
        });

        // Handle individual market assignment form submission
        $('#assign-market-form').on('submit', function (e) {
            e.preventDefault();
            
            const productId = $('#assign-product-id').val();
            const marketId = $('#market-search').val();
            
            if (!productId || !marketId) {
                showToast('Por favor selecciona un mercado', 'error');
                return;
            }
            
            const button = $('#confirm-assign');
            toggleButtonLoading(button, true);
            
            $.post(window.MarketConfigRoutes.assignMarket, {
                _token: window.csrfToken,
                product_id: productId,
                market_id: marketId
            })
            .done(function(response) {
                if (response.success) {
                    showToast('Mercado asignado correctamente', 'success');
                    $('#assign-market-modal').addClass('hidden');
                    // Reload page to see changes
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.message || 'Error al asignar mercado', 'error');
                }
            })
            .fail(function() {
                showToast('Error al asignar mercado', 'error');
            })
            .always(function() {
                toggleButtonLoading(button, false);
            });
        });

        // Bulk market assignment
        $('#bulk-assign-market').on('click', function () {
            const selectedIds = $('.product-checkbox:checked').map(function () {
                return this.value;
            }).get();

            if (selectedIds.length > 0) {
                // You can implement bulk assignment modal here
                console.log('Bulk assign market for:', selectedIds);
                // $('#bulk-assign-modal').removeClass('hidden');
            }
        });

        // Close modals
        $('.close-modal, .modal-overlay').on('click', function (e) {
            if (e.target === this) {
                $(this).closest('.modal').addClass('hidden');
            }
        });

        // Create Market Modal functionality
        $('#create-market-btn').on('click', function () {
            $('#create-market-modal').removeClass('hidden');
            $('#market-name').focus();
        });

        // Close create market modal
        $('#close-create-market-modal, #cancel-create-market').on('click', function () {
            $('#create-market-modal').addClass('hidden');
            resetCreateMarketForm();
        });

        // Handle create market form submission
        $('#create-market-form').on('submit', function (e) {
            e.preventDefault();
            
            console.log('Form submitted - creating market');

            const submitBtn = $('#confirm-create-market');
            const btnText = submitBtn.find('.btn-text');
            const btnLoading = submitBtn.find('.btn-loading');

            // Show loading state
            btnText.addClass('hidden');
            btnLoading.removeClass('hidden');
            submitBtn.prop('disabled', true);

            // Clear previous errors
            $('.error-message').addClass('hidden');

            const formData = {
                market_name: $('#market-name').val().trim(),
                description: $('#market-description').val().trim(),
                _token: window.csrfToken
            };
            
            console.log('Sending data:', formData);
            console.log('URL:', window.MarketConfigRoutes.createMarket);

            $.ajax({
                url: window.MarketConfigRoutes.createMarket,
                method: 'POST',
                data: formData,
                success: function (response) {
                    console.log('Success response:', response);
                    if (response.success) {
                        showToast('Mercado creado correctamente', 'success');
                        $('#create-market-modal').addClass('hidden');
                        resetCreateMarketForm();

                        // Refresh the page to show the new market option
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        console.log('Success but with error:', response.message);
                        showToast(response.message || 'Error al crear el mercado', 'error');
                    }
                },
                error: function (xhr) {
                    console.error('Error response:', xhr);
                    let errorMessage = 'Error al crear el mercado';

                    if (xhr.responseJSON) {
                        console.log('Error details:', xhr.responseJSON);
                        if (xhr.responseJSON.errors) {
                            // Handle validation errors
                            const errors = xhr.responseJSON.errors;
                            if (errors.market_name) {
                                $('#name-error').text(errors.market_name[0]).removeClass('hidden');
                            }
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    }

                    showToast(errorMessage, 'error');
                },
                complete: function () {
                    console.log('Request completed');
                    // Reset loading state
                    btnText.removeClass('hidden');
                    btnLoading.addClass('hidden');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        function resetCreateMarketForm() {
            $('#create-market-form')[0].reset();
            $('.error-message').addClass('hidden');
        }

        // Keyboard shortcuts
        $(document).on('keydown', function (e) {
            // Escape key to close modals
            if (e.key === 'Escape') {
                $('.modal').addClass('hidden');
            }

            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('#search').focus();
            }
        });

        // Smooth animations for filter changes
        $('.filter-item').each(function (index) {
            $(this).css('animation-delay', (index * 50) + 'ms');
        });

        // Auto-focus search on page load
        if (!$('#search').val()) {
            setTimeout(function () {
                $('#search').focus();
            }, 500);
        }

        // Show filter count in tab title
        function updateTabTitle() {
            const activeFilters = $('[name]:not([name=""])').filter(function () {
                return $(this).val() && $(this).val() !== '';
            }).length;

            if (activeFilters > 0) {
                document.title = `(${activeFilters}) Configuración de Mercado - Materiales`;
            } else {
                document.title = 'Configuración de Mercado - Materiales';
            }
        }

        updateTabTitle();

        // Update title when filters change
        $('.select2-filter, #search').on('change input', function () {
            setTimeout(updateTabTitle, 100);
        });
    });

    /**
     * Initialize sidebar toggle functionality
     */
    function initializeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('toggle-sidebar');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarLogo = document.getElementById('sidebar-logo');
        const mainContent = document.getElementById('main-content');

        if (sidebar && toggleButton) {
            // Set initial state based on localStorage
            const setInitialSidebarState = () => {
                const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('w-20');
                    sidebar.classList.remove('w-56');
                    sidebarTexts.forEach(text => text.classList.add('hidden'));
                    if (sidebarLogo) sidebarLogo.classList.add('opacity-0');
                    if (mainContent) mainContent.classList.add('ml-20');
                    //if (mainContent) mainContent.classList.remove('ml-56');
                } else {
                    sidebar.classList.add('w-56');
                    sidebar.classList.remove('w-20');
                    sidebarTexts.forEach(text => text.classList.remove('hidden'));
                    if (sidebarLogo) sidebarLogo.classList.remove('opacity-0');
                    //if (mainContent) mainContent.classList.add('ml-56');
                    if (mainContent) mainContent.classList.remove('ml-20');
                }
            };
            setInitialSidebarState();

            // Toggle sidebar on button click
            toggleButton.addEventListener('click', () => {
                const isCurrentlyCollapsed = sidebar.classList.contains('w-20');

                sidebar.classList.toggle('w-56');
                sidebar.classList.toggle('w-20');
                sidebarTexts.forEach(text => text.classList.toggle('hidden'));
                if (sidebarLogo) sidebarLogo.classList.toggle('opacity-0');

                // Adjust main content margin
                if (mainContent) {
                    if (isCurrentlyCollapsed) {
                        //mainContent.classList.add('ml-56');
                        mainContent.classList.remove('ml-20');
                    } else {
                        mainContent.classList.add('ml-20');
                        //mainContent.classList.remove('ml-56');
                    }
                }

                localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('w-20'));
            });
        }
    }

    initializeSidebar();

    // ==========================================
    // NEW BULK AND INDIVIDUAL OPERATIONS
    // ==========================================

    // Global variables
    let allProducts = [];
    let allMarkets = [];

    // Initialize new functionality
    initializeNewFunctionality();

    function initializeNewFunctionality() {
        // Wait for DOM to be fully loaded before loading data
        setTimeout(() => {
            loadAllProducts();
            loadAllMarkets();
            bindNewEventHandlers();
        }, 100);
    }

    // Load all products for bulk operations
    function loadAllProducts() {
        allProducts = [];
        $('table tbody tr').each(function () {
            const row = $(this);
            const sku = row.find('td:eq(1)').text().trim();
            const descripcion = row.find('td:eq(2)').text().trim();
            const mercado = row.find('td:eq(8) span span').text().trim(); // Mercado está en columna 8

            if (sku && descripcion) {
                allProducts.push({
                    sku: sku,
                    descripcion: descripcion,
                    mercado: mercado === 'RESTO' ? '' : mercado
                });
            }
        });
        console.log('Products loaded:', allProducts.length);
    }

    // Load all available markets
    function loadAllMarkets() {
        console.log('Loading markets from:', window.MarketConfigRoutes.getMarkets);
        $.get(window.MarketConfigRoutes.getMarkets)
            .done(function (response) {
                console.log('Markets response:', response);
                if (response.success) {
                    allMarkets = response.markets;
                    console.log('Markets loaded:', allMarkets.length);
                    populateMarketSelectors();
                }
            })
            .fail(function (xhr, status, error) {
                console.error('Error loading markets:', error);
                showToast('Error al cargar mercados', 'error');
            });
    }

    // Populate all market selectors
    function populateMarketSelectors() {
        const selectors = [
            '#bulk-market-selector',
            '#market-filter-selector'
        ];

        selectors.forEach(selector => {
            const $select = $(selector);
            $select.empty();

            if (selector === '#bulk-market-selector') {
                $select.append('<option value="">Selecciona un mercado...</option>');
            } else {
                $select.append('<option value="">Todos los mercados</option>');
            }

            allMarkets.forEach(market => {
                $select.append(`<option value="${market}">${market}</option>`);
            });
        });

        // Initialize Select2
        $('.select2-markets').select2({
            placeholder: 'Selecciona un mercado...',
            allowClear: true,
            width: '100%'
        });
    }

    // Bind new event handlers
    function bindNewEventHandlers() {
        // Bulk assign market button
        $('#bulk-assign-market-btn').click(function () {
            openBulkAssignMarketModal();
        });

        // Bulk remove market button
        $('#bulk-remove-market-btn').click(function () {
            openBulkRemoveMarketModal();
        });

        // Manage markets button
        $('#manage-markets-btn').click(function () {
            showMarketManagementOptions();
        });

        // Individual remove market buttons
        $(document).on('click', '.remove-market-btn', function () {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const currentMarket = $(this).data('current-market');

            openRemoveMarketModal(productId, productName, currentMarket);
        });

        // Individual edit market buttons (reuse assign modal)
        $(document).on('click', '.edit-market-btn', function () {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const currentMarket = $(this).data('current-market');

            // Reuse existing assign market modal
            $('#assign-market-modal').removeClass('hidden');
            $('#modal-product-sku').text(productId);
            $('#modal-product-name').text(productName);
            $('#modal-current-market').text(currentMarket);
            $('#assign-product-id').val(productId);

            // Load markets and set current one as selected
            loadMarketsForAssignment(currentMarket);
        });

        // Modal close handlers
        $('#close-bulk-assign-market-modal, #cancel-bulk-assign-market').click(function () {
            closeBulkAssignMarketModal();
        });

        $('#close-bulk-remove-market-modal, #cancel-bulk-remove-market').click(function () {
            closeBulkRemoveMarketModal();
        });

        $('#close-edit-market-modal, #cancel-edit-market').click(function () {
            closeEditMarketModal();
        });

        // Form submissions
        $('#bulk-assign-market-form').submit(function (e) {
            e.preventDefault();
            processBulkAssignMarket();
        });

        $('#bulk-remove-market-form').submit(function (e) {
            e.preventDefault();
            processBulkRemoveMarket();
        });

        $('#edit-market-form').submit(function (e) {
            e.preventDefault();
            processEditMarket();
        });

        // Individual remove market form
        $('#remove-market-form').submit(function (e) {
            e.preventDefault();
            processRemoveMarket();
        });
    }

    // ==========================================
    // BULK ASSIGN MARKET MODAL
    // ==========================================

    function openBulkAssignMarketModal() {
        loadAllProducts();
        populateProductsTable();
        $('#bulk-assign-market-modal').removeClass('hidden');
        setTimeout(() => {
            $('#bulk-assign-market-modal .modal-container').removeClass('scale-95').addClass('scale-100');
        }, 10);
    }

    function closeBulkAssignMarketModal() {
        $('#bulk-assign-market-modal .modal-container').removeClass('scale-100').addClass('scale-95');
        setTimeout(() => {
            $('#bulk-assign-market-modal').addClass('hidden');
            resetBulkAssignModal();
        }, 200);
    }

    function populateProductsTable() {
        const tbody = $('#products-table-body');
        tbody.empty();

        allProducts.forEach(product => {
            const hasMarket = product.mercado && product.mercado !== 'RESTO';
            const marketDisplay = hasMarket ? product.mercado : 'RESTO';
            const marketClass = hasMarket ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

            const row = `
                    <tr class="hover:bg-gray-50 product-row" data-sku="${product.sku}">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="product-checkbox form-checkbox text-blue-600" value="${product.sku}">
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${product.sku}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${product.descripcion}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${marketClass}">
                                ${marketDisplay}
                            </span>
                        </td>
                    </tr>
                `;
            tbody.append(row);
        });

        updateProductCounts();
        bindProductTableEvents();
    }

    function bindProductTableEvents() {
        // Select all checkbox
        $('#select-all-products').change(function () {
            const isChecked = $(this).is(':checked');
            $('.product-checkbox:visible').prop('checked', isChecked);
            updateSelectedCount();
            toggleAssignButton();
        });

        // Individual checkboxes
        $(document).on('change', '.product-checkbox', function () {
            updateSelectedCount();
            toggleAssignButton();
            updateSelectAllState();
        });

        // Search filter
        $('#product-search-filter').on('input', function () {
            filterProductsTable();
        });

        // Filter buttons
        $('#filter-no-market').click(function () {
            filterProductsByMarket(false);
        });

        $('#filter-with-market').click(function () {
            filterProductsByMarket(true);
        });

        $('#clear-filters').click(function () {
            clearProductFilters();
        });

        // Market selection
        $('#bulk-market-selector').change(function () {
            toggleAssignButton();
            checkForWarnings();
        });
    }

    function filterProductsTable() {
        const searchTerm = $('#product-search-filter').val().toLowerCase();
        $('.product-row').each(function () {
            const row = $(this);
            const text = row.text().toLowerCase();
            const matches = text.includes(searchTerm);
            row.toggle(matches);
        });
        updateProductCounts();
    }

    function filterProductsByMarket(hasMarket) {
        $('.product-row').each(function () {
            const row = $(this);
            const marketSpan = row.find('td:eq(3) span');
            const isResto = marketSpan.hasClass('bg-red-100');
            const shouldShow = hasMarket ? !isResto : isResto;
            row.toggle(shouldShow);
        });
        updateProductCounts();
    }

    function clearProductFilters() {
        $('#product-search-filter').val('');
        $('.product-row').show();
        updateProductCounts();
    }

    function updateSelectedCount() {
        const selectedCount = $('.product-checkbox:checked').length;
        $('#selected-count').text(`${selectedCount} productos seleccionados`);
    }

    function updateProductCounts() {
        const visibleCount = $('.product-row:visible').length;
        $('#total-products-count').text(`${visibleCount} productos disponibles`);
    }

    function updateSelectAllState() {
        const visibleCheckboxes = $('.product-checkbox:visible');
        const checkedCheckboxes = $('.product-checkbox:checked:visible');
        const selectAllCheckbox = $('#select-all-products');

        if (checkedCheckboxes.length === 0) {
            selectAllCheckbox.prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.prop('indeterminate', false).prop('checked', true);
        } else {
            selectAllCheckbox.prop('indeterminate', true).prop('checked', false);
        }
    }

    function toggleAssignButton() {
        const hasSelection = $('.product-checkbox:checked').length > 0;
        const hasMarket = $('#bulk-market-selector').val();
        $('#confirm-bulk-assign-market').prop('disabled', !(hasSelection && hasMarket));
    }

    function checkForWarnings() {
        const selectedProducts = getSelectedProducts();
        const hasProductsWithMarkets = selectedProducts.some(p => p.mercado && p.mercado !== 'RESTO');
        $('#assignment-warning').toggle(hasProductsWithMarkets);
    }

    function getSelectedProducts() {
        const selectedSkus = $('.product-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        return allProducts.filter(p => selectedSkus.includes(p.sku));
    }

    function processBulkAssignMarket() {
        const selectedProducts = getSelectedProducts();
        const marketId = $('#bulk-market-selector').val();

        if (selectedProducts.length === 0 || !marketId) {
            showToast('Selecciona productos y un mercado', 'error');
            return;
        }

        const productIds = selectedProducts.map(p => p.sku);
        const button = $('#confirm-bulk-assign-market');

        toggleButtonLoading(button, true);

        $.post(window.MarketConfigRoutes.bulkAssignMarket, {
            _token: window.csrfToken,
            product_ids: productIds,
            market_id: marketId
        })
            .done(function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    closeBulkAssignMarketModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(response.message || 'Error al asignar mercado', 'error');
                }
            })
            .fail(function () {
                showToast('Error al procesar la solicitud', 'error');
            })
            .always(function () {
                toggleButtonLoading(button, false);
            });
    }

    function resetBulkAssignModal() {
        $('#bulk-market-selector').val('').trigger('change');
        $('#product-search-filter').val('');
        $('.product-checkbox').prop('checked', false);
        $('#select-all-products').prop('checked', false);
        $('#assignment-warning').hide();
        $('#confirm-bulk-assign-market').prop('disabled', true);
    }

    // ==========================================
    // BULK REMOVE MARKET MODAL
    // ==========================================

    function openBulkRemoveMarketModal() {
        loadAllProducts();
        populateRemoveProductsTable();
        $('#bulk-remove-market-modal').removeClass('hidden');
        setTimeout(() => {
            $('#bulk-remove-market-modal .modal-container').removeClass('scale-95').addClass('scale-100');
        }, 10);
    }

    function closeBulkRemoveMarketModal() {
        $('#bulk-remove-market-modal .modal-container').removeClass('scale-100').addClass('scale-95');
        setTimeout(() => {
            $('#bulk-remove-market-modal').addClass('hidden');
            resetBulkRemoveModal();
        }, 200);
    }

    function populateRemoveProductsTable() {
        const tbody = $('#remove-products-table-body');
        tbody.empty();

        allProducts.forEach(product => {
            const hasMarket = product.mercado && product.mercado !== 'RESTO';
            const marketDisplay = hasMarket ? product.mercado : 'RESTO';
            const marketClass = hasMarket ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

            const row = `
                    <tr class="hover:bg-gray-50 remove-product-row" data-sku="${product.sku}" data-market="${product.mercado || ''}">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="remove-product-checkbox form-checkbox text-red-600" value="${product.sku}">
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">${product.sku}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${product.descripcion}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${marketClass}">
                                ${marketDisplay}
                            </span>
                        </td>
                    </tr>
                `;
            tbody.append(row);
        });

        updateRemoveProductCounts();
        bindRemoveProductTableEvents();
    }

    function bindRemoveProductTableEvents() {
        // Select all checkbox
        $('#select-all-remove-products').change(function () {
            const isChecked = $(this).is(':checked');
            $('.remove-product-checkbox:visible').prop('checked', isChecked);
            updateRemoveSelectedCount();
            toggleRemoveButton();
        });

        // Individual checkboxes
        $(document).on('change', '.remove-product-checkbox', function () {
            updateRemoveSelectedCount();
            toggleRemoveButton();
            updateRemoveSelectAllState();
        });

        // Search filter
        $('#remove-product-search-filter').on('input', function () {
            filterRemoveProductsTable();
        });

        // Market filter
        $('#market-filter-selector').change(function () {
            filterRemoveProductsByMarket();
        });

        // Filter buttons
        $('#filter-only-with-market').click(function () {
            filterRemoveProductsByMarketStatus(true);
        });

        $('#clear-remove-filters').click(function () {
            clearRemoveProductFilters();
        });
    }

    function filterRemoveProductsTable() {
        const searchTerm = $('#remove-product-search-filter').val().toLowerCase();
        $('.remove-product-row').each(function () {
            const row = $(this);
            const text = row.text().toLowerCase();
            const matches = text.includes(searchTerm);
            row.toggle(matches);
        });
        updateRemoveProductCounts();
    }

    function filterRemoveProductsByMarket() {
        const selectedMarket = $('#market-filter-selector').val();
        if (!selectedMarket) {
            $('.remove-product-row').show();
        } else {
            $('.remove-product-row').each(function () {
                const row = $(this);
                const productMarket = row.data('market');
                const shouldShow = productMarket === selectedMarket;
                row.toggle(shouldShow);
            });
        }
        updateRemoveProductCounts();
    }

    function filterRemoveProductsByMarketStatus(hasMarket) {
        $('.remove-product-row').each(function () {
            const row = $(this);
            const marketSpan = row.find('td:eq(3) span');
            const isResto = marketSpan.hasClass('bg-red-100');
            const shouldShow = hasMarket ? !isResto : isResto;
            row.toggle(shouldShow);
        });
        updateRemoveProductCounts();
    }

    function clearRemoveProductFilters() {
        $('#remove-product-search-filter').val('');
        $('#market-filter-selector').val('').trigger('change');
        $('.remove-product-row').show();
        updateRemoveProductCounts();
    }

    function updateRemoveSelectedCount() {
        const selectedCount = $('.remove-product-checkbox:checked').length;
        $('#selected-remove-count').text(`${selectedCount} productos seleccionados`);
    }

    function updateRemoveProductCounts() {
        const visibleCount = $('.remove-product-row:visible').length;
        $('#total-remove-products-count').text(`${visibleCount} productos disponibles`);
    }

    function updateRemoveSelectAllState() {
        const visibleCheckboxes = $('.remove-product-checkbox:visible');
        const checkedCheckboxes = $('.remove-product-checkbox:checked:visible');
        const selectAllCheckbox = $('#select-all-remove-products');

        if (checkedCheckboxes.length === 0) {
            selectAllCheckbox.prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes.length === visibleCheckboxes.length) {
            selectAllCheckbox.prop('indeterminate', false).prop('checked', true);
        } else {
            selectAllCheckbox.prop('indeterminate', true).prop('checked', false);
        }
    }

    function toggleRemoveButton() {
        const hasSelection = $('.remove-product-checkbox:checked').length > 0;
        $('#confirm-bulk-remove-market').prop('disabled', !hasSelection);
    }

    function getSelectedRemoveProducts() {
        const selectedSkus = $('.remove-product-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        return allProducts.filter(p => selectedSkus.includes(p.sku));
    }

    function processBulkRemoveMarket() {
        const selectedProducts = getSelectedRemoveProducts();

        if (selectedProducts.length === 0) {
            showToast('Selecciona al menos un producto', 'error');
            return;
        }

        const productIds = selectedProducts.map(p => p.sku);
        const button = $('#confirm-bulk-remove-market');

        toggleButtonLoading(button, true);

        $.post(window.MarketConfigRoutes.bulkRemoveMarket, {
            _token: window.csrfToken,
            product_ids: productIds
        })
            .done(function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    closeBulkRemoveMarketModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(response.message || 'Error al quitar mercado', 'error');
                }
            })
            .fail(function () {
                showToast('Error al procesar la solicitud', 'error');
            })
            .always(function () {
                toggleButtonLoading(button, false);
            });
    }

    function resetBulkRemoveModal() {
        $('#market-filter-selector').val('').trigger('change');
        $('#remove-product-search-filter').val('');
        $('.remove-product-checkbox').prop('checked', false);
        $('#select-all-remove-products').prop('checked', false);
        $('#confirm-bulk-remove-market').prop('disabled', true);
    }

    // ==========================================
    // INDIVIDUAL REMOVE MARKET
    // ==========================================

    function openRemoveMarketModal(productId, productName, currentMarket) {
        $('#remove-product-id').val(productId);
        $('#remove-product-name').text(productName);
        $('#remove-current-market').text(currentMarket);
        $('#remove-market-modal').removeClass('hidden');
    }

    function processRemoveMarket() {
        const productId = $('#remove-product-id').val();
        const button = $('#confirm-remove');

        toggleButtonLoading(button, true);

        $.post(window.MarketConfigRoutes.removeMarketFromMaterial, {
            _token: window.csrfToken,
            product_id: productId
        })
            .done(function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#remove-market-modal').addClass('hidden');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(response.message || 'Error al quitar mercado', 'error');
                }
            })
            .fail(function () {
                showToast('Error al procesar la solicitud', 'error');
            })
            .always(function () {
                toggleButtonLoading(button, false);
            });
    }

    // ==========================================
    // MARKET MANAGEMENT
    // ==========================================

    function showMarketManagementOptions() {
        const marketOptions = allMarkets.map(market =>
            `<option value="${market}">${market}</option>`
        ).join('');

        const modalContent = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" id="market-management-selector">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Gestionar Mercados</h3>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecciona un mercado para editar:
                                </label>
                                <select id="market-to-edit" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Selecciona un mercado...</option>
                                    ${marketOptions}
                                </select>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button id="cancel-market-management" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                                    Cancelar
                                </button>
                                <button id="edit-selected-market" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg" disabled>
                                    Editar Mercado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

        $('body').append(modalContent);

        // Bind events
        $('#market-to-edit').change(function () {
            $('#edit-selected-market').prop('disabled', !$(this).val());
        });

        $('#cancel-market-management').click(function () {
            $('#market-management-selector').remove();
        });

        $('#edit-selected-market').click(function () {
            const selectedMarket = $('#market-to-edit').val();
            if (selectedMarket) {
                $('#market-management-selector').remove();
                openEditMarketModal(selectedMarket);
            }
        });
    }

    function openEditMarketModal(marketName) {
        $('#edit-old-market-name').val(marketName);
        $('#current-market-name').text(marketName);
        $('#new-market-name').val(marketName);

        loadMarketProductsCount(marketName);

        $('#edit-market-modal').removeClass('hidden');
        setTimeout(() => {
            $('#edit-market-modal .modal-container').removeClass('scale-95').addClass('scale-100');
        }, 10);
    }

    function closeEditMarketModal() {
        $('#edit-market-modal .modal-container').removeClass('scale-100').addClass('scale-95');
        setTimeout(() => {
            $('#edit-market-modal').addClass('hidden');
        }, 200);
    }

    function loadMarketProductsCount(marketName) {
        $.get(window.MarketConfigRoutes.productsByMarket, {
            market_name: marketName
        })
            .done(function (response) {
                if (response.success) {
                    $('#current-market-products-count').text(`${response.count} productos`);
                    populateAffectedProductsList(response.products);
                }
            })
            .fail(function () {
                $('#current-market-products-count').text('Error al cargar');
            });
    }

    function populateAffectedProductsList(products) {
        const container = $('#affected-products-list');
        container.empty();

        if (products.length === 0) {
            container.html('<p class="text-gray-500">No hay productos asignados a este mercado.</p>');
            return;
        }

        const productsList = products.slice(0, 10).map(product =>
            `<div class="py-1 text-sm text-gray-600">${product.SKU} - ${product['Descripción_Presentación']}</div>`
        ).join('');

        const moreText = products.length > 10 ?
            `<div class="py-1 text-sm text-blue-600">... y ${products.length - 10} productos más</div>` : '';

        container.html(productsList + moreText);
    }

    // Toggle products preview
    $(document).on('click', '#toggle-products-preview', function () {
        const preview = $('#products-preview');
        const chevron = $('#preview-chevron');

        if (preview.hasClass('hidden')) {
            preview.removeClass('hidden');
            chevron.removeClass('fa-chevron-right').addClass('fa-chevron-down');
        } else {
            preview.addClass('hidden');
            chevron.removeClass('fa-chevron-down').addClass('fa-chevron-right');
        }
    });

    function processEditMarket() {
        const oldMarketName = $('#edit-old-market-name').val();
        const newMarketName = $('#new-market-name').val().trim();

        if (!newMarketName) {
            showToast('Ingresa un nombre para el mercado', 'error');
            return;
        }

        if (oldMarketName === newMarketName) {
            showToast('El nombre del mercado debe ser diferente', 'error');
            return;
        }

        const button = $('#confirm-edit-market');
        toggleButtonLoading(button, true);

        $.post(window.MarketConfigRoutes.editMarketName, {
            _token: window.csrfToken,
            old_market_name: oldMarketName,
            new_market_name: newMarketName
        })
            .done(function (response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    closeEditMarketModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(response.message || 'Error al editar mercado', 'error');
                }
            })
            .fail(function () {
                showToast('Error al procesar la solicitud', 'error');
            })
            .always(function () {
                toggleButtonLoading(button, false);
            });
    }

    // ==========================================
    // UTILITY FUNCTIONS
    // ==========================================

    function toggleButtonLoading(button, isLoading) {
        const btnText = button.find('.btn-text');
        const btnLoading = button.find('.btn-loading');

        if (isLoading) {
            btnText.addClass('hidden');
            btnLoading.removeClass('hidden');
            button.prop('disabled', true);
        } else {
            btnText.removeClass('hidden');
            btnLoading.addClass('hidden');
            button.prop('disabled', false);
        }
    }

    function loadMarketsForAssignment(currentMarket = null) {
        const select = $('#market-search');
        select.empty().append('<option value="">Selecciona un mercado...</option>');

        allMarkets.forEach(market => {
            const selected = market === currentMarket ? 'selected' : '';
            select.append(`<option value="${market}" ${selected}>${market}</option>`);
        });

        select.trigger('change');
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastContainer = $('#toast-container');
        
        if (toastContainer.length === 0) {
            $('body').append('<div id="toast-container" class="toast-container"></div>');
        }

        const toastId = 'toast-' + Date.now();
        const iconClass = type === 'success' ? 'fa-check-circle text-green-600' : 
                         type === 'error' ? 'fa-exclamation-circle text-red-600' : 
                         'fa-info-circle text-blue-600';
        
        const bgClass = type === 'success' ? 'bg-green-50 border-green-200' : 
                       type === 'error' ? 'bg-red-50 border-red-200' : 
                       'bg-blue-50 border-blue-200';

        const toast = `
            <div id="${toastId}" class="toast ${bgClass} border rounded-lg p-4 mb-3 shadow-lg transform translate-x-full transition-transform duration-300">
                <div class="flex items-center">
                    <i class="fas ${iconClass} mr-3"></i>
                    <span class="text-gray-800">${message}</span>
                    <button class="ml-auto text-gray-400 hover:text-gray-600" onclick="removeToast('${toastId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        $('#toast-container').append(toast);
        
        // Animate in
        setTimeout(() => {
            $(`#${toastId}`).removeClass('translate-x-full');
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            removeToast(toastId);
        }, 5000);
    }

    // Remove toast function
    window.removeToast = function(toastId) {
        const toast = $(`#${toastId}`);
        if (toast.length) {
            toast.addClass('translate-x-full');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    };
});
