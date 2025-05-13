<?php
// Include header
include_once 'includes/header.php';

// Check if user is logged in
requireLogin();

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get user orders
$orders = getUserOrders($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = cleanInput($_POST['name']);
    $address = cleanInput($_POST['address']);
    $phone = cleanInput($_POST['phone']);
    
    $result = updateUserProfile($userId, $name, $address, $phone);
    
    if ($result['success']) {
        showAlert($result['message'], 'success');
        // Refresh user data
        $user = getUserById($userId);
    } else {
        showAlert($result['message'], 'danger');
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        showAlert('Please fill all password fields', 'danger');
    } else if ($newPassword !== $confirmPassword) {
        showAlert('New passwords do not match', 'danger');
    } else if (strlen($newPassword) < 6) {
        showAlert('Password must be at least 6 characters', 'danger');
    } else {
        $result = changeUserPassword($userId, $currentPassword, $newPassword);
        
        if ($result['success']) {
            showAlert($result['message'], 'success');
        } else {
            showAlert($result['message'], 'danger');
        }
    }
}
?>

<section class="mb-5">
    <div class="container">
        <h1 class="mb-4">My Account</h1>
        
        <div class="row">
            <!-- Account Sidebar -->
            <div class="col-md-3">
                <div class="account-sidebar">
                    <h4>Hello, <?php echo $user['name']; ?></h4>
                    <hr>
                    <div class="account-menu list-group">
                        <a href="#" class="list-group-item list-group-item-action active" data-target="#profile">My Profile</a>
                        <a href="#" class="list-group-item list-group-item-action" data-target="#orders">My Orders</a>
                        <a href="#" class="list-group-item list-group-item-action" data-target="#password">Change Password</a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                    </div>
                </div>
            </div>
            
            <!-- Account Content -->
            <div class="col-md-9">
                <div class="account-content">
                    <!-- Profile Tab -->
                    <div id="profile" class="account-tab-content">
                        <h3 class="mb-4">My Profile</h3>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" readonly>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo $user['address']; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                            </div>
                            <input type="hidden" name="update_profile" value="1">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                    
                    <!-- Orders Tab -->
                    <div id="orders" class="account-tab-content" style="display: none;">
                        <h3 class="mb-4">My Orders</h3>
                        
                        <?php if (empty($orders)): ?>
                        <div class="alert alert-info">
                            You have no orders yet. <a href="products.php">Browse products</a> to rent an iPhone.
                        </div>
                        <?php else: ?>
                        
                        <?php foreach ($orders as $order): ?>
                        <div class="order-card card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Order #<?php echo $order['id']; ?> - <?php echo formatDate($order['created_at']); ?></span>
                                <span class="status-badge badge bg-<?php echo getStatusColor($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p><strong>Rental Period:</strong> <?php echo formatDate($order['rental_start']); ?> to <?php echo formatDate($order['rental_end']); ?></p>
                                <p><strong>Total Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php endif; ?>
                    </div>
                    
                    <!-- Change Password Tab -->
                    <div id="password" class="account-tab-content" style="display: none;">
                        <h3 class="mb-4">Change Password</h3>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" minlength="6" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                            </div>
                            <input type="hidden" name="change_password" value="1">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Helper function to get status color
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

// Include footer
include_once 'includes/footer.php';
?>
