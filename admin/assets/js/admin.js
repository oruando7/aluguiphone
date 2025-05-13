/**
 * Admin JavaScript file
 * Contains client-side functionality for the iPhone Rental admin panel
 */

// Document Ready Function
$(document).ready(function() {
    // Toggle sidebar
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('active');
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Image preview for product upload
    $('#image').on('change', function() {
        previewImage(this);
    });
    
    // Handler para o campo de armazenamento customizado
    if ($('#storage').length) {
        // Setup inicial
        if ($('#storage').val() === 'outro') {
            $('#custom-storage-container').show();
        } else {
            $('#custom-storage-container').hide();
        }
        
        // Event listener para mudanças
        $('#storage').on('change', function() {
            if ($(this).val() === 'outro') {
                $('#custom-storage-container').show();
                $('#custom_storage').focus();
            } else {
                $('#custom-storage-container').hide();
            }
        });
        
        // Atualizar o valor do storage ao enviar o formulário
        $('form').on('submit', function() {
            if ($('#storage').val() === 'outro' && $('#custom_storage').val().trim() !== '') {
                $('#storage').val($('#custom_storage').val().trim());
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm deletes
    $('.delete-confirm').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Date range picker initialization if available
    if (typeof daterangepicker !== 'undefined' && $('#date_range').length) {
        $('#date_range').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            }
        });
        
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        
        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    }
    
    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var input = $($(this).attr('toggle'));
        if (input.attr('type') == 'password') {
            input.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});

// Image preview function
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

// Format currency
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

// Show loading spinner
function showSpinner() {
    $('body').append('<div class="spinner-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
}

// Hide loading spinner
function hideSpinner() {
    $('.spinner-overlay').remove();
}

// Chart.js initialization if available
if (typeof Chart !== 'undefined' && $('#ordersChart').length) {
    var ctx = document.getElementById('ordersChart').getContext('2d');
    var ordersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Orders',
                data: [12, 19, 3, 5, 2, 3, 7],
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
