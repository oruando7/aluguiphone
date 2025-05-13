<?php
// Include header
include_once 'includes/header.php';
include_once 'includes/mercadopago.php';

// Check if order ID is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    showAlert('Invalid order', 'danger');
    redirect('index.php');
}

$orderId = (int)$_GET['order_id'];
$order = getOrderDetails($orderId);

// Check if order exists and belongs to current user
if (!$order || ($order['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
    showAlert('Order not found', 'danger');
    redirect('index.php');
}

// Check payment status
$status = isset($_GET['status']) ? $_GET['status'] : $order['payment_status'];

// Update order status if provided in URL
if (isset($_GET['status']) && $status != $order['payment_status']) {
    updateOrderPayment($orderId, $order['payment_id'], $status);
    $order['payment_status'] = $status;
    
    // If payment is approved, send confirmation email
    if ($status == 'approved') {
        sendOrderConfirmation($order);
    }
}

// Clear cart after confirmation
clearCart();

// Log page view
logPageView('confirmation_' . $orderId);

// Get status color and message
function getStatusColorAndMessage($status) {
    switch ($status) {
        case 'approved':
            return [
                'color' => 'success',
                'icon' => 'check-circle',
                'message' => 'Your payment has been approved and your order is confirmed.'
            ];
        case 'pending':
            return [
                'color' => 'warning',
                'icon' => 'clock',
                'message' => 'Your payment is pending. We will process your order once the payment is confirmed.'
            ];
        case 'failed':
            return [
                'color' => 'danger',
                'icon' => 'exclamation-circle',
                'message' => 'Your payment was not successful. Please try again or contact customer support.'
            ];
        default:
            return [
                'color' => 'info',
                'icon' => 'info-circle',
                'message' => 'Your order has been received. We will process it shortly.'
            ];
    }
}

$statusInfo = getStatusColorAndMessage($status);
?>

<section class="mb-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-<?php echo $statusInfo['icon']; ?> fa-5x text-<?php echo $statusInfo['color']; ?>"></i>
                        </div>
                        <h2 class="mb-3">Thank You for Your Order!</h2>
                        <p class="lead mb-4">Order #<?php echo $orderId; ?></p>
                        <div class="alert alert-<?php echo $statusInfo['color']; ?> mb-4">
                            <?php echo $statusInfo['message']; ?>
                        </div>
                        
                        <?php if (isMercadoPagoConfigured() && $status != 'approved' && isset($_SESSION['payment_preference']) && isset($_SESSION['public_key'])): ?>
                            <!-- Mercado Pago Payment Button -->
                            <div id="payment-form" data-public-key="<?php echo $_SESSION['public_key']; ?>" 
                                 data-preference-id="<?php echo $_SESSION['payment_preference']['id'] ?? ''; ?>">
                                <div class="checkout-btn mb-4"></div>
                            </div>
                            
                            <script src="https://sdk.mercadopago.com/js/v2"></script>
                            <script>
                                // Initialize the MercadoPago checkout
                                const mp = new MercadoPago('<?php echo $_SESSION['public_key']; ?>', {
                                    locale: 'en-US'
                                });
                                
                                mp.checkout({
                                    preference: {
                                        id: '<?php echo $_SESSION['payment_preference']['id'] ?? ''; ?>'
                                    },
                                    render: {
                                        container: '.checkout-btn',
                                        label: 'Pay Now',
                                    }
                                });
                            </script>
                        <?php endif; ?>
                        
                        <div class="order-details mt-5">
                            <h3 class="mb-4">Order Details</h3>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end text-start"><strong>Order Date:</strong></div>
                                <div class="col-md-6 text-md-start text-start"><?php echo formatDate($order['created_at']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end text-start"><strong>Rental Period:</strong></div>
                                <div class="col-md-6 text-md-start text-start">
                                    <?php echo formatDate($order['rental_start']); ?> to <?php echo formatDate($order['rental_end']); ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end text-start"><strong>Payment Status:</strong></div>
                                <div class="col-md-6 text-md-start text-start">
                                    <span class="badge bg-<?php echo $statusInfo['color']; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end text-start"><strong>Total Amount:</strong></div>
                                <div class="col-md-6 text-md-start text-start"><?php echo formatPrice($order['total_amount']); ?></div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-center gap-3">
                            <a href="order-details.php?id=<?php echo $orderId; ?>" class="btn btn-outline-primary">View Order Details</a>
                            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
