<?php
require_once 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    requireLogin();

    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $status = trim($_POST['status']);

    // --- PERBAIKAN: BLOK VALIDASI BARU ---
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    
    // Validasi telepon: harus angka, 9-13 digit, diawali 0 (setelah dibersihkan)
    $telepon_cleaned = preg_replace('/[^0-9]/', '', $telepon);
    if (!empty($telepon) && !preg_match('/^0[0-9]{8,12}$/', $telepon_cleaned)) {
         $errors[] = "Format telepon tidak valid. Harus 9-13 digit angka diawali 0 (misal: 08123456789).";
    }
    // --- AKHIR BLOK VALIDASI ---

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Lanjutkan jika tidak ada error
        $stmt = $conn->prepare("INSERT INTO members (nama, email, telepon, alamat, tanggal_lahir, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            // Gunakan $telepon_cleaned agar data yang disimpan bersih
            $stmt->bind_param('ssssss', $nama, $email, $telepon_cleaned, $alamat, $tanggal_lahir, $status);
            if ($stmt->execute()) {
                $success = 'Data anggota berhasil ditambahkan!';
                $newMemberId = $stmt->insert_id;
                
                createNotification($conn, 'member_added', 'Anggota Baru Ditambahkan', 
                                 "Anggota baru '{$nama}' berhasil ditambahkan ke sistem.", $newMemberId);
                
                logActivity($conn, 'Create Member', 'members', $newMemberId, 
                           "Anggota baru ditambahkan: {$nama} ({$email})");
                
                $stmt->close();
                header("refresh:2;url=dashboard.php");
                exit();
            } else {
                // Tangani error duplikat email
                if ($conn->errno == 1062) {
                    $error = 'Gagal menambahkan data: Email ini sudah terdaftar.';
                } else {
                    $error = 'Gagal menambahkan data: ' . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $error = 'Gagal menambahkan data: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tambah Anggota - SIGAP</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
	<div class="navbar" style="margin: 1.5rem auto; max-width: 1400px; padding: 0 1.5rem;">
		<div class="navbar-brand">
			<span class="navbar-brand-logo"></span>
			<h1>SIGAP</h1>
		</div>
        <a href="dashboard.php" class="nav-tab active" style="margin-left: 1rem;">Kembali ke Dashboard</a>
		<div class="navbar-spacer"></div>
	</div>
    
    <div class="container">
        <div class="form-container">
            <h2>Tambah Anggota Baru</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="telepon">Telepon</label>
                    <input type="tel" id="telepon" name="telepon" placeholder="Contoh: 08123456789">
                </div>
                
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3"></textarea>
                </div>
                
				<div class="form-group">
					<label for="tanggal_lahir">Tanggal Lahir *</label>
					<input type="date" id="tanggal_lahir" name="tanggal_lahir" 
						   required>
				</div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Tidak Aktif">Tidak Aktif</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="dashboard.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
<script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>