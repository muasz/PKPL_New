<?php 
require_once 'includes/header.php';

// Redirect admin ke dashboard admin
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin') {
    header('Location: admin.php');
    exit;
}

// Ambil gambar dari catalog untuk slider
$slider_query = "SELECT image_url, title, category FROM catalog ORDER BY created_at DESC LIMIT 5";
$slider_result = $conn->query($slider_query);
$slider_images = [];
if ($slider_result && $slider_result->num_rows > 0) {
    while ($row = $slider_result->fetch_assoc()) {
        $slider_images[] = $row;
    }
}
?>

<!-- Hero Section with Image Slider --><div id="hero-slider" style="position: relative; height: 80vh; min-height: 600px; overflow: hidden; color: white; display: flex; align-items: center; margin: -3px 0 0 0; padding: 3px 0 0 0; border: 0; font-size: 16px; line-height: 1;">
        
        <!-- Slider Background Images -->
        <div class="slider-container" style="position: absolute; inset: 0; z-index: 1;">
            <?php if (!empty($slider_images)): ?>
                <?php foreach ($slider_images as $index => $image): ?>
                    <div class="slide <?= $index === 0 ? 'active' : '' ?>" 
                         style="position: absolute; inset: 0; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('<?= htmlspecialchars($image['image_url']) ?>'); 
                                background-size: cover; background-position: center; background-repeat: no-repeat;
                                opacity: <?= $index === 0 ? '1' : '0' ?>; transition: opacity 1.5s ease-in-out;">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback images if no catalog images -->
                <div class="slide active" style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(139, 92, 246, 0.8), rgba(124, 58, 237, 0.8)), url('https://images.unsplash.com/photo-1582473403894-666c8b10ce14?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; opacity: 1;"></div>
                <div class="slide" style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(139, 92, 246, 0.8), rgba(124, 58, 237, 0.8)), url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; opacity: 0;"></div>
                <div class="slide" style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(139, 92, 246, 0.8), rgba(124, 58, 237, 0.8)), url('https://images.unsplash.com/photo-1594736797933-d0b22321b654?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; opacity: 0;"></div>
            <?php endif; ?>
        </div>
        
        <!-- Overlay for better text readability -->
        <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); z-index: 2;"></div>
        
        <!-- Hero Content -->
        <div class="container" style="position: relative; z-index: 3; text-align: center; font-size: 16px; line-height: 1.6;">
            <div style="max-width: 800px; margin: 0 auto;">
                <h1 style="margin: 0 0 1.5rem 0; font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 800; line-height: 1.2; text-shadow: 2px 2px 4px rgba(0,0,0,0.7);">
                    Selamat Datang di <span style="color: #fbbf24;">PierceFlow</span>
                </h1>
                <p style="margin: 0 0 3rem 0; font-size: 1.3rem; line-height: 1.6; text-shadow: 1px 1px 2px rgba(0,0,0,0.7); max-width: 600px; margin-left: auto; margin-right: auto;">
                    Layanan Reservasi Piercing Profesional, Aman, dan Terpercaya
                </p>
                
                <!-- Modern CTA Buttons -->
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="catalog.php" style="
                        background: linear-gradient(135deg, #10b981, #059669);
                        color: white;
                        text-decoration: none;
                        padding: 1rem 2rem;
                        border-radius: 15px;
                        font-weight: 700;
                        font-size: 1.1rem;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 30px rgba(16, 185, 129, 0.4)'" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(16, 185, 129, 0.3)'">
                        � Lihat Katalog
                    </a>
                    <a href="<?= isset($_SESSION['user_id']) ? 'booking.php' : 'register.php' ?>" style="
                        background: rgba(255, 255, 255, 0.2);
                        color: white;
                        text-decoration: none;
                        padding: 1rem 2rem;
                        border-radius: 15px;
                        font-weight: 700;
                        font-size: 1.1rem;
                        transition: all 0.3s ease;
                        border: 2px solid rgba(255, 255, 255, 0.3);
                        backdrop-filter: blur(10px);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-3px)'" 
                       onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
                        <?= isset($_SESSION['user_id']) ? '📅 Booking Sekarang' : '🚀 Daftar Sekarang' ?>
                    </a>
                    
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="konsultasi.php" style="
                        background: rgba(16, 185, 129, 0.2);
                        color: white;
                        text-decoration: none;
                        padding: 1rem 2rem;
                        border-radius: 15px;
                        font-weight: 700;
                        font-size: 1.1rem;
                        transition: all 0.3s ease;
                        border: 2px solid rgba(16, 185, 129, 0.3);
                        backdrop-filter: blur(10px);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='rgba(16, 185, 129, 0.3)'; this.style.transform='translateY(-3px)'" 
                       onmouseout="this.style.background='rgba(16, 185, 129, 0.2)'; this.style.transform='translateY(0)'">
                        💬 Konsultasi Gratis
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Slider Navigation Dots -->
        <?php if (!empty($slider_images) && count($slider_images) > 1): ?>
        <div class="slider-dots" style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); z-index: 4; display: flex; gap: 0.5rem;">
            <?php foreach ($slider_images as $index => $image): ?>
                <button class="dot <?= $index === 0 ? 'active' : '' ?>" 
                        onclick="showSlide(<?= $index ?>)"
                        style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; background: <?= $index === 0 ? 'white' : 'transparent' ?>; cursor: pointer; transition: all 0.3s ease;">
                </button>
            <?php endforeach; ?>
        </div>
        <?php elseif (empty($slider_images)): ?>
        <!-- Fallback dots for demo slides -->
        <div class="slider-dots" style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); z-index: 4; display: flex; gap: 0.5rem;">
            <button class="dot active" onclick="showSlide(0)" style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; background: white; cursor: pointer; transition: all 0.3s ease;"></button>
            <button class="dot" onclick="showSlide(1)" style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; background: transparent; cursor: pointer; transition: all 0.3s ease;"></button>
            <button class="dot" onclick="showSlide(2)" style="width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; background: transparent; cursor: pointer; transition: all 0.3s ease;"></button>
        </div>
        <?php endif; ?>
    </div>

<!-- Content Section with Background -->
<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
    <!-- Modern Features Section -->
    <div class="container" style="padding: 4rem 0;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; font-weight: 800; color: #334155; margin: 0 0 1rem 0;">
                ⭐ Mengapa Memilih PierceFlow?
            </h2>
            <p style="font-size: 1.2rem; color: #64748b; margin: 0;">
                Pengalaman piercing terbaik dengan teknologi modern dan layanan profesional
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <!-- Feature Card 1 -->
            <div style="
                background: white; 
                border-radius: 20px; 
                padding: 2.5rem; 
                box-shadow: 0 8px 32px rgba(0,0,0,0.1); 
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            " onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.15)'" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(0,0,0,0.1)'">
                <!-- Gradient Background -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(135deg, #10b981, #059669);"></div>
                
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔔</div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin: 0 0 1rem 0;">
                    Notifikasi Real-time
                </h3>
                <p style="color: #64748b; line-height: 1.7; margin: 0; font-size: 1rem;">
                    Dapatkan notifikasi langsung untuk setiap status reservasi Anda. Pantau jadwal booking dengan mudah melalui sistem notifikasi canggih kami.
                </p>
            </div>
            
            <!-- Feature Card 2 -->
            <div style="
                background: white; 
                border-radius: 20px; 
                padding: 2.5rem; 
                box-shadow: 0 8px 32px rgba(0,0,0,0.1); 
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            " onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.15)'" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(0,0,0,0.1)'">
                <!-- Gradient Background -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(135deg, #f59e0b, #d97706);"></div>
                
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔒</div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin: 0 0 1rem 0;">
                    Keamanan Terjamin
                </h3>
                <p style="color: #64748b; line-height: 1.7; margin: 0; font-size: 1rem;">
                    Data pribadi Anda aman dengan enkripsi tingkat tinggi. Semua peralatan piercing steril dan profesional sesuai standar kesehatan internasional.
                </p>
            </div>
            
            <!-- Feature Card 3 -->
            <div style="
                background: white; 
                border-radius: 20px; 
                padding: 2.5rem; 
                box-shadow: 0 8px 32px rgba(0,0,0,0.1); 
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            " onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.15)'" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(0,0,0,0.1)'">
                <!-- Gradient Background -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(135deg, #8b5cf6, #7c3aed);"></div>
                
                <div style="font-size: 4rem; margin-bottom: 1rem;">💳</div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin: 0 0 1rem 0;">
                    Pembayaran Fleksibel
                </h3>
                <p style="color: #64748b; line-height: 1.7; margin: 0; font-size: 1rem;">
                    Berbagai metode pembayaran tersedia untuk kemudahan Anda. Proses transaksi cepat, mudah, dan aman dengan sistem pembayaran terpercaya.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Modern About Section -->
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 4rem 0; margin: 2rem 0;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 3rem; align-items: center;">
                <!-- Content -->
                <div>
                    <h2 style="font-size: 2.5rem; font-weight: 800; margin: 0 0 1.5rem 0; line-height: 1.2;">
                        🏆 Tentang PierceFlow
                    </h2>
                    <div style="font-size: 1.1rem; line-height: 1.8; opacity: 0.95;">
                        <p style="margin: 0 0 1.5rem 0;">
                            PierceFlow adalah platform reservasi piercing terpercaya yang menghubungkan Anda dengan layanan piercing profesional. 
                            Kami menyediakan berbagai jenis layanan piercing dengan peralatan steril dan teknisi berpengalaman.
                        </p>
                        <p style="margin: 0 0 1.5rem 0;">
                            Dengan sistem reservasi online yang mudah, Anda dapat memilih jadwal yang sesuai dan menghindari antrian panjang. 
                            Keamanan dan kepuasan pelanggan adalah prioritas utama kami.
                        </p>
                        <div style="background: rgba(255, 255, 255, 0.1); padding: 1.5rem; border-radius: 15px; border-left: 4px solid #fbbf24;">
                            <p style="margin: 0; font-weight: 600;">
                                ✨ Bergabunglah dengan <strong>ribuan pelanggan</strong> yang telah mempercayai PierceFlow untuk kebutuhan piercing mereka.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 20px; text-align: center; backdrop-filter: blur(10px);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">1000+</div>
                        <div style="opacity: 0.9; font-weight: 600;">Pelanggan Puas</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 20px; text-align: center; backdrop-filter: blur(10px);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">99%</div>
                        <div style="opacity: 0.9; font-weight: 600;">Tingkat Kepuasan</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 20px; text-align: center; backdrop-filter: blur(10px);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">24/7</div>
                        <div style="opacity: 0.9; font-weight: 600;">Customer Support</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.1); padding: 2rem; border-radius: 20px; text-align: center; backdrop-filter: blur(10px);">
                        <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem;">100%</div>
                        <div style="opacity: 0.9; font-weight: 600;">Steril & Aman</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modern CTA Section -->
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="container" style="padding: 4rem 0;">
        <div style="
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 25px;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
        ">
            <!-- Background Pattern -->
            <div style="position: absolute; inset: 0; opacity: 0.1; background-image: radial-gradient(circle at 30% 30%, white 2px, transparent 2px), radial-gradient(circle at 70% 70%, white 2px, transparent 2px); background-size: 30px 30px;"></div>
            
            <div style="position: relative; z-index: 2; max-width: 600px; margin: 0 auto;">
                <h2 style="font-size: 2.5rem; font-weight: 800; margin: 0 0 1rem 0; line-height: 1.2;">
                    🚀 Siap Memulai Perjalanan Piercing Anda?
                </h2>
                <p style="font-size: 1.2rem; margin: 0 0 2rem 0; opacity: 0.95; line-height: 1.6;">
                    Daftar sekarang dan dapatkan pengalaman booking yang mudah, aman, dan professional. Bergabunglah dengan komunitas PierceFlow hari ini!
                </p>
                
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="register.php" style="
                        background: rgba(255, 255, 255, 0.9);
                        color: #059669;
                        text-decoration: none;
                        padding: 1rem 2.5rem;
                        border-radius: 15px;
                        font-weight: 700;
                        font-size: 1.1rem;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0, 0, 0, 0.2)'; this.style.background='white'" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.1)'; this.style.background='rgba(255, 255, 255, 0.9)'">
                        🎯 Daftar Gratis Sekarang
                    </a>
                    
                    <a href="catalog.php" style="
                        background: transparent;
                        color: white;
                        text-decoration: none;
                        padding: 1rem 2rem;
                        border-radius: 15px;
                        font-weight: 700;
                        font-size: 1.1rem;
                        transition: all 0.3s ease;
                        border: 2px solid rgba(255, 255, 255, 0.5);
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    " onmouseover="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(-3px)'" 
                       onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                        � Lihat Portfolio
                    </a>
                </div>
                
                <!-- Trust Indicators -->
                <div style="margin-top: 2rem; display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap; opacity: 0.8; font-size: 0.9rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>✅</span> Gratis Selamanya
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>🔒</span> 100% Aman
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span>⚡</span> Daftar 30 Detik
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Modern User Dashboard Preview -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="container" style="padding: 4rem 0;">
        <div style="
            background: white;
            border-radius: 25px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        ">
            <h2 style="font-size: 2rem; font-weight: 700; color: #334155; margin: 0 0 1rem 0;">
                👋 Halo, <?= htmlspecialchars($_SESSION['name']) ?>!
            </h2>
            <p style="color: #64748b; margin: 0 0 2rem 0; font-size: 1.1rem;">
                Selamat datang kembali di PierceFlow. Apa yang ingin Anda lakukan hari ini?
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; max-width: 800px; margin: 0 auto;">
                <a href="booking.php" style="
                    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                    color: white;
                    text-decoration: none;
                    padding: 2rem;
                    border-radius: 20px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
                " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 30px rgba(139, 92, 246, 0.4)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(139, 92, 246, 0.3)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                    <div style="font-weight: 700; font-size: 1.2rem;">Buat Booking Baru</div>
                </a>
                
                <a href="dashboard.php" style="
                    background: linear-gradient(135deg, #10b981, #059669);
                    color: white;
                    text-decoration: none;
                    padding: 2rem;
                    border-radius: 20px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);
                " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 30px rgba(16, 185, 129, 0.4)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(16, 185, 129, 0.3)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                    <div style="font-weight: 700; font-size: 1.2rem;">Lihat Dashboard</div>
                </a>
                
                <a href="catalog.php" style="
                    background: linear-gradient(135deg, #f59e0b, #d97706);
                    color: white;
                    text-decoration: none;
                    padding: 2rem;
                    border-radius: 20px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
                " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 30px rgba(245, 158, 11, 0.4)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(245, 158, 11, 0.3)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">�</div>
                    <div style="font-weight: 700; font-size: 1.2rem;">Lihat Katalog</div>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<!-- End Content Section -->

<!-- CSS for Enhanced Slider -->
<style>
.slider-dots .dot:hover {
    transform: scale(1.2);
    border-width: 3px;
}

.slide {
    animation: slideIn 1.5s ease-in-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: scale(1.05); }
    to { opacity: 1; transform: scale(1); }
}

#hero-slider:hover .slider-dots {
    opacity: 1;
}

.slider-dots {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #hero-slider {
        height: 60vh;
        min-height: 500px;
    }
    
    .slider-dots {
        bottom: 1rem;
    }
    
    .slider-dots .dot {
        width: 10px;
        height: 10px;
    }
}

@media (max-width: 480px) {
    #hero-slider {
        height: 50vh;
        min-height: 400px;
    }
}

/* Loading animation for images */
.slide {
    background-color: #1e293b;
}

.slide::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top-color: rgba(255,255,255,0.8);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Logo responsive adjustments */
@media (max-width: 768px) {
    .nav-brand img {
        height: 35px !important;
    }
}

@media (max-width: 480px) {
    .nav-brand img {
        height: 30px !important;
    }
    
    .nav-brand span {
        font-size: 1.2rem !important;
    }
}
</style>

<!-- JavaScript for Hero Image Slider -->
<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
const totalSlides = slides.length;

function showSlide(index) {
    // Hide all slides
    slides.forEach(slide => {
        slide.style.opacity = '0';
    });
    
    // Deactivate all dots
    dots.forEach(dot => {
        dot.style.background = 'transparent';
        dot.classList.remove('active');
    });
    
    // Show current slide
    if (slides[index]) {
        slides[index].style.opacity = '1';
    }
    
    // Activate current dot
    if (dots[index]) {
        dots[index].style.background = 'white';
        dots[index].classList.add('active');
    }
    
    currentSlide = index;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    showSlide(currentSlide);
}

// Auto-slide every 5 seconds
if (totalSlides > 1) {
    setInterval(nextSlide, 5000);
}

// Preload images for better performance
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($slider_images)): ?>
    const slideImages = <?= json_encode($slider_images) ?>;
    slideImages.forEach(function(image) {
        const img = new Image();
        img.src = image.image_url;
    });
    <?php else: ?>
    // Preload fallback images
    const fallbackImages = [
        'https://images.unsplash.com/photo-1582473403894-666c8b10ce14?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1594736797933-d0b22321b654?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'
    ];
    fallbackImages.forEach(function(src) {
        const img = new Image();
        img.src = src;
    });
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
