// Market Configuration JavaScript - Enhanced Simple Version
document.addEventListener('DOMContentLoaded', function() {
    console.log('Market Configuration JavaScript loaded');
    
    // Initialize market assignment
    initializeMarketAssignment();
    initializeAutoSearch();
    initializeFilterSelects();
});

function initializeMarketAssignment() {
    console.log('Initializing market assignment...');
    
    // Handle assign market button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.assign-market-btn') || e.target.closest('.change-market-btn')) {
            e.preventDefault();
            const button = e.target.closest('.assign-market-btn, .change-market-btn');
            console.log('Assign button clicked:', button);
            openModal(button);
        }
    });
    
    // Handle close modal buttons
    const closeButtons = document.querySelectorAll('#close-assign-modal, #cancel-assign');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });
    
    // Handle form submission
    const assignForm = document.getElementById('assign-market-form');
    if (assignForm) {
        assignForm.addEventListener('submit', submitForm);
    }
    
    // Initialize Select2
    setTimeout(initSelect2, 100);
}

function initSelect2() {
    console.log('Initializing Select2...');
    
    const marketSelect = $('#market-search');
    if (marketSelect.length && typeof $ !== 'undefined') {
        marketSelect.select2({
            width: '100%',
            placeholder: 'Buscar mercado...',
            allowClear: true,
            ajax: {
                url: '/market-configuration/available-markets',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term || '' };
                },
                processResults: function (data) {
                    return {
                        results: data.markets.map(market => ({
                            id: market.id,
                            text: `${market.name} (${market.code})`
                        }))
                    };
                }
            },
            minimumInputLength: 0
        });
        
        marketSelect.on('change', function() {
            const submitBtn = document.getElementById('confirm-assign');
            if (submitBtn) {
                submitBtn.disabled = !this.value;
            }
        });
    }
}

function openModal(button) {
    console.log('Opening modal for button:', button);
    
    const productId = button.dataset.productId;
    const productName = button.dataset.productName;

    const productIdInput = document.getElementById('assign-product-id');
    const productNameElement = document.getElementById('assign-product-name');
    
    if (productIdInput) productIdInput.value = productId;
    if (productNameElement) productNameElement.textContent = productName;
    
    const modal = document.getElementById('assign-market-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeModal() {
    const modal = document.getElementById('assign-market-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
    
    const form = document.getElementById('assign-market-form');
    if (form) form.reset();
    
    if (typeof $ !== 'undefined') {
        $('#market-search').val(null).trigger('change');
    }
    
    const submitBtn = document.getElementById('confirm-assign');
    if (submitBtn) submitBtn.disabled = true;
}

async function submitForm(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('confirm-assign');
    const productId = document.getElementById('assign-product-id').value;
    const marketId = document.getElementById('market-search').value;
    
    if (!productId || !marketId) {
        alert('Por favor selecciona un mercado');
        return;
    }
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Asignando...';
    }
    
    try {
        const response = await fetch(`/market-configuration/assign-market/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ market_id: marketId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            closeModal();
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error al asignar mercado: ' + error.message);
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Asignar Mercado';
        }
    }
}

function initializeAutoSearch() {
    let searchTimeout;
    const searchForm = document.querySelector('form[action*="market-configuration"]');
    const searchInput = document.getElementById('search');

    if (!searchForm) return;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    }

    const selectFilters = document.querySelectorAll('#market_status, #brand_id, #franchise_id, #business_unit_id');
    selectFilters.forEach(select => {
        select.addEventListener('change', () => {
            setTimeout(() => searchForm.submit(), 100);
        });
    });
}

// Initialize enhanced Select2 for filter dropdowns
function initializeFilterSelects() {
    if (typeof $ === 'undefined') {
        console.error('jQuery is required for Select2');
        return;
    }

    // Enhanced configuration for filter selects
    const filterSelects = [
        {
            selector: '#brand_id',
            placeholder: 'Buscar marca...',
            allowClear: true
        },
        {
            selector: '#franchise_id', 
            placeholder: 'Buscar franquicia...',
            allowClear: true
        },
        {
            selector: '#business_unit_id',
            placeholder: 'Buscar unidad de negocio...',
            allowClear: true
        },
        {
            selector: '#market_status',
            placeholder: 'Seleccionar estado...',
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
