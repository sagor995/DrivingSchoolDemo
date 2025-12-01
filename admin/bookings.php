<?php
session_start();
require_once '../config/db_config.php';
require_admin_login();
check_session_timeout();

$db = Database::getInstance()->getConnection();
$message = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $db->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
    if ($stmt->execute([$new_status, $booking_id])) {
        $message = '<div class="alert alert-success">Booking status updated successfully!</div>';
    }
}

// Handle delete
if (isset($_POST['delete_booking'])) {
    $booking_id = intval($_POST['booking_id']);
    $stmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
    if ($stmt->execute([$booking_id])) {
        $message = '<div class="alert alert-success">Booking deleted successfully!</div>';
    }
}

// Get filter
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT b.*, p.package_name FROM bookings b 
          LEFT JOIN packages p ON b.package_id = p.id 
          WHERE 1=1";
$params = [];

if ($filter_status !== 'all') {
    $query .= " AND b.status = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $query .= " AND (b.full_name LIKE ? OR b.mobile_number LIKE ? OR b.email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY b.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="page-header">
                <h1>Manage Bookings</h1>
                <p>View and manage all lesson bookings</p>
            </div>

            <?php echo $message; ?>

            <!-- Filters -->
            <div class="content-box">
                <form method="GET" action="">
                    <div class="form-row">
                        <div>
                            <label>Filter by Status</label>
                            <select name="status" onchange="this.form.submit()">
                                <option value="all" <?php echo $filter_status == 'all' ? 'selected' : ''; ?>>All Bookings</option>
                                <option value="Pending" <?php echo $filter_status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Confirmed" <?php echo $filter_status == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="Cancelled" <?php echo $filter_status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Completed" <?php echo $filter_status == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <div>
                            <label>Search</label>
                            <input type="text" name="search" placeholder="Name, phone, email..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div style="display: flex; align-items: flex-end; gap: 10px;">
                            <button type="submit" class="btn-primary">Filter</button>
                            <a href="bookings.php" class="btn-action">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Bookings Table -->
            <div class="content-box">
                <?php if (count($bookings) > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Package</th>
                                    <th>Date & Time</th>
                                    <th>Lesson Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($booking['full_name']); ?></strong>
                                        </td>
                                        <td>
                                            <div><?php echo htmlspecialchars($booking['mobile_number']); ?></div>
                                            <small style="color: #666;"><?php echo htmlspecialchars($booking['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['package_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div><?php echo date('d M Y', strtotime($booking['preferred_date'])); ?></div>
                                            <small><?php echo date('h:i A', strtotime($booking['preferred_time'])); ?></small>
                                        </td>
                                        <td><?php echo $booking['lesson_type']; ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <select name="status" onchange="if(confirm('Update status?')) this.form.submit();" class="badge badge-<?php echo strtolower($booking['status']); ?>">
                                                    <option value="Pending" <?php echo $booking['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="Confirmed" <?php echo $booking['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="Cancelled" <?php echo $booking['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    <option value="Completed" <?php echo $booking['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn-action">View</a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this booking?');">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" name="delete_booking" class="btn-action" style="background: #fee2e2; color: #991b1b;">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-data">No bookings found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>