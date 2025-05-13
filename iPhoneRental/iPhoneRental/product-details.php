<?php
// Include header
include_once 'includes/header.php';

// Ensure we have database connection
global $conn;

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    showAlert('Product not found', 'danger');
    redirect('products.php');
}

$productId = (int)$_GET['id'];

// Get product details
$product = getOne('products', $productId);

if (!$product) {
    showAlert('Product not found', 'danger');
    redirect('products.php');
}

// Get product additional images
$productImages = [];
$sql = "SELECT * FROM product_images WHERE product_id = $1 ORDER BY id ASC";
$result = pg_query_params($conn, $sql, [$productId]);
if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $productImages[] = $row;
    }
}

// Log page view
logPageView('product_details_' . $productId);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isLoggedIn()) {
        // Store intended action in session
        $_SESSION['redirect_after_login'] = 'product-details.php?id=' . $productId;
        showAlert('Please login to rent this product', 'warning');
        redirect('login.php');
    }
    
    // Get form data
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $startDate = isset($_POST['start_date']) ? cleanInput($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? cleanInput($_POST['end_date']) : '';
    
    // Validate form data
    if (empty($startDate) || empty($endDate)) {
        showAlert('Please select both start and end dates', 'danger');
    } else if (strtotime($endDate) < strtotime($startDate)) {
        showAlert('End date cannot be before start date', 'danger');
    } else if ($quantity <= 0 || $quantity > $product['stock']) {
        showAlert('Invalid quantity selected', 'danger');
    } else {
        // Add to cart
        addToCart($productId, $quantity, $startDate, $endDate);
        showAlert('Product added to cart successfully', 'success');
        redirect('checkout.php');
    }
}
?>

<section class="mb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <!-- Imagem principal e galeria -->
                <div class="product-gallery mb-4">
                    <img src="<?php echo getProductImageUrl($product['image']); ?>" class="product-image img-fluid mb-2" alt="<?php echo $product['name']; ?>" id="main-product-image">
                    
                    <?php if (!empty($productImages)): ?>
                    <div class="row product-thumbnails">
                        <div class="col-2 mb-2">
                            <img src="<?php echo getProductImageUrl($product['image']); ?>" class="img-thumbnail active" alt="Main" onclick="switchMainImage(this)">
                        </div>
                        <?php foreach ($productImages as $img): ?>
                        <div class="col-2 mb-2">
                            <img src="<?php echo getProductImageUrl($img['image_path']); ?>" class="img-thumbnail" alt="Additional" onclick="switchMainImage(this)">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- O que vem na caixa -->
                <div class="what-in-box p-4 bg-light rounded mb-4">
                    <h3 class="h5 mb-3">O que vem na caixa:</h3>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="item-box p-2 mb-2 border rounded">
                                <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                                <div>Smartphone</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="item-box p-2 mb-2 border rounded">
                                <i class="fas fa-plug fa-2x mb-2"></i>
                                <div>Carregador</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="item-box p-2 mb-2 border rounded">
                                <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                <div>Película</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="item-box p-2 mb-2 border rounded">
                                <i class="fas fa-mobile fa-2x mb-2"></i>
                                <div>Capinha</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Próximos passos -->
                <div class="next-steps p-4 bg-light rounded">
                    <h3 class="h5 mb-3">Próximos passos:</h3>
                    <div class="steps">
                        <div class="step d-flex mb-3">
                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">1</div>
                            <div class="step-content">
                                <strong>Finalize seu pedido</strong>
                                <p class="mb-0 small">Selecione as datas e adicione o produto ao carrinho</p>
                            </div>
                        </div>
                        <div class="step d-flex mb-3">
                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">2</div>
                            <div class="step-content">
                                <strong>Escolha o meio de pagamento</strong>
                                <p class="mb-0 small">Pague com segurança utilizando Mercado Pago</p>
                            </div>
                        </div>
                        <div class="step d-flex mb-3">
                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">3</div>
                            <div class="step-content">
                                <strong>Aguarde a aprovação</strong>
                                <p class="mb-0 small">Seu pedido será processado e confirmado por email</p>
                            </div>
                        </div>
                        <div class="step d-flex">
                            <div class="step-number rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">4</div>
                            <div class="step-content">
                                <strong>Acompanhe sua entrega</strong>
                                <p class="mb-0 small">Receba seu iPhone no conforto da sua casa</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 product-info">
                <h1><?php echo $product['name']; ?></h1>
                
                <div class="product-specs mb-3">
                    <span class="badge bg-info me-2"><?php echo $product['storage']; ?></span>
                    <span class="badge bg-secondary"><?php echo $product['rental_period']; ?></span>
                </div>
                
                <p class="price" id="product-price" data-price="<?php echo $product['price']; ?>"><?php echo formatPrice($product['price']); ?> / day</p>
                <div class="mb-4">
                    <p><?php echo nl2br($product['description']); ?></p>
                </div>
                
                <?php if ($product['stock'] > 0): ?>
                <div class="rental-form">
                    <h3>Alugar este iPhone</h3>
                    <form method="POST" id="rental-form">
                        <div class="mb-3">
                            <label class="form-label">Período de Aluguel</label>
                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle me-2"></i> Plano de <?php echo $product['rental_period']; ?></strong>
                                <p class="mb-0 small">Este iPhone está disponível para o período de <?php echo $product['rental_period']; ?></p>
                            </div>
                            <input type="hidden" id="rental_period" name="rental_period" value="<?php echo $product['rental_period']; ?>">
                            
                            <!-- Valores ocultos para armazenar as datas de início e fim para compatibilidade -->
                            <?php
                            $startDate = date('Y-m-d');
                            $endDate = date('Y-m-d', strtotime('+' . (strpos($product['rental_period'], '12') !== false ? '12 months' : '24 months')));
                            ?>
                            <input type="hidden" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                            <input type="hidden" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantidade</label>
                            <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary quantity-decrease">-</button>
                                <input type="number" class="form-control text-center quantity-input" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" data-max="<?php echo $product['stock']; ?>" required readonly>
                                <button type="button" class="btn btn-outline-secondary quantity-increase">+</button>
                            </div>
                            <small class="text-muted"><?php echo $product['stock']; ?> disponíveis</small>
                        </div>
                        
                        <div class="rental-summary mb-3 p-3 bg-light rounded">
                            <h5>Resumo do Aluguel</h5>
                            <p>Período: <strong><?php echo $product['rental_period']; ?></strong></p>
                            <p>Preço Total: <strong class="text-primary"><?php echo formatPrice($product['price']); ?></strong></p>
                            <input type="hidden" id="total-price" name="total_price" value="<?php echo $product['price']; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Adicionar ao Carrinho</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <strong>Fora de Estoque</strong> - Este produto não está disponível no momento.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="mb-4">You May Also Like</h2>
        <div class="row">
            <?php
            // Get related products (excluding current product)
            $relatedProducts = getAll('products', "id != $productId AND is_available = 1", 'RAND()', '4');
            
            if ($relatedProducts):
                foreach ($relatedProducts as $relatedProduct):
            ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="product-card card h-100">
                    <img src="<?php echo getProductImageUrl($relatedProduct['image']); ?>" class="card-img-top" alt="<?php echo $relatedProduct['name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $relatedProduct['name']; ?></h5>
                        <p class="price"><?php echo formatPrice($relatedProduct['price']); ?> / day</p>
                        <a href="product-details.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            else:
            ?>
            <div class="col-12 text-center">
                <p>No related products available at the moment.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
