<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';

// Handle add/edit
if (isset($_POST['save_faq'])) {
    $id = intval($_POST['id'] ?? 0);
    $question = sanitize_input($_POST['question']);
    $answer = sanitize_input($_POST['answer']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if ($id > 0) {
        $stmt = $db->prepare("UPDATE faqs SET question=?, answer=?, is_active=? WHERE id=?");
        $stmt->execute([$question, $answer, $is_active, $id]);
        $message = '<div class="alert alert-success">FAQ updated!</div>';
    } else {
        $stmt = $db->prepare("INSERT INTO faqs (question, answer, is_active) VALUES (?, ?, ?)");
        $stmt->execute([$question, $answer, $is_active]);
        $message = '<div class="alert alert-success">FAQ added!</div>';
    }
}

// Handle delete
if (isset($_POST['delete_faq'])) {
    $id = intval($_POST['id']);
    $db->prepare("DELETE FROM faqs WHERE id=?")->execute([$id]);
    $message = '<div class="alert alert-success">FAQ deleted!</div>';
}

$faqs = $db->query("SELECT * FROM faqs ORDER BY display_order ASC, id ASC")->fetchAll();
$edit_faq = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_faq = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQs - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Manage FAQs</h1>
                <p>Add, edit, or remove frequently asked questions</p>
            </div>

            <?php echo $message; ?>

            <div class="content-box">
                <h2><?php echo $edit_faq ? 'Edit FAQ' : 'Add New FAQ'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $edit_faq['id'] ?? 0; ?>">
                    
                    <div class="form-group">
                        <label>Question *</label>
                        <input type="text" name="question" value="<?php echo htmlspecialchars($edit_faq['question'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Answer *</label>
                        <textarea name="answer" rows="4" required><?php echo htmlspecialchars($edit_faq['answer'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" value="1" <?php echo isset($edit_faq) ? ($edit_faq['is_active'] ? 'checked' : '') : 'checked'; ?>>
                            Active (visible on website)
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_faq" class="btn-primary">
                            <?php echo $edit_faq ? 'Update' : 'Add'; ?> FAQ
                        </button>
                        <?php if ($edit_faq): ?>
                            <a href="faqs.php" class="btn-action">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="content-box">
                <h2>All FAQs</h2>
                <?php if (count($faqs) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Question</th>
                                    <th>Answer</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faqs as $faq): ?>
                                    <tr>
                                        <td><?php echo $faq['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($faq['question']); ?></strong></td>
                                        <td><?php echo substr(htmlspecialchars($faq['answer']), 0, 100) . '...'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $faq['is_active'] ? 'confirmed' : 'cancelled'; ?>">
                                                <?php echo $faq['is_active'] ? 'Active' : 'Hidden'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $faq['id']; ?>" class="btn-action">Edit</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete?');">
                                                <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                                <button type="submit" name="delete_faq" class="btn-action" style="background: #fee2e2; color: #991b1b;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No FAQs yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>