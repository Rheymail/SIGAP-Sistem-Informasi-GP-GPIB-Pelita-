# NOTIFICATION SYSTEM - PANDUAN LENGKAP

## 📌 Overview

Sistem notifikasi otomatis memberitahu user tentang:
- ✅ Ulang tahun anggota (h-1 bulan)
- ✅ Anggota baru ditambahkan
- ✅ Data anggota diperbarui
- ✅ Anggota dihapus

---

## 🎂 Birthday Notifications

### Cara Kerja

1. **Trigger:** Setiap session baru (page load pertama per hari)
2. **Check:** Cari semua member dengan birthday dalam 30 hari ke depan
3. **Create:** Buat notifikasi 1x saja per birthday (no duplicates)
4. **Cleanup:** Hapus notifikasi > 40 hari otomatis

### Birthday Window

```
Hari Ini ─────── [30 Days] ───────────┐
                                      │
                                  Birthday
                                      │
                     [40 Days] ────────┘
                        (Cleanup)
```

- ✅ Notification created: Dalam 30 hari ke depan
- ✅ Notification displayed: Di halaman notifikasi
- 🗑️ Notification deleted: Setelah 40 hari lewat

### Notifikasi Ulang Tahun Hanya Dibuat 1x

**Sebelum:**
- Notifikasi dibuat setiap hari dalam 30 hari window
- Menghasilkan duplikat

**Setelah Optimization:**
- Notifikasi hanya dibuat 1x saat memasuki window 30 hari
- Tetap ditampilkan sampai birthday lewat
- Auto-cleanup setelah 40 hari

---

## 👤 Member Activity Notifications

### Triggered When

| Activity | Notification Type | Description |
|----------|-------------------|-------------|
| Member added | `member_added` | Anggota baru ditambahkan ke sistem |
| Member updated | `member_updated` | Data anggota diperbarui |
| Member deleted | `member_deleted` | Anggota dihapus dari sistem |

### Example

```
1. User membuka http://localhost/rey/add_member.php
2. Isi form dan submit
3. Sistem:
   - Insert ke tabel members
   - Buat notifikasi type 'member_added'
   - Log activity ke activity_logs
   - Redirect ke dashboard
4. User buka notifikasi.php
5. Lihat notifikasi: "Anggota Baru Ditambahkan: [nama]"
```

---

## 🔔 Accessing Notifications

### Halaman Notifikasi

**URL:** `http://localhost/rey/notifikasi.php`

**Fitur:**
- ✅ List semua notifikasi (dengan pagination)
- ✅ Badge unread count di navbar
- ✅ Mark as read (individual)
- ✅ Mark all as read
- ✅ Filter by type & status

### Notification Badge

Navbar menampilkan badge dengan jumlah notifikasi belum dibaca:
```
🔔 3  ← Shows 3 unread notifications
```

Badge update otomatis setiap 60 detik.

---

## 🗄️ Database Schema

### Table: `notifications`

```sql
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `member_id` INT UNSIGNED DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### Notification Types

| Type | Used For | Example Message |
|------|----------|-----------------|
| `birthday` | Ulang tahun | "Ulang tahun John Doe dalam 5 hari" |
| `member_added` | Member baru | "Anggota baru 'Jane Doe' ditambahkan" |
| `member_updated` | Update member | "Data anggota 'John' diperbarui" |
| `member_deleted` | Hapus member | "Anggota 'Jane' dihapus" |
| `status_change` | Status update | "Status diubah menjadi Tidak Aktif" |
| `info` | Info umum | "Informasi penting..." |

---

## 📊 Query Examples

### Get Unread Notifications

```sql
SELECT id, type, title, message, created_at 
FROM notifications 
WHERE is_read = 0 
ORDER BY created_at DESC;
```

### Get Birthday Notifications

```sql
SELECT id, title, message, member_id, created_at 
FROM notifications 
WHERE type = 'birthday' 
ORDER BY created_at DESC;
```

### Mark Notification as Read

```sql
UPDATE notifications SET is_read = 1 WHERE id = 123;
```

### Count Unread

```sql
SELECT COUNT(*) as unread_count FROM notifications WHERE is_read = 0;
```

---

## 🔧 Configuration

### In `config.php`

```php
// Birthday notification window (days)
// Notifications generated for birthdays within 30 days
define('BIRTHDAY_NOTIFICATION_DAYS', 30);

// Cleanup old notifications after X days
define('BIRTHDAY_CLEANUP_DAYS', 40);
```

### In `includes/functions.php`

**Function: `generateBirthdayNotifications()`**
- Called on every session start (page load)
- Generates birthday notifications
- Auto-cleanup old notifications

**Function: `createNotification()`**
- Create manual notification
- Parameters: type, title, message, member_id

**Function: `markNotificationAsRead()`**
- Mark notification as read

---

## 🧪 Testing Notifications

### Simulate Birthday Notification

**Add test member with birthday in 5 days:**

```php
// In any PHP file
require_once 'config.php';

$testDate = date('Y-m-d', strtotime('+5 days'));
$nama = 'Test Birthday Member';

// Insert member
$stmt = $conn->prepare("INSERT INTO members (nama, email, tanggal_lahir, status) 
                       VALUES (?, ?, ?, 'Aktif')");
$stmt->bind_param('sss', $nama, 'test@example.com', $testDate);
$stmt->execute();

// Refresh page to trigger generateBirthdayNotifications()
// Or run manually: generateBirthdayNotifications($conn);

// Check notifikasi.php for new notification
```

### Verify Notification Was Created

```sql
SELECT * FROM notifications 
WHERE type = 'birthday' 
ORDER BY created_at DESC LIMIT 1;
```

---

## 🐛 Troubleshooting

### Notification not appearing

**Check:**
1. Is table `notifications` created?
   ```sql
   SHOW TABLES LIKE 'notifications';
   ```

2. Is notification in database?
   ```sql
   SELECT COUNT(*) FROM notifications;
   ```

3. Is `is_read = 0`?
   ```sql
   SELECT * FROM notifications WHERE is_read = 0;
   ```

4. Check browser cache
   - Ctrl+Shift+Delete (clear cache)
   - Refresh page

### Duplicate birthday notifications

**Fix:** Already fixed in latest version  
- Before: Daily creation
- After: Created only 1x per birthday

### Badge not updating

**Check:**
1. Is AJAX working?
2. Check browser console for errors
3. Verify `api/get_notification_count.php` accessible

---

## 📝 Code Reference

### Create Notification

```php
createNotification(
    $conn,
    'birthday',  // type
    'Ulang Tahun Mendatang',  // title
    'Ulang tahun John dalam 5 hari',  // message
    123  // member_id
);
```

### Mark as Read

```php
markNotificationAsRead($conn, 456);  // notification_id
```

### Generate Birthday Notifications

```php
generateBirthdayNotifications($conn);
```

---

## 🎯 Best Practices

✅ Check notifications regularly  
✅ Mark important ones as unread for follow-up  
✅ Archive old notifications periodically  
✅ Review birthday list before each month  
✅ Customize notification messages if needed  

---

## 📚 Related Documentation

- [BIRTHDAY_NOTIFICATIONS.md](BIRTHDAY_NOTIFICATIONS.md) - Birthday optimization details
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues

---

**Last Updated:** November 2025
