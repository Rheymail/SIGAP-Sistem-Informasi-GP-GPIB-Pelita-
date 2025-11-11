<?php
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Cek pesan logout
if (isset($_GET['message']) && $_GET['message'] == 'logout_success') {
    $success = 'Anda berhasil logout. Silakan login kembali jika diperlukan.';
}
// Cek pesan registrasi
if (isset($_GET['message']) && $_GET['message'] == 'register_success') {
    $success = 'Akun berhasil dibuat! Silakan login.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $stmt->close();
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } else {
            $error = 'Username atau password salah!';
        }
        $stmt->close();
    } else {
        $error = 'Terjadi kesalahan pada server (login).';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIGAP</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="form-container" style="max-width: 450px; margin: 2rem auto;">
            
            <h1 style="text-align: center; color: #667eea; margin-bottom: 0.5rem;">SIGAP</h1>
            <h2 style="text-align: center; margin-bottom: 2rem;">Login</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
            </form>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: #94a3b8;">Belum punya akun? <a href="register.php" style="color: #667eea; text-decoration: none;">Daftar di sini</a></p>
            </div>
            
        </div>
    </div>
</body>
</html>