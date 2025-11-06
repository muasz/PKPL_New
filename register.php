<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password dan simpan user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
        $stmt->close();
    }
}
?>

<div class="container">
    <div class="form-container">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="Images/Logo.jfif" alt="PierceFlow Logo" 
                 style="height: 60px; width: auto; border-radius: 10px; box-shadow: 0 4px 20px rgba(139, 92, 246, 0.2);">
        </div>
        <h2 class="page-title">Registrasi</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <p style="text-align: center; margin-top: 1rem;">
                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
            </p>
        <?php else: ?>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" required 
                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Nomor HP</label>
                <input type="tel" id="phone" name="phone" required 
                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       minlength="6">
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem;">
            Sudah punya akun? <a href="login.php" style="color: var(--primary-color);">Login di sini</a>
        </p>
        
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
