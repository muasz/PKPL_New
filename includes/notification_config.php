<?php
// Notification Configuration for PierceFlow
return [
    // Email Configuration
    'email' => [
        'enabled' => false, // Set true when SMTP is configured
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => 'pierceflow.official@gmail.com',
        'smtp_password' => 'your_app_password',
        'from_email' => 'noreply@pierceflow.com',
        'from_name' => 'PierceFlow Studio',
        'admin_email' => 'admin@pierceflow.com'
    ],
    
    // WhatsApp Configuration
    'whatsapp' => [
        'enabled' => false, // Set true when API key is configured
        'api_url' => 'https://api.fonnte.com/send',
        'api_token' => 'YOUR_FONNTE_TOKEN',
        'admin_phone' => '081234567890'
    ],
    
    // Development Mode
    'development' => [
        'log_notifications' => true,
        'simulate_success' => true,
        'show_debug_info' => true
    ]
];
?>