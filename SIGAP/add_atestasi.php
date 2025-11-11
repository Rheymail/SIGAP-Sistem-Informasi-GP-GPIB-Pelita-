<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = intval($_POST['member_id']);
    $gereja_asal = mysqli_real_escape_string($conn, $_POST['gereja_asal']);
    $gereja_tujuan = mysqli_real_escape_string($conn, $_POST['gereja_tujuan']);
    $tanggal_keluar = $_POST['tanggal_keluar'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    $stmt = $conn->prepare("INSERT INTO atestasi (member_id, gereja_asal, gereja_tujuan, tanggal_keluar, tanggal_masuk, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $member_id, $gereja_asal, $gereja_tujuan, $tanggal_keluar, $tanggal_masuk, $keterangan);
    
    if ($stmt->execute()) {
        $success = 'Atestasi berhasil ditambahkan!';
        logActivity($conn, 'Create Atestasi', 'atestasi', $conn->insert_id, "Atestasi baru dibuat untuk member ID: $member_id");
        header("refresh:2;url=atestasi.php");
    } else {
        $error = 'Gagal menambahkan atestasi: ' . $conn->error;
    }
    $stmt->close();
}

// Get all members for dropdown
$members_query = "SELECT id, nama FROM members ORDER BY nama";
$members_result = $conn->query($members_query);

$pageTitle = 'Tambah Atestasi - SIGAP';
include 'includes/header.php';
?>

<div class="container">
    <div class="form-container fade-in">
        <h2>Tambah Atestasi Baru</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" data-loading>
            <div class="form-group">
                <label for="member_id">Anggota *</label>
                <select id="member_id" name="member_id" required>
                    <option value="">Pilih Anggota</option>
                    <?php while ($member = $members_result->fetch_assoc()): ?>
                        <option value="<?php echo $member['id']; ?>"><?php echo h($member['nama']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="gereja_asal">Gereja Asal</label>
                <input type="text" id="gereja_asal" name="gereja_asal">
            </div>
            
            <div class="form-group">
                <label for="gereja_tujuan">Gereja Tujuan</label>
                <input type="text" id="gereja_tujuan" name="gereja_tujuan">
            </div>
            
            <div class="form-group">
                <label for="tanggal_keluar">Tanggal Keluar</label>
                <input type="date" id="tanggal_keluar" name="tanggal_keluar">
            </div>
            
            <div class="form-group">
                <label for="tanggal_masuk">Tanggal Masuk</label>
                <input type="date" id="tanggal_masuk" name="tanggal_masuk">
            </div>
            
            <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="atestasi.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

