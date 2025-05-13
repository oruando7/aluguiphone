<?php
// Include configuration
require_once 'config/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is AJAX
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get product ID and quantity
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate input
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

// Check if product exists
$product = getOne('products', $productId);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if quantity is available
if ($quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit;
}

// Update cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$updated = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['product_id'] == $productId) {
        $_SESSION['cart'][$key]['quantity'] = $quantity;
        $updated = true;
        break;
    }
}

if (!$updated) {
    echo json_encode(['success' => false, 'message' => 'Product not found in cart']);
    exit;
}

// Return success response
echo json_encode([
    'success' => true, 
    'message' => 'Cart updated successfully',
    'quantity' => $quantity
]);
