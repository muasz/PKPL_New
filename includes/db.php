<?php
require_once __DIR__ . '/railway_database.php';

try {
    // Use Railway database configuration
    $conn = RailwayDatabase::getConnection();
    
    // Setup tables if needed (for first deployment)
    if (isset($_GET['setup']) && $_GET['setup'] === 'db') {
        RailwayDatabase::setupTables();
        RailwayDatabase::seedDefaultData();
        echo "Database setup completed successfully!";
        exit;
    }
    
} catch (Exception $e) {
    error_log("Database error in db.php: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>
