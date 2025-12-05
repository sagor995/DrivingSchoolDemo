<?php
require_once 'config/db_config.php';

$db = Database::getInstance()->getConnection();

// Check if maintenance mode is enabled
$maintenance_check = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode' LIMIT 1")->fetch();
$is_maintenance = ($maintenance_check && $maintenance_check['setting_value'] === 'true');

// If maintenance mode is ON and user is not admin, show maintenance page
if ($is_maintenance && !isset($_SESSION['admin_logged_in'])) {
    $maintenance_msg = $db->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_message' LIMIT 1")->fetch();
    $message = $maintenance_msg ? $maintenance_msg['setting_value'] : 'Website is under development. We will be back soon!';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Under Development</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: system-ui, -apple-system, sans-serif;
                background: radial-gradient(circle at top, #1e90ff 0, #001f3f 45%, #000 100%);;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .container {
                background: white;
                border-radius: 20px;
                padding: 60px 40px;
                text-align: center;
                max-width: 600px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .icon {
                font-size: 80px;
                margin-bottom: 20px;
                animation: bounce 2s infinite;
            }
            @keyframes bounce {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
            }
            h1 {
                font-size: 32px;
                color: #111;
                margin-bottom: 15px;
            }
            p {
                font-size: 18px;
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .logo {
                width: 120px;
                height: 120px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: radial-gradient(circle at 30% 20%, #fff 0, #ffdd80 20%, #ffb300 50%, #b77400 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 60px;
                font-weight: 700;
                color: #111;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            .admin-link {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #e5e7eb;
            }
            .admin-link a {
                color: #667eea;
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
            }
            .admin-link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="uploads/logo_1764629433.png" alt="Anab Driving School Logo" class="logo">
            <div class="icon">🚧</div>
            <h1>Under Development</h1>
            <p><?php echo htmlspecialchars($message); ?></p>
            <p class="info-label">Need to contact us before launch?</p>
        <p class="info-value">
            <strong>Call / WhatsApp:</strong> <span>+447915067832</span><br>
        </p>
          <!--  <div class="admin-link">
                <a href="admin/login.php">Admin Login →</a>
            </div>-->
        </div>
        
    </body>
    </html>
    <?php
    exit;
}

// Get site settings
$settings_query = $db->query("SELECT setting_key, setting_value FROM site_settings");
$settings = [];
while ($row = $settings_query->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get active packages
$packages = $db->query("SELECT * FROM packages WHERE is_active = TRUE ORDER BY display_order ASC, id ASC")->fetchAll();

// Get featured package
$featured_package = $db->query("SELECT * FROM packages WHERE is_featured = TRUE AND is_active = TRUE LIMIT 1")->fetch();

// Get key highlights
$highlights = $db->query("SELECT * FROM key_highlights WHERE is_active = TRUE ORDER BY display_order ASC")->fetchAll();

// Get hero badges
$hero_badges = $db->query("SELECT * FROM hero_badges WHERE is_active = TRUE ORDER BY display_order ASC")->fetchAll();

// Get FAQs
$faqs = $db->query("SELECT * FROM faqs WHERE is_active = TRUE ORDER BY display_order ASC")->fetchAll();

// Get approved testimonials
$testimonials = $db->query("SELECT * FROM testimonials WHERE is_approved = TRUE ORDER BY display_order ASC LIMIT 6")->fetchAll();

// Handle booking form submission
$booking_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $full_name = sanitize_input($_POST['full_name']);
    $mobile_number = sanitize_input($_POST['mobile_number']);
    $email = sanitize_input($_POST['email']);
    $contact_method = sanitize_input($_POST['contact_method']);
    $package_id = intval($_POST['package_id']);
    $lesson_type = sanitize_input($_POST['lesson_type']);
    $preferred_date = $_POST['preferred_date'];
    $pickup_location = sanitize_input($_POST['pickup_location']);
    $notes = sanitize_input($_POST['notes']);
    
    try {
        $stmt = $db->prepare("INSERT INTO bookings (full_name, mobile_number, email, contact_method, package_id, lesson_type, preferred_date, pickup_location, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
        
       if ($stmt->execute([$full_name, $mobile_number, $email, $contact_method, $package_id, $lesson_type, $preferred_date, $pickup_location, $notes])) {

            // Find package name from $packages list
            $package_name = '';
            foreach ($packages as $pkg) {
                if ((int)$pkg['id'] === $package_id) {
                    $package_name = $pkg['package_name'];
                    break;
                }
            }

            // Build booking data array for email_helper
            $booking_data = [
                'full_name'       => $full_name,
                'mobile_number'   => $mobile_number,
                'email'           => $email,
                'contact_method'  => $contact_method,
                'package_name'    => $package_name,
                'lesson_type'     => $lesson_type,
                'preferred_date'  => $preferred_date,
                'pickup_location' => $pickup_location,
                'notes'           => $notes,
            ];

            // NEW: use PHPMailer SMTP ✅
            send_booking_email($booking_data);                    // to admin
            send_booking_confirmation_to_student($booking_data);  // to student (same as test-email.php)

           $booking_message = '<div class="alert alert-success" style="margin: 20px; padding: 14px; background: #d1fae5; color: #065f46; border-radius: 8px;">✅ Booking request submitted successfully! We will contact you shortly.</div>';
        }
    } catch (PDOException $e) {
        $booking_message = '<div class="alert alert-error" style="margin: 20px; padding: 14px; background: #fee2e2; color: #991b1b; border-radius: 8px;">❌ There was an error submitting your booking. Please try again or call us directly.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($settings['business_name'] ?? 'Anab Driving School'); ?></title>
  <style>
    /* Keep all your existing CSS from the original file */
    :root {
      --bg: #dadfe6;
      --bg-dark: #0f1013;
      --accent: #ffb300;
      --text: #111111;
      --muted: #777777;
      --card: #ffffff;
      --radius: 14px;
      --shadow: 0 10px 30px rgba(0,0,0,0.08);
    }

    * { box-sizing: border-box; }
    body {
      margin:0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background:var(--bg);
      color:var(--text);
    }

    /* NAVBAR */
    .nav {
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(16px);
      background: rgba(15,16,19,0.96);
      color: #fff;
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:10px 24px;
    }
    .nav-left { display:flex; align-items:center; gap:10px; }
    .logo-img {
      height: 80px;
      width: auto;
      object-fit: contain;
    }
    .logo-mark {
      width:34px; height:34px; border-radius:50%;
      background: radial-gradient(circle at 30% 20%, #fff 0, #ffdd80 20%, #ffb300 50%, #b77400 100%);
      display:flex; align-items:center; justify-content:center;
      font-weight:700; font-size:18px; color:#111;
      box-shadow:0 6px 18px rgba(0,0,0,0.35);
    }
    .nav-title { font-weight:600; letter-spacing:0.03em; font-size:18px; }

    .nav-links {
      display:flex;
      align-items:center;
    }
    .nav-links a {
      color:#ddd;
      text-decoration:none;
      margin-left:18px;
      font-size:14px;
    }
    .nav-links a:hover { color:var(--accent); }
    .nav-cta {
      background:var(--accent);
      color:#111;
      border:none;
      padding:10px 18px;
      border-radius:999px;
      font-size:14px;
      font-weight:600;
      cursor:pointer;
      box-shadow:0 8px 20px rgba(0,0,0,0.45);
      margin-left:18px;
      white-space:nowrap;
    }

    /* mobile nav toggle */
    .nav-toggle {
      display:none;
      width:32px;
      height:32px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,0.4);
      background:transparent;
      padding:0;
      margin-left:12px;
      cursor:pointer;
      align-items:center;
      justify-content:center;
    }
    .nav-toggle-lines {
      width:18px;
      height:2px;
      background:#fff;
      position:relative;
    }
    .nav-toggle-lines::before,
    .nav-toggle-lines::after {
      content:"";
      position:absolute;
      left:0;
      width:18px;
      height:2px;
      background:#fff;
    }
    .nav-toggle-lines::before { top:-5px; }
    .nav-toggle-lines::after { top:5px; }

    /* GENERAL LAYOUT */
    .section { padding:60px 20px; max-width:1120px; margin:auto; }
    .section h2 { font-size:28px; margin-bottom:10px; }
    .section-sub { color:var(--muted); margin-bottom:30px; font-size:15px; }

    /* HERO */
    .hero {
      background:linear-gradient(135deg, #0f1013 0%, #1b1d24 60%, #101012 100%);
      color:#fff;
      padding:70px 20px 60px;
    }
    .hero-inner {
      max-width:1120px;
      margin:auto;
      display:grid;
      grid-template-columns:minmax(0,1.1fr) minmax(0,0.9fr);
      gap:40px;
      align-items:center;
    }
    @media (max-width: 820px) {
      .hero-inner { grid-template-columns:1fr; }
    }
    .hero-tag {
      display:inline-flex;
      align-items:center;
      gap:8px;
      background:rgba(255,255,255,0.06);
      padding:6px 12px;
      border-radius:999px;
      font-size:12px;
      margin-bottom:16px;
    }
    .hero-tag-dot {
      width:8px; height:8px; border-radius:50%;
      background:#4ade80;
      box-shadow:0 0 0 6px rgba(74,222,128,0.2);
    }
    .hero h1 { font-size:36px; margin:0 0 16px; }
    .hero-lead { color:#c5c7d3; font-size:15px; margin-bottom:22px; }
    .hero-badges { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:26px; }
    .badge {
      font-size:11px;
      padding:6px 10px;
      border-radius:999px;
      border:1px solid rgba(255,255,255,0.1);
      color:#d6d8e3;
    }
    .hero-cta-row {
      display:flex;
      flex-wrap:wrap;
      gap:12px;
      align-items:center;
    }
    .hero-primary {
      background:var(--accent);
      color:#111;
      padding:12px 22px;
      border-radius:999px;
      border:none;
      cursor:pointer;
      font-weight:600;
      box-shadow:0 10px 24px rgba(0,0,0,0.5);
    }
    .hero-secondary {
      background:transparent;
      border:1px solid rgba(255,255,255,0.3);
      color:#fff;
      padding:11px 20px;
      border-radius:999px;
      cursor:pointer;
      font-size:14px;
    }
    .hero-meta { font-size:12px; color:#9ca3af; margin-top:8px; }

    .hero-card {
      background:radial-gradient(circle at top left, #23242d 0, #111320 55%, #050509 100%);
      border-radius:var(--radius);
      padding:22px 20px;
      box-shadow:0 16px 40px rgba(0,0,0,0.6);
      border:1px solid rgba(255,255,255,0.05);
      color:#e5e7eb;
    }
    .hero-card-header {
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:14px;
      gap:8px;
    }
    .hero-car-tag {
      font-size:11px;
      padding:4px 10px;
      border-radius:999px;
      background:rgba(148,163,184,0.2);
      white-space:nowrap;
    }
    .hero-price { font-weight:700; font-size:18px; }
    .hero-grid {
      display:grid;
      grid-template-columns:repeat(2, minmax(0,1fr));
      gap:10px;
      font-size:12px;
      margin-bottom:16px;
    }
    .hero-label { color:#9ca3af; font-size:11px; }
    .hero-val { font-weight:500; }
    .hero-timeline { font-size:11px; color:#9ca3af; margin-bottom:10px; }
    .hero-pill-row { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
    .hero-pill {
      font-size:10px;
      padding:5px 9px;
      border-radius:999px;
      background:rgba(15,118,110,0.25);
      border:1px solid rgba(45,212,191,0.3);
      color:#a5f3fc;
    }
    .hero-indicator { font-size:11px; color:#4ade80; }

    /* SERVICES / PACKAGES */
    .cards-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(240px,1fr));
      gap:20px;
    }
    .card {
      background:var(--card);
      border-radius:var(--radius);
      padding:20px;
      box-shadow:var(--shadow);
    }
    .card h3 { margin:0 0 6px; font-size:18px; }
    .card-price { font-weight:700; margin-bottom:4px; }
    .card-meta { font-size:13px; color:var(--muted); margin-bottom:10px; }
    .card ul {
      padding-left:18px;
      margin:0 0 10px;
      font-size:13px;
      color:#444;
    }
    .card-cta {
      display:inline-block;
      margin-top:4px;
      font-size:13px;
      color:#111;
      font-weight:600;
      text-decoration:none;
    }

    /* ABOUT */
    .split {
      display:grid;
      grid-template-columns:minmax(0,1.1fr) minmax(0,0.9fr);
      gap:32px;
      align-items:flex-start;
    }
    @media (max-width: 880px) {
      .split { grid-template-columns:1fr; }
    }
    .about-tag {
      font-size:11px;
      text-transform:uppercase;
      color:var(--muted);
      letter-spacing:0.12em;
      margin-bottom:6px;
    }
    .about-p { font-size:14px; color:#444; line-height:1.6; }
    .about-list { font-size:14px; color:#444; padding-left:18px; }

    /* BOOKING FORM */
    .booking-box {
      background:var(--card);
      border-radius:var(--radius);
      padding:24px 22px;
      box-shadow:var(--shadow);
    }
    .form-row {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
      gap:14px;
    }
    label {
      font-size:13px;
      font-weight:500;
      display:block;
      margin-top:10px;
    }
    input, select, textarea {
      width:100%;
      padding:10px 11px;
      margin-top:4px;
      border-radius:8px;
      border:1px solid #d1d5db;
      font-size:13px;
      font-family:inherit;
    }
    textarea { min-height:90px; resize:vertical; }
    .btn-primary {
      margin-top:16px;
      background:var(--bg-dark);
      color:#fff;
      padding:12px 20px;
      border-radius:999px;
      border:none;
      cursor:pointer;
      font-weight:600;
      width:100%;
    }

    /* TESTIMONIALS */
    .quote-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:18px;
    }
    .quote {
      background:#111827;
      color:#e5e7eb;
      border-radius:var(--radius);
      padding:18px 18px 16px;
      box-shadow:0 12px 30px rgba(15,23,42,0.7);
      position:relative;
      overflow:hidden;
    }
    .quote::before {
      content:"\201C";
      position:absolute;
      font-size:70px;
      color:rgba(55,65,81,0.3);
      top:-22px;
      left:6px;
    }
    .quote-name { margin-top:10px; font-size:13px; font-weight:600; }
    .quote-meta { font-size:11px; color:#9ca3af; }

    /* FAQ */
    .faq-item {
      background:var(--card);
      border-radius:var(--radius);
      padding:14px 16px;
      box-shadow:var(--shadow);
      margin-bottom:10px;
    }
    .faq-q { font-size:14px; font-weight:600; margin-bottom:4px; }
    .faq-a { font-size:13px; color:#555; }

    /* CONTACT */
    .contact-grid {
      display:grid;
      grid-template-columns:minmax(0,1.05fr) minmax(0,0.95fr);
      gap:26px;
    }
    @media (max-width: 860px) {
      .contact-grid { grid-template-columns:1fr; }
    }
    .contact-card {
      background:var(--card);
      border-radius:var(--radius);
      padding:20px;
      box-shadow:var(--shadow);
      font-size:14px;
      color:#444;
    }
    .contact-row { margin-bottom:8px; }
    .contact-label { font-weight:600; font-size:13px; }
    .map-placeholder {
      border-radius:var(--radius);
      background:linear-gradient(135deg,#d1d5db,#9ca3af);
      height:220px;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#111827;
      font-size:14px;
      font-weight:600;
      box-shadow:var(--shadow);
    }

    /* FOOTER */
    footer {
      background:#020617;
      color:#9ca3af;
      padding:20px;
      font-size:12px;
    }
    .footer-inner {
      max-width:1120px;
      margin:auto;
      display:flex;
      flex-wrap:wrap;
      justify-content:space-between;
      align-items:flex-start;
      gap:15px;
    }
    @media (max-width: 640px) {
      .footer-inner {
        flex-direction: column;
        text-align: center;
      }
      .footer-inner > div:last-child {
        width: 100%;
        justify-content: center;
      }
    }

    /* WHATSAPP FLOAT */
    .wa-float {
      position:fixed;
      right:18px;
      bottom:18px;
      z-index:60;
    }
    .wa-btn {
      background:#22c55e;
      color:#fff;
      border-radius:999px;
      padding:10px 14px;
      display:flex;
      align-items:center;
      gap:8px;
      font-size:13px;
      text-decoration:none;
      box-shadow:0 10px 24px rgba(22,163,74,0.6);
    }
    .wa-dot {
      width:9px;
      height:9px;
      border-radius:50%;
      background:#bbf7d0;
    }

    /* MOBILE TWEAKS */
    @media (max-width: 768px) {
      .nav {
        padding:8px 14px;
      }
      .nav-title {
        font-size:16px;
      }
      .nav-links {
        position:fixed;
        top:56px;
        right:0;
        left:0;
        background:#0f1013;
        padding:10px 18px 16px;
        flex-direction:column;
        gap:8px;
        display:none;
      }
      .nav-links a {
        margin-left:0;
        padding:6px 0;
        font-size:15px;
      }
      .nav-cta {
        width:100%;
        text-align:center;
        margin-left:0;
        margin-top:4px;
      }
      .nav-links.open {
        display:flex;
      }
      .nav-toggle {
        display:flex;
      }
      .hero {
        padding:56px 16px 40px;
      }
      .hero h1 {
        font-size:28px;
      }
      .section {
        padding:40px 16px;
      }
      .hero-card {
        margin-top:10px;
      }
      .wa-float {
        right:12px;
        bottom:12px;
      }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="nav">
    <div class="nav-left">
      <?php if (!empty($settings['logo_path']) && file_exists($settings['logo_path'])): ?>
        <img src="<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="Logo" class="logo-img">
      <?php else: ?>
        <div class="logo-mark">A</div>
      <?php endif; ?>
      <div class="nav-title"><?php echo htmlspecialchars($settings['business_name'] ?? 'Anab Driving School'); ?></div>
    </div>
    <div style="display:flex; align-items:center; gap:8px;">
      <div class="nav-links" id="navLinks">
        <a href="#home">Home</a>
        <a href="#about">About</a>
        <a href="#packages">Lessons</a>
        <a href="#booking">Book Online</a>
        <a href="#testimonials">Reviews</a>
        <a href="#contact">Contact</a>
        <button class="nav-cta" onclick="document.getElementById('booking').scrollIntoView({behavior:'smooth'});">Book a Lesson</button>
      </div>
      <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
        <div class="nav-toggle-lines"></div>
      </button>
    </div>
  </nav>

  <!-- HERO / HOME -->
  <header id="home" class="hero">
    <div class="hero-inner">
      <div>
        <div class="hero-tag">
          <span class="hero-tag-dot"></span> 
          <?php echo htmlspecialchars($settings['hero_tag'] ?? 'DVSA-Style Professional Training'); ?>
        </div>
        <h1><?php echo htmlspecialchars($settings['hero_title'] ?? 'Confident Driving Starts Here.'); ?></h1>
        <p class="hero-lead"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? ''); ?></p>
        
        <div class="hero-badges">
          <?php foreach ($hero_badges as $badge): ?>
            <div class="badge"><?php echo htmlspecialchars($badge['badge_text']); ?></div>
          <?php endforeach; ?>
        </div>
        
        <div class="hero-cta-row">
          <button class="hero-primary" onclick="document.getElementById('booking').scrollIntoView({behavior:'smooth'});">Book Your First Lesson</button>
          <button class="hero-secondary" onclick="document.getElementById('packages').scrollIntoView({behavior:'smooth'});">View Lesson Packages</button>
        </div>
        <div class="hero-meta">No obligation – the form sends a booking request only. Payment can be arranged after confirmation.</div>
      </div>

      <?php if ($featured_package): ?>
        <aside class="hero-card">
          <div class="hero-card-header">
            <div>
              <div class="hero-label">Featured Package</div>
              <div class="hero-price"><?php echo htmlspecialchars($featured_package['package_name']); ?></div>
            </div>
            <div class="hero-car-tag"><?php echo $featured_package['car_type']; ?></div>
          </div>
          <div class="hero-grid">
            <div>
              <div class="hero-label">Duration</div>
              <div class="hero-val"><?php echo htmlspecialchars($featured_package['duration']); ?></div>
            </div>
            <div>
              <div class="hero-label">From</div>
              <div class="hero-val">£<?php echo number_format($featured_package['price'], 2); ?></div>
            </div>
          </div>
          <div class="hero-timeline"><?php echo htmlspecialchars($featured_package['description']); ?></div>
          <div class="hero-pill-row">
            <?php 
            $features = explode('|', $featured_package['features']);
            foreach ($features as $feature): 
            ?>
              <div class="hero-pill"><?php echo htmlspecialchars(trim($feature)); ?></div>
            <?php endforeach; ?>
          </div>
        </aside>
      <?php endif; ?>
    </div>
  </header>

  <!-- ABOUT PAGE -->
  <section id="about" class="section">
    <div class="split">
      <div>
        <div class="about-tag">About the Instructor</div>
        <h2><?php echo htmlspecialchars($settings['about_title'] ?? 'Friendly, Patient and Focused on Real-World Driving.'); ?></h2>
        <p class="about-p"><?php echo nl2br(htmlspecialchars($settings['about_description'] ?? '')); ?></p>
      </div>
      <div class="card">
        <h3>Key Highlights</h3>
        <ul class="about-list">
          <?php foreach ($highlights as $highlight): ?>
            <li><?php echo htmlspecialchars($highlight['highlight_text']); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>

  <!-- PACKAGES / SERVICES PAGE -->
  <section id="packages" class="section">
    <h2>Lesson Packages & Pricing</h2>
    <p class="section-sub">Choose the package that's right for you. All packages include pick-up and patient, professional instruction.</p>
    <div class="cards-grid">
      <?php foreach ($packages as $package): ?>
        <div class="card">
          <h3><?php echo htmlspecialchars($package['package_name']); ?></h3>
          <div class="card-price">£<?php echo number_format($package['price'], 2); ?></div>
          <div class="card-meta"><?php echo htmlspecialchars($package['description']); ?></div>
          <ul>
            <?php 
            $features = explode('|', $package['features']);
            foreach ($features as $feature): 
            ?>
              <li><?php echo htmlspecialchars(trim($feature)); ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="#booking" class="card-cta">Book this package →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- BOOK ONLINE PAGE -->
  <section id="booking" class="section">
    <h2>Book Online</h2>
    <p class="section-sub">Fill out the form below to request a booking. We'll confirm your lesson details shortly.</p>

    <?php echo $booking_message; ?>

    <div class="booking-box">
      <form method="POST" action="">
        <div class="form-row">
          <div>
            <label>Full Name *</label>
            <input type="text" name="full_name" required />
          </div>
          <div>
            <label>Mobile Number *</label>
            <input type="tel" name="mobile_number" required />
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Email Address</label>
            <input type="email" name="email" />
          </div>
          <div>
            <label>Preferred Contact Method</label>
            <select name="contact_method">
              <option>WhatsApp</option>
              <option>Phone Call</option>
              <option>Email</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Choose Package *</label>
            <select name="package_id" required>
              <option value="">Select a package...</option>
              <?php foreach ($packages as $pkg): ?>
                <option value="<?php echo $pkg['id']; ?>">
                  <?php echo htmlspecialchars($pkg['package_name']); ?> - £<?php echo number_format($pkg['price'], 2); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label>Preferred Lesson Type</label>
            <select name="lesson_type">
              <option>Manual</option>
              <option>Automatic</option>
              <option>Either</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Preferred Date</label>
            <input type="date" name="preferred_date" required />
          </div>
        </div>

        <label>Pickup Location / Postcode</label>
        <input type="text" name="pickup_location" placeholder="e.g. BL1 2AB or 'Near Bolton Town Centre'" />

        <label>Notes (current experience, test date, anything we should know)</label>
        <textarea name="notes" placeholder="Tell us about your driving experience, if you have a test booked, or any concerns..."></textarea>

        <button type="submit" name="submit_booking" class="btn-primary">Submit Booking Request</button>
      </form>
    </div>
  </section>

  <!-- TESTIMONIALS PAGE -->
  <?php if (count($testimonials) > 0): ?>
    <section id="testimonials" class="section">
      <h2>Learner Reviews</h2>
      <p class="section-sub">See what our students have to say about their experience.</p>

      <div class="quote-grid">
        <?php foreach ($testimonials as $testimonial): ?>
          <div class="quote">
            <div><?php echo htmlspecialchars($testimonial['review_text']); ?></div>
            <div class="quote-name"><?php echo htmlspecialchars($testimonial['student_name']); ?></div>
            <div class="quote-meta"><?php echo htmlspecialchars($testimonial['lesson_type']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- FAQ PAGE -->
  <?php if (count($faqs) > 0): ?>
    <section class="section">
      <h2>Common Questions</h2>
      <p class="section-sub">Find answers to frequently asked questions about our lessons.</p>

      <?php foreach ($faqs as $faq): ?>
        <div class="faq-item">
          <div class="faq-q"><?php echo htmlspecialchars($faq['question']); ?></div>
          <div class="faq-a"><?php echo htmlspecialchars($faq['answer']); ?></div>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <!-- CONTACT PAGE -->
  <section id="contact" class="section">
    <h2>Contact & Area Covered</h2>
    <p class="section-sub">Get in touch to book lessons or ask questions.</p>

    <div class="contact-grid">
      <div class="contact-card">
        <div class="contact-row">
          <span class="contact-label">Phone:</span> <?php echo htmlspecialchars($settings['phone_number'] ?? ''); ?>
        </div>
        <div class="contact-row">
          <span class="contact-label">WhatsApp:</span> <?php echo htmlspecialchars($settings['whatsapp_number'] ?? ''); ?>
        </div>
        <div class="contact-row">
          <span class="contact-label">Email:</span> <?php echo htmlspecialchars($settings['email'] ?? ''); ?>
        </div>
        <div class="contact-row">
          <span class="contact-label">Area covered:</span> <?php echo htmlspecialchars($settings['service_area'] ?? ''); ?>
        </div>
        <div class="contact-row">
          <span class="contact-label">Lesson times:</span> <?php echo htmlspecialchars($settings['lesson_times'] ?? ''); ?>
        </div>
      </div>
      <div class="map-placeholder">
        Map Area – You can embed Google Maps here
      </div>
    </div>
  </section>

  <!-- FOOTER -->
 <footer style="text-align: center;">
  <div style="max-width: 1120px; margin: auto; padding: 30px 20px;">
    <!-- Copyright -->
    <div style="margin-bottom: 15px; font-size: 14px;">
      © <?php echo date('Y'); ?> Anab Driving School. All rights reserved.
    </div>
    
    <!-- Social Links 
    <div style="margin-bottom: 20px;">
      <a href="#" style="color: #9ca3af; margin: 0 10px;">Facebook</a>
      <a href="#" style="color: #9ca3af; margin: 0 10px;">Instagram</a>
      <a href="#" style="color: #9ca3af; margin: 0 10px;">Contact</a>
    </div>-->
    
    <!-- Developer Credit with Logo -->
    <div style="border-top: 1px solid #374151; padding-top: 15px;">
      <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
        <span style="font-size: 12px; color: #6b7280;">Developed by</span>
        <a href="#" target="_blank">
          <img src="logo/sa.png" 
               alt="SA" 
               style="height: 20px; width: auto;">
        </a>
      </div>
      <div style="font-size: 10px; color: #6b7280; margin-top: 8px;">
        All rights reserved
      </div>
    </div>
    
  </div>
</footer>

  <!-- WHATSAPP FLOAT -->
  <?php if (!empty($settings['whatsapp_number'])): 
    // Format WhatsApp number - remove all non-digits and ensure country code
    $wa_number = preg_replace('/[^0-9]/', '', $settings['whatsapp_number']);
    // If starts with 0, replace with 44 (UK country code)
    if (substr($wa_number, 0, 1) === '0') {
        $wa_number = '44' . substr($wa_number, 1);
    }
    $wa_message = urlencode("Hi, I'd like to enquire about driving lessons.");
  ?>
    <div class="wa-float">
      <a href="https://wa.me/<?php echo $wa_number; ?>?text=<?php echo $wa_message; ?>" class="wa-btn" target="_blank">
        <span class="wa-dot"></span>
        WhatsApp Us
      </a>
    </div>
  <?php endif; ?>

  <script>
    // Mobile nav toggle
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');

    if (navToggle && navLinks) {
      navToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
      });

      // Close menu when clicking a link
      navLinks.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => {
          navLinks.classList.remove('open');
        });
      });
    }
  </script>

</body>
</html>