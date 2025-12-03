<?php
require_once 'config/db_config.php';
require_once 'config/email_helper.php';

// Test data
$test_booking = [
    'full_name' => 'Test Student',
    'mobile_number' => '07123456789',
    'email' => 'your-test-email@gmail.com', // CHANGE THIS
    'contact_method' => 'Email',
    'package_name' => 'Test Package',
    'lesson_type' => 'Manual',
    'preferred_date' => date('Y-m-d'),
    'preferred_time' => '10:00',
    'pickup_location' => 'Test Location',
    'notes' => 'This is a test booking'
];

echo "<h1>Testing Email Configuration</h1>";

// Test 1: Send to admin
echo "<h2>Test 1: Sending to Admin...</h2>";
if (send_booking_email($test_booking)) {
    echo "<p style='color: green;'>✅ Success! Check your admin email: " . ADMIN_EMAIL . "</p>";
} else {
    echo "<p style='color: red;'>❌ Failed! Check error logs.</p>";
}

// Test 2: Send to student
echo "<h2>Test 2: Sending Confirmation to Student...</h2>";
if (send_booking_confirmation_to_student($test_booking)) {
    echo "<p style='color: green;'>✅ Success! Check test email inbox.</p>";
} else {
    echo "<p style='color: red;'>❌ Failed! Check error logs.</p>";
}

echo "<h2>Configuration:</h2>";
echo "<pre>";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP User: " . SMTP_USER . "\n";
echo "Admin Email: " . ADMIN_EMAIL . "\n";
echo "</pre>";
?>