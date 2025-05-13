<?php
/**
 * Email Functions
 * Handles sending emails for various purposes
 */

// Send email
function sendEmail($to, $subject, $message, $headers = []) {
    // Get email settings from database
    $emailHost = getSetting('email_host');
    $emailUsername = getSetting('email_username');
    $emailPassword = getSetting('email_password');
    $emailPort = getSetting('email_port');
    
    // Check if email settings are configured
    if (empty($emailHost) || empty($emailUsername) || empty($emailPassword)) {
        return [
            'success' => false,
            'message' => 'Email settings not configured'
        ];
    }
    
    // In a real implementation, you would use PHPMailer or similar library
    // This is a simplified version using mail() function
    
    // Set default headers
    $defaultHeaders = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . $emailUsername . '>'
    ];
    
    // Merge headers
    $allHeaders = array_merge($defaultHeaders, $headers);
    
    // Send email
    $result = mail($to, $subject, $message, implode("\r\n", $allHeaders));
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Email sent successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to send email'
        ];
    }
}

// Send order confirmation email
function sendOrderConfirmation($order) {
    // Get user email
    $userEmail = $order['user']['email'];
    $userName = $order['user']['name'];
    
    // Prepare email subject
    $subject = 'Order Confirmation - ' . SITE_NAME_CUSTOM;
    
    // Prepare email message
    $message = '<html><body>';
    $message .= '<h2>Thank you for your order!</h2>';
    $message .= '<p>Dear ' . $userName . ',</p>';
    $message .= '<p>Your order has been confirmed. Here are the details:</p>';
    
    $message .= '<table border="0" cellpadding="5" cellspacing="0" width="100%">';
    $message .= '<tr><td><strong>Order ID:</strong></td><td>' . $order['id'] . '</td></tr>';
    $message .= '<tr><td><strong>Order Date:</strong></td><td>' . formatDate($order['created_at']) . '</td></tr>';
    $message .= '<tr><td><strong>Rental Period:</strong></td><td>' . formatDate($order['rental_start']) . ' to ' . formatDate($order['rental_end']) . '</td></tr>';
    $message .= '<tr><td><strong>Total Amount:</strong></td><td>' . formatPrice($order['total_amount']) . '</td></tr>';
    $message .= '</table>';
    
    $message .= '<h3>Order Items:</h3>';
    $message .= '<table border="0" cellpadding="5" cellspacing="0" width="100%">';
    $message .= '<tr><th>Product</th><th>Quantity</th><th>Price</th></tr>';
    
    foreach ($order['items'] as $item) {
        $message .= '<tr>';
        $message .= '<td>' . $item['name'] . '</td>';
        $message .= '<td>' . $item['quantity'] . '</td>';
        $message .= '<td>' . formatPrice($item['price']) . '</td>';
        $message .= '</tr>';
    }
    
    $message .= '</table>';
    
    $message .= '<p>Thank you for choosing ' . SITE_NAME_CUSTOM . ' for your iPhone rental needs!</p>';
    $message .= '<p>If you have any questions, please contact us.</p>';
    $message .= '<p>Best regards,<br>' . SITE_NAME_CUSTOM . ' Team</p>';
    $message .= '</body></html>';
    
    // Send email
    return sendEmail($userEmail, $subject, $message);
}

// Send registration confirmation email
function sendRegistrationEmail($user) {
    // Prepare email subject
    $subject = 'Welcome to ' . SITE_NAME_CUSTOM;
    
    // Prepare email message
    $message = '<html><body>';
    $message .= '<h2>Welcome to ' . SITE_NAME_CUSTOM . '!</h2>';
    $message .= '<p>Dear ' . $user['name'] . ',</p>';
    $message .= '<p>Thank you for registering with us. Your account has been created successfully.</p>';
    $message .= '<p>You can now log in and start renting iPhones for your needs.</p>';
    $message .= '<p>Best regards,<br>' . SITE_NAME_CUSTOM . ' Team</p>';
    $message .= '</body></html>';
    
    // Send email
    return sendEmail($user['email'], $subject, $message);
}

// Send password reset email
function sendPasswordResetEmail($user, $resetToken) {
    // Prepare reset URL
    $resetUrl = SITE_URL . '/reset-password.php?token=' . $resetToken . '&email=' . urlencode($user['email']);
    
    // Prepare email subject
    $subject = 'Password Reset - ' . SITE_NAME_CUSTOM;
    
    // Prepare email message
    $message = '<html><body>';
    $message .= '<h2>Password Reset Request</h2>';
    $message .= '<p>Dear ' . $user['name'] . ',</p>';
    $message .= '<p>We received a request to reset your password. If you did not make this request, please ignore this email.</p>';
    $message .= '<p>To reset your password, please click the link below:</p>';
    $message .= '<p><a href="' . $resetUrl . '">Reset Password</a></p>';
    $message .= '<p>This link will expire in 24 hours.</p>';
    $message .= '<p>Best regards,<br>' . SITE_NAME_CUSTOM . ' Team</p>';
    $message .= '</body></html>';
    
    // Send email
    return sendEmail($user['email'], $subject, $message);
}

// Send contact form email
function sendContactEmail($name, $email, $subject, $message) {
    // Get admin email
    $adminEmail = ADMIN_EMAIL;
    
    // Prepare email subject
    $emailSubject = 'Contact Form: ' . $subject;
    
    // Prepare email message
    $emailMessage = '<html><body>';
    $emailMessage .= '<h2>Contact Form Submission</h2>';
    $emailMessage .= '<p><strong>Name:</strong> ' . $name . '</p>';
    $emailMessage .= '<p><strong>Email:</strong> ' . $email . '</p>';
    $emailMessage .= '<p><strong>Subject:</strong> ' . $subject . '</p>';
    $emailMessage .= '<p><strong>Message:</strong></p>';
    $emailMessage .= '<p>' . nl2br($message) . '</p>';
    $emailMessage .= '</body></html>';
    
    // Set reply-to header
    $headers = [
        'Reply-To: ' . $name . ' <' . $email . '>'
    ];
    
    // Send email
    return sendEmail($adminEmail, $emailSubject, $emailMessage, $headers);
}

// Check if email settings are configured
function isEmailConfigured() {
    $emailHost = getSetting('email_host');
    $emailUsername = getSetting('email_username');
    $emailPassword = getSetting('email_password');
    
    return !empty($emailHost) && !empty($emailUsername) && !empty($emailPassword);
}

// Save email configuration
function saveEmailConfig($host, $username, $password, $port) {
    updateSetting('email_host', $host);
    updateSetting('email_username', $username);
    updateSetting('email_password', $password);
    updateSetting('email_port', $port);
    
    return true;
}
