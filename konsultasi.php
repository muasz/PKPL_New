<?php
require_once 'includes/header.php';

$success = '';
$error = '';

// Proses form konsultasi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $topik = $_POST['topik'];
    $pesan = trim($_POST['pesan']);
    
    // Validasi
    if (empty($nama) || empty($email) || empty($phone) || empty($topik) || empty($pesan)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Simpan ke database (buat tabel consultations jika belum ada)
        $create_table_query = "CREATE TABLE IF NOT EXISTS consultations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            topik VARCHAR(50) NOT NULL,
            pesan TEXT NOT NULL,
            status ENUM('pending', 'responded', 'closed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($create_table_query);
        
        // Insert consultation
        $stmt = $conn->prepare("INSERT INTO consultations (nama, email, phone, topik, pesan, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssss", $nama, $email, $phone, $topik, $pesan);
        
        if ($stmt->execute()) {
            $consultation_id = $conn->insert_id;
            $success = 'Terima kasih! Konsultasi Anda (#' . $consultation_id . ') telah diterima. Kami akan merespons dalam 1x24 jam.';
            
            // Reset form
            $_POST = array();
        } else {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
        $stmt->close();
    }
}
?>

<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: calc(100vh - 80px); padding: 2rem 0;">
    <div class="container">
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #334155; margin: 0 0 1rem 0;">
                💬 Konsultasi Gratis
            </h1>
            <p style="font-size: 1.2rem; color: #64748b; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Punya pertanyaan tentang piercing? Ingin tahu prosedur yang tepat? Konsultasikan dengan ahli kami secara gratis!
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 0 auto;">
            <!-- Consultation Form -->
            <div style="background: white; border-radius: 20px; padding: 2.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        💭
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #334155;">Form Konsultasi</h2>
                        <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Isi form di bawah untuk memulai konsultasi</p>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
                        <strong>✅ Berhasil!</strong><br>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
                        <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" style="display: grid; gap: 1.5rem;">
                    <!-- Nama -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            👤 Nama Lengkap *
                        </label>
                        <input type="text" name="nama" required
                               value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>"
                               placeholder="Masukkan nama lengkap Anda"
                               style="
                                   width: 100%;
                                   padding: 0.875rem;
                                   border: 2px solid #e2e8f0;
                                   border-radius: 10px;
                                   font-size: 1rem;
                                   transition: all 0.3s ease;
                               "
                               onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139,92,246,0.1)'"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                    </div>

                    <!-- Email & Phone -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                                ✉️ Email *
                            </label>
                            <input type="email" name="email" required
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                   placeholder="nama@email.com"
                                   style="
                                       width: 100%;
                                       padding: 0.875rem;
                                       border: 2px solid #e2e8f0;
                                       border-radius: 10px;
                                       font-size: 1rem;
                                       transition: all 0.3s ease;
                                   "
                                   onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139,92,246,0.1)'"
                                   onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                                📞 No. Telepon *
                            </label>
                            <input type="tel" name="phone" required
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"
                                   placeholder="08123456789"
                                   style="
                                       width: 100%;
                                       padding: 0.875rem;
                                       border: 2px solid #e2e8f0;
                                       border-radius: 10px;
                                       font-size: 1rem;
                                       transition: all 0.3s ease;
                                   "
                                   onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139,92,246,0.1)'"
                                   onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                        </div>
                    </div>

                    <!-- Topik Konsultasi -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            🎯 Topik Konsultasi *
                        </label>
                        <select name="topik" required
                                style="
                                    width: 100%;
                                    padding: 0.875rem;
                                    border: 2px solid #e2e8f0;
                                    border-radius: 10px;
                                    font-size: 1rem;
                                    transition: all 0.3s ease;
                                    background: white;
                                "
                                onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139,92,246,0.1)'"
                                onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
                            <option value="">-- Pilih Topik Konsultasi --</option>
                            <option value="jenis_piercing" <?= (isset($_POST['topik']) && $_POST['topik'] === 'jenis_piercing') ? 'selected' : '' ?>>Jenis-jenis Piercing</option>
                            <option value="prosedur_safety" <?= (isset($_POST['topik']) && $_POST['topik'] === 'prosedur_safety') ? 'selected' : '' ?>>Prosedur & Keamanan</option>
                            <option value="perawatan" <?= (isset($_POST['topik']) && $_POST['topik'] === 'perawatan') ? 'selected' : '' ?>>Perawatan After Care</option>
                            <option value="harga" <?= (isset($_POST['topik']) && $_POST['topik'] === 'harga') ? 'selected' : '' ?>>Konsultasi Harga</option>
                            <option value="lokasi_cocok" <?= (isset($_POST['topik']) && $_POST['topik'] === 'lokasi_cocok') ? 'selected' : '' ?>>Lokasi Piercing yang Cocok</option>
                            <option value="jewelry" <?= (isset($_POST['topik']) && $_POST['topik'] === 'jewelry') ? 'selected' : '' ?>>Jenis Jewelry & Material</option>
                            <option value="home_service" <?= (isset($_POST['topik']) && $_POST['topik'] === 'home_service') ? 'selected' : '' ?>>Layanan Home Service</option>
                            <option value="lainnya" <?= (isset($_POST['topik']) && $_POST['topik'] === 'lainnya') ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>

                    <!-- Pesan -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            💬 Pertanyaan/Pesan *
                        </label>
                        <textarea name="pesan" rows="5" required
                                  placeholder="Tuliskan pertanyaan atau hal yang ingin Anda konsultasikan..."
                                  style="
                                      width: 100%;
                                      padding: 0.875rem;
                                      border: 2px solid #e2e8f0;
                                      border-radius: 10px;
                                      font-size: 1rem;
                                      transition: all 0.3s ease;
                                      resize: vertical;
                                      font-family: inherit;
                                  "
                                  onfocus="this.style.borderColor='#8b5cf6'; this.style.boxShadow='0 0 0 3px rgba(139,92,246,0.1)'"
                                  onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"><?= isset($_POST['pesan']) ? htmlspecialchars($_POST['pesan']) : '' ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" style="
                        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                        color: white;
                        border: none;
                        padding: 1rem 2rem;
                        border-radius: 10px;
                        font-size: 1.1rem;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(139,92,246,0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        🚀 Kirim Konsultasi
                    </button>
                </form>
            </div>

            <!-- Information Panel -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- FAQ Quick -->
                <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 1.5rem 0; font-size: 1.3rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                        ❓ Frequently Asked Questions
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="padding: 1rem; background: #faf5ff; border-left: 4px solid #8b5cf6; border-radius: 8px;">
                            <div style="font-weight: 600; color: #7c3aed; margin-bottom: 0.3rem;">Apakah piercing sakit?</div>
                            <div style="font-size: 0.9rem; color: #64748b;">Tingkat nyeri berbeda-beda tergantung lokasi dan toleransi individu. Kami menggunakan teknik dan alat steril untuk meminimalkan rasa sakit.</div>
                        </div>
                        
                        <div style="padding: 1rem; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 8px;">
                            <div style="font-weight: 600; color: #059669; margin-bottom: 0.3rem;">Berapa lama proses penyembuhan?</div>
                            <div style="font-size: 0.9rem; color: #64748b;">Waktu penyembuhan berkisar 4-12 minggu tergantung jenis piercing dan perawatan yang tepat.</div>
                        </div>
                        
                        <div style="padding: 1rem; background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: 8px;">
                            <div style="font-weight: 600; color: #d97706; margin-bottom: 0.3rem;">Bagaimana cara merawat piercing baru?</div>
                            <div style="font-size: 0.9rem; color: #64748b;">Kami akan memberikan panduan lengkap perawatan dan produk aftercare yang direkomendasikan.</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 20px; padding: 2rem; color: white; box-shadow: 0 10px 30px rgba(139,92,246,0.3);">
                    <h3 style="margin: 0 0 1.5rem 0; font-size: 1.3rem; font-weight: 700;">
                        📞 Hubungi Langsung
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="https://wa.me/6281234567890?text=Halo%20PierceFlow,%20saya%20ingin%20konsultasi%20tentang%20piercing" 
                           target="_blank"
                           style="
                               display: flex;
                               align-items: center;
                               gap: 1rem;
                               padding: 1rem;
                               background: rgba(255,255,255,0.2);
                               border-radius: 12px;
                               color: white;
                               text-decoration: none;
                               transition: all 0.3s ease;
                           "
                           onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateX(5px)'"
                           onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateX(0)'">
                            <div style="width: 50px; height: 50px; background: #25d366; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">💬</div>
                            <div>
                                <div style="font-weight: 600; font-size: 1.1rem;">WhatsApp</div>
                                <div style="opacity: 0.9; font-size: 0.9rem;">Chat langsung dengan ahli kami</div>
                            </div>
                        </a>
                        
                        <a href="tel:+6281234567890"
                           style="
                               display: flex;
                               align-items: center;
                               gap: 1rem;
                               padding: 1rem;
                               background: rgba(255,255,255,0.2);
                               border-radius: 12px;
                               color: white;
                               text-decoration: none;
                               transition: all 0.3s ease;
                           "
                           onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateX(5px)'"
                           onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='translateX(0)'">
                            <div style="width: 50px; height: 50px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📞</div>
                            <div>
                                <div style="font-weight: 600; font-size: 1.1rem;">Telepon</div>
                                <div style="opacity: 0.9; font-size: 0.9rem;">+62 812-3456-7890</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Studio Hours -->
                <div style="background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <h3 style="margin: 0 0 1.5rem 0; font-size: 1.3rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                        🕒 Jam Operasional
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                            <span style="font-weight: 600; color: #334155;">Senin - Jumat</span>
                            <span style="color: #10b981;">09:00 - 21:00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                            <span style="font-weight: 600; color: #334155;">Sabtu - Minggu</span>
                            <span style="color: #10b981;">10:00 - 22:00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                            <span style="font-weight: 600; color: #334155;">Response Time</span>
                            <span style="color: #f59e0b;">< 24 Jam</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div style="text-align: center; margin-top: 3rem;">
            <div style="background: white; border-radius: 20px; padding: 2rem; max-width: 600px; margin: 0 auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem; font-weight: 700; color: #334155;">
                    Siap untuk Booking?
                </h3>
                <p style="margin: 0 0 1.5rem 0; color: #64748b;">
                    Sudah puas dengan konsultasi? Yuk langsung booking layanan profesional kami!
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="register.php" style="
                        background: linear-gradient(135deg, #10b981, #059669);
                        color: white;
                        text-decoration: none;
                        padding: 0.875rem 1.5rem;
                        border-radius: 10px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(16,185,129,0.3)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        🎯 Daftar & Booking
                    </a>
                    <a href="catalog.php" style="
                        background: white;
                        color: #8b5cf6;
                        text-decoration: none;
                        padding: 0.875rem 1.5rem;
                        border-radius: 10px;
                        font-weight: 600;
                        border: 2px solid #8b5cf6;
                        transition: all 0.3s ease;
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='#8b5cf6'; this.style.color='white'"
                       onmouseout="this.style.background='white'; this.style.color='#8b5cf6'">
                        📸 Lihat Portfolio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .container > div:first-child {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
    
    .container > div:first-child > div:nth-child(1) > form > div:nth-child(4) {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>