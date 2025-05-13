<?php
/**
 * Installation Page
 * Sets up the admin account for the admin panel
 */

// Include configuration files
require_once 'config/config.php';

// Establish database connection if not already established
if (!isset($conn) || !$conn) {
    $pg_host = getenv('PGHOST');
    $pg_port = getenv('PGPORT');
    $pg_user = getenv('PGUSER');
    $pg_password = getenv('PGPASSWORD');
    $pg_database = getenv('PGDATABASE');
    
    $conn_string = "host=$pg_host port=$pg_port dbname=$pg_database user=$pg_user password=$pg_password";
    $conn = pg_connect($conn_string);
    
    if (!$conn) {
        die("Connection failed: Unable to connect to PostgreSQL database");
    }
}

// Check if already installed (admin exists)
$checkAdmin = "SELECT * FROM users WHERE is_admin = TRUE LIMIT 1";
$result = pg_query($conn, $checkAdmin);
$adminExists = pg_num_rows($result) > 0;

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = 'Todos os campos são obrigatórios';
        $messageType = 'danger';
    } elseif ($password !== $confirmPassword) {
        $message = 'As senhas não coincidem';
        $messageType = 'danger';
    } elseif (strlen($password) < 6) {
        $message = 'A senha deve ter pelo menos 6 caracteres';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Email inválido';
        $messageType = 'danger';
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if ($adminExists) {
            // Update existing admin
            $updateAdmin = "UPDATE users SET name = $1, email = $2, password = $3 WHERE is_admin = TRUE";
            $result = pg_query_params($conn, $updateAdmin, [$name, $email, $hashedPassword]);
        } else {
            // Create new admin
            $insertAdmin = "INSERT INTO users (name, email, password, is_admin) VALUES ($1, $2, $3, TRUE)";
            $result = pg_query_params($conn, $insertAdmin, [$name, $email, $hashedPassword]);
        }
        
        if ($result) {
            $message = 'Administrador configurado com sucesso. <a href="admin/login.php">Clique aqui para fazer login</a>';
            $messageType = 'success';
        } else {
            $message = 'Erro ao configurar administrador: ' . pg_last_error($conn);
            $messageType = 'danger';
        }
    }
}

// Get existing admin data if admin exists
$adminData = [];
if ($adminExists) {
    $adminData = pg_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - iPhone Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .installation-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            font-weight: 700;
            color: #333;
        }
        .form-control {
            padding: 12px;
            margin-bottom: 15px;
        }
        .btn-primary {
            padding: 12px;
            width: 100%;
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0069d9;
        }
        .installation-complete {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="installation-container">
            <div class="logo">
                <h1>iPhone Rental</h1>
                <p>Configuração do Administrador</p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo isset($adminData['name']) ? $adminData['name'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($adminData['email']) ? $adminData['email'] : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Senha</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $adminExists ? 'Atualizar Administrador' : 'Criar Administrador'; ?>
                </button>
            </form>
            
            <?php if ($adminExists): ?>
                <div class="mt-3 text-center">
                    <p>Um administrador já existe. Este formulário irá atualizar as informações do administrador.</p>
                    <a href="admin/login.php" class="btn btn-outline-primary mt-2">Ir para o Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>