# 🚀 PierceFlow - Ready for Production!

Sistem booking studio piercing dengan notifikasi WhatsApp otomatis yang siap di-deploy ke Railway.

## ✨ Fitur Utama

- 📅 **Booking System** - Studio & home service booking
- 💬 **WhatsApp Notifications** - Otomatis ke customer & admin
- 👤 **Guest Consultation** - Konsultasi tanpa registrasi
- 🛡️ **Admin Panel** - Manajemen booking, user, dan konfigurasi
- 📊 **Dashboard** - Statistik dan monitoring real-time
- 📱 **Responsive Design** - Mobile-friendly interface

## 🏗️ Teknologi

- **Backend**: PHP 8.0+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **WhatsApp**: Multi-provider API (Fonnte, Wablas, WooWA)
- **Deployment**: Railway Platform with auto-scaling
- **Database**: MySQL dengan migrasi otomatis

## 🚀 Quick Deploy ke Railway

### 1. One-Click Deploy
[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/template)

### 2. Manual Deploy
```bash
git clone https://github.com/yourusername/pierceflow.git
cd pierceflow
git push railway main
```

### 3. Environment Setup
Setelah deploy, tambahkan environment variables berikut:

```env
WHATSAPP_PROVIDER=fonnte
FONNTE_TOKEN=your_fonnte_token_here
PRODUCTION_MODE=true
WHATSAPP_PRODUCTION=true
```

### 4. Database Setup
Kunjungi `https://your-app.up.railway.app/includes/db.php?setup=db` untuk setup database otomatis.

## 📋 Default Login

**Admin Account:**
- Email: `admin@pierceflow.com`
- Password: `admin123`

⚠️ **Penting: Ganti password admin setelah login pertama!**

## 🔧 Konfigurasi WhatsApp API

1. Masuk ke admin panel
2. Klik "Notification Settings" 
3. Pilih provider WhatsApp (Fonnte/Wablas/WooWA)
4. Masukkan API token
5. Test kirim pesan
6. Aktifkan production mode

## 📁 Struktur File

```
pierceflow/
├── 📄 index.php           # Homepage
├── 📄 booking.php         # Booking system
├── 📄 admin.php          # Admin dashboard
├── 📄 health.php         # Health check endpoint
├── 📁 includes/
│   ├── db.php            # Database connection
│   ├── railway_database.php  # Railway DB config
│   └── production_whatsapp.php  # WhatsApp service
├── 📁 assets/            # CSS, JS, images
├── 🐳 railway.json       # Railway deployment config
├── 📋 Procfile           # Process definition
└── 📦 composer.json      # PHP dependencies
```

## 🎯 Cara Penggunaan

### Untuk Customer:
1. Kunjungi website
2. Pilih layanan piercing
3. Isi form booking
4. Terima konfirmasi via WhatsApp

### Untuk Guest Consultation:
1. Klik "Konsultasi"
2. Isi pertanyaan
3. Admin akan merespons via email/WhatsApp

### Untuk Admin:
1. Login ke admin panel
2. Monitor booking real-time
3. Konfirmasi/tolak booking
4. Kelola layanan dan user

## 📊 Monitoring

- **Health Check**: `/health.php`
- **Admin Dashboard**: `/admin.php`
- **Database Status**: Realtime di admin panel
- **WhatsApp Logs**: Admin notification settings

## 💰 Estimasi Biaya

- **Railway Hosting**: $5/month
- **Domain** (opsional): $10-15/year
- **WhatsApp API**: ~Rp 75/pesan
- **Total**: ~$5-7/month

## 🔒 Security Features

- Password hashing dengan bcrypt
- SQL injection protection
- XSS protection headers
- File upload validation
- Admin authentication
- Environment variable protection

## 📞 Support

Jika ada masalah:
1. Cek `/health.php` untuk status sistem
2. Periksa Railway logs
3. Verifikasi environment variables
4. Test koneksi database

## 🎉 Selamat!

Sistem PierceFlow Anda sudah siap digunakan! 🎊

**Next Steps:**
- [ ] Ganti password admin
- [ ] Setup WhatsApp API token
- [ ] Test booking end-to-end  
- [ ] Konfigurasi domain custom (opsional)
- [ ] Backup database berkala

---
**Made with ❤️ for professional piercing studios**