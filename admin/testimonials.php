<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';

// Handle add/edit
if (isset($_POST['save_testimonial'])) {
    $id = intval($_POST['id'] ?? 0);
    $student_name = sanitize_input($_POST['student_name']);
    $review_text = sanitize_input($_POST['review_text']);
    $lesson_type = sanitize_input($_POST['lesson_type']);
    $rating = intval($_POST['rating']);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;
    
    if ($id > 0) {
        $stmt = $db->prepare("UPDATE testimonials SET student_name=?, review_text=?, lesson_type=?, rating=?, is_approved=? WHERE id=?");
        $stmt->execute([$student_name, $review_text, $lesson_type, $rating, $is_approved, $id]);
        $message = '<div class="alert alert-success">Testimonial updated!</div>';
    } else {
        $stmt = $db->prepare("INSERT INTO testimonials (student_name, review_text, lesson_type, rating, is_approved) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$student_name, $review_text, $lesson_type, $rating, $is_approved]);
        $message = '<div class="alert alert-success">Testimonial added!</div>';
    }
}

// Handle delete
if (isset($_POST['delete_testimonial'])) {
    $id = intval($_POST['id']);
    $db->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
    $message = '<div class="alert alert-success">Testimonial deleted!</div>';
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY display_order ASC, id DESC")->fetchAll();
$edit_testimonial = null;
if (isset($_GET['edit'])) {
    $edit_testimonial = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $edit_testimonial->execute([intval($_GET['edit'])]);
    $edit_testimonial = $edit_testimonial->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Manage Testimonials</h1>
                <p>Add, edit, or remove student reviews</p>
            </div>

            <?php echo $message; ?>

            <div class="content-box">
                <h2><?php echo $edit_testimonial ? 'Edit Testimonial' : 'Add New Testimonial'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_testimonial['id'] ?? 0; ?>">
                    
                    <div class="form-row">
                        <div>
                            <label>Student Name *</label>
                            <input type="text" name="student_name" value="<?php echo htmlspecialchars($edit_testimonial['student_name'] ?? ''); ?>" required>
                        </div>
                        <div>
                            <label>Lesson Type</label>
                            <input type="text" name="lesson_type" value="<?php echo htmlspecialchars($edit_testimonial['lesson_type'] ?? ''); ?>" placeholder="e.g., Manual lessons – city routes">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Review Text *</label>
                        <textarea name="review_text" rows="4" required><?php echo htmlspecialchars($edit_testimonial['review_text'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div>
                            <label>Rating (1-5)</label>
                            <input type="number" name="rating" min="1" max="5" value="<?php echo $edit_testimonial['rating'] ?? 5; ?>">
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" name="is_approved" value="1" <?php echo isset($edit_testimonial) ? ($edit_testimonial['is_approved'] ? 'checked' : '') : 'checked'; ?>>
                                Approved (visible on website)
                            </label>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_testimonial" class="btn-primary">
                            <?php echo $edit_testimonial ? 'Update' : 'Add'; ?> Testimonial
                        </button>
                        <?php if ($edit_testimonial): ?>
                            <a href="testimonials.php" class="btn-action">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="content-box">
                <h2>All Testimonials</h2>
                <?php if (count($testimonials) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Review</th>
                                    <th>Lesson Type</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($testimonials as $t): ?>
                                    <tr>
                                        <td><?php echo $t['id']; ?></td>
                                        <td><?php echo htmlspecialchars($t['student_name']); ?></td>
                                        <td><?php echo substr(htmlspecialchars($t['review_text']), 0, 80) . '...'; ?></td>
                                        <td><?php echo htmlspecialchars($t['lesson_type']); ?></td>
                                        <td><?php echo str_repeat('⭐', $t['rating']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $t['is_approved'] ? 'confirmed' : 'cancelled'; ?>">
                                                <?php echo $t['is_approved'] ? 'Approved' : 'Hidden'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $t['id']; ?>" class="btn-action">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete?');">
                                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                                <button type="submit" name="delete_testimonial" class="btn-action" style="background: #fee2e2; color: #991b1b;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No testimonials yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>