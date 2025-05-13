<?php
/**
 * Authentication Functions
 * Handles user authentication, registration, and session management
 */

// Register a new user
function registerUser($name, $email, $password, $address = '', $phone = '') {
    global $conn;
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = $1";
    $result = pg_query_params($conn, $sql, [$email]);
    
    if (pg_num_rows($result) > 0) {
        return [
            'success' => false,
            'message' => 'Email already exists'
        ];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (name, email, password, address, phone) VALUES ($1, $2, $3, $4, $5) RETURNING id";
    $result = pg_query_params($conn, $sql, [$name, $email, $hashedPassword, $address, $phone]);
    
    if ($result) {
        $row = pg_fetch_assoc($result);
        return [
            'success' => true,
            'user_id' => $row['id'],
            'message' => 'Registration successful'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Registration failed: ' . pg_last_error($conn)
        ];
    }
}

// Login user
function loginUser($email, $password) {
    global $conn;
    
    $sql = "SELECT id, name, email, password, is_admin FROM users WHERE email = $1";
    $result = pg_query_params($conn, $sql, [$email]);
    
    if (pg_num_rows($result) == 0) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
    
    $user = pg_fetch_assoc($result);
    
    if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Invalid password'
        ];
    }
}

// Logout user
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    return true;
}

// Update user profile
function updateUserProfile($userId, $name, $address, $phone) {
    global $conn;
    
    $sql = "UPDATE users SET name = $1, address = $2, phone = $3 WHERE id = $4";
    $result = pg_query_params($conn, $sql, [$name, $address, $phone, $userId]);
    
    if ($result) {
        // Update session variables
        $_SESSION['user_name'] = $name;
        
        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Profile update failed: ' . pg_last_error($conn)
        ];
    }
}

// Change user password
function changeUserPassword($userId, $currentPassword, $newPassword) {
    global $conn;
    
    // Get current password from database
    $sql = "SELECT password FROM users WHERE id = $1";
    $result = pg_query_params($conn, $sql, [$userId]);
    
    if (pg_num_rows($result) == 0) {
        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }
    
    $user = pg_fetch_assoc($result);
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Current password is incorrect'
        ];
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $sql = "UPDATE users SET password = $1 WHERE id = $2";
    $result = pg_query_params($conn, $sql, [$hashedPassword, $userId]);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Password change failed: ' . pg_last_error($conn)
        ];
    }
}

// Check if email exists
function emailExists($email) {
    global $conn;
    
    $sql = "SELECT id FROM users WHERE email = $1";
    $result = pg_query_params($conn, $sql, [$email]);
    
    return pg_num_rows($result) > 0;
}

// Get user by ID
function getUserById($userId) {
    global $conn;
    
    $sql = "SELECT id, name, email, address, phone, is_admin, created_at FROM users WHERE id = $1";
    $result = pg_query_params($conn, $sql, [$userId]);
    
    if (pg_num_rows($result) == 0) {
        return false;
    }
    
    return pg_fetch_assoc($result);
}

// Get all users (for admin)
function getAllUsers() {
    global $conn;
    
    $sql = "SELECT id, name, email, address, phone, is_admin, created_at FROM users ORDER BY created_at DESC";
    $result = pg_query($conn, $sql);
    
    if (!$result) {
        return [];
    }
    
    $users = [];
    
    while ($row = pg_fetch_assoc($result)) {
        $users[] = $row;
    }
    
    return $users;
}

// Require login for protected pages
function requireLogin() {
    if (!isLoggedIn()) {
        showAlert('Please login to access this page', 'warning');
        redirect('login.php');
    }
}

// Require admin for admin pages
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        showAlert('Access denied', 'danger');
        redirect('../index.php');
    }
}
