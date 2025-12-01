<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <ul class="sidebar-nav">
        <li>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="bookings.php" class="<?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>">
                <span class="nav-icon">📅</span>
                <span>Bookings</span>
            </a>
        </li>
        <li>
            <a href="packages.php" class="<?php echo ($current_page == 'packages.php') ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span>
                <span>Packages</span>
            </a>
        </li>
        <li>
            <a href="testimonials.php" class="<?php echo ($current_page == 'testimonials.php') ? 'active' : ''; ?>">
                <span class="nav-icon">⭐</span>
                <span>Testimonials</span>
            </a>
        </li>
        <li>
            <a href="faqs.php" class="<?php echo ($current_page == 'faqs.php') ? 'active' : ''; ?>">
                <span class="nav-icon">❓</span>
                <span>FAQs</span>
            </a>
        </li>
        <li>
            <a href="highlights.php" class="<?php echo ($current_page == 'highlights.php') ? 'active' : ''; ?>">
                <span class="nav-icon">✨</span>
                <span>Key Highlights</span>
            </a>
        </li>
        <li>
            <a href="settings.php" class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <span class="nav-icon">⚙️</span>
                <span>Site Settings</span>
            </a>
        </li>
        <li>
            <a href="../index.php" target="_blank">
                <span class="nav-icon">🌐</span>
                <span>View Website</span>
            </a>
        </li>
    </ul>
</aside>