<?php
/**
 * DATABASE SETUP SCRIPT
 * 
 * This script initializes the SQLite database with sample data
 * Run this script once to set up the database
 */

$db_file = 'database.db';
$success_messages = [];
$error_messages = [];

// Check if database already exists
$db_exists = file_exists($db_file);

try {
    // Create database connection
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop existing tables if they exist
    if ($db_exists) {
        $db->exec("DROP TABLE IF EXISTS users");
        $db->exec("DROP TABLE IF EXISTS products");
        $success_messages[] = "Existing tables dropped successfully";
    }
    
    // Create users table
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $success_messages[] = "Users table created successfully";
    
    // Create products table
    $db->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            stock INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $success_messages[] = "Products table created successfully";
    
    // Insert sample users (plain text passwords - intentionally insecure!)
    $users = [
        ['admin', 'password123', 'admin@example.com', 'admin'],
        ['user', 'user123', 'user@example.com', 'user'],
        ['test', 'test123', 'test@example.com', 'user'],
        ['john', 'john2024', 'john@example.com', 'user'],
        ['alice', 'alice2024', 'alice@example.com', 'user']
    ];
    
    $stmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    $success_messages[] = count($users) . " sample users inserted successfully";
    
    // Insert sample products
    $products = [
        ['Laptop Dell XPS 15', 'High-performance laptop with 16GB RAM and 512GB SSD', 1299.99, 15],
        ['iPhone 15 Pro', 'Latest iPhone with A17 Pro chip and titanium design', 999.99, 25],
        ['Samsung Galaxy S24', 'Android flagship phone with AI features', 899.99, 30],
        ['iPad Air', 'Powerful tablet with M1 chip and 10.9-inch display', 599.99, 20],
        ['MacBook Pro 14"', 'Professional laptop with M3 Pro chip', 1999.99, 10],
        ['Sony WH-1000XM5', 'Premium noise-cancelling wireless headphones', 399.99, 40],
        ['AirPods Pro', 'Wireless earbuds with active noise cancellation', 249.99, 50],
        ['Apple Watch Series 9', 'Advanced smartwatch with health features', 399.99, 35],
        ['iPad Pro 12.9"', 'Pro tablet with M2 chip and Liquid Retina display', 1099.99, 12],
        ['Magic Keyboard', 'Wireless keyboard for Mac and iPad', 99.99, 45],
        ['Logitech MX Master 3S', 'Advanced wireless mouse for productivity', 99.99, 38],
        ['LG UltraWide Monitor', '34-inch curved monitor with QHD resolution', 599.99, 18],
        ['PlayStation 5', 'Next-gen gaming console', 499.99, 8],
        ['Xbox Series X', 'Powerful gaming console with 4K gaming', 499.99, 10],
        ['Nintendo Switch OLED', 'Hybrid gaming console with OLED screen', 349.99, 22]
    ];
    
    $stmt = $db->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    $success_messages[] = count($products) . " sample products inserted successfully";
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
        $success_messages[] = "Uploads directory created successfully";
    }
    
    // Create .htaccess file to prevent directory listing (minimal security)
    $htaccess_content = "Options -Indexes\n";
    file_put_contents($upload_dir . '.htaccess', $htaccess_content);
    $success_messages[] = ".htaccess file created in uploads directory";
    
} catch (PDOException $e) {
    $error_messages[] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error_messages[] = "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Database Setup</h1>
            <p>Initialize SQLite Database with Sample Data</p>
        </div>
        
        <nav>
            <a href="index.php">Login</a>
            <a href="search.php">Search</a>
            <a href="upload.php">Upload</a>
            <a href="command.php">Command</a>
            <a href="admin.php">Admin</a>
        </nav>
        
        <div class="content">
            <h2>Database Setup Results</h2>
            
            <?php if (count($success_messages) > 0): ?>
                <div class="success-message">
                    <h3>✅ Setup Completed Successfully!</h3>
                    <ul>
                        <?php foreach ($success_messages as $message): ?>
                            <li><?php echo htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (count($error_messages) > 0): ?>
                <div class="error-message">
                    <h3>❌ Errors Occurred:</h3>
                    <ul>
                        <?php foreach ($error_messages as $message): ?>
                            <li><?php echo htmlspecialchars($message); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (count($success_messages) > 0): ?>
                <div class="info-message">
                    <h3>📋 Database Information</h3>
                    <p><strong>Database File:</strong> <code><?php echo $db_file; ?></code></p>
                    <p><strong>Tables Created:</strong> users, products</p>
                    <p><strong>Sample Users:</strong></p>
                    <ul>
                        <li>Username: <code>admin</code> | Password: <code>password123</code> | Role: admin</li>
                        <li>Username: <code>user</code> | Password: <code>user123</code> | Role: user</li>
                        <li>Username: <code>test</code> | Password: <code>test123</code> | Role: user</li>
                        <li>Username: <code>john</code> | Password: <code>john2024</code> | Role: user</li>
                        <li>Username: <code>alice</code> | Password: <code>alice2024</code> | Role: user</li>
                    </ul>
                    <p><strong>Sample Products:</strong> 15 products added to database</p>
                </div>
                
                <div class="results-box">
                    <h3>🚀 Next Steps</h3>
                    <ol>
                        <li>Visit <a href="index.php">Login Page</a> to test SQL Injection</li>
                        <li>Visit <a href="search.php">Search Page</a> to test XSS</li>
                        <li>Visit <a href="upload.php">Upload Page</a> to test File Upload vulnerabilities</li>
                        <li>Visit <a href="command.php">Command Page</a> to test Command Injection</li>
                        <li>Visit <a href="admin.php">Admin Page</a> to view system information</li>
                    </ol>
                </div>
                
                <div style="text-align: center; margin-top: 30px;">
                    <a href="index.php" class="btn">Go to Login Page</a>
                </div>
            <?php endif; ?>
            
            <div class="warning-box" style="margin-top: 30px;">
                <h3>⚠️ Important Notes</h3>
                <ul>
                    <li>This setup script can be run multiple times - it will reset the database each time</li>
                    <li>All passwords are stored in PLAIN TEXT (intentionally insecure for testing)</li>
                    <li>The database file (database.db) will be created in the same directory</li>
                    <li>Make sure PHP has write permissions for this directory</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>ModSecurity Testing Website | For Educational Purposes Only</p>
            <p><a>trannhatbuilder</a></p>
        </div>
    </div>
</body>
</html>
