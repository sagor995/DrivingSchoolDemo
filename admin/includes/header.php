<?php
// Get logo from settings
$db = Database::getInstance()->getConnection();
$logo_query = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'logo_path' LIMIT 1");
$logo_result = $logo_query->fetch();
$logo_path = $logo_result ? '../' . $logo_result['setting_value'] : '';
?>
<header class="admin-header">
    <div class="header-left">
        <?php if (!empty($logo_path) && file_exists($logo_path)): ?>
            <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" style="height: 36px; width: auto; object-fit: contain;">
        <?php else: ?>
            <div class="admin-logo">A</div>
        <?php endif; ?>
        <div class="header-title">Anab Driving School</div>
    </div>
    <div class="header-right">
        <div class="admin-user">
            <span>👤</span>
            <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
        </div>
        <a href="./logout.php" class="btn-logout">Logout</a>
    </div>
</header>