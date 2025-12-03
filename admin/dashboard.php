<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();

// Handle maintenance mode toggle
if (isset($_POST['toggle_maintenance'])) {
    $current_mode = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'")->fetch();
    $new_mode = ($current_mode['setting_value'] === 'true') ? 'false' : 'true';
    $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'")->execute([$new_mode]);
    header('Location: dashboard.php');
    exit;
}

// Get maintenance mode status
$maintenance_status = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'")->fetch();
$is_maintenance_on = ($maintenance_status && $maintenance_status['setting_value'] === 'true');

// Get statistics
$stats = [
    'pending_bookings' => $db->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn(),
    'total_bookings' => $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'active_packages' => $db->query("SELECT COUNT(*) FROM packages WHERE is_active = TRUE")->fetchColumn(),
    'total_testimonials' => $db->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = TRUE")->fetchColumn()
];

// Get recent bookings
$recent_bookings = $db->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Get site settings
$settings_query = $db->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
while ($row = $settings_query->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Anab Driving School</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
            </div>
            <!-- Maintenance Mode Toggle -->
            <div class="content-box" style="background: <?php echo $is_maintenance_on ? '#fee2e2' : '#d1fae5'; ?>; border-left: 4px solid <?php echo $is_maintenance_on ? '#ef4444' : '#10b981'; ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h3 style="margin-bottom: 8px; color: #111;">
                            <?php echo $is_maintenance_on ? '🚧 Maintenance Mode: ON' : '✅ Website: Live'; ?>
                        </h3>
                        <p style="font-size: 14px; color: #666; margin: 0;">
                            <?php if ($is_maintenance_on): ?>
                                Your website is currently showing "Under Development" page to visitors. Admin can still access.
                            <?php else: ?>
                                Your website is live and accessible to all visitors.
                            <?php endif; ?>
                        </p>
                    </div>
                    <form method="POST" style="margin: 0;">
                        <button type="submit" 
                                name="toggle_maintenance" 
                                class="btn-primary" 
                                style="background: <?php echo $is_maintenance_on ? '#10b981' : '#ef4444'; ?>; white-space: nowrap;"
                                onclick="return confirm('<?php echo $is_maintenance_on ? 'Turn OFF maintenance mode and make website live?' : 'Turn ON maintenance mode? Visitors will see under development page.'; ?>')">
                            <?php echo $is_maintenance_on ? '✅ Go Live' : '🚧 Enable Maintenance'; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['pending_bookings']; ?></div>
                        <div class="stat-label">Pending Bookings</div>
                    </div>
                </div>

                <div class="stat-card stat-primary">
                    <div class="stat-icon">📅</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_bookings']; ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['active_packages']; ?></div>
                        <div class="stat-label">Active Packages</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $stats['total_testimonials']; ?></div>
                        <div class="stat-label">Testimonials</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="bookings.php" class="action-btn">
                        <span class="action-icon">📅</span>
                        <span>View Bookings</span>
                    </a>
                    <a href="packages.php" class="action-btn">
                        <span class="action-icon">📦</span>
                        <span>Manage Packages</span>
                    </a>
                    <a href="settings.php" class="action-btn">
                        <span class="action-icon">⚙️</span>
                        <span>Site Settings</span>
                    </a>
                    <a href="../index.php" target="_blank" class="action-btn">
                        <span class="action-icon">🌐</span>
                        <span>View Website</span>
                    </a>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="content-box">
                <div class="box-header">
                    <h2>Recent Bookings</h2>
                    <a href="bookings.php" class="btn-sm">View All</a>
                </div>
                
                <?php if (count($recent_bookings) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['mobile_number']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($booking['preferred_date'])); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($booking['status']); ?>">
                                                <?php echo $booking['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn-action">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No bookings yet.</p>
                <?php endif; ?>
            </div>

            <!-- Site Information -->
            <div class="info-grid">
                <div class="content-box">
                    <h3>Contact Information</h3>
                    <div class="info-list">
                        <div class="info-item">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($settings['phone_number'] ?? 'Not set'); ?>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong> <?php echo htmlspecialchars($settings['email'] ?? 'Not set'); ?>
                        </div>
                        <div class="info-item">
                            <strong>Service Area:</strong> <?php echo htmlspecialchars($settings['service_area'] ?? 'Not set'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>