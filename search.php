<?php
/**
 * SEARCH PAGE - VULNERABLE TO XSS (Cross-Site Scripting)
 * 
 * WARNING: This code is intentionally insecure for ModSecurity testing purposes
 * VULNERABILITY: Reflected XSS via direct output of user input
 * DO NOT USE IN PRODUCTION!
 */

session_start();

// Initialize database connection
$db = null;
$products = [];

try {
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $db_error = "Database not initialized. Please run <a href='setup.php'>setup.php</a> first.";
}

$search_query = '';
$search_results = [];

// Handle search submission
if (isset($_GET['q'])) {
    $search_query = $_GET['q'];
    
    if ($db) {
        try {
            // Safe database query (using prepared statement)
            $stmt = $db->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ?");
            $search_param = "%$search_query%";
            $stmt->execute([$search_param, $search_param]);
            $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $db_error = "Search error: " . $e->getMessage();
        }
    }
}

$logged_in = isset($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Product Search</h1>
            <p>Vulnerability Testing Lab - Educational Purpose Only</p>
        </div>
        
        <nav>
            <a href="index.php">Login</a>
            <a href="search.php" class="active">Search</a>
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
                <p><strong>VULNERABILITY: Reflected XSS (Cross-Site Scripting)</strong></p>
                <p>This page directly outputs user input without sanitization, allowing JavaScript injection.</p>
                <p><strong>Test payloads:</strong></p>
                <ul>
                    <li><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code> - Basic XSS</li>
                    <li><code>&lt;img src=x onerror=alert('XSS')&gt;</code> - Image-based XSS</li>
                    <li><code>&lt;svg onload=alert('XSS')&gt;</code> - SVG-based XSS</li>
                    <li><code>&lt;iframe src="javascript:alert('XSS')"&gt;</code> - iFrame XSS</li>
                    <li><code>&lt;body onload=alert('XSS')&gt;</code> - Event handler XSS</li>
                </ul>
            </div>
            
            <?php if (isset($db_error)): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong> <?php echo $db_error; ?>
                </div>
            <?php endif; ?>
            
            <h2>Search Products <span class="vulnerability-tag">XSS VULNERABLE</span></h2>
            
            <form method="GET" action="search.php">
                <div class="form-group">
                    <label for="search">Search Query:</label>
                    <input type="text" id="search" name="q" placeholder="Enter product name..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <button type="submit" class="btn">Search</button>
            </form>
            
            <?php if ($search_query): ?>
                <div class="results-box">
                    <h3>Search Results</h3>
                    
                    <!-- VULNERABILITY: Direct output without sanitization -->
                    <p>You searched for: <strong><?php echo $search_query; ?></strong></p>
                    <p>Found <?php echo count($search_results); ?> results</p>
                    
                    <?php if (count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                                        <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <!-- VULNERABILITY: Reflected XSS in "no results" message -->
                        <div class="info-message">
                            <p>No products found matching: <strong><?php echo $search_query; ?></strong></p>
                            <p>Try searching for: laptop, phone, tablet, headphones</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="info-message">
                    <strong>ℹ️ Available Products:</strong><br>
                    Try searching for: <code>laptop</code>, <code>phone</code>, <code>tablet</code>, <code>headphones</code>
                </div>
            <?php endif; ?>
            
            <div class="code-block" style="margin-top: 30px;">
                <strong>Vulnerable Code:</strong><br><br>
                <code>
                // Direct output without htmlspecialchars() - XSS VULNERABILITY!<br>
                echo "You searched for: &lt;strong&gt;" . $search_query . "&lt;/strong&gt;";<br>
                <br>
                // This allows JavaScript injection through user input<br>
                // Example: search.php?q=&lt;script&gt;alert('XSS')&lt;/script&gt;<br>
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
