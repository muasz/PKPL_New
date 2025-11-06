<?php
require_once 'includes/header.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

// Proses pembatalan booking
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);
    $user_id = $_SESSION['user_id'];
    
    // Update status menjadi cancelled
    $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $success = 'Booking berhasil dibatalkan!';
    } else {
        $error = 'Gagal membatalkan booking.';
    }
    $stmt->close();
}

// Ambil semua booking user
$user_id = $_SESSION['user_id'];
$query = "SELECT b.*, s.name as service_name, s.price 
          FROM bookings b 
          JOIN services s ON b.service_id = s.id 
          WHERE b.user_id = ? 
          ORDER BY b.date DESC, b.time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Modern Dashboard Header -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #8b5cf6 100%); padding: 4rem 0; margin-bottom: 3rem; position: relative; overflow: hidden;">
    <!-- Background Pattern -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><circle cx=\"50\" cy=\"50\" r=\"2\" fill=\"white\"/></svg>'); background-size: 50px 50px;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div style="text-align: center; color: white;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👋</div>
            <h1 style="margin: 0 0 0.5rem 0; font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                Selamat Datang, <?= htmlspecialchars($_SESSION['name']) ?>!
            </h1>
            <p style="margin: 0 0 2rem 0; font-size: 1.2rem; opacity: 0.9;">
                Kelola semua reservasi piercing Anda dengan mudah
            </p>
            
            <!-- Quick Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                <?php
                // Get quick stats
                $stats_query = "SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count
                    FROM bookings WHERE user_id = ?";
                $stats_stmt = $conn->prepare($stats_query);
                $stats_stmt->bind_param("i", $user_id);
                $stats_stmt->execute();
                $stats = $stats_stmt->get_result()->fetch_assoc();
                $stats_stmt->close();
                ?>
                
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2);">
                    <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;"><?= $stats['total_bookings'] ?></div>
                    <div style="opacity: 0.9; font-size: 0.95rem;">📊 Total Booking</div>
                </div>
                
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2);">
                    <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;"><?= $stats['pending_count'] ?></div>
                    <div style="opacity: 0.9; font-size: 0.95rem;">⏳ Menunggu</div>
                </div>
                
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 1.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2);">
                    <div style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;"><?= $stats['confirmed_count'] ?></div>
                    <div style="opacity: 0.9; font-size: 0.95rem;">✅ Dikonfirmasi</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Modern Alert Messages -->
    <?php if ($success): ?>
        <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1rem 1.5rem; border-radius: 15px; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.8rem; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);">
            <div style="font-size: 1.5rem;">✅</div>
            <div style="font-weight: 600;"><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1rem 1.5rem; border-radius: 15px; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.8rem; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);">
            <div style="font-size: 1.5rem;">❌</div>
            <div style="font-weight: 600;"><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>
    
    <!-- Quick Actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <a href="booking.php" style="
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            text-decoration: none;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.3);
        " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px rgba(139, 92, 246, 0.4)'" 
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(139, 92, 246, 0.3)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
            <div style="font-weight: 700; font-size: 1.2rem; margin-bottom: 0.5rem;">Buat Booking Baru</div>
            <div style="opacity: 0.9; font-size: 0.95rem;">Reservasi layanan piercing sekarang</div>
        </a>
        
        <a href="catalog.php" style="
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            text-decoration: none;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.3);
        " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px rgba(16, 185, 129, 0.4)'" 
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(16, 185, 129, 0.3)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;">�</div>
            <div style="font-weight: 700; font-size: 1.2rem; margin-bottom: 0.5rem;">Lihat Katalog</div>
            <div style="opacity: 0.9; font-size: 0.95rem;">Portfolio hasil piercing kami</div>
        </a>
        
        <a href="#account-info" style="
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            text-decoration: none;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
        " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 15px 40px rgba(245, 158, 11, 0.4)'" 
           onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(245, 158, 11, 0.3)'">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
            <div style="font-weight: 700; font-size: 1.2rem; margin-bottom: 0.5rem;">Info Akun</div>
            <div style="opacity: 0.9; font-size: 0.95rem;">Kelola informasi profil Anda</div>
        </a>
    </div>
    
    <!-- Modern Booking History -->
    <div style="background: white; border-radius: 25px; padding: 0; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem;">
        <!-- Section Header -->
        <div style="background: linear-gradient(135deg, #1f2937, #374151); color: white; padding: 2rem;">
            <h2 style="margin: 0; font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; gap: 0.8rem;">
                📋 Riwayat Booking
            </h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.8; font-size: 1rem;">
                Lihat dan kelola semua reservasi Anda
            </p>
        </div>
        
        <div style="padding: 2rem;">
            <?php if ($result && $result->num_rows > 0): ?>
                <!-- Modern Cards Layout for Mobile/Tablet -->
                <div style="display: none;" class="booking-cards">
                    <?php 
                    // Reset result pointer for cards
                    $result->data_seek(0);
                    while ($booking = $result->fetch_assoc()): 
                    ?>
                        <div style="background: #f8fafc; border-radius: 15px; padding: 1.5rem; margin-bottom: 1rem; border-left: 4px solid 
                            <?php 
                            $status_colors = [
                                'pending' => '#f59e0b',
                                'confirmed' => '#10b981',
                                'cancelled' => '#ef4444',
                                'rejected' => '#ef4444'
                            ];
                            echo $status_colors[$booking['status']] ?? '#6b7280';
                            ?>;">
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    <div style="font-weight: 700; font-size: 1.1rem; color: #1f2937; margin-bottom: 0.3rem;">
                                        <?= htmlspecialchars($booking['service_name']) ?>
                                    </div>
                                    <div style="color: #6b7280; font-size: 0.9rem;">#<?= $booking['id'] ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: 700; color: #059669; font-size: 1.1rem;">
                                        Rp <?= number_format($booking['price'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <div style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.2rem;">📅 Tanggal</div>
                                    <div style="font-weight: 600; color: #374151;"><?= date('d/m/Y', strtotime($booking['date'])) ?></div>
                                </div>
                                <div>
                                    <div style="font-size: 0.8rem; color: #6b7280; margin-bottom: 0.2rem;">⏰ Waktu</div>
                                    <div style="font-weight: 600; color: #374151;"><?= date('H:i', strtotime($booking['time'])) ?></div>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="
                                    background: <?php 
                                    echo $status_colors[$booking['status']] ?? '#6b7280';
                                    ?>;
                                    color: white;
                                    padding: 0.4rem 1rem;
                                    border-radius: 20px;
                                    font-size: 0.85rem;
                                    font-weight: 600;
                                ">
                                    <?php
                                    $status_text = [
                                        'pending' => '⏳ Menunggu',
                                        'confirmed' => '✅ Dikonfirmasi',
                                        'cancelled' => '❌ Dibatalkan',
                                        'rejected' => '🚫 Ditolak'
                                    ];
                                    echo $status_text[$booking['status']] ?? $booking['status'];
                                    ?>
                                </span>
                                
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <button onclick="if(confirm('Yakin ingin membatalkan booking ini?')) window.location='?cancel=<?= $booking['id'] ?>'" 
                                            style="
                                                background: #ef4444;
                                                color: white;
                                                border: none;
                                                padding: 0.5rem 1rem;
                                                border-radius: 10px;
                                                font-size: 0.85rem;
                                                font-weight: 600;
                                                cursor: pointer;
                                                transition: all 0.3s ease;
                                            "
                                            onmouseover="this.style.background='#dc2626'"
                                            onmouseout="this.style.background='#ef4444'">
                                        🗑️ Batalkan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Modern Table for Desktop -->
                <div style="overflow-x: auto;" class="booking-table">
                    <table style="width: 100%; border-collapse: collapse; background: white;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0);">
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">Layanan</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">Tanggal</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">Waktu</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">Harga</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">Status</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #334155; border-bottom: 2px solid #e2e8f0;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset result pointer for table
                            $result->data_seek(0);
                            while ($booking = $result->fetch_assoc()): 
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease;" 
                                    onmouseover="this.style.backgroundColor='#f8fafc'" 
                                    onmouseout="this.style.backgroundColor='white'">
                                    <td style="padding: 1rem; font-weight: 600; color: #475569;">#<?= $booking['id'] ?></td>
                                    <td style="padding: 1rem; color: #334155; font-weight: 500;"><?= htmlspecialchars($booking['service_name']) ?></td>
                                    <td style="padding: 1rem; color: #475569;"><?= date('d/m/Y', strtotime($booking['date'])) ?></td>
                                    <td style="padding: 1rem; color: #475569;"><?= date('H:i', strtotime($booking['time'])) ?></td>
                                    <td style="padding: 1rem; color: #059669; font-weight: 600;">Rp <?= number_format($booking['price'], 0, ',', '.') ?></td>
                                    <td style="padding: 1rem;">
                                        <span style="
                                            background: <?php 
                                            $status_colors = [
                                                'pending' => '#f59e0b',
                                                'confirmed' => '#10b981',
                                                'cancelled' => '#ef4444',
                                                'rejected' => '#ef4444'
                                            ];
                                            echo $status_colors[$booking['status']] ?? '#6b7280';
                                            ?>;
                                            color: white;
                                            padding: 0.4rem 0.8rem;
                                            border-radius: 15px;
                                            font-size: 0.85rem;
                                            font-weight: 600;
                                            display: inline-flex;
                                            align-items: center;
                                            gap: 0.3rem;
                                        ">
                                            <?php
                                            $status_text = [
                                                'pending' => '⏳ Menunggu',
                                                'confirmed' => '✅ Dikonfirmasi',
                                                'cancelled' => '❌ Dibatalkan',
                                                'rejected' => '🚫 Ditolak'
                                            ];
                                            echo $status_text[$booking['status']] ?? $booking['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <?php if ($booking['status'] == 'pending'): ?>
                                            <button onclick="if(confirm('Yakin ingin membatalkan booking ini?')) window.location='?cancel=<?= $booking['id'] ?>'" 
                                                    style="
                                                        background: linear-gradient(135deg, #ef4444, #dc2626);
                                                        color: white;
                                                        border: none;
                                                        padding: 0.5rem 1rem;
                                                        border-radius: 10px;
                                                        font-size: 0.85rem;
                                                        font-weight: 600;
                                                        cursor: pointer;
                                                        transition: all 0.3s ease;
                                                        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
                                                    "
                                                    onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(239, 68, 68, 0.4)'"
                                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(239, 68, 68, 0.3)'">
                                                🗑️ Batalkan
                                            </button>
                                        <?php elseif ($booking['status'] == 'confirmed'): ?>
                                            <span style="color: #10b981; font-weight: 700; font-size: 0.9rem;">✨ Siap Dilayani</span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-size: 0.9rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.7;">📝</div>
                    <h3 style="margin: 0 0 1rem 0; color: #334155; font-weight: 700;">Belum Ada Booking</h3>
                    <p style="margin: 0 0 2rem 0; font-size: 1.1rem;">Anda belum memiliki reservasi apapun.</p>
                    <a href="booking.php" style="
                        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                        color: white;
                        text-decoration: none;
                        padding: 1rem 2rem;
                        border-radius: 15px;
                        font-weight: 700;
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 30px rgba(139, 92, 246, 0.4)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(139, 92, 246, 0.3)'">
                        🚀 Buat Booking Pertama
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modern Account Info -->
    <div id="account-info" style="background: white; border-radius: 25px; padding: 0; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
        <!-- Section Header -->
        <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 2rem;">
            <h3 style="margin: 0; font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; gap: 0.8rem;">
                👤 Informasi Akun
            </h3>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1rem;">
                Detail profil dan pengaturan akun Anda
            </p>
        </div>
        
        <!-- Account Details -->
        <div style="padding: 2.5rem;">
            <div style="display: grid; gap: 1.5rem;">
                <!-- Name Card -->
                <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 1.5rem; border-radius: 15px; border-left: 4px solid #8b5cf6;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="background: #8b5cf6; color: white; padding: 0.8rem; border-radius: 12px; font-size: 1.2rem;">
                            👨‍💼
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.3rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Nama Lengkap
                            </div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">
                                <?= htmlspecialchars($_SESSION['name']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Card -->
                <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 1.5rem; border-radius: 15px; border-left: 4px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="background: #10b981; color: white; padding: 0.8rem; border-radius: 12px; font-size: 1.2rem;">
                            📧
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.3rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Email Address
                            </div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">
                                <?= htmlspecialchars($_SESSION['email']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Member Since Card -->
                <div style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); padding: 1.5rem; border-radius: 15px; border-left: 4px solid #f59e0b;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="background: #f59e0b; color: white; padding: 0.8rem; border-radius: 12px; font-size: 1.2rem;">
                            📅
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.3rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                Member Since
                            </div>
                            <div style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">
                                <?php
                                // Get user registration date
                                $user_query = "SELECT created_at FROM users WHERE id = ?";
                                $user_stmt = $conn->prepare($user_query);
                                $user_stmt->bind_param("i", $_SESSION['user_id']);
                                $user_stmt->execute();
                                $user_result = $user_stmt->get_result();
                                $user_data = $user_result->fetch_assoc();
                                $user_stmt->close();
                                
                                if ($user_data && $user_data['created_at']) {
                                    echo date('d F Y', strtotime($user_data['created_at']));
                                } else {
                                    echo 'Tidak tersedia';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Actions -->
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #f1f5f9;">
                <h4 style="margin: 0 0 1.5rem 0; color: #334155; font-weight: 700; font-size: 1.1rem;">🔧 Pengaturan Akun</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <button style="
                        background: linear-gradient(135deg, #3b82f6, #2563eb);
                        color: white;
                        border: none;
                        padding: 1rem;
                        border-radius: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
                    " onclick="alert('Fitur edit profil akan segera hadir!')"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(59, 130, 246, 0.4)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.3)'">
                        ✏️ Edit Profil
                    </button>
                    
                    <a href="logout.php" style="
                        background: linear-gradient(135deg, #ef4444, #dc2626);
                        color: white;
                        text-decoration: none;
                        border: none;
                        padding: 1rem;
                        border-radius: 12px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(239, 68, 68, 0.4)'"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(239, 68, 68, 0.3)'">
                        🚪 Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Responsive CSS -->
<style>
@media (max-width: 768px) {
    .booking-table { display: none !important; }
    .booking-cards { display: block !important; }
}

@media (min-width: 769px) {
    .booking-table { display: block !important; }
    .booking-cards { display: none !important; }
}
</style>

<?php 
$stmt->close();
require_once 'includes/footer.php'; 
?>
