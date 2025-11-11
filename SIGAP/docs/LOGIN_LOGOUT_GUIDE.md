LOGIN & LOGOUT FEATURE - PANDUAN PENGGUNAAN

File yang dibuat/diubah:
========================

1. logout.php (UPDATED)
   - Handler untuk logout user
   - Destroy session dan redirect ke login
   - Verifikasi user sudah login sebelum logout

2. includes/header.php (UPDATED)
   - Tambah user profile button dengan dropdown menu
   - Menu berisi: Profil, Ganti Password, Logout
   - Tampilkan username user yang login

3. login.php (UPDATED)
   - Tampilkan pesan "Anda berhasil logout" jika baru logout
   - Improved form validation

4. profile.php (BARU)
   - Halaman profil user
   - Tampilkan informasi user (ID, username, role)
   - Quick action buttons (Ganti Password, Logout)
   - Tips keamanan

5. change_password.php (BARU)
   - Halaman untuk mengubah password
   - Verifikasi password lama
   - Validasi password baru minimal 6 karakter
   - Konfirmasi password sebelum di-hash dan disimpan
   - Auto logout setelah password berhasil diubah

6. style.css (UPDATED)
   - CSS untuk user profile dropdown menu
   - Styling untuk active state
   - Hover effects dan animations

7. script.js (UPDATED)
   - JavaScript untuk toggle user menu dropdown
   - Close dropdown saat click di luar
   - Close dropdown saat klik menu item

FITUR UTAMA
===========

✅ User Profile Menu
   - Klik tombol username di navbar kanan
   - Dropdown menu dengan opsi: Profil, Ganti Password, Logout
   - Menu tertutup otomatis saat logout

✅ Login
   - Masukkan username: admin
   - Masukkan password: admin123 (atau password yang sudah diganti)
   - Session akan dimulai, user redirect ke dashboard

✅ Logout
   - Klik tombol username → pilih "🚪 Logout"
   - Atau buka langsung: http://localhost/rey/logout.php
   - Session dihapus, redirect ke login dengan pesan "Anda berhasil logout"
   - Tombol logout memiliki confirmation dialog

✅ Ganti Password
   - Klik tombol username → pilih "🔐 Ganti Password"
   - Atau buka: http://localhost/rey/change_password.php
   - Masukkan password lama (verifikasi)
   - Masukkan password baru (min 6 karakter)
   - Konfirmasi password baru
   - Klik "Ganti Password" → berhasil → auto logout
   - User harus login lagi dengan password baru

✅ Profil User
   - Klik tombol username → pilih "⚙️ Profil"
   - Atau buka: http://localhost/rey/profile.php
   - Lihat info user (ID, username, role, status)
   - Quick actions: Ganti Password, Dashboard, Logout

FLOW PENGGUNAAN
===============

1. FIRST TIME LOGIN
   ├─ Buka http://localhost/rey/
   ├─ Redirect ke login.php
   ├─ Username: admin
   ├─ Password: admin123
   └─ Redirect ke dashboard.php

2. DI DASHBOARD
   ├─ Navbar kanan: 👤 admin (user profile button)
   ├─ Klik button → dropdown menu muncul
   ├─ Pilih "🔐 Ganti Password" → ubah password
   ├─ Atau pilih "⚙️ Profil" → lihat info
   └─ Atau pilih "🚪 Logout" → confirm → session destroy

3. AFTER LOGOUT
   ├─ Redirect ke login.php
   ├─ Tampil pesan: "Anda berhasil logout"
   ├─ Login lagi dengan password baru (jika sudah diganti)
   └─ Redirect ke dashboard lagi

SECURITY FEATURES
=================

✅ Password Hashing
   - Password disimpan menggunakan password_hash(PASSWORD_DEFAULT)
   - Verifikasi menggunakan password_verify()

✅ Session Management
   - Session di-check di setiap halaman yang butuh login (requireLogin())
   - Auto redirect ke login jika session tidak valid

✅ Prepared Statements
   - Semua query menggunakan prepared statements
   - Prevent SQL injection

✅ Logout Confirmation
   - User harus confirm sebelum logout
   - Prevent accidental logout

✅ Auto Logout After Password Change
   - Setelah ganti password, user auto logout
   - User harus login lagi dengan password baru

TROUBLESHOOTING
===============

Problem 1: "User profile menu tidak muncul"
Solusi:
- Refresh halaman (F5)
- Pastikan JavaScript sudah di-load (check browser console)
- Pastikan style.css sudah ter-load

Problem 2: "Logout tidak bekerja"
Solusi:
- Cek apakah session sudah diinisialisasi
- Cek file logout.php bisa diakses
- Clear browser cookies dan coba lagi

Problem 3: "Ganti password tidak bekerja"
Solusi:
- Pastikan password lama benar
- Pastikan password baru minimal 6 karakter
- Pastikan password baru dan konfirmasi cocok
- Cek error message untuk detail

Problem 4: "Stuck di login setelah logout"
Solusi:
- Refresh halaman
- Clear browser cookies (CTRL+SHIFT+DELETE)
- Coba login dengan username/password benar

TESTING CHECKLIST
=================

- [ ] Jalankan aplikasi di http://localhost/rey/
- [ ] Login dengan admin / admin123
- [ ] Lihat user profile button di navbar (👤 admin)
- [ ] Klik button → dropdown menu muncul
- [ ] Klik "⚙️ Profil" → halaman profile terbuka
- [ ] Klik "🔐 Ganti Password" → form ganti password terbuka
- [ ] Input password lama, password baru, konfirmasi
- [ ] Klik "Ganti Password" → success message → auto logout
- [ ] Login lagi dengan password lama → gagal
- [ ] Login lagi dengan password baru → berhasil
- [ ] Klik user profile button lagi
- [ ] Klik "🚪 Logout" → confirm dialog muncul
- [ ] Klik OK → session destroy → redirect ke login
- [ ] Pesan "Anda berhasil logout" ditampilkan
- [ ] Login lagi untuk memastikan semuanya berfungsi

FILES SUMMARY
=============

File Baru:
- profile.php
- change_password.php

File Diubah:
- logout.php (improved)
- includes/header.php
- login.php
- style.css
- script.js

File Tidak Diubah (tapi butuh ):
- dashboard.php (includes header.php, jadi otomatis punya menu)
- anggota.php (includes header.php, jadi otomatis punya menu)
- atestasi.php (includes header.php, jadi otomatis punya menu)
- notifikasi.php (includes header.php, jadi otomatis punya menu)
- about.php (includes header.php, jadi otomatis punya menu)
- config.php (isLoggedIn & requireLogin sudah ada)

---
Last Updated: 2025-11-10
Version: 1.0
