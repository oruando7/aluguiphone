/**
 * Main JavaScript file
 * Contains client-side functionality for the iPhone Rental website
 */

// Função para trocar a imagem principal no detalhe do produto
function switchMainImage(thumbnail) {
    const mainImage = document.getElementById('main-product-image');
    if (mainImage) {
        mainImage.src = thumbnail.src;
    }
    
    // Remover classe active de todas as miniaturas
    const thumbnails = document.querySelectorAll('.product-thumbnails img');
    thumbnails.forEach(thumb => {
        thumb.classList.remove('active');
    });
    
    // Adicionar classe active na miniatura clicada
    thumbnail.classList.add('active');
}

// Document Ready Function
$(document).ready(function() {
    // Initialize date pickers
    initDatePickers();
    
    // Initialize product quantity controls
    initQuantityControls();
    
    // Initialize Mercado Pago if available
    initMercadoPago();
    
    // Handle rental form submission
    initRentalForm();
    
    // Cart page functionality
    initCartFunctions();
    
    // Account page tabs
    initAccountTabs();
});

// Initialize Date Pickers
function initDatePickers() {
    if ($('.datepicker').length) {
        // Simple date input enhancement
        $('.datepicker').each(function() {
            // Set minimum date to today
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var yyyy = today.getFullYear();
            today = yyyy + '-' + mm + '-' + dd;
            
            $(this).attr('min', today);
            
            // When start date changes, update end date min value
            if ($(this).hasClass('start-date')) {
                $(this).on('change', function() {
                    var startDate = $(this).val();
                    var endDateInput = $('.end-date');
                    endDateInput.attr('min', startDate);
                    
                    // If end date is before start date, update it
                    if (endDateInput.val() < startDate) {
                        endDateInput.val(startDate);
                    }
                    
                    // Update rental price if both dates are set
                    updateRentalPrice();
                });
            }
            
            // When end date changes, update rental price
            if ($(this).hasClass('end-date')) {
                $(this).on('change', function() {
                    updateRentalPrice();
                });
            }
        });
    }
}

// Update rental price based on selected dates
function updateRentalPrice() {
    var startDateInput = $('.start-date');
    var endDateInput = $('.end-date');
    
    if (startDateInput.length && endDateInput.length && startDateInput.val() && endDateInput.val()) {
        var startDate = new Date(startDateInput.val());
        var endDate = new Date(endDateInput.val());
        
        // Calculate days difference (include both start and end day)
        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.floor(timeDiff / (1000 * 3600 * 24)) + 1;
        
        if (daysDiff < 1) {
            daysDiff = 1;
        }
        
        // Get product price
        var basePrice = parseFloat($('#product-price').data('price'));
        var totalPrice = basePrice * daysDiff;
        
        // Update price display
        $('#rental-days').text(daysDiff);
        $('#rental-price').text('$' + totalPrice.toFixed(2));
        $('#total-price').val(totalPrice.toFixed(2));
    }
}

// Initialize quantity controls
function initQuantityControls() {
    // Decrease quantity
    $('.quantity-decrease').on('click', function() {
        var input = $(this).parent().find('.quantity-input');
        var currentValue = parseInt(input.val());
        
        if (currentValue > 1) {
            input.val(currentValue - 1);
            input.trigger('change');
        }
    });
    
    // Increase quantity
    $('.quantity-increase').on('click', function() {
        var input = $(this).parent().find('.quantity-input');
        var currentValue = parseInt(input.val());
        var maxValue = parseInt(input.data('max') || 10);
        
        if (currentValue < maxValue) {
            input.val(currentValue + 1);
            input.trigger('change');
        }
    });
    
    // Update cart item
    $('.quantity-input').on('change', function() {
        if ($(this).hasClass('cart-quantity')) {
            var productId = $(this).data('product-id');
            var quantity = $(this).val();
            updateCartItem(productId, quantity);
        }
    });
}

// Update cart item quantity
function updateCartItem(productId, quantity) {
    $.ajax({
        url: 'update-cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            // Reload page to update cart
            location.reload();
        },
        error: function(xhr, status, error) {
            console.error("Error updating cart: " + error);
        }
    });
}

// Initialize Mercado Pago SDK
function initMercadoPago() {
    if (typeof MercadoPago !== 'undefined' && $('#payment-form').length) {
        const publicKey = $('#payment-form').data('public-key');
        
        if (publicKey) {
            const mp = new MercadoPago(publicKey);
            
            // Initialize the checkout
            mp.checkout({
                preference: {
                    id: $('#payment-form').data('preference-id')
                },
                render: {
                    container: '.checkout-btn',
                    label: 'Pay Now'
                }
            });
        }
    }
}

// Initialize rental form
function initRentalForm() {
    $('#rental-form').on('submit', function(e) {
        var startDate = $('.start-date').val();
        var endDate = $('.end-date').val();
        
        if (!startDate || !endDate) {
            e.preventDefault();
            alert('Please select both start and end dates');
            return false;
        }
        
        var start = new Date(startDate);
        var end = new Date(endDate);
        
        if (end < start) {
            e.preventDefault();
            alert('End date cannot be before start date');
            return false;
        }
        
        // Show loading spinner
        showSpinner();
        
        return true;
    });
}

// Initialize cart functions
function initCartFunctions() {
    // Remove item from cart
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();
        
        var productId = $(this).data('product-id');
        
        $.ajax({
            url: 'remove-from-cart.php',
            type: 'POST',
            data: {
                product_id: productId
            },
            success: function(response) {
                // Reload page to update cart
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error("Error removing item: " + error);
            }
        });
    });
    
    // Proceed to checkout
    $('#checkout-btn').on('click', function() {
        // Check if cart is empty
        if ($('.cart-item').length === 0) {
            alert('Your cart is empty');
            return false;
        }
        
        // Show loading spinner
        showSpinner();
        
        return true;
    });
}

// Initialize account tabs
function initAccountTabs() {
    if ($('.account-menu').length) {
        $('.account-menu .list-group-item').on('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            $('.account-menu .list-group-item').removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Hide all tab content
            $('.account-tab-content').hide();
            
            // Show content for selected tab
            const targetTab = $(this).data('target');
            $(targetTab).show();
        });
    }
}

// Show loading spinner
function showSpinner() {
    $('body').append('<div class="spinner-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
}

// Hide loading spinner
function hideSpinner() {
    $('.spinner-overlay').remove();
}

// Image preview for product upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            $('#image-preview').attr('src', e.target.result);
            $('#image-preview-container').show();
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
