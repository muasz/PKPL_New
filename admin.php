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
            $redirect_params = ['success=' . urlencode($success)];
            if (isset($_GET['filter']) && $_GET['filter'] != 'all') {
                $redirect_params[] = 'filter=' . urlencode($_GET['filter']);
            }
            if (isset($_GET['type']) && $_GET['type'] != 'all') {
                $redirect_params[] = 'type=' . urlencode($_GET['type']);
            }
            $redirect_url = 'admin.php?' . implode('&', $redirect_params);
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
$filter_conditions = [];

if ($filter_status != 'all') {
    $filter_conditions[] = "b.status = '" . $conn->real_escape_string($filter_status) . "'";
}

$filter_condition = '';
if (!empty($filter_conditions)) {
    $filter_condition = " WHERE " . implode(' AND ', $filter_conditions);
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
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <!-- Status Filter -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label style="font-size: 0.9rem; font-weight: 500;">Status:</label>
                        <select id="filterStatus" onchange="updateFilters()" style="
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
                    
                    <!-- Reset Filter Button -->
                    <?php if ($filter_status != 'all'): ?>
                    <button onclick="window.location.href='admin.php'" style="
                        background: rgba(239, 68, 68, 0.8);
                        border: 1px solid rgba(239, 68, 68, 0.4);
                        border-radius: 8px;
                        padding: 0.5rem 1rem;
                        color: white;
                        font-size: 0.9rem;
                        cursor: pointer;
                        backdrop-filter: blur(10px);
                        transition: all 0.3s ease;
                    " onmouseover="this.style.background='rgba(239, 68, 68, 1)'" 
                       onmouseout="this.style.background='rgba(239, 68, 68, 0.8)'">
                        🗑️ Reset
                    </button>
                    <?php endif; ?>
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
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Alamat</th>
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
                                    <td style="padding: 1rem;">
                                        <div style="color: #475569; font-weight: 500; margin-bottom: 0.3rem;">
                                            <?= htmlspecialchars($booking['user_phone']) ?>
                                        </div>
                                        <div style="display: flex; gap: 0.3rem; flex-wrap: wrap;">
                                            <a href="tel:<?= htmlspecialchars($booking['user_phone']) ?>" 
                                               style="
                                                   display: inline-flex;
                                                   align-items: center;
                                                   gap: 0.3rem;
                                                   padding: 0.3rem 0.6rem;
                                                   background: linear-gradient(135deg, #10b981, #059669);
                                                   color: white;
                                                   text-decoration: none;
                                                   border-radius: 5px;
                                                   font-size: 0.75rem;
                                                   font-weight: 600;
                                                   transition: all 0.3s ease;
                                               "
                                               onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 2px 8px rgba(16,185,129,0.3)'"
                                               onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                                               title="Hubungi <?= htmlspecialchars($booking['user_name']) ?>">
                                                📞 Call
                                            </a>
                                            
                                            <?php 
                                            // Format phone for WhatsApp (remove +, spaces, dashes)
                                            $wa_phone = preg_replace('/[^0-9]/', '', $booking['user_phone']);
                                            if (substr($wa_phone, 0, 1) == '0') {
                                                $wa_phone = '62' . substr($wa_phone, 1); // Replace leading 0 with 62 for Indonesia
                                            }
                                            ?>
                                            <a href="https://wa.me/<?= $wa_phone ?>?text=Halo%20<?= urlencode($booking['user_name']) ?>,%20ini%20dari%20PierceFlow%20Studio%20mengenai%20booking%20Anda%20pada%20<?= urlencode(date('d/m/Y', strtotime($booking['date']))) ?>%20jam%20<?= urlencode(date('H:i', strtotime($booking['time']))) ?>" 
                                               target="_blank"
                                               style="
                                                   display: inline-flex;
                                                   align-items: center;
                                                   gap: 0.3rem;
                                                   padding: 0.3rem 0.6rem;
                                                   background: linear-gradient(135deg, #25d366, #20ba5a);
                                                   color: white;
                                                   text-decoration: none;
                                                   border-radius: 5px;
                                                   font-size: 0.75rem;
                                                   font-weight: 600;
                                                   transition: all 0.3s ease;
                                               "
                                               onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 2px 8px rgba(37,211,102,0.3)'"
                                               onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'"
                                               title="WhatsApp <?= htmlspecialchars($booking['user_name']) ?>">
                                                💬 WA
                                            </a>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem; font-weight: 500; color: #334155;"><?= htmlspecialchars($booking['service_name']) ?></td>
                                    <td style="padding: 1rem; color: #475569;"><?= date('d/m/Y', strtotime($booking['date'])) ?></td>
                                    <td style="padding: 1rem; color: #475569; font-weight: 500;"><?= date('H:i', strtotime($booking['time'])) ?></td>
                                    <td style="padding: 1rem; max-width: 200px;">
                                        <?php 
                                        $current_address = $booking['address'] ?? '';
                                        
                                        if (!empty($current_address) && trim($current_address) !== ''): ?>
                                            <div style="
                                                font-size: 0.85rem;
                                                color: #475569;
                                                line-height: 1.4;
                                                background: #f0fdf4;
                                                padding: 0.8rem;
                                                border-radius: 10px;
                                                border-left: 3px solid #10b981;
                                            ">
                                                <div style="font-weight: 600; color: #10b981; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.3rem;">
                                                    🏠 Alamat Customer:
                                                </div>
                                                <div style="margin-bottom: 0.8rem; color: #374151;">
                                                    📍 <?= htmlspecialchars(substr($current_address, 0, 60)) ?><?= strlen($current_address) > 60 ? '...' : '' ?>
                                                </div>
                                                
                                                <!-- Action Buttons -->
                                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                    <!-- Google Maps Button -->
                                                    <a href="https://maps.google.com/?q=<?= urlencode($current_address) ?>" 
                                                       target="_blank" 
                                                       style="
                                                           display: inline-flex;
                                                           align-items: center;
                                                           gap: 0.3rem;
                                                           padding: 0.4rem 0.8rem;
                                                           background: linear-gradient(135deg, #10b981, #059669);
                                                           color: white;
                                                           text-decoration: none;
                                                           border-radius: 6px;
                                                           font-size: 0.75rem;
                                                           font-weight: 600;
                                                           transition: all 0.3s ease;
                                                           border: none;
                                                           cursor: pointer;
                                                       "
                                                       onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(16,185,129,0.3)'"
                                                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                                       title="Buka di Google Maps">
                                                        🗺️ Maps
                                                    </a>
                                                    
                                                    <!-- Copy Address Button -->
                                                    <button onclick="copyAddress('<?= addslashes($current_address) ?>', <?= $booking['id'] ?>)"
                                                            style="
                                                                display: inline-flex;
                                                                align-items: center;
                                                                gap: 0.3rem;
                                                                padding: 0.4rem 0.8rem;
                                                                background: linear-gradient(135deg, #6366f1, #4f46e5);
                                                                color: white;
                                                                border: none;
                                                                border-radius: 6px;
                                                                font-size: 0.75rem;
                                                                font-weight: 600;
                                                                cursor: pointer;
                                                                transition: all 0.3s ease;
                                                            "
                                                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(99,102,241,0.3)'"
                                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                                            title="Copy alamat ke clipboard">
                                                        📋 Copy
                                                    </button>
                                                    
                                                    <!-- Full Address Modal Button -->
                                                    <button onclick="showFullAddress('<?= addslashes($current_address) ?>', '<?= htmlspecialchars($booking['user_name']) ?>')"
                                                            style="
                                                                display: inline-flex;
                                                                align-items: center;
                                                                gap: 0.3rem;
                                                                padding: 0.4rem 0.8rem;
                                                                background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                                                                color: white;
                                                                border: none;
                                                                border-radius: 6px;
                                                                font-size: 0.75rem;
                                                                font-weight: 600;
                                                                cursor: pointer;
                                                                transition: all 0.3s ease;
                                                            "
                                                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(139,92,246,0.3)'"
                                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                                                            title="Lihat alamat lengkap">
                                                        👁️ Detail
                                                    </button>
                                                </div>
                                            </div>
                                        <?php else: // home_service but no address ?>
                                            <div style="
                                                font-size: 0.85rem;
                                                color: #ef4444;
                                                font-weight: 500;
                                                background: #fef2f2;
                                                padding: 0.5rem;
                                                border-radius: 8px;
                                                border-left: 3px solid #ef4444;
                                                display: flex;
                                                align-items: center;
                                                gap: 0.3rem;
                                            ">
                                                ⚠️ Alamat belum diisi!
                                            </div>
                                        <?php endif; ?>
                                    </td>
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
                                        <?php
                                        $form_params = [];
                                        if (isset($_GET['filter']) && $_GET['filter'] != 'all') {
                                            $form_params[] = 'filter=' . urlencode($_GET['filter']);
                                        }
                                        if (isset($_GET['type']) && $_GET['type'] != 'all') {
                                            $form_params[] = 'type=' . urlencode($_GET['type']);
                                        }
                                        $form_action = 'admin.php' . (!empty($form_params) ? '?' . implode('&', $form_params) : '');
                                        ?>
                                        <form method="POST" action="<?= $form_action ?>" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;" onsubmit="return confirmUpdate(<?= $booking['id'] ?>, this)">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <select name="status" id="status_<?= $booking['id'] ?>" style="
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
                                            <button type="submit" style="
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
            
            <!-- Analytics -->
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; cursor: pointer; transition: transform 0.3s;" onclick="window.scrollTo(0, 0);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">Analytics</div>
                <div style="opacity: 0.9; font-size: 0.9rem;">Lihat statistik booking dan performa</div>
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
    function updateFilters() {
        const statusFilter = document.getElementById('filterStatus').value;
        const typeFilter = document.getElementById('filterType').value;
        
        let url = 'admin.php?';
        let params = [];
        
        if (statusFilter !== 'all') {
            params.push('filter=' + encodeURIComponent(statusFilter));
        }
        
        if (typeFilter !== 'all') {
            params.push('type=' + encodeURIComponent(typeFilter));
        }
        
        if (params.length > 0) {
            url += params.join('&');
        } else {
            url = 'admin.php';
        }
        
        window.location.href = url;
    }
    
    function confirmUpdate(bookingId, form) {
        const selectElement = form.querySelector('select[name="status"]');
        const newStatus = selectElement.value;
        const statusNames = {
            'pending': 'Menunggu',
            'confirmed': 'Dikonfirmasi', 
            'cancelled': 'Dibatalkan',
            'rejected': 'Ditolak'
        };
        
        const confirmed = confirm(`Ubah status booking #${bookingId} menjadi "${statusNames[newStatus]}"?`);
        
        if (confirmed) {
            // Debug: Log form data
            console.log('Submitting form:', {
                bookingId: bookingId,
                newStatus: newStatus,
                formAction: form.action,
                formMethod: form.method
            });
            
            // Tampilkan loading indicator
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '⏳ Updating...';
            submitBtn.disabled = true;
            
            // Allow form to submit
            return true;
        }
        
        return false;
    }
    
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
    
    // Auto-hide success/error messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    });
    
    // Address Management Functions
    function copyAddress(address, bookingId) {
        navigator.clipboard.writeText(address).then(function() {
            // Show success notification
            showNotification('✅ Alamat booking #' + bookingId + ' berhasil di-copy!', 'success');
        }).catch(function(err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = address;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('✅ Alamat berhasil di-copy!', 'success');
        });
    }
    
    function showFullAddress(address, userName) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        `;
        
        modal.innerHTML = `
            <div style="
                background: white;
                padding: 2rem;
                border-radius: 15px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                animation: modalSlideIn 0.3s ease-out;
            ">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0; color: #10b981; display: flex; align-items: center; gap: 0.5rem;">
                        🏠 Alamat Home Service
                    </h3>
                    <button onclick="this.closest('.modal').remove()" style="
                        background: #ef4444;
                        color: white;
                        border: none;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        cursor: pointer;
                        font-size: 1rem;
                    ">×</button>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; color: #374151; margin-bottom: 0.5rem;">
                        👤 Customer: ${userName}
                    </div>
                </div>
                
                <div style="
                    background: #f9fafb;
                    padding: 1rem;
                    border-radius: 8px;
                    border-left: 4px solid #10b981;
                    margin-bottom: 1.5rem;
                    line-height: 1.6;
                    color: #374151;
                ">
                    📍 ${address}
                </div>
                
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button onclick="copyAddress('${address.replace(/'/g, "\\'")}', 'modal')" style="
                        background: linear-gradient(135deg, #6366f1, #4f46e5);
                        color: white;
                        border: none;
                        padding: 0.75rem 1.5rem;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    ">📋 Copy Alamat</button>
                    
                    <a href="https://maps.google.com/?q=${encodeURIComponent(address)}" 
                       target="_blank" 
                       style="
                           background: linear-gradient(135deg, #10b981, #059669);
                           color: white;
                           text-decoration: none;
                           padding: 0.75rem 1.5rem;
                           border-radius: 8px;
                           font-weight: 600;
                           display: flex;
                           align-items: center;
                           gap: 0.5rem;
                       ">🗺️ Buka Maps</a>
                </div>
            </div>
        `;
        
        modal.className = 'modal';
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        document.body.appendChild(modal);
    }
    
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? '#10b981' : '#ef4444';
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideInRight 0.3s ease-out;
        `;
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    </script>
    
    <style>
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.7) translateY(-30px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100px);
        }
    }
    </style>
</div>

<?php require_once 'includes/footer.php'; ?>
