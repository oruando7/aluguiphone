<?php
// Include header
include_once 'includes/header.php';

// Check if user is admin
requireAdmin();

// Filter variables
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? cleanInput($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? cleanInput($_GET['date_to']) : '';

// Build where clause
$whereClause = '';
$whereParams = [];
$whereTypes = '';

if ($userId > 0) {
    $whereClause .= !empty($whereClause) ? ' AND ' : '';
    $whereClause .= 'o.user_id = ?';
    $whereParams[] = $userId;
    $whereTypes .= 'i';
    
    // Get user details for display
    $user = getUserById($userId);
}

if (!empty($status)) {
    $whereClause .= !empty($whereClause) ? ' AND ' : '';
    $whereClause .= 'o.payment_status = ?';
    $whereParams[] = $status;
    $whereTypes .= 's';
}

if (!empty($dateFrom)) {
    $whereClause .= !empty($whereClause) ? ' AND ' : '';
    $whereClause .= 'DATE(o.created_at) >= ?';
    $whereParams[] = $dateFrom;
    $whereTypes .= 's';
}

if (!empty($dateTo)) {
    $whereClause .= !empty($whereClause) ? ' AND ' : '';
    $whereClause .= 'DATE(o.created_at) <= ?';
    $whereParams[] = $dateTo;
    $whereTypes .= 's';
}

// Prepare the query
$sql = "SELECT o.*, u.name as user_name, u.email as user_email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id";

if (!empty($whereClause)) {
    $sql .= " WHERE " . $whereClause;
}

$sql .= " ORDER BY o.created_at DESC";

// Execute the query
$orders = customQuery($sql, $whereParams, $whereTypes);

// Log page view
logPageView('admin_orders');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <?php if (isset($user)): ?>
                Orders for <?php echo $user['name']; ?>
            <?php else: ?>
                All Orders
            <?php endif; ?>
        </h1>
    </div>
    
    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Orders</h6>
        </div>
        <div class="card-body">
            <form action="orders.php" method="GET" class="row g-3">
                <?php if ($userId > 0): ?>
                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                <?php endif; ?>
                
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="orders.php<?php echo $userId > 0 ? '?user_id=' . $userId : ''; ?>" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Orders List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Rental Period</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders && count($orders) > 0): ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <?php echo $order['user_name']; ?><br>
                                    <small class="text-muted"><?php echo $order['user_email']; ?></small>
                                </td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <?php echo formatDate($order['rental_start']); ?> to <?php echo formatDate($order['rental_end']); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <a href="order-view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
