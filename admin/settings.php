<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';

// Handle settings update
if (isset($_POST['update_settings'])) {
    foreach ($_POST as $key => $value) {
        if ($key !== 'update_settings') {
            $clean_value = sanitize_input($value);
            $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$clean_value, $key]);
        }
    }
    $message = '<div class="alert alert-success">Settings updated successfully!</div>';
}

// Handle logo upload
if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
    $file = $_FILES['logo'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (in_array($file['type'], $allowed) && $file['size'] <= MAX_FILE_SIZE) {
        // Get old logo path to delete it
        $old_logo_query = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'logo_path'");
        $old_logo = $old_logo_query->fetch();
        
        // Delete old logo file if it exists
        if ($old_logo && !empty($old_logo['setting_value'])) {
            $old_file = '../' . $old_logo['setting_value'];
            if (file_exists($old_file) && is_file($old_file)) {
                @unlink($old_file); // Delete old file
            }
        }
        
        // Upload new logo
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $ext;
        $destination = UPLOAD_DIR . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'logo_path'");
            $stmt->execute(['uploads/' . $filename]);
            $message = '<div class="alert alert-success">Logo uploaded successfully! Old logo has been removed.</div>';
        }
    } else {
        $message = '<div class="alert alert-error">Invalid file type or size too large (max 5MB).</div>';
    }
}

// Get all settings
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
    <title>Site Settings - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Site Settings</h1>
                <p>Update your website content and contact information</p>
            </div>

            <?php echo $message; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- Business Information -->
                <div class="content-box">
                    <h2>Business Information</h2>
                    
                    <div class="form-row">
                        <div>
                            <label>Business Name</label>
                            <input type="text" name="business_name" value="<?php echo htmlspecialchars($settings['business_name'] ?? ''); ?>">
                        </div>
                        <div>
                            <label>Service Area</label>
                            <input type="text" name="service_area" value="<?php echo htmlspecialchars($settings['service_area'] ?? ''); ?>" placeholder="e.g., Bolton, Manchester">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label>Phone Number</label>
                            <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($settings['phone_number'] ?? ''); ?>">
                        </div>
                        <div>
                            <label>WhatsApp Number</label>
                            <input type="tel" name="whatsapp_number" value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                        </div>
                        <div>
                            <label>Notification Email (for bookings)</label>
                            <input type="email" name="notification_email" value="<?php echo htmlspecialchars($settings['notification_email'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lesson Times</label>
                        <input type="text" name="lesson_times" value="<?php echo htmlspecialchars($settings['lesson_times'] ?? ''); ?>" placeholder="e.g., Mon–Fri evenings • Sat–Sun daytime">
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="content-box">
                    <h2>Logo</h2>
                    <?php if (!empty($settings['logo_path'])): ?>
                        <div style="margin-bottom: 16px;">
                            <p><strong>Current Logo:</strong></p>
                            <img src="../<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Logo" style="max-width: 200px; border: 1px solid #ddd; padding: 10px; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Upload New Logo (JPG, PNG, GIF, WebP - Max 5MB)</label>
                        <input type="file" name="logo" accept="image/*">
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="content-box">
                    <h2>Hero Section (Homepage Top)</h2>
                    
                    <div class="form-group">
                        <label>Hero Tag Line</label>
                        <input type="text" name="hero_tag" value="<?php echo htmlspecialchars($settings['hero_tag'] ?? ''); ?>" placeholder="e.g., DVSA-Style Professional Training">
                    </div>

                    <div class="form-group">
                        <label>Main Headline</label>
                        <input type="text" name="hero_title" value="<?php echo htmlspecialchars($settings['hero_title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Hero Subtitle</label>
                        <textarea name="hero_subtitle" rows="3"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- About Section -->
                <div class="content-box">
                    <h2>About Section</h2>
                    
                    <div class="form-group">
                        <label>About Title</label>
                        <input type="text" name="about_title" value="<?php echo htmlspecialchars($settings['about_title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>About Description</label>
                        <textarea name="about_description" rows="6"><?php echo htmlspecialchars($settings['about_description'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Terms & Policies -->
                <div class="content-box">
                    <h2>Terms & Policies</h2>
                    
                    <div class="form-group">
                        <label>Terms & Conditions</label>
                        <textarea name="terms_conditions" rows="6"><?php echo htmlspecialchars($settings['terms_conditions'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Privacy Policy</label>
                        <textarea name="privacy_policy" rows="6"><?php echo htmlspecialchars($settings['privacy_policy'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="content-box">
                    <h2>Social Media Links (Optional)</h2>
                    
                    <div class="form-row">
                        <div>
                            <label>Facebook URL</label>
                            <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/yourpage">
                        </div>
                        <div>
                            <label>Instagram URL</label>
                            <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/yourpage">
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance Mode -->
                <div class="content-box" style="border-left: 4px solid #f59e0b;">
                    <h2>🚧 Maintenance Mode</h2>
                    <p style="margin-bottom: 15px; font-size: 14px; color: #666;">
                        Enable this to show "Under Development" page to visitors while you work on the site. Admin can still access.
                    </p>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="maintenance_mode" value="true" <?php echo ($settings['maintenance_mode'] ?? 'false') === 'true' ? 'checked' : ''; ?>>
                            <strong>Enable Maintenance Mode</strong> (Show "Under Development" page)
                        </label>
                    </div>

                    <div class="form-group">
                        <label>Maintenance Message</label>
                        <textarea name="maintenance_message" rows="3" placeholder="Website is under development. We will be back soon!"><?php echo htmlspecialchars($settings['maintenance_message'] ?? 'Website is under development. We will be back soon!'); ?></textarea>
                        <small style="display: block; margin-top: 5px; color: #666;">
                            This message will be shown to visitors when maintenance mode is ON.
                        </small>
                    </div>
                </div>

                <div class="content-box">
                    <button type="submit" name="update_settings" class="btn-primary">Save All Settings</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>