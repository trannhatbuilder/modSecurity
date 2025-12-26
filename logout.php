<?php
/**
 * LOGOUT PAGE
 * 
 * Destroys the session and logs out the user
 */

session_start();

// Destroy all session data
$_SESSION = [];

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="3;url=index.php">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👋 Logout</h1>
            <p>Vulnerability Testing Lab - Educational Purpose Only</p>
        </div>
        
        <nav>
            <a href="index.php">Login</a>
            <a href="search.php">Search</a>
            <a href="upload.php">Upload</a>
            <a href="command.php">Command</a>
            <a href="admin.php">Admin</a>
        </nav>
        
        <div class="content">
            <div class="success-message">
                <h2>✅ Logged Out Successfully!</h2>
                <p>You have been logged out from the system.</p>
                <p>Redirecting to login page in 3 seconds...</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn">Go to Login Page</a>
            </div>
        </div>
        
        <div class="footer">
            <p>ModSecurity Testing Website | For Educational Purposes Only</p>
            <p>⚠️ DO NOT deploy this website on production servers!</p>
        </div>
    </div>
</body>
</html>
