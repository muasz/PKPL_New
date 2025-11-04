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
    $address = trim($_POST['address']);
    $user_id = $_SESSION['user_id'];
    
    // Validasi
    if (empty($service_id) || empty($date) || empty($time)) {
        $error = 'Semua field wajib diisi!';
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
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, service_id, date, time, address, status) VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("iisss", $user_id, $service_id, $date, $time, $address);
                
                if ($stmt->execute()) {
                    $success = 'Booking berhasil! Status: Menunggu konfirmasi.';
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
                <form method="POST" action="" id="bookingForm" style="display: grid; gap: 2rem;">
                    
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
                    
                    <!-- Address -->
                    <div>
                        <label style="display: block; margin-bottom: 0.8rem; font-weight: 700; color: #334155; font-size: 1.1rem;">
                            🏠 Alamat (Opsional)
                        </label>
                        <textarea name="address" rows="4"
                                  placeholder="Masukkan alamat jika diperlukan untuk konsultasi atau layanan home service..."
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
                    <a href="services.php" style="
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
                        🛠️ Lihat Layanan
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

<?php require_once 'includes/footer.php'; ?>
