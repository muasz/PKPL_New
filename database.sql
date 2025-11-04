-- Database: pierceflow_db
CREATE DATABASE IF NOT EXISTS pierceflow_db;
USE pierceflow_db;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    address TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, phone, password, role) VALUES
('Administrator', 'admin@pierceflow.local', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample services
INSERT INTO services (name, price, description) VALUES
('Tindik Telinga', 75000, 'Layanan tindik telinga profesional dengan peralatan steril. Termasuk anting stainless steel.'),
('Tindik Hidung', 85000, 'Tindik hidung dengan teknik modern dan aman. Perhiasan titanium berkualitas tinggi.'),
('Tindik Industrial', 150000, 'Tindik industrial (2 lubang) dengan barbell panjang. Proses steril dan aman.'),
('Tindik Helix', 90000, 'Tindik helix di bagian atas telinga. Perhiasan berkualitas dan proses higienis.'),
('Tindik Tragus', 95000, 'Tindik tragus dengan peralatan profesional. Termasuk konsultasi gratis.'),
('Tindik Septum', 100000, 'Tindik septum hidung dengan teknik presisi. Perhiasan medis grade tersedia.');
