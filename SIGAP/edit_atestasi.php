<?php
require_once 'config.php';
requireLogin();

$success = '';
$error = '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Jika ID tidak valid, kembalikan ke halaman atestasi
if ($id <= 0) {
    header('Location: atestasi.php');
    exit;
}

// Logika untuk mengambil data (GET) dan mengisi form
$stmt = $conn->prepare("SELECT a.*, m.nama as member_name 
                        FROM atestasi a 
                        LEFT JOIN members m ON a.member_id = m.id 
                        WHERE a.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$atestasi = $result->fetch_assoc();
$stmt->close();

if (!$atestasi) {
    // Jika data atestasi dengan ID itu tidak ada
    $_SESSION['error_message'] = "Data atestasi tidak ditemukan.";
    header('Location: atestasi.php');
    exit;
}

// Logika untuk UPDATE data saat form disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gereja_asal = trim($_POST['gereja_asal']);
    $gereja_tujuan = trim($_POST['gereja_tujuan']);
    $tanggal_keluar = !empty($_POST['tanggal_keluar']) ? $_POST['tanggal_keluar'] : null;
    $tanggal_masuk = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : null;
    $status = trim($_POST['status']);
    $keterangan = trim($_POST['keterangan']);
    
    $stmt = $conn->prepare("UPDATE atestasi SET gereja_asal = ?, gereja_tujuan = ?, tanggal_keluar = ?, tanggal_masuk = ?, status = ?, keterangan = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $gereja_asal, $gereja_tujuan, $tanggal_keluar, $tanggal_masuk, $status, $keterangan, $id);
    
    if ($stmt->execute()) {
        $success = 'Data atestasi berhasil diupdate!';
        logActivity($conn, 'Update Atestasi', 'atestasi', $id, "Atestasi diupdate untuk member ID: {$atestasi['member_id']}, Status: $status");
        header("refresh:2;url=atestasi.php");
    } else {
        $error = 'Gagal mengupdate atestasi: ' . $stmt->error;
    }
    $stmt->close();
}

$pageTitle = 'Edit Atestasi - SIGAP';

// PERUBAHAN: Memanggil header utama
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container fade-in">
        <h2>Edit Data Atestasi</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" data-loading>
            <div class="form-group">
                <label for="member_name">Anggota</label>
                <input type="text" id="member_name" name="member_name" 
                       value="<?php echo h($atestasi['member_name']); ?>" disabled 
                       style="background: rgba(31, 41, 55, 0.5); color: #94a3b8; cursor: not-allowed;">
            </div>
            
            <div class="form-group">
                <label for="gereja_asal">Gereja Asal</label>
                <input type="text" id="gereja_asal" name="gereja_asal" value="<?php echo h($atestasi['gereja_asal']); ?>">
            </div>
            
            <div class="form-group">
                <label for="gereja_tujuan">Gereja Tujuan</label>
                <input type="text" id="gereja_tujuan" name="gereja_tujuan" value="<?php echo h($atestasi['gereja_tujuan']); ?>">
            </div>
            
            <div class="form-group">
                <label for="tanggal_keluar">Tanggal Keluar</label>
                <input type="date" id="tanggal_keluar" name="tanggal_keluar" value="<?php echo h($atestasi['tanggal_keluar']); ?>">
            </div>
            
            <div class="form-group">
                <label for="tanggal_masuk">Tanggal Masuk</label>
                <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?php echo h($atestasi['tanggal_masuk']); ?>">
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="Pending" <?php echo ($atestasi['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo ($atestasi['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo ($atestasi['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3"><?php echo h($atestasi['keterangan']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Data</button>
                <a href="atestasi.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php 
// PERUBAHAN: Memanggil footer utama
include 'includes/footer.php'; 
?>
```eof