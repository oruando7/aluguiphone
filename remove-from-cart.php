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

// Get product ID
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate input
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// Remove from cart
removeFromCart($productId);

// Return success response
echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
