<?php
require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Handle manual notification sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notification'])) {
    $booking_id = intval($_POST['booking_id']);
    $notification_type = $_POST['notification_type'];
    
    // Get booking data
    $booking_query = "SELECT b.*, s.name as service_name, s.price, u.name as user_name, u.email, u.phone 
                     FROM bookings b 
                     JOIN services s ON b.service_id = s.id 
                     JOIN users u ON b.user_id = u.id 
                     WHERE b.id = ?";
    $stmt = $conn->prepare($booking_query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($booking_data) {
        require_once 'includes/notification_system_new.php';
        
        $result = false;
        switch ($notification_type) {
            case 'email_user':
                $emailNotification = new SimpleEmailNotification();
                $result = $emailNotification->sendBookingConfirmation($booking_data, $booking_data['email']);
                break;
            case 'whatsapp_user':
                $whatsappNotification = new SimpleWhatsAppNotification();
                $response = $whatsappNotification->sendBookingConfirmation($booking_data['phone'], $booking_data);
                $result = $response['success'] ?? false;
                break;
            case 'email_admin':
                $emailNotification = new SimpleEmailNotification();
                $result = $emailNotification->sendAdminNotification($booking_data);
                break;
        }
        
        if ($result) {
            $success = 'Notifikasi berhasil dikirim untuk booking #' . $booking_id;
        } else {
            $error = 'Gagal mengirim notifikasi untuk booking #' . $booking_id;
        }
    } else {
        $error = 'Booking tidak ditemukan.';
    }
}

// Get recent bookings for notification management
$recent_bookings_query = "SELECT b.*, s.name as service_name, s.price, u.name as user_name, u.email, u.phone,
                         DATE_FORMAT(b.created_at, '%d/%m/%Y %H:%i') as created_formatted
                         FROM bookings b 
                         JOIN services s ON b.service_id = s.id 
                         JOIN users u ON b.user_id = u.id 
                         ORDER BY b.created_at DESC 
                         LIMIT 20";
$recent_bookings = $conn->query($recent_bookings_query);
?>

<div class="container">
    <h1 class="page-title">🔔 Kelola Notifikasi</h1>
    <p class="page-subtitle">Kirim ulang notifikasi atau monitor status pengiriman</p>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Notification Info Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 2rem; border-radius: 15px; box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="background: rgba(255,255,255,0.2); padding: 0.8rem; border-radius: 10px; font-size: 1.5rem;">📧</div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700;">Email Notifications</h3>
            </div>
            <p style="margin: 0; opacity: 0.9; line-height: 1.5;">Email otomatis dikirim setelah booking berhasil. Berisi detail lengkap dan instruksi untuk customer.</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); color: white; padding: 2rem; border-radius: 15px; box-shadow: 0 8px 32px rgba(37, 211, 102, 0.3);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="background: rgba(255,255,255,0.2); padding: 0.8rem; border-radius: 10px; font-size: 1.5rem;">💬</div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700;">WhatsApp Notifications</h3>
            </div>
            <p style="margin: 0; opacity: 0.9; line-height: 1.5;">WhatsApp konfirmasi dikirim langsung ke nomor customer dengan template profesional.</p>
        </div>
        
        <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 2rem; border-radius: 15px; box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div style="background: rgba(255,255,255,0.2); padding: 0.8rem; border-radius: 10px; font-size: 1.5rem;">⚡</div>
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700;">Manual Resend</h3>
            </div>
            <p style="margin: 0; opacity: 0.9; line-height: 1.5;">Admin dapat mengirim ulang notifikasi kapan saja jika customer belum menerima.</p>
        </div>
    </div>
    
    <!-- Recent Bookings for Notification Management -->
    <div style="background: white; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; color: white;">
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                📋 Recent Bookings & Notifications
            </h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Kelola dan kirim ulang notifikasi untuk booking terbaru</p>
        </div>
        
        <div style="padding: 0;">
            <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Booking ID</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Customer</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Service</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Tanggal</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Status</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Send Notifications</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease;" 
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    
                                    <td style="padding: 1rem; font-weight: 600; color: #8b5cf6;">#<?= $booking['id'] ?></td>
                                    
                                    <td style="padding: 1rem;">
                                        <div style="font-weight: 600; color: #334155; margin-bottom: 0.2rem;"><?= htmlspecialchars($booking['user_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($booking['email']) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($booking['phone']) ?></div>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <div style="color: #475569; font-weight: 500; margin-bottom: 0.2rem;"><?= htmlspecialchars($booking['service_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;">Rp <?= number_format($booking['price'], 0, ',', '.') ?></div>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <div style="color: #475569;"><?= date('d/m/Y', strtotime($booking['date'])) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= date('H:i', strtotime($booking['time'])) ?> WIB</div>
                                        <div style="font-size: 0.75rem; color: #9ca3af;">Created: <?= $booking['created_formatted'] ?></div>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'background: linear-gradient(135deg, #f59e0b, #d97706); color: white;',
                                            'confirmed' => 'background: linear-gradient(135deg, #10b981, #059669); color: white;',
                                            'cancelled' => 'background: linear-gradient(135deg, #ef4444, #dc2626); color: white;',
                                            'rejected' => 'background: linear-gradient(135deg, #64748b, #475569); color: white;'
                                        ];
                                        $statusStyle = $statusColors[$booking['status']] ?? 'background: #e2e8f0; color: #64748b;';
                                        $status_text = [
                                            'pending' => 'Menunggu',
                                            'confirmed' => 'Dikonfirmasi',
                                            'cancelled' => 'Dibatalkan',
                                            'rejected' => 'Ditolak'
                                        ];
                                        ?>
                                        <span style="<?= $statusStyle ?> padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            <?= $status_text[$booking['status']] ?? $booking['status'] ?>
                                        </span>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                                            <!-- Email User -->
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <input type="hidden" name="notification_type" value="email_user">
                                                <input type="hidden" name="send_notification" value="1">
                                                <button type="submit" title="Send Email to Customer" style="
                                                    background: #3b82f6;
                                                    color: white;
                                                    border: none;
                                                    padding: 0.4rem 0.7rem;
                                                    border-radius: 6px;
                                                    font-size: 0.7rem;
                                                    cursor: pointer;
                                                    font-weight: 600;
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 0.2rem;
                                                ">📧 Email</button>
                                            </form>
                                            
                                            <!-- WhatsApp User -->
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <input type="hidden" name="notification_type" value="whatsapp_user">
                                                <input type="hidden" name="send_notification" value="1">
                                                <button type="submit" title="Send WhatsApp to Customer" style="
                                                    background: #25d366;
                                                    color: white;
                                                    border: none;
                                                    padding: 0.4rem 0.7rem;
                                                    border-radius: 6px;
                                                    font-size: 0.7rem;
                                                    cursor: pointer;
                                                    font-weight: 600;
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 0.2rem;
                                                ">💬 WA</button>
                                            </form>
                                            
                                            <!-- Email Admin -->
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <input type="hidden" name="notification_type" value="email_admin">
                                                <input type="hidden" name="send_notification" value="1">
                                                <button type="submit" title="Send Email to Admin" style="
                                                    background: #f59e0b;
                                                    color: white;
                                                    border: none;
                                                    padding: 0.4rem 0.7rem;
                                                    border-radius: 6px;
                                                    font-size: 0.7rem;
                                                    cursor: pointer;
                                                    font-weight: 600;
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 0.2rem;
                                                ">📋 Admin</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #64748b;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #334155;">Belum Ada Booking</h3>
                    <p style="margin: 0;">Booking akan muncul di sini setelah customer melakukan reservasi.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Notification Configuration Tips -->
    <div style="background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%); border-radius: 15px; padding: 2rem; margin-top: 2rem;">
        <h3 style="margin: 0 0 1rem 0; color: #0277bd; display: flex; align-items: center; gap: 0.5rem;">
            💡 Tips Konfigurasi Notifikasi
        </h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            <div>
                <h4 style="margin: 0 0 0.5rem 0; color: #01579b;">📧 Email Setup:</h4>
                <ul style="margin: 0; padding-left: 1.2rem; color: #0277bd; line-height: 1.6;">
                    <li>Konfigurasi SMTP server di <code>notification_system.php</code></li>
                    <li>Gunakan Gmail App Password untuk keamanan</li>
                    <li>Test email sebelum production</li>
                </ul>
            </div>
            
            <div>
                <h4 style="margin: 0 0 0.5rem 0; color: #01579b;">💬 WhatsApp Setup:</h4>
                <ul style="margin: 0; padding-left: 1.2rem; color: #0277bd; line-height: 1.6;">
                    <li>Daftar API key di Fonnte atau provider lain</li>
                    <li>Update token di <code>SimpleWhatsAppNotification</code></li>
                    <li>Set nomor admin untuk notifikasi internal</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>