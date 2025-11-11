<?php
require_once 'config.php';
requireLogin();

// Get current user info
$stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        header("Location: logout.php");
        exit();
    }
    $stmt->close();
}

$pageTitle = 'Profil - SIGAP';
include 'includes/header.php';
?>

<div class="container">
    <div class="member-detail-card fade-in" style="max-width: 600px; margin: 0 auto;">
        <div class="member-header">
            <div class="member-avatar" style="background: linear-gradient(135deg, #667eea, #764ba2); font-size: 2rem;">
                👤
            </div>
            <div class="member-info" style="flex: 1;">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p style="color: #94a3b8;">ID: <?php echo $user['id']; ?></p>
                <span class="badge badge-success" style="margin-top: 0.5rem;">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="change_password.php" class="btn btn-warning">🔐 Ganti Password</a>
                <a href="dashboard.php" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>
        
        <div class="member-details-grid">
            <div class="detail-item">
                <span class="detail-label">Username</span>
                <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">User ID</span>
                <span class="detail-value">#<?php echo $user['id']; ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Role</span>
                <span class="detail-value">
                    <span class="badge badge-success"><?php echo ucfirst($user['role']); ?></span>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Status Login</span>
                <span class="detail-value">
                    <span class="badge badge-success">Aktif</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="chart-container fade-in" style="max-width: 600px; margin: 2rem auto; text-align: center;">
        <h3 class="chart-title">Aksi Cepat</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <a href="change_password.php" class="btn btn-primary" style="grid-column: 1 / -1;">
                🔐 Ganti Password
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                📊 Dashboard
            </a>
            <a href="logout.php" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin logout?');">
                🚪 Logout
            </a>
        </div>
    </div>

    <!-- Info Box -->
    <div style="max-width: 600px; margin: 2rem auto; padding: 1.5rem; background: rgba(102, 126, 234, 0.1); border-radius: 12px; border-left: 4px solid #667eea;">
        <h4 style="color: #e5e7eb; margin-bottom: 1rem;">💡 Informasi Keamanan</h4>
        <ul style="color: #cbd5e1; line-height: 1.8; margin-left: 1.5rem;">
            <li>Selalu gunakan password yang kuat dan unik</li>
            <li>Jangan bagikan password Anda dengan siapa pun</li>
            <li>Ganti password secara berkala</li>
            <li>Pastikan untuk logout ketika selesai menggunakan aplikasi</li>
            <li>Jangan akses aplikasi ini dari komputer publik tanpa logout</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
