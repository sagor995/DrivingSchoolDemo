v1-- Create Database
CREATE DATABASE IF NOT EXISTS driving_school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE driving_school;

-- Admin Users Table
CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Site Settings Table
CREATE TABLE site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Packages Table
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(100),
    description TEXT,
    car_type ENUM('Manual', 'Automatic', 'Both') DEFAULT 'Both',
    features TEXT,
    is_featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Key Highlights Table
CREATE TABLE key_highlights (
    id INT PRIMARY KEY AUTO_INCREMENT,
    highlight_text VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FAQ Table
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials Table
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    review_text TEXT NOT NULL,
    lesson_type VARCHAR(100),
    rating INT DEFAULT 5,
    is_approved BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    contact_method ENUM('WhatsApp', 'Phone Call', 'Email') DEFAULT 'WhatsApp',
    package_id INT,
    lesson_type ENUM('Manual', 'Automatic', 'Either') DEFAULT 'Either',
    preferred_date DATE,
    preferred_time TIME,
    pickup_location VARCHAR(255),
    notes TEXT,
    status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
);

-- Hero Badges Table
CREATE TABLE hero_badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    badge_text VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert Default Admin (password: admin123 - CHANGE THIS!)
INSERT INTO admin_users (username, password, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'anabdrivingschool@gmail.com');

-- Insert Default Site Settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('business_name', 'Anab Driving School'),
('phone_number', '07XXX XXX XXX'),
('whatsapp_number', '07XXX XXX XXX'),
('email', 'anabdrivingschool@gmail.com'),
('service_area', 'Bolton'),
('hero_title', 'Confident Driving Starts Here.'),
('hero_subtitle', 'Manual & automatic driving lessons tailored for beginners, nervous drivers and test-ready learners. Local pick-up, flexible hours and patient, friendly instruction.'),
('hero_tag', 'DVSA-Style Professional Training'),
('about_title', 'Friendly, Patient and Focused on Real-World Driving.'),
('about_description', 'This section will introduce the real instructor – their experience, qualifications and teaching approach.'),
('lesson_times', 'Mon–Fri evenings • Sat–Sun daytime'),
('logo_path', 'uploads/logo.png'),
('terms_conditions', ''),
('privacy_policy', ''),
('facebook_url', ''),
('instagram_url', ''),
('notification_email', 'anabdrivingschool@gmail.com');

-- Insert Sample Packages
INSERT INTO packages (package_name, price, duration, description, car_type, features, display_order) VALUES
('Beginner Taster – 2 Hours', 70.00, '2 Hours', 'Perfect if you've never driven before and want to experience the car in a quiet area.', 'Both', 'Manual or automatic car|Pick-up from home or campus|Basic controls & moving off', 1),
('10 Hour Starter Pack', 290.00, '10 Hours', 'Ideal for learners starting from scratch and wanting consistent weekly lessons.', 'Both', 'Structured lesson plan|City & suburban routes|Progress updates every session', 2),
('Test Booster – 5 Hours', 160.00, '5 Hours', 'For learners with an upcoming test who want to sharpen their skills and practise routes.', 'Both', 'Focus on common test faults|Roundabouts, parking & manoeuvres|Mock test with feedback', 3),
('Motorway & Confidence Session', 50.00, '1 Hour', 'Great for new full licence holders or nervous drivers looking to gain extra confidence.', 'Both', 'Motorway & dual carriageways|Night driving or bad-weather practice|Fully customised to your needs', 4);

-- Insert Sample Key Highlights
INSERT INTO key_highlights (highlight_text, display_order) VALUES
('DVSA-qualified instructor', 1),
('Years of local driving experience around Bolton', 2),
('Flexible lesson times around work, college and school runs', 3),
('Support with theory test preparation and hazard perception', 4),
('Clear progress tracking from first drive to test day', 5);

-- Insert Sample FAQs
INSERT INTO faqs (question, answer, display_order) VALUES
('How long are your driving lessons?', 'Most lessons are 60 minutes as standard, but 90-minute and 2-hour blocks can be arranged.', 1),
('What is your cancellation policy?', 'We require 24–48 hours notice to avoid being charged. Please contact us as soon as possible if you need to reschedule.', 2),
('Do you pick up from home, college or work?', 'Yes, we offer flexible pick-up within Bolton and surrounding areas. We can collect you from home, university, or workplace.', 3);

-- Insert Sample Hero Badges
INSERT INTO hero_badges (badge_text, display_order) VALUES
('Beginner & Refresher Lessons', 1),
('Test Route Practice', 2),
('Flexible Evening & Weekend Slots', 3);

-- Insert Sample Testimonials
INSERT INTO testimonials (student_name, review_text, lesson_type, rating, display_order) VALUES
('Sarah M.', 'Honestly the best instructor I could have asked for. Calm, clear and made every lesson feel manageable instead of scary. Passed first time!', 'Manual lessons – city centre routes', 5, 1),
('James T.', 'I was a very nervous driver but the lessons were always patient and encouraging. We practised my actual test routes and that really helped.', 'Refresher & test booster package', 5, 2),
('Amira K.', 'Great communication, flexible with evenings and weekends. I liked the clear feedback after each session so I knew what to focus on next.', 'Automatic lessons – after work slots', 5, 3);