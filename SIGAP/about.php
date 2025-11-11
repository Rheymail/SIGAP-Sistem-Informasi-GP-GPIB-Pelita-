<?php
require_once 'config.php';
$pageTitle = 'About Us - SIGAP';
include 'includes/header.php';
?>

			<div class="container">
				<div class="about-hero">
					<h2>About Us</h2>
					<p>Kami membangun SIGAP (Sistem Informasi GP Pelita) untuk memudahkan pengelolaan data jemaat/anggota secara modern, aman, dan efisien. Fokus kami adalah pengalaman pakai yang sederhana, visual yang jelas, dan alur kerja yang cepat.</p>
				</div>

				<div class="about-grid" style="margin-bottom: 2rem;">
					<div class="about-text">
						<h3>Berawal dari Kebutuhan Nyata</h3>
						<p>Berangkat dari aktivitas administrasi yang masih manual, Tim Inforkom Litbang GP GPIB Pelita merancang sistem yang dapat membantu organisasi mempercepat pencatatan, pelacakan status, dan pelaporan tanpa proses yang berulang.
						Dengan pendekatan yang human-centered, kami memastikan fitur-fitur SIGAP mudah dipahami oleh pengguna dari berbagai latar belakang.</p>
					</div>
					<div class="about-text">
						<h3>Berkembang Bersama Pengguna</h3>
						<p>Seiring penggunaan, SIGAP terus kami kembangkan berdasarkan masukan pengguna. Mulai dari monitoring anggota, notifikasi ulang tahun, hingga atestasi (perpindahan jemaat) yang transparan.</p>
						<p>Tujuan kami sederhana: membuat pekerjaan Anda lebih ringan dan keputusan lebih cepat dengan data yang rapi dan akurat.</p>
					</div>
				</div>

				<div class="about-grid" style="align-items: stretch;">
					<div class="about-img">
						<img src="https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=1600&auto-format&fit=crop" alt="Tim berkolaborasi">
					</div>
					<div class="about-img">
						<img src="https://images.unsplash.com/photo-1551434678-e076c223a692?q=80&w=1600&auto-format&fit=crop" alt="Kolaborasi tim">
					</div>
				</div>


				<div class="about-location-section">
					<h3 style="text-align: center; margin-bottom: 2rem; font-size: 1.8rem; color: #1a202c;">📍 Kunjungi Kami</h3>
					
					<div class="location-container">
						<div class="location-info">
							<h4>GPIB Pelita Jakarta</h4>
							<p>
								<strong>Alamat:</strong>
								Jl. Pelita No.13, RT.4/RW.12
								Lubang Buaya, Kec. Cipayung Kota Jakarta Timur
								Daerah Khusus Ibukota Jakarta 13810
							</p>
							<p style="margin-top: 1rem;">
								<strong>Koordinat:</strong>
								-6.3789° S, 106.8883° E
							</p>
							<p style="margin-top: 1rem;">
								<strong>Kontak:</strong>
								Rhey Nathaniel S Mail: <a href="https://wa.me/6287715543340" style="color: #7c5cff; text-decoration: none;">+62 87715543340</a>
							</p>
						</div>
						
						<div class="location-map">
							<iframe 
								width="100%" 
								height="400" 
								style="border:0; border-radius: 8px;" 
								loading="lazy" 
								allowfullscreen="" 
								referrerpolicy="no-referrer-when-downgrade"
								src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.3456789!2d106.8883!3d-6.3789!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f56f0e4e4e4d%3A0x1234567890abcdef!2sJl.%20Pelita%20No.13%2C%20RT.4%2FRW.12%2C%20Lubang%20Buaya%2C%20Kec.%20Cipayung%2C%20Kota%20Jakarta%20Timur!5e0!3m2!1sid!2sid!4v1234567890">
							</iframe>
						</div>
					</div>
				</div>
			</div>
<?php include 'includes/footer.php'; ?>