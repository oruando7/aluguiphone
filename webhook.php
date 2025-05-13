<?php
/**
 * Webhook Handler for Mercado Pago
 * Receives payment notifications from Mercado Pago
 */

// Include configuration
require_once 'config/config.php';
require_once 'includes/mercadopago.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get the JSON data from the request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if data was decoded correctly
if (json_last_error() !== JSON_ERROR_NONE) {
    // Log error
    error_log('Webhook: Invalid JSON data received');
    
    // Return error response
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

// Process webhook
$result = processWebhook($data);

if ($result['success']) {
    // Log success
    error_log('Webhook: ' . $result['message']);
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => $result['message']]);
} else {
    // Log error
    error_log('Webhook: ' . $result['message']);
    
    // Return error response
    http_response_code(500);
    echo json_encode(['error' => $result['message']]);
}

