# MPD University - Portal Akademik

![MPD University Logo](assets/img/logo.png)

Portal akademik modern untuk mengelola sistem informasi universitas dengan antarmuka yang responsif dan user-friendly.

## 📋 Daftar Isi

-   [Fitur Utama](#fitur-utama)
-   [Teknologi yang Digunakan](#teknologi-yang-digunakan)
-   [Persyaratan Sistem](#persyaratan-sistem)
-   [Instalasi](#instalasi)
-   [Konfigurasi](#konfigurasi)
-   [Penggunaan](#penggunaan)
-   [Struktur Project](#struktur-project)
-   [User Roles](#user-roles)
-   [Fitur UI/UX](#fitur-uiux)
-   [Keamanan](#keamanan)
-   [Mobile Responsiveness](#mobile-responsiveness)
-   [Troubleshooting](#troubleshooting)
-   [Kontribusi](#kontribusi)
-   [Lisensi](#lisensi)
-   [Developer](#developer)
-   [Acknowledgments](#acknowledgments)

## 🚀 Fitur Utama

### Portal Utama

-   Landing page dengan animasi modern
-   Informasi akademik dan pengumuman
-   Profil universitas lengkap
-   Responsive design untuk semua perangkat

### Dashboard Admin

-   Manajemen pengguna (mahasiswa & dosen)
-   Kelola mata kuliah dan jadwal
-   Sistem laporan akademik
-   Pengaturan sistem dan notifikasi
-   Backup dan restore database

### Dashboard Dosen

-   Manajemen mata kuliah yang diampu
-   Input dan kelola nilai mahasiswa
-   Jadwal mengajar personal
-   Profil dosen dengan avatar customizable

### Dashboard Mahasiswa

-   Informasi mata kuliah yang diambil
-   Transkrip nilai dan IPK
-   Jadwal kuliah personal
-   Manajemen tugas dan deadline
-   Sistem absensi

## 🛠 Teknologi yang Digunakan

### Backend

-   **PHP 7.4+** - Server-side scripting
-   **MySQL** - Database management
-   **Apache/Nginx/Xampp** - Web server

### Frontend

-   **HTML5** - Markup structure
-   **CSS3** - Styling dengan modern features
-   **JavaScript (ES6+)** - Interactive functionality
-   **Font Awesome 6.4.0** - Icon library
-   **Remixicon** - Additional icons
-   **Typed.js** - Text animation effects

### Framework & Libraries

-   **Responsive Grid System** - Custom CSS Grid
-   **Mobile-First Design** - Progressive enhancement
-   **SVG Animations** - Smooth blob animations

## 📋 Persyaratan Sistem

-   **Web Server**: Apache 2.4+ atau Nginx 1.18+
-   **PHP**: 7.4 atau lebih tinggi
-   **Database**: MySQL 5.7+ atau MariaDB 10.3+
-   **Browser**: Chrome 80+, Firefox 75+, Safari 13+, Edge 80+

## 📦 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/mpd-university.git
cd mpd-university
```

### 2. Setup Web Server

```bash
# Untuk XAMPP
cp -r mpd-university/ /xampp/htdocs/

# Untuk LAMP/LEMP
cp -r mpd-university/ /var/www/html/
```

### 3. Buat Database

```sql
CREATE DATABASE mpd_university;
USE mpd_university;

-- Import struktur database
SOURCE database/mpd_university.sql;
```

### 4. Konfigurasi Database

Edit file `koneksi.php`:

```php
$host = "localhost";
$username = "root";
$password = "your_password";
$database = "mpd_university";
```

### 5. Set Permissions

```bash
chmod 755 assets/uploads/
chmod 644 *.php
```

## ⚙️ Konfigurasi

### Database Setup

1. Import file SQL yang disediakan
2. Sesuaikan koneksi database di `koneksi.php`
3. Pastikan user database memiliki privilege yang cukup

### Default User Accounts

```
Admin:
- Username: admin
- Password: admin123

Dosen:
- Username: dosen
- Password: dosen123

Mahasiswa:
- Username: mahasiswa
- Password: mahasiswa123
```

## 🎯 Penggunaan

### Akses Portal

```
http://localhost/mpd_university/
```

### Login System

1. Kunjungi halaman login: `/login.php`
2. Masukkan credentials sesuai role
3. Sistem akan redirect ke dashboard yang sesuai

### Admin Dashboard

-   URL: `/admin/index.php`
-   Kelola semua aspek sistem
-   Monitor aktivitas pengguna
-   Generate laporan

### Dosen Dashboard

-   URL: `/dosen/index.php`
-   Kelola mata kuliah
-   Input nilai mahasiswa
-   Update jadwal mengajar

### Mahasiswa Dashboard

-   URL: `/mahasiswa/index.php`
-   Lihat jadwal dan tugas
-   Cek transkrip nilai
-   Update profil

## 📁 Struktur Project

```
mpd_university/
├── admin/                  # Admin dashboard & functions
│   ├── index.php          # Admin homepage
│   ├── mahasiswa.php      # Student management
│   ├── dosen.php          # Lecturer management
│   ├── mata_kuliah.php    # Course management
│   ├── pengaturan.php     # System settings
│   ├── article.php        # Article management
│   └── includes/          # Admin navigation & components
├── dosen/                 # Lecturer dashboard
│   ├── index.php          # Lecturer homepage
│   ├── mata_kuliah.php    # Course management
│   ├── profile.php        # Profile management
│   └── includes/          # Lecturer navigation
├── mahasiswa/             # Student dashboard
│   ├── index.php          # Student homepage
│   ├── mata_kuliah.php    # Course info
│   ├── nilai.php          # Grades & transcript
│   ├── tugas.php          # Assignments
│   └── includes/          # Student navigation
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── img/              # Images & logos
│   └── uploads/          # User uploaded files
├── auth/                  # Authentication system
│   └── process_login.php  # Login handler
├── includes/              # Shared components
│   ├── nav.php           # Main navigation
│   └── footer.php        # Footer component
├── index.php             # Homepage
├── login.php             # Login page
├── akademik.php          # Academic information
├── profile.php           # University profile
└── koneksi.php           # Database connection
```

## 👥 User Roles

### 🔧 Administrator

**Akses Penuh Sistem**

-   Dashboard analytics
-   User management (CRUD)
-   Course & schedule management
-   System settings & backup
-   Report generation
-   Article & announcement management

### 👨‍🏫 Dosen (Lecturer)

**Academic Management**

-   Personal dashboard
-   Course management
-   Grade input & management
-   Student attendance
-   Profile customization
-   Schedule viewing

### 🎓 Mahasiswa (Student)

**Academic Information**

-   Personal dashboard
-   Course enrollment info
-   Grade viewing & transcript
-   Assignment management
-   Schedule viewing
-   Profile management

## 🎨 Fitur UI/UX

### Responsive Design

-   Mobile-first approach
-   Tablet & desktop optimization
-   Touch-friendly interface
-   Adaptive navigation

### Modern Interface

-   Clean & minimal design
-   Smooth animations
-   Interactive elements
-   Consistent color scheme
-   Professional typography

### Accessibility

-   Semantic HTML structure
-   Keyboard navigation support
-   Screen reader friendly
-   High contrast ratios

## 🔒 Keamanan

### Authentication

-   Session-based authentication
-   Password hashing (PHP password_hash)
-   Role-based access control
-   Login attempt validation

### Data Protection

-   SQL injection prevention
-   XSS protection
-   CSRF token implementation
-   Input sanitization

## 📱 Mobile Responsiveness

### Breakpoints

```css
/* Mobile */
@media (max-width: 480px) /* Tablet */ @media (max-width: 768px) /* Desktop */ @media (max-width: 1024px) /* Large Desktop */ @media (min-width: 1200px);
```

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**

```php
// Check koneksi.php settings
// Verify MySQL service is running
// Confirm database exists
```

**Session Problems**

```php
// Ensure session_start() is called
// Check session directory permissions
```

## 🤝 Kontribusi

Kami menyambut kontribusi dari developer lain! Ikuti langkah berikut:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b fitur/fiturkeren`)
3. Commit perubahan (`git commit -m 'Add some fiturkeren'`)
4. Push ke branch (`git push origin fitur/fiturkeren`)
5. Buat Pull Request

## 📄 Lisensi

Project ini dilisensikan under MIT License - lihat file [LICENSE](LICENSE) untuk detail.

## 👨‍💻 Developer

**MPD University Development Team**

-   Fachri Akbar Alghifari
-   Satrio Baskoro
-   Tobi Saputra
-   Muhammad Rafly Maulana
-   Muhammad Iihab Wahyudin
-   Aprillia Mahardika
-   Annisa Lika Salsabila

## 🙏 Acknowledgments

-   Font Awesome untuk icon library
-   Typed.js untuk text animations
-   Remixicon untuk additional icons
-   CSS-Tricks untuk responsive design inspiration

---
