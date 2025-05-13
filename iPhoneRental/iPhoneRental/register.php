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
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $address = cleanInput($_POST['address']);
    $phone = cleanInput($_POST['phone']);
    
    // Validate form data
    if (empty($name) || empty($email) || empty($password)) {
        showAlert('Please fill all required fields', 'danger');
    } else if ($password !== $confirmPassword) {
        showAlert('Passwords do not match', 'danger');
    } else if (strlen($password) < 6) {
        showAlert('Password must be at least 6 characters', 'danger');
    } else {
        // Register user
        $result = registerUser($name, $email, $password, $address, $phone);
        
        if ($result['success']) {
            // Auto login user
            loginUser($email, $password);
            
            // Send registration email
            $user = [
                'name' => $name,
                'email' => $email
            ];
            sendRegistrationEmail($user);
            
            showAlert('Registration successful', 'success');
            
            // Redirect to appropriate page
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
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
            <h2 class="text-center mb-4">Create an Account</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
