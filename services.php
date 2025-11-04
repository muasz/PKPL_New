<?php
require_once 'includes/header.php';

// Ambil data layanan dari database
$query = "SELECT * FROM services ORDER BY price ASC";
$result = $conn->query($query);
?>

<!-- Modern Services Page -->
<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh;">
    
    <!-- Modern Page Header -->
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; padding: 4rem 0; text-align: center; position: relative; overflow: hidden;">
        <!-- Background Pattern -->
        <div style="position: absolute; inset: 0; opacity: 0.1; background-image: radial-gradient(circle at 25% 25%, white 2px, transparent 2px), radial-gradient(circle at 75% 75%, white 2px, transparent 2px); background-size: 40px 40px;"></div>
        
        <div class="container" style="position: relative; z-index: 2;">
            <div style="max-width: 700px; margin: 0 auto;">
                <h1 style="margin: 0 0 1rem 0; font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; line-height: 1.2;">
                    🛠️ Layanan Kami
                </h1>
                <p style="margin: 0; font-size: 1.2rem; opacity: 0.95; line-height: 1.6;">
                    Pilih layanan piercing profesional dengan peralatan steril dan teknisi berpengalaman. Semua layanan menggunakan standar kesehatan internasional.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Modern Services Grid -->
    <div class="container" style="padding: 4rem 0;">
        <?php if ($result && $result->num_rows > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem;">
                <?php while ($service = $result->fetch_assoc()): ?>
                    <div style="
                        background: white;
                        border-radius: 25px;
                        padding: 0;
                        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                        overflow: hidden;
                        position: relative;
                        border: 1px solid rgba(139, 92, 246, 0.1);
                        min-height: 520px;
                        display: flex;
                        flex-direction: column;
                    " onmouseover="this.style.transform='translateY(-8px) scale(1.02)'; this.style.boxShadow='0 20px 50px rgba(139, 92, 246, 0.2)'" 
                       onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 8px 32px rgba(0,0,0,0.1)'">
                        
                        <!-- Card Header -->
                        <div style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 2rem; position: relative;">
                            <!-- Smart Service Icon based on name -->
                            <?php
                            $serviceName = strtolower($service['name']);
                            $icon = '💎'; // default
                            
                            // Smart icon mapping untuk berbagai jenis tindik
                            if (strpos($serviceName, 'hidung') !== false || strpos($serviceName, 'nose') !== false) {
                                $icon = '👃';
                            } elseif (strpos($serviceName, 'telinga') !== false || strpos($serviceName, 'ear') !== false || 
                                     strpos($serviceName, 'helix') !== false || strpos($serviceName, 'tragus') !== false || 
                                     strpos($serviceName, 'industrial') !== false || strpos($serviceName, 'lobe') !== false) {
                                $icon = '👂';
                            } elseif (strpos($serviceName, 'bibir') !== false || strpos($serviceName, 'lip') !== false) {
                                $icon = '👄';
                            } elseif (strpos($serviceName, 'lidah') !== false || strpos($serviceName, 'tongue') !== false) {
                                $icon = '👅';
                            } elseif (strpos($serviceName, 'septum') !== false) {
                                $icon = '🐂';
                            } elseif (strpos($serviceName, 'kaki') !== false || strpos($serviceName, 'foot') !== false || 
                                     strpos($serviceName, 'toe') !== false || strpos($serviceName, 'ankle') !== false) {
                                $icon = '🦶';
                            } elseif (strpos($serviceName, 'tangan') !== false || strpos($serviceName, 'hand') !== false || 
                                     strpos($serviceName, 'finger') !== false) {
                                $icon = '✋';
                            } elseif (strpos($serviceName, 'alis') !== false || strpos($serviceName, 'eyebrow') !== false) {
                                $icon = '👁️';
                            } elseif (strpos($serviceName, 'navel') !== false || strpos($serviceName, 'belly') !== false || 
                                     strpos($serviceName, 'pusar') !== false) {
                                $icon = '⭕';
                            }
                            ?>
                            <div style="font-size: 3rem; margin-bottom: 0.5rem; text-align: center;"><?= $icon ?></div>
                            <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700; text-align: center; line-height: 1.3;">
                                <?= htmlspecialchars($service['name']) ?>
                            </h3>
                        </div>
                        
                        <!-- Card Content -->
                        <div style="padding: 2rem; flex: 1; display: flex; flex-direction: column;">
                            <!-- Price Display -->
                            <div style="text-align: center; margin-bottom: 1.5rem;">
                                <div style="
                                    background: linear-gradient(135deg, #10b981, #059669);
                                    color: white;
                                    display: inline-block;
                                    padding: 0.8rem 2rem;
                                    border-radius: 20px;
                                    font-size: 1.4rem;
                                    font-weight: 800;
                                    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
                                ">
                                    Rp <?= number_format($service['price'], 0, ',', '.') ?>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div style="margin-bottom: 1.5rem; flex-grow: 1; display: flex; align-items: center;">
                                <p style="color: #64748b; line-height: 1.7; margin: 0; text-align: center; font-size: 1rem;">
                                    <?php 
                                    $description = $service['description'];
                                    if (empty($description) || strlen(trim($description)) < 10) {
                                        // Default description based on service name
                                        $defaultDesc = "Layanan " . strtolower($service['name']) . " profesional dengan peralatan steril dan teknisi berpengalaman. Menggunakan perhiasan medical grade untuk keamanan maksimal.";
                                        echo htmlspecialchars($defaultDesc);
                                    } else {
                                        echo htmlspecialchars($description);
                                    }
                                    ?>
                                </p>
                            </div>
                            
                            <!-- Features List -->
                            <div style="background: #f8fafc; border-radius: 15px; padding: 1rem; margin-bottom: auto;">
                                <div style="font-size: 0.85rem; color: #475569; display: grid; grid-template-columns: 1fr 1fr; gap: 0.3rem;">
                                    <div style="display: flex; align-items: center;">
                                        <span style="color: #10b981; margin-right: 0.3rem; font-size: 0.8rem;">✅</span>
                                        Steril & Aman
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span style="color: #10b981; margin-right: 0.3rem; font-size: 0.8rem;">✅</span>
                                        Medical Grade
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span style="color: #10b981; margin-right: 0.3rem; font-size: 0.8rem;">✅</span>
                                        Konsultasi Gratis
                                    </div>
                                    <div style="display: flex; align-items: center;">
                                        <span style="color: #10b981; margin-right: 0.3rem; font-size: 0.8rem;">✅</span>
                                        Aftercare Guide
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Button -->
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="booking.php?service_id=<?= $service['id'] ?>" style="
                                        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                                        color: white;
                                        text-decoration: none;
                                        display: inline-flex;
                                        align-items: center;
                                        gap: 0.5rem;
                                        padding: 1rem 2rem;
                                        border-radius: 15px;
                                        font-weight: 700;
                                        font-size: 1rem;
                                        transition: all 0.3s ease;
                                        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
                                        width: 100%;
                                        justify-content: center;
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(139, 92, 246, 0.4)'" 
                                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(139, 92, 246, 0.3)'">
                                        📅 Booking Sekarang
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" style="
                                        background: linear-gradient(135deg, #f59e0b, #d97706);
                                        color: white;
                                        text-decoration: none;
                                        display: inline-flex;
                                        align-items: center;
                                        gap: 0.5rem;
                                        padding: 1rem 2rem;
                                        border-radius: 15px;
                                        font-weight: 700;
                                        font-size: 1rem;
                                        transition: all 0.3s ease;
                                        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
                                        width: 100%;
                                        justify-content: center;
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(245, 158, 11, 0.4)'" 
                                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(245, 158, 11, 0.3)'">
                                        🔐 Login untuk Booking
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                <div style="font-size: 4rem; margin-bottom: 2rem;">🛠️</div>
                <h3 style="color: #475569; margin-bottom: 1rem; font-size: 2rem;">Belum Ada Layanan</h3>
                <p style="font-size: 1.1rem; line-height: 1.6;">
                    Layanan piercing sedang dalam persiapan. Silakan cek kembali nanti atau hubungi admin untuk informasi lebih lanjut.
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modern Why Choose Us Section -->
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 4rem 0; margin-top: 2rem;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="font-size: 2.5rem; font-weight: 800; margin: 0 0 1rem 0;">
                    🏆 Mengapa Memilih PierceFlow?
                </h2>
                <p style="font-size: 1.2rem; margin: 0; opacity: 0.9;">
                    Standar tertinggi dalam industri piercing dengan teknologi dan layanan terdepan
                </p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                <!-- Keunggulan Cards -->
                <div style="
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 2rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-5px)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">👨‍⚕️</div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 1rem 0; text-align: center;">
                        Teknisi Bersertifikat
                    </h3>
                    <div style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.8rem;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Pengalaman lebih dari 5 tahun</span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Sertifikasi internasional piercing</span>
                        </div>
                    </div>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 2rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-5px)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">🏥</div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 1rem 0; text-align: center;">
                        Standar Medis
                    </h3>
                    <div style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.8rem;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Peralatan steril medis internasional</span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Ruang operasi ber-AC dan private</span>
                        </div>
                    </div>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 2rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-5px)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">💎</div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 1rem 0; text-align: center;">
                        Perhiasan Premium
                    </h3>
                    <div style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.8rem;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Titanium & stainless steel medical grade</span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Hypoallergenic & anti-infeksi</span>
                        </div>
                    </div>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 2rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-5px)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">💬</div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 1rem 0; text-align: center;">
                        Konsultasi Gratis
                    </h3>
                    <div style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.8rem;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Konsultasi pre & post piercing</span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Aftercare guide lengkap</span>
                        </div>
                    </div>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 2rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-5px)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">🛡️</div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 1rem 0; text-align: center;">
                        Garansi & Support
                    </h3>
                    <div style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.8rem;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Garansi healing process</span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Customer support 24/7</span>
                        </div>
                    </div>
                </div>
                
                <div style="
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 20px;
                    padding: 2rem;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.2);
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='translateY(-5px)'" 
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'; this.style.transform='translateY(0)'">
                    <div style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">📱</div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; margin: 0 0 1rem 0; text-align: center;">
                        Teknologi Modern
                    </h3>
                    <div style="font-size: 1rem; opacity: 0.9; line-height: 1.6;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.8rem;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Sistem booking online 24/7</span>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <span style="color: #fbbf24; margin-right: 0.8rem; font-size: 1.2rem;">✅</span>
                            <span>Real-time status tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modern Bottom CTA -->
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="container" style="padding: 4rem 0;">
        <div style="
            background: white;
            border-radius: 25px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.15);
            border: 2px solid rgba(139, 92, 246, 0.1);
        ">
            <h3 style="font-size: 2rem; font-weight: 700; color: #334155; margin: 0 0 1rem 0;">
                ✨ Tertarik dengan Layanan Kami?
            </h3>
            <p style="color: #64748b; margin: 0 0 2rem 0; font-size: 1.1rem; line-height: 1.6;">
                Daftar sekarang dan rasakan pengalaman piercing profesional yang aman dan nyaman
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="register.php" style="
                    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                    color: white;
                    text-decoration: none;
                    padding: 1rem 2rem;
                    border-radius: 15px;
                    font-weight: 700;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(139, 92, 246, 0.3)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    🚀 Daftar Gratis
                </a>
                <a href="login.php" style="
                    background: transparent;
                    color: #8b5cf6;
                    text-decoration: none;
                    padding: 1rem 2rem;
                    border-radius: 15px;
                    font-weight: 700;
                    border: 2px solid #8b5cf6;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                " onmouseover="this.style.background='#8b5cf6'; this.style.color='white'; this.style.transform='translateY(-3px)'" 
                   onmouseout="this.style.background='transparent'; this.style.color='#8b5cf6'; this.style.transform='translateY(0)'">
                    🔐 Login
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
