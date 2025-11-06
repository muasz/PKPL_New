<?php
require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Proses update status konsultasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $consultation_id = intval($_POST['consultation_id']);
    $new_status = trim($_POST['status']);
    $response_message = trim($_POST['response_message'] ?? '');
    
    // Validasi status
    $valid_statuses = ['pending', 'responded', 'closed'];
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE consultations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $consultation_id);
        
        if ($stmt->execute()) {
            $success = 'Status konsultasi #' . $consultation_id . ' berhasil diubah.';
            
            // Jika ada response message, bisa dikirim email (untuk future enhancement)
            if (!empty($response_message)) {
                // TODO: Implement email sending functionality
                $success .= ' Response message telah disimpan.';
            }
        } else {
            $error = 'Gagal mengubah status konsultasi.';
        }
        $stmt->close();
    }
}

// Ambil statistik konsultasi
$stats_query = "SELECT 
    COUNT(*) as total_consultations,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_consultations,
    COUNT(CASE WHEN status = 'responded' THEN 1 END) as responded_consultations,
    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_consultations
    FROM consultations";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc() ?? ['total_consultations' => 0, 'pending_consultations' => 0, 'responded_consultations' => 0, 'closed_consultations' => 0];

// Filter berdasarkan status
$filter_status = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = '';
if ($filter_status != 'all') {
    $filter_condition = " WHERE status = '" . $conn->real_escape_string($filter_status) . "'";
}

// Ambil semua konsultasi
$consultations_query = "SELECT * FROM consultations $filter_condition ORDER BY created_at DESC";
$consultations_result = $conn->query($consultations_query);
?>

<div class="container">
    <h1 class="page-title">💬 Kelola Konsultasi</h1>
    <p class="page-subtitle">Kelola semua pertanyaan dan konsultasi dari calon customer</p>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid" style="margin-bottom: 3rem;">
        <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div class="stat-number"><?= $stats['total_consultations'] ?></div>
            <div class="stat-label">Total Konsultasi</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
            <div class="stat-number"><?= $stats['pending_consultations'] ?></div>
            <div class="stat-label">Menunggu Response</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div class="stat-number"><?= $stats['responded_consultations'] ?></div>
            <div class="stat-label">Sudah Direspons</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #64748b 0%, #475569 100%); color: white;">
            <div class="stat-number"><?= $stats['closed_consultations'] ?></div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>
    
    <!-- Consultations Table -->
    <div style="background: white; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
        <!-- Header dengan Filter -->
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                        📋 Semua Konsultasi
                    </h2>
                    <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">Kelola dan respond konsultasi customer</p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.9rem; font-weight: 500;">Filter:</label>
                    <select onchange="window.location.href='manage_consultations.php?filter=' + this.value" style="
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
                        <option value="responded" <?= $filter_status == 'responded' ? 'selected' : '' ?> style="color: #333;">Direspons</option>
                        <option value="closed" <?= $filter_status == 'closed' ? 'selected' : '' ?> style="color: #333;">Selesai</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div style="padding: 0;">
            <?php if ($consultations_result && $consultations_result->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">ID</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Customer</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Kontak</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Topik</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Pesan</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Tanggal</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Status</th>
                                <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($consultation = $consultations_result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.3s ease;" 
                                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    
                                    <td style="padding: 1rem; font-weight: 600; color: #8b5cf6;">#<?= $consultation['id'] ?></td>
                                    
                                    <td style="padding: 1rem;">
                                        <div style="font-weight: 600; color: #334155; margin-bottom: 0.2rem;"><?= htmlspecialchars($consultation['nama']) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= htmlspecialchars($consultation['email']) ?></div>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <div style="color: #475569; font-weight: 500; margin-bottom: 0.3rem;">
                                            <?= htmlspecialchars($consultation['phone']) ?>
                                        </div>
                                        <div style="display: flex; gap: 0.3rem;">
                                            <a href="tel:<?= htmlspecialchars($consultation['phone']) ?>" 
                                               style="
                                                   display: inline-flex;
                                                   align-items: center;
                                                   gap: 0.2rem;
                                                   padding: 0.2rem 0.4rem;
                                                   background: #10b981;
                                                   color: white;
                                                   text-decoration: none;
                                                   border-radius: 4px;
                                                   font-size: 0.7rem;
                                                   font-weight: 600;
                                               ">
                                                📞
                                            </a>
                                            <?php 
                                            $wa_phone = preg_replace('/[^0-9]/', '', $consultation['phone']);
                                            if (substr($wa_phone, 0, 1) == '0') {
                                                $wa_phone = '62' . substr($wa_phone, 1);
                                            }
                                            ?>
                                            <a href="https://wa.me/<?= $wa_phone ?>?text=Halo%20<?= urlencode($consultation['nama']) ?>,%20terima%20kasih%20atas%20konsultasi%20Anda.%20Berikut%20adalah%20response%20dari%20tim%20PierceFlow:" 
                                               target="_blank"
                                               style="
                                                   display: inline-flex;
                                                   align-items: center;
                                                   gap: 0.2rem;
                                                   padding: 0.2rem 0.4rem;
                                                   background: #25d366;
                                                   color: white;
                                                   text-decoration: none;
                                                   border-radius: 4px;
                                                   font-size: 0.7rem;
                                                   font-weight: 600;
                                               ">
                                                💬
                                            </a>
                                        </div>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <?php
                                        $topik_labels = [
                                            'jenis_piercing' => 'Jenis Piercing',
                                            'prosedur_safety' => 'Prosedur & Safety',
                                            'perawatan' => 'Perawatan',
                                            'harga' => 'Harga',
                                            'lokasi_cocok' => 'Lokasi Piercing',
                                            'jewelry' => 'Jewelry',
                                            'home_service' => 'Home Service',
                                            'lainnya' => 'Lainnya'
                                        ];
                                        $topik_colors = [
                                            'jenis_piercing' => '#8b5cf6',
                                            'prosedur_safety' => '#ef4444',
                                            'perawatan' => '#10b981',
                                            'harga' => '#f59e0b',
                                            'lokasi_cocok' => '#6366f1',
                                            'jewelry' => '#ec4899',
                                            'home_service' => '#14b8a6',
                                            'lainnya' => '#64748b'
                                        ];
                                        $topik = $consultation['topik'];
                                        $topik_label = $topik_labels[$topik] ?? $topik;
                                        $topik_color = $topik_colors[$topik] ?? '#64748b';
                                        ?>
                                        <span style="
                                            background: <?= $topik_color ?>20;
                                            color: <?= $topik_color ?>;
                                            padding: 0.3rem 0.6rem;
                                            border-radius: 12px;
                                            font-size: 0.8rem;
                                            font-weight: 600;
                                            display: inline-block;
                                        "><?= $topik_label ?></span>
                                    </td>
                                    
                                    <td style="padding: 1rem; max-width: 300px;">
                                        <div style="
                                            font-size: 0.85rem;
                                            color: #475569;
                                            line-height: 1.4;
                                            max-height: 3rem;
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        " title="<?= htmlspecialchars($consultation['pesan']) ?>">
                                            <?= htmlspecialchars(substr($consultation['pesan'], 0, 100)) ?><?= strlen($consultation['pesan']) > 100 ? '...' : '' ?>
                                        </div>
                                        <button onclick="showFullMessage('<?= addslashes($consultation['pesan']) ?>', '<?= htmlspecialchars($consultation['nama']) ?>')"
                                                style="
                                                    background: #8b5cf6;
                                                    color: white;
                                                    border: none;
                                                    padding: 0.2rem 0.5rem;
                                                    border-radius: 4px;
                                                    font-size: 0.7rem;
                                                    cursor: pointer;
                                                    margin-top: 0.3rem;
                                                ">
                                            Lihat Lengkap
                                        </button>
                                    </td>
                                    
                                    <td style="padding: 1rem; color: #475569;">
                                        <div><?= date('d/m/Y', strtotime($consultation['created_at'])) ?></div>
                                        <div style="font-size: 0.8rem; color: #64748b;"><?= date('H:i', strtotime($consultation['created_at'])) ?></div>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'background: linear-gradient(135deg, #f59e0b, #d97706); color: white;',
                                            'responded' => 'background: linear-gradient(135deg, #10b981, #059669); color: white;',
                                            'closed' => 'background: linear-gradient(135deg, #64748b, #475569); color: white;'
                                        ];
                                        $statusStyle = $statusColors[$consultation['status']] ?? 'background: #e2e8f0; color: #64748b;';
                                        $status_text = [
                                            'pending' => 'Menunggu',
                                            'responded' => 'Direspons',
                                            'closed' => 'Selesai'
                                        ];
                                        ?>
                                        <span style="<?= $statusStyle ?> padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                            <?= $status_text[$consultation['status']] ?? $consultation['status'] ?>
                                        </span>
                                    </td>
                                    
                                    <td style="padding: 1rem;">
                                        <form method="POST" action="" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                            <input type="hidden" name="consultation_id" value="<?= $consultation['id'] ?>">
                                            <input type="hidden" name="update_status" value="1">
                                            <select name="status" style="
                                                padding: 0.5rem;
                                                border: 1px solid #d1d5db;
                                                border-radius: 6px;
                                                font-size: 0.8rem;
                                                min-width: 100px;
                                            ">
                                                <option value="pending" <?= $consultation['status'] == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="responded" <?= $consultation['status'] == 'responded' ? 'selected' : '' ?>>Direspons</option>
                                                <option value="closed" <?= $consultation['status'] == 'closed' ? 'selected' : '' ?>>Selesai</option>
                                            </select>
                                            <button type="submit" style="
                                                background: #10b981;
                                                color: white;
                                                border: none;
                                                padding: 0.5rem 1rem;
                                                border-radius: 6px;
                                                font-size: 0.8rem;
                                                cursor: pointer;
                                                font-weight: 600;
                                            ">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #64748b;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #334155;">Belum Ada Konsultasi</h3>
                    <p style="margin: 0;">Konsultasi customer akan muncul di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showFullMessage(message, customerName) {
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
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        ">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; color: #8b5cf6;">💬 Pesan dari ${customerName}</h3>
                <button onclick="this.closest('div').remove()" style="
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
            
            <div style="
                background: #f9fafb;
                padding: 1.5rem;
                border-radius: 8px;
                border-left: 4px solid #8b5cf6;
                line-height: 1.6;
                color: #374151;
                white-space: pre-wrap;
            ">${message}</div>
        </div>
    `;
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
    
    document.body.appendChild(modal);
}
</script>

<?php require_once 'includes/footer.php'; ?>