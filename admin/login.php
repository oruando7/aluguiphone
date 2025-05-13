<?php
// Include configuration
require_once '../config/config.php';

// Check if already logged in
if (isLoggedIn() && isAdmin()) {
    redirect('dashboard.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        showAlert('Please enter email and password', 'danger');
    } else {
        // Attempt login
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // Debug info
            showAlert('DEBUG: is_admin value = ' . (isset($_SESSION['is_admin']) ? var_export($_SESSION['is_admin'], true) : 'not set') . 
                     ' - isAdmin function returns: ' . (isAdmin() ? 'true' : 'false'), 'info');
            
            // Check if user is admin
            if (isAdmin()) {
                showAlert('Login successful', 'success');
                redirect('dashboard.php');
            } else {
                // Logout non-admin users
                logoutUser();
                showAlert('Access denied. Admin privileges required.', 'danger');
            }
        } else {
            showAlert($result['message'], 'danger');
        }
    }
}

// Log page view
logPageView('admin_login');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME_CUSTOM; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4 col-md-6 col-sm-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                            <h3 class="card-title">Admin Login</h3>
                            <p class="text-muted">Enter your credentials to access the admin panel</p>
                        </div>
                        
                        <!-- Display alerts -->
                        <?php displayAlert(); ?>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Login</button>
                                <a href="../index.php" class="btn btn-link">Return to Website</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted"><?php echo SITE_NAME_CUSTOM; ?> &copy; <?php echo date('Y'); ?></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
