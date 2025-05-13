<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and database connection
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Registrar visita na página atual
recordPageVisit($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME_CUSTOM; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mobile-alt me-2"></i><?php echo SITE_NAME_CUSTOM; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (getActivePage() == 'index.php') ? 'active' : ''; ?>" href="index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (getActivePage() == 'products.php') ? 'active' : ''; ?>" href="products.php">Produtos</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (getActivePage() == 'account.php') ? 'active' : ''; ?>" href="account.php">Minha Conta</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php 
                    // Get cart items if exists
                    $cartCount = 0;
                    if (isset($_SESSION['cart'])) {
                        $cartCount = count($_SESSION['cart']);
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative <?php echo (getActivePage() == 'checkout.php') ? 'active' : ''; ?>" href="checkout.php">
                            <i class="fas fa-shopping-cart"></i> Carrinho
                            <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cartCount; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="account.php">Minha Conta</a></li>
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="admin/index.php">Painel Administrativo</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (getActivePage() == 'login.php') ? 'active' : ''; ?>" href="login.php">Entrar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (getActivePage() == 'register.php') ? 'active' : ''; ?>" href="register.php">Cadastrar</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Container -->
    <div class="container py-4">
        <!-- Display alert messages if any -->
        <?php displayAlert(); ?>
