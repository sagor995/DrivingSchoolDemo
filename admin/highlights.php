<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';

// Handle add/edit
if (isset($_POST['save_highlight'])) {
    $id = intval($_POST['id'] ?? 0);
    $highlight_text = sanitize_input($_POST['highlight_text']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id > 0) {
        $stmt = $db->prepare("UPDATE key_highlights SET highlight_text=?, is_active=? WHERE id=?");
        $stmt->execute([$highlight_text, $is_active, $id]);
        $message = '<div class="alert alert-success">Highlight updated successfully!</div>';
    } else {
        $stmt = $db->prepare("INSERT INTO key_highlights (highlight_text, is_active) VALUES (?, ?)");
        $stmt->execute([$highlight_text, $is_active]);
        $message = '<div class="alert alert-success">Highlight added successfully!</div>';
    }
}

// Handle delete
if (isset($_POST['delete_highlight'])) {
    $id = intval($_POST['id']);
    $db->prepare("DELETE FROM key_highlights WHERE id=?")->execute([$id]);
    $message = '<div class="alert alert-success">Highlight deleted successfully!</div>';
}

$highlights = $db->query("SELECT * FROM key_highlights ORDER BY display_order ASC, id ASC")->fetchAll();
$edit_highlight = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM key_highlights WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_highlight = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Key Highlights - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>✨ Manage Key Highlights</h1>
                <p>Edit the bullet points shown in the About section of your website</p>
            </div>

            <?php echo $message; ?>

            <div class="content-box">
                <h2><?php echo $edit_highlight ? 'Edit Highlight' : 'Add New Highlight'; ?></h2>
                <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
                    These highlights appear as bullet points in the "Key Highlights" section on your website's About page.
                </p>
                
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_highlight['id'] ?? 0; ?>">
                    
                    <div class="form-group">
                        <label>Highlight Text *</label>
                        <input type="text" 
                               name="highlight_text" 
                               value="<?php echo htmlspecialchars($edit_highlight['highlight_text'] ?? ''); ?>" 
                               required 
                               placeholder="e.g., DVSA-qualified instructor">
                        <small style="display: block; margin-top: 5px; color: #666;">
                            Keep it concise - one key point per highlight (aim for under 100 characters)
                        </small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   <?php echo isset($edit_highlight) ? ($edit_highlight['is_active'] ? 'checked' : '') : 'checked'; ?>>
                            Active (visible on website)
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_highlight" class="btn-primary">
                            <?php echo $edit_highlight ? 'Update' : 'Add'; ?> Highlight
                        </button>
                        <?php if ($edit_highlight): ?>
                            <a href="highlights.php" class="btn-action">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="content-box">
                <h2>All Key Highlights</h2>
                <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                    These appear in the About section of your website. Drag to reorder (feature coming soon).
                </p>
                
                <?php if (count($highlights) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">Order</th>
                                    <th>Highlight Text</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($highlights as $h): ?>
                                    <tr>
                                        <td style="text-align: center; color: #999;">
                                            #<?php echo $h['display_order'] ?: $h['id']; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($h['highlight_text']); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $h['is_active'] ? 'confirmed' : 'cancelled'; ?>">
                                                <?php echo $h['is_active'] ? 'Active' : 'Hidden'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $h['id']; ?>" class="btn-action">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this highlight?');">
                                                <input type="hidden" name="id" value="<?php echo $h['id']; ?>">
                                                <button type="submit" name="delete_highlight" class="btn-action" style="background: #fee2e2; color: #991b1b;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>No highlights yet. Add your first highlight above to showcase your strengths!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Preview Section -->
            <?php if (count($highlights) > 0): ?>
                <div class="content-box" style="background: #f0f9ff; border-left: 4px solid #3b82f6;">
                    <h3>📋 Preview - How They Appear on Website</h3>
                    <div style="margin-top: 15px;">
                        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <h4 style="margin: 0 0 15px 0; color: #1a2332;">Key Highlights</h4>
                            <ul style="list-style-type: disc; padding-left: 25px; margin: 0; font-size: 14px; color: #444; line-height: 1.8;">
                                <?php foreach ($highlights as $h): ?>
                                    <?php if ($h['is_active']): ?>
                                        <li><?php echo htmlspecialchars($h['highlight_text']); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tips Section -->
            <div class="content-box" style="background: #fffbeb; border-left: 4px solid #ffb300;">
                <h3>💡 Tips for Great Highlights</h3>
                <ul style="margin: 10px 0 0 20px; font-size: 14px; color: #444; line-height: 1.8;">
                    <li><strong>Be Specific:</strong> "DVSA-qualified instructor" is better than "Qualified instructor"</li>
                    <li><strong>Highlight Benefits:</strong> Focus on what students gain, not just features</li>
                    <li><strong>Keep it Short:</strong> One clear point per highlight</li>
                    <li><strong>Use Numbers:</strong> "10+ years experience" is more compelling than "Experienced"</li>
                    <li><strong>Show Credibility:</strong> Mention qualifications, certifications, or achievements</li>
                    <li><strong>Address Concerns:</strong> "Flexible lesson times" addresses scheduling worries</li>
                </ul>
            </div>

            <!-- Suggested Highlights -->
            <div class="content-box">
                <h3>📝 Suggested Highlights (Copy & Customize)</h3>
                <div style="display: grid; gap: 10px; margin-top: 15px;">
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ DVSA-qualified instructor with Grade A rating</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Years of local driving experience around Bolton</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Flexible lesson times around work, college and school runs</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Support with theory test preparation and hazard perception</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Clear progress tracking from first drive to test day</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Patient approach perfect for nervous learners</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ High first-time pass rate with structured lesson plans</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Modern, dual-control vehicles (manual and automatic)</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Pick-up and drop-off from home, work or college</strong>
                    </div>
                    <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #10b981;">
                        <strong>✓ Specialist experience with international licence holders</strong>
                    </div>
                </div>
                <p style="margin-top: 15px; font-size: 13px; color: #666; font-style: italic;">
                    Click "Add New Highlight" above and paste any of these (customize to match your actual services)
                </p>
            </div>
        </main>
    </div>
</body>
</html>