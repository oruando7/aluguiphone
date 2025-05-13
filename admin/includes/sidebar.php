<!-- Sidebar -->
<div id="sidebar">
    <div class="sidebar-header">
        <h3><?php echo SITE_NAME_CUSTOM; ?></h3>
        <h6>Painel Administrativo</h6>
    </div>
    
    <ul class="list-unstyled sidebar-menu">
        <li class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Painel</span>
            </a>
        </li>
        
        <li class="nav-item <?php echo $currentPage == 'products.php' || $currentPage == 'product-edit.php' ? 'active' : ''; ?>">
            <a href="products.php" class="nav-link">
                <i class="fas fa-fw fa-mobile-alt"></i>
                <span>Produtos</span>
            </a>
        </li>
        
        <li class="nav-item <?php echo $currentPage == 'orders.php' || $currentPage == 'order-view.php' ? 'active' : ''; ?>">
            <a href="orders.php" class="nav-link">
                <i class="fas fa-fw fa-shopping-cart"></i>
                <span>Pedidos</span>
            </a>
        </li>
        
        <li class="nav-item <?php echo $currentPage == 'customers.php' ? 'active' : ''; ?>">
            <a href="customers.php" class="nav-link">
                <i class="fas fa-fw fa-users"></i>
                <span>Clientes</span>
            </a>
        </li>
        
        <li class="nav-item <?php echo $currentPage == 'settings.php' ? 'active' : ''; ?>">
            <a href="settings.php" class="nav-link">
                <i class="fas fa-fw fa-cog"></i>
                <span>Configurações</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="../logout.php" class="nav-link">
                <i class="fas fa-fw fa-sign-out-alt"></i>
                <span>Sair</span>
            </a>
        </li>
    </ul>
</div>
