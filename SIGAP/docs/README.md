# SIGAP - Sistem Informasi Anggota Gereja Persekutuan

**Versi:** 1.0  
**Last Updated:** November 2025

## 📋 Daftar Isi
1. [Quick Start](#quick-start)
2. [Struktur Proyek](#struktur-proyek)
3. [Database](#database)
4. [Fitur Utama](#fitur-utama)
5. [Setup & Installation](#setup--installation)
6. [Dokumentasi Lengkap](#dokumentasi-lengkap)

---

## 🚀 Quick Start

### Prasyarat
- XAMPP (Apache + MySQL + PHP)
- MySQL 5.7+
- Browser modern

### Setup Awal (3 Langkah)

**1. Import Database**
```bash
mysql -u root member_data < database_with_seed.sql
```

**2. Buat Admin Account**
```
http://localhost/rey/setup_admin.php
```

**3. Login**
```
Username: admin
Password: admin123
```

---

## 📁 Struktur Proyek

```
rey/
├── 📂 config/               # Konfigurasi aplikasi
│   ├── config.php          # Database & session config
│   └── database_with_seed.sql # Schema + sample data
│
├── 📂 includes/            # Template & fungsi helper
│   ├── header.php         # Navigation header
│   ├── footer.php         # Footer template
│   └── functions.php      # Helper functions
│
├── 📂 pages/              # Halaman aplikasi utama
│   ├── dashboard.php      # Dashboard (home)
│   ├── anggota.php        # Manajemen anggota
│   ├── member_detail.php  # Detail anggota
│   ├── notifikasi.php     # Sistem notifikasi
│   ├── atestasi.php       # Data atestasi
│   └── login.php          # Form login
│
├── 📂 admin/              # Halaman admin
│   ├── add_member.php     # Tambah anggota
│   ├── edit_member.php    # Edit anggota
│   ├── delete_member.php  # Hapus anggota
│   ├── add_atestasi.php   # Tambah atestasi
│   ├── bulk_update_status.php
│   └── bulk_delete.php
│
├── 📂 api/                # API endpoints
│   └── get_notification_count.php # API notifikasi
│
├── 📂 assets/             # Static files
│   └── (images, fonts, dll)
│
├── 📂 docs/               # Dokumentasi
│   ├── SETUP_GUIDE.md
│   ├── NOTIFICATIONS.md
│   ├── BIRTHDAY_NOTIFICATIONS.md
│   └── TROUBLESHOOTING.md
│
├── 📄 style.css           # Main stylesheet
├── 📄 script.js           # Main JavaScript
├── 📄 .gitignore          # Git ignore rules
└── 📄 README.md           # This file
```

---

## 🗄️ Database

### Tabel Utama

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Akun login (admin/user) |
| `members` | Data anggota jemaat |
| `atestasi` | Data perpindahan jemaat |
| `notifications` | Sistem notifikasi |
| `activity_logs` | Audit trail aktivitas |

### Connection Info
```php
Host: localhost
User: root
Password: (empty)
Database: member_data
```

---

## ⭐ Fitur Utama

### 1. 👥 Manajemen Anggota
- ✅ Tambah/edit/hapus anggota
- ✅ Search & filter berdasarkan nama, email, telepon
- ✅ Filter status (Aktif/Tidak Aktif)
- ✅ Pagination (10/25/50/100 per halaman)
- ✅ Bulk operations (update status, delete multiple)

### 2. 🔔 Sistem Notifikasi
- ✅ Notifikasi ulang tahun otomatis (h-1 bulan)
- ✅ Notifikasi saat member ditambah/edit/hapus
- ✅ Badge notifikasi di navbar
- ✅ Mark as read (individual & all)
- ✅ Hanya 1 notifikasi per birthday (no duplicates)

### 3. 🔐 Sistem Login & Registrasi
- ✅ Login dengan username/password
- ✅ Self-registration (user bisa daftar sendiri)
- ✅ Password hashing (bcrypt)
- ✅ Role-based access (admin/user)
- ✅ Session management

### 4. 📊 Dashboard
- ✅ Statistik anggota (total, aktif, tidak aktif)
- ✅ Daftar ulang tahun mendatang
- ✅ Chart & visualisasi data
- ✅ Quick actions

### 5. 📋 Atestasi (Perpindahan Jemaat)
- ✅ Tambah/edit data atestasi
- ✅ Status management (Pending/Approved/Rejected)
- ✅ Linked dengan data member

### 6. 📝 Activity Logging
- ✅ Audit trail semua aktivitas
- ✅ Track user, action, timestamp, IP
- ✅ Berguna untuk compliance & security

---

## 🔧 Setup & Installation

### 1. Database Setup

```bash
# Buka terminal/PowerShell di folder project
# Import SQL schema
mysql -u root member_data < database_with_seed.sql

# Atau via phpMyAdmin
# - Buka http://localhost/phpmyadmin
# - Pilih "Import"
# - Upload file database_with_seed.sql
```

### 2. Admin Account

```bash
# Buka browser
http://localhost/rey/setup_admin.php

# Atau jalankan via CLI
php setup_admin.php
```

### 3. Start Application

```
http://localhost/rey/dashboard.php
```

**Default Credentials:**
```
Username: admin
Password: admin123
```

### 4. (Optional) Change Admin Password

Setelah login:
1. Buka Profile/Settings
2. Change Password

---

## 📚 Dokumentasi Lengkap

Dokumentasi detail tersedia di folder `/docs`:

- 📖 **[SETUP_GUIDE.md](docs/SETUP_GUIDE.md)** - Panduan lengkap setup
- 📖 **[REGISTRATION.md](docs/REGISTRATION.md)** - Sistem registrasi
- 📖 **[NOTIFICATIONS.md](docs/NOTIFICATIONS.md)** - Sistem notifikasi
- 📖 **[BIRTHDAY_NOTIFICATIONS.md](docs/BIRTHDAY_NOTIFICATIONS.md)** - Birthday notification optimization
- 📖 **[TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md)** - Troubleshooting umum

---

## 🔑 Key Files

| File | Deskripsi |
|------|-----------|
| `config.php` | Konfigurasi utama (database, session) |
| `includes/functions.php` | Fungsi helper (notifications, logging, dll) |
| `includes/header.php` | Navigation template |
| `includes/footer.php` | Footer template |
| `style.css` | Main stylesheet (responsive design) |
| `script.js` | Main JavaScript (interactivity) |
| `setup_admin.php` | Setup initial admin user |
| `.gitignore` | Git ignore rules |

---

## 🛡️ Security Features

✅ **SQL Injection Prevention** - Prepared statements  
✅ **XSS Prevention** - HTML escaping  
✅ **Password Hashing** - bcrypt (PASSWORD_DEFAULT)  
✅ **Session Management** - Secure session handling  
✅ **Activity Logging** - Audit trail semua aktivitas  
✅ **Input Validation** - Server-side validation  

---

## 📈 Development Roadmap

### Completed ✅
- [x] Database schema & sample data
- [x] Login & authentication
- [x] Member management (CRUD)
- [x] Notification system
- [x] Activity logging
- [x] User registration
- [x] Dashboard & statistics
- [x] Atestasi management

### Future Enhancements 📋
- [ ] Email notifications
- [ ] Advanced reporting/export
- [ ] Multi-language support
- [ ] Two-factor authentication
- [ ] Mobile app
- [ ] SMS notifications

---

## 🤝 Support

Untuk masalah atau pertanyaan:
1. Baca dokumentasi di folder `/docs`
2. Check file `TROUBLESHOOTING.md`
3. Hubungi administrator

---

## 📄 License

© 2025 SIGAP. All rights reserved.

---

**Last Updated:** November 2025  
**Version:** 1.0
