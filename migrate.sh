#!/bin/bash

# Railway Database Migration Script
echo "Starting database migration for PierceFlow..."

# Check if DATABASE_URL is set
if [ -z "$DATABASE_URL" ]; then
    echo "Warning: DATABASE_URL not found. Using local development settings."
else
    echo "DATABASE_URL found. Connecting to Railway MySQL database..."
fi

# Setup database tables and seed data
php -r "
require_once 'includes/railway_database.php';

try {
    echo 'Connecting to database...' . PHP_EOL;
    \$conn = RailwayDatabase::getConnection();
    
    echo 'Setting up database tables...' . PHP_EOL;
    RailwayDatabase::setupTables();
    
    echo 'Seeding default data...' . PHP_EOL;
    RailwayDatabase::seedDefaultData();
    
    echo 'Database migration completed successfully!' . PHP_EOL;
    
} catch (Exception \$e) {
    echo 'Migration failed: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

echo "Database migration completed!"