SIGAP - Setup & Database Instructions

Ringkasan singkat
1. Impor `database_create.sql` untuk membuat database dan tabel.
2. Jalankan `setup_admin.php` sekali untuk membuat user admin (default password `admin123`).
3. Opsional: jalankan `database_updates.sql` untuk membersihkan duplikat email dan menambahkan UNIQUE index serta sample data.
4. Sesuaikan `config.php` (DB_USER, DB_PASS) jika diperlukan. Aktifkan `DEV_BYPASS_LOGIN` hanya saat development lokal.

Langkah-langkah (Windows + XAMPP)

1) Impor database
- Gunakan phpMyAdmin: buka phpMyAdmin -> Import -> pilih `database_create.sql` -> Go.
- Atau gunakan MySQL CLI (PowerShell):

```powershell
# Sesuaikan path jika perlu
& 'C:\xampp\mysql\bin\mysql.exe' -u root -p < .\database_create.sql
```

2) Buat akun admin (hanya untuk setup awal)
- Buka browser: http://localhost/rey/setup_admin.php
- Script akan membuat user `admin` dengan password `admin123` (hash disimpan).
- Setelah login, segera ganti password admin lewat fitur yang tersedia atau ubah di database.

3) Terapkan update opsional (UNIQUE email + sample data)
- Pastikan tidak ada duplikat email yang ingin Anda simpan. Script `database_updates.sql` akan membuat backup tabel `members_duplicates_backup` berisi baris duplikat sebelum menghapusnya.
- Jalankan:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' -u root -p < .\database_updates.sql
```

4) Konfigurasi `config.php`
- Pastikan `DB_USER`, `DB_PASS`, `DB_NAME` sesuai environment Anda.
- `DEV_BYPASS_LOGIN` defaultnya `false`. Hanya set ke `true` saat butuh bypass sementara di development lokal.

5) Verifikasi fungsi utama
- Login: http://localhost/rey/login.php
- Tambah anggota: http://localhost/rey/add_member.php
- Lihat anggota: http://localhost/rey/anggota.php
- Tambah atestasi: http://localhost/rey/add_atestasi.php
- Lihat notifikasi: http://localhost/rey/notifikasi.php

Catatan keamanan & saran
- Semua password harus disimpan sebagai hash (sudah dilakukan).
- Pastikan `setup_admin.php` tidak dapat diakses di lingkungan produksi (hapus atau lindungi setelah setup).
- Gunakan HTTPS di produksi.
- Pertimbangkan menambahkan CSRF token ke form penting.
- Jika ingin, saya bisa mengubah semua query lain menjadi prepared statements (sudah memperbaiki beberapa file utama).

Jika mau, saya lanjutkan: scan menyeluruh untuk mengganti semua query yang masih rentan (otomatis) atau saya bisa melakukan perubahan bertahap per file.
