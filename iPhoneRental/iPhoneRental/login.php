<?php
// Include header
include_once 'includes/header.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form data
    if (empty($email) || empty($password)) {
        showAlert('Please enter email and password', 'danger');
    } else {
        // Attempt login
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            showAlert('Login successful', 'success');
            
            // Redirect to appropriate page
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else if (isAdmin()) {
                redirect('admin/index.php');
            } else {
                redirect('index.php');
            }
        } else {
            showAlert($result['message'], 'danger');
        }
    }
}
?>

<section class="mb-5">
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Login to Your Account</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
