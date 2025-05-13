<?php
// Include header
include_once 'includes/header.php';

// Log page view
logPageView('home');

// Get featured products
$featuredProducts = getAll('products', 'is_available = 1', 'id DESC', '4');
?>

<!-- Hero Section -->
<section class="hero text-center">
    <div class="container">
        <h1>Rent the Latest iPhones</h1>
        <p class="lead">Get the newest iPhone models for your temporary needs at affordable prices.</p>
        <a href="products.php" class="btn btn-primary btn-lg">Browse Products</a>
    </div>
</section>

<!-- Featured Products -->
<section class="mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php if ($featuredProducts): ?>
                <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-3 col-sm-6">
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
                    <p>No products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-outline-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="bg-light py-5 mb-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Us</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Latest Models</h3>
                    <p>We offer the newest iPhone models with all features and accessories included.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Affordable Prices</h3>
                    <p>Rent iPhones at competitive prices with flexible rental periods.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Fast Delivery</h3>
                    <p>Quick delivery and pickup services available for your convenience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="mb-5">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row">
            <div class="col-md-3 text-center">
                <div class="border rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <i class="fas fa-search fa-3x text-primary"></i>
                </div>
                <h4 class="mt-3">1. Choose iPhone</h4>
                <p>Browse our collection and select the iPhone model you need</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="border rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                </div>
                <h4 class="mt-3">2. Select Dates</h4>
                <p>Choose your rental start and end dates</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="border rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <i class="fas fa-credit-card fa-3x text-primary"></i>
                </div>
                <h4 class="mt-3">3. Make Payment</h4>
                <p>Secure payment through Mercado Pago</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="border rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <i class="fas fa-check-circle fa-3x text-primary"></i>
                </div>
                <h4 class="mt-3">4. Enjoy Your iPhone</h4>
                <p>Receive your iPhone and enjoy!</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="bg-light py-5 mb-5">
    <div class="container">
        <h2 class="text-center mb-5">What Our Customers Say</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial">
                    <p class="quote">"Great service! I needed an iPhone for a business trip and they delivered it right to my hotel. Very convenient."</p>
                    <p class="author">- John Doe</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">
                    <p class="quote">"Affordable prices and excellent condition iPhones. Will definitely use this service again."</p>
                    <p class="author">- Jane Smith</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial">
                    <p class="quote">"I wanted to try the latest iPhone before buying one. This service was perfect for that purpose!"</p>
                    <p class="author">- Michael Johnson</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="mb-5">
    <div class="container">
        <div class="bg-primary text-white p-5 rounded">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Ready to rent your iPhone?</h2>
                    <p class="lead mb-0">Browse our collection and find the perfect iPhone for your needs.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="products.php" class="btn btn-light btn-lg">Browse iPhones</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
