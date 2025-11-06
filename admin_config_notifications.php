<?php
require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

require_once 'includes/production_whatsapp.php';
$whatsappService = new ProductionWhatsAppService();
$status = $whatsappService->getStatus();

// Handle configuration update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_config'])) {
        $whatsapp_token = trim($_POST['whatsapp_token']);
        $admin_phone = trim($_POST['admin_phone']);
        $production_mode = isset($_POST['production_mode']) ? true : false;
        
        try {
            // Update API token
            if (!empty($whatsapp_token)) {
                $whatsappService->setApiToken($whatsapp_token);
            }
            
            // Enable/disable production mode
            $whatsappService->enableProductionMode($production_mode);
            
            $success = 'Konfigurasi berhasil disimpan! ';
            $success .= 'Mode: ' . ($production_mode ? 'PRODUCTION (Live)' : 'DEVELOPMENT (Simulasi)');
            if (!empty($whatsapp_token)) {
                $success .= '<br>Token: ' . substr($whatsapp_token, 0, 10) . '...';
            }
            
            // Refresh status
            $status = $whatsappService->getStatus();
            
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['test_whatsapp'])) {
        $test_phone = trim($_POST['test_phone']);
        if (!empty($test_phone)) {
            try {
                $result = $whatsappService->testConnection($test_phone);
                if ($result['success']) {
                    $success = '✅ Test WhatsApp berhasil dikirim ke ' . $test_phone . '! Cek HP Anda.';
                    if (isset($result['simulated']) && $result['simulated']) {
                        $success .= ' (Mode simulasi - cek log file)';
                    }
                } else {
                    $error = '❌ Test WhatsApp gagal: ' . ($result['error'] ?? 'Unknown error');
                }
            } catch (Exception $e) {
                $error = 'Error testing WhatsApp: ' . $e->getMessage();
            }
        } else {
            $error = 'Masukkan nomor WhatsApp untuk test!';
        }
    }
}

// Check current status
$current_token_status = 'Belum dikonfigurasi';
$whatsapp_ready = false;
?>

<div class="container">
    <h1 class="page-title">⚙️ Konfigurasi Notifikasi</h1>
    <p class="page-subtitle">Setup Email dan WhatsApp untuk notifikasi otomatis</p>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Current Status -->
        <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 1rem 0; color: #8b5cf6; display: flex; align-items: center; gap: 0.5rem;">
                📊 Status Saat Ini
            </h3>
            
            <div style="space-y: 1rem;">
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; margin-bottom: 0.3rem;">📧 Email Notifications:</div>
                    <div style="background: #fef3c7; color: #92400e; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                        ⏳ Development Mode (SMTP belum dikonfigurasi)
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; margin-bottom: 0.3rem;">💬 WhatsApp Notifications:</div>
                    <?php if ($status['production_mode'] && $status['has_token']): ?>
                        <div style="background: #dcfce7; color: #166534; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                            🚀 PRODUCTION MODE - Live WhatsApp API
                        </div>
                    <?php elseif ($status['has_token']): ?>
                        <div style="background: #dbeafe; color: #1e40af; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                            🧪 Ready - Token tersimpan (Mode Dev)
                        </div>
                    <?php else: ?>
                        <div style="background: #fef3c7; color: #92400e; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                            ⏳ Token belum dikonfigurasi
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <div style="font-weight: 600; margin-bottom: 0.3rem;">🔄 Auto Notifications:</div>
                    <div style="background: #dcfce7; color: #166534; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem;">
                        ✅ Aktif - Provider: <?= ucfirst($status['active_provider']) ?>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <div style="font-weight: 600; margin-bottom: 0.3rem;">📱 Admin Phone:</div>
                    <div style="background: #f3f4f6; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.9rem; color: #374151;">
                        <?= $status['admin_phone'] ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Setup Guide -->
        <div style="background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%); border-radius: 15px; padding: 2rem;">
            <h3 style="margin: 0 0 1rem 0; color: #0277bd;">🚀 Quick Setup</h3>
            
            <div style="color: #01579b; line-height: 1.6;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 1rem;">WhatsApp API Setup:</h4>
                <ol style="margin: 0 0 1rem 0; padding-left: 1.2rem; font-size: 0.9rem;">
                    <li>Daftar di <a href="https://fonnte.com" target="_blank" style="color: #0277bd; font-weight: 600;">Fonnte.com</a></li>
                    <li>Scan QR Code dengan WhatsApp</li>
                    <li>Copy API Token</li>
                    <li>Paste ke form di samping</li>
                    <li>Test notification</li>
                </ol>
                
                <div style="background: rgba(255,255,255,0.7); padding: 1rem; border-radius: 8px; font-size: 0.85rem;">
                    💡 <strong>Tips:</strong> Fonnte memberikan 100 pesan gratis untuk testing!
                </div>
            </div>
        </div>
    </div>
    
    <!-- Configuration Form -->
    <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 1.5rem 0; color: #8b5cf6;">⚙️ Konfigurasi WhatsApp API</h3>
        
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        🔑 Fonnte API Token:
                    </label>
                    <input type="text" name="whatsapp_token" 
                           placeholder="Masukkan API Token dari Fonnte"
                           style="
                               width: 100%;
                               padding: 0.8rem;
                               border: 2px solid #e5e7eb;
                               border-radius: 8px;
                               font-size: 0.9rem;
                               transition: border-color 0.3s;
                           "
                           onfocus="this.style.borderColor='#8b5cf6'"
                           onblur="this.style.borderColor='#e5e7eb'">
                    <small style="color: #6b7280; font-size: 0.8rem;">
                        💡 Dapatkan token gratis di <a href="https://fonnte.com" target="_blank">fonnte.com</a>
                    </small>
                </div>
                
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        📱 Admin WhatsApp Number:
                    </label>
                    <input type="text" name="admin_phone" 
                           placeholder="08123456789"
                           style="
                               width: 100%;
                               padding: 0.8rem;
                               border: 2px solid #e5e7eb;
                               border-radius: 8px;
                               font-size: 0.9rem;
                               transition: border-color 0.3s;
                           "
                           onfocus="this.style.borderColor='#8b5cf6'"
                           onblur="this.style.borderColor='#e5e7eb'">
                    <small style="color: #6b7280; font-size: 0.8rem;">
                        📞 Nomor admin untuk menerima alert booking baru
                    </small>
                </div>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                    <h4 style="margin: 0 0 0.5rem 0; color: #92400e; display: flex; align-items: center; gap: 0.5rem;">
                        ⚡ Mode Deployment
                    </h4>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="production_mode" <?= $status['production_mode'] ? 'checked' : '' ?> style="width: 1.2rem; height: 1.2rem;">
                        <span style="font-weight: 600; color: #92400e;">
                            🚀 PRODUCTION MODE - Kirim WhatsApp Asli
                        </span>
                    </label>
                    <small style="color: #92400e; font-size: 0.8rem; margin-top: 0.3rem; display: block;">
                        ⚠️ Centang ini untuk mengirim WhatsApp sungguhan (pastikan token sudah benar)
                    </small>
                </div>
            </div>
            
            <button type="submit" name="update_config" style="
                background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                color: white;
                border: none;
                padding: 1rem 2rem;
                border-radius: 10px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.3s;
                box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
            " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                💾 Simpan Konfigurasi
            </button>
        </form>
    </div>
    
    <!-- Test Section -->
    <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 15px; padding: 2rem; margin-top: 2rem;">
        <h3 style="margin: 0 0 1rem 0; color: #166534;">🧪 Test Notifications</h3>
        
        <form method="POST" style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 1rem;">
            <h4 style="margin: 0 0 1rem 0; color: #15803d;">💬 Test WhatsApp</h4>
            <div style="display: flex; gap: 1rem; align-items: end;">
                <div style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        📱 Nomor WhatsApp untuk Test:
                    </label>
                    <input type="text" name="test_phone" 
                           placeholder="08123456789" 
                           value="<?= $status['admin_phone'] ?>"
                           style="
                               width: 100%;
                               padding: 0.8rem;
                               border: 2px solid #e5e7eb;
                               border-radius: 8px;
                               font-size: 0.9rem;
                           ">
                </div>
                <button type="submit" name="test_whatsapp" style="
                    background: #25d366;
                    color: white;
                    border: none;
                    padding: 0.8rem 1.5rem;
                    border-radius: 8px;
                    font-size: 0.9rem;
                    font-weight: 600;
                    cursor: pointer;
                    white-space: nowrap;
                ">
                    🚀 Kirim Test
                </button>
            </div>
            <small style="color: #166534; font-size: 0.8rem; display: block; margin-top: 0.5rem;">
                💡 Test ini akan mengirim pesan WhatsApp <?= $status['production_mode'] ? 'sungguhan' : 'simulasi' ?> ke nomor yang Anda masukkan
            </small>
        </form>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            <div style="background: white; padding: 1rem; border-radius: 8px;">
                <h4 style="margin: 0 0 0.5rem 0; color: #15803d;">📊 Status Log:</h4>
                <p style="margin: 0; color: #166534; font-size: 0.9rem;">
                    Log file: <code><?= basename($status['log_file']) ?></code><br>
                    Mode: <strong><?= $status['production_mode'] ? 'Production' : 'Development' ?></strong><br>
                    Provider: <strong><?= ucfirst($status['active_provider']) ?></strong>
                </p>
            </div>
            
            <div style="background: white; padding: 1rem; border-radius: 8px;">
                <h4 style="margin: 0 0 0.5rem 0; color: #15803d;">📧 Email Status:</h4>
                <p style="margin: 0; color: #166534; font-size: 0.9rem;">
                    Email notifications dalam mode development.<br>
                    Semua email di-log untuk tracking.<br>
                    SMTP configuration coming soon.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>