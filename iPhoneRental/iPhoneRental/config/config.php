<?php
/**
 * Main Configuration File
 * Contains global settings and configurations
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define('SITE_NAME', 'iPhone Rental Service');
define('ADMIN_EMAIL', 'admin@example.com');

// Directory Paths
define('ROOT_DIR', dirname(__DIR__));
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_DIR . '/error.log');

// Time Zone
date_default_timezone_set('UTC');

// Include database configuration
require_once(ROOT_DIR . '/config/database.php');

// Include functions
require_once(INCLUDES_DIR . '/functions.php');
require_once(INCLUDES_DIR . '/db.php');
require_once(INCLUDES_DIR . '/auth.php');

// Get site settings from database
function getSetting($name) {
    global $conn;
    $sql = "SELECT value FROM settings WHERE name = $1";
    $result = pg_query_params($conn, $sql, [$name]);
    
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        return $row['value'];
    }
    
    return null;
}

// Load common settings
$siteNameFromDB = getSetting('site_name');
if ($siteNameFromDB) {
    define('SITE_NAME_CUSTOM', $siteNameFromDB);
} else {
    define('SITE_NAME_CUSTOM', SITE_NAME);
}
