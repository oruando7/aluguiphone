<?php
// Include header
include_once 'includes/header.php';

// Check if user is admin
requireAdmin();

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    showAlert('Invalid order', 'danger');
    redirect('orders.php');
}

$orderId = (int)$_GET['id'];
$order = getOrderDetails($orderId);

// Check if order exists
if (!$order) {
    showAlert('Order not found', 'danger');
    redirect('orders.php');
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = cleanInput($_POST['status']);
    
    if (updateOrderPayment($orderId, $order['payment_id'], $status)) {
        // If payment is approved and was not approved before, send confirmation email
        if ($status == 'approved' && $order['payment_status'] != 'approved') {
            sendOrderConfirmation($order);
        }
        
        showAlert('Order status updated successfully', 'success');
        redirect('order-view.php?id=' . $orderId);
    } else {
        showAlert('Error updating order status', 'danger');
    }
}

// Refresh order details after update
$order = getOrderDetails($orderId);

// Log page view
logPageView('admin_order_view_' . $orderId);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Order #<?php echo $orderId; ?></h1>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Order Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo getProductImageUrl($item['image']); ?>" alt="<?php echo $item['name']; ?>" class="product-thumbnail me-3">
                                            <div>
                                                <strong><?php echo $item['name']; ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th><?php echo formatPrice($order['total_amount']); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Order Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rental Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Rental Period:</div>
                        <div class="col-md-8">
                            <?php echo formatDate($order['rental_start']); ?> to <?php echo formatDate($order['rental_end']); ?>
                            <br>
                            <small class="text-muted">
                                Duration: <?php echo getRentalDuration($order['rental_start'], $order['rental_end']); ?> days
                            </small>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['payment_id'])): ?>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Payment ID:</div>
                        <div class="col-md-8"><?php echo $order['payment_id']; ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Order Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 fw-bold">Order Date:</div>
                        <div class="col-6"><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6 fw-bold">Status:</div>
                        <div class="col-6">
                            <span class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Status Update Form -->
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $orderId); ?>">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Update Status:</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $order['payment_status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="cancelled" <?php echo $order['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <input type="hidden" name="update_status" value="1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
                    </form>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Name:</div>
                        <div class="col-8"><?php echo $order['user']['name']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Email:</div>
                        <div class="col-8"><?php echo $order['user']['email']; ?></div>
                    </div>
                    <?php if (!empty($order['user']['phone'])): ?>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Phone:</div>
                        <div class="col-8"><?php echo $order['user']['phone']; ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['user']['address'])): ?>
                    <div class="row mb-3">
                        <div class="col-4 fw-bold">Address:</div>
                        <div class="col-8"><?php echo nl2br($order['user']['address']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <a href="customers.php" class="btn btn-outline-primary btn-sm w-100 mt-2">View All Customers</a>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="orders.php?user_id=<?php echo $order['user_id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-shopping-cart me-2"></i>View Customer Orders
                        </a>
                        <button class="btn btn-success btn-sm" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for status badge
function getStatusBadgeClass($status) {
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

// Include footer
include_once 'includes/footer.php';
?>
