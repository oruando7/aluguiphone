<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Not logged in or not admin, redirect to login page
    showAlert('Por favor, faça login com credenciais de administrador', 'warning');
    redirect('login.php');
}

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - <?php echo SITE_NAME_CUSTOM; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>

<!-- Page Wrapper -->
<div class="wrapper">
    
    <?php include 'sidebar.php'; ?>
    
    <!-- Content Wrapper -->
    <div id="content-wrapper">
        
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
            <!-- Sidebar Toggle -->
            <button id="sidebarToggle" class="btn btn-link d-md-none">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ms-auto">
                
                <!-- Visit Website -->
                <li class="nav-item">
                    <a class="nav-link" href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt fa-fw me-1"></i>
                        Visitar Site
                    </a>
                </li>
                
                <div class="topbar-divider d-none d-sm-block"></div>
                
                <!-- Admin User Information -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-none d-lg-inline text-gray-600 small me-2"><?php echo $_SESSION['user_name']; ?></span>
                        <i class="fas fa-user-circle fa-fw"></i>
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cogs fa-sm fa-fw me-2 text-gray-400"></i>
                            Configurações
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                            Sair
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        
        <!-- Begin Page Content -->
        <div class="content">
            <?php displayAlert(); ?>
