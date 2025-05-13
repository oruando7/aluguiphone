<?php
// Include header
include_once 'includes/header.php';

// Check if user is admin
requireAdmin();

// Handle product deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    
    // Check if product exists
    $product = getOne('products', $productId);
    
    if ($product) {
        // Delete product
        if (delete('products', $productId)) {
            showAlert('Product deleted successfully', 'success');
        } else {
            showAlert('Error deleting product', 'danger');
        }
    } else {
        showAlert('Product not found', 'danger');
    }
    
    // Redirect to avoid resubmission
    redirect('products.php');
}

// Get products
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$whereClause = '';

if (!empty($search)) {
    $whereClause = "name LIKE '%$search%' OR description LIKE '%$search%'";
}

$products = getAll('products', $whereClause, 'id DESC');

// Log page view
logPageView('admin_products');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
        <a href="product-edit.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Add New Product
        </a>
    </div>
    
    <!-- Search Box -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="products.php" method="GET" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo $search; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <a href="products.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Products</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products && count($products) > 0): ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <img src="<?php echo getProductImageUrl($product['image']); ?>" alt="<?php echo $product['name']; ?>" class="product-thumbnail">
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <?php if ($product['is_available']): ?>
                                    <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Not Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this product?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
