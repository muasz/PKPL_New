<?php
require_once 'includes/header.php';

// Filter berdasarkan kategori
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';
$filter_condition = '';
if ($filter_category != 'all') {
    $filter_condition = " WHERE category = '" . $conn->real_escape_string($filter_category) . "'";
}

// Ambil semua catalog items
$catalog_query = "SELECT * FROM catalog $filter_condition ORDER BY created_at DESC";
$catalog_result = $conn->query($catalog_query);

// Hitung total semua items
$total_count_query = $conn->query("SELECT COUNT(*) as total FROM catalog");
$total_count = $total_count_query->fetch_assoc()['total'];

// Hitung jumlah per kategori
$categories = ['telinga', 'hidung', 'industrial', 'helix', 'tragus', 'septum', 'lainnya'];
$category_counts = [];
foreach ($categories as $cat) {
    $count_query = $conn->query("SELECT COUNT(*) as total FROM catalog WHERE category = '$cat'");
    $category_counts[$cat] = $count_query->fetch_assoc()['total'];
}
?>

<div class="container">
    <!-- Hero Section -->
    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 20px; padding: 3rem 2rem; margin-bottom: 3rem; color: white; text-align: center;">
        <h1 style="margin: 0 0 1rem 0; font-size: 2.5rem; font-weight: 700;">📸 Galeri Katalog</h1>
        <p style="margin: 0; font-size: 1.1rem; opacity: 0.95;">Portfolio hasil tindik profesional kami</p>
    </div>
    
    <!-- Filter Categories -->
    <div style="background: white; border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <span style="font-weight: 600; color: #334155;">Filter Kategori:</span>
            
            <a href="catalog.php?category=all" 
               style="text-decoration: none; padding: 0.6rem 1.2rem; border-radius: 25px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s ease;
                      <?= $filter_category == 'all' ? 'background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white;' : 'background: #f1f5f9; color: #64748b;' ?>">
                Semua (<?= $total_count ?>)
            </a>
            
            <?php
            $category_labels = [
                'telinga' => '👂 Telinga',
                'hidung' => '👃 Hidung',
                'industrial' => '🔗 Industrial',
                'helix' => '🌀 Helix',
                'tragus' => '⭐ Tragus',
                'septum' => '💍 Septum',
                'lainnya' => '✨ Lainnya'
            ];
            
            foreach ($category_labels as $cat => $label):
                if ($category_counts[$cat] > 0):
            ?>
                <a href="catalog.php?category=<?= $cat ?>" 
                   style="text-decoration: none; padding: 0.6rem 1.2rem; border-radius: 25px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s ease;
                          <?= $filter_category == $cat ? 'background: linear-gradient(135deg, #10b981, #059669); color: white;' : 'background: #f1f5f9; color: #64748b;' ?>">
                    <?= $label ?> (<?= $category_counts[$cat] ?>)
                </a>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
    
    <!-- Catalog Grid -->
    <?php if ($catalog_result && $catalog_result->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <?php while ($item = $catalog_result->fetch_assoc()): ?>
                <div class="catalog-card" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                    <!-- Image -->
                    <div style="position: relative; height: 250px; overflow: hidden; background: linear-gradient(135deg, #f1f5f9, #e2e8f0);">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                             onmouseover="this.style.transform='scale(1.1)'"
                             onmouseout="this.style.transform='scale(1)'">
                        
                        <!-- Category Badge -->
                        <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(139, 92, 246, 0.95); color: white; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; backdrop-filter: blur(10px);">
                            <?= $category_labels[$item['category']] ?? $item['category'] ?>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 0.8rem 0; color: #1e293b; font-size: 1.3rem; font-weight: 700;">
                            <?= htmlspecialchars($item['title']) ?>
                        </h3>
                        <p style="margin: 0; color: #64748b; line-height: 1.6; font-size: 0.95rem;">
                            <?= htmlspecialchars($item['description']) ?>
                        </p>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.85rem; color: #94a3b8;">
                                📅 <?= date('d M Y', strtotime($item['created_at'])) ?>
                            </span>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="booking.php" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                                    📅 Book Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div style="font-size: 5rem; margin-bottom: 1rem;">📭</div>
            <h3 style="color: #475569; margin-bottom: 0.5rem;">Belum Ada Katalog</h3>
            <p style="color: #64748b;">Tidak ada item katalog untuk kategori ini</p>
        </div>
    <?php endif; ?>
    
    <style>
    .catalog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(139, 92, 246, 0.2) !important;
    }
    </style>
</div>

<?php require_once 'includes/footer.php'; ?>
