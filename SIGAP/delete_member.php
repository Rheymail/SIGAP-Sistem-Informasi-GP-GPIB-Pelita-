<?php
require_once 'config.php';
requireLogin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Get member name before deletion for notification
    $getStmt = $conn->prepare("SELECT nama FROM members WHERE id = ? LIMIT 1");
    $memberName = '';
    if ($getStmt) {
        $getStmt->bind_param('i', $id);
        $getStmt->execute();
        $result = $getStmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $memberName = $row['nama'];
        }
        $getStmt->close();
    }
    
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Data anggota berhasil dihapus!';
            
            // Create notification for member deleted
            if ($memberName) {
                createNotification($conn, 'member_deleted', 'Anggota Dihapus', 
                                 "Anggota '{$memberName}' telah dihapus dari sistem.");
                
                // Log activity
                logActivity($conn, 'Delete Member', 'members', $id, 
                           "Anggota dihapus: {$memberName}");
            }
        } else {
            $_SESSION['error'] = 'Gagal menghapus data: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Gagal menghapus data: ' . $conn->error;
    }
}

header("Location: dashboard.php");
exit();
?>