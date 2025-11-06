<?php
session_start();
require_once __DIR__ . '/db.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PierceFlow - Layanan Reservasi Piercing Profesional</title>
    <meta name="description" content="PierceFlow - Platform reservasi piercing profesional dengan teknisi berpengalaman dan peralatan steril. Booking mudah, aman, dan terpercaya.">
    <meta name="keywords" content="piercing, tindik, reservasi, booking, profesional, aman, steril">
    <meta property="og:title" content="PierceFlow - Layanan Reservasi Piercing Profesional">
    <meta property="og:description" content="Platform reservasi piercing profesional dengan teknisi berpengalaman dan peralatan steril.">
    <meta property="og:image" content="Images/Logo.jfif">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/jpeg" href="Images/Logo.jfif">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php" style="display: flex; align-items: center; gap: 0.5rem;">
                    <img src="Images/Logo.jfif" alt="PierceFlow Logo" style="height: 40px; width: auto; border-radius: 8px;">
                    <span style="font-weight: 700; color: #8b5cf6;">PierceFlow</span>
                </a>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Menu untuk Admin -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li><a href="admin.php" class="<?= $current_page == 'admin.php' ? 'active' : '' ?>">Dashboard</a></li>
                        <li><a href="manage_catalog.php" class="<?= $current_page == 'manage_catalog.php' ? 'active' : '' ?>">Kelola Katalog</a></li>
                        <li><a href="manage_consultations.php" class="<?= $current_page == 'manage_consultations.php' ? 'active' : '' ?>">Konsultasi</a></li>
                        <li><a href="admin_notifications.php" class="<?= $current_page == 'admin_notifications.php' ? 'active' : '' ?>">Notifikasi</a></li>
                        <li><a href="admin_config_notifications.php" class="<?= $current_page == 'admin_config_notifications.php' ? 'active' : '' ?>">Config</a></li>
                        <li><a href="manage_users.php" class="<?= $current_page == 'manage_users.php' ? 'active' : '' ?>">Kelola User</a></li>
                    
                    <!-- Menu untuk User biasa -->
                    <?php else: ?>
                        <li><a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                        <li><a href="catalog.php" class="<?= $current_page == 'catalog.php' ? 'active' : '' ?>">Katalog</a></li>
                        <li><a href="booking.php" class="<?= $current_page == 'booking.php' ? 'active' : '' ?>">Booking</a></li>
                    <?php endif; ?>
                    
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <!-- Menu untuk Guest (belum login) -->
                    <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="catalog.php" class="<?= $current_page == 'catalog.php' ? 'active' : '' ?>">Katalog</a></li>
                    <li><a href="konsultasi.php" class="<?= $current_page == 'konsultasi.php' ? 'active' : '' ?>">Konsultasi</a></li>
                    <li><a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>">Login</a></li>
                    <li><a href="register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav><main style="display: block; margin: 0; padding: 0;">
