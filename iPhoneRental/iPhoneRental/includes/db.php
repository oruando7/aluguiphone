<?php
/**
 * Database Helper Functions
 * Provides functions to interact with the database
 */

// Connect to the database
function dbConnect() {
    $conn_string = "host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS;
    $conn = pg_connect($conn_string);
    
    if (!$conn) {
        die("Connection failed: " . pg_last_error());
    }
    
    return $conn;
}

// We use the connection that was established in database.php
// If we need a new connection, we can use dbConnect()

// Get all records from a table
function getAll($table, $conditions = '', $orderBy = '', $limit = '') {
    global $conn;
    
    $sql = "SELECT * FROM " . $table;
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . $conditions;
    }
    
    if (!empty($orderBy)) {
        $sql .= " ORDER BY " . $orderBy;
    }
    
    if (!empty($limit)) {
        $sql .= " LIMIT " . $limit;
    }
    
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        return false;
    }
    
    $data = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

// Get a single record from a table
function getOne($table, $id, $idField = 'id') {
    global $conn;
    
    $sql = "SELECT * FROM " . $table . " WHERE " . $idField . " = $1";
    $result = pg_query_params($conn, $sql, [$id]);
    
    if (!$result || pg_num_rows($result) == 0) {
        return false;
    }
    
    return pg_fetch_assoc($result);
}

// Insert a record into a table
function insert($table, $data) {
    global $conn;
    
    $fields = array_keys($data);
    $values = array_values($data);
    
    // Create placeholders for the prepared statement ($1, $2, etc.)
    $placeholders = [];
    for ($i = 1; $i <= count($fields); $i++) {
        $placeholders[] = '$' . $i;
    }
    
    $sql = "INSERT INTO " . $table . " (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ") RETURNING id";
    $result = pg_query_params($conn, $sql, $values);
    
    if (!$result) {
        return false;
    }
    
    // Get the inserted ID
    $row = pg_fetch_assoc($result);
    return $row['id'];
}

// Update a record in a table
function update($table, $data, $id, $idField = 'id') {
    global $conn;
    
    $fields = [];
    $values = array_values($data);
    
    // Create set statements ($1, $2, etc.)
    $i = 1;
    foreach (array_keys($data) as $key) {
        $fields[] = $key . " = $" . $i++;
    }
    
    // Add the ID as the last parameter
    $values[] = $id;
    
    $sql = "UPDATE " . $table . " SET " . implode(", ", $fields) . " WHERE " . $idField . " = $" . $i;
    $result = pg_query_params($conn, $sql, $values);
    
    return $result ? true : false;
}

// Delete a record from a table
function delete($table, $id, $idField = 'id') {
    global $conn;
    
    $sql = "DELETE FROM " . $table . " WHERE " . $idField . " = $1";
    $result = pg_query_params($conn, $sql, [$id]);
    
    return $result ? true : false;
}

// Custom query with prepared statement
function customQuery($sql, $params = [], $types = '') {
    global $conn;
    
    if (empty($params)) {
        $result = pg_query($conn, $sql);
    } else {
        $result = pg_query_params($conn, $sql, $params);
    }
    
    if (!$result) {
        return false;
    }
    
    // Check if this is a SELECT query
    if (stripos(trim($sql), 'SELECT') === 0) {
        $data = [];
        
        while ($row = pg_fetch_assoc($result)) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    return true; // For INSERT, UPDATE, DELETE
}

// Log page view
function logPageView($page) {
    global $conn;
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO page_views (page, user_id, ip_address) VALUES ($1, $2, $3)";
    $result = pg_query_params($conn, $sql, [$page, $userId, $ipAddress]);
    
    return $result ? true : false;
}

// Get dashboard metrics
function getDashboardMetrics() {
    global $conn;
    
    $metrics = [
        'total_orders' => 0,
        'total_users' => 0,
        'total_revenue' => 0,
        'popular_products' => [],
        'recent_orders' => [],
        'page_views' => 0
    ];
    
    // Total orders
    $sql = "SELECT COUNT(*) as count FROM orders";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $metrics['total_orders'] = $row['count'];
    }
    
    // Total users (non-admin)
    $sql = "SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $metrics['total_users'] = $row['count'];
    }
    
    // Total revenue
    $sql = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'approved'";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $metrics['total_revenue'] = $row['total'] ?: 0;
    }
    
    // Popular products
    $sql = "SELECT p.id, p.name, COUNT(oi.id) as order_count 
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id, p.name
            ORDER BY order_count DESC
            LIMIT 5";
    $result = pg_query($conn, $sql);
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $metrics['popular_products'][] = $row;
        }
    }
    
    // Recent orders
    $sql = "SELECT o.id, o.total_amount, o.payment_status, o.created_at, u.name as user_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5";
    $result = pg_query($conn, $sql);
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $metrics['recent_orders'][] = $row;
        }
    }
    
    // Page views
    $sql = "SELECT COUNT(*) as count FROM page_views";
    $result = pg_query($conn, $sql);
    if ($result && $row = pg_fetch_assoc($result)) {
        $metrics['page_views'] = $row['count'];
    }
    
    return $metrics;
}
