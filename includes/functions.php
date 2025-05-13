<?php
/**
 * Helper Functions
 * Contains utility functions used throughout the application
 */

// Clean input data
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Get rental duration in days
function getRentalDuration($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days + 1; // Include both start and end day
}

// Calculate rental price
function calculateRentalPrice($productPrice, $duration) {
    return $productPrice * $duration;
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && ($_SESSION['is_admin'] == 1 || $_SESSION['is_admin'] == 't' || $_SESSION['is_admin'] === true || $_SESSION['is_admin'] === 'true');
}

// Redirect to URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Show alert message
function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Display alert message
function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alertType = $_SESSION['alert']['type'];
        $alertMessage = $_SESSION['alert']['message'];
        
        $alertClass = 'alert-info';
        if ($alertType == 'success') {
            $alertClass = 'alert-success';
        } elseif ($alertType == 'danger') {
            $alertClass = 'alert-danger';
        } elseif ($alertType == 'warning') {
            $alertClass = 'alert-warning';
        }
        
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo $alertMessage;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['alert']);
    }
}

// Get product image URL
function getProductImageUrl($image) {
    if (empty($image)) {
        return 'https://placehold.co/600x400?text=No+Image';
    }
    
    // Check if the image is external (starts with http)
    if (strpos($image, 'http') === 0) {
        return $image;
    }
    
    return SITE_URL . '/uploads/' . $image;
}

// Get active page for navigation
function getActivePage() {
    $currentFile = basename($_SERVER['PHP_SELF']);
    return $currentFile;
}

// Add an item to cart
function addToCart($productId, $quantity, $startDate, $endDate) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already exists in cart
    $exists = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $productId) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            $_SESSION['cart'][$key]['start_date'] = $startDate;
            $_SESSION['cart'][$key]['end_date'] = $endDate;
            $exists = true;
            break;
        }
    }
    
    if (!$exists) {
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
}

// Remove an item from cart
function removeFromCart($productId) {
    if (!isset($_SESSION['cart'])) {
        return;
    }
    
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['product_id'] == $productId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }
    
    // Re-index array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Get cart items with product details
function getCartItems() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    $totalAmount = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $product = getOne('products', $item['product_id']);
        
        if ($product) {
            $duration = getRentalDuration($item['start_date'], $item['end_date']);
            $price = calculateRentalPrice($product['price'], $duration);
            $subtotal = $price * $item['quantity'];
            
            $items[] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'image' => $product['image'],
                'price' => $product['price'],
                'rental_price' => $price,
                'quantity' => $item['quantity'],
                'start_date' => $item['start_date'],
                'end_date' => $item['end_date'],
                'duration' => $duration,
                'subtotal' => $subtotal
            ];
            
            $totalAmount += $subtotal;
        }
    }
    
    return [
        'items' => $items,
        'total_amount' => $totalAmount
    ];
}

// Clear cart
function clearCart() {
    unset($_SESSION['cart']);
}

// Process checkout and create order
function createOrder($userId, $cart) {
    global $conn;
    
    // Begin transaction
    pg_query($conn, "BEGIN");
    
    try {
        // Insert order
        $sql = "INSERT INTO orders (user_id, total_amount, rental_start, rental_end) VALUES ($1, $2, $3, $4) RETURNING id";
        
        // Use the first item's dates for the whole order
        $startDate = $cart['items'][0]['start_date'];
        $endDate = $cart['items'][0]['end_date'];
        
        $result = pg_query_params($conn, $sql, [$userId, $cart['total_amount'], $startDate, $endDate]);
        
        if (!$result) {
            throw new Exception(pg_last_error($conn));
        }
        
        $row = pg_fetch_assoc($result);
        $orderId = $row['id'];
        
        // Insert order items
        foreach ($cart['items'] as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($1, $2, $3, $4)";
            $result = pg_query_params($conn, $sql, [$orderId, $item['product_id'], $item['quantity'], $item['rental_price']]);
            
            if (!$result) {
                throw new Exception(pg_last_error($conn));
            }
            
            // Update product stock
            $sql = "UPDATE products SET stock = stock - $1 WHERE id = $2";
            $result = pg_query_params($conn, $sql, [$item['quantity'], $item['product_id']]);
            
            if (!$result) {
                throw new Exception(pg_last_error($conn));
            }
        }
        
        // Commit transaction
        pg_query($conn, "COMMIT");
        
        return $orderId;
    } catch (Exception $e) {
        // Rollback transaction on error
        pg_query($conn, "ROLLBACK");
        return false;
    }
}

// Update order payment status
function updateOrderPayment($orderId, $paymentId, $status) {
    global $conn;
    
    $sql = "UPDATE orders SET payment_id = $1, payment_status = $2 WHERE id = $3";
    $result = pg_query_params($conn, $sql, [$paymentId, $status, $orderId]);
    
    return $result ? true : false;
}

// Get order details by ID
function getOrderDetails($orderId) {
    global $conn;
    
    $order = getOne('orders', $orderId);
    
    if (!$order) {
        return false;
    }
    
    $sql = "SELECT oi.*, p.name, p.image 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = $1";
    $result = pg_query_params($conn, $sql, [$orderId]);
    
    if (!$result) {
        return false;
    }
    
    $items = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    $order['items'] = $items;
    
    // Get user details
    $user = getOne('users', $order['user_id']);
    $order['user'] = $user;
    
    return $order;
}

// Get user orders
function getUserOrders($userId) {
    global $conn;
    
    $sql = "SELECT * FROM orders WHERE user_id = $1 ORDER BY created_at DESC";
    $result = pg_query_params($conn, $sql, [$userId]);
    
    if (!$result) {
        return [];
    }
    
    $orders = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

// Registrar visita de página
function recordPageVisit($pageUrl) {
    global $conn;
    
    // Remover parâmetros da URL
    $pageUrl = strtok(basename($pageUrl), '?');
    
    // Verificar se a página existe no banco de dados
    $sql = "SELECT * FROM site_stats WHERE page_url = $1";
    $result = pg_query_params($conn, $sql, [$pageUrl]);
    
    if (pg_num_rows($result) > 0) {
        // Atualizar contador
        $sql = "UPDATE site_stats SET visit_count = visit_count + 1, last_updated = CURRENT_TIMESTAMP WHERE page_url = $1";
        pg_query_params($conn, $sql, [$pageUrl]);
    } else {
        // Inserir nova página
        $sql = "INSERT INTO site_stats (page_url, visit_count) VALUES ($1, 1)";
        pg_query_params($conn, $sql, [$pageUrl]);
    }
}

// Obter estatísticas de visita
function getVisitStats() {
    global $conn;
    
    $stats = [];
    
    $sql = "SELECT page_url, visit_count, last_updated FROM site_stats ORDER BY visit_count DESC";
    $result = pg_query($conn, $sql);
    
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $stats[] = $row;
        }
    }
    
    return $stats;
}

// Obter total de visitas
function getTotalVisits() {
    global $conn;
    
    $sql = "SELECT SUM(visit_count) as total FROM site_stats";
    $result = pg_query($conn, $sql);
    
    if ($result && $row = pg_fetch_assoc($result)) {
        return $row['total'] ?: 0;
    }
    
    return 0;
}

// Calculate stats for admin dashboard
function getStats() {
    global $conn;
    
    $stats = [
        'users' => 0,
        'products' => 0,
        'orders' => 0,
        'revenue' => 0,
        'visits' => 0
    ];
    
    // Count users
    $sql = "SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $stats['users'] = $row['count'];
    }
    
    // Count products
    $sql = "SELECT COUNT(*) as count FROM products";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $stats['products'] = $row['count'];
    }
    
    // Count orders
    $sql = "SELECT COUNT(*) as count FROM orders";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $stats['orders'] = $row['count'];
    }
    
    // Calculate revenue from completed orders
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'approved'";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $stats['revenue'] = $row['total'] ?: 0;
    }
    
    // Get total visits
    $stats['visits'] = getTotalVisits();
    
    return $stats;
}
