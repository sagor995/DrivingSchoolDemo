<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';
$booking_id = intval($_GET['id'] ?? 0);

if ($booking_id === 0) {
    redirect(SITE_URL . '/admin/bookings.php');
}

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = sanitize_input($_POST['status']);
    $stmt = $db->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt->execute([$new_status, $booking_id])) {
        $message = '<div class="alert alert-success">Status updated successfully!</div>';
    }
}

// Get booking details
$stmt = $db->prepare("SELECT b.*, p.package_name FROM bookings b 
                      LEFT JOIN packages p ON b.package_id = p.id 
                      WHERE b.id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect(SITE_URL . '/admin/bookings.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Booking Details #<?php echo $booking['id']; ?></h1>
                <p>
                    <a href="bookings.php">← Back to Bookings</a>
                </p>
            </div>

            <?php echo $message; ?>

            <div class="content-box">
                <div class="info-grid">
                    <div>
                        <h3>Student Information</h3>
                        <div class="info-list">
                            <div class="info-item">
                                <strong>Full Name:</strong> <?php echo htmlspecialchars($booking['full_name']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Mobile:</strong> 
                                <a href="tel:<?php echo $booking['mobile_number']; ?>">
                                    <?php echo htmlspecialchars($booking['mobile_number']); ?>
                                </a>
                            </div>
                            <div class="info-item">
                                <strong>Email:</strong> 
                                <a href="mailto:<?php echo $booking['email']; ?>">
                                    <?php echo htmlspecialchars($booking['email']); ?>
                                </a>
                            </div>
                            <div class="info-item">
                                <strong>Preferred Contact:</strong> <?php echo $booking['contact_method']; ?>
                            </div>
                        </div>

                        <?php if ($booking['contact_method'] === 'WhatsApp'): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $booking['mobile_number']); ?>" 
                               target="_blank" 
                               class="btn-primary" 
                               style="display: inline-block; margin-top: 12px;">
                                💬 Open WhatsApp Chat
                            </a>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h3>Booking Details</h3>
                        <div class="info-list">
                            <div class="info-item">
                                <strong>Package:</strong> <?php echo htmlspecialchars($booking['package_name'] ?? 'N/A'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Lesson Type:</strong> <?php echo $booking['lesson_type']; ?>
                            </div>
                            <div class="info-item">
                                <strong>Preferred Date:</strong> <?php echo date('l, d F Y', strtotime($booking['preferred_date'])); ?>
                            </div>
                            <div class="info-item">
                                <strong>Preferred Time:</strong> <?php echo date('h:i A', strtotime($booking['preferred_time'])); ?>
                            </div>
                            <div class="info-item">
                                <strong>Pickup Location:</strong> <?php echo htmlspecialchars($booking['pickup_location']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($booking['notes'])): ?>
                <div class="content-box">
                    <h3>Additional Notes</h3>
                    <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($booking['notes']); ?></p>
                </div>
            <?php endif; ?>

            <div class="content-box">
                <h3>Update Status</h3>
                <form method="POST">
                    <div class="form-row">
                        <div>
                            <label>Current Status</label>
                            <select name="status" required>
                                <option value="Pending" <?php echo $booking['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Confirmed" <?php echo $booking['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="Cancelled" <?php echo $booking['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Completed" <?php echo $booking['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" name="update_status" class="btn-primary">Update Status</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="content-box">
                <h3>Timeline</h3>
                <div class="info-list">
                    <div class="info-item">
                        <strong>Booking Submitted:</strong> <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?>
                    </div>
                    <div class="info-item">
                        <strong>Last Updated:</strong> <?php echo date('d M Y, h:i A', strtotime($booking['updated_at'])); ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>