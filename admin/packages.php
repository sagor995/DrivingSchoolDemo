<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';

// Handle add/edit package
if (isset($_POST['save_package'])) {
    $id = intval($_POST['id'] ?? 0);
    $package_name = sanitize_input($_POST['package_name']);
    $price = floatval($_POST['price']);
    $duration = sanitize_input($_POST['duration']);
    $description = sanitize_input($_POST['description']);
    $car_type = sanitize_input($_POST['car_type']);
    $features = sanitize_input($_POST['features']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id > 0) {
        // Update
        $stmt = $db->prepare("UPDATE packages SET package_name=?, price=?, duration=?, description=?, car_type=?, features=?, is_featured=?, is_active=? WHERE id=?");
        $stmt->execute([$package_name, $price, $duration, $description, $car_type, $features, $is_featured, $is_active, $id]);
        $message = '<div class="alert alert-success">Package updated successfully!</div>';
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO packages (package_name, price, duration, description, car_type, features, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$package_name, $price, $duration, $description, $car_type, $features, $is_featured, $is_active]);
        $message = '<div class="alert alert-success">Package added successfully!</div>';
    }
}

// Handle delete
if (isset($_POST['delete_package'])) {
    $id = intval($_POST['id']);
    $stmt = $db->prepare("DELETE FROM packages WHERE id=?");
    $stmt->execute([$id]);
    $message = '<div class="alert alert-success">Package deleted successfully!</div>';
}

// Get all packages
$packages = $db->query("SELECT * FROM packages ORDER BY display_order ASC, id DESC")->fetchAll();

// Get package for editing
$edit_package = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_package = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Manage Packages</h1>
                <p>Add, edit, or remove lesson packages</p>
            </div>

            <?php echo $message; ?>

            <!-- Add/Edit Package Form -->
            <div class="content-box">
                <h2><?php echo $edit_package ? 'Edit Package' : 'Add New Package'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_package['id'] ?? 0; ?>">
                    
                    <div class="form-row">
                        <div>
                            <label>Package Name *</label>
                            <input type="text" name="package_name" value="<?php echo htmlspecialchars($edit_package['package_name'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label>Price (£) *</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $edit_package['price'] ?? ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div>
                            <label>Duration</label>
                            <input type="text" name="duration" value="<?php echo htmlspecialchars($edit_package['duration'] ?? ''); ?>" placeholder="e.g., 10 Hours, 2 Hours">
                        </div>
                        <div>
                            <label>Car Type</label>
                            <select name="car_type">
                                <option value="Both" <?php echo ($edit_package['car_type'] ?? '') == 'Both' ? 'selected' : ''; ?>>Both</option>
                                <option value="Manual" <?php echo ($edit_package['car_type'] ?? '') == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                <option value="Automatic" <?php echo ($edit_package['car_type'] ?? '') == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo htmlspecialchars($edit_package['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Features (one per line or separated by |)</label>
                        <textarea name="features" rows="4" placeholder="Pick-up from home&#10;Structured lesson plan&#10;Progress updates"><?php echo htmlspecialchars($edit_package['features'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured" value="1" <?php echo ($edit_package['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                            Set as Featured Package (shown in hero section)
                        </label>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" <?php echo isset($edit_package) ? ($edit_package['is_active'] ? 'checked' : '') : 'checked'; ?>>
                            Active (visible on website)
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_package" class="btn-primary">
                            <?php echo $edit_package ? 'Update Package' : 'Add Package'; ?>
                        </button>
                        <?php if ($edit_package): ?>
                            <a href="packages.php" class="btn-action">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Packages List -->
            <div class="content-box">
                <h2>All Packages</h2>
                <?php if (count($packages) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Package Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Car Type</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $pkg): ?>
                                    <tr>
                                        <td><?php echo $pkg['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($pkg['package_name']); ?></strong></td>
                                        <td>£<?php echo number_format($pkg['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($pkg['duration']); ?></td>
                                        <td><?php echo $pkg['car_type']; ?></td>
                                        <td><?php echo $pkg['is_featured'] ? '⭐ Yes' : 'No'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $pkg['is_active'] ? 'confirmed' : 'cancelled'; ?>">
                                                <?php echo $pkg['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $pkg['id']; ?>" class="btn-action">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this package?');">
                                                <input type="hidden" name="id" value="<?php echo $pkg['id']; ?>">
                                                <button type="submit" name="delete_package" class="btn-action" style="background: #fee2e2; color: #991b1b;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No packages found. Add your first package above.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>