<?php
require_once 'config.php';
requireLogin();

// =====================================================================
// PERUBAHAN: BAGIAN LOGIKA AJAX (DIJADIKAN SATU FILE)
// =====================================================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    // Ini adalah panggilan AJAX, atur header ke JSON dan hentikan HTML
    header('Content-Type: application/json');
    
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $response = ['success' => false, 'message' => 'Aksi tidak valid'];

    if ($id > 0) {
        $newStatus = '';
        $logAction = '';

        if ($action == 'approve') {
            $newStatus = 'Approved';
            $logAction = 'Approve Atestasi';
        } elseif ($action == 'reject') {
            $newStatus = 'Rejected';
            $logAction = 'Reject Atestasi';
        }

        if (!empty($newStatus)) {
            $stmt = $conn->prepare("UPDATE atestasi SET status = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('si', $newStatus, $id);
                if ($stmt->execute()) {
                    logActivity($conn, $logAction, 'atestasi', $id, "Status diubah menjadi $newStatus");
                    $response = [
                        'success' => true,
                        'message' => "Status atestasi berhasil diubah menjadi $newStatus!",
                        'newStatus' => $newStatus
                    ];
                } else {
                    $response['message'] = 'Gagal mengupdate status: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Gagal mempersiapkan statement: ' . $conn->error;
            }
        }
    } else {
        $response['message'] = 'ID atestasi tidak valid.';
    }

    // Kirim respons JSON dan hentikan eksekusi script
    echo json_encode($response);
    exit();
}
// =====================================================================
// AKHIR DARI LOGIKA AJAX
// =====================================================================


// Logika normal untuk menampilkan halaman (GET Request biasa)

// Cek jika ada pesan sukses dari sesi (untuk add/edit)
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Get all atestasi
$query = "SELECT a.*, m.nama as member_name 
		  FROM atestasi a 
		  LEFT JOIN members m ON a.member_id = m.id 
		  ORDER BY a.created_at DESC";
$atestasi_result = $conn->query($query);

$pageTitle = 'Atestasi - SIGAP';
include 'includes/header.php';
?>

<div class="container">
	<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center;">
		<h2>Manajemen Atestasi (Perpindahan Jemaat)</h2>
		<a href="add_atestasi.php" class="btn btn-primary">+ Tambah Atestasi</a>
	</div>

    <?php if ($success_message): ?>
        <div class="alert alert-success" id="session-alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

	<div class="table-container fade-in">
		<table class="data-table">
			<thead>
				<tr>
					<th>ID</th>
					<th>Anggota</th>
					<th>Gereja Asal</th>
					<th>Gereja Tujuan</th>
					<th>Tanggal Keluar</th>
					<th>Tanggal Masuk</th>
					<th>Status</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($atestasi_result && $atestasi_result->num_rows > 0): ?>
					<?php while ($row = $atestasi_result->fetch_assoc()): ?>
						<tr>
							<td><?php echo $row['id']; ?></td>
							<td>
								<a href="member_detail.php?id=<?php echo $row['member_id']; ?>" style="color: #667eea;">
									<?php echo h($row['member_name']); ?>
								</a>
							</td>
							<td><?php echo h($row['gereja_asal'] ?: '-'); ?></td>
							<td><?php echo h($row['gereja_tujuan'] ?: '-'); ?></td>
							<td><?php echo formatDate($row['tanggal_keluar']); ?></td>
							<td><?php echo formatDate($row['tanggal_masuk']); ?></td>
							
							<!-- Sel Status dengan ID unik -->
							<td id="status-<?php echo $row['id']; ?>">
								<span class="badge badge-<?php 
									echo $row['status'] == 'Approved' ? 'success' : 
										($row['status'] == 'Rejected' ? 'danger' : 'warning'); 
								?>">
									<?php echo $row['status']; ?>
								</span>
							</td>
							
							<!-- Sel Aksi dengan ID unik dan tombol dengan kelas -->
							<td class="action-buttons" id="actions-<?php echo $row['id']; ?>">
								<a href="edit_atestasi.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
								<?php if ($row['status'] == 'Pending'): ?>
									<button class="btn btn-sm btn-success btn-approve" data-id="<?php echo $row['id']; ?>">Setujui</button>
									<button class="btn btn-sm btn-danger btn-reject" data-id="<?php echo $row['id']; ?>">Tolak</button>
								<?php endif; ?>
							</td>
						</tr>
					<?php endwhile; ?>
				<?php else: ?>
					<tr>
						<td colspan="8" class="text-center">
							<div class="empty-state">
								<div class="empty-state-icon">🔄</div>
								<h3>Belum ada data atestasi</h3>
								<p>Mulai dengan menambahkan atestasi baru.</p>
								<a href="add_atestasi.php" class="btn btn-primary">+ Tambah Atestasi</a>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Fungsi untuk menangani update status
    function handleStatusUpdate(event) {
        // Cek apakah yang diklik adalah tombol approve atau reject
        if (event.target.classList.contains('btn-approve') || event.target.classList.contains('btn-reject')) {
            event.preventDefault(); // Mencegah aksi default
            
            const button = event.target;
            const id = button.dataset.id;
            const isApprove = button.classList.contains('btn-approve');
            
            // PERUBAHAN: Tentukan 'action' dan 'url'
            const action = isApprove ? 'approve' : 'reject';
            // URL sekarang memanggil file ini sendiri (atestasi.php) dengan parameter
            const url = `atestasi.php?action=${action}&id=${id}`;
            const actionText = isApprove ? 'menyetujui' : 'menolak';

            if (confirm(`Apakah Anda yakin ingin ${actionText} atestasi ini?`)) {
                // Tampilkan loading di tombol
                button.disabled = true;
                button.innerHTML = '<span class="spinner"></span>';

                fetch(url) // Panggil URL yang sudah ditentukan
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 1. Tampilkan notifikasi Toast
                            if (window.showToast) {
                                window.showToast(data.message, 'success');
                            }

                            // 2. Perbarui sel Status
                            const statusCell = document.getElementById(`status-${id}`);
                            if (statusCell) {
                                const newStatus = data.newStatus;
                                const badgeClass = newStatus === 'Approved' ? 'success' : 'danger';
                                statusCell.innerHTML = `<span class="badge badge-${badgeClass}">${newStatus}</span>`;
                            }

                            // 3. Perbarui sel Aksi (hapus tombol)
                            const actionCell = document.getElementById(`actions-${id}`);
                            if (actionCell) {
                                // Hanya sisakan tombol Edit
                                const editButton = actionCell.querySelector('.btn-warning');
                                actionCell.innerHTML = editButton ? editButton.outerHTML : 'Selesai';
                            }
                        } else {
                            // Tampilkan error
                            if (window.showToast) {
                                window.showToast(data.message, 'error');
                            }
                            button.disabled = false;
                            button.innerHTML = isApprove ? 'Setujui' : 'Tolak';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (window.showToast) {
                            window.showToast('Terjadi kesalahan jaringan.', 'error');
                        }
                        button.disabled = false;
                        button.innerHTML = isApprove ? 'Setujui' : 'Tolak';
                    });
            }
        }
    }

    // Tambahkan event listener ke seluruh body (event delegation)
    document.body.addEventListener('click', handleStatusUpdate);

    // Hilangkan session alert (jika ada) setelah 5 detik
    const sessionAlert = document.getElementById('session-alert');
    if (sessionAlert) {
        setTimeout(() => {
            sessionAlert.style.transition = 'opacity 0.5s ease';
            sessionAlert.style.opacity = '0';
            setTimeout(() => sessionAlert.remove(), 500);
        }, 5000);
    }
});
</script>

<?php include 'includes/footer.php'; ?>