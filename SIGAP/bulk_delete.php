<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['member_ids'])) {
	$member_ids = $_POST['member_ids'];
	$deleted = 0;
	
	foreach ($member_ids as $id) {
		$id = intval($id);
		$stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
		$stmt->bind_param("i", $id);
		if ($stmt->execute()) {
			$deleted++;
			logActivity($conn, 'Bulk Delete', 'members', $id, "Anggota dihapus");
		}
		$stmt->close();
	}
	
	$_SESSION['success_message'] = "$deleted anggota berhasil dihapus!";
} else {
	$_SESSION['error_message'] = "Invalid request!";
}

header('Location: anggota.php');
exit;
?>

