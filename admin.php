<?php
/**
 * ADMIN PAGE - System Information and File Management
 * 
 * WARNING: This code is intentionally insecure for ModSecurity testing purposes
 * DO NOT USE IN PRODUCTION!
 */

session_start();

// Initialize database connection
$db = null;
$users = [];
$products = [];

try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all users
    $stmt = $db->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all products
    $stmt = $db->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $db_error = "Database not initialized. Please run <a href='setup.php'>setup.php</a> first.";
}

// Get uploaded files
$upload_dir = 'uploads/';
$uploaded_files = [];
if (is_dir($upload_dir)) {
    $uploaded_files = array_diff(scandir($upload_dir), ['.', '..']);
}

// Get system information
$system_info = [
    'PHP Version' => phpversion(),
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'Server Name' => $_SERVER['SERVER_NAME'] ?? 'N/A',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Server Admin' => $_SERVER['SERVER_ADMIN'] ?? 'N/A',
    'Operating System' => php_uname(),
    'Current Directory' => getcwd(),
    'Script Filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'N/A',
];

$logged_in = isset($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Admin Panel</h1>
            <p>Vulnerability Testing Lab - Educational Purpose Only</p>
        </div>
        
        <nav>
            <a href="index.php">Login</a>
            <a href="search.php">Search</a>
            <a href="upload.php">Upload</a>
            <a href="command.php">Command</a>
            <a href="admin.php" class="active">Admin</a>
            <?php if ($logged_in): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php endif; ?>
        </nav>
        
        <div class="content">
            <?php if (isset($db_error)): ?>
                <div class="error-message">
                    <strong>❌ Database Error:</strong> <?php echo $db_error; ?>
                </div>
            <?php endif; ?>
            
            <!-- System Information -->
            <h2>System Information</h2>
            <table>
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($system_info as $key => $value): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                            <td><code><?php echo htmlspecialchars($value); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Database Statistics -->
            <h2 style="margin-top: 40px;">Database Statistics</h2>
            <div class="results-box">
                <p><strong>Total Users:</strong> <?php echo count($users); ?></p>
                <p><strong>Total Products:</strong> <?php echo count($products); ?></p>
                <p><strong>Database File:</strong> <code>database.db</code></p>
            </div>
            
            <!-- Users Table -->
            <?php if (count($users) > 0): ?>
                <h3 style="margin-top: 20px;">Registered Users</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><code><?php echo htmlspecialchars($user['password']); ?></code></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <!-- Products Table -->
            <?php if (count($products) > 0): ?>
                <h3 style="margin-top: 30px;">Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['description']); ?></td>
                                <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <!-- Uploaded Files -->
            <h2 style="margin-top: 40px;">Uploaded Files</h2>
            <?php if (count($uploaded_files) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Size</th>
                            <th>Modified</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uploaded_files as $file): ?>
                            <?php
                                $filepath = $upload_dir . $file;
                                $filesize = filesize($filepath);
                                $modified = date("Y-m-d H:i:s", filemtime($filepath));
                            ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($file); ?></code></td>
                                <td><?php echo number_format($filesize); ?> bytes</td>
                                <td><?php echo $modified; ?></td>
                                <td><a href="<?php echo htmlspecialchars($filepath); ?>" target="_blank">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="info-message">
                    <p>No files uploaded yet. Go to <a href="upload.php">Upload page</a> to upload files.</p>
                </div>
            <?php endif; ?>
            
            <!-- PHP Info Link -->
            <div class="warning-box" style="margin-top: 40px;">
                <h3>⚠️ Sensitive Information</h3>
                <p>This admin panel displays sensitive system information that should be protected in production environments.</p>
                <p><strong>Security issues:</strong></p>
                <ul>
                    <li>Exposes PHP version and configuration</li>
                    <li>Shows server paths and directory structure</li>
                    <li>Displays plain-text passwords in database</li>
                    <li>No authentication required to access admin panel</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>ModSecurity Testing Website | For Educational Purposes Only</p>
            <p>⚠️ DO NOT deploy this website on production servers!</p>
        </div>
    </div>
</body>
</html>
