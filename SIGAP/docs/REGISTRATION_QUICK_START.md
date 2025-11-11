# QUICK START - SISTEM REGISTRASI

## Akses Halaman Registrasi

### Opsi 1: Langsung ke URL
```
http://localhost/rey/register.php
```

### Opsi 2: Dari halaman login
1. Buka http://localhost/rey/login.php
2. Klik tombol "Daftar di sini" di bagian bawah

## Cara Mendaftar

1. **Masukkan username**
   - Minimal 3 karakter
   - Maksimal 50 karakter
   - Gunakan huruf, angka, underscore (_), titik (.), atau dash (-)
   - Contoh: `john_doe`, `user123`, `test.account`

2. **Masukkan password**
   - Minimal 6 karakter
   - Contoh: `MyPassword123!`

3. **Konfirmasi password**
   - Pastikan sama dengan password yang dimasukkan di atas

4. **Klik tombol "Daftar"**
   - Jika ada error, akan ditampilkan pesan
   - Jika sukses, akan redirect ke halaman login dalam 3 detik

5. **Login dengan akun baru**
   - Username: (yang Anda daftar)
   - Password: (yang Anda daftar)

## Fitur Keamanan

✅ **Password Hashing** - Menggunakan bcrypt (PASSWORD_DEFAULT)  
✅ **SQL Injection Prevention** - Prepared statements  
✅ **XSS Prevention** - HTML escaping  
✅ **Username Validation** - Format strict, no special characters  
✅ **Duplicate Prevention** - Check username sebelum insert  
✅ **Activity Logging** - Setiap registrasi dicatat  

## Default Role

- Akun baru otomatis mendapat role: **user**
- Untuk upgrade ke admin, hubungi administrator

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| "Username sudah digunakan" | Pilih username yang berbeda |
| "Password minimal 6 karakter" | Masukkan password dengan panjang ≥ 6 |
| "Password tidak cocok" | Pastikan konfirmasi password sama |
| "Username minimal 3 karakter" | Masukkan username dengan panjang ≥ 3 |
| "Username hanya boleh..." | Gunakan hanya alphanumeric, _, ., - |

## Admin Memberikan Akses Admin

Jika admin ingin memberikan role admin ke user baru:

### Via phpMyAdmin
1. Buka http://localhost/phpmyadmin
2. Pilih database `member_data`
3. Klik tabel `users`
4. Edit row user yang ingin di-upgrade
5. Ubah kolom `role` dari `user` menjadi `admin`
6. Save

### Via SQL
```sql
UPDATE users SET role = 'admin' WHERE username = 'nama_username';
```

## Database Schema

```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

## Files yang Ditambah/Diupdate

- ✅ `register.php` - Halaman registrasi (BARU)
- ✅ `login.php` - Update dengan link ke registrasi
- ✅ `REGISTRATION_SYSTEM_DOCUMENTATION.md` - Dokumentasi lengkap

## Next Steps

1. Test registrasi dengan membuka http://localhost/rey/register.php
2. Buat akun test
3. Login dengan akun baru
4. Cek tabel `users` di database untuk verifikasi

---

**Pertanyaan atau masalah?** Hubungi administrator.
