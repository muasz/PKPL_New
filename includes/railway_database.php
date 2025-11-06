<?php
// Railway Database Configuration
class RailwayDatabase {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            // Railway provides DATABASE_URL environment variable
            $databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
            
            if ($databaseUrl) {
                // Parse Railway MySQL URL format: mysql://username:password@host:port/database
                $urlParts = parse_url($databaseUrl);
                
                $host = $urlParts['host'];
                $port = $urlParts['port'] ?? 3306;
                $username = $urlParts['user'];
                $password = $urlParts['pass'];
                $database = ltrim($urlParts['path'], '/');
                
            } else {
                // Fallback to localhost for development
                $host = 'localhost';
                $port = 3306;
                $username = 'root';
                $password = '';
                $database = 'pierceflow_db';
            }
            
            try {
                self::$connection = new mysqli($host, $username, $password, $database, $port);
                
                if (self::$connection->connect_error) {
                    throw new Exception("Database connection failed: " . self::$connection->connect_error);
                }
                
                // Set charset
                self::$connection->set_charset("utf8mb4");
                
                error_log("Database connected successfully to: " . $host);
                
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw $e;
            }
        }
        
        return self::$connection;
    }
    
    public static function setupTables() {
        $conn = self::getConnection();
        
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user','admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Create services table
        $sql = "CREATE TABLE IF NOT EXISTS services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Create bookings table
        $sql = "CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            service_id INT NOT NULL,
            service_type ENUM('studio', 'home_service') NOT NULL,
            date DATE NOT NULL,
            time TIME NOT NULL,
            address TEXT,
            status ENUM('pending', 'confirmed', 'cancelled', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        )";
        $conn->query($sql);
        
        // Create catalog table  
        $sql = "CREATE TABLE IF NOT EXISTS catalog (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            image VARCHAR(255),
            category VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Create consultations table
        $sql = "CREATE TABLE IF NOT EXISTS consultations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            topik ENUM('jenis_piercing', 'prosedur_safety', 'perawatan', 'harga', 'lokasi_cocok', 'jewelry', 'home_service', 'lainnya') NOT NULL,
            pesan TEXT NOT NULL,
            status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        error_log("Database tables created successfully");
        return true;
    }
    
    public static function seedDefaultData() {
        $conn = self::getConnection();
        
        // Check if admin user exists
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $adminCount = $result->fetch_assoc()['count'];
        
        if ($adminCount == 0) {
            // Create default admin
            $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $adminName = 'Administrator';
            $adminEmail = 'admin@pierceflow.com';
            $adminPhone = '081234567890';
            $adminRole = 'admin';
            $stmt->bind_param("sssss", $adminName, $adminEmail, $adminPhone, $adminPassword, $adminRole);
            $stmt->execute();
            
            error_log("Default admin user created: admin@pierceflow.com / admin123");
        }
        
        // Check if services exist
        $result = $conn->query("SELECT COUNT(*) as count FROM services");
        $serviceCount = $result->fetch_assoc()['count'];
        
        if ($serviceCount == 0) {
            // Insert default services
            $services = [
                ['Ear Piercing', 'Piercing telinga standar dengan jewelry berkualitas', 75000, 'ear-piercing.jpg'],
                ['Nose Piercing', 'Piercing hidung dengan teknik profesional', 125000, 'nose-piercing.jpg'],
                ['Lip Piercing', 'Piercing bibir dengan berbagai pilihan style', 150000, 'lip-piercing.jpg'],
                ['Tongue Piercing', 'Piercing lidah dengan prosedur aman dan steril', 175000, 'tongue-piercing.jpg'],
                ['Eyebrow Piercing', 'Piercing alis dengan posisi yang tepat', 100000, 'eyebrow-piercing.jpg'],
                ['Belly Piercing', 'Piercing pusar dengan jewelry premium', 200000, 'belly-piercing.jpg'],
                ['Cartilage Piercing', 'Piercing tulang rawan telinga', 125000, 'cartilage-piercing.jpg'],
                ['Industrial Piercing', 'Piercing industrial dengan barbell panjang', 250000, 'industrial-piercing.jpg'],
                ['Septum Piercing', 'Piercing septum dengan jewelry circular', 150000, 'septum-piercing.jpg'],
                ['Helix Piercing', 'Piercing helix pada bagian atas telinga', 100000, 'helix-piercing.jpg']
            ];
            
            $stmt = $conn->prepare("INSERT INTO services (name, description, price, image) VALUES (?, ?, ?, ?)");
            foreach ($services as $service) {
                $stmt->bind_param("ssds", $service[0], $service[1], $service[2], $service[3]);
                $stmt->execute();
            }
            
            error_log("Default services inserted successfully");
        }
        
        return true;
    }
}
?>