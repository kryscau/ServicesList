<?php
include_once("../../_conf/global.php");
include_once("../../_inc/if.ynh-mode.php");

$security_edit_pwd_file = __DIR__ . '/../data/password_state.txt';
if (!file_exists($security_edit_pwd_file)) {
    file_put_contents($security_edit_pwd_file, 'false');
}

if (file_exists($security_edit_pwd_file) && trim(file_get_contents($security_edit_pwd_file)) === 'true') {
    echo '<html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Editing disabled</title>
            <style>
                body { font-family: sans-serif; text-align: center; margin-top: 50px; }
            </style>
            <script>
                setTimeout(function() {
                    window.location.href = "/";
                }, 5000);
            </script>
        </head>
        <body>
            <h1>✋ Editing temporarily disabled</h1>
            <p>Service editing is temporarily disabled by the administrator.</p>
            <p>You will be redirected to the home page in <strong>5 seconds</strong>.</p>
        </body>
        </html>';
    exit;
}

// Error/success message
$message = '';

// Form processing
if (isset($_POST['submit'])) {
    $new_password = $_POST['password'];
    
    if (empty($new_password)) {
        $message = '<div style="color: red; margin-bottom: 15px;">The password cannot be blank!</div>';
    } else {
        // Generate the hash with bcrypt
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        try {
            // Connecting to the database
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if the admin table exists
            $tables = $pdo->query("SHOW TABLES LIKE 'admin'")->fetchAll();
            
            if (count($tables) === 0) {
                // Create the admin table if it does not exist
                $pdo->exec("CREATE TABLE IF NOT EXISTS `admin` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `password_hash` varchar(255) NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                
                // Enter the new password
                $stmt = $pdo->prepare("INSERT INTO admin (password_hash) VALUES (?)");
                $stmt->execute([$hash]);
            } else {
                // Check if a record already exists
                $count = $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();
                
                if ($count > 0) {
                    // Update existing password
                    $stmt = $pdo->prepare("UPDATE admin SET password_hash = ? WHERE id = 1");
                    $stmt->execute([$hash]);
                } else {
                    // Enter a new password
                    $stmt = $pdo->prepare("INSERT INTO admin (password_hash) VALUES (?)");
                    $stmt->execute([$hash]);
                }
            }
            
            $message = '<div style="color: green; margin-bottom: 15px;">
                <p><strong>Password successfully updated!</strong></p>
                <p>Your new password: <strong>' . htmlspecialchars($new_password) . '</strong></p>
                <p>Generated hash: <code>' . $hash . '</code></p>
                <p><strong>IMPORTANT:</strong> Delete this file immediately after use! (for more security)</p>
            </div>';
            file_put_contents($security_edit_pwd_file, 'true');
            
        } catch (PDOException $e) {
            $message = '<div style="color: red; margin-bottom: 15px;">v: ' . $e->getMessage() . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update admin password</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        h1 {
            color: #4f46e5;
            margin-bottom: 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 20px;
        }
        button {
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4338ca;
        }
        .warning {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 10px 15px;
            margin: 20px 0;
            color: #b91c1c;
        }
        code {
            background-color: #f1f5f9;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <h1>Update admin password</h1>
    
    <div class="warning">
        <strong>ATTENTION:</strong> This file is for one-time use only. Delete it immediately after updating your password for added security!
    </div>
    
    <?php echo $message; ?>
    
    <div class="card">
        <form method="post">
            <div>
                <label for="password">New admin password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="submit">Update password</button>
        </form>
    </div>
    
    <div class="warning" style="margin-top: 20px;">
        <strong>RAPPEL:</strong> Don't forget to delete this file after use!
    </div>
</body>
</html>