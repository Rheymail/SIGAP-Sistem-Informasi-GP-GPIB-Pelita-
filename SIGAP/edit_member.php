<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';

if ($id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Get member data
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result || $result->num_rows == 0) {
        $stmt->close();
        header("Location: dashboard.php");
        exit();
    }
    $member = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    requireLogin();

    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $telepon = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $status = trim($_POST['status']);
    $sektor = isset($_POST['sektor']) && $_POST['sektor'] !== '' ? intval($_POST['sektor']) : null;
    
    // --- VALIDASI DATA ---
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    
    $telepon_cleaned = preg_replace('/[^0-9]/', '', $telepon);
    if (!empty($telepon) && !preg_match('/^0[0-9]{8,12}$/', $telepon_cleaned)) {
         $errors[] = "Format telepon tidak valid. Harus 9-13 digit angka diawali 0 (misal: 08123456789).";
    }
    // --- AKHIR VALIDASI ---

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Lanjutkan jika tidak ada error
        $colCheck = $conn->query("SHOW COLUMNS FROM members LIKE 'sektor'");
        if ($colCheck && $colCheck->num_rows == 0) {
            $conn->query("ALTER TABLE members ADD COLUMN sektor TINYINT(2) NULL AFTER pekerjaan");
        }

        $stmt = $conn->prepare("UPDATE members SET nama = ?, email = ?, telepon = ?, alamat = ?, tanggal_lahir = ?, status = ?, sektor = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('ssssssii', $nama, $email, $telepon_cleaned, $alamat, $tanggal_lahir, $status, $sektor, $id);
            if ($stmt->execute()) {
                $success = 'Data anggota berhasil diupdate!';
                
                createNotification($conn, 'member_updated', 'Data Anggota Diperbarui', 
                                 "Data anggota '{$nama}' telah diperbarui.", $id);
                
                logActivity($conn, 'Update Member', 'members', $id, 
                           "Data anggota diperbarui: {$nama} ({$email})");
                
                $stmt->close();
                
                // PERUBAHAN DI SINI (1 dari 2):
                // Arahkan kembali ke anggota.php setelah sukses
                header("refresh:2;url=anggota.php");
                exit();
            } else {
                if ($conn->errno == 1062) {
                    $error = 'Gagal mengupdate data: Email ini sudah terdaftar.';
                } else {
                    $error = 'Gagal mengupdate data: ' . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $error = 'Gagal mengupdate data: ' . $conn->error;
        }
    }

    // Jika terjadi error, isi data $member tetap dari data POST agar form tidak ter-reset
    $member['nama'] = $nama;
    $member['email'] = $email;
    $member['telepon'] = $telepon;
    $member['alamat'] = $alamat;
    $member['tanggal_lahir'] = $tanggal_lahir;
    $member['status'] = $status;
    $member['sektor'] = $sektor;
}

$pageTitle = 'Edit Anggota - SIGAP';
include 'includes/header.php';
?>
    
<div class="container">
    <div class="form-container">
        <h2>Edit Data Anggota</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($member['nama']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telepon">Telepon</label>
                <input type="tel" id="telepon" name="telepon" value="<?php echo htmlspecialchars($member['telepon']); ?>" placeholder="Contoh: 08123456789">
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($member['alamat']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir *</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                       value="<?php echo $member['tanggal_lahir']; ?>" required>
            </div>

            <div class="form-group">
                <label for="sektor">Sektor</label>
                <select id="sektor" name="sektor">
                    <option value="">-- Pilih Sektor --</option>
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $sel = (isset($member['sektor']) && $member['sektor'] == $i) ? 'selected' : '';
                        echo "<option value=\"$i\" $sel>$i</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="Aktif" <?php echo $member['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Tidak Aktif" <?php echo $member['status'] == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="anggota.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>