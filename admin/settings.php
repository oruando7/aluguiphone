<?php
// Include header
include_once 'includes/header.php';
include_once '../includes/mercadopago.php';
include_once '../includes/email.php';

// Check if user is admin
requireAdmin();

// Get current settings
$siteName = getSetting('site_name');
$mercadopagoPublicKey = getSetting('mercadopago_public_key');
$mercadopagoAccessToken = getSetting('mercadopago_access_token');
$emailHost = getSetting('email_host');
$emailUsername = getSetting('email_username');
$emailPassword = getSetting('email_password');
$emailPort = getSetting('email_port');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['site_settings'])) {
        // Update site settings
        $newSiteName = cleanInput($_POST['site_name']);
        
        updateSetting('site_name', $newSiteName);
        showAlert('Site settings updated successfully', 'success');
        
        // Refresh page to show updated settings
        redirect('settings.php');
    } else if (isset($_POST['mercadopago_settings'])) {
        // Update Mercado Pago settings
        $newPublicKey = cleanInput($_POST['mercadopago_public_key']);
        $newAccessToken = cleanInput($_POST['mercadopago_access_token']);
        
        saveMercadoPagoConfig($newPublicKey, $newAccessToken);
        showAlert('Mercado Pago settings updated successfully', 'success');
        
        // Refresh page to show updated settings
        redirect('settings.php');
    } else if (isset($_POST['email_settings'])) {
        // Update email settings
        $newEmailHost = cleanInput($_POST['email_host']);
        $newEmailUsername = cleanInput($_POST['email_username']);
        $newEmailPassword = cleanInput($_POST['email_password']);
        $newEmailPort = cleanInput($_POST['email_port']);
        
        saveEmailConfig($newEmailHost, $newEmailUsername, $newEmailPassword, $newEmailPort);
        showAlert('Email settings updated successfully', 'success');
        
        // Refresh page to show updated settings
        redirect('settings.php');
    }
}

// Log page view
logPageView('admin_settings');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Settings</h1>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <!-- Site Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Site Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo $siteName; ?>" required>
                        </div>
                        <input type="hidden" name="site_settings" value="1">
                        <button type="submit" class="btn btn-primary">Save Site Settings</button>
                    </form>
                </div>
            </div>
            
            <!-- Mercado Pago Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Mercado Pago Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="mercadopago_public_key" class="form-label">Public Key</label>
                            <input type="text" class="form-control" id="mercadopago_public_key" name="mercadopago_public_key" value="<?php echo $mercadopagoPublicKey; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="mercadopago_access_token" class="form-label">Access Token</label>
                            <input type="text" class="form-control" id="mercadopago_access_token" name="mercadopago_access_token" value="<?php echo $mercadopagoAccessToken; ?>">
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>You need to set up both Public Key and Access Token to enable Mercado Pago payments.</small>
                        </div>
                        <input type="hidden" name="mercadopago_settings" value="1">
                        <button type="submit" class="btn btn-primary">Save Mercado Pago Settings</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <!-- Email Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Email Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                        <div class="mb-3">
                            <label for="email_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="email_host" name="email_host" value="<?php echo $emailHost; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_port" class="form-label">SMTP Port</label>
                            <input type="text" class="form-control" id="email_port" name="email_port" value="<?php echo $emailPort; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_username" class="form-label">Email Username</label>
                            <input type="text" class="form-control" id="email_username" name="email_username" value="<?php echo $emailUsername; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email_password" class="form-label">Email Password</label>
                            <input type="password" class="form-control" id="email_password" name="email_password" value="<?php echo $emailPassword; ?>" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Configure your email settings to enable order confirmation emails and other notifications.</small>
                        </div>
                        <input type="hidden" name="email_settings" value="1">
                        <button type="submit" class="btn btn-primary">Save Email Settings</button>
                    </form>
                </div>
            </div>
            
            <!-- System Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6 fw-bold">PHP Version:</div>
                        <div class="col-6"><?php echo phpversion(); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6 fw-bold">MySQL Version:</div>
                        <div class="col-6"><?php echo $conn->server_info; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6 fw-bold">Mercado Pago:</div>
                        <div class="col-6">
                            <?php if (isMercadoPagoConfigured()): ?>
                            <span class="badge bg-success">Configured</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Not Configured</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6 fw-bold">Email System:</div>
                        <div class="col-6">
                            <?php if (isEmailConfigured()): ?>
                            <span class="badge bg-success">Configured</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Not Configured</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
