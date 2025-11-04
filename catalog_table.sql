-- Tabel catalog untuk galeri/portfolio piercing
CREATE TABLE IF NOT EXISTS catalog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('telinga', 'hidung', 'industrial', 'helix', 'tragus', 'septum', 'lainnya') DEFAULT 'lainnya',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample catalog items
INSERT INTO catalog (title, image_url, description, category) VALUES
('Tindik Telinga Classic', 'https://via.placeholder.com/400x300/8b5cf6/ffffff?text=Tindik+Telinga', 'Tindik telinga klasik dengan anting stainless steel berkualitas tinggi.', 'telinga'),
('Tindik Hidung Modern', 'https://via.placeholder.com/400x300/10b981/ffffff?text=Tindik+Hidung', 'Tindik hidung dengan stud titanium yang aman dan stylish.', 'hidung'),
('Industrial Piercing', 'https://via.placeholder.com/400x300/f59e0b/ffffff?text=Industrial', 'Tindik industrial dengan barbell panjang premium.', 'industrial'),
('Helix Piercing', 'https://via.placeholder.com/400x300/6366f1/ffffff?text=Helix', 'Tindik helix di cartilage telinga bagian atas.', 'helix'),
('Tragus Piercing', 'https://via.placeholder.com/400x300/ef4444/ffffff?text=Tragus', 'Tindik tragus dengan perhiasan mini yang elegan.', 'tragus'),
('Septum Piercing', 'https://via.placeholder.com/400x300/8b5cf6/ffffff?text=Septum', 'Tindik septum dengan ring atau horseshoe jewelry.', 'septum');
