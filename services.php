<?php
include_once("./_conf/global.php");
include_once("./_conf/db.php");

$host = $_SERVER['HTTP_HOST'] ?? 'localhost'; // fallback
$host = preg_replace('/:[0-9]+$/', '', $host); // remove port if present

// Services list initialization
$services = [];

// Use YunoHost portal API if enabled
if (!empty($config['site']['ynh_data']) && $config['site']['ynh_data'] === true) {
    $ynh_api_url = "https://{$host}/yunohost/portalapi/public";
    $json = @file_get_contents($ynh_api_url);

    if ($json !== false) {
        $data = json_decode($json, true);
        if (isset($data['apps']) && is_array($data['apps'])) {
            foreach ($data['apps'] as $app) {
                if (!empty($app['public']) && !empty($app['url']) && !empty($app['label'])) {
                    $services[] = [
                        'icon' => '',
                        'name' => $app['label'],
                        'url'  => 'https://' . $app['url'],
                        'logo' => $app['logo'] ?? null
                    ];
                }
            }
        }
    }
}

// Fallback to DB or static data if needed
if (empty($services)) {
    if (!empty($config['site']['ynh_data']) && $config['site']['ynh_data'] === false) {
        try {
            $stmt = $pdo->query("SELECT * FROM services ORDER BY order_num ASC");
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $services = [
                ['icon' => 'fa-solid fa-envelope', 'name' => 'Mail Server', 'url' => '#'],
                ['icon' => 'fa-solid fa-cloud', 'name' => 'Cloud Storage', 'url' => '#'],
                ['icon' => 'fa-solid fa-calendar', 'name' => 'Calendar', 'url' => '#'],
                ['icon' => 'fa-solid fa-comments', 'name' => 'Chat', 'url' => '#']
            ];
        }
    }
}

// Edit mode check
$edit_mode = isset($_GET['edit']);
if ($edit_mode) {
    if (!empty($config['site']['ynh_data']) && $config['site']['ynh_data'] === true) {
        include_once("./_inc/if.ynh-mode.php");
    } else {
        header('Location: admin/edit/services');
        exit;
    }
}

$title = $config['site']['name'];
$description = "Grouping of services offered by " . $config['site']['name'] . ".";
$url = "https://" . $config['site']['main_domain'] . "/services";
$meta_image = "https://" . $config['site']['imgs_domain'] . "/url/kvs-meta-img";
$robots = "index, follow";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($title); ?>" />
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo htmlspecialchars($url); ?>" />
    <meta property="og:title" content="<?php echo htmlspecialchars($title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($meta_image); ?>" />
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="<?php echo htmlspecialchars($url); ?>" />
    <meta property="twitter:title" content="<?php echo htmlspecialchars($title); ?>" />
    <meta property="twitter:description" content="<?php echo htmlspecialchars($description); ?>" />
    <meta property="twitter:image" content="<?php echo htmlspecialchars($meta_image); ?>" />
    <meta name="robots" content="<?php echo htmlspecialchars($robots); ?>" />
    <link rel="stylesheet" type="text/css" href="assets/css/my.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body.ynh-mode .service-icon-img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            margin-bottom: 6px;
        }
    </style>
</head>
<body class="<?php echo $config['site']['ynh_data'] ? 'ynh-mode' : ''; ?>">
    <div class="container">
        <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>
        <p class="subtitle">
            Public services managed by <a href="<?php echo htmlspecialchars($config['author']['bio']); ?>" title="See my Bio" target="_blank"><?php echo htmlspecialchars($config['author']['name']); ?></a>
        </p>

        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <a href="<?php echo htmlspecialchars($service['url']); ?>" title="<?php echo htmlspecialchars($service['name']) ?>" target="_blank" class="service-card">
                    <?php if (!empty($service['logo'])): ?>
                        <img src="<?php echo htmlspecialchars($service['logo']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>" class="service-icon-img">
                    <?php elseif (!empty($service['icon'])): ?>
                        <i class="service-icon <?php echo htmlspecialchars($service['icon']); ?>"></i>
                    <?php endif; ?>
                    <div class="service-name"><?php echo htmlspecialchars($service['name']); ?></div>
                </a>
            <?php endforeach; ?>
            <a href="//<?php echo $config['site']['imgs_domain']; ?>/url/kvs-yunohost" title="YunoHost" target="_blank" class="service-card">
                <i class="service-icon fa-solid fa-external-link"></i>
                <div class="service-name">YunoHost</div>
            </a>
        </div>
    </div>

    <a href="?edit" class="admin-link">Admin</a>
</body>
</html>
