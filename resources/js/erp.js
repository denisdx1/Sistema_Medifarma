// ERP System JavaScript Functions
// Auto Search and Modal Management

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Sidebar Toggle Functionality ---
    initializeSidebar();
    
    // --- Enhanced Select2 Initializer ---
    initializeSelect2();
    initializeFilterSelects();
    
    // --- Auto Search Functionality ---
    initializeAutoSearch();
    
    // --- Generate SKU Modal ---
    initializeGenerateSKUModal();
});

/**
 * Initialize sidebar toggle functionality
 */
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('toggle-sidebar');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    const sidebarLogo = document.getElementById('sidebar-logo');

    if (sidebar && toggleButton) {
        // Set initial state based on localStorage
        const setInitialSidebarState = () => {
            const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('w-20');
                sidebar.classList.remove('w-56');
                sidebarTexts.forEach(text => text.classList.add('hidden'));
                sidebarLogo.classList.add('opacity-0');
            } else {
                sidebar.classList.add('w-56');
                sidebar.classList.remove('w-20');
                sidebarTexts.forEach(text => text.classList.remove('hidden'));
                sidebarLogo.classList.remove('opacity-0');
            }
        };
        setInitialSidebarState();

        // Toggle sidebar on button click
        toggleButton.addEventListener('click', () => {
            sidebar.classList.toggle('w-56');
            sidebar.classList.toggle('w-20');
            sidebarTexts.forEach(text => text.classList.toggle('hidden'));
            sidebarLogo.classList.toggle('opacity-0');
            localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('w-20'));
        });
    }
}

/**
 * Initialize Select2 components
 */
function initializeSelect2() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({
            width: '100%'
        });
    } else {
        console.warn('jQuery or Select2 not loaded');
    }
}

/**
 * Initialize auto search functionality
 */
function initializeAutoSearch() {
    let searchTimeout;
    const searchForm = document.querySelector('form[action*="erp"]');
    const searchInput = document.getElementById('search');
    const searchLoading = document.getElementById('search-loading');

    if (!searchForm) {
        console.warn('Search form not found');
        return;
    }

    // Function to show loading indicator
    function showLoading() {
        if (searchLoading) {
            searchLoading.classList.remove('hidden');
        }
    }

    // Function to hide loading indicator
    function hideLoading() {
        if (searchLoading) {
            searchLoading.classList.add('hidden');
        }
    }

    // Function to perform auto search with debounce
    function performAutoSearch() {
        clearTimeout(searchTimeout);
        showLoading();
        
        searchTimeout = setTimeout(() => {
            searchForm.submit();
        }, 500); // Wait 500ms after user stops typing
    }

    // Function to perform immediate search (for selects)
    function performImmediateSearch() {
        showLoading();
        setTimeout(() => {
            searchForm.submit();
        }, 100); // Short delay to show loading indicator
    }

    // Add event listeners for auto search on text input
    if (searchInput) {
        searchInput.addEventListener('input', performAutoSearch);
        
        // Hide loading if user clears the field
        searchInput.addEventListener('input', () => {
            if (searchInput.value === '') {
                clearTimeout(searchTimeout);
                hideLoading();
            }
        });
    }

    // Auto search when Select2 filters change
    if (typeof $ !== 'undefined') {
        $('#status, #franchise_id, #brand_id, #business_unit_id, #market_id').on('change', function() {
            performImmediateSearch();
        });
    }
}

/**
 * Initialize Generate SKU Modal functionality
 */
function initializeGenerateSKUModal() {
    const generateModal = document.getElementById('generate-sku-modal');
    
    if (!generateModal) {
        console.warn('Generate SKU modal not found');
        return;
    }

    const generateForm = document.getElementById('generate-sku-form');
    const closeGenerateModalButton = document.getElementById('close-generate-modal-button');
    const cancelGenerateButton = document.getElementById('cancel-generate-button');
    
    const modalGenerateProductName = document.getElementById('modal-generate-product-name');
    const modalGenerateFranchise = document.getElementById('modal-generate-franchise');
    const modalGenerateBrand = document.getElementById('modal-generate-brand');
    const modalGenerateBusinessUnit = document.getElementById('modal-generate-business-unit');
    const modalGenerateMarket = document.getElementById('modal-generate-market');

    /**
     * Open the generate SKU modal with product data
     */
    const openGenerateModal = (button) => {
        if (!generateForm) return;

        generateForm.action = button.dataset.action;
        
        if (modalGenerateProductName) modalGenerateProductName.textContent = button.dataset.productName;
        if (modalGenerateFranchise) modalGenerateFranchise.textContent = button.dataset.franchise;
        if (modalGenerateBrand) modalGenerateBrand.textContent = button.dataset.brand;
        if (modalGenerateBusinessUnit) modalGenerateBusinessUnit.textContent = button.dataset.businessUnit;
        if (modalGenerateMarket) modalGenerateMarket.textContent = button.dataset.market;
        
        generateModal.classList.remove('hidden');
        setTimeout(() => {
            generateModal.classList.remove('opacity-0');
            const modalContainer = generateModal.querySelector('.modal-container');
            if (modalContainer) {
                modalContainer.classList.remove('scale-95');
            }
        }, 10);
    };

    /**
     * Close the generate SKU modal
     */
    const closeGenerateModal = () => {
        const modalContainer = generateModal.querySelector('.modal-container');
        if (modalContainer) {
            modalContainer.classList.add('scale-95');
        }
        generateModal.classList.add('opacity-0');
        setTimeout(() => generateModal.classList.add('hidden'), 300);
    };

    // Event delegation for opening the modal
    document.body.addEventListener('click', function(event) {
        const button = event.target.closest('.open-generate-sku-modal-button');
        if (button) {
            event.preventDefault();
            openGenerateModal(button);
        }
    });

    // Event listeners for closing the modal
    if (closeGenerateModalButton) {
        closeGenerateModalButton.addEventListener('click', closeGenerateModal);
    }
    
    if (cancelGenerateButton) {
        cancelGenerateButton.addEventListener('click', closeGenerateModal);
    }
    
    // Close modal when clicking outside
    generateModal.addEventListener('click', (e) => {
        if (e.target === generateModal) {
            closeGenerateModal();
        }
    });
}

// Utility functions
const ERPUtils = {
    /**
     * Show a toast notification
     */
    showToast: function(message, type = 'info') {
        // Implementation for toast notifications if needed
        console.log(`${type.toUpperCase()}: ${message}`);
    },

    /**
     * Debounce function utility
     */
    debounce: function(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    },

    /**
     * Format currency
     */
    formatCurrency: function(amount, currency = 'PEN') {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
};

// Initialize enhanced Select2 for filter dropdowns
function initializeFilterSelects() {
    if (typeof $ === 'undefined') {
        console.error('jQuery is required for Select2');
        return;
    }

    // Enhanced configuration for filter selects
    const filterSelects = [
        {
            selector: '#status',
            placeholder: 'Seleccionar estado...',
            allowClear: true
        },
        {
            selector: '#franchise_id',
            placeholder: 'Buscar franquicia...',
            allowClear: true
        },
        {
            selector: '#brand_id',
            placeholder: 'Buscar marca...',
            allowClear: true
        },
        {
            selector: '#business_unit_id',
            placeholder: 'Buscar unidad de negocio...',
            allowClear: true
        },
        {
            selector: '#market_id',
            placeholder: 'Buscar mercado...',
            allowClear: true
        }
    ];

    filterSelects.forEach(config => {
        const $select = $(config.selector);
        if ($select.length) {
            $select.select2({
                width: '100%',
                placeholder: config.placeholder,
                allowClear: config.allowClear,
                minimumResultsForSearch: 5, // Show search box only if more than 5 options
                dropdownCssClass: 'custom-dropdown',
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    loadingMore: function() {
                        return "Cargando más resultados...";
                    }
                }
            });

            // Custom styling and behavior
            $select.on('select2:open', function() {
                $('.select2-dropdown').addClass('animate-fadeIn');
            });

            $select.on('select2:close', function() {
                $('.select2-dropdown').removeClass('animate-fadeIn');
            });
        }
    });
}

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ERPUtils };
}
