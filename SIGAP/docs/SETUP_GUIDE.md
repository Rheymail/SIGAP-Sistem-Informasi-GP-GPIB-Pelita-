# SETUP GUIDE - PANDUAN INSTALASI SIGAP

## 📋 Prerequisites

- **XAMPP** (Apache + MySQL + PHP 7.4+)
- **PHP** 7.4 atau lebih baru
- **MySQL** 5.7 atau lebih baru
- **Browser** modern (Chrome, Firefox, Safari, Edge)

---

## 🚀 Step 1: Database Setup

### Via CLI/Terminal

```bash
# Asumsikan Anda di folder C:\xampp\htdocs\rey

# Buka MySQL
mysql -u root

# Create & import database
mysql -u root member_data < database_with_seed.sql

# Verify
mysql -u root -D member_data -e "SHOW TABLES;"
```

### Via phpMyAdmin UI

1. Buka **http://localhost/phpmyadmin/**
2. Klik menu **"Import"**
3. Pilih file **`database_with_seed.sql`**
4. Klik tombol **"Go"**
5. Tunggu sampai selesai

---

## 🔧 Step 2: Admin Account Setup

### Option A: Via Browser (Recommended)

1. Buka **http://localhost/rey/setup_admin.php**
2. Halaman akan membuat akun admin otomatis
3. Lihat pesan sukses dengan credentials

### Option B: Via CLI

```bash
cd C:\xampp\htdocs\rey
php setup_admin.php
```

**Default Credentials:**
```
Username: admin
Password: admin123
```

⚠️ **PENTING:** Ganti password setelah login pertama!

---

## ✅ Step 3: Verify Installation

### Check Database

```bash
# List all tables
mysql -u root -D member_data -e "SHOW TABLES;"

# Show users
mysql -u root -D member_data -e "SELECT id, username, role FROM users;"

# Show sample members
mysql -u root -D member_data -e "SELECT COUNT(*) AS total FROM members;"
```

### Check Files

Pastikan file ini ada di `C:\xampp\htdocs\rey/`:
```
✅ config.php
✅ includes/functions.php
✅ includes/header.php
✅ includes/footer.php
✅ login.php
✅ dashboard.php
✅ style.css
✅ script.js
```

---

## 🌐 Step 4: Access Application

### Login
```
URL: http://localhost/rey/login.php
Username: admin
Password: admin123
```

### Dashboard
```
URL: http://localhost/rey/dashboard.php
```

### Register New Account
```
URL: http://localhost/rey/register.php
```

---

## 🎯 Quick Verification Checklist

- [ ] Database `member_data` terbuat dengan 5 tabel
- [ ] Tabel `users` memiliki akun admin
- [ ] Tabel `members` memiliki sample data (3 baris)
- [ ] Login berhasil dengan username/password admin
- [ ] Dashboard dapat diakses tanpa error
- [ ] Notifikasi system berfungsi
- [ ] Member management dapat diakses

---

## 🔐 Post-Installation Security

### 1. Change Admin Password
```
Login → Profile → Change Password
```

### 2. Remove/Secure setup_admin.php
```bash
# Opsi 1: Hapus file
del setup_admin.php

# Opsi 2: Move ke folder tidak accessible
move setup_admin.php setup_admin.php.bak
```

### 3. Set File Permissions
```bash
# Make config.php read-only (recommended)
attrib +R config.php
```

### 4. Update config.php for Production
```php
// Production settings
define('DEV_BYPASS_LOGIN', false); // Make sure this is false
```

---

## 📁 Folder Structure After Setup

```
rey/
├── config.php              ✅ Main configuration
├── setup_admin.php        ⚠️  Consider removing after setup
├── database_with_seed.sql ✅ Database schema
├── login.php              ✅ Login page
├── register.php           ✅ Registration page
├── dashboard.php          ✅ Home page
├── anggota.php            ✅ Members page
├── notifikasi.php         ✅ Notifications page
├── includes/
│   ├── functions.php      ✅ Helper functions
│   ├── header.php         ✅ Header template
│   └── footer.php         ✅ Footer template
├── api/
│   └── get_notification_count.php ✅ Notification API
└── style.css              ✅ Stylesheet
```

---

## 🐛 Troubleshooting Setup

### Error: "Connection refused"
**Solusi:** 
- Pastikan MySQL service running: `services.msc`
- Atau restart XAMPP: `xampp-control.exe`

### Error: "Table already exists"
**Solusi:**
- Database sudah ada dari setup sebelumnya
- Hapus database: `mysql -u root -e "DROP DATABASE member_data;"`
- Re-import: `mysql -u root member_data < database_with_seed.sql`

### Error: "Access denied"
**Solusi:**
- Check MySQL credentials di `config.php`
- Verify MySQL is running
- Reset MySQL root password jika diperlukan

### White screen of death
**Solusi:**
- Check Apache error log: `C:\xampp\apache\logs\error.log`
- Check PHP error log: `C:\xampp\php\logs\php_error.log`
- Verify folder permissions

---

## ✨ Next Steps

1. **Login dan explore dashboard**
2. **Tambah data anggota baru**
3. **Test notification system**
4. **Customize sesuai kebutuhan**
5. **Read full documentation in /docs**

---

**Need Help?** Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
