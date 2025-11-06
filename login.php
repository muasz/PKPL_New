<?php
require_once 'includes/header.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Login berhasil
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect berdasarkan role
                if ($user['role'] == 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Email tidak terdaftar!';
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
        <h2 class="page-title">Login</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem;">
            Belum punya akun? <a href="register.php" style="color: var(--primary-color);">Daftar di sini</a>
        </p>
        
        <div class="alert alert-info" style="margin-top: 2rem;">
            <strong>Demo Admin:</strong><br>
            Email: admin@pierceflow.local<br>
            Password: admin123
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
