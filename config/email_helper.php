<?php
// Choose installation method
// If using Composer:
//require_once __DIR__ . '/../vendor/autoload.php';

// If manual installation:
 require_once __DIR__ . '/PHPMailer/src/Exception.php';
 require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
 require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_booking_email($booking_data) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress(ADMIN_EMAIL);
        $mail->addReplyTo($booking_data['email'], $booking_data['full_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = '🚗 New Booking Request - ' . $booking_data['full_name'];
        
        // Email body
        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1a2332; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #1a2332; }
                .value { margin-left: 10px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .cta-button { background: #ffb300; color: #111; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚗 New Booking Request</h1>
                </div>
                <div class="content">
                    <h2>Student Information</h2>
                    <div class="field">
                        <span class="label">Name:</span>
                        <span class="value">' . htmlspecialchars($booking_data['full_name']) . '</span>
                    </div>
                    <div class="field">
                        <span class="label">Phone:</span>
                        <span class="value">' . htmlspecialchars($booking_data['mobile_number']) . '</span>
                    </div>
                    <div class="field">
                        <span class="label">Email:</span>
                        <span class="value">' . htmlspecialchars($booking_data['email']) . '</span>
                    </div>
                    <div class="field">
                        <span class="label">Contact Method:</span>
                        <span class="value">' . htmlspecialchars($booking_data['contact_method']) . '</span>
                    </div>
                    
                    <h2>Booking Details</h2>
                    <div class="field">
                        <span class="label">Package:</span>
                        <span class="value">' . htmlspecialchars($booking_data['package_name']) . '</span>
                    </div>
                    <div class="field">
                        <span class="label">Lesson Type:</span>
                        <span class="value">' . htmlspecialchars($booking_data['lesson_type']) . '</span>
                    </div>
                    <div class="field">
                        <span class="label">Preferred Date:</span>
                        <span class="value">' . htmlspecialchars($booking_data['preferred_date']) . '</span>
                    </div>
                    
                    <div class="field">
                        <span class="label">Pickup Location:</span>
                        <span class="value">' . htmlspecialchars($booking_data['pickup_location']) . '</span>
                    </div>
                    
                    ' . (!empty($booking_data['notes']) ? '
                    <h2>Additional Notes</h2>
                    <div class="field">
                        <p>' . nl2br(htmlspecialchars($booking_data['notes'])) . '</p>
                    </div>
                    ' : '') . '
                    
                    <div style="text-align: center;">
                        <a href="' . SITE_URL . '/admin/bookings.php" class="cta-button">View in Admin Panel</a>
                    </div>
                </div>
                <div class="footer">
                    <p>This is an automated notification from your driving school website.</p>
                    <p>© ' . date('Y') . ' Anab Driving School</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Plain text version
        $mail->AltBody = "New Booking Request\n\n"
            . "Name: " . $booking_data['full_name'] . "\n"
            . "Phone: " . $booking_data['mobile_number'] . "\n"
            . "Email: " . $booking_data['email'] . "\n"
            . "Package: " . $booking_data['package_name'] . "\n"
            . "Date: " . $booking_data['preferred_date'] . "\n";
            
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

function send_booking_confirmation_to_student($booking_data) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($booking_data['email'], $booking_data['full_name']);
        
        $mail->isHTML(true);
        $mail->Subject = '✅ Booking Request Received - Anab Driving School';
        
        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1a2332; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffb300; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>✅ Booking Request Received!</h1>
                </div>
                <div class="content">
                    <p>Dear ' . htmlspecialchars($booking_data['full_name']) . ',</p>
                    
                    <p>Thank you for your booking request with Anab Driving School!</p>
                    
                    <div class="highlight">
                        <strong>📅 Your Booking Details:</strong><br>
                        Package: ' . htmlspecialchars($booking_data['package_name']) . '<br>
                        Date: ' . htmlspecialchars($booking_data['preferred_date']) . '
                    </div>
                    
                    <p>We have received your request and will contact you shortly to confirm your lesson.</p>
                    
                    <p><strong>What happens next?</strong></p>
                    <ul>
                        <li>We\'ll review your booking request</li>
                        <li>Contact you via ' . htmlspecialchars($booking_data['contact_method']) . '</li>
                        <li>Confirm the lesson details with you</li>
                        <li>Send you confirmation once booked</li>
                    </ul>
                    
                    <p>If you have any questions, feel free to contact us.</p>
                    
                    <p>Best regards,<br>
                    <strong>Anab Driving School Team</strong></p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>