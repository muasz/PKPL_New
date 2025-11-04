<?php
require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Proses Hapus User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Tidak bisa hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $error = 'Anda tidak dapat menghapus akun sendiri!';
    } else {
        // Cek role
        $check = $conn->query("SELECT role FROM users WHERE id = $id");
        $user = $check->fetch_assoc();
        
        if ($user['role'] == 'admin') {
            $error = 'Tidak dapat menghapus admin lain!';
        } else {
            // Hapus semua booking user tersebut
            $conn->query("DELETE FROM bookings WHERE user_id = $id");
            
            // Hapus user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $success = 'User berhasil dihapus beserta semua bookingnya!';
            } else {
                $error = 'Gagal menghapus user.';
            }
            $stmt->close();
        }
    }
}

// Ambil semua user
$users_query = "SELECT u.*, COUNT(b.id) as total_bookings,
                SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings
                FROM users u
                LEFT JOIN bookings b ON u.id = b.user_id
                GROUP BY u.id
                ORDER BY u.role DESC, u.created_at DESC";
$users_result = $conn->query($users_query);
?>

<!-- Modern Users Management Page -->
<div style="min-height: 100vh; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 2rem 0;">
    <div class="container">
        <!-- Modern Page Header -->
        <div style="background: white; border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="margin: 0; font-size: 2rem; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 0.5rem;">
                        👥 Manajemen User
                    </h1>
                    <p style="margin: 0.5rem 0 0 0; color: #64748b; font-size: 1.1rem;">Kelola semua pengguna yang terdaftar</p>
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
    
        <!-- Modern Users Table -->
        <div style="background: white; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem;">
            <!-- Table Header -->
            <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; color: white;">
                <h2 style="margin: 0; font-size: 1.4rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    👥 Daftar User
                </h2>
                <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">Semua pengguna yang terdaftar di sistem</p>
            </div>
            
            <div style="padding: 0;">
                <?php if ($users_result && $users_result->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <!-- Modern Table Header -->
                            <thead>
                                <tr style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">ID</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Nama</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Email</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Nomor HP</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Role</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Booking</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Terdaftar</th>
                                    <th style="padding: 1.2rem 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr style="
                                        border-bottom: 1px solid #f1f5f9; 
                                        transition: all 0.3s ease;
                                    " onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                        <td style="padding: 1rem; font-weight: 600; color: #8b5cf6;">#<?= $user['id'] ?></td>
                                        <td style="padding: 1rem; font-weight: 600; color: #334155;"><?= htmlspecialchars($user['name']) ?></td>
                                        <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($user['email']) ?></td>
                                        <td style="padding: 1rem; color: #475569;"><?= htmlspecialchars($user['phone']) ?></td>
                                        <td style="padding: 1rem;">
                                            <span style="
                                                background: <?= $user['role'] == 'admin' ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #10b981, #059669)' ?>; 
                                                color: white; 
                                                padding: 0.4rem 1rem; 
                                                border-radius: 20px; 
                                                font-size: 0.8rem; 
                                                font-weight: 600;
                                            ">
                                                <?= $user['role'] == 'admin' ? '👑 Admin' : '👤 User' ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                <span style="
                                                    background: linear-gradient(135deg, #8b5cf6, #7c3aed); 
                                                    color: white; 
                                                    padding: 0.2rem 0.6rem; 
                                                    border-radius: 12px; 
                                                    font-size: 0.7rem; 
                                                    font-weight: 600;
                                                    text-align: center;
                                                ">
                                                    Total: <?= $user['total_bookings'] ?>
                                                </span>
                                                <span style="
                                                    background: linear-gradient(135deg, #10b981, #059669); 
                                                    color: white; 
                                                    padding: 0.2rem 0.6rem; 
                                                    border-radius: 12px; 
                                                    font-size: 0.7rem; 
                                                    font-weight: 600;
                                                    text-align: center;
                                                ">
                                                    Konfirm: <?= $user['confirmed_bookings'] ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td style="padding: 1rem; color: #475569; font-size: 0.85rem;"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                        <td style="padding: 1rem;">
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span style="
                                                    background: linear-gradient(135deg, #8b5cf6, #7c3aed); 
                                                    color: white; 
                                                    padding: 0.4rem 1rem; 
                                                    border-radius: 8px; 
                                                    font-size: 0.8rem; 
                                                    font-weight: 600;
                                                ">
                                                    ✋ Anda
                                                </span>
                                            <?php elseif ($user['role'] == 'admin'): ?>
                                                <span style="
                                                    background: #e2e8f0; 
                                                    color: #64748b; 
                                                    padding: 0.4rem 1rem; 
                                                    border-radius: 8px; 
                                                    font-size: 0.8rem; 
                                                    font-weight: 600;
                                                    cursor: not-allowed;
                                                " title="Admin tidak bisa dihapus">
                                                    🔒 Protected
                                                </span>
                                            <?php else: ?>
                                                <a href="?delete=<?= $user['id'] ?>" 
                                                   onclick="return confirm('Hapus user <?= htmlspecialchars($user['name']) ?>?\n\nSemua booking mereka juga akan dihapus!')"
                                                   style="
                                                       background: linear-gradient(135deg, #ef4444, #dc2626); 
                                                       color: white; 
                                                       text-decoration: none; 
                                                       padding: 0.4rem 1rem; 
                                                       border-radius: 8px; 
                                                       font-size: 0.8rem; 
                                                       font-weight: 600;
                                                       transition: transform 0.2s ease;
                                                       display: inline-flex;
                                                       align-items: center;
                                                       gap: 0.3rem;
                                                   " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
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
                        <div style="font-size: 4rem; margin-bottom: 1rem;">👥</div>
                        <h4 style="color: #475569; margin-bottom: 0.5rem;">Belum ada user</h4>
                        <p>Sistem belum memiliki pengguna terdaftar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Modern Statistics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <?php
            $stats = $conn->query("SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
                COUNT(CASE WHEN role = 'user' THEN 1 END) as total_customers
                FROM users")->fetch_assoc();
            ?>
            
            <!-- Total Users -->
            <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">👥</div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #8b5cf6; margin-bottom: 0.5rem;"><?= $stats['total_users'] ?></div>
                <div style="color: #64748b; font-weight: 600; font-size: 1.1rem;">Total User</div>
                <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.3rem;">Semua pengguna terdaftar</div>
            </div>
            
            <!-- Total Admins -->
            <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">👑</div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.5rem;"><?= $stats['total_admins'] ?></div>
                <div style="color: #64748b; font-weight: 600; font-size: 1.1rem;">Administrator</div>
                <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.3rem;">Pengelola sistem</div>
            </div>
            
            <!-- Total Customers -->
            <div style="background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 15px; right: 20px; font-size: 3rem; opacity: 0.3;">👤</div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.5rem;"><?= $stats['total_customers'] ?></div>
                <div style="color: #64748b; font-weight: 600; font-size: 1.1rem;">Customer</div>
                <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.3rem;">Pelanggan regular</div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
