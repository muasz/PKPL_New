<?php
// Production WhatsApp API Configuration
return [
    // WhatsApp API Providers (pilih salah satu)
    'providers' => [
        'fonnte' => [
            'name' => 'Fonnte.com',
            'url' => 'https://api.fonnte.com/send',
            'free_credits' => 100,
            'setup_difficulty' => 'Easy',
            'recommended' => true
        ],
        'wablas' => [
            'name' => 'Wablas.com', 
            'url' => 'https://console.wablas.com/api/v2/send-message',
            'free_credits' => 1000,
            'setup_difficulty' => 'Medium'
        ],
        'woowa' => [
            'name' => 'WooWA.com',
            'url' => 'https://api.woowa.id/api/v1/send-message',
            'free_credits' => 50,
            'setup_difficulty' => 'Easy'
        ]
    ],
    
    // Current Configuration
    'active_provider' => 'fonnte',
    'api_token' => '', // Will be set from admin panel
    'admin_phone' => '081234567890', // Admin number for notifications
    
    // Production Settings
    'production_mode' => false, // Set true to enable real sending
    'log_messages' => true,
    'retry_failed' => true,
    'timeout' => 30
];
?>