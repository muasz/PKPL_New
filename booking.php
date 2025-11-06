<?php
require_once 'includes/header.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$selected_service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

// Ambil data layanan
$services_query = "SELECT * FROM services ORDER BY name ASC";
$services_result = $conn->query($services_query);

// Proses form booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = intval($_POST['service_id']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $service_type = $_POST['service_type'] ?? '';
    $address = trim($_POST['address']);
    $user_id = $_SESSION['user_id'];
    
    // Validasi
    if (empty($service_id) || empty($date) || empty($time) || empty($service_type)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($service_type === 'home_service' && empty($address)) {
        $error = 'Alamat wajib diisi untuk layanan home service!';
    } else {
        // Validasi tanggal tidak boleh di masa lalu
        $booking_date = new DateTime($date);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($booking_date < $today) {
            $error = 'Tanggal booking tidak boleh di masa lalu!';
        } else {
            // Cek konflik jadwal (tanggal dan waktu yang sama)
            $stmt = $conn->prepare("SELECT id FROM bookings WHERE date = ? AND time = ? AND status != 'cancelled' AND status != 'rejected'");
            $stmt->bind_param("ss", $date, $time);
            $stmt->execute();
            $conflict_result = $stmt->get_result();
            
            if ($conflict_result->num_rows > 0) {
                $error = 'Jadwal tersebut sudah dibooking! Silakan pilih waktu lain.';
            } else {
                // Insert booking
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, service_type, date, time, address, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("iissss", $user_id, $service_id, $service_type, $date, $time, $address);
                
                if ($stmt->execute()) {
                    $booking_id = $conn->insert_id; // Get the inserted booking ID
                    
                    // Get complete booking data for notifications
                    $booking_query = "SELECT b.*, s.name as service_name, s.price, u.name as user_name, u.email, u.phone 
                                    FROM bookings b 
                                    JOIN services s ON b.service_id = s.id 
                                    JOIN users u ON b.user_id = u.id 
                                    WHERE b.id = ?";
                    $stmt_booking = $conn->prepare($booking_query);
                    $stmt_booking->bind_param("i", $booking_id);
                    $stmt_booking->execute();
                    $booking_data = $stmt_booking->get_result()->fetch_assoc();
                    $stmt_booking->close();
                    
                    // Send notifications using production service
                    require_once 'includes/production_whatsapp.php';
                    require_once 'includes/notification_system_new.php';
                    
                    $whatsappService = new ProductionWhatsAppService();
                    
                    // Send WhatsApp notifications
                    $whatsapp_user_result = $whatsappService->sendBookingNotification($booking_data['phone'], $booking_data);
                    $whatsapp_admin_result = $whatsappService->sendAdminAlert('081234567890', $booking_data); // Admin number
                    
                    // Send email notifications (still in development mode)
                    $emailNotification = new SimpleEmailNotification();
                    $email_user_result = $emailNotification->sendBookingConfirmation($booking_data, $booking_data['email']);
                    $email_admin_result = $emailNotification->sendAdminNotification($booking_data);
                    
                    $notification_results = [
                        'email_user' => $email_user_result,
                        'email_admin' => $email_admin_result,
                        'whatsapp_user' => $whatsapp_user_result,
                        'whatsapp_admin' => $whatsapp_admin_result
                    ];
                    
                    // Success message with notification status
                    $success = 'Booking berhasil! Status: Menunggu konfirmasi.';
                    
                    // Add notification status to success message  
                    $whatsapp_status = $whatsappService->getStatus();
                    
                    if ($notification_results['email_user']) {
                        $success .= '<br>📧 Email konfirmasi telah diproses untuk ' . $booking_data['email'] . ' (Log Mode)';
                    }
                    
                    if ($notification_results['whatsapp_user']['success']) {
                        if (isset($notification_results['whatsapp_user']['simulated']) && $notification_results['whatsapp_user']['simulated']) {
                            $success .= '<br>💬 WhatsApp konfirmasi telah diproses untuk ' . $booking_data['phone'] . ' (Mode Simulasi)';
                        } else {
                            $success .= '<br>🚀 WhatsApp konfirmasi berhasil dikirim ke ' . $booking_data['phone'] . ' (Live)';
                        }
                    }
                    
                    $success .= '<br><br>🔔 <strong>Status Notifikasi:</strong><br>';
                    $success .= '✅ Email: Development Mode (Logged)<br>';
                    $success .= '✅ WhatsApp: ' . ($whatsapp_status['production_mode'] ? 'Production Mode (Live)' : 'Development Mode (Simulasi)') . '<br>';
                    $success .= '✅ Provider: ' . ucfirst($whatsapp_status['active_provider']) . '<br>';
                    
                    if (!$whatsapp_status['production_mode']) {
                        $success .= '<br><small>💡 <strong>Admin:</strong> Aktifkan Production Mode di <a href="admin_config_notifications.php" style="color: #8b5cf6;">Config Panel</a> untuk WhatsApp live</small>';
                    }
                    
                    // Log notification results for admin
                    error_log("Booking #$booking_id notifications sent - Email: " . 
                             ($notification_results['email_user'] ? 'SUCCESS' : 'FAILED') . 
                             ", WhatsApp: " . (($notification_results['whatsapp_user']['success'] ?? false) ? 'SUCCESS' : 'FAILED'));
                    
                    // Reset form
                    $selected_service_id = 0;
                } else {
                    $error = 'Terjadi kesalahan. Silakan coba lagi.';
                }
            }
            $stmt->close();
        }
    }
}
?>

<!-- Modern Booking Page -->
<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh;">
    
    <!-- Modern Page Header -->
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 4rem 0; text-align: center; position: relative; overflow: hidden;">
        <!-- Background Pattern -->
        <div style="position: absolute; inset: 0; opacity: 0.1; background-image: radial-gradient(circle at 30% 30%, white 2px, transparent 2px), radial-gradient(circle at 70% 70%, white 2px, transparent 2px); background-size: 35px 35px;"></div>
        
        <div class="container" style="position: relative; z-index: 2;">
            <div style="max-width: 600px; margin: 0 auto;">
                <h1 style="margin: 0 0 1rem 0; font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; line-height: 1.2;">
                    📅 Booking Layanan
                </h1>
                <p style="margin: 0; font-size: 1.2rem; opacity: 0.95; line-height: 1.6;">
                    Isi form di bawah untuk membuat reservasi piercing profesional dan aman
                </p>
                
                <!-- User Welcome -->
                <div style="background: rgba(255, 255, 255, 0.1); padding: 1rem 1.5rem; border-radius: 15px; margin-top: 2rem; backdrop-filter: blur(10px);">
                    <div style="font-size: 1rem; opacity: 0.9;">
                        👋 Halo, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>! Siap untuk booking layanan piercing?
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container" style="padding: 4rem 0;">
        <!-- Alert Messages -->
        <?php if ($error): ?>
            <div style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 500; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 2rem; border-radius: 20px; margin-bottom: 2rem; text-align: center; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
                <div style="font-size: 1.2rem; font-weight: 600; margin-bottom: 1rem;">
                    ✅ <?= htmlspecialchars($success) ?>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem;">
                    <a href="dashboard.php" style="
                        background: rgba(255, 255, 255, 0.9);
                        color: #059669;
                        text-decoration: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 12px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='white'; this.style.transform='translateY(-2px)'" 
                       onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.transform='translateY(0)'">
                        📊 Lihat Dashboard
                    </a>
                    <a href="booking.php" style="
                        background: transparent;
                        color: white;
                        text-decoration: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 12px;
                        font-weight: 600;
                        border: 2px solid rgba(255, 255, 255, 0.5);
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(-2px)'" 
                       onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                        📅 Booking Lagi
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Modern Booking Form -->
        <div style="background: white; border-radius: 25px; padding: 0; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
            <!-- Form Header -->
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 2rem; text-align: center;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    📝 Form Booking
                </h2>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.95rem;">
                    Lengkapi informasi di bawah untuk reservasi Anda
                </p>
            </div>
            
            <!-- Form Content -->
            <div style="padding: 2.5rem;">
                <form method="POST" action="" id="bookingForm" onsubmit="return validateForm()" style="display: grid; gap: 2rem;">
                    
                    <!-- Service Selection -->
                    <div>
                        <label style="display: block; margin-bottom: 0.8rem; font-weight: 700; color: #334155; font-size: 1.1rem;">
                            🛠️ Pilih Layanan *
                        </label>
                        <select name="service_id" required style="
                            width: 100%;
                            padding: 1rem 1.2rem;
                            border: 2px solid #e2e8f0;
                            border-radius: 15px;
                            font-size: 1rem;
                            transition: all 0.3s ease;
                            background: white;
                            cursor: pointer;
                            font-weight: 500;
                        " onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'" 
                           onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            <option value="">-- Pilih Layanan --</option>
                            <?php if ($services_result && $services_result->num_rows > 0): ?>
                                <?php while ($service = $services_result->fetch_assoc()): ?>
                                    <option value="<?= $service['id'] ?>" 
                                            <?= ($selected_service_id == $service['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['name']) ?> - Rp <?= number_format($service['price'], 0, ',', '.') ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Service Type Selection -->
                    <div>
                        <label style="display: block; margin-bottom: 0.8rem; font-weight: 700; color: #334155; font-size: 1.1rem;">
                            📍 Pilih Jenis Layanan *
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <!-- Studio Visit Option -->
                            <label style="
                                display: flex;
                                align-items: center;
                                padding: 1.2rem;
                                border: 2px solid #e2e8f0;
                                border-radius: 15px;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                background: white;
                            " onmouseover="this.style.borderColor='#8b5cf6'; this.style.backgroundColor='#faf5ff'"
                               onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='white'">
                                <input type="radio" name="service_type" value="studio" required 
                                       <?= (isset($_POST['service_type']) && $_POST['service_type'] === 'studio') ? 'checked' : '' ?>
                                       style="margin-right: 0.8rem; transform: scale(1.2);"
                                       onchange="toggleAddressField()">
                                <div>
                                    <div style="font-weight: 700; color: #8b5cf6; font-size: 1rem; margin-bottom: 0.3rem;">
                                        🏢 Datang ke Studio
                                    </div>
                                    <div style="font-size: 0.85rem; color: #64748b;">
                                        Kunjungi studio kami untuk layanan profesional
                                    </div>
                                </div>
                            </label>
                            
                            <!-- Home Service Option -->
                            <label style="
                                display: flex;
                                align-items: center;
                                padding: 1.2rem;
                                border: 2px solid #e2e8f0;
                                border-radius: 15px;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                background: white;
                            " onmouseover="this.style.borderColor='#10b981'; this.style.backgroundColor='#f0fdf4'"
                               onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='white'">
                                <input type="radio" name="service_type" value="home_service" required 
                                       <?= (isset($_POST['service_type']) && $_POST['service_type'] === 'home_service') ? 'checked' : '' ?>
                                       style="margin-right: 0.8rem; transform: scale(1.2);"
                                       onchange="toggleAddressField()">
                                <div>
                                    <div style="font-weight: 700; color: #10b981; font-size: 1rem; margin-bottom: 0.3rem;">
                                        🏠 Home Service
                                    </div>
                                    <div style="font-size: 0.85rem; color: #64748b;">
                                        Kami datang ke lokasi Anda
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Date and Time Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- Date Selection -->
                        <div>
                            <label style="display: block; margin-bottom: 0.8rem; font-weight: 700; color: #334155; font-size: 1.1rem;">
                                📅 Tanggal *
                            </label>
                            <input type="date" name="date" required 
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= isset($_POST['date']) ? htmlspecialchars($_POST['date']) : '' ?>"
                                   style="
                                       width: 100%;
                                       padding: 1rem 1.2rem;
                                       border: 2px solid #e2e8f0;
                                       border-radius: 15px;
                                       font-size: 1rem;
                                       transition: all 0.3s ease;
                                       background: white;
                                       font-weight: 500;
                                   "
                                   onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'"
                                   onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                        
                        <!-- Time Selection -->
                        <div>
                            <label style="display: block; margin-bottom: 0.8rem; font-weight: 700; color: #334155; font-size: 1.1rem;">
                                ⏰ Waktu *
                            </label>
                            <select name="time" required style="
                                width: 100%;
                                padding: 1rem 1.2rem;
                                border: 2px solid #e2e8f0;
                                border-radius: 15px;
                                font-size: 1rem;
                                transition: all 0.3s ease;
                                background: white;
                                cursor: pointer;
                                font-weight: 500;
                            " onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'" 
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                                <option value="">-- Pilih Waktu --</option>
                                <option value="09:00:00">🌅 09:00 - Pagi</option>
                                <option value="10:00:00">🌅 10:00 - Pagi</option>
                                <option value="11:00:00">🌅 11:00 - Pagi</option>
                                <option value="12:00:00">☀️ 12:00 - Siang</option>
                                <option value="13:00:00">☀️ 13:00 - Siang</option>
                                <option value="14:00:00">☀️ 14:00 - Siang</option>
                                <option value="15:00:00">🌤️ 15:00 - Sore</option>
                                <option value="16:00:00">🌤️ 16:00 - Sore</option>
                                <option value="17:00:00">🌤️ 17:00 - Sore</option>
                                <option value="18:00:00">🌆 18:00 - Sore</option>
                                <option value="19:00:00">🌆 19:00 - Malam</option>
                                <option value="20:00:00">🌙 20:00 - Malam</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Address Section (Dynamic based on service type) -->
                    <div id="addressField" style="display: none;">
                        <label style="display: block; margin-bottom: 0.8rem; font-weight: 700; color: #334155; font-size: 1.1rem;">
                            <span id="addressLabel">📍 Informasi Alamat</span>
                        </label>
                        <textarea name="address" id="addressInput" rows="4"
                                  placeholder="Masukkan alamat lengkap untuk layanan home service..."
                                  style="
                                      width: 100%;
                                      padding: 1rem 1.2rem;
                                      border: 2px solid #e2e8f0;
                                      border-radius: 15px;
                                      font-size: 1rem;
                                      transition: all 0.3s ease;
                                      background: white;
                                      resize: vertical;
                                      font-family: inherit;
                                  "
                                  onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'"
                                  onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                        
                        <!-- Studio Address Info with Map (when studio is selected) -->
                        <div id="studioInfo" style="display: none; margin-top: 1rem; padding: 1.5rem; background: linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius: 15px; border-left: 4px solid #8b5cf6; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.1);">
                            <div style="font-weight: 700; color: #8b5cf6; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.2rem;">
                                🏢 Lokasi Studio Kami
                            </div>
                            
                            <!-- Studio Info Grid -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                                <!-- Address Details -->
                                <div style="background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                    <div style="color: #475569; line-height: 1.8;">
                                        <div style="font-weight: 700; color: #8b5cf6; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                            📍 <strong>PierceFlow Studio</strong>
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">
                                            📍 Jl. Profesional No. 123, Kota Jakarta Selatan
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">
                                            🕒 Senin - Minggu, 09:00 - 21:00
                                        </div>
                                        <div style="margin-bottom: 0.5rem;">
                                            📞 (021) 1234-5678
                                        </div>
                                        <div>
                                            📧 info@pierceflow.com
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick Actions -->
                                <div style="background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                    <div style="font-weight: 700; color: #8b5cf6; margin-bottom: 0.8rem;">
                                        🚀 Quick Actions
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                        <a href="https://maps.google.com/?q=Jl.+Profesional+No.+123,+Jakarta+Selatan" 
                                           target="_blank"
                                           style="
                                               display: flex;
                                               align-items: center;
                                               gap: 0.5rem;
                                               padding: 0.5rem;
                                               background: #f0fdf4;
                                               color: #16a34a;
                                               text-decoration: none;
                                               border-radius: 8px;
                                               font-size: 0.9rem;
                                               font-weight: 600;
                                               transition: all 0.3s ease;
                                           "
                                           onmouseover="this.style.background='#dcfce7'"
                                           onmouseout="this.style.background='#f0fdf4'">
                                            🗺️ Buka di Google Maps
                                        </a>
                                        <a href="tel:+622112345678"
                                           style="
                                               display: flex;
                                               align-items: center;
                                               gap: 0.5rem;
                                               padding: 0.5rem;
                                               background: #eff6ff;
                                               color: #2563eb;
                                               text-decoration: none;
                                               border-radius: 8px;
                                               font-size: 0.9rem;
                                               font-weight: 600;
                                               transition: all 0.3s ease;
                                           "
                                           onmouseover="this.style.background='#dbeafe'"
                                           onmouseout="this.style.background='#eff6ff'">
                                            📞 Hubungi Studio
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Google Maps Embed -->
                            <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.1234567890!2d106.8123456!3d-6.2345678!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTQnMDQuNCJTIDEwNsKwNDgnNTIuNCJF!5e0!3m2!1sen!2sid!4v1699123456789!5m2!1sen!2sid"
                                    width="100%" 
                                    height="300" 
                                    style="border:0; border-radius: 12px;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                            
                            <!-- Map Instructions -->
                            <div style="margin-top: 1rem; padding: 1rem; background: white; border-radius: 12px; border-left: 4px solid #10b981;">
                                <div style="font-weight: 700; color: #10b981; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                    🧭 Petunjuk Arah:
                                </div>
                                <div style="color: #475569; font-size: 0.9rem; line-height: 1.6;">
                                    • <strong>Dari Stasiun MRT Blok M:</strong> Naik ojek online 15 menit<br>
                                    • <strong>Dari Terminal Blok M:</strong> Naik busway TransJakarta koridor 1<br>
                                    • <strong>Parkir:</strong> Tersedia parkir gratis di basement gedung<br>
                                    • <strong>Landmark:</strong> Seberang Mall Plaza Senayan, gedung warna biru
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Important Notes -->
                    <div style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border-left: 4px solid #0ea5e9; padding: 1.5rem; border-radius: 15px;">
                        <div style="display: flex; align-items: flex-start; gap: 0.8rem;">
                            <div style="font-size: 1.5rem;">ℹ️</div>
                            <div>
                                <div style="font-weight: 700; color: #0c4a6e; margin-bottom: 0.5rem; font-size: 1rem;">
                                    Catatan Penting:
                                </div>
                                <ul style="margin: 0; padding-left: 1rem; color: #0c4a6e; line-height: 1.6; font-size: 0.95rem;">
                                    <li>Booking akan dikonfirmasi admin dalam 1x24 jam</li>
                                    <li>Pastikan tanggal dan waktu yang Anda pilih sesuai</li>
                                    <li>Anda akan menerima notifikasi status di dashboard</li>
                                    <li>Untuk perubahan jadwal, hubungi admin terlebih dahulu</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" style="
                        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                        color: white;
                        border: none;
                        padding: 1.2rem 2rem;
                        border-radius: 15px;
                        font-size: 1.1rem;
                        font-weight: 700;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                        width: 100%;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(139, 92, 246, 0.4)'" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(139, 92, 246, 0.3)'">
                        🚀 Kirim Booking Sekarang
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div style="margin-top: 2rem; text-align: center;">
            <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 1rem 0; color: #334155; font-weight: 700;">⚡ Quick Links</h3>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="catalog.php" style="
                        background: #f8fafc;
                        color: #475569;
                        text-decoration: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 12px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        border: 1px solid #e2e8f0;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='#e2e8f0'; this.style.transform='translateY(-1px)'" 
                       onmouseout="this.style.background='#f8fafc'; this.style.transform='translateY(0)'">
                        � Lihat Katalog
                    </a>
                    <a href="dashboard.php" style="
                        background: #f8fafc;
                        color: #475569;
                        text-decoration: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 12px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        border: 1px solid #e2e8f0;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='#e2e8f0'; this.style.transform='translateY(-1px)'" 
                       onmouseout="this.style.background='#f8fafc'; this.style.transform='translateY(0)'">
                        📊 Dashboard Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle address field based on service type
    function toggleAddressField() {
        const serviceType = document.querySelector('input[name="service_type"]:checked');
        const addressField = document.getElementById('addressField');
        const addressInput = document.getElementById('addressInput');
        const addressLabel = document.getElementById('addressLabel');
        const studioInfo = document.getElementById('studioInfo');
        
        console.log('toggleAddressField called, serviceType:', serviceType ? serviceType.value : 'none');
        
        if (serviceType) {
            addressField.style.display = 'block';
            
            if (serviceType.value === 'home_service') {
                // Show address input for home service
                addressInput.style.display = 'block';
                addressInput.required = true;
                addressLabel.innerHTML = '🏠 Alamat Home Service *';
                addressInput.placeholder = 'Masukkan alamat lengkap untuk layanan home service...';
                studioInfo.style.display = 'none';
                
                console.log('Showing home service address input');
            } else if (serviceType.value === 'studio') {
                // Hide address input and show studio map/info
                addressInput.style.display = 'none';
                addressInput.required = false;
                addressInput.value = '';
                addressLabel.innerHTML = '🗺️ Lokasi Studio Kami';
                
                // Show studio info with smooth transition
                studioInfo.style.display = 'block';
                studioInfo.style.opacity = '0';
                setTimeout(() => {
                    studioInfo.style.transition = 'opacity 0.5s ease-in-out';
                    studioInfo.style.opacity = '1';
                }, 50);
                
                console.log('Showing studio map and info');
            }
        } else {
            addressField.style.display = 'none';
            addressInput.required = false;
            console.log('No service type selected, hiding address field');
        }
    }

    // Form validation
    function validateForm() {
        const nama = document.querySelector('input[name="nama"]').value;
        const email = document.querySelector('input[name="email"]').value;
        const phone = document.querySelector('input[name="phone"]').value;
        const service = document.querySelector('select[name="service_id"]').value;
        const tanggal = document.querySelector('input[name="tanggal"]').value;
        const waktu = document.querySelector('input[name="waktu"]').value;
        const serviceType = document.querySelector('input[name="service_type"]:checked');
        
        if (!nama || !email || !phone || !service || !tanggal || !waktu || !serviceType) {
            alert('Mohon lengkapi semua field yang diperlukan!');
            return false;
        }
        
        // Check address for home service
        if (serviceType && serviceType.value === 'home_service') {
            const address = document.querySelector('textarea[name="address"]').value;
            if (!address || address.trim() === '') {
                alert('Alamat wajib diisi untuk layanan home service!');
                return false;
            }
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Format email tidak valid!');
            return false;
        }
        
        // Phone validation
        const phoneRegex = /^[0-9\-\+\s\(\)]+$/;
        if (!phoneRegex.test(phone)) {
            alert('Format nomor telepon tidak valid!');
            return false;
        }
        
        return true;
    }

    // Set minimum date to today and handle initial service type state
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.querySelector('input[name="tanggal"]');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;
        }
        
        // Handle initial service type selection (on page load with POST data)
        toggleAddressField();
    });
</script>

<?php require_once 'includes/footer.php'; ?>
