<?php
// Prevent caching to ensure fresh data
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Proses update status booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = trim($_POST['status']);
    
    // Debug info
    error_log("Updating booking ID: $booking_id to status: $new_status");
    
    // Validasi status
    $valid_statuses = ['pending', 'confirmed', 'cancelled', 'rejected'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $booking_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $status_names = [
                    'pending' => 'Menunggu',
                    'confirmed' => 'Dikonfirmasi', 
                    'cancelled' => 'Dibatalkan',
                    'rejected' => 'Ditolak'
                ];
                $success = 'Status booking #' . $booking_id . ' berhasil diubah menjadi: ' . $status_names[$new_status];
                error_log("Update successful: $success");
            } else {
                $error = 'Tidak ada perubahan status (mungkin status sudah sama).';
                error_log("No rows affected for booking ID: $booking_id");
            }
        } else {
            $error = 'Gagal mengubah status booking: ' . $stmt->error;
            error_log("Database error: " . $stmt->error);
        }
        $stmt->close();
        
        // Redirect untuk refresh data
        if (isset($success)) {
            $redirect_url = 'admin.php?success=' . urlencode($success);
            if (isset($_GET['filter']) && $_GET['filter'] != 'all') {
                $redirect_url .= '&filter=' . urlencode($_GET['filter']);
            }
            header('Location: ' . $redirect_url);
            exit;
        }
    } else {
        $error = 'Status tidak valid: ' . $new_status;
        error_log("Invalid status: $new_status");
    }
}

// Proses aksi admin (terima/tolak booking) - untuk backward compatibility
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'confirm') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Booking #' . $booking_id . ' berhasil dikonfirmasi!';
            header('Location: admin.php?success=' . urlencode($success));
            exit;
        } else {
            $error = 'Gagal mengkonfirmasi booking.';
        }
    } elseif ($action == 'reject') {
        $stmt = $conn->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Booking #' . $booking_id . ' berhasil ditolak!';
            header('Location: admin.php?success=' . urlencode($success));
            exit;
        } else {
            $error = 'Gagal menolak booking.';
        }
    } elseif ($action == 'delete') {
        // Hanya bisa delete booking yang cancelled atau rejected
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND (status = 'cancelled' OR status = 'rejected')");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = 'Booking #' . $booking_id . ' berhasil dihapus!';
            header('Location: admin.php?success=' . urlencode($success));
            exit;
        } else {
            $error = 'Gagal menghapus booking.';
        }
    }
    $stmt->close();
}

// Cek jika ada pesan success dari redirect
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Statistik
$stats_query = "SELECT 
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
    COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_bookings,
    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_bookings,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_bookings,
    SUM(CASE WHEN status = 'confirmed' THEN s.price ELSE 0 END) as total_revenue
    FROM bookings b
    JOIN services s ON b.service_id = s.id";
$stats = $conn->query($stats_query)->fetch_assoc();

// Total users
$users_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'];

// Filter berdasarkan status
$filter_status = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = '';
if ($filter_status != 'all') {
    $filter_condition = " WHERE b.status = '" . $conn->real_escape_string($filter_status) . "'";
}

// Ambil semua booking dengan info user dan service (fresh data)
$bookings_query = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                   s.name as service_name, s.price
                   FROM bookings b
                   JOIN users u ON b.user_id = u.id
                   JOIN services s ON b.service_id = s.id
                   $filter_condition
                   ORDER BY b.created_at DESC, b.date DESC, b.time DESC";
$bookings_result = $conn->query($bookings_query);

// Debug: Refresh statistik setelah update
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<div class="container">
    <h1 class="page-title">Admin Dashboard</h1>
    <p class="page-subtitle">Kelola semua reservasi dan statistik PierceFlow</p>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Modern Dashboard Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        
        <!-- Row 1: Main Metrics (3 columns) -->
        <!-- Total Booking Card -->
        <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; position: relative; overflow: hidden; padding: 2rem;">
            <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">📊</div>
            <div class="stat-number" style="color: white; font-size: 2.8rem; font-weight: bold; margin-bottom: 0.5rem;"><?= $stats['total_bookings'] ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.95); font-weight: 600; font-size: 1.1rem;">Total Booking</div>
            <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 0.3rem;">Semua reservasi</div>
        </div>
        
        <!-- Pending Bookings -->
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; position: relative; overflow: hidden; padding: 2rem;">
            <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">⏳</div>
            <div class="stat-number" style="color: white; font-size: 2.8rem; font-weight: bold; margin-bottom: 0.5rem;"><?= $stats['pending_bookings'] ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.95); font-weight: 600; font-size: 1.1rem;">Menunggu</div>
            <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 0.3rem;">Butuh konfirmasi</div>
        </div>
        
        <!-- Confirmed Bookings -->
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; position: relative; overflow: hidden; padding: 2rem;">
            <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">✅</div>
            <div class="stat-number" style="color: white; font-size: 2.8rem; font-weight: bold; margin-bottom: 0.5rem;"><?= $stats['confirmed_bookings'] ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.95); font-weight: 600; font-size: 1.1rem;">Dikonfirmasi</div>
            <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 0.3rem;">Siap dilayani</div>
        </div>
        
        <!-- Row 2: Secondary Metrics (2 columns) + Revenue (1 larger column) -->
        <!-- Total Users -->
        <div class="stat-card" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; position: relative; overflow: hidden; padding: 2rem;">
            <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">👥</div>
            <div class="stat-number" style="color: white; font-size: 2.8rem; font-weight: bold; margin-bottom: 0.5rem;"><?= $users_count ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.95); font-weight: 600; font-size: 1.1rem;">Total User</div>
            <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 0.3rem;">Pengguna terdaftar</div>
        </div>
        
        <!-- Cancelled/Rejected -->
        <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; position: relative; overflow: hidden; padding: 2rem;">
            <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">❌</div>
            <div class="stat-number" style="color: white; font-size: 2.8rem; font-weight: bold; margin-bottom: 0.5rem;"><?= $stats['cancelled_bookings'] + $stats['rejected_bookings'] ?></div>
            <div class="stat-label" style="color: rgba(255,255,255,0.95); font-weight: 600; font-size: 1.1rem;">Dibatalkan</div>
            <div style="font-size: 0.85rem; opacity: 0.8; margin-top: 0.3rem;">Tidak jadi</div>
        </div>
        
        <!-- Revenue Card - Special Layout -->
        <div class="stat-card" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; position: relative; overflow: hidden; padding: 2rem; min-height: 140px;">
            <div style="position: absolute; top: 10px; right: 15px; font-size: 3.5rem; opacity: 0.2;">💰</div>
            <div>
                <div class="stat-number" style="color: white; font-size: 2.2rem; font-weight: bold; margin-bottom: 0.3rem;">
                    Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>
                </div>
                <div class="stat-label" style="color: rgba(255,255,255,0.95); font-weight: 600; font-size: 1.1rem; margin-bottom: 0.5rem;">
                    Total Pemasukan
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; opacity: 0.9;">
                    <span>Dari <?= $stats['confirmed_bookings'] ?> booking</span>
                    <span>Rata-rata: Rp <?php 
                        $avg = $stats['confirmed_bookings'] > 0 ? $stats['total_revenue'] / $stats['confirmed_bookings'] : 0;
                        echo number_format($avg, 0, ',', '.');
                    ?></span>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Modern Booking Table -->
    <div style="background: white; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 3rem;">
        <!-- Modern Header -->
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        📋 Semua Booking
                    </h2>
                    <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">Kelola semua reservasi pelanggan</p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.9rem; font-weight: 500;">Filter:</label>
                    <select id="filterStatus" onchange="window.location.href='admin.php?filter=' + this.value" style="
                        background: rgba(255,255,255,0.2); 
                        border: 1px solid rgba(255,255,255,0.3); 
                        border-radius: 8px; 
                        padding: 0.5rem 1rem; 
                        color: white;
                        font-size: 0.9rem;
                        backdrop-filter: blur(10px);
                        cursor: pointer;
                    ">
                        <option value="all" <?= $filter_status == 'all' ? 'selected' : '' ?> style="color: #333;">Semua Status</option>
                        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?> style="color: #333;">Menunggu</option>
                        <option value="confirmed" <?= $filter_status == 'confirmed' ? 'selected' : '' ?> style="color: #333;">Dikonfirmasi</option>
                        <option value="cancelled" <?= $filter_status == 'cancelled' ? 'selected' : '' ?> style="color: #333;">Dibatalkan</option>
                        <option value="rejected" <?= $filter_status == 'rejected' ? 'selected' : '' ?> style="color: #333;">Ditolak</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div style="padding: 0;">
            <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <!-- Modern Table Header -->
                        <thead>
                            <tr style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">ID</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">User</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Kontak</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Layanan</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Tanggal</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Waktu</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Harga</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Status</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Ubah Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                                <tr style="
                                    border-bottom: 1px solid #f1f5f9; 
                                    transition: all 0.3s ease;
                                " onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 1rem; font-weight: 600; color: #8b5cf6;">#<?= $booking['id'] ?></td>
                                    <td style="padding: 1rem;">
                                        <div style="font-weight: 600; color: #334155; margin-bottom: 0.2rem;"><?= htmlspecialchars($booking['user_name']) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($booking['user_email']) ?></div>
                                    </td>
                                    <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($booking['user_phone']) ?></td>
                                    <td style="padding: 1rem; font-weight: 500; color: #334155;"><?= htmlspecialchars($booking['service_name']) ?></td>
                                    <td style="padding: 1rem; color: #475569;"><?= date('d/m/Y', strtotime($booking['date'])) ?></td>
                                    <td style="padding: 1rem; color: #475569; font-weight: 500;"><?= date('H:i', strtotime($booking['time'])) ?></td>
                                    <td style="padding: 1rem; font-weight: 600; color: #059669;">Rp <?= number_format($booking['price'], 0, ',', '.') ?></td>
                                    <td style="padding: 1rem;">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'background: linear-gradient(135deg, #f59e0b, #d97706); color: white;',
                                            'confirmed' => 'background: linear-gradient(135deg, #10b981, #059669); color: white;',
                                            'cancelled' => 'background: linear-gradient(135deg, #64748b, #475569); color: white;',
                                            'rejected' => 'background: linear-gradient(135deg, #ef4444, #dc2626); color: white;'
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
                                        <form method="POST" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;" onsubmit="return confirm('Ubah status booking #<?= $booking['id'] ?>?')">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <select name="status" style="
                                                border: 2px solid #e2e8f0; 
                                                border-radius: 8px; 
                                                padding: 0.4rem 0.8rem; 
                                                font-size: 0.8rem;
                                                background: white;
                                                color: #334155;
                                                transition: border-color 0.3s ease;
                                                cursor: pointer;
                                            " onfocus="this.style.borderColor='#8b5cf6';" onblur="this.style.borderColor='#e2e8f0';">
                                                <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>Dikonfirmasi</option>
                                                <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                                                <option value="rejected" <?= $booking['status'] == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                                            </select>
                                            <button type="submit" name="update_status" style="
                                                background: linear-gradient(135deg, #10b981, #059669); 
                                                color: white; 
                                                border: none; 
                                                padding: 0.4rem 1rem; 
                                                border-radius: 8px; 
                                                font-size: 0.8rem; 
                                                font-weight: 600;
                                                cursor: pointer;
                                                transition: transform 0.2s ease, box-shadow 0.2s ease;
                                            " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 12px rgba(16,185,129,0.3)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                                ✓ Update
                                            </button>
                                        </form>
                                        
                                        <?php if ($booking['status'] == 'cancelled' || $booking['status'] == 'rejected'): ?>
                                            <a href="?action=delete&id=<?= $booking['id'] ?>" 
                                               style="
                                                   background: linear-gradient(135deg, #ef4444, #dc2626); 
                                                   color: white; 
                                                   text-decoration: none;
                                                   padding: 0.3rem 0.8rem; 
                                                   border-radius: 6px; 
                                                   font-size: 0.75rem; 
                                                   font-weight: 600;
                                                   display: inline-block;
                                                   margin-top: 0.5rem;
                                                   transition: transform 0.2s ease;
                                               " onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';"
                                               onclick="return confirm('Hapus booking ini secara permanen?')">
                                                🗑️ Hapus
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📅</div>
                    <h4 style="color: #475569; margin-bottom: 0.5rem;">Belum ada booking</h4>
                    <p>Tidak ada reservasi yang tersedia untuk filter ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Info Admin -->
    <div class="alert alert-warning" style="margin-top: 2rem;">
        <strong>Info Admin:</strong> Anda login sebagai <strong><?= htmlspecialchars($_SESSION['name']) ?></strong> 
        (<?= htmlspecialchars($_SESSION['email']) ?>)
    </div>
    
    <!-- Quick Actions -->
    <div style="margin-top: 3rem;">
        <h3 style="text-align: center; margin-bottom: 2rem; color: var(--primary-color);">🚀 Quick Actions</h3>
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            
            <!-- Kelola Layanan -->
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="location.href='manage_services.php'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⚙️</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Kelola Layanan</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Tambah, edit, dan hapus layanan piercing</div>
            </div>
            
            <!-- Kelola User -->
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="location.href='manage_users.php'">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Kelola User</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Lihat dan kelola data pengguna</div>
            </div>
            
            <!-- Print Laporan -->
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="window.print()">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🖨️</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Print Laporan</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Cetak laporan booking dan statistik</div>
            </div>
            
            <!-- Refresh Data -->
            <div class="stat-card" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="location.reload()">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔄</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Refresh Data</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Muat ulang data terbaru</div>
            </div>
            
            <!-- Export Data -->
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="exportBookingData()">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Export Data</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Download data booking ke CSV</div>
            </div>
            
            <!-- Backup Database -->
            <div class="stat-card" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="alert('Fitur backup akan segera tersedia!')">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💾</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Backup Data</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Backup database dan pengaturan</div>
            </div>
            
        </div>
    </div>
    
    <style>
    .stat-card:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2) !important;
    }
    </style>
    
    <script>
    function exportBookingData() {
        // Simple CSV export function
        const table = document.querySelector('table');
        if (!table) {
            alert('Tidak ada data booking untuk diekspor');
            return;
        }
        
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(function(row) {
            const cols = row.querySelectorAll('td, th');
            const csvRow = [];
            cols.forEach(function(col, index) {
                // Skip kolom "Ubah Status" (kolom terakhir)
                if (index < cols.length - 1) {
                    csvRow.push('"' + col.innerText.replace(/"/g, '""') + '"');
                }
            });
            if (csvRow.length > 0) {
                csv.push(csvRow.join(','));
            }
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'booking_data_' + new Date().toISOString().slice(0,10) + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
    </script>
</div>

<?php require_once 'includes/footer.php'; ?>
