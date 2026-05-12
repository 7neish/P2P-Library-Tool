
DROP DATABASE IF EXISTS p2p_tool_library;
CREATE DATABASE p2p_tool_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE p2p_tool_library;


CREATE TABLE membership_tiers (
    tier_id INT AUTO_INCREMENT PRIMARY KEY,
    tier_name VARCHAR(50) NOT NULL,
    discount_rate DECIMAL(5,2) DEFAULT 0,
    min_rentals_required INT DEFAULT 0,
    min_trust_score DECIMAL(5,2) DEFAULT 0
);


CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    latitude DECIMAL(10,7) DEFAULT NULL,
    longitude DECIMAL(10,7) DEFAULT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'MEMBER', 
    current_trust_score DECIMAL(5,2) DEFAULT 50.00, 
    tier_id INT DEFAULT 1,
    wallet_balance DECIMAL(15,2) DEFAULT 0,
    referral_code VARCHAR(50) UNIQUE,
    referred_by_id INT DEFAULT NULL,
    kyc_status VARCHAR(20) DEFAULT 'PENDING', 
    is_blacklisted TINYINT(1) DEFAULT 0,
    suspension_end_date DATE DEFAULT NULL,
    total_borrow_count INT DEFAULT 0,
    on_time_return_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tier_id) REFERENCES membership_tiers(tier_id)
);


CREATE TABLE zones (
    zone_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    librarian_id INT,
    center_latitude DECIMAL(10,7),
    center_longitude DECIMAL(10,7),
    radius_km DECIMAL(6,2) DEFAULT 5,
    FOREIGN KEY (librarian_id) REFERENCES users(user_id)
);


CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    level INT DEFAULT 1,
    description VARCHAR(255),
    FOREIGN KEY (parent_id) REFERENCES categories(category_id)
);


CREATE TABLE tools (
    tool_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    category_id INT NOT NULL,
    zone_id INT,
    tool_name VARCHAR(255) NOT NULL,
    description TEXT,
    serial_number VARCHAR(100),
    hourly_rate DECIMAL(10,2) DEFAULT 0,
    daily_rate DECIMAL(10,2) DEFAULT 0,
    weekly_rate DECIMAL(10,2) DEFAULT 0,
    deposit_amount DECIMAL(10,2) DEFAULT 0,
    buffer_hours INT DEFAULT 10, 
    status VARCHAR(20) DEFAULT 'AVAILABLE', 
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id)
);

CREATE TABLE tool_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    tool_id INT NOT NULL,
    doc_type VARCHAR(50), 
    file_url VARCHAR(500),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE,
    FOREIGN KEY (tool_id) REFERENCES tools(tool_id)
);


CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    tool_id INT NOT NULL,
    borrower_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    actual_return_time DATETIME DEFAULT NULL,
    rental_cost DECIMAL(10,2) DEFAULT 0,
    deposit_amount DECIMAL(10,2) DEFAULT 0,
    total_price DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'PENDING', 
    qr_handover_code VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tool_id) REFERENCES tools(tool_id),
    FOREIGN KEY (borrower_id) REFERENCES users(user_id)
);


CREATE TABLE escrow_transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'HELD', 
    transaction_type VARCHAR(20) DEFAULT 'DEPOSIT', 
    notes VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);


CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment VARCHAR(500),
    tool_condition_rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id)
);


CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    booking_id INT,
    encrypted_content TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);


CREATE TABLE damage_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    description TEXT,
    photo_evidence_url VARCHAR(500),
    estimated_repair_cost DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'PENDING', 
    technician_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (technician_id) REFERENCES users(user_id)
);


CREATE TABLE maintenance_logs (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    tool_id INT NOT NULL,
    technician_id INT,
    task_description TEXT,
    cost DECIMAL(10,2),
    usage_hours_at_service INT,
    service_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_service_due TIMESTAMP NULL,
    FOREIGN KEY (tool_id) REFERENCES tools(tool_id),
    FOREIGN KEY (technician_id) REFERENCES users(user_id)
);


CREATE TABLE promotions (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    promo_code VARCHAR(50) UNIQUE,
    discount_percent DECIMAL(5,2),
    category_id INT NULL,
    start_date DATE,
    end_date DATE
);



INSERT INTO membership_tiers (tier_name, discount_rate, min_rentals_required, min_trust_score) VALUES
('Basic',   0.00, 0, 0),
('Pro',    15.00, 5, 70),
('Elite',  25.00, 20, 85);


INSERT INTO users (email, password_hash, full_name, phone, address, latitude, longitude, role, current_trust_score, tier_id, wallet_balance, referral_code, kyc_status) VALUES
('admin@library.com',     '$2y$12$9onE1z4I2qgk4hf/fwtpuOUG/puCBw57MbbOu4sau3i1pv0L9dFtm', 'Admin User',      '01000000001', 'Maadi, Cairo',     29.9602, 31.2569, 'ADMIN',      100.00, 3, 0,    'ADMIN1',  'VERIFIED'),
('librarian@library.com', '$2y$12$9onE1z4I2qgk4hf/fwtpuOUG/puCBw57MbbOu4sau3i1pv0L9dFtm', 'Karim Librarian', '01000000002', 'Maadi, Cairo',     29.9602, 31.2569, 'LIBRARIAN',  95.00,  3, 0,    'LIB001',  'VERIFIED'),
('tech@library.com',      '$2y$12$9onE1z4I2qgk4hf/fwtpuOUG/puCBw57MbbOu4sau3i1pv0L9dFtm', 'Sami Technician', '01000000003', 'Nasr City, Cairo', 30.0566, 31.3447, 'TECHNICIAN', 90.00,  2, 0,    'TECH01',  'VERIFIED'),
('ahmed@example.com',     '$2y$12$9onE1z4I2qgk4hf/fwtpuOUG/puCBw57MbbOu4sau3i1pv0L9dFtm', 'Ahmed Hassan',    '01000000004', 'Maadi, Cairo',     29.9602, 31.2569, 'MEMBER',     85.00,  2, 500,  'AHM001',  'VERIFIED'),
('mariam@example.com',    '$2y$12$9onE1z4I2qgk4hf/fwtpuOUG/puCBw57MbbOu4sau3i1pv0L9dFtm', 'Mariam Ali',      '01000000005', 'Maadi, Cairo',     29.9601, 31.2570, 'MEMBER',     92.00,  2, 1000, 'MAR001',  'VERIFIED'),
('khaled@example.com',    '$2y$12$9onE1z4I2qgk4hf/fwtpuOUG/puCBw57MbbOu4sau3i1pv0L9dFtm', 'Khaled Omar',     '01000000006', 'Nasr City, Cairo', 30.0566, 31.3447, 'MEMBER',     65.00,  1, 200,  'KHA001',  'VERIFIED');

INSERT INTO zones (name, librarian_id, center_latitude, center_longitude, radius_km) VALUES
('Maadi Zone',     2, 29.9602, 31.2569, 5),
('Nasr City Zone', 2, 30.0566, 31.3447, 5);

INSERT INTO categories (parent_id, name, level, description) VALUES
(NULL, 'Power Tools',     1, 'Electric and battery tools'),
(NULL, '3D Printing',     1, 'Printers and accessories'),
(NULL, 'Garden Tools',    1, 'Outdoor equipment'),
(1,    'Drills',          2, 'All types of drills'),
(1,    'Saws',            2, 'Cutting tools'),
(4,    'SDS Drills',      3, 'Heavy duty rotary hammer drills'),
(4,    'Impact Drills',   3, 'Impact drivers');

INSERT INTO tools (owner_id, category_id, zone_id, tool_name, description, serial_number, hourly_rate, daily_rate, weekly_rate, deposit_amount, buffer_hours, status, latitude, longitude) VALUES
(5, 6, 1, 'Bosch SDS Drill GBH-2-26',     'Heavy duty rotary hammer drill, perfect for concrete', 'BSH-2026-001', 50, 200, 1000, 500,  10, 'AVAILABLE', 29.9601, 31.2570),
(5, 2, 1, 'Creality Ender 3 V2 Printer',  '3D printer, includes 500g PLA filament',                'CR-ENDR-002',  80, 350, 1800, 3000, 24, 'AVAILABLE', 29.9601, 31.2570),
(4, 5, 1, 'Makita Circular Saw',          'Professional 7-inch circular saw',                      'MK-CIRC-003',  40, 150, 800,  400,  10, 'AVAILABLE', 29.9602, 31.2569),
(6, 7, 2, 'DeWalt Impact Driver',         '20V max cordless impact driver kit',                    'DW-IMP-004',   30, 120, 600,  300,  8,  'AVAILABLE', 30.0566, 31.3447);

INSERT INTO tool_documents (tool_id, doc_type, file_url, expiry_date) VALUES
(1, 'MANUAL',       'docs/bosch_drill_manual.pdf',     '2027-12-31'),
(1, 'SAFETY_VIDEO', 'docs/bosch_safety.mp4',           NULL),
(1, 'WARRANTY',     'docs/bosch_warranty.pdf',         '2026-12-31'),
(2, 'MANUAL',       'docs/ender3_manual.pdf',          NULL);

INSERT INTO promotions (promo_code, discount_percent, start_date, end_date) VALUES
('WELCOME10', 10.00, '2026-01-01', '2026-12-31'),
('SUMMER20',  20.00, '2026-06-01', '2026-08-31');
