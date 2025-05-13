<?php
// Include header
include_once 'includes/header.php';

// Check if user is logged in
requireLogin();

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    showAlert('Invalid order', 'danger');
    redirect('account.php');
}

$orderId = (int)$_GET['id'];
$order = getOrderDetails($orderId);

// Check if order exists and belongs to current user
if (!$order || ($order['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
    showAlert('Order not found', 'danger');
    redirect('account.php');
}

// Log page view
logPageView('order_details_' . $orderId);

// Get status color
function getStatusColor($status) {
    switch ($status) {
        case 'approved':
            return 'success';
        case 'pending':
            return 'warning';
        case 'failed':
            return 'danger';
        case 'cancelled':
            return 'secondary';
        default:
            return 'info';
    }
}
?>

<section class="mb-5">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <h1>Order #<?php echo $orderId; ?></h1>
                    <a href="account.php" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Account</a>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-2 col-4">
                                <img src="<?php echo getProductImageUrl($item['image']); ?>" class="img-fluid" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="col-md-6 col-8">
                                <h5><?php echo $item['name']; ?></h5>
                            </div>
                            <div class="col-md-2 col-6">
                                <p class="text-muted">
                                    <strong>Quantity:</strong> <?php echo $item['quantity']; ?>
                                </p>
                            </div>
                            <div class="col-md-2 col-6">
                                <p class="fw-bold"><?php echo formatPrice($item['price']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6">Order Date:</div>
                            <div class="col-6 text-end"><?php echo formatDate($order['created_at']); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">Rental Period:</div>
                            <div class="col-6 text-end">
                                <?php echo formatDate($order['rental_start']); ?> to <?php echo formatDate($order['rental_end']); ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6">Status:</div>
                            <div class="col-6 text-end">
                                <span class="badge bg-<?php echo getStatusColor($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row fw-bold">
                            <div class="col-6">Total:</div>
                            <div class="col-6 text-end"><?php echo formatPrice($order['total_amount']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo $order['user']['name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $order['user']['email']; ?></p>
                        <?php if (!empty($order['user']['phone'])): ?>
                        <p><strong>Phone:</strong> <?php echo $order['user']['phone']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order['user']['address'])): ?>
                        <p><strong>Address:</strong> <?php echo nl2br($order['user']['address']); ?></p>
                        <?php endif; ?>
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
