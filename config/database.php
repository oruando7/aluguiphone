<?php
/**
 * Database Configuration File
 * Contains the database connection settings
 */

// Get PostgreSQL credentials from environment variables
$pg_host = getenv('PGHOST');
$pg_port = getenv('PGPORT');
$pg_user = getenv('PGUSER');
$pg_password = getenv('PGPASSWORD');
$pg_database = getenv('PGDATABASE');

// Set database constants
define('DB_HOST', $pg_host);
define('DB_PORT', $pg_port);
define('DB_USER', $pg_user);
define('DB_PASS', $pg_password);
define('DB_NAME', $pg_database);

// Create connection
$conn_string = "host=$pg_host port=$pg_port dbname=$pg_database user=$pg_user password=$pg_password";
$conn = pg_connect($conn_string);

// Check connection
if (!$conn) {
    die("Connection failed: Unable to connect to PostgreSQL database");
}

// Create necessary tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        address TEXT,
        phone VARCHAR(20),
        is_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS products (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 1,
        image VARCHAR(255),
        is_available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS orders (
        id SERIAL PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_id VARCHAR(100),
        payment_status VARCHAR(50) DEFAULT 'pending',
        rental_start DATE NOT NULL,
        rental_end DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS order_items (
        id SERIAL PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS page_views (
        id SERIAL PRIMARY KEY,
        page VARCHAR(100) NOT NULL,
        user_id INT,
        ip_address VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS settings (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $sql) {
    $result = pg_query($conn, $sql);
    if (!$result) {
        die("Error creating table: " . pg_last_error($conn));
    }
}

// Insert default admin if not exists
$check_admin = "SELECT * FROM users WHERE is_admin = TRUE LIMIT 1";
$result = pg_query($conn, $check_admin);

if (pg_num_rows($result) == 0) {
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (name, email, password, is_admin) VALUES ('Admin', 'admin@example.com', '$default_password', TRUE)";
    $result = pg_query($conn, $insert_admin);
    if (!$result) {
        die("Error creating default admin: " . pg_last_error($conn));
    }
}

// Insert default settings if not exists
$check_settings = "SELECT * FROM settings WHERE name IN ('mercadopago_public_key', 'mercadopago_access_token', 'email_host', 'email_username', 'email_password', 'email_port', 'site_name')";
$result = pg_query($conn, $check_settings);

if (pg_num_rows($result) < 7) {
    $settings = [
        "INSERT INTO settings (name, value) VALUES ('mercadopago_public_key', '') ON CONFLICT (name) DO NOTHING",
        "INSERT INTO settings (name, value) VALUES ('mercadopago_access_token', '') ON CONFLICT (name) DO NOTHING",
        "INSERT INTO settings (name, value) VALUES ('email_host', 'smtp.example.com') ON CONFLICT (name) DO NOTHING",
        "INSERT INTO settings (name, value) VALUES ('email_username', '') ON CONFLICT (name) DO NOTHING",
        "INSERT INTO settings (name, value) VALUES ('email_password', '') ON CONFLICT (name) DO NOTHING",
        "INSERT INTO settings (name, value) VALUES ('email_port', '587') ON CONFLICT (name) DO NOTHING",
        "INSERT INTO settings (name, value) VALUES ('site_name', 'iPhone Rental Service') ON CONFLICT (name) DO NOTHING"
    ];
    
    foreach ($settings as $sql) {
        $result = pg_query($conn, $sql);
        if (!$result) {
            die("Error inserting default settings: " . pg_last_error($conn));
        }
    }
}

// Sample products (only insert if table is empty)
$check_products = "SELECT * FROM products LIMIT 1";
$result = pg_query($conn, $check_products);

if (pg_num_rows($result) == 0) {
    $sample_products = [
        "INSERT INTO products (name, description, price, stock, image) VALUES 
        ('iPhone 13 Pro', 'The latest iPhone 13 Pro with A15 Bionic chip, Pro camera system, and Super Retina XDR display.', 29.99, 5, 'iphone_13_pro.svg')",
        
        "INSERT INTO products (name, description, price, stock, image) VALUES 
        ('iPhone 13', 'The powerful iPhone 13 featuring A15 Bionic chip, advanced dual-camera system, and Super Retina XDR display.', 24.99, 8, 'iphone_13.svg')",
        
        "INSERT INTO products (name, description, price, stock, image) VALUES 
        ('iPhone 12 Pro', 'iPhone 12 Pro with A14 Bionic chip, Pro camera system, and Ceramic Shield front cover.', 19.99, 10, 'iphone_12_pro.svg')",
        
        "INSERT INTO products (name, description, price, stock, image) VALUES 
        ('iPhone 12', 'iPhone 12 with A14 Bionic chip, advanced dual-camera system, and Super Retina XDR display.', 17.99, 12, 'iphone_12.svg')",
        
        "INSERT INTO products (name, description, price, stock, image) VALUES 
        ('iPhone SE', 'The powerful iPhone SE with A15 Bionic chip, advanced camera, and 4.7-inch Retina HD display.', 12.99, 15, 'iphone_se.svg')"
    ];
    
    foreach ($sample_products as $sql) {
        $result = pg_query($conn, $sql);
        if (!$result) {
            echo "Error inserting sample products: " . pg_last_error($conn);
        }
    }
}

// We don't close the connection here as it will be used throughout the application
