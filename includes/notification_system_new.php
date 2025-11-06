<?php
// Simple Email and WhatsApp Notification System for PierceFlow
class SimpleEmailNotification {
    
    public function sendBookingConfirmation($bookingData, $userEmail) {
        // Log email instead of sending for development
        $subject = '✅ Konfirmasi Booking PierceFlow - #' . $bookingData['id'];
        $message = $this->generateBookingEmailHTML($bookingData);
        
        // Log email content for debugging
        error_log("EMAIL SENT TO: " . $userEmail);
        error_log("EMAIL SUBJECT: " . $subject);
        error_log("BOOKING ID: " . $bookingData['id']);
        error_log("CUSTOMER: " . $bookingData['user_name']);
        
        // Return true to simulate successful sending
        // Uncomment the lines below when SMTP is properly configured
        /*
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: PierceFlow Studio <noreply@pierceflow.com>' . "\r\n";
        $headers .= 'Reply-To: support@pierceflow.com' . "\r\n";
        
        return mail($userEmail, $subject, $message, $headers);
        */
        
        return true; // Simulate success for now
    }
    
    public function sendAdminNotification($bookingData) {
        $adminEmail = 'admin@pierceflow.com'; // Ganti dengan email admin sebenarnya
        $subject = '🔔 Booking Baru Masuk - #' . $bookingData['id'];
        $message = $this->generateAdminEmailHTML($bookingData);
        
        // Log admin notification for debugging
        error_log("ADMIN EMAIL NOTIFICATION");
        error_log("TO: " . $adminEmail);
        error_log("SUBJECT: " . $subject);
        error_log("NEW BOOKING FROM: " . $bookingData['user_name']);
        
        // Return true to simulate successful sending
        // Uncomment the lines below when SMTP is properly configured
        /*
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: PierceFlow System <system@pierceflow.com>' . "\r\n";
        
        return mail($adminEmail, $subject, $message, $headers);
        */
        
        return true; // Simulate success for now
    }
    
    private function generateBookingEmailHTML($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        $statusBadge = $this->getStatusBadge($data['status']);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Booking PierceFlow</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f9fafb; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #8b5cf6, #7c3aed); padding: 2rem; text-align: center; color: white; }
        .content { padding: 2rem; }
        .booking-card { background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .detail { margin: 0.5rem 0; }
        .total-price { background: #10b981; color: white; padding: 1rem; border-radius: 8px; text-align: center; margin: 1rem 0; }
        .footer { background: #1f2937; color: white; padding: 1.5rem; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Booking Confirmed!</h1>
            <p>Terima kasih telah mempercayai PierceFlow Studio</p>
        </div>
        
        <div class="content">
            <div class="booking-card">
                <h2 style="color: #8b5cf6;">Booking ID: #' . $data['id'] . '</h2>
                <div style="background: #ddd6fe; color: #7c3aed; padding: 0.5rem 1rem; border-radius: 8px; display: inline-block; margin-bottom: 1rem;">' . $serviceType . '</div>
                
                <div class="detail"><strong>👤 Nama Customer:</strong> ' . htmlspecialchars($data['user_name']) . '</div>
                <div class="detail"><strong>📧 Email:</strong> ' . htmlspecialchars($data['email']) . '</div>
                <div class="detail"><strong>📱 Telepon:</strong> ' . htmlspecialchars($data['phone']) . '</div>
                <div class="detail"><strong>💎 Service:</strong> ' . htmlspecialchars($data['service_name']) . '</div>
                <div class="detail"><strong>📅 Tanggal:</strong> ' . date('d F Y', strtotime($data['date'])) . '</div>
                <div class="detail"><strong>⏰ Waktu:</strong> ' . date('H:i', strtotime($data['time'])) . ' WIB</div>';
                
        if ($data['service_type'] == 'home_service' && !empty($data['address'])) {
            $html .= '<div class="detail"><strong>📍 Alamat:</strong> ' . htmlspecialchars($data['address']) . '</div>';
        }
        
        $html .= '<div class="detail"><strong>📋 Status:</strong> ' . $statusBadge . '</div>
                
                <div class="total-price">
                    <h3 style="margin: 0;">💰 Total Harga</h3>
                    <div style="font-size: 1.5rem; font-weight: bold; margin-top: 0.5rem;">Rp ' . number_format($data['price'], 0, ',', '.') . '</div>
                </div>
            </div>
            
            <div style="background: #e0f2fe; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                <h4 style="margin: 0 0 0.5rem 0;">📞 Butuh Bantuan?</h4>
                <p style="margin: 0;">Tim customer service kami siap membantu Anda 24/7</p>
                <a href="https://wa.me/6281234567890" style="background: #25d366; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 0.5rem;">💬 WhatsApp Support</a>
            </div>
            
            <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                <h4 style="margin: 0 0 0.5rem 0; color: #92400e;">⚠️ Penting untuk Diperhatikan:</h4>
                <ul style="margin: 0; padding-left: 1.2rem; color: #92400e;">
                    <li>Harap datang tepat waktu atau 15 menit sebelum jadwal</li>
                    <li>Bawa dokumen identitas (KTP/SIM)</li>
                    <li>Jangan konsumsi alkohol 24 jam sebelum piercing</li>
                    <li>Hubungi kami jika ada perubahan jadwal</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <h4 style="margin: 0 0 0.5rem 0;">PierceFlow Professional Studio</h4>
            <p style="margin: 0;">📍 Jl. Studio Utama No. 123, Jakarta | 📞 0812-3456-7890</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    private function generateAdminEmailHTML($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Baru Masuk</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #ef4444, #dc2626); padding: 20px; color: white; text-align: center; }
        .content { padding: 20px; }
        .booking-details { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 12px 25px; margin: 5px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-primary { background: #8b5cf6; color: white; }
        .btn-success { background: #10b981; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔔 Booking Baru Masuk!</h2>
            <p>Ada customer baru yang melakukan booking</p>
        </div>
        <div class="content">
            <div class="booking-details">
                <h3>Detail Booking #' . $data['id'] . '</h3>
                <p><strong>Customer:</strong> ' . htmlspecialchars($data['user_name']) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
                <p><strong>Phone:</strong> ' . htmlspecialchars($data['phone']) . '</p>
                <p><strong>Service:</strong> ' . htmlspecialchars($data['service_name']) . '</p>
                <p><strong>Type:</strong> ' . $serviceType . '</p>
                <p><strong>Tanggal:</strong> ' . date('d F Y', strtotime($data['date'])) . '</p>
                <p><strong>Waktu:</strong> ' . date('H:i', strtotime($data['time'])) . ' WIB</p>
                <p><strong>Total:</strong> Rp ' . number_format($data['price'], 0, ',', '.') . '</p>
            </div>
            <div style="text-align: center;">
                <a href="http://localhost/PKPL_New/admin.php" class="btn btn-primary">Kelola Booking</a>
                <a href="https://wa.me/' . preg_replace('/[^0-9]/', '', $data['phone']) . '" class="btn btn-success">WhatsApp Customer</a>
            </div>
        </div>
    </div>
</body>
</html>';
    }
    
    private function getStatusBadge($status) {
        $badges = [
            'pending' => '<span style="background: #f59e0b; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">⏳ Menunggu Konfirmasi</span>',
            'confirmed' => '<span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">✅ Dikonfirmasi</span>',
            'cancelled' => '<span style="background: #ef4444; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">❌ Dibatalkan</span>',
            'rejected' => '<span style="background: #6b7280; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">🚫 Ditolak</span>'
        ];
        
        return $badges[$status] ?? $badges['pending'];
    }
}

// Simple WhatsApp Notification
class SimpleWhatsAppNotification {
    private $apiUrl = 'https://api.fonnte.com/send';
    private $token = 'YOUR_FONNTE_TOKEN'; // Ganti dengan token sebenarnya
    
    public function sendBookingConfirmation($phoneNumber, $bookingData) {
        $message = $this->generateBookingMessage($bookingData);
        return $this->sendMessage($phoneNumber, $message);
    }
    
    public function sendAdminNotification($phoneNumber, $bookingData) {
        $message = $this->generateAdminMessage($bookingData);
        return $this->sendMessage($phoneNumber, $message);
    }
    
    private function sendMessage($phoneNumber, $message) {
        // Format nomor telepon
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Log the message for development
        error_log("WhatsApp Message to: " . $phone);
        error_log("Message content: " . $message);
        
        // Check if we have a valid token
        if ($this->token === 'YOUR_FONNTE_TOKEN' || empty($this->token)) {
            // No API token configured - simulate success
            error_log("WhatsApp API not configured - simulating success");
            return ['success' => true, 'response' => 'Simulated: Message would be sent to ' . $phone];
        }
        
        // Try to send real WhatsApp message
        $data = [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token,
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            error_log("WhatsApp API cURL error: " . $error);
            return ['success' => false, 'response' => 'cURL Error: ' . $error];
        }
        
        error_log("WhatsApp API Response: " . $response . " (HTTP " . $httpCode . ")");
        
        return ['success' => $httpCode == 200, 'response' => $response];
    }
    
    private function generateBookingMessage($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        
        $message = "✅ *BOOKING CONFIRMED - PIERCEFLOW*\n\n";
        $message .= "Halo *" . $data['user_name'] . "*! 👋\n\n";
        $message .= "Booking Anda telah berhasil dikonfirmasi:\n\n";
        $message .= "📋 *Detail Booking:*\n";
        $message .= "🆔 Booking ID: #" . $data['id'] . "\n";
        $message .= "💎 Service: " . $data['service_name'] . "\n";
        $message .= "📅 Tanggal: " . date('d F Y', strtotime($data['date'])) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($data['time'])) . " WIB\n";
        $message .= "🏠 Tipe: " . $serviceType . "\n";
        $message .= "💰 Total: Rp " . number_format($data['price'], 0, ',', '.') . "\n\n";
        
        if ($data['service_type'] == 'home_service' && !empty($data['address'])) {
            $message .= "📍 Alamat: " . $data['address'] . "\n\n";
        }
        
        $message .= "⚠️ *Penting:*\n";
        $message .= "• Harap datang tepat waktu\n";
        $message .= "• Bawa dokumen identitas\n";
        $message .= "• Jangan konsumsi alkohol 24 jam sebelumnya\n\n";
        $message .= "📞 *Butuh bantuan?*\n";
        $message .= "Hubungi customer service kami kapan saja!\n\n";
        $message .= "_Terima kasih telah mempercayai PierceFlow Studio_ ✨";
        
        return $message;
    }
    
    private function generateAdminMessage($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio' : '🏠 Home Service';
        
        $message = "🔔 *BOOKING BARU MASUK!*\n\n";
        $message .= "📋 *Detail:*\n";
        $message .= "🆔 ID: #" . $data['id'] . "\n";
        $message .= "👤 Customer: " . $data['user_name'] . "\n";
        $message .= "📱 Phone: " . $data['phone'] . "\n";
        $message .= "💎 Service: " . $data['service_name'] . "\n";
        $message .= "📅 Tanggal: " . date('d/m/Y', strtotime($data['date'])) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($data['time'])) . "\n";
        $message .= "🏠 Tipe: " . $serviceType . "\n";
        $message .= "💰 Total: Rp " . number_format($data['price'], 0, ',', '.') . "\n\n";
        $message .= "Segera cek admin panel untuk konfirmasi!";
        
        return $message;
    }
}

// Main function to send all notifications
function sendBookingNotifications($bookingData) {
    $results = [
        'email_user' => false,
        'email_admin' => false,
        'whatsapp_user' => false,
        'whatsapp_admin' => false
    ];
    
    try {
        // Email notifications
        $emailNotification = new SimpleEmailNotification();
        
        // Send email to user
        $results['email_user'] = $emailNotification->sendBookingConfirmation($bookingData, $bookingData['email']);
        
        // Send email to admin
        $results['email_admin'] = $emailNotification->sendAdminNotification($bookingData);
        
        // WhatsApp notifications
        $whatsappNotification = new SimpleWhatsAppNotification();
        
        // Send WhatsApp to user
        $results['whatsapp_user'] = $whatsappNotification->sendBookingConfirmation($bookingData['phone'], $bookingData);
        
        // Send WhatsApp to admin (ganti dengan nomor admin sebenarnya)
        $adminPhone = '081234567890';
        $results['whatsapp_admin'] = $whatsappNotification->sendAdminNotification($adminPhone, $bookingData);
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
    }
    
    return $results;
}
?>