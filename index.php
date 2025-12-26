<?php
/**
 * LOGIN PAGE - VULNERABLE TO SQL INJECTION
 * 
 * WARNING: This code is intentionally insecure for ModSecurity testing purposes
 * VULNERABILITY: SQL Injection via direct string concatenation
 * DO NOT USE IN PRODUCTION!
 */

session_start();

// Initialize database connection
$db = null;
try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $db_error = "Database not initialized. Please run <a href='setup.php'>setup.php</a> first.";
}

$error = '';
$success = '';

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($db) {
        // VULNERABILITY: SQL Injection - Direct concatenation without prepared statements
        // This allows attacks like: admin' OR '1'='1
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        
        try {
            $result = $db->query($query);
            $user = $result->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $success = "Login successful! Welcome " . htmlspecialchars($user['username']);
            } else {
                $error = "Invalid credentials. SQL Query: " . htmlspecialchars($query);
            }
        } catch(PDOException $e) {
            // VULNERABILITY: Detailed error messages expose SQL structure
            $error = "SQL Error: " . $e->getMessage() . "<br>Query: " . htmlspecialchars($query);
        }
    } else {
        $error = $db_error ?? "Database connection failed";
    }
}

$logged_in = isset($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 ModSecurity Testing Website</h1>
            <p>Vulnerability Testing Lab - Educational Purpose Only</p>
        </div>
        
        <nav>
            <a href="index.php" class="active">Login</a>
            <a href="search.php">Search</a>
            <a href="upload.php">Upload</a>
            <a href="command.php">Command</a>
            <a href="admin.php">Admin</a>
            <?php if ($logged_in): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php endif; ?>
        </nav>
        
        <div class="content">
            <div class="warning-box">
                <h3>⚠️ Security Warning</h3>
                <p><strong>VULNERABILITY: SQL Injection</strong></p>
                <p>This page is intentionally vulnerable for ModSecurity WAF testing. The login form does not use prepared statements.</p>
                <p><strong>Test payloads:</strong></p>
                <ul>
                    <li><code>admin' OR '1'='1</code> - SQL Injection bypass</li>
                    <li><code>admin' --</code> - Comment-based injection</li>
                    <li><code>' OR 1=1 --</code> - Classic bypass</li>
                    <li><code>admin' UNION SELECT NULL--</code> - UNION-based injection</li>
                </ul>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong><br>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <strong>✅ Success:</strong><br>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$logged_in): ?>
                <h2>Login Form <span class="vulnerability-tag">SQL INJECTION</span></h2>
                
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" placeholder="Enter username" required>
                        <small style="color: #666;">Default: admin</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <small style="color: #666;">Default: password123</small>
                    </div>
                    
                    <button type="submit" name="login" class="btn">Login</button>
                </form>
                
                <div class="info-message" style="margin-top: 20px;">
                    <strong>ℹ️ Default Credentials:</strong><br>
                    Username: <code>admin</code> | Password: <code>password123</code><br>
                    Username: <code>user</code> | Password: <code>user123</code>
                </div>
            <?php else: ?>
                <div class="success-message">
                    <h2>✅ You are logged in!</h2>
                    <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
                    <p>You can now access all vulnerable pages to test ModSecurity WAF.</p>
                    <br>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            <?php endif; ?>
            
            <div class="code-block" style="margin-top: 30px;">
                <strong>Vulnerable Code:</strong><br><br>
                <code>
                // Direct SQL concatenation - NO PROTECTION!<br>
                $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";<br>
                $result = $db->query($query);<br>
                </code>
            </div>
        </div>
        
        <div class="footer">
            <p>ModSecurity Testing Website | For Educational Purposes Only</p>
            <p>⚠️ DO NOT deploy this website on production servers!</p>
        </div>
    </div>
</body>
</html>
