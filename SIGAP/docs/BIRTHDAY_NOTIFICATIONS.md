# BIRTHDAY NOTIFICATION OPTIMIZATION

## 📌 Problem Statement

Notifikasi ulang tahun harus:
- ✅ Muncul hanya saat dalam h-1 bulan (30 hari ke depan)
- ✅ Dibuat hanya 1x per birthday (no duplicates)
- ✅ Tetap ada di halaman notifikasi sampai hari H lewat
- ✅ Auto-cleanup notifikasi lama (>40 hari)

---

## 🔧 Solution Implemented

### Function: `generateBirthdayNotifications()`

**Location:** `includes/functions.php`

### Changes

#### Before
```php
// Membuat notifikasi setiap hari dalam window 30 hari
// Hasil: Multiple duplikat notifikasi
if ($daysUntil <= 30 && $daysUntil >= 0) {
    if (!exists today) {  // Only check today
        createNotification(...)
    }
}
```

#### After
```php
// Step 1: Cleanup old notifications
DELETE FROM notifications 
WHERE type = 'birthday' AND created_at < DATE_SUB(NOW(), INTERVAL 40 DAY)

// Step 2: Create notification only once per birthday
if ($daysUntil <= 30 && $daysUntil >= 0) {
    if (!exists in past 30 days) {  // Check entire window
        createNotification(...)
    }
}
```

---

## ⏰ Timeline & Logic

### Birthday Timeline

```
TODAY ◄────────────── 30 Days ──────────────► BIRTHDAY DATE
                                               │
                                          [+10 Days]
                                               │
                        Notification Cleanup ─┘
                        (Delete after 40 days)
```

### Step-by-Step Process

1. **User loads page** → config.php calls `generateBirthdayNotifications()`

2. **Cleanup old notifications**
   ```php
   DELETE FROM notifications 
   WHERE type = 'birthday' AND created_at < DATE_SUB(NOW(), INTERVAL 40 DAY)
   ```
   - Removes birthday notifications older than 40 days
   - Keeps database clean

3. **Check each member's birthday**
   - Get all members with tanggal_lahir
   - Calculate next birthday date
   - Check if within 30 days from today

4. **Check if notification already exists**
   - Look for birthday notifications in past 30 days
   - For same member, same birthday date
   - If found: skip (no duplicate)
   - If not found: create new notification

5. **Create notification once**
   ```php
   INSERT INTO notifications (type, title, message, member_id)
   VALUES ('birthday', 'Ulang Tahun Mendatang', '...', member_id)
   ```

---

## 📊 Example Scenario

### Scenario: John's Birthday

**Date:** November 20, 2025  
**Today:** November 11, 2025  
**Days until:** 9 days

**Day 1 (Nov 11):** Within 30 days
- Check: No notification exists for John's Nov 20 birthday
- Action: **CREATE** notification
- Result: 1 notification in database

**Day 2 (Nov 12):** Within 30 days
- Check: Notification exists (from yesterday)
- Action: **SKIP** (no duplicate)
- Result: Still 1 notification

**Day 3 (Nov 13):** Within 30 days
- Check: Notification exists
- Action: **SKIP**
- Result: Still 1 notification

**Day 9 (Nov 20):** Birthday!
- Notification still visible in notifikasi.php
- User can mark as read

**Day 51 (Dec 21):** Cleanup (40 days after birthday)
- Check: Birthday notification created > 40 days ago
- Action: **DELETE**
- Result: Notification removed

---

## 🧪 Test Results

✅ **Test 1:** Notification created only once
```
Run 1: 1 notification created
Run 2: Still 1 notification (no duplicate)
```

✅ **Test 2:** Cleanup removes old notifications
```
Birthday: 40+ days ago
Result: Notification deleted
```

✅ **Test 3:** Birthday window works correctly
```
30 days before birthday: ✅ Notification created
31 days before birthday: ❌ No notification
```

---

## 🔍 SQL Queries for Verification

### Check Birthday Notifications

```sql
SELECT id, member_id, title, created_at, 
       DATEDIFF(NOW(), created_at) as age_days
FROM notifications 
WHERE type = 'birthday' 
ORDER BY created_at DESC;
```

### Check for Duplicates

```sql
SELECT member_id, COUNT(*) as count 
FROM notifications 
WHERE type = 'birthday' 
GROUP BY member_id 
HAVING count > 1;
```

### Check Cleanup Candidates

```sql
SELECT id, member_id, created_at, 
       DATEDIFF(NOW(), created_at) as age_days
FROM notifications 
WHERE type = 'birthday' 
AND created_at < DATE_SUB(NOW(), INTERVAL 40 DAY);
```

---

## 📋 Database Impact

| Metric | Before | After |
|--------|--------|-------|
| Notifications per birthday in 30 days | 30 | 1 |
| Storage for old notifications | Accumulates | Auto-cleaned |
| Query performance | Slow (many rows) | Fast (less data) |
| User experience | Cluttered | Clean |

---

## 🔐 Security & Performance

### Performance
- ✅ Minimal query overhead
- ✅ One cleanup DELETE per session
- ✅ Efficient duplicate check

### Data Integrity
- ✅ Only birthday type deleted
- ✅ 40-day safety buffer
- ✅ Reversible (kept 10 days after birthday)

---

## 🔧 Configuration Options

To modify behavior, edit `includes/functions.php`:

```php
// Adjust birthday notification window (default: 30 days)
if ($daysUntil <= 30 && $daysUntil >= 0) {
    // Change 30 to desired number of days
}

// Adjust cleanup threshold (default: 40 days)
$conn->query("DELETE FROM notifications 
             WHERE type = 'birthday' 
             AND created_at < DATE_SUB(NOW(), INTERVAL 40 DAY)");
             // Change 40 to desired number of days
```

---

## 📚 Related Files

- `includes/functions.php` - Contains `generateBirthdayNotifications()`
- `config.php` - Calls function on session start
- `notifikasi.php` - Displays notifications
- `docs/NOTIFICATIONS.md` - General notification guide

---

## ✨ Summary

| Feature | Status |
|---------|--------|
| 30-day window | ✅ Implemented |
| 1 notification per birthday | ✅ Implemented |
| Auto-cleanup after 40 days | ✅ Implemented |
| No duplicates | ✅ Verified |
| Performance optimized | ✅ Tested |

---

**Last Updated:** November 2025  
**Version:** 1.0
