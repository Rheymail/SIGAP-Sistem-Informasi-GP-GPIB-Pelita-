# SISTEM REGISTRASI AKUN - DOKUMENTASI

## Overview
Sistem registrasi akun memungkinkan pengguna baru untuk membuat akun sendiri tanpa perlu administrator membuat akun secara manual.

## Files yang Dibuat/Diupdate

### 1. `register.php` (BARU)
Halaman registrasi dengan form dan validasi lengkap.

**Fitur:**
- ✅ Input username dan password
- ✅ Validasi username (3-50 karakter, format alphanumeric)
- ✅ Validasi password (minimal 6 karakter)
- ✅ Konfirmasi password
- ✅ Check duplikat username
- ✅ Password hashing dengan PASSWORD_DEFAULT
- ✅ Activity logging saat registrasi
- ✅ Redirect ke login setelah sukses

### 2. `login.php` (UPDATED)
Tambahkan link ke halaman registrasi di bawah form login.

**Perubahan:**
- Tambah tombol "Daftar di sini" yang link ke `register.php`
- User bisa langsung navigate ke registrasi dari halaman login

## Validasi yang Diterapkan

### Username
| Kriteria | Persyaratan |
|----------|------------|
| Panjang | Minimal 3, maksimal 50 karakter |
| Format | Huruf, angka, underscore (_), titik (.), dash (-) |
| Unique | Tidak boleh sama dengan username yang sudah ada |
| Mandatory | Tidak boleh kosong |

### Password
| Kriteria | Persyaratan |
|----------|------------|
| Panjang | Minimal 6, maksimal 100 karakter |
| Mandatory | Tidak boleh kosong |
| Hashing | Menggunakan `password_hash()` dengan PASSWORD_DEFAULT |
| Konfirmasi | Harus sama persis dengan input pertama |

## Database Schema

### Tabel: `users`
```sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Catatan:** Kolom `role` otomatis diset ke `'user'` untuk akun baru. Admin dapat mengubah role ke `'admin'` secara manual di database jika diperlukan.

## Flow Registrasi

```
User membuka register.php
         ↓
Masukkan username & password
         ↓
Submit form
         ↓
Validasi client-side (browser)
         ↓
Validasi server-side:
  - Cek panjang username (3-50)
  - Cek format username (alphanumeric)
  - Cek panjang password (6-100)
  - Cek password === password_confirm
  - Cek username belum digunakan (SELECT)
         ↓
Jika error → Tampilkan pesan error
Jika valid → Insert ke database dengan password ter-hash
         ↓
Log activity ke activity_logs
         ↓
Tampilkan pesan sukses
         ↓
Redirect ke login.php (3 detik)
```

## Security Measures

### 1. Password Hashing
```php
$hash = password_hash($password, PASSWORD_DEFAULT);
```
- Menggunakan bcrypt dengan salt otomatis
- Tidak menyimpan password dalam plaintext
- Secure terhadap rainbow table attacks

### 2. SQL Injection Prevention
```php
$checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$checkStmt->bind_param('s', $username);
```
- Menggunakan prepared statement
- Parameter di-bind dengan type hinting

### 3. XSS Prevention
```php
value="<?php echo htmlspecialchars($username); ?>"
```
- Output di-escape menggunakan `htmlspecialchars()`

### 4. Username Validation
```php
if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    $errors[] = 'Username hanya boleh berisi huruf, angka, underscore, titik, dan dash';
}
```
- Format username ketat untuk menghindari karakter berbahaya
- Pattern: `[a-zA-Z0-9_.-]+`

## Testing

### Test Case 1: Registrasi Sukses
```
Username: testuser123
Password: password123
Confirm: password123
Expected: ✅ Akun berhasil dibuat, redirect ke login
```

### Test Case 2: Username Terlalu Pendek
```
Username: ab
Password: password123
Confirm: password123
Expected: ❌ Error: Username minimal 3 karakter
```

### Test Case 3: Username Sudah Ada
```
Username: admin (sudah ada)
Password: password123
Confirm: password123
Expected: ❌ Error: Username sudah digunakan
```

### Test Case 4: Password Tidak Cocok
```
Username: testuser456
Password: password123
Confirm: password456
Expected: ❌ Error: Password dan konfirmasi password tidak sama
```

### Test Case 5: Karakter Username Tidak Valid
```
Username: test@user!
Password: password123
Confirm: password123
Expected: ❌ Error: Username hanya boleh berisi huruf, angka, underscore, titik, dan dash
```

## Default Role

**Akun baru** otomatis mendapat role `'user'`:
```php
$role = 'user'; // Default role for new registrations
```

Jika ingin akun dengan role `'admin'`, admin harus:
1. Masuk ke phpMyAdmin
2. Buka tabel `users`
3. Edit row yang bersangkutan
4. Ubah kolom `role` dari `'user'` menjadi `'admin'`

Atau gunakan query SQL:
```sql
UPDATE users SET role = 'admin' WHERE username = 'nama_user';
```

## Activity Logging

Setiap registrasi sukses dicatat di tabel `activity_logs`:
```php
logActivity($conn, 'User Registration', 'users', $insStmt->insert_id, 
           "User baru berhasil terdaftar: {$username}");
```

## Cara Menggunakan

1. **Buka halaman registrasi:**
   - http://localhost/rey/register.php

2. **Atau dari halaman login:**
   - Klik tombol "Daftar di sini" di halaman login

3. **Isi form:**
   - Username: (minimal 3 karakter)
   - Password: (minimal 6 karakter)
   - Konfirmasi Password: (harus sama)

4. **Submit form**
   - Jika ada error: akan ditampilkan pesan error
   - Jika sukses: akan redirect ke login.php

5. **Login dengan akun baru**
   - Gunakan username dan password yang baru dibuat

## Optional Enhancements (Future)

- [ ] Email verification sebelum akun aktif
- [ ] CAPTCHA untuk anti-bot
- [ ] Password strength indicator
- [ ] Rate limiting untuk prevent brute force
- [ ] Send welcome email ke user baru
- [ ] Admin approval sebelum akun aktif
- [ ] Auto-generate member profile saat registrasi
