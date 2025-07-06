<?php
session_start();
include_once("../../_conf/global.php");
include_once("../../_conf/db.php");
include_once("../../_inc/if.ynh-mode.php");

// Error and success messages
$error = '';
$success = '';

// Check if the user is logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;


// Processing the login form
if (isset($_POST['login'])) {
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->query("SELECT password_hash FROM admin LIMIT 1");
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $is_logged_in = true;
            $success = "Connection successful!";
        } else {
            $error = "Incorrect password!";
        }
    } catch (PDOException $e) {
        $error = "Error while verifying password: " . $e->getMessage();
    }
}

// Processing the logout form
if (isset($_POST['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    $is_logged_in = false;
    $success = "Disconnected successfully!";
}

// Processing the service addition form
if ($is_logged_in && isset($_POST['add_service'])) {
    $name = $_POST['name'];
    $icon = $_POST['icon'];
    $url = $_POST['url'];
    
    try {
        // Find the next order
        $stmt = $pdo->query("SELECT MAX(order_num) as max_order FROM services");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_order = ($result['max_order'] ?? 0) + 1;
        
        $stmt = $pdo->prepare("INSERT INTO services (name, icon, url, order_num) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $icon, $url, $next_order]);
        $success = "Service added successfully!";
    } catch (PDOException $e) {
        $error = "Error while adding the service: " . $e->getMessage();
    }
}

// Processing the service change form
if ($is_logged_in && isset($_POST['edit_service'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $icon = $_POST['icon'];
    $url = $_POST['url'];
    
    try {
        $stmt = $pdo->prepare("UPDATE services SET name = ?, icon = ?, url = ? WHERE id = ?");
        $stmt->execute([$name, $icon, $url, $id]);
        $success = "Service successfully modified!";
    } catch (PDOException $e) {
        $error = "Error while modifying the service: " . $e->getMessage();
    }
}

// Processing the service cancellation form
if ($is_logged_in && isset($_POST['delete_service'])) {
    $id = $_POST['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Service successfully deleted!";
    } catch (PDOException $e) {
        $error = "Error while deleting the service: " . $e->getMessage();
    }
}

// Processing of the service reorganization form
if ($is_logged_in && isset($_POST['reorder'])) {
    $ids = $_POST['service_ids'];
    
    try {
        $pdo->beginTransaction();
        
        foreach ($ids as $order => $id) {
            $stmt = $pdo->prepare("UPDATE services SET order_num = ? WHERE id = ?");
            $stmt->execute([$order + 1, $id]);
        }
        
        $pdo->commit();
        $success = "Services successfully reorganized!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error during service reorganization: " . $e->getMessage();
    }
}


// Retrieving services from the database
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY order_num ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $services = [];
    $error = "Error retrieving services: " . $e->getMessage();
}

$title = $config['site']['name'];
$description = "Internal system administration area of the ". $config['site']['name'] ." website.";
$url = "https://". $config['site']['main_domain'] ."/services?edit";
$meta_image = "https://". $config['site']['imgs_domain'] ."/url/kvs-meta-img";
$robots = "noindex, nofollow";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Primary Meta Tags -->
	<title><?php echo $title; ?> - Admin</title>
	<meta name="title" content="<?php echo $title; ?> - Admin" />
	<meta name="description" content="<?php echo $description; ?>" />
	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website" />
	<meta property="og:url" content="<?php echo $url; ?>" />
	<meta property="og:title" content="<?php echo $title; ?> - Admin" />
	<meta property="og:description" content="<?php echo $description; ?>" />
	<meta property="og:image" content="<?php echo $meta_image; ?>" />
	<!-- Twitter -->
	<meta property="twitter:card" content="summary_large_image" />
	<meta property="twitter:url" content="<?php echo $url; ?>" />
	<meta property="twitter:title" content="<?php echo $title; ?> - Admin" />
	<meta property="twitter:description" content="<?php echo $description; ?>" />
	<meta property="twitter:image" content="<?php echo $meta_image; ?>" />
	<meta name="robots" content="<?php echo $robots; ?>" />
    <link rel="stylesheet" type="text/css" href="/admin/assets/css/edit-services.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery for drag and drop functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title"><?php echo $title; ?> Admin</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!$is_logged_in): ?>
            <!-- Login form -->
            <div class="card">
                <h2>Login</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login">Log in</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Logout form -->
            <form method="post" style="text-align: right; margin-bottom: 1rem;">
                <button type="submit" name="logout" class="btn-secondary">Log out</button>
            </form>
            
            <!-- Service Addition Form -->
            <div class="card">
                <h2>Add a service</h2>
                <form method="post">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon</label>
                        <div class="icon-input-group">
                            <input type="text" id="icon" name="icon" class="icon-input" placeholder="fa-solid fa-envelope" required>
                            <i id="icon-preview" class="icon-preview fa-solid fa-envelope"></i>
                            <button type="button" class="icon-select-btn" onclick="openIconSelector('icon', 'icon-preview')">Choose</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="text" id="url" name="url" placeholder="https://example.com" required>
                    </div>
                    <button type="submit" name="add_service">Add</button>
                </form>
            </div>
            
            <!-- List of services -->
            <div class="card">
                <h2>Services</h2>
                <form method="post" id="reorder-form">
                    <table>
                        <thead>
                            <tr>
                                <th width="5%"></th>
                                <th width="10%">Icon</th>
                                <th width="25%">Name</th>
                                <th width="40%">URL</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable">
                            <?php foreach ($services as $service): ?>
                                <tr data-id="<?php echo $service['id']; ?>">
                                    <td><i class="fas fa-grip-lines drag-handle"></i></td>
                                    <td><i class="<?php echo htmlspecialchars($service['icon']); ?> service-icon"></i></td>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td><?php echo htmlspecialchars($service['url']); ?></td>
                                    <td class="actions">
                                        <button type="button" class="btn-secondary edit-btn" 
                                                data-id="<?php echo $service['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($service['name']); ?>"
                                                data-icon="<?php echo htmlspecialchars($service['icon']); ?>"
                                                data-url="<?php echo htmlspecialchars($service['url']); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" name="delete_service" class="btn-danger" onclick="return confirm('Are you sure you want to delete this service?');">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <input type="hidden" name="reorder" value="1">
                    <div id="service-ids-container"></div>
                    <button type="submit" id="save-order" style="margin-top: 1rem;">Save order</button>
                </form>
            </div>
            
            <!-- Edit modal -->
            <div id="edit-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 2rem; border-radius: 0.5rem; width: 90%; max-width: 500px;">
                    <h2>Edit the service</h2>
                    <form method="post" id="edit-form">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="form-group">
                            <label for="edit-name">Name</label>
                            <input type="text" id="edit-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-icon">Icon</label>
                            <div class="icon-input-group">
                                <input type="text" id="edit-icon" name="icon" class="icon-input" required>
                                <i id="edit-icon-preview" class="icon-preview"></i>
                                <button type="button" class="icon-select-btn" onclick="openIconSelector('edit-icon', 'edit-icon-preview')">Choose</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit-url">URL</label>
                            <input type="text" id="edit-url" name="url" required>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <button type="button" id="cancel-edit" class="btn-secondary">Cancel</button>
                            <button type="submit" name="edit_service">Save</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Icon selector -->
            <div id="icon-selector-modal" class="icon-selector-modal">
                <div class="icon-selector-content">
                    <div class="icon-selector-header">
                        <h2>Select an icon</h2>
                        <button type="button" class="icon-selector-close" onclick="closeIconSelector()">&times;</button>
                    </div>
                    <input type="text" id="icon-search" class="icon-search" placeholder="Search for an icon...">
                    <div class="icon-categories">
                        <button type="button" class="icon-category active" data-category="all">All</button>
                        <button type="button" class="icon-category" data-category="solid">Solid</button>
                        <button type="button" class="icon-category" data-category="regular">Regular</button>
                        <button type="button" class="icon-category" data-category="brands">Brands</button>
                    </div>
                    <div id="icon-grid" class="icon-grid">
                        <!-- The icons will be loaded here by JavaScript. -->
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="/services" class="back-link">&larr; Back to home page</a>
    </div>
    
    <script src="/admin/assets/js/icon-selector.js"></script>
</body>
</html>