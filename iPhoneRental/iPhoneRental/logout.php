<?php
// Include configuration
require_once 'config/config.php';

// Log the user out
logoutUser();

// Redirect to home page
showAlert('You have been logged out successfully', 'success');
redirect('index.php');
