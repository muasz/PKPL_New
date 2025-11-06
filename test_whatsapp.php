<?php
// Test WhatsApp Notification
require_once 'includes/notification_system_new.php';

// Test data
$testBookingData = [
    'id' => 999,
    'user_name' => 'Test User',
    'service_name' => 'Test Piercing Service',
    'date' => '2025-11-07',
    'time' => '14:30:00',
    'service_type' => 'studio',
    'price' => 150000,
    'phone' => '081234567890' // Ganti dengan nomor Anda untuk test
];

echo "<h2>🧪 Test WhatsApp Notification</h2>";

// Create WhatsApp notification instance
$whatsapp = new SimpleWhatsAppNotification();

// Send test message
echo "<p>📱 Mengirim pesan test ke: " . $testBookingData['phone'] . "</p>";

$result = $whatsapp->sendBookingConfirmation($testBookingData['phone'], $testBookingData);

echo "<h3>📊 Hasil Test:</h3>";
echo "<pre>";
print_r($result);
echo "</pre>";

if ($result['success']) {
    echo "<div style='background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
    echo "✅ <strong>Success!</strong> WhatsApp notification berhasil dikirim (atau disimulasikan)";
    echo "</div>";
} else {
    echo "<div style='background: #fef2f2; color: #dc2626; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
    echo "❌ <strong>Failed!</strong> WhatsApp notification gagal dikirim";
    echo "</div>";
}

echo "<h3>💬 Preview Pesan yang Dikirim:</h3>";
echo "<div style='background: #f3f4f6; padding: 1rem; border-radius: 8px; white-space: pre-wrap; font-family: monospace;'>";

$serviceType = $testBookingData['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';

$message = "✅ *BOOKING CONFIRMED - PIERCEFLOW*\n\n";
$message .= "Halo *" . $testBookingData['user_name'] . "*! 👋\n\n";
$message .= "Booking Anda telah berhasil dikonfirmasi:\n\n";
$message .= "📋 *Detail Booking:*\n";
$message .= "🆔 Booking ID: #" . $testBookingData['id'] . "\n";
$message .= "💎 Service: " . $testBookingData['service_name'] . "\n";
$message .= "📅 Tanggal: " . date('d F Y', strtotime($testBookingData['date'])) . "\n";
$message .= "⏰ Waktu: " . date('H:i', strtotime($testBookingData['time'])) . " WIB\n";
$message .= "🏠 Tipe: " . $serviceType . "\n";
$message .= "💰 Total: Rp " . number_format($testBookingData['price'], 0, ',', '.') . "\n\n";
$message .= "⚠️ *Penting:*\n";
$message .= "• Harap datang tepat waktu\n";
$message .= "• Bawa dokumen identitas\n";
$message .= "• Jangan konsumsi alkohol 24 jam sebelumnya\n\n";
$message .= "📞 *Butuh bantuan?*\n";
$message .= "Hubungi customer service kami kapan saja!\n\n";
$message .= "_Terima kasih telah mempercayai PierceFlow Studio_ ✨";

echo htmlspecialchars($message);
echo "</div>";

echo "<hr>";
echo "<p><a href='admin_config_notifications.php'>⚙️ Konfigurasi WhatsApp API</a></p>";
echo "<p><a href='admin_notifications.php'>🔔 Kelola Notifikasi</a></p>";
echo "<p><a href='admin.php'>🏠 Kembali ke Admin Dashboard</a></p>";
?>