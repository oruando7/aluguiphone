<?php
// Include header
include_once 'includes/header.php';

// Log page view
logPageView('products');

// Make sure we have the database connection
global $conn;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 8;
$offset = ($page - 1) * $itemsPerPage;

// Search functionality
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$whereClause = 'is_available = TRUE';

if (!empty($search)) {
    $whereClause .= " AND (name ILIKE '%$search%' OR description ILIKE '%$search%')";
}

// Get total product count for pagination
$result = pg_query($conn, "SELECT COUNT(*) as count FROM products WHERE $whereClause");
$row = pg_fetch_assoc($result);
$totalProducts = $row['count'];
$totalPages = ceil($totalProducts / $itemsPerPage);

// Get products with pagination
$products = getAll('products', $whereClause, 'id DESC', "$itemsPerPage OFFSET $offset");
?>

<section class="mb-5">
    <div class="container">
        <h1 class="mb-4">iPhone Rental Products</h1>
        
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form action="products.php" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="row">
            <?php if ($products): ?>
                <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card card h-100">
                        <img src="<?php echo getProductImageUrl($product['image']); ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text"><?php echo substr($product['description'], 0, 100); ?>...</p>
                            <p class="price"><?php echo formatPrice($product['price']); ?> / day</p>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No products found. Please try a different search term.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center mt-4">
                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
