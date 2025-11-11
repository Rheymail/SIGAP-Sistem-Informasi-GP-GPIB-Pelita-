<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM members WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
	header("Location: anggota.php");
	exit();
}

$member = $result->fetch_assoc();

// Get activity logs for this member
$activityQuery = "SELECT * FROM activity_logs WHERE table_name = 'members' AND record_id = ? ORDER BY created_at DESC LIMIT 10";
$activityStmt = $conn->prepare($activityQuery);
$activityStmt->bind_param("i", $id);
$activityStmt->execute();
$activities = $activityStmt->get_result();

// Get initials for avatar
$initials = strtoupper(substr($member['nama'], 0, 2));

$pageTitle = 'Detail Anggota - ' . h($member['nama']);
include 'includes/header.php';
?>

<div class="container">
	<div class="member-detail-card fade-in">
		<div class="member-header">
			<div class="member-avatar">
				<?php echo $initials; ?>
			</div>
			<div class="member-info" style="flex: 1;">
				<h2><?php echo h($member['nama']); ?></h2>
				<p><?php echo h($member['email']); ?></p>
				<span class="badge badge-<?php echo $member['status'] == 'Aktif' ? 'success' : 'danger'; ?>" style="margin-top: 0.5rem;">
					<?php echo $member['status']; ?>
				</span>
			</div>
			<div style="display: flex; gap: 0.5rem;">
				<a href="edit_member.php?id=<?php echo $member['id']; ?>" class="btn btn-warning">✏️ Edit</a>
				<a href="anggota.php" class="btn btn-secondary">← Kembali</a>
			</div>
		</div>
		
		<div class="member-details-grid">
			<div class="detail-item">
				<span class="detail-label">ID Anggota</span>
				<span class="detail-value">#<?php echo $member['id']; ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Nama Lengkap</span>
				<span class="detail-value"><?php echo h($member['nama']); ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Email</span>
				<span class="detail-value"><?php echo h($member['email']); ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Telepon</span>
				<span class="detail-value"><?php echo h($member['telepon'] ?: '-'); ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Sektor</span>
				<span class="detail-value"><?php echo isset($member['sektor']) && $member['sektor'] !== null ? h($member['sektor']) : '-'; ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Alamat</span>
				<span class="detail-value"><?php echo h($member['alamat'] ?: '-'); ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Tanggal Lahir</span>
				<span class="detail-value"><?php echo formatDate($member['tanggal_lahir']); ?></span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Status</span>
				<span class="detail-value">
					<span class="badge badge-<?php echo $member['status'] == 'Aktif' ? 'success' : 'danger'; ?>">
						<?php echo $member['status']; ?>
					</span>
				</span>
			</div>
			<div class="detail-item">
				<span class="detail-label">Tanggal Dibuat</span>
				<span class="detail-value"><?php echo formatDate($member['created_at'], 'd/m/Y H:i'); ?></span>
			</div>
			<?php if ($member['updated_at'] != $member['created_at']): ?>
			<div class="detail-item">
				<span class="detail-label">Terakhir Diupdate</span>
				<span class="detail-value"><?php echo formatDate($member['updated_at'], 'd/m/Y H:i'); ?></span>
			</div>
			<?php endif; ?>
		</div>
	</div>
	
	<?php if ($activities && $activities->num_rows > 0): ?>
	<div class="chart-container fade-in">
		<h3 class="chart-title">Riwayat Aktivitas</h3>
		<div style="max-height: 400px; overflow-y: auto;">
			<?php while ($activity = $activities->fetch_assoc()): ?>
				<div style="padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.06);">
					<div style="display: flex; justify-content: space-between; align-items: start;">
						<div>
							<div style="color: #e5e7eb; font-weight: 500; margin-bottom: 0.25rem;">
								<?php echo h($activity['action']); ?>
							</div>
							<div style="color: #94a3b8; font-size: 0.85rem;">
								<?php echo h($activity['description'] ?: '-'); ?>
							</div>
						</div>
						<div style="color: #94a3b8; font-size: 0.85rem;">
							<?php echo formatDate($activity['created_at'], 'd/m/Y H:i'); ?>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
		</div>
	</div>
	<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>