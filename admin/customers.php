<?php
// Include header
include_once 'includes/header.php';

// Check if user is admin
requireAdmin();

// Get all users (excluding admins)
$users = getAll('users', 'is_admin = 0', 'created_at DESC');

// Log page view
logPageView('admin_customers');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customers</h1>
    </div>
    
    <!-- Customers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Customers</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                            <?php 
                                // Get user orders
                                $orderCount = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = {$user['id']}")->fetch_assoc()['count'];
                            ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo !empty($user['phone']) ? $user['phone'] : '-'; ?></td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($orderCount > 0): ?>
                                    <a href="orders.php?user_id=<?php echo $user['id']; ?>"><?php echo $orderCount; ?> order(s)</a>
                                    <?php else: ?>
                                    0 orders
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info view-customer" 
                                            data-bs-toggle="modal" data-bs-target="#customerDetailsModal"
                                            data-id="<?php echo $user['id']; ?>"
                                            data-name="<?php echo $user['name']; ?>"
                                            data-email="<?php echo $user['email']; ?>"
                                            data-phone="<?php echo $user['phone']; ?>"
                                            data-address="<?php echo $user['address']; ?>"
                                            data-created="<?php echo date('d M Y', strtotime($user['created_at'])); ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No customers found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer Details Modal -->
<div class="modal fade" id="customerDetailsModal" tabindex="-1" aria-labelledby="customerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerDetailsModalLabel">Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="customer-details">
                    <p><strong>Name:</strong> <span id="modal-name"></span></p>
                    <p><strong>Email:</strong> <span id="modal-email"></span></p>
                    <p><strong>Phone:</strong> <span id="modal-phone"></span></p>
                    <p><strong>Address:</strong> <span id="modal-address"></span></p>
                    <p><strong>Registered:</strong> <span id="modal-created"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="modal-orders-link">View Orders</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Handle customer details modal
    document.addEventListener('DOMContentLoaded', function() {
        const customerDetailsModal = document.getElementById('customerDetailsModal');
        
        if (customerDetailsModal) {
            customerDetailsModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const email = button.getAttribute('data-email');
                const phone = button.getAttribute('data-phone') || 'Not provided';
                const address = button.getAttribute('data-address') || 'Not provided';
                const created = button.getAttribute('data-created');
                
                document.getElementById('modal-name').textContent = name;
                document.getElementById('modal-email').textContent = email;
                document.getElementById('modal-phone').textContent = phone;
                document.getElementById('modal-address').textContent = address;
                document.getElementById('modal-created').textContent = created;
                
                document.getElementById('modal-orders-link').href = 'orders.php?user_id=' + id;
            });
        }
    });
</script>

<?php
// Include footer
include_once 'includes/footer.php';
?>
