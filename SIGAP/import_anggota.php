<?php
require_once 'config.php';
requireLogin();

$pageTitle = 'Import Anggota - SIGAP';
$success_count = 0;
$failed_count = 0;
$errors = [];
$imported = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
	$file = $_FILES['excel_file'];
	
	// Check file upload error
	if ($file['error'] !== UPLOAD_ERR_OK) {
		$errors[] = 'Error saat upload file: ' . $file['error'];
	} else {
		// Check file extension
		$file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		if (!in_array($file_ext, ['xlsx', 'xls'])) {
			$errors[] = 'Format file tidak didukung. Hanya .xlsx atau .xls yang diperbolehkan.';
		} else {
			// Check if PhpSpreadsheet is available
			$phpspreadsheet_available = false;
			if (file_exists('vendor/autoload.php')) {
				require_once 'vendor/autoload.php';
				$phpspreadsheet_available = class_exists('PhpOffice\PhpSpreadsheet\IOFactory');
			}
			
			if (!$phpspreadsheet_available) {
				$errors[] = 'PhpSpreadsheet library tidak ditemukan. Silakan install dengan: composer require phpoffice/phpspreadsheet';
			} else {
				try {
					$tmp = $file['tmp_name'];
					$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp);
					$sheet = $spreadsheet->getActiveSheet();
					$rows = $sheet->toArray();
					
					if (count($rows) < 2) {
						$errors[] = 'File Excel kosong atau hanya memiliki header.';
					} else {
						// Get header row and normalize
						$header = array_map(function($h) {
							return strtolower(trim($h));
						}, $rows[0]);
						
						// Process data rows
						for ($i = 1; $i < count($rows); $i++) {
							$row = $rows[$i];
							
							// Skip empty rows
							if (empty(array_filter($row))) {
								continue;
							}
							
							// Combine header with row data
							$data = [];
							foreach ($header as $idx => $key) {
								$data[$key] = isset($row[$idx]) ? trim($row[$idx]) : '';
							}
							
							// Extract and validate data
							$nama = isset($data['nama']) ? trim($data['nama']) : '';
							$email = isset($data['email']) ? trim($data['email']) : '';
							$telepon = isset($data['telepon']) ? trim($data['telepon']) : '';
							$alamat = isset($data['alamat']) ? trim($data['alamat']) : '';
							
							// Handle date fields
							$tanggal_bergabung = null;
							if (isset($data['tanggal bergabung']) || isset($data['tanggal_bergabung'])) {
								$date_val = isset($data['tanggal bergabung']) ? $data['tanggal bergabung'] : $data['tanggal_bergabung'];
								if ($date_val) {
									// Try to parse Excel date or standard date
									if (is_numeric($date_val)) {
										// Excel date serial number
										$tanggal_bergabung = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date_val)->format('Y-m-d');
									} else {
										$parsed = date('Y-m-d', strtotime($date_val));
										$tanggal_bergabung = $parsed !== '1970-01-01' ? $parsed : null;
									}
								}
							}
							
							$tanggal_lahir = null;
							if (isset($data['tanggal lahir']) || isset($data['tanggal_lahir'])) {
								$date_val = isset($data['tanggal lahir']) ? $data['tanggal lahir'] : $data['tanggal_lahir'];
								if ($date_val) {
									if (is_numeric($date_val)) {
										$tanggal_lahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date_val)->format('Y-m-d');
									} else {
										$parsed = date('Y-m-d', strtotime($date_val));
										$tanggal_lahir = $parsed !== '1970-01-01' ? $parsed : null;
									}
								}
							}
							
							// Handle status
							$status = 'Aktif'; // default
							if (isset($data['status']) && !empty($data['status'])) {
								$status_val = trim($data['status']);
								if (in_array($status_val, ['Aktif', 'Tidak Aktif'])) {
									$status = $status_val;
								}
							}
							
							// Handle sektor
							$sektor = null;
							if (isset($data['sektor']) && !empty($data['sektor'])) {
								$sektor_val = intval($data['sektor']);
								if ($sektor_val >= 1 && $sektor_val <= 12) {
									$sektor = $sektor_val;
								}
							}
							
							// Validate required fields
							if (empty($nama)) {
								$failed_count++;
								$errors[] = "Baris " . ($i + 1) . ": Nama kosong, data dilewati.";
								continue;
							}
							
							// --- PERBAIKAN: BLOK VALIDASI BARU ---
							if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
								$failed_count++;
								$errors[] = "Baris " . ($i + 1) . ": Format email '$email' tidak valid.";
								continue;
							}

							$telepon_cleaned = preg_replace('/[^0-9]/', '', $telepon);
							if (!empty($telepon) && !preg_match('/^0[0-9]{8,12}$/', $telepon_cleaned)) {
								$failed_count++;
								$errors[] = "Baris " . ($i + 1) . ": Format telepon '$telepon' tidak valid.";
								continue;
							}
							// --- AKHIR BLOK VALIDASI ---

							// Check if email already exists (optional validation)
							if (!empty($email)) {
								$check_stmt = $conn->prepare("SELECT id FROM members WHERE email = ?");
								$check_stmt->bind_param('s', $email);
								$check_stmt->execute();
								if ($check_stmt->get_result()->num_rows > 0) {
									$failed_count++;
									$errors[] = "Baris " . ($i + 1) . ": Email '$email' sudah terdaftar.";
									$check_stmt->close();
									continue;
								}
								$check_stmt->close();
							}
							
							// Ensure sektor column exists
							$colCheck = $conn->query("SHOW COLUMNS FROM members LIKE 'sektor'");
							if ($colCheck && $colCheck->num_rows == 0) {
								$conn->query("ALTER TABLE members ADD COLUMN sektor TINYINT(2) NULL AFTER pekerjaan");
							}
							
							// Insert into database
							if ($sektor !== null) {
								$stmt = $conn->prepare("INSERT INTO members (nama, email, telepon, alamat, tanggal_bergabung, tanggal_lahir, status, sektor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
								// Gunakan $telepon_cleaned
								$stmt->bind_param('sssssssi', $nama, $email, $telepon_cleaned, $alamat, $tanggal_bergabung, $tanggal_lahir, $status, $sektor);
							} else {
								$stmt = $conn->prepare("INSERT INTO members (nama, email, telepon, alamat, tanggal_bergabung, tanggal_lahir, status, sektor) VALUES (?, ?, ?, ?, ?, ?, ?, NULL)");
								// Gunakan $telepon_cleaned
								$stmt->bind_param('sssssss', $nama, $email, $telepon_cleaned, $alamat, $tanggal_bergabung, $tanggal_lahir, $status);
							}
							
							if ($stmt->execute()) {
								$success_count++;
								$newMemberId = $stmt->insert_id;
								
								// Create notification
								createNotification($conn, 'member_added', 'Anggota Baru Diimport', 
									"Anggota baru '{$nama}' berhasil diimport dari Excel.", $newMemberId);
								
								// Log activity
								logActivity($conn, 'Import Member', 'members', $newMemberId, 
									"Anggota diimport dari Excel: {$nama} ({$email})");
							} else {
								$failed_count++;
								$errors[] = "Baris " . ($i + 1) . ": " . $stmt->error;
							}
							$stmt->close();
						}
						
						$imported = true;
					}
				} catch (Exception $e) {
					$errors[] = 'Error membaca file Excel: ' . $e->getMessage();
				}
			}
		}
	}
}

include 'includes/header.php';
?>

<div class="container">
	<div class="dashboard-header">
		<h2>Import Data Anggota dari Excel</h2>
	</div>
	
	<?php if ($imported): ?>
		<div class="form-container">
			<div class="alert alert-success">
				<h3>Import Selesai!</h3>
				<p><strong>Berhasil:</strong> <?php echo $success_count; ?> anggota</p>
				<p><strong>Gagal:</strong> <?php echo $failed_count; ?> anggota</p>
			</div>
			
			<?php if (!empty($errors)): ?>
				<div class="alert alert-error">
					<h4>Detail Error:</h4>
					<ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
						<?php foreach ($errors as $error): ?>
							<li><?php echo h($error); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			
			<div class="form-actions">
				<a href="anggota.php" class="btn btn-primary">Kembali ke Daftar Anggota</a>
				<button type="button" class="btn btn-secondary" onclick="window.location.reload()">Import Lagi</button>
			</div>
		</div>
	<?php else: ?>
		<div class="form-container">
			<?php if (!empty($errors)): ?>
				<div class="alert alert-error">
					<h4>Error:</h4>
					<ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
						<?php foreach ($errors as $error): ?>
							<li><?php echo h($error); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			
			<form method="POST" action="" enctype="multipart/form-data">
				<div class="form-group">
					<label for="excel_file">Pilih File Excel (.xlsx atau .xls)</label>
					<input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
					<p style="margin-top: 0.5rem; font-size: 0.85rem; color: #94a3b8;">
						Format Excel harus memiliki kolom header pada baris pertama:<br>
						<strong>Nama</strong> (wajib), <strong>Email</strong>, <strong>Telepon</strong>, <strong>Alamat</strong>, 
						<strong>Tanggal Bergabung</strong>, <strong>Tanggal Lahir</strong>, <strong>Status</strong> (Aktif/Tidak Aktif), 
						<strong>Sektor</strong> (1-12)
					</p>
				</div>
				
				<div class="form-actions">
					<button type="submit" class="btn btn-primary">Import Data</button>
					<a href="anggota.php" class="btn btn-secondary">Batal</a>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>