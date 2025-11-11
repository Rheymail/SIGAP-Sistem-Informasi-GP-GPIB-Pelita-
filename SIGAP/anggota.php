<?php
require_once 'config.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sektor_filter = isset($_GET['sektor']) ? $_GET['sektor'] : '';
$bulan_lahir_filter = isset($_GET['bulan_lahir']) ? $_GET['bulan_lahir'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;

// Build where clause
$where = [];
$params = [];
$types = '';

if ($search) {
	$where[] = "(nama LIKE ? OR email LIKE ? OR telepon LIKE ? OR alamat LIKE ?)";
	$searchPattern = "%$search%";
	$params = array_merge($params, [$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
	$types .= 'ssss';
}

if ($status_filter) {
	$where[] = "status = ?";
	$params[] = $status_filter;
	$types .= 's';
}

if ($sektor_filter) {
	$where[] = "sektor = ?";
	$params[] = $sektor_filter;
	$types .= 'i'; // Sektor adalah integer
}

if ($bulan_lahir_filter) {
    $colCheck = $conn->query("SHOW COLUMNS FROM members LIKE 'tanggal_lahir'");
    if ($colCheck && $colCheck->num_rows > 0) {
		$where[] = "MONTH(tanggal_lahir) = ?";
		$params[] = $bulan_lahir_filter;
		$types .= 'i'; // Bulan adalah integer
	}
}


$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get pagination info
$countQuery = "SELECT COUNT(*) as total FROM members $whereClause";
$countStmt = $conn->prepare($countQuery);
if ($types) {
	$countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Get members
$query = "SELECT * FROM members $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($types) {
	$stmt->bind_param($types, ...$params);
}
$stmt->execute();
$members_result = $stmt->get_result();

$pageTitle = 'Anggota - SIGAP';
include 'includes/header.php';
?>

<div class="container">
	<div class="dashboard-header">
		<h2>Kelola Data Anggota</h2>
	</div>

	<form method="get" action="anggota.php" id="filter-form">
		<div class="filter-bar">
			<div class="filter-group" style="flex: 2;">
				<label>Pencarian</label>
				<input type="text" name="search" placeholder="Cari nama, email, telepon, atau alamat..." 
					value="<?php echo h($search); ?>" id="search-input">
					</div>
			<div class="filter-group">
				<label>Filter</label>
				<select name="status" onchange="this.form.submit()">
					<option value="">Semua Status</option>
					<option value="Aktif" <?php echo $status_filter == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
					<option value="Tidak Aktif" <?php echo $status_filter == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
				</select>
			</div>

			<div class="filter-group">
				<label>Sektor</label>
				<select name="sektor" onchange="this.form.submit()">
					<option value="">Semua Sektor</option>
					<?php for ($i = 1; $i <= 12; $i++): ?>
						<option value="<?php echo $i; ?>" <?php echo $sektor_filter == $i ? 'selected' : ''; ?>>
							Sektor <?php echo $i; ?>
						</option>
					<?php endfor; ?>
				</select>
			</div>

			<div class="filter-group">
				<label>Bulan Lahir</label>
				<select name="bulan_lahir" onchange="this.form.submit()">
					<option value="">Semua Bulan</option>
					<?php
					$months = [
						1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
						5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
						9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
					];
					foreach ($months as $num => $name):
					?>
						<option value="<?php echo $num; ?>" <?php echo $bulan_lahir_filter == $num ? 'selected' : ''; ?>>
							<?php echo $name; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="filter-group">
				<label>Per Halaman</label>
				<select name="per_page" onchange="this.form.submit()">
					<option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
					<option value="25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25</option>
					<option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
					<option value="100" <?php echo $perPage == 100 ? 'selected' : ''; ?>>100</option>
				</select>
			</div>
		</div>
	</form>
	<div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
		<a href="add_member.php" class="btn btn-primary">+ Tambah Anggota</a>
		<button type="button" class="btn btn-primary" onclick="openImportModal()">📥 Import Excel</button>
	</div>

	<div class="bulk-actions" id="bulk-actions">
		<div class="bulk-actions-info">
			<span class="selected-count">0</span> item dipilih
		</div>
		<div class="bulk-actions-buttons">
			<button type="button" class="btn btn-sm btn-warning" onclick="bulkUpdateStatus('Aktif')">Set Aktif</button>
			<button type="button" class="btn btn-sm btn-warning" onclick="bulkUpdateStatus('Tidak Aktif')">Set Tidak Aktif</button>
			<button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">Hapus</button>
		</div>
	</div>

	<div class="table-container fade-in">
		<table class="data-table">
			<thead>
				<tr>
					<th style="width: 40px;">
						<input type="checkbox" id="select-all" class="table-checkbox" title="Pilih Semua">
					</th>
					<th>ID</th>
					<th>Nama</th>
					<th>Email</th>
					<th>Telepon</th>
					<th>Alamat</th>
					<th>Tanggal Lahir</th>
					<th>Status</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($members_result && $members_result->num_rows > 0): ?>
					<?php while ($row = $members_result->fetch_assoc()): ?>
						<tr data-member-id="<?php echo $row['id']; ?>">
							<td>
								<input type="checkbox" name="member_ids[]" value="<?php echo $row['id']; ?>" class="table-checkbox">
							</td>
							<td><?php echo $row['id']; ?></td>
							<td><?php echo h($row['nama']); ?></td>
							<td><?php echo h($row['email']); ?></td>
							<td><?php echo h($row['telepon']); ?></td>
							<td><?php echo h(substr($row['alamat'], 0, 50)) . (strlen($row['alamat']) > 50 ? '...' : ''); ?></td>
							<td><?php echo $row['tanggal_lahir'] ? formatDate($row['tanggal_lahir']) : '-'; ?></td>
							<td>
								<span class="badge badge-<?php echo $row['status'] == 'Aktif' ? 'success' : 'danger'; ?>">
									<?php echo $row['status']; ?>
								</span>
							</td>
							<td class="action-buttons">
								<a href="member_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" data-tooltip="Lihat Detail">👁</a>
								<a href="edit_member.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-tooltip="Edit">✏️</a>
								<a href="delete_member.php?id=<?php echo $row['id']; ?>" 
									class="btn btn-sm btn-danger" 
									data-tooltip="Hapus"
									onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">🗑️</a>
							</td>
						</tr>
					<?php endwhile; ?>
				<?php else: ?>
					<tr>
						<td colspan="9" class="text-center">
							<div class="empty-state">
								<div class="empty-state-icon">👥</div>
								<h3><?php echo ($search || $status_filter || $sektor_filter || $bulan_lahir_filter) ? 'Tidak ada hasil filter' : 'Tidak ada data anggota'; ?></h3>
								<p><?php echo ($search || $status_filter || $sektor_filter || $bulan_lahir_filter) ? 'Coba ganti atau hapus filter Anda.' : 'Silakan tambah anggota baru.'; ?></p>
								<?php if (!($search || $status_filter || $sektor_filter || $bulan_lahir_filter)): ?>
									<a href="add_member.php" class="btn btn-primary">+ Tambah Anggota</a>
								<?php endif; ?>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php if ($totalPages > 1): ?>
		<div class="pagination">
			<?php if ($page > 1): ?>
				<a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">« Prev</a>
			<?php else: ?>
				<span class="disabled">« Prev</span>
			<?php endif; ?>
			
			<?php
			$start = max(1, $page - 2);
			$end = min($totalPages, $page + 2);
			
			if ($start > 1) {
				echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '">1</a>';
				if ($start > 2) echo '<span>...</span>';
			}
			
			for ($i = $start; $i <= $end; $i++):
			?>
				<?php if ($i == $page): ?>
					<span class="active"><?php echo $i; ?></span>
				<?php else: ?>
					<a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
				<?php endif; ?>
			<?php endfor; ?>
			
			<?php
			if ($end < $totalPages) {
				if ($end < $totalPages - 1) echo '<span>...</span>';
				echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $totalPages])) . '">' . $totalPages . '</a>';
			}
			?>
			
			<?php if ($page < $totalPages): ?>
				<a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next »</a>
			<?php else: ?>
				<span class="disabled">Next »</span>
			<?php endif; ?>
		</div>
		<div style="text-align: center; margin-top: 1rem; color: #94a3b8;">
			Menampilkan <?php echo $offset + 1; ?> - <?php echo min($offset + $perPage, $total); ?> dari <?php echo $total; ?> anggota
		</div>
	<?php endif; ?>
</div>

<div class="modal" id="importModal">
	<div class="modal-content">
		<button class="modal-close" onclick="closeImportModal()">×</button>
		<h3>Import Data Anggota dari Excel</h3>
		<form method="POST" action="import_anggota.php" enctype="multipart/form-data" id="importForm" onsubmit="handleImportSubmit(event)">
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
				<button type="submit" class="btn btn-primary" id="importSubmitBtn">Import</button>
				<button type="button" class="btn btn-secondary" onclick="closeImportModal()">Batal</button>
			</div>
		</form>
	</div>
</div>

<script>
function openImportModal() {
	document.getElementById('importModal').classList.add('active');
}

function closeImportModal() {
	document.getElementById('importModal').classList.remove('active');
	document.getElementById('importForm').reset();
	const submitBtn = document.getElementById('importSubmitBtn');
	submitBtn.disabled = false;
	submitBtn.innerHTML = 'Import';
}

function handleImportSubmit(e) {
	const fileInput = document.getElementById('excel_file');
	const submitBtn = document.getElementById('importSubmitBtn');
	
	if (!fileInput.files || fileInput.files.length === 0) {
		e.preventDefault();
		showToast('Silakan pilih file Excel terlebih dahulu', 'error');
		return false;
	}
	
	// Show loading state
	submitBtn.disabled = true;
	submitBtn.innerHTML = '<span class="spinner"></span> Memproses...';
	
	// Form will submit normally
	return true;
}

// Close modal when clicking outside
document.getElementById('importModal').addEventListener('click', function(e) {
	if (e.target === this) {
		closeImportModal();
	}
});
</script>

<script>
function bulkUpdateStatus(status) {
	const checked = document.querySelectorAll('input[type="checkbox"][name="member_ids[]"]:checked');
	if (checked.length === 0) {
		showToast('Pilih minimal satu anggota', 'error');
		return;
	}
	
	const ids = Array.from(checked).map(cb => cb.value);
	if (confirm(`Apakah Anda yakin ingin mengubah status ${ids.length} anggota menjadi ${status}?`)) {
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = 'bulk_update_status.php';
		
		ids.forEach(id => {
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'member_ids[]';
			input.value = id;
			form.appendChild(input);
		});
		
		const statusInput = document.createElement('input');
		statusInput.type = 'hidden';
		statusInput.name = 'status';
		statusInput.value = status;
		form.appendChild(statusInput);
		
		document.body.appendChild(form);
		form.submit();
	}
}

function bulkDelete() {
	const checked = document.querySelectorAll('input[type="checkbox"][name="member_ids[]"]:checked');
	if (checked.length === 0) {
		showToast('Pilih minimal satu anggota', 'error');
		return;
	}
	
	const ids = Array.from(checked).map(cb => cb.value);
	if (confirm(`Apakah Anda yakin ingin menghapus ${ids.length} anggota?`)) {
		const form = document.createElement('form');
		form.method = 'POST';
		form.action = 'bulk_delete.php';
		
		ids.forEach(id => {
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'member_ids[]';
			input.value = id;
			form.appendChild(input);
		});
		
		document.body.appendChild(form);
		form.submit();
	}
}

/* PERBAIKAN:
JavaScript untuk 'search-input' dihapus.
Menekan 'Enter' di dalam input pencarian sekarang akan 
secara otomatis men-submit form 'filter-form' (default behavior HTML).
*/
</script>

<?php include 'includes/footer.php'; ?>