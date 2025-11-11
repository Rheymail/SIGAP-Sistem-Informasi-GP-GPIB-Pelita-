PANDUAN MEMPERBAIKI MASALAH LOGIN

Jika Anda tidak bisa login dengan admin/admin123, ikuti langkah-langkah berikut:

## LANGKAH 1: Diagnosis
Buka di browser: http://localhost/rey/diagnose_login.php

Script ini akan memeriksa:
✅ Koneksi database
✅ Keberadaan tabel 'users'
✅ Keberadaan user admin
✅ Integritas password hash
✅ Hasil test login

## LANGKAH 2: Jika Admin Tidak Ada
Buka di browser: http://localhost/rey/create_admin.php

Script ini akan:
1. Hapus user admin lama (jika ada)
2. Membuat user admin baru dengan password 'admin123' (ter-hash)
3. Verifikasi apakah password cocok

Tunggu sampai melihat pesan: "✅ Admin account is ready to use!"

## LANGKAH 3: Coba Login Lagi
Buka: http://localhost/rey/login.php

Username: admin
Password: admin123

## JIKA MASIH GAGAL:

### Problem 1: "Tabel 'users' tidak ada"
Solusi: Jalankan database_create.sql terlebih dahulu
```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -u root -p < .\database_create.sql
```

### Problem 2: "User admin tidak ditemukan" setelah jalankan create_admin.php
Solusi: Periksa:
- Apakah Anda melihat pesan error saat membuat admin?
- Periksa di phpMyAdmin apakah user admin sudah ada di tabel users
- Coba jalankan create_admin.php lagi

### Problem 3: "Password TIDAK COCOK" di diagnose_login.php
Solusi:
- Jalankan create_admin.php lagi untuk membuat ulang admin dengan password baru
- Pastikan tidak ada typo saat mengetik password

### Problem 4: Login page blank atau error
Solusi: Periksa:
- Apakah database sudah ada?
- Apakah config.php sudah benar? (DB_USER, DB_PASS, DB_NAME)
- Cek di phpMyAdmin apakah bisa koneksi

## DEBUGGING LEBIH LANJUT:

Jika masih tidak bisa, tambahkan debug log ke login.php:

Di bagian if ($_SERVER['REQUEST_METHOD'] == 'POST') {
Tambahkan di atas:
```php
// DEBUG
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
```

Kemudian lihat isi file login_debug.log di workspace Anda.

## CHECKLIST:

- [ ] Jalankan database_create.sql (1x saja)
- [ ] Jalankan create_admin.php dan lihat "✅ Admin account is ready to use!"
- [ ] Lihat tabel users di phpMyAdmin dan pastikan ada 1 record dengan username=admin
- [ ] Coba login di login.php dengan admin / admin123
- [ ] Jika berhasil, Anda akan redirect ke dashboard.php
- [ ] Segera ganti password admin di halaman settings/profile

## CONTACT:
Jika masih ada masalah, kumpulkan output dari:
1. diagnose_login.php
2. create_admin.php
3. Pesan error apapun dari browser developer console (F12)

---
Last Updated: 2025-11-10
