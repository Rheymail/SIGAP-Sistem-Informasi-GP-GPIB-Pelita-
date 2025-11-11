<?php
require_once 'config.php';

// Get statistics
$stats = getMemberStatistics($conn);
$total_members = $stats['total'];
$active_members = $stats['active'];
$inactive_members = $stats['inactive'];

// Upcoming birthdays within next 1 month
$upcoming_birthdays = 0;
$birthdays_result = mysqli_query($conn, "SELECT tanggal_lahir, nama FROM members WHERE tanggal_lahir IS NOT NULL");
$upcoming_birthdays_list = [];
if ($birthdays_result) {
	while ($b = mysqli_fetch_assoc($birthdays_result)) {
		$birthDate = $b['tanggal_lahir'];
		if (!$birthDate) continue;
		
		$monthDay = date('m-d', strtotime($birthDate));
		$yearNow = date('Y');
		$nextBirthday = strtotime($yearNow . '-' . $monthDay);
		$today = strtotime(date('Y-m-d'));

		if ($nextBirthday < $today) {
			$nextBirthday = strtotime(($yearNow + 1) . '-' . $monthDay);
		}

		$diffDays = ($nextBirthday - $today) / 86400;
		if ($diffDays >= 0 && $diffDays <= 31) {
			$upcoming_birthdays++;
			$upcoming_birthdays_list[] = [
				'nama' => $b['nama'],
				'days' => floor($diffDays),
				'date' => date('d M', $nextBirthday)
			];
		}
	}
}

// Get atestasi count
$atestasi_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM atestasi WHERE status = 'Pending'");
$agp_atestasi = $atestasi_result ? mysqli_fetch_assoc($atestasi_result)['total'] : 0;

// Get sektor statistics (anggota dengan sektor 1-12 vs tanpa sektor)
$sektor_with = 0;
$sektor_without = 0;
$sektor_check = $conn->query("SHOW COLUMNS FROM members LIKE 'sektor'");
if ($sektor_check && $sektor_check->num_rows > 0) {
	$sektor_with_result = $conn->query("SELECT COUNT(*) as total FROM members WHERE sektor IS NOT NULL AND sektor BETWEEN 1 AND 12");
	$sektor_without_result = $conn->query("SELECT COUNT(*) as total FROM members WHERE sektor IS NULL");
	
	if ($sektor_with_result) {
		$sektor_with = mysqli_fetch_assoc($sektor_with_result)['total'];
	}
	if ($sektor_without_result) {
		$sektor_without = mysqli_fetch_assoc($sektor_without_result)['total'];
	}
}

// Prepare chart data
$growthData = $stats['growth'];
$chartLabels = [];
$chartData = [];
foreach ($growthData as $row) {
	$chartLabels[] = date('M Y', strtotime($row['month'] . '-01'));
	$chartData[] = (int)$row['count'];
}

$pageTitle = 'Dashboard - SIGAP';
include 'includes/header.php';
?>

<div class="container">
	<div class="dashboard-header">
		<h2>Monitoring Data AGP</h2>
	</div>
	
	<!-- Statistics Cards -->
	<div class="stats-container">
		<div class="stat-card fade-in">
			<div class="stat-icon">👥</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $total_members; ?></div>
				<div class="stat-label">Total Anggota</div>
			</div>
		</div>
		
		<div class="stat-card stat-success fade-in">
			<div class="stat-icon">✓</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $active_members; ?></div>
				<div class="stat-label">Anggota Aktif</div>
			</div>
		</div>
		
		<div class="stat-card stat-danger fade-in">
			<div class="stat-icon">✗</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $inactive_members; ?></div>
				<div class="stat-label">Tidak Aktif</div>
			</div>
		</div>

		<div class="stat-card fade-in">
			<div class="stat-icon">🎂</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $upcoming_birthdays; ?></div>
				<div class="stat-label">Ulang Tahun (≤ 1 Bulan)</div>
			</div>
		</div>

		<div class="stat-card stat-info fade-in">
			<div class="stat-icon">🔄</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $agp_atestasi; ?></div>
				<div class="stat-label">Atestasi Pending</div>
			</div>
		</div>

		<div class="stat-card fade-in">
			<div class="stat-icon">📍</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $sektor_with; ?></div>
				<div class="stat-label">Sudah Ada Sektor</div>
			</div>
		</div>

		<div class="stat-card fade-in">
			<div class="stat-icon">❓</div>
			<div class="stat-content">
				<div class="stat-value"><?php echo $sektor_without; ?></div>
				<div class="stat-label">Belum Ada Sektor</div>
			</div>
		</div>
	</div>
	
	<!-- Charts -->
	<div class="chart-container fade-in">
		<!-- PERUBAHAN DI SINI: Judul diubah dari 12 Bulan ke 3 Bulan -->
		<h3 class="chart-title">Pertumbuhan Anggota (3 Bulan Terakhir)</h3>
		<canvas id="growthChart" height="80"></canvas>
	</div>
	
	<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
		<!-- Status Distribution Chart -->
		<div class="chart-container fade-in">
			<h3 class="chart-title">Distribusi Status</h3>
			<canvas id="statusChart"></canvas>
		</div>
		
		<!-- Sektor Distribution Chart -->
		<div class="chart-container fade-in">
			<h3 class="chart-title">Distribusi Sektor</h3>
			<canvas id="sektorChart"></canvas>
		</div>
	</div>
	
	<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
		<!-- Upcoming Birthdays -->
		<div class="chart-container fade-in">
			<h3 class="chart-title">Ulang Tahun Mendatang</h3>
			<?php if (count($upcoming_birthdays_list) > 0): ?>
				<div style="max-height: 300px; overflow-y: auto;">
					<?php foreach ($upcoming_birthdays_list as $bday): ?>
						<div style="padding: 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
							<div>
								<div style="color: #e5e7eb; font-weight: 500;"><?php echo h($bday['nama']); ?></div>
								<div style="color: #94a3b8; font-size: 0.85rem;"><?php echo $bday['date']; ?></div>
							</div>
							<div style="color: #667eea; font-weight: 600;"><?php echo $bday['days']; ?> hari</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="empty-state" style="padding: 2rem;">
					<div class="empty-state-icon">🎂</div>
					<p>Tidak ada ulang tahun dalam 30 hari ke depan</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
// Growth Chart
const growthCtx = document.getElementById('growthChart').getContext('2d');
new Chart(growthCtx, {
	type: 'line',
	data: {
		labels: <?php echo json_encode($chartLabels); ?>,
		datasets: [{
			label: 'Anggota Baru',
			data: <?php echo json_encode($chartData); ?>,
			borderColor: '#667eea',
			backgroundColor: 'rgba(102, 126, 234, 0.1)',
			tension: 0.4,
			fill: true
		}]
	},
	options: {
		responsive: true,
		maintainAspectRatio: true,
		plugins: {
			legend: {
				labels: { color: '#e5e7eb' }
			}
		},
		scales: {
			y: {
				beginAtZero: true,
				ticks: { color: '#94a3b8' },
				grid: { color: 'rgba(255, 255, 255, 0.06)' }
			},
			x: {
				ticks: { color: '#94a3b8' },
				grid: { color: 'rgba(255, 255, 255, 0.06)' }
			}
		}
	}
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
	type: 'doughnut',
	data: {
		labels: ['Aktif', 'Tidak Aktif'],
		datasets: [{
			data: [<?php echo $active_members; ?>, <?php echo $inactive_members; ?>],
			backgroundColor: ['#10b981', '#ef4444'],
			borderWidth: 0
		}]
	},
	options: {
		responsive: true,
		maintainAspectRatio: true,
		plugins: {
			legend: {
				position: 'bottom',
				labels: { color: '#e5e7eb', padding: 15 }
			}
		}
	}
});

// Sektor Chart
const sektorCtx = document.getElementById('sektorChart').getContext('2d');
new Chart(sektorCtx, {
	type: 'doughnut',
	data: {
		labels: ['Sudah Ada Sektor (1-12)', 'Belum Ada Sektor'],
		datasets: [{
			data: [<?php echo $sektor_with; ?>, <?php echo $sektor_without; ?>],
			backgroundColor: ['#667eea', '#94a3b8'],
			borderWidth: 0
		}]
	},
	options: {
		responsive: true,
		maintainAspectRatio: true,
		plugins: {
			legend: {
				position: 'bottom',
				labels: { color: '#e5e7eb', padding: 15 }
			},
			tooltip: {
				callbacks: {
					label: function(context) {
						let label = context.label || '';
						if (label) {
							label += ': ';
						}
						const total = context.dataset.data.reduce((a, b) => a + b, 0);
						const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
						label += context.parsed + ' anggota (' + percentage + '%)';
						return label;
					}
				}
			}
		}
	}
});
</script>

<?php include 'includes/footer.php'; ?>