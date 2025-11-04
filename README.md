# PierceFlow - Website Reservasi Piercing

Website reservasi piercing profesional menggunakan PHP murni dan MySQL.

## 🚀 Fitur

- **Autentikasi User**: Register, login, dan logout dengan session management
- **Manajemen Layanan**: Daftar layanan piercing dengan harga
- **Sistem Booking**: Reservasi online dengan validasi konflik jadwal
- **Dashboard User**: Melihat riwayat dan membatalkan booking
- **Admin Panel**: Mengelola semua booking dan melihat statistik
- **Desain Responsif**: Tampilan modern dengan CSS murni

## 📋 Persyaratan

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- XAMPP/Laragon atau web server lain
- Browser modern (Chrome, Firefox, Edge)

## 🛠️ Instalasi

1. **Clone atau download project ini** ke folder htdocs (XAMPP) atau www (Laragon)

2. **Import database**:
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Import file `database.sql`
   - Atau jalankan query dalam file tersebut secara manual

3. **Konfigurasi database** (jika perlu):
   - Edit file `includes/db.php`
   - Sesuaikan username, password, dan nama database

4. **Jalankan aplikasi**:
   - Akses http://localhost/PKPL%20PHP/
   - Atau jalankan: `php -S localhost:8000`

## 👤 Akun Demo

**Admin**:
- Email: `admin@pierceflow.local`
- Password: `admin123`

**User**: Daftar melalui halaman register

## 📁 Struktur File

```
PKPL PHP/
├── index.php              # Halaman utama
├── services.php           # Daftar layanan
├── booking.php            # Form reservasi
├── login.php              # Login
├── register.php           # Registrasi
├── logout.php             # Logout
├── dashboard.php          # Dashboard user
├── admin.php              # Admin panel
├── database.sql           # Schema database
├── includes/
│   ├── db.php            # Koneksi database
│   ├── header.php        # Header/navbar
│   └── footer.php        # Footer
└── assets/
    ├── style.css         # CSS utama
    └── script.js         # JavaScript
```

## 🎨 Teknologi

- **Backend**: PHP 7.4+ (murni, tanpa framework)
- **Database**: MySQL dengan MySQLi
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Desain**: Custom CSS dengan warna ungu, putih, dan abu-abu

## 📝 Catatan

- Semua password di-hash menggunakan `password_hash()`
- Validasi form di sisi client (JavaScript) dan server (PHP)
- Proteksi halaman dengan session checking
- Konflik jadwal dicek otomatis saat booking
- Status booking: pending, confirmed, cancelled, rejected

## 🔧 Troubleshooting

**Database connection error**:
- Pastikan MySQL service sudah running
- Cek kredensial di `includes/db.php`
- Pastikan database `pierceflow_db` sudah dibuat

**Page not found**:
- Pastikan file ada di folder yang benar
- Cek URL path sesuai lokasi folder

**Session tidak bekerja**:
- Pastikan `session_start()` dipanggil
- Cek konfigurasi PHP session

## 📞 Support

Untuk bantuan atau pertanyaan, silakan buat issue atau hubungi developer.

## 📄 License

Project ini dibuat untuk keperluan pembelajaran.

---
© 2025 PierceFlow. All rights reserved.
