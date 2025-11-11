<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi
    if (strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } elseif ($new_password != $confirm_password) {
        $error = 'Password baru dan konfirmasi tidak cocok!';
    } else {
        // Ambil password lama dari database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verifikasi password lama
                if (password_verify($old_password, $user['password'])) {
                    // Hash password baru
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param('si', $new_hash, $_SESSION['user_id']);
                        if ($updateStmt->execute()) {
                            $success = 'Password berhasil diubah! Silakan login kembali.';
                            $updateStmt->close();
                            // Logout setelah berhasil
                            sleep(2);
                            header("Location: logout.php");
                            exit();
                        } else {
                            $error = 'Gagal mengubah password: ' . $updateStmt->error;
                        }
                        $updateStmt->close();
                    } else {
                        $error = 'Gagal mempersiapkan update: ' . $conn->error;
                    }
                } else {
                    $error = 'Password lama tidak cocok!';
                }
            } else {
                $error = 'User tidak ditemukan!';
            }
            $stmt->close();
        } else {
            $error = 'Gagal mempersiapkan statement: ' . $conn->error;
        }
    }
}

$pageTitle = 'Ganti Password - SIGAP';
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width: 500px; margin: 2rem auto;">
        <h2>🔐 Ganti Password</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="old_password">Password Lama *</label>
                <input type="password" id="old_password" name="old_password" required>
                <small>Masukkan password Anda saat ini untuk verifikasi</small>
            </div>
            
            <div class="form-group">
                <label for="new_password">Password Baru *</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <small>Minimal 6 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru *</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                <small>Ketik ulang password baru untuk konfirmasi</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Ganti Password</button>
                <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(102, 126, 234, 0.1); border-radius: 12px; border-left: 4px solid #667eea; color: #cbd5e1;">
            <h4 style="color: #e5e7eb; margin-bottom: 1rem;">💡 Tips Keamanan:</h4>
            <ul style="margin-top: 0.5rem; margin-left: 1.5rem; line-height: 1.6;">
                <li>Gunakan password yang kuat (kombinasi huruf, angka, simbol)</li>
                <li>Jangan bagikan password Anda dengan orang lain</li>
                <li>Ganti password secara berkala</li>
                <li>Gunakan password yang berbeda untuk setiap aplikasi</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>