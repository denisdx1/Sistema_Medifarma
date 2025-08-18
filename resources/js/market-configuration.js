// Market Configuration JavaScript - Simple Version
document.addEventListener('DOMContentLoaded', function() {
    console.log('Market Configuration JavaScript loaded');
    
    // Initialize market assignment
    initializeMarketAssignment();
    initializeAutoSearch();
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
        console.log('Select2 element found, initializing...');
        
        // First, let's test the API directly
        fetch('/test-markets-api')
            .then(response => response.json())
            .then(data => {
                console.log('Test API response:', data);
            })
            .catch(error => {
                console.error('Test API error:', error);
            });
        
        marketSelect.select2({
            width: '100%',
            placeholder: 'Buscar mercado...',
            allowClear: true,
            ajax: {
                url: '/market-configuration/available-markets',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    console.log('Select2 sending params:', params);
                    return { search: params.term || '' };
                },
                processResults: function (data) {
                    console.log('Select2 received data:', data);
                    if (!data.markets) {
                        console.error('No markets property in response');
                        return { results: [] };
                    }
                    return {
                        results: data.markets.map(market => ({
                            id: market.id,
                            text: `${market.name} (${market.code})`
                        }))
                    };
                },
                transport: function (params, success, failure) {
                    console.log('Select2 transport called with params:', params);
                    var $request = $.ajax(params);
                    
                    $request.then(success);
                    $request.fail(function(jqXHR, textStatus, errorThrown) {
                        console.error('Select2 AJAX error:', {
                            status: jqXHR.status,
                            statusText: jqXHR.statusText,
                            responseText: jqXHR.responseText,
                            textStatus: textStatus,
                            errorThrown: errorThrown
                        });
                        failure();
                    });
                    
                    return $request;
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
    } else {
        console.error('Select2 element not found or jQuery not available');
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
