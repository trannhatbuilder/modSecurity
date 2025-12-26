<?php
/**
 * FILE UPLOAD PAGE - VULNERABLE TO ARBITRARY FILE UPLOAD
 * 
 * WARNING: This code is intentionally insecure for ModSecurity testing purposes
 * VULNERABILITY: No file type validation, allowing upload of malicious files
 * DO NOT USE IN PRODUCTION!
 */

session_start();

$upload_dir = 'uploads/';
$message = '';
$uploaded_file = '';

// Create uploads directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // VULNERABILITY: No file type validation!
    // This allows uploading PHP files, executables, etc.
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $original_name = $file['name'];
        $tmp_name = $file['tmp_name'];
        
        // VULNERABILITY: Direct use of user-supplied filename
        // This can lead to directory traversal attacks
        $destination = $upload_dir . $original_name;
        
        if (move_uploaded_file($tmp_name, $destination)) {
            $message = "✅ File uploaded successfully!";
            $uploaded_file = $destination;
        } else {
            $message = "❌ Failed to move uploaded file.";
        }
    } else {
        $message = "❌ Upload error: " . $file['error'];
    }
}

// List uploaded files
$uploaded_files = [];
if (is_dir($upload_dir)) {
    $uploaded_files = array_diff(scandir($upload_dir), ['.', '..']);
}

$logged_in = isset($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📤 File Upload</h1>
            <p>Vulnerability Testing Lab - Educational Purpose Only</p>
        </div>
        
        <nav>
            <a href="index.php">Login</a>
            <a href="search.php">Search</a>
            <a href="upload.php" class="active">Upload</a>
            <a href="command.php">Command</a>
            <a href="admin.php">Admin</a>
            <?php if ($logged_in): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php endif; ?>
        </nav>
        
        <div class="content">
            <div class="warning-box">
                <h3>⚠️ Security Warning</h3>
                <p><strong>VULNERABILITY: Arbitrary File Upload</strong></p>
                <p>This page accepts ANY file type without validation, including malicious files.</p>
                <p><strong>Potential exploits:</strong></p>
                <ul>
                    <li>Upload PHP backdoor/webshell files (.php, .phtml, .php5)</li>
                    <li>Upload executable files (.exe, .sh, .bat)</li>
                    <li>Upload HTML files with XSS payloads</li>
                    <li>Directory traversal via filename (../../etc/passwd)</li>
                    <li>Overwrite existing files with same name</li>
                </ul>
            </div>
            
            <?php if ($message): ?>
                <div class="<?php echo strpos($message, '✅') !== false ? 'success-message' : 'error-message'; ?>">
                    <?php echo $message; ?>
                    <?php if ($uploaded_file): ?>
                        <br><strong>File location:</strong> <code><?php echo htmlspecialchars($uploaded_file); ?></code>
                        <br><strong>Access URL:</strong> <a href="<?php echo htmlspecialchars($uploaded_file); ?>" target="_blank"><?php echo htmlspecialchars($uploaded_file); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <h2>Upload File <span class="vulnerability-tag">UNRESTRICTED UPLOAD</span></h2>
            
            <form method="POST" action="upload.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Choose File:</label>
                    <input type="file" id="file" name="file" required>
                    <small style="color: #666;">⚠️ NO file type restrictions! Any file can be uploaded.</small>
                </div>
                
                <button type="submit" class="btn">Upload File</button>
            </form>
            
            <?php if (count($uploaded_files) > 0): ?>
                <div class="results-box">
                    <h3>Uploaded Files (<?php echo count($uploaded_files); ?>)</h3>
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
                </div>
            <?php else: ?>
                <div class="info-message" style="margin-top: 20px;">
                    <strong>ℹ️ No files uploaded yet</strong><br>
                    Try uploading any file - there are no restrictions!
                </div>
            <?php endif; ?>
            
            <div class="code-block" style="margin-top: 30px;">
                <strong>Vulnerable Code:</strong><br><br>
                <code>
                // NO FILE TYPE VALIDATION!<br>
                $destination = $upload_dir . $_FILES['file']['name'];<br>
                move_uploaded_file($_FILES['file']['tmp_name'], $destination);<br>
                <br>
                // Allows uploading:<br>
                // - PHP webshells: shell.php<br>
                // - Malicious executables: malware.exe<br>
                // - Path traversal: ../../etc/passwd<br>
                </code>
            </div>
            
            <div class="warning-box" style="margin-top: 20px; background: #f8d7da; border-color: #dc3545;">
                <h3>💀 Example Webshell Content</h3>
                <p>An attacker could upload a file named <code>shell.php</code> with this content:</p>
                <div class="code-block">
                    <code>
                    &lt;?php<br>
                    &nbsp;&nbsp;// Simple PHP webshell<br>
                    &nbsp;&nbsp;system($_GET['cmd']);<br>
                    ?&gt;<br>
                    <br>
                    // Then access: uploads/shell.php?cmd=whoami<br>
                    </code>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>ModSecurity Testing Website | For Educational Purposes Only</p>
            <p>⚠️ DO NOT deploy this website on production servers!</p>
        </div>
    </div>
</body>
</html>
