<?php
// Helper Functions untuk SIGAP

// Fungsi untuk log activity
function logActivity($conn, $action, $table_name = null, $record_id = null, $description = null) {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($tableCheck->num_rows == 0) {
        return; // Table doesn't exist yet
    }
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ississ", $user_id, $action, $table_name, $record_id, $description, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Fungsi untuk create notification
function createNotification($conn, $type, $title, $message, $member_id = null) {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck->num_rows == 0) {
        return; // Table doesn't exist yet
    }
    
    $stmt = $conn->prepare("INSERT INTO notifications (type, title, message, member_id) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssi", $type, $title, $message, $member_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fungsi untuk get unread notifications count
function getUnreadNotificationsCount($conn) {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck->num_rows == 0) {
        return 0; // Table doesn't exist yet
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }
    return 0;
}

// Fungsi untuk mark notification as read
function markNotificationAsRead($conn, $id) {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck->num_rows == 0) {
        return; // Table doesn't exist yet
    }
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fungsi untuk generate birthday notifications
function generateBirthdayNotifications($conn) {
    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($tableCheck->num_rows == 0) {
        return; // Table doesn't exist yet
    }
    
    $today = date('Y-m-d');
    $todayTime = strtotime($today);
    
    // Step 1: Clean up old birthday notifications (birthday has passed)
    $conn->query("DELETE FROM notifications WHERE type = 'birthday' AND created_at < DATE_SUB(NOW(), INTERVAL 40 DAY)");
    
    // Get members with birthdays - using tanggal_lahir or tanggal_bergabung as fallback
    $query = "SELECT id, nama, COALESCE(tanggal_lahir, tanggal_bergabung) as birth_date FROM members WHERE tanggal_lahir IS NOT NULL OR tanggal_bergabung IS NOT NULL";
    $result = $conn->query($query);
    
    if (!$result) return;
    
    while ($member = $result->fetch_assoc()) {
        if (!$member['birth_date']) continue;
        
        try {
            $birthDate = new DateTime($member['birth_date']);
            $todayDate = new DateTime($today);
            
            // Calculate next birthday
            $thisYearBirthday = new DateTime($todayDate->format('Y') . '-' . $birthDate->format('m-d'));
            if ($thisYearBirthday < $todayDate) {
                $thisYearBirthday->modify('+1 year');
            }
            
            // Check if birthday is within next 30 days
            $daysUntil = $todayDate->diff($thisYearBirthday)->days;
            if ($daysUntil <= 30 && $daysUntil >= 0) {
                // Check if notification for THIS birthday already exists (created only once per birthday)
                // Look for birthday notifications in the last 30 days for this member
                $birthdayDate = $thisYearBirthday->format('Y-m-d');
                $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
                
                $checkQuery = "SELECT id FROM notifications WHERE type = 'birthday' AND member_id = ? 
                             AND created_at >= ? AND created_at <= DATE_ADD(?, INTERVAL 1 DAY)";
                $checkStmt = $conn->prepare($checkQuery);
                if ($checkStmt) {
                    $checkStmt->bind_param("iss", $member['id'], $thirtyDaysAgo, $birthdayDate);
                    $checkStmt->execute();
                    $exists = $checkStmt->get_result()->num_rows > 0;
                    $checkStmt->close();
                    
                    if (!$exists) {
                        $title = "Ulang Tahun Mendatang";
                        $message = "Ulang tahun {$member['nama']} dalam {$daysUntil} hari";
                        createNotification($conn, 'birthday', $title, $message, $member['id']);
                    }
                }
            }
        } catch (Exception $e) {
            // Skip invalid dates
            continue;
        }
    }
}

// Fungsi untuk escape output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk format date
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

// Fungsi untuk get member statistics
function getMemberStatistics($conn) {
    $stats = [];
    
    $stats['total'] = $conn->query("SELECT COUNT(*) as count FROM members")->fetch_assoc()['count'];
    $stats['active'] = $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'Aktif'")->fetch_assoc()['count'];
    $stats['inactive'] = $conn->query("SELECT COUNT(*) as count FROM members WHERE status = 'Tidak Aktif'")->fetch_assoc()['count'];
    
    // --- PERUBAHAN DI SINI ---
    // Get monthly growth data for chart (diubah dari 12 bulan menjadi 3 bulan)
    $growthQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                    FROM members 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                    GROUP BY month 
                    ORDER BY month";
    $growthResult = $conn->query($growthQuery);
    $stats['growth'] = [];
    while ($row = $growthResult->fetch_assoc()) {
        $stats['growth'][] = $row;
    }
    
    return $stats;
}

// Fungsi untuk pagination
function getPagination($conn, $table, $where = '', $perPage = 10, $currentPage = 1) {
    $offset = ($currentPage - 1) * $perPage;
    
    $countQuery = "SELECT COUNT(*) as total FROM $table" . ($where ? " WHERE $where" : "");
    $total = $conn->query($countQuery)->fetch_assoc()['total'];
    $totalPages = ceil($total / $perPage);
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

// Fungsi untuk search members
function searchMembers($conn, $searchTerm, $status = '', $limit = 10, $offset = 0) {
    $where = "1=1";
    $params = [];
    $types = '';
    
    if ($searchTerm) {
        $where .= " AND (nama LIKE ? OR email LIKE ? OR telepon LIKE ? OR alamat LIKE ?)";
        $searchPattern = "%$searchTerm%";
        $params = [$searchPattern, $searchPattern, $searchPattern, $searchPattern];
        $types = 'ssss';
    }
    
    if ($status) {
        $where .= " AND status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $query = "SELECT * FROM members WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

?>