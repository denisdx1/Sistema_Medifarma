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
            $('#filters-form')[0].reset();
            $('.select2-filter').val(null).trigger('change');

            // Submit form to reload with cleared filters
            setTimeout(function () {
                $('#filters-form').submit();
            }, 100);
        });

        // Handle checkbox selection
        $('#select-all').on('change', function () {
            $('.product-checkbox').prop('checked', this.checked);
            updateSelectedCount();
        });

        // Handle individual checkbox changes
        $('.product-checkbox').on('change', function () {
            updateSelectedCount();
            
            // Update "select all" checkbox state
            const totalCheckboxes = $('.product-checkbox').length;
            const checkedCheckboxes = $('.product-checkbox:checked').length;
            
            $('#select-all').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
            $('#select-all').prop('checked', checkedCheckboxes === totalCheckboxes);
        });

        // Remove market button functionality
        $('.remove-market-btn').on('click', function () {
            const productId = $(this).data('product-id');
            const productName = $(this).data('product-name');
            const currentMarket = $(this).data('current-market');
            
            if (confirm(`¿Estás seguro de que quieres remover el mercado "${currentMarket}" del producto "${productName}"?`)) {
                removeMarketFromProduct(productId);
            }
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
            $('#create-market-modal').removeClass('hidden');
        });

        // Close create market modal
        $('#close-create-market-modal, #cancel-create-market').on('click', function () {
            $('#create-market-modal').addClass('hidden');
            resetCreateMarketForm();
        });

        // Handle create market form submission
        $('#create-market-form').on('submit', function (e) {
            e.preventDefault();
            
            const marketName = $('#market-name').val().trim();
            
            if (!marketName) {
                showToast('Por favor ingresa un nombre para el mercado', 'error');
                return;
            }
            
            const submitBtn = $('#create-market-submit');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Creando...');
            
            $.post(window.MarketConfigRoutes.createMarket, {
                market_name: marketName,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .done(function(response) {
                if (response.success) {
                    showToast(response.message || 'Mercado creado exitosamente', 'success');
                    $('#create-market-modal').addClass('hidden');
                    resetCreateMarketForm();
                    
                    // Reload page to show new market
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(response.message || 'Error al crear mercado', 'error');
                }
            })
            .fail(function() {
                showToast('Error al crear mercado', 'error');
            })
            .always(function() {
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            });
        });

        // Export functionality
        $('#bulk-export').on('click', function() {
            exportSelectedProducts();
        });

        // Close modal when clicking outside
        $('.close-modal, .modal-overlay').on('click', function (e) {
            if (e.target === this) {
                $(this).closest('.modal-overlay').addClass('hidden');
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function (e) {
            // ESC key to close modals
            if (e.key === 'Escape') {
                $('.modal-overlay:not(.hidden)').addClass('hidden');
                e.preventDefault();
            }
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
                $('#filters-form').submit();
            }, 500);
        });

        // Initialize results info
        updateResultsInfo();
    });

    // Helper Functions
    function updateSelectedCount() {
        const selectedCount = $('.product-checkbox:checked').length;
        const countDisplay = $('#selected-count');
        
        if (countDisplay.length) {
            countDisplay.text(selectedCount);
        }
        
        // Show/hide export button based on selection
        const exportBtn = $('#bulk-export');
        if (exportBtn.length) {
            exportBtn.toggle(selectedCount > 0);
        }
    }

    function resetCreateMarketForm() {
        $('#market-name').val('');
        const submitBtn = $('#create-market-submit');
        submitBtn.prop('disabled', false);
        submitBtn.html('<i class="fas fa-save mr-2"></i>Crear Mercado');
    }

    function removeMarketFromProduct(productId) {
        $.post(window.MarketConfigRoutes.removeMarketFromMaterial, {
            product_id: productId,
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function(response) {
            if (response.success) {
                showToast(response.message || 'Mercado removido exitosamente', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showToast(response.message || 'Error al remover mercado', 'error');
            }
        })
        .fail(function() {
            showToast('Error al remover mercado', 'error');
        });
    }

    function exportSelectedProducts() {
        const selectedProducts = [];
        $('.product-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            selectedProducts.push({
                sku: $(this).val(),
                descripcion: row.find('td:nth-child(3)').text().trim(),
                marca: row.find('td:nth-child(4)').text().trim(),
                laboratorio: row.find('td:nth-child(5)').text().trim(),
                corporacion: row.find('td:nth-child(6)').text().trim(),
                mercado: row.find('td:nth-child(7)').text().trim()
            });
        });

        if (selectedProducts.length === 0) {
            showToast('No hay productos seleccionados para exportar', 'warning');
            return;
        }

        // Create CSV content
        const headers = ['SKU', 'Descripción', 'Marca', 'Laboratorio', 'Corporación', 'Mercado'];
        const csvContent = [
            headers.join(','),
            ...selectedProducts.map(product => 
                [product.sku, product.descripcion, product.marca, product.laboratorio, product.corporacion, product.mercado]
                .map(field => `"${field.replace(/"/g, '""')}"`)
                .join(',')
            )
        ].join('\n');

        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `productos_seleccionados_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showToast(`Se exportaron ${selectedProducts.length} productos`, 'success');
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
        $('.toast').remove();
        
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
            <div class="toast fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center">
                <i class="${icon} mr-2"></i>
                <span>${message}</span>
                <button class="ml-4 text-white hover:text-gray-200" onclick="$(this).parent().remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
        
        $('body').append(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.fadeOut(() => {
                toast.remove();
            });
        }, 5000);
    }

    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
        showToast('Ha ocurrido un error. Por favor recarga la página.', 'error');
    });
});
