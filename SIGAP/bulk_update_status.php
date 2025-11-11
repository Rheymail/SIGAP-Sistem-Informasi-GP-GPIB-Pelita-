<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_ids']) && isset($_POST['status'])) {
	$member_ids = $_POST['member_ids'];
	$status = $_POST['status'];
	$updated = 0;
	
	foreach ($member_ids as $id) {
		$id = intval($id);
		$stmt = $conn->prepare("UPDATE members SET status = ? WHERE id = ?");
		$stmt->bind_param("si", $status, $id);
		if ($stmt->execute()) {
			$updated++;
			logActivity($conn, 'Bulk Update Status', 'members', $id, "Status diubah menjadi $status");
		}
		$stmt->close();
	}
	
	$_SESSION['success_message'] = "$updated anggota berhasil diupdate!";
} else {
	$_SESSION['error_message'] = "Invalid request!";
}

header('Location: anggota.php');
exit;
?>

