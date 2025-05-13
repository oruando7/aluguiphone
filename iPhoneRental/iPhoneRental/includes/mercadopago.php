<?php
/**
 * Mercado Pago Integration
 * Handles payment processing with Mercado Pago
 */

// Include Mercado Pago SDK
// Note: The SDK would normally be installed via Composer
// This is a simplified implementation

// Initialize Mercado Pago
function initMercadoPago() {
    // Get Mercado Pago credentials from database
    $accessToken = getSetting('mercadopago_access_token');
    
    if (empty($accessToken)) {
        return false;
    }
    
    // In a real implementation, you would initialize the SDK here
    // For example: \MercadoPago\SDK::setAccessToken($accessToken);
    
    return true;
}

// Create payment preference
function createPaymentPreference($items, $orderId, $customer) {
    // Get Mercado Pago credentials
    $accessToken = getSetting('mercadopago_access_token');
    $publicKey = getSetting('mercadopago_public_key');
    
    if (empty($accessToken) || empty($publicKey)) {
        return [
            'success' => false,
            'message' => 'Mercado Pago credentials not configured'
        ];
    }
    
    // Format items for Mercado Pago
    $mpItems = [];
    foreach ($items as $item) {
        $mpItems[] = [
            'id' => $item['product_id'],
            'title' => $item['name'],
            'quantity' => $item['quantity'],
            'currency_id' => 'USD',
            'unit_price' => $item['rental_price']
        ];
    }
    
    // In a real implementation, you would create a preference using the SDK
    // This is a simplified version
    
    // Prepare back URLs
    $baseUrl = SITE_URL;
    $backUrls = [
        'success' => $baseUrl . '/confirmation.php?status=success&order_id=' . $orderId,
        'failure' => $baseUrl . '/confirmation.php?status=failure&order_id=' . $orderId,
        'pending' => $baseUrl . '/confirmation.php?status=pending&order_id=' . $orderId
    ];
    
    // Prepare webhook URL for IPN (Instant Payment Notification)
    $notificationUrl = $baseUrl . '/webhook.php';
    
    // Create a mock preference (simulate SDK response)
    $preference = [
        'items' => $mpItems,
        'back_urls' => $backUrls,
        'notification_url' => $notificationUrl,
        'external_reference' => $orderId,
        'payer' => [
            'name' => $customer['name'],
            'email' => $customer['email']
        ]
    ];
    
    // In a real implementation, you would save the preference and get its ID
    
    return [
        'success' => true,
        'preference' => $preference,
        'public_key' => $publicKey
    ];
}

// Process webhook notification
function processWebhook($data) {
    global $conn;
    
    // Get Mercado Pago credentials
    $accessToken = getSetting('mercadopago_access_token');
    
    if (empty($accessToken)) {
        return [
            'success' => false,
            'message' => 'Mercado Pago credentials not configured'
        ];
    }
    
    // In a real implementation, you would validate the webhook data
    // and retrieve the payment information using the SDK
    
    // For this implementation, we'll use the data directly
    if (isset($data['type']) && $data['type'] == 'payment') {
        $paymentId = $data['data']['id'];
        
        // Mock getting payment details
        // In a real implementation, you would get this from the API
        $orderId = $data['external_reference'];
        $status = $data['status'] ?? 'pending';
        
        // Map Mercado Pago status to our status
        $statusMap = [
            'approved' => 'approved',
            'pending' => 'pending',
            'in_process' => 'pending',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'charged_back' => 'charged_back'
        ];
        
        $orderStatus = $statusMap[$status] ?? 'pending';
        
        // Update order status
        $sql = "UPDATE orders SET payment_id = ?, payment_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $paymentId, $orderStatus, $orderId);
        
        if ($stmt->execute()) {
            // If payment is approved, send confirmation email
            if ($orderStatus == 'approved') {
                $order = getOrderDetails($orderId);
                sendOrderConfirmation($order);
            }
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to update order status'
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Invalid webhook data'
    ];
}

// Check if Mercado Pago is configured
function isMercadoPagoConfigured() {
    $accessToken = getSetting('mercadopago_access_token');
    $publicKey = getSetting('mercadopago_public_key');
    
    return !empty($accessToken) && !empty($publicKey);
}

// Save Mercado Pago configuration
function saveMercadoPagoConfig($publicKey, $accessToken) {
    updateSetting('mercadopago_public_key', $publicKey);
    updateSetting('mercadopago_access_token', $accessToken);
    
    return true;
}

// Helper function to update a setting
function updateSetting($name, $value) {
    global $conn;
    
    $sql = "UPDATE settings SET value = ? WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $value, $name);
    
    return $stmt->execute();
}
