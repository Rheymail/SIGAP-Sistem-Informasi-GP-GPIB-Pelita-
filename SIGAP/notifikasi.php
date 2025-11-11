<?php
require_once 'config.php';

// Mark notification as read if ID provided
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
	markNotificationAsRead($conn, intval($_GET['mark_read']));
	header('Location: notifikasi.php');
	exit;
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
	$conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
	header('Location: notifikasi.php');
	exit;
}

// Get notifications
$query = "SELECT n.*, m.nama as member_name 
		  FROM notifications n 
		  LEFT JOIN members m ON n.member_id = m.id 
		  ORDER BY n.created_at DESC 
		  LIMIT 100";
$notifications_result = $conn->query($query);

$pageTitle = 'Notifikasi - SIGAP';
include 'includes/header.php';
?>

<div class="container">
	<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center;">
		<h2>Notifikasi</h2>
		<?php if (getUnreadNotificationsCount($conn) > 0): ?>
			<a href="?mark_all_read=1" class="btn btn-secondary">Tandai Semua Sudah Dibaca</a>
		<?php endif; ?>
	</div>

	<div class="table-container fade-in">
		<table class="data-table">
			<thead>
				<tr>
					<th style="width: 50px;">Status</th>
					<th>Waktu</th>
					<th>Jenis</th>
					<th>Judul</th>
					<th>Pesan</th>
					<th>Anggota</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php if ($notifications_result && $notifications_result->num_rows > 0): ?>
					<?php while ($notif = $notifications_result->fetch_assoc()): ?>
						<tr style="<?php echo $notif['is_read'] == 0 ? 'background: rgba(102, 126, 234, 0.1);' : ''; ?>">
							<td class="text-center">
								<?php if ($notif['is_read'] == 0): ?>
									<span class="notif-dot notif-unread" data-tooltip="Belum dibaca"></span>
								<?php else: ?>
									<span class="notif-dot notif-read" data-tooltip="Sudah dibaca"></span>
								<?php endif; ?>
							</td>
							<td><?php echo formatDate($notif['created_at'], 'd/m/Y H:i'); ?></td>
							<td>
								<span class="badge badge-<?php 
									echo $notif['type'] == 'birthday' ? 'success' : 
										($notif['type'] == 'status_change' ? 'warning' : 'info'); 
								?>">
									<?php 
									echo $notif['type'] == 'birthday' ? '🎂 Ulang Tahun' : 
										($notif['type'] == 'status_change' ? '🔄 Status' : 'ℹ️ Info'); 
									?>
								</span>
							</td>
							<td><strong><?php echo h($notif['title']); ?></strong></td>
							<td><?php echo h($notif['message']); ?></td>
							<td>
								<?php if ($notif['member_name']): ?>
									<a href="member_detail.php?id=<?php echo $notif['member_id']; ?>" style="color: #667eea;">
										<?php echo h($notif['member_name']); ?>
									</a>
								<?php else: ?>
									-
								<?php endif; ?>
							</td>
							<td>
								<?php if ($notif['is_read'] == 0): ?>
									<a href="?mark_read=<?php echo $notif['id']; ?>" class="btn btn-sm btn-success notif-mark-read">
										<span class="notif-icon">✓</span>
										<span class="notif-text">Baca</span>
									</a>
								<?php else: ?>
									<span class="notif-read-label">✓ Sudah dibaca</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endwhile; ?>
				<?php else: ?>
					<tr>
						<td colspan="7" class="text-center">
							<div class="empty-state">
								<div class="empty-state-icon">🔔</div>
								<h3>Belum ada notifikasi</h3>
								<p>Notifikasi akan muncul di sini ketika ada aktivitas penting.</p>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<?php include 'includes/footer.php'; ?>
