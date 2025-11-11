<?php
$unreadCount = getUnreadNotificationsCount($conn);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($pageTitle) ? $pageTitle : 'SIGAP'; ?></title>
	<link rel="stylesheet" href="style.css?v=1005">
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
	<div class="layout">
		<main class="main-content">
			<div class="container" id="navbar-main-container">
				<div class="navbar">
					<div class="navbar-brand">
						<img src="assets/logo.jpg" alt="Logo SIGAP" class="navbar-brand-logo">
						<h1>SIGAP</h1>
					</div>
					<button class="mobile-menu-btn" id="mobile-menu-btn">☰</button>
					<nav class="nav-tabs">
						<a href="dashboard.php" class="nav-tab <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
						<a href="anggota.php" class="nav-tab <?php echo $currentPage == 'anggota.php' ? 'active' : ''; ?>">Anggota</a>
						<a href="notifikasi.php" class="nav-tab <?php echo $currentPage == 'notifikasi.php' ? 'active' : ''; ?>">Notifikasi</a>
						<a href="atestasi.php" class="nav-tab <?php echo $currentPage == 'atestasi.php' ? 'active' : ''; ?>">Atestasi</a>
						<a href="about.php" class="nav-tab <?php echo $currentPage == 'about.php' ? 'active' : ''; ?>">About Us</a>
					</nav>
					<div class="navbar-spacer"></div>
					<div class="navbar-right">
						<div class="search">
							<span>🔎</span>
							<input type="text" placeholder="Search" id="global-search" data-searchable="true">
						</div>
						<a class="icon-btn" href="notifikasi.php" title="Notifikasi">
							🔔
							<?php if ($unreadCount > 0): ?>
								<span class="notification-badge"><?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?></span>
							<?php endif; ?>
						</a>

						<div class="user-menu-container">
							<?php
							$roleClass = 'admin'; // default
							if (isset($_SESSION['user_id'])) {
								$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
								$stmt->bind_param("i", $_SESSION['user_id']);
								$stmt->execute();
								$result = $stmt->get_result();
								if ($userData = $result->fetch_assoc()) {
									$roleClass = strtolower($userData['role']) === 'admin' ? 'admin' : 'user';
								}
								$stmt->close();
							}
							?>
							<button class="user-profile-btn user-avatar-<?php echo $roleClass; ?>" title="Profil & Logout" id="user-profile-btn">
								<?php 
									if ($roleClass === 'admin') {
										echo '👨‍💼'; // admin icon kuning
									} else {
										echo '👤'; // user icon abu-abu
									}
								?>
							</button>
								<div class="user-menu-dropdown" id="user-menu-dropdown">
									<div class="user-menu-header">
										<p class="user-name"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></p>
										<p class="user-id">ID: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '-'; ?></p>
									</div>
									<hr class="user-menu-divider">
									<a href="profile.php" class="user-menu-item">⚙️ Profil</a>
									<a href="change_password.php" class="user-menu-item">🔐 Ganti Password</a>
									<hr class="user-menu-divider">
									<a href="logout.php" class="user-menu-item logout-btn" onclick="return confirm('Apakah Anda yakin ingin logout?')">🚪 Logout</a>
								</div>
							</div>
					</div>
				</div>
			</div>
			
			<div class="mobile-menu" id="mobile-menu">
				<button class="mobile-menu-close" onclick="document.getElementById('mobile-menu').classList.remove('active')">×</button>
				<nav class="mobile-nav">
					<a href="dashboard.php">Dashboard</a>
					<a href="anggota.php">Anggota</a>
					<a href="notifikasi.php">Notifikasi</a>
					<a href="atestasi.php">Atestasi</a>
					<a href="about.php">About Us</a>
					<a href="profile.php">Profil</a>
					<a href="logout.php">Logout</a>
				</nav>
			</div>