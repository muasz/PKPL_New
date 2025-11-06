<?php
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'service' => 'PierceFlow Booking System',
    'version' => '2.0.0',
    'checks' => []
];

try {
    // Database connection check
    require_once __DIR__ . '/includes/railway_database.php';
    $conn = RailwayDatabase::getConnection();
    $health['checks']['database'] = 'connected';
    
    // Check if tables exist
    $tables = ['users', 'services', 'bookings', 'catalog', 'consultations'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $tableStatus[$table] = $result->num_rows > 0 ? 'exists' : 'missing';
    }
    
    $health['checks']['tables'] = $tableStatus;
    
} catch (Exception $e) {
    $health['status'] = 'error';
    $health['checks']['database'] = 'failed: ' . $e->getMessage();
}

// WhatsApp service check
try {
    if (file_exists(__DIR__ . '/includes/production_whatsapp.php')) {
        $health['checks']['whatsapp_service'] = 'available';
    } else {
        $health['checks']['whatsapp_service'] = 'missing';
    }
} catch (Exception $e) {
    $health['checks']['whatsapp_service'] = 'error: ' . $e->getMessage();
}

// File system check
$critical_files = [
    'index.php',
    'booking.php',
    'admin.php',
    'includes/db.php',
    'includes/railway_database.php'
];

$file_status = [];
foreach ($critical_files as $file) {
    $file_status[$file] = file_exists(__DIR__ . '/' . $file) ? 'exists' : 'missing';
}

$health['checks']['critical_files'] = $file_status;

// Environment check
$health['environment'] = [
    'php_version' => phpversion(),
    'database_url_set' => !empty($_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL')),
    'timezone' => date_default_timezone_get(),
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];

// Set appropriate HTTP status code
if ($health['status'] === 'error') {
    http_response_code(500);
} else {
    http_response_code(200);
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>