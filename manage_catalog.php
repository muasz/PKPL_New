<?php
require_once 'includes/header.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Proses tambah catalog item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_catalog'])) {
    $title = trim($_POST['title']);
    $image_url = trim($_POST['image_url']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    
    if (empty($title) || empty($image_url) || empty($category)) {
        $error = 'Judul, gambar URL, dan kategori harus diisi!';
    } else {
        $stmt = $conn->prepare("INSERT INTO catalog (title, image_url, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $image_url, $description, $category);
        
        if ($stmt->execute()) {
            $success = 'Item katalog berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan item: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Proses edit catalog item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_catalog'])) {
    $id = intval($_POST['catalog_id']);
    $title = trim($_POST['title']);
    $image_url = trim($_POST['image_url']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    
    if (empty($title) || empty($image_url) || empty($category)) {
        $error = 'Judul, gambar URL, dan kategori harus diisi!';
    } else {
        $stmt = $conn->prepare("UPDATE catalog SET title = ?, image_url = ?, description = ?, category = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $image_url, $description, $category, $id);
        
        if ($stmt->execute()) {
            $success = 'Item katalog berhasil diupdate!';
        } else {
            $error = 'Gagal update item: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Proses hapus catalog item
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM catalog WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Item katalog berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus item: ' . $stmt->error;
    }
    $stmt->close();
}

// Ambil semua catalog items
$catalog_result = $conn->query("SELECT * FROM catalog ORDER BY created_at DESC");

// Untuk mode edit
$edit_mode = false;
$edit_item = null;
if (isset($_GET['edit']) && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $edit_query = $conn->query("SELECT * FROM catalog WHERE id = $edit_id");
    if ($edit_query->num_rows > 0) {
        $edit_mode = true;
        $edit_item = $edit_query->fetch_assoc();
    }
}
?>

<div class="container">
    <h1 class="page-title">📸 Kelola Katalog</h1>
    <p class="page-subtitle">Tambah, edit, dan hapus item katalog galeri</p>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <!-- Form Tambah/Edit Catalog -->
    <div style="background: white; border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <h2 style="margin: 0 0 1.5rem 0; color: var(--primary-color);">
            <?= $edit_mode ? '✏️ Edit Item Katalog' : '➕ Tambah Item Katalog' ?>
        </h2>
        
        <form method="POST" action="manage_catalog.php">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="catalog_id" value="<?= $edit_item['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title">Judul *</label>
                <input type="text" id="title" name="title" required 
                       value="<?= $edit_mode ? htmlspecialchars($edit_item['title']) : '' ?>"
                       placeholder="Contoh: Tindik Telinga Classic">
            </div>
            
            <div class="form-group">
                <label for="image_url">URL Gambar *</label>
                <input type="url" id="image_url" name="image_url" required 
                       value="<?= $edit_mode ? htmlspecialchars($edit_item['image_url']) : '' ?>"
                       placeholder="https://example.com/image.jpg">
                <small style="color: #64748b;">Masukkan URL gambar (bisa dari upload atau placeholder)</small>
            </div>
            
            <div class="form-group">
                <label for="category">Kategori *</label>
                <select id="category" name="category" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="telinga" <?= $edit_mode && $edit_item['category'] == 'telinga' ? 'selected' : '' ?>>👂 Telinga</option>
                    <option value="hidung" <?= $edit_mode && $edit_item['category'] == 'hidung' ? 'selected' : '' ?>>👃 Hidung</option>
                    <option value="industrial" <?= $edit_mode && $edit_item['category'] == 'industrial' ? 'selected' : '' ?>>🔗 Industrial</option>
                    <option value="helix" <?= $edit_mode && $edit_item['category'] == 'helix' ? 'selected' : '' ?>>🌀 Helix</option>
                    <option value="tragus" <?= $edit_mode && $edit_item['category'] == 'tragus' ? 'selected' : '' ?>>⭐ Tragus</option>
                    <option value="septum" <?= $edit_mode && $edit_item['category'] == 'septum' ? 'selected' : '' ?>>💍 Septum</option>
                    <option value="lainnya" <?= $edit_mode && $edit_item['category'] == 'lainnya' ? 'selected' : '' ?>>✨ Lainnya</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" 
                          placeholder="Deskripsi detail tentang item katalog ini..."><?= $edit_mode ? htmlspecialchars($edit_item['description']) : '' ?></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button type="submit" name="<?= $edit_mode ? 'edit_catalog' : 'add_catalog' ?>" class="btn btn-primary">
                    <?= $edit_mode ? '💾 Update Item' : '➕ Tambah Item' ?>
                </button>
                
                <?php if ($edit_mode): ?>
                    <a href="manage_catalog.php" class="btn" style="background: #64748b; color: white; text-decoration: none;">
                        ❌ Batal Edit
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Daftar Catalog Items -->
    <div style="background: white; border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 2rem; color: white;">
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">📋 Daftar Item Katalog</h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Total: <?= $catalog_result->num_rows ?> item</p>
        </div>
        
        <div style="padding: 0;">
            <?php if ($catalog_result && $catalog_result->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Preview</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Judul</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Kategori</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Deskripsi</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Tanggal</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #334155; border-bottom: 2px solid #e2e8f0;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $catalog_result->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 1rem; font-weight: 600; color: #8b5cf6;">#<?= $item['id'] ?></td>
                                    <td style="padding: 1rem;">
                                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                             alt="<?= htmlspecialchars($item['title']) ?>"
                                             style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    </td>
                                    <td style="padding: 1rem; font-weight: 600; color: #334155;"><?= htmlspecialchars($item['title']) ?></td>
                                    <td style="padding: 1rem;">
                                        <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                            <?= htmlspecialchars($item['category']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; color: #64748b; max-width: 300px;">
                                        <?= htmlspecialchars(substr($item['description'], 0, 80)) ?><?= strlen($item['description']) > 80 ? '...' : '' ?>
                                    </td>
                                    <td style="padding: 1rem; color: #475569; font-size: 0.9rem;">
                                        <?= date('d/m/Y', strtotime($item['created_at'])) ?>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="manage_catalog.php?edit=1&id=<?= $item['id'] ?>" 
                                               style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                                                ✏️ Edit
                                            </a>
                                            <a href="manage_catalog.php?action=delete&id=<?= $item['id'] ?>" 
                                               style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;"
                                               onclick="return confirm('Yakin ingin menghapus item ini?')">
                                                🗑️ Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem 2rem; color: #64748b;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h4 style="color: #475569; margin-bottom: 0.5rem;">Belum Ada Item Katalog</h4>
                    <p>Tambahkan item katalog pertama Anda menggunakan form di atas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tips -->
    <div class="alert alert-info" style="margin-top: 2rem;">
        <strong>💡 Tips:</strong>
        <ul style="margin: 0.5rem 0 0 1.5rem;">
            <li>Gunakan URL gambar dengan resolusi minimal 400x300px untuk hasil terbaik</li>
            <li>Bisa gunakan layanan seperti Imgur, Cloudinary, atau placeholder seperti https://via.placeholder.com/</li>
            <li>Pastikan URL gambar bisa diakses publik</li>
        </ul>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
