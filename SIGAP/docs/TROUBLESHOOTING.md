# TROUBLESHOOTING GUIDE

## 🔴 Common Issues & Solutions

---

## 🔐 Login Issues

### ❌ Error: "Username atau password salah"

**Possible Causes:**
1. Wrong credentials
2. Account doesn't exist
3. Database connection issue

**Solutions:**
```bash
# 1. Verify admin account exists
mysql -u root -D member_data -e "SELECT * FROM users WHERE username = 'admin';"

# 2. Reset admin password
php setup_admin.php

# 3. Check database connection in config.php
# Verify DB_HOST, DB_USER, DB_PASS, DB_NAME
```

### ❌ Error: "Terjadi kesalahan pada server"

**Possible Causes:**
1. Database connection failed
2. Missing config.php
3. PHP errors

**Solutions:**
1. Check `config.php` exists and is readable
2. Verify MySQL is running: `services.msc`
3. Check PHP error log: `C:\xampp\php\logs\php_error.log`

### ❌ White screen after login

**Possible Causes:**
1. PHP fatal error
2. Missing includes
3. Session issue

**Solutions:**
```bash
# 1. Check for PHP errors
tail -f C:\xampp\php\logs\php_error.log

# 2. Verify includes folder
# Make sure includes/functions.php, header.php, footer.php exist

# 3. Clear browser cache
# Ctrl+Shift+Delete in browser
```

---

## 📊 Database Issues

### ❌ Error: "SQLSTATE[HY000]: General error: 1030"

**Possible Causes:**
1. Table not found
2. Database not imported
3. Schema mismatch

**Solutions:**
```bash
# 1. Check if database exists
mysql -u root -e "SHOW DATABASES LIKE 'member_data';"

# 2. Check if tables exist
mysql -u root -D member_data -e "SHOW TABLES;"

# 3. Re-import if needed
mysql -u root member_data < database_with_seed.sql

# 4. If already exists, drop first
mysql -u root -e "DROP DATABASE member_data;"
mysql -u root member_data < database_with_seed.sql
```

### ❌ Error: "Access denied for user 'root'@'localhost'"

**Possible Causes:**
1. MySQL not running
2. Wrong root password
3. MySQL permissions issue

**Solutions:**
```bash
# 1. Start MySQL
net start MySQL80
# or
# Use XAMPP Control Panel: Start MySQL

# 2. Test connection
mysql -u root -e "SELECT 1;"

# 3. If password protected, add to config.php
// In config.php
define('DB_PASS', 'your_password_here');

# 4. Reset root password (if locked out)
# Stop MySQL and restart with --skip-grant-tables
# Then use mysql_upgrade utility
```

### ❌ Error: "Table 'member_data.users' doesn't exist"

**Possible Causes:**
1. Database imported but users table missing
2. Schema version mismatch
3. Partial import

**Solutions:**
```bash
# 1. Verify all tables
mysql -u root -D member_data -e "SHOW TABLES;"

# 2. Create users table manually if missing
mysql -u root -D member_data << 'EOF'
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF

# 3. Re-import database completely
mysql -u root -e "DROP DATABASE member_data;"
mysql -u root member_data < database_with_seed.sql
```

---

## 🔔 Notification Issues

### ❌ Notifications not appearing

**Possible Causes:**
1. notifications table not created
2. generateBirthdayNotifications() not called
3. Birthday date not set on member

**Solutions:**
```bash
# 1. Check notifications table exists
mysql -u root -D member_data -e "SHOW TABLES LIKE 'notifications';"

# 2. Check notifications have data
mysql -u root -D member_data -e "SELECT COUNT(*) FROM notifications;"

# 3. Verify member has birthday set
mysql -u root -D member_data -e "SELECT id, nama, tanggal_lahir FROM members WHERE tanggal_lahir IS NOT NULL;"

# 4. Manually trigger function
# Open any page to trigger generateBirthdayNotifications()
```

### ❌ Duplicate notifications

**Status:** ✅ FIXED in latest version

If you're seeing duplicates:
```bash
# Clear old duplicates
mysql -u root -D member_data << 'EOF'
DELETE FROM notifications 
WHERE id NOT IN (
    SELECT MAX(id) FROM notifications 
    WHERE type = 'birthday' 
    GROUP BY member_id
);
EOF

# Make sure you're on latest code
# Version 1.0 has the fix built-in
```

### ❌ Notification badge not updating

**Possible Causes:**
1. AJAX not working
2. API endpoint issue
3. Browser cache

**Solutions:**
```bash
# 1. Check API endpoint
curl http://localhost/rey/api/get_notification_count.php

# 2. Clear browser cache
# Ctrl+Shift+Delete

# 3. Check JavaScript errors
# Press F12 → Console tab → Look for red errors

# 4. Verify notifications in database
mysql -u root -D member_data -e "SELECT COUNT(*) as unread FROM notifications WHERE is_read = 0;"
```

---

## 👤 Member Management Issues

### ❌ Cannot add/edit members

**Possible Causes:**
1. Permission denied
2. Database error
3. Validation error

**Solutions:**
```bash
# 1. Check user role
mysql -u root -D member_data -e "SELECT username, role FROM users WHERE username = 'current_user';"

# 2. Check members table permissions
mysql -u root -D member_data -e "SHOW TABLE STATUS LIKE 'members';"

# 3. Check for error messages
# Look at browser console (F12)
# Check PHP error log
```

### ❌ Search/Filter not working

**Possible Causes:**
1. JavaScript issue
2. Form not submitting
3. SQL error

**Solutions:**
```bash
# 1. Check JavaScript console for errors (F12)

# 2. Test SQL manually
mysql -u root -D member_data << 'EOF'
SELECT * FROM members 
WHERE nama LIKE '%search_term%' 
OR email LIKE '%search_term%'
LIMIT 10;
EOF

# 3. Clear form and try again
# Or try different search term
```

---

## 📁 File Permission Issues

### ❌ "Permission denied" errors

**Possible Causes:**
1. Read-only file permissions
2. Folder not writable
3. Apache user doesn't have access

**Solutions:**
```bash
# 1. Check folder permissions
dir "C:\xampp\htdocs\rey" /s

# 2. Make folder writable
# Right-click folder → Properties → Security
# Add full permissions for SYSTEM and Users

# 3. For specific files
attrib -R config.php
attrib -R database_with_seed.sql

# 4. If needed, restart Apache
# XAMPP Control Panel → Stop/Start Apache
```

---

## 🌐 Connection Issues

### ❌ "Connection refused"

**Possible Causes:**
1. XAMPP not running
2. Apache not started
3. Wrong URL

**Solutions:**
```bash
# 1. Start XAMPP
# Open C:\xampp\xampp-control.exe
# Click "Start" for Apache and MySQL

# 2. Verify Apache running
netstat -ano | findstr :80

# 3. Try different URL
http://localhost/rey/login.php
http://127.0.0.1/rey/login.php

# 4. Check firewall
# Allow Apache through firewall
```

### ❌ "Unable to connect to server"

**Possible Causes:**
1. Typo in URL
2. File not in right location
3. Apache configuration issue

**Solutions:**
```bash
# 1. Verify file location
# Should be in C:\xampp\htdocs\rey\

# 2. Check Apache config
# C:\xampp\apache\conf\httpd.conf
# Verify DocumentRoot is correct

# 3. Try accessing phpMyAdmin
# If this works, web server is OK
http://localhost/phpmyadmin/

# 4. Restart Apache and MySQL
# XAMPP Control Panel → Stop All → Start All
```

---

## 💻 Performance Issues

### ❌ Page loading very slow

**Possible Causes:**
1. Large dataset
2. Database query issue
3. Server resources low

**Solutions:**
```bash
# 1. Check database size
mysql -u root -D member_data -e "SHOW TABLE STATUS;"

# 2. Optimize tables
mysql -u root -D member_data -e "OPTIMIZE TABLE members, notifications, activity_logs;"

# 3. Check running processes
tasklist | findstr php

# 4. Restart XAMPP
# XAMPP Control Panel → Stop All → Start All

# 5. Try pagination
# Change items per page in admin panel
```

### ❌ High CPU/Memory usage

**Possible Causes:**
1. Infinite loop
2. Large query result
3. Multiple PHP processes

**Solutions:**
```bash
# 1. Stop all PHP processes
taskkill /F /IM php.exe

# 2. Check error logs
type C:\xampp\apache\logs\error.log

# 3. Restart XAMPP carefully
# Watch resource monitor while restarting

# 4. If persistent, check for infinite loops in code
```

---

## 🧪 Testing & Verification

### Verify Installation

```bash
# 1. Check database
mysql -u root -D member_data -e "SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM members;"

# 2. Check files
dir C:\xampp\htdocs\rey\*.php

# 3. Test login
# http://localhost/rey/login.php
# Try: username=admin, password=admin123

# 4. Test database
# http://localhost/phpmyadmin/
```

### Debug Mode

**Enable in config.php:**
```php
// Set to true for development
define('DEV_BYPASS_LOGIN', false);

// Check error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📞 Getting More Help

### Check Log Files

```bash
# Apache error log
type C:\xampp\apache\logs\error.log

# PHP error log
type C:\xampp\php\logs\php_error.log

# XAMPP log
type C:\xampp\xampp.log
```

### Key Diagnostic Commands

```bash
# Test MySQL
mysql -u root -e "SELECT VERSION();"

# Test PHP
php -v

# Test Apache status
netstat -ano | findstr :80

# List XAMPP services
services.msc
```

---

## ✅ Quick Checklist

- [ ] XAMPP running (Apache + MySQL)
- [ ] Database imported successfully
- [ ] Admin account created
- [ ] Can login with admin/admin123
- [ ] Dashboard loads without errors
- [ ] Members page accessible
- [ ] Notifications showing
- [ ] Can add/edit/delete members
- [ ] Search/filter working
- [ ] Activity logs recording

---

**Last Updated:** November 2025

**Still having issues?** Check documentation in `/docs` folder or review error logs above.
