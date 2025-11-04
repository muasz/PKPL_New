<?php
require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Proses Tambah Layanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $name = trim($_POST['name']);
        $price = intval($_POST['price']);
        $description = trim($_POST['description']);
        
        if (empty($name) || $price <= 0) {
            $error = 'Nama layanan dan harga harus diisi dengan benar!';
        } else {
            $stmt = $conn->prepare("INSERT INTO services (name, price, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $name, $price, $description);
            if ($stmt->execute()) {
                $success = 'Layanan berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan layanan.';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] == 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $price = intval($_POST['price']);
        $description = trim($_POST['description']);
        
        if (empty($name) || $price <= 0) {
            $error = 'Nama layanan dan harga harus diisi dengan benar!';
        } else {
            $stmt = $conn->prepare("UPDATE services SET name = ?, price = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sisi", $name, $price, $description, $id);
            if ($stmt->execute()) {
                $success = 'Layanan berhasil diupdate!';
            } else {
                $error = 'Gagal mengupdate layanan.';
            }
            $stmt->close();
        }
    }
}

// Proses Hapus Layanan
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Cek apakah ada booking yang menggunakan layanan ini
    $check = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE service_id = $id");
    $result = $check->fetch_assoc();
    
    if ($result['total'] > 0) {
        $error = 'Tidak dapat menghapus layanan yang masih memiliki booking!';
    } else {
        $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = 'Layanan berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus layanan.';
        }
        $stmt->close();
    }
}

// Ambil data layanan untuk edit
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_service = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Ambil semua layanan
$services_query = "SELECT s.*, COUNT(b.id) as total_bookings 
                   FROM services s 
                   LEFT JOIN bookings b ON s.id = b.service_id 
                   GROUP BY s.id 
                   ORDER BY s.name ASC";
$services_result = $conn->query($services_query);
?>

<!-- Modern Services Management Page -->
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 2rem 0;">
    <div class="container">
        <!-- Modern Page Header -->
        <div style="background: white; border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="margin: 0; font-size: 2rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                        🛠️ Manajemen Layanan
                    </h1>
                    <p style="margin: 0.5rem 0 0 0; color: #64748b; font-size: 1.1rem;">Kelola semua layanan piercing yang tersedia</p>
                </div>
                <a href="admin.php" style="
                    background: linear-gradient(135deg, #64748b, #475569); 
                    color: white; 
                    text-decoration: none; 
                    padding: 0.8rem 1.5rem; 
                    border-radius: 12px; 
                    font-weight: 600;
                    transition: transform 0.2s ease;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    ← Dashboard
                </a>
            </div>
        </div>
        
        <!-- Alert Messages -->
        <?php if ($success): ?>
            <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500;">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500;">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    
        <!-- Modern Form Section -->
        <div style="background: white; border-radius: 20px; padding: 0; margin-bottom: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
            <!-- Form Header -->
            <div style="background: linear-gradient(135deg, <?= $edit_service ? '#f59e0b' : '#10b981' ?> 0%, <?= $edit_service ? '#d97706' : '#059669' ?> 100%); padding: 2rem; color: white;">
                <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <?= $edit_service ? '✏️ Edit Layanan' : '➕ Tambah Layanan Baru' ?>
                </h2>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">
                    <?= $edit_service ? 'Perbarui informasi layanan yang sudah ada' : 'Tambahkan layanan piercing baru ke sistem' ?>
                </p>
            </div>
            
            <!-- Form Content -->
            <div style="padding: 2rem;">
                <form method="POST" action="" style="display: grid; gap: 1.5rem;">
                    <input type="hidden" name="action" value="<?= $edit_service ? 'edit' : 'add' ?>">
                    <?php if ($edit_service): ?>
                        <input type="hidden" name="id" value="<?= $edit_service['id'] ?>">
                    <?php endif; ?>
                    
                    <!-- Service Name Input -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            Nama Layanan *
                        </label>
                        <input type="text" name="name" required 
                               value="<?= $edit_service ? htmlspecialchars($edit_service['name']) : '' ?>"
                               style="
                                   width: 100%; 
                                   padding: 0.75rem 1rem; 
                                   border: 2px solid #e2e8f0; 
                                   border-radius: 12px; 
                                   font-size: 1rem;
                                   transition: border-color 0.3s ease;
                                   background: white;
                               "
                               onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                               placeholder="Contoh: Tindik Hidung">
                    </div>
                    
                    <!-- Price Input -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            Harga (Rp) *
                        </label>
                        <input type="number" name="price" required min="1000" step="1000"
                               value="<?= $edit_service ? $edit_service['price'] : '' ?>"
                               style="
                                   width: 100%; 
                                   padding: 0.75rem 1rem; 
                                   border: 2px solid #e2e8f0; 
                                   border-radius: 12px; 
                                   font-size: 1rem;
                                   transition: border-color 0.3s ease;
                                   background: white;
                               "
                               onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'"
                               onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                               placeholder="85000">
                    </div>
                    
                    <!-- Description Input -->
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #334155;">
                            Deskripsi
                        </label>
                        <textarea name="description" rows="4"
                                  style="
                                      width: 100%; 
                                      padding: 0.75rem 1rem; 
                                      border: 2px solid #e2e8f0; 
                                      border-radius: 12px; 
                                      font-size: 1rem;
                                      transition: border-color 0.3s ease;
                                      background: white;
                                      resize: vertical;
                                  "
                                  onfocus="this.style.borderColor='#10b981'; this.style.boxShadow='0 0 0 3px rgba(16,185,129,0.1)'"
                                  onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                                  placeholder="Deskripsi layanan piercing..."><?= $edit_service ? htmlspecialchars($edit_service['description']) : '' ?></textarea>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: flex-end;">
                        <?php if ($edit_service): ?>
                            <a href="manage_services.php" style="
                                background: #64748b; 
                                color: white; 
                                text-decoration: none; 
                                padding: 0.75rem 1.5rem; 
                                border-radius: 12px; 
                                font-weight: 600;
                                transition: transform 0.2s ease;
                                display: flex;
                                align-items: center;
                                gap: 0.5rem;
                            " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                Batal
                            </a>
                        <?php endif; ?>
                        
                        <button type="submit" style="
                            background: linear-gradient(135deg, <?= $edit_service ? '#f59e0b' : '#10b981' ?>, <?= $edit_service ? '#d97706' : '#059669' ?>); 
                            color: white; 
                            border: none; 
                            padding: 0.75rem 2rem; 
                            border-radius: 12px; 
                            font-size: 1rem; 
                            font-weight: 600;
                            cursor: pointer;
                            transition: transform 0.2s ease;
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                        " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            <?= $edit_service ? '💾 Update Layanan' : '➕ Tambah Layanan' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    
        <!-- Modern Services Table -->
        <div style="background: white; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem;">
            <!-- Table Header -->
            <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; color: white;">
                <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    📋 Daftar Layanan
                </h2>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">Semua layanan piercing yang tersedia</p>
            </div>
            
            <div style="padding: 0;">
                <?php if ($services_result && $services_result->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <!-- Modern Table Header -->
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">ID</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Nama Layanan</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Harga</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Deskripsi</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Total Booking</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($service = $services_result->fetch_assoc()): ?>
                                    <tr style="
                                        border-bottom: 1px solid #f1f5f9; 
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                        <td style="padding: 1rem; font-weight: 600; color: #8b5cf6;">#<?= $service['id'] ?></td>
                                        <td style="padding: 1rem; font-weight: 600; color: #334155;"><?= htmlspecialchars($service['name']) ?></td>
                                        <td style="padding: 1rem; font-weight: 600; color: #059669;">Rp <?= number_format($service['price'], 0, ',', '.') ?></td>
                                        <td style="padding: 1rem; color: #475569; max-width: 200px;">
                                            <?php 
                                            $desc = htmlspecialchars($service['description']);
                                            echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                            ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <span style="
                                                background: <?= $service['total_bookings'] > 0 ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #64748b, #475569)' ?>; 
                                                color: white; 
                                                padding: 0.3rem 0.8rem; 
                                                border-radius: 15px; 
                                                font-size: 0.8rem; 
                                                font-weight: 600;
                                            ">
                                                <?= $service['total_bookings'] ?> booking
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                                <a href="?edit=<?= $service['id'] ?>" style="
                                                    background: linear-gradient(135deg, #f59e0b, #d97706); 
                                                    color: white; 
                                                    text-decoration: none; 
                                                    padding: 0.4rem 1rem; 
                                                    border-radius: 8px; 
                                                    font-size: 0.8rem; 
                                                    font-weight: 600;
                                                    transition: transform 0.2s ease;
                                                    display: flex;
                                                    align-items: center;
                                                    gap: 0.3rem;
                                                " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                    ✏️ Edit
                                                </a>
                                                
                                                <?php if ($service['total_bookings'] == 0): ?>
                                                    <a href="?delete=<?= $service['id'] ?>" 
                                                       onclick="return confirm('Hapus layanan ini?')"
                                                       style="
                                                           background: linear-gradient(135deg, #ef4444, #dc2626); 
                                                           color: white; 
                                                           text-decoration: none; 
                                                           padding: 0.4rem 1rem; 
                                                           border-radius: 8px; 
                                                           font-size: 0.8rem; 
                                                           font-weight: 600;
                                                           transition: transform 0.2s ease;
                                                           display: flex;
                                                           align-items: center;
                                                           gap: 0.3rem;
                                                       " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                        🗑️ Hapus
                                                    </a>
                                                <?php else: ?>
                                                    <span style="
                                                        background: #e2e8f0; 
                                                        color: #64748b; 
                                                        padding: 0.4rem 1rem; 
                                                        border-radius: 8px; 
                                                        font-size: 0.8rem; 
                                                        font-weight: 600;
                                                        cursor: not-allowed;
                                                        display: flex;
                                                        align-items: center;
                                                        gap: 0.3rem;
                                                    " title="Tidak bisa dihapus karena ada booking">
                                                        🔒 Terkunci
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🛠️</div>
                        <h4 style="color: #475569; margin-bottom: 0.5rem;">Belum ada layanan</h4>
                        <p>Tambahkan layanan piercing pertama Anda</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
