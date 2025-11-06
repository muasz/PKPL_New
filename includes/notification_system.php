<?php
// Simple Email and WhatsApp Notification without external dependencies
class SimpleEmailNotification {
    
    public function sendBookingConfirmation($bookingData, $userEmail) {
        $subject = '✅ Konfirmasi Booking PierceFlow - #' . $bookingData['booking_id'];
        $message = $this->generateBookingEmailHTML($bookingData);
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: PierceFlow Studio <noreply@pierceflow.com>' . "\r\n";
        $headers .= 'Reply-To: support@pierceflow.com' . "\r\n";
        
        return mail($userEmail, $subject, $message, $headers);
    }
    
    public function sendAdminNotification($bookingData) {
        $adminEmail = 'admin@pierceflow.com'; // Ganti dengan email admin sebenarnya
        $subject = '🔔 Booking Baru Masuk - #' . $bookingData['booking_id'];
        $message = $this->generateAdminEmailHTML($bookingData);
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: PierceFlow System <system@pierceflow.com>' . "\r\n";
        
        return mail($adminEmail, $subject, $message, $headers);
    }
    
    private function generateBookingEmailHTML($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        $statusBadge = $this->getStatusBadge($data['status']);
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Konfirmasi Booking PierceFlow</title>
            <style>
                body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f9fafb; }
                .container { max-width: 600px; margin: 0 auto; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 1.8rem; font-weight: 700; }
                .header p { margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1rem; }
                .content { padding: 2rem; }
                .booking-card { background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #8b5cf6; }
                .booking-id { font-size: 1.5rem; font-weight: 700; color: #8b5cf6; margin-bottom: 1rem; }
                .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 0.7rem 0; border-bottom: 1px solid #e5e7eb; }
                .detail-label { font-weight: 600; color: #374151; }
                .detail-value { color: #6b7280; }
                .service-type { background: #ddd6fe; color: #7c3aed; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; display: inline-block; margin-bottom: 1rem; }
                .total-price { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem; border-radius: 8px; text-align: center; margin: 1rem 0; }
                .footer { background: #1f2937; color: white; padding: 1.5rem; text-align: center; }
                .contact-info { background: #e0f2fe; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
                .whatsapp-btn { background: #25d366; color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 25px; font-weight: 600; display: inline-block; margin: 0.5rem; }
                .important-notes { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem; margin: 1rem 0; }
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
                        <div class="booking-id">Booking ID: #' . $data['booking_id'] . '</div>
                        <div class="service-type">' . $serviceType . '</div>
                        
                        <div class="detail-row">
                            <span class="detail-label">👤 Nama Customer:</span>
                            <span class="detail-value">' . htmlspecialchars($data['user_name']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">📧 Email:</span>
                            <span class="detail-value">' . htmlspecialchars($data['email']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">📱 Telepon:</span>
                            <span class="detail-value">' . htmlspecialchars($data['phone']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">💎 Service:</span>
                            <span class="detail-value">' . htmlspecialchars($data['service_name']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">📅 Tanggal:</span>
                            <span class="detail-value">' . date('d F Y', strtotime($data['booking_date'])) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">⏰ Waktu:</span>
                            <span class="detail-value">' . date('H:i', strtotime($data['booking_time'])) . ' WIB</span>
                        </div>';
                        
        $emailBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Konfirmasi Booking PierceFlow</title>
            <style>
                body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f9fafb; }
                .container { max-width: 600px; margin: 0 auto; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 1.8rem; font-weight: 700; }
                .header p { margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1rem; }
                .content { padding: 2rem; }
                .booking-card { background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid #8b5cf6; }
                .booking-id { font-size: 1.5rem; font-weight: 700; color: #8b5cf6; margin-bottom: 1rem; }
                .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 0.7rem 0; border-bottom: 1px solid #e5e7eb; }
                .detail-label { font-weight: 600; color: #374151; }
                .detail-value { color: #6b7280; }
                .service-type { background: #ddd6fe; color: #7c3aed; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; display: inline-block; margin-bottom: 1rem; }
                .total-price { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem; border-radius: 8px; text-align: center; margin: 1rem 0; }
                .footer { background: #1f2937; color: white; padding: 1.5rem; text-align: center; }
                .contact-info { background: #e0f2fe; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
                .whatsapp-btn { background: #25d366; color: white; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 25px; font-weight: 600; display: inline-block; margin: 0.5rem; }
                .important-notes { background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem; margin: 1rem 0; }
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
                        <div class="booking-id">Booking ID: #' . $data['booking_id'] . '</div>
                        <div class="service-type">' . $serviceType . '</div>
                        
                        <div class="detail-row">
                            <span class="detail-label">👤 Nama Customer:</span>
                            <span class="detail-value">' . htmlspecialchars($data['user_name']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">📧 Email:</span>
                            <span class="detail-value">' . htmlspecialchars($data['email']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">📱 Telepon:</span>
                            <span class="detail-value">' . htmlspecialchars($data['phone']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">💎 Service:</span>
                            <span class="detail-value">' . htmlspecialchars($data['service_name']) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">📅 Tanggal:</span>
                            <span class="detail-value">' . date('d F Y', strtotime($data['booking_date'])) . '</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">⏰ Waktu:</span>
                            <span class="detail-value">' . date('H:i', strtotime($data['booking_time'])) . ' WIB</span>
                        </div>';
                        
        if ($data['service_type'] == 'home_service' && !empty($data['address'])) {
            $emailBody .= '
                        <div class="detail-row">
                            <span class="detail-label">📍 Alamat:</span>
                            <span class="detail-value">' . htmlspecialchars($data['address']) . '</span>
                        </div>';
        }
        
        $emailBody .= '
                        <div class="detail-row">
                            <span class="detail-label">📋 Status:</span>
                            <span class="detail-value">' . $statusBadge . '</span>
                        </div>
                        
                        <div class="total-price">
                            <h3 style="margin: 0; font-size: 1.2rem;">💰 Total Harga</h3>
                            <div style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">Rp ' . number_format($data['price'], 0, ',', '.') . '</div>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <h4 style="margin: 0 0 0.5rem 0; color: #0369a1;">📞 Butuh Bantuan?</h4>
                        <p style="margin: 0; color: #0c4a6e;">Tim customer service kami siap membantu Anda 24/7</p>
                        <a href="https://wa.me/6281234567890?text=Halo%20PierceFlow,%20saya%20butuh%20bantuan%20terkait%20booking%20%23' . $data['booking_id'] . '" class="whatsapp-btn">💬 WhatsApp Support</a>
                    </div>
                    
                    <div class="important-notes">
                        <h4 style="margin: 0 0 0.5rem 0; color: #92400e;">⚠️ Penting untuk Diperhatikan:</h4>
                        <ul style="margin: 0; padding-left: 1.2rem; color: #92400e;">
                            <li>Harap datang tepat waktu atau 15 menit sebelum jadwal</li>
                            <li>Bawa dokumen identitas (KTP/SIM)</li>
                            <li>Jangan konsumsi alkohol 24 jam sebelum piercing</li>
                            <li>Hubungi kami jika ada perubahan jadwal</li>
                            <li>Pembayaran dapat dilakukan tunai atau transfer</li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer">
                    <h4 style="margin: 0 0 0.5rem 0;">PierceFlow Professional Studio</h4>
                    <p style="margin: 0; opacity: 0.8;">Your trusted partner for safe and stylish piercing</p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; opacity: 0.6;">
                        📍 Jl. Studio Utama No. 123, Jakarta | 📞 0812-3456-7890 | 🌐 www.pierceflow.com
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    private function generateAdminEmailHTML($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Booking Baru Masuk</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 20px; color: white; text-align: center; }
                .content { padding: 20px; }
                .booking-details { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
                .action-buttons { text-align: center; margin-top: 20px; }
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
                        <h3>Detail Booking #' . $data['booking_id'] . '</h3>
                        <p><strong>Customer:</strong> ' . htmlspecialchars($data['user_name']) . '</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
                        <p><strong>Phone:</strong> ' . htmlspecialchars($data['phone']) . '</p>
                        <p><strong>Service:</strong> ' . htmlspecialchars($data['service_name']) . '</p>
                        <p><strong>Type:</strong> ' . $serviceType . '</p>
                        <p><strong>Tanggal:</strong> ' . date('d F Y', strtotime($data['booking_date'])) . '</p>
                        <p><strong>Waktu:</strong> ' . date('H:i', strtotime($data['booking_time'])) . ' WIB</p>
                        <p><strong>Total:</strong> Rp ' . number_format($data['price'], 0, ',', '.') . '</p>
                    </div>
                    <div class="action-buttons">
                        <a href="http://localhost/PKPL_New/admin.php" class="btn btn-primary">Kelola Booking</a>
                        <a href="https://wa.me/' . preg_replace('/[^0-9]/', '', $data['phone']) . '?text=Halo%20' . urlencode($data['user_name']) . ',%20terima%20kasih%20sudah%20booking%20di%20PierceFlow" class="btn btn-success">WhatsApp Customer</a>
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

// Simple WhatsApp Notification using API
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
        
        $data = [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->token,
                'Content-Type: application/x-www-form-urlencoded'
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        // Log response untuk debugging
        error_log("WhatsApp API Response: " . $response);
        
        return ['success' => $httpCode == 200, 'response' => $response];
    }
    
    private function generateBookingMessage($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        
        $message = "✅ *BOOKING CONFIRMED - PIERCEFLOW*\n\n";
        $message .= "Halo *" . $data['user_name'] . "*! 👋\n\n";
        $message .= "Booking Anda telah berhasil dikonfirmasi:\n\n";
        $message .= "📋 *Detail Booking:*\n";
        $message .= "🆔 Booking ID: #" . $data['booking_id'] . "\n";
        $message .= "💎 Service: " . $data['service_name'] . "\n";
        $message .= "📅 Tanggal: " . date('d F Y', strtotime($data['booking_date'])) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($data['booking_time'])) . " WIB\n";
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
        $message .= "_Terima kasih telah mempercayai PierceFlow Studio_ ✨\n";
        $message .= "🌐 www.pierceflow.com";
        
        return $message;
    }
    
    private function generateAdminMessage($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio' : '🏠 Home Service';
        
        $message = "🔔 *BOOKING BARU MASUK!*\n\n";
        $message .= "📋 *Detail:*\n";
        $message .= "🆔 ID: #" . $data['booking_id'] . "\n";
        $message .= "👤 Customer: " . $data['user_name'] . "\n";
        $message .= "📱 Phone: " . $data['phone'] . "\n";
        $message .= "💎 Service: " . $data['service_name'] . "\n";
        $message .= "📅 Tanggal: " . date('d/m/Y', strtotime($data['booking_date'])) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($data['booking_time'])) . "\n";
        $message .= "🏠 Tipe: " . $serviceType . "\n";
        $message .= "💰 Total: Rp " . number_format($data['price'], 0, ',', '.') . "\n\n";
        $message .= "Segera cek admin panel untuk konfirmasi!";
        
        return $message;
    }
}

// Function untuk mengirim semua notifikasi
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
        
        // WhatsApp notifications (jika token tersedia)
        $whatsappNotification = new SimpleWhatsAppNotification();
        
        // Send WhatsApp to user
        $results['whatsapp_user'] = $whatsappNotification->sendBookingConfirmation($bookingData['phone'], $bookingData);
        
        // Send WhatsApp to admin (ganti dengan nomor admin)
        $adminPhone = '081234567890'; // Nomor admin
        $results['whatsapp_admin'] = $whatsappNotification->sendAdminNotification($adminPhone, $bookingData);
        
    } catch (Exception $e) {
        error_log("Notification error: " . $e->getMessage());
    }
    
    return $results;
}
?>