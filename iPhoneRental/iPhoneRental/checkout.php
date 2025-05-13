<?php
// Include header
include_once 'includes/header.php';
include_once 'includes/mercadopago.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    showAlert('Your cart is empty', 'warning');
    redirect('products.php');
}

// Get cart items with product details
$cart = getCartItems();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Store intended action in session
        $_SESSION['redirect_after_login'] = 'checkout.php';
        showAlert('Please login to complete your order', 'warning');
        redirect('login.php');
    }
    
    // Create order
    $userId = $_SESSION['user_id'];
    $orderId = createOrder($userId, $cart);
    
    if ($orderId) {
        // Initialize MercadoPago if configured
        if (isMercadoPagoConfigured()) {
            // Get user data for customer information
            $user = getUserById($userId);
            
            // Create payment preference
            $paymentResult = createPaymentPreference($cart['items'], $orderId, $user);
            
            if ($paymentResult['success']) {
                // Store preference in session
                $_SESSION['payment_preference'] = $paymentResult['preference'];
                $_SESSION['public_key'] = $paymentResult['public_key'];
                
                // Redirect to payment page
                redirect('confirmation.php?order_id=' . $orderId);
            } else {
                showAlert('Error creating payment: ' . $paymentResult['message'], 'danger');
            }
        } else {
            // If MercadoPago is not configured, just show confirmation
            clearCart();
            showAlert('Your order has been placed successfully', 'success');
            redirect('confirmation.php?order_id=' . $orderId . '&status=pending');
        }
    } else {
        showAlert('Error creating order. Please try again.', 'danger');
    }
}

// Log page view
logPageView('checkout');
?>

<section class="mb-5">
    <div class="container">
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Your Cart</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($cart['items'])): ?>
                        <div class="alert alert-info">
                            Your cart is empty. <a href="products.php">Browse products</a> to add items to your cart.
                        </div>
                        <?php else: ?>
                        
                        <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-item row align-items-center mb-3">
                            <div class="col-md-2 col-4">
                                <img src="<?php echo getProductImageUrl($item['image']); ?>" class="img-fluid" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-4 col-8">
                                <h5><?php echo $item['name']; ?></h5>
                                <p class="text-muted">
                                    <?php echo formatDate($item['start_date']); ?> to <?php echo formatDate($item['end_date']); ?>
                                    <br>
                                    <small><?php echo $item['duration']; ?> days</small>
                                </p>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary quantity-decrease">-</button>
                                    <input type="number" class="form-control text-center quantity-input cart-quantity" 
                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                           data-product-id="<?php echo $item['product_id']; ?>" readonly>
                                    <button type="button" class="btn btn-outline-secondary quantity-increase">+</button>
                                </div>
                            </div>
                            <div class="col-md-2 col-4">
                                <div class="text-end">
                                    <p class="fw-bold"><?php echo formatPrice($item['subtotal']); ?></p>
                                    <small class="text-muted"><?php echo formatPrice($item['rental_price']); ?> × <?php echo $item['quantity']; ?></small>
                                </div>
                            </div>
                            <div class="col-md-1 col-2">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-from-cart" 
                                        data-product-id="<?php echo $item['product_id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($cart['total_amount']); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold"><?php echo formatPrice($cart['total_amount']); ?></span>
                        </div>
                        
                        <?php if (!empty($cart['items'])): ?>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="checkout-btn">
                                    Place Order
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Methods</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-methods">
                            <img src="https://www.mercadopago.com/org-img/Manual/ManualMP/imgs/isologoHorizontal.png" alt="Mercado Pago">
                        </div>
                        <hr>
                        <p class="text-muted small">Your payment information is processed securely. We do not store your credit card details.</p>
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
