<?php
include_once("./_conf/global.php");
include_once("./_conf/db.php");

// Retrieving services from the database
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY order_num ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the table does not yet exist, a default table is used.
    $services = [
        ['icon' => 'fa-solid fa-envelope', 'name' => 'Mail Server', 'url' => '#'],
        ['icon' => 'fa-solid fa-cloud', 'name' => 'Cloud Storage', 'url' => '#'],
        ['icon' => 'fa-solid fa-calendar', 'name' => 'Calendar', 'url' => '#'],
        ['icon' => 'fa-solid fa-comments', 'name' => 'Chat', 'url' => '#']
    ];
}

// Check if you are in edit mode
$edit_mode = isset($_GET['edit']);

// If you are in edit mode, redirect to edit-services
if ($edit_mode) {
    header('Location: admin/edit/services');
    exit;
}


$title = $config['site']['name'];
$description = "Grouping of services offered by ". $config['site']['name'] . ".";
$url = "https://". $config['site']['main_domain'] ."/services";
$meta_image = "https://". $config['site']['imgs_domain'] ."/-/WaWA7/FoRUmETa47.png/raw";
$robots = "index, follow";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Primary Meta Tags -->
	<title><?php echo $title; ?></title>
	<meta name="title" content="<?php echo $title; ?>" />
	<meta name="description" content="<?php echo $description; ?>" />
	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website" />
	<meta property="og:url" content="<?php echo $url; ?>" />
	<meta property="og:title" content="<?php echo $title; ?>" />
	<meta property="og:description" content="<?php echo $description; ?>" />
	<meta property="og:image" content="<?php echo $meta_image; ?>" />
	<!-- Twitter -->
	<meta property="twitter:card" content="summary_large_image" />
	<meta property="twitter:url" content="<?php echo $url; ?>" />
	<meta property="twitter:title" content="<?php echo $title; ?>" />
	<meta property="twitter:description" content="<?php echo $description; ?>" />
	<meta property="twitter:image" content="<?php echo $meta_image; ?>" />
	<meta name="robots" content="<?php echo $robots; ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/my.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1 class="title"><?php echo $title; ?></h1>
        <p class="subtitle">Public services managed by <a href="<?php echo $config['author']['bio']; ?>" title="See my Bio" target="_blank"><?php echo $config['author']['name']; ?></a></p>
        
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <a href="<?php echo htmlspecialchars($service['url']); ?>" title="<?php echo htmlspecialchars($service['name']) ?>" target="_blank" class="service-card">
                    <i class="service-icon <?php echo htmlspecialchars($service['icon']); ?>"></i>
                    <div class="service-name"><?php echo htmlspecialchars($service['name']); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <a href="?edit" class="admin-link">Admin</a>
</body>
</html>