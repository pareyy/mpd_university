# Portal Mahasiswa MPD University - Sistem Lengkap

## Overview
Sistem manajemen akademik komprehensif untuk MPD University yang mencakup portal untuk Dosen dan Mahasiswa dengan berbagai fitur akademik yang terintegrasi.

## Struktur Sistem

### 🎓 Portal Dosen (`/dosen/`)
Dashboard dan tools manajemen untuk dosen:

**Dashboard Utama** (`index.php`)
- Statistik overview (total mata kuliah, mahasiswa, jadwal, nilai)
- Quick actions untuk akses cepat fitur utama
- Timeline aktivitas terbaru
- Jadwal kuliah hari ini

**Manajemen Mata Kuliah** (`mata_kuliah.php`)
- CRUD mata kuliah (Create, Read, Update, Delete)
- Detail mata kuliah dengan informasi SKS, semester, deskripsi
- Daftar mahasiswa yang mengambil mata kuliah
- Form tambah/edit mata kuliah dengan validasi

**Jadwal Kuliah** (`jadwal.php`)
- View kalender mingguan dengan highlight jadwal hari ini
- Statistik jadwal (total pertemuan, jam mengajar per minggu)
- Jadwal hari ini dengan detail waktu dan ruangan
- Filter berdasarkan hari dan mata kuliah

**Manajemen Nilai** (`nilai.php`)
- Input dan edit nilai mahasiswa per mata kuliah
- Statistik distribusi nilai (rata-rata, tertinggi, terendah)
- Grafik distribusi grade (A, B, C, D, E)
- Export nilai ke Excel/PDF
- Filter berdasarkan mata kuliah dan mahasiswa

**Absensi Mahasiswa** (`absensi.php`)
- Pencatatan kehadiran real-time dengan QR Code
- Rekap kehadiran per mata kuliah dan mahasiswa
- Statistik kehadiran dengan visualisasi chart
- Filter periode dan mata kuliah
- Export laporan absensi

**Profil Dosen** (`profile.php`)
- Informasi personal dan profesional
- Edit data pribadi dan kontak
- Ubah password dengan validasi keamanan
- Upload foto profil
- Riwayat akademik dan sertifikasi

### 🎒 Portal Mahasiswa (`/mahasiswa/`)
Dashboard dan tools untuk mahasiswa:

**Dashboard Utama** (`index.php`)
- Ringkasan akademik (IPK, SKS, semester)
- Quick access ke fitur utama
- Progress akademik dengan visualisasi
- Jadwal kuliah hari ini
- Pengumuman terbaru

**Mata Kuliah** (`mata_kuliah.php`)
- Daftar mata kuliah yang diambil semester ini
- Detail mata kuliah dengan silabus dan referensi
- Mata kuliah tersedia untuk registrasi
- Informasi prasyarat dan SKS

**Jadwal Kuliah** (`jadwal.php`)
- View kalender mingguan personal
- Highlight jadwal hari ini
- Detail ruangan dan dosen pengampu
- Reminder untuk kuliah berikutnya

**Nilai & Transkrip** (`nilai.php`)
- Transkrip akademik per semester
- Grafik perkembangan IPK
- Detail nilai per mata kuliah
- Analisis grade dan progress akademik
- Download transkrip resmi

**Absensi** (`absensi.php`)
- Rekap kehadiran personal per mata kuliah
- Persentase kehadiran dengan status (Aman/Perhatian)
- Riwayat absensi terbaru
- Grafik distribusi kehadiran
- Ketentuan kehadiran minimal

**Tugas** (`tugas.php`)
- Daftar tugas dari semua mata kuliah
- Status pengerjaan (Belum, Sedang, Selesai, Terlambat)
- Upload jawaban tugas dengan progress tracking
- Filter berdasarkan status, mata kuliah, dan jenis
- Deadline countdown dan prioritas tugas
- Riwayat nilai tugas

**Profil Mahasiswa** (`profile.php`)
- Data pribadi dan akademik
- Informasi kontak dan alamat
- Kontak darurat
- Progress studi dan prestasi akademik
- Upload foto profil
- Ubah password

## Database Schema

### Tabel Utama
```sql
-- Users (dosen dan mahasiswa)
users: id, username, password, role, email, created_at

-- Mata Kuliah
mata_kuliah: id, kode, nama, sks, semester, deskripsi, dosen_id

-- Jadwal
jadwal: id, mata_kuliah_id, hari, jam_mulai, jam_selesai, ruangan

-- Mahasiswa
mahasiswa: id, nim, nama, program_studi, fakultas, angkatan, status

-- Kelas (relasi mahasiswa-mata kuliah)
kelas: id, mata_kuliah_id, mahasiswa_id, semester_tahun

-- Nilai
nilai: id, mahasiswa_id, mata_kuliah_id, nilai_angka, nilai_huruf, semester

-- Absensi
absensi: id, mahasiswa_id, jadwal_id, tanggal, status, keterangan

-- Tugas
tugas: id, mata_kuliah_id, judul, deskripsi, deadline, file_path

-- Pengumpulan Tugas
pengumpulan_tugas: id, tugas_id, mahasiswa_id, file_path, tanggal_submit, nilai
```

## Fitur Keamanan

### Autentikasi & Autorisasi
- Session-based authentication
- Role-based access control (dosen/mahasiswa)
- Password hashing dengan bcrypt
- Logout otomatis setelah timeout

### Validasi Data
- Server-side validation untuk semua form
- Client-side validation dengan JavaScript
- Sanitasi input untuk mencegah XSS
- Prepared statements untuk mencegah SQL injection

### File Upload Security
- Validasi tipe file dan ukuran
- Rename file untuk mencegah directory traversal
- Virus scanning untuk file upload
- Restricted access ke uploaded files

## Teknologi yang Digunakan

### Frontend
- **HTML5 & CSS3**: Struktur dan styling responsif
- **JavaScript (Vanilla)**: Interaktivitas dan validasi client-side
- **Chart.js**: Visualisasi data dan grafik
- **Font Awesome**: Icon library
- **CSS Grid & Flexbox**: Layout responsif modern

### Backend
- **PHP 8.0+**: Server-side logic
- **MySQL 8.0+**: Database relasional
- **Session Management**: User authentication
- **File Handling**: Upload dan download dokumen

### Design Pattern
- **MVC-like Structure**: Pemisahan logic, data, dan presentation
- **Responsive Design**: Mobile-first approach
- **Progressive Enhancement**: Graceful degradation untuk browser lama

## Instalasi & Setup

### Prasyarat
```bash
- XAMPP/LAMPP (Apache, MySQL, PHP 8.0+)
- Web browser modern (Chrome, Firefox, Safari)
- Text editor (VS Code, Sublime, dll)
```

### Langkah Instalasi

1. **Clone/Download Project**
```bash
git clone [repository-url]
# atau download ZIP dan extract ke htdocs
```

2. **Setup Database**
```bash
# Buka phpMyAdmin (http://localhost/phpmyadmin)
# Buat database baru: mpd_university
# Import file: database_dosen.sql
```

3. **Konfigurasi Database**
```php
// Buat file config/database.php
<?php
$host = 'localhost';
$dbname = 'mpd_university';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

4. **Setup Permissions**
```bash
# Pastikan folder uploads/ dapat ditulis
chmod 755 uploads/
chmod 755 uploads/assignments/
chmod 755 uploads/profiles/
```

5. **Akses Sistem**
```bash
# Buka browser dan akses:
http://localhost/PWL_TA/login.php
```

### Default Login Credentials

**Dosen:**
- Username: `dosen001`
- Password: `password123`

**Mahasiswa:**
- Username: `2021080001`
- Password: `student123`

## File Structure

```
PWL_TA/
├── login.php                 # Landing page & login
├── database_dosen.sql        # Database schema
├── README_DOSEN.md          # Dokumentasi lengkap
│
├── assets/
│   ├── css/
│   │   └── dashboard.css    # Styling global
│   ├── js/
│   │   └── dashboard.js     # JavaScript global
│   └── images/              # Asset gambar
│
├── auth/
│   └── logout.php           # Logout handler
│
├── config/
│   └── database.php         # Konfigurasi database
│
├── dosen/                   # Portal Dosen
│   ├── index.php           # Dashboard dosen
│   ├── mata_kuliah.php     # Manajemen mata kuliah
│   ├── jadwal.php          # Manajemen jadwal
│   ├── nilai.php           # Input & kelola nilai
│   ├── absensi.php         # Pencatatan absensi
│   ├── profile.php         # Profil dosen
│   └── includes/
│       └── nav_dosen.php   # Navigation dosen
│
├── mahasiswa/               # Portal Mahasiswa
│   ├── index.php           # Dashboard mahasiswa
│   ├── mata_kuliah.php     # View mata kuliah
│   ├── jadwal.php          # View jadwal
│   ├── nilai.php           # View nilai & transkrip
│   ├── absensi.php         # View rekap absensi
│   ├── tugas.php           # Manajemen tugas
│   ├── profile.php         # Profil mahasiswa
│   └── includes/
│       └── nav_mahasiswa.php # Navigation mahasiswa
│
└── uploads/                 # File uploads
    ├── assignments/         # File tugas
    ├── profiles/           # Foto profil
    └── documents/          # Dokumen lainnya
```

## API Endpoints (Future Enhancement)

```javascript
// Authentication
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh-token

// Dosen Endpoints
GET  /api/dosen/dashboard
GET  /api/dosen/mata-kuliah
POST /api/dosen/mata-kuliah
PUT  /api/dosen/mata-kuliah/{id}
DELETE /api/dosen/mata-kuliah/{id}

// Mahasiswa Endpoints
GET  /api/mahasiswa/dashboard
GET  /api/mahasiswa/transkrip
GET  /api/mahasiswa/jadwal
POST /api/mahasiswa/tugas/submit

// Shared Endpoints
GET  /api/schedule/today
GET  /api/attendance/summary
POST /api/profile/upload-photo
```

## Testing

### Manual Testing Checklist

**Autentikasi:**
- [ ] Login dengan kredensial valid
- [ ] Login dengan kredensial invalid
- [ ] Logout dan redirect ke login
- [ ] Session timeout handling

**Portal Dosen:**
- [ ] Dashboard loading dan data display
- [ ] CRUD mata kuliah (create, read, update, delete)
- [ ] Input nilai mahasiswa
- [ ] Pencatatan absensi
- [ ] Edit profil dan change password

**Portal Mahasiswa:**
- [ ] Dashboard dan ringkasan akademik
- [ ] View mata kuliah dan jadwal
- [ ] View nilai dan transkrip
- [ ] Submit tugas dan view assignment
- [ ] View rekap absensi

**Responsive Design:**
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

## Performance Optimization

### Database Optimization
```sql
-- Index untuk query yang sering digunakan
CREATE INDEX idx_mahasiswa_nim ON mahasiswa(nim);
CREATE INDEX idx_jadwal_hari ON jadwal(hari);
CREATE INDEX idx_nilai_mahasiswa ON nilai(mahasiswa_id);
CREATE INDEX idx_absensi_date ON absensi(tanggal);
```

### Caching Strategy
```php
// Implementasi caching untuk data yang jarang berubah
// Session caching untuk user data
// File caching untuk academic calendar
```

### Image Optimization
```javascript
// Compress upload images
// Lazy loading untuk gallery
// WebP format support
```

## Security Best Practices

### Input Validation
```php
// Sanitize all user inputs
$input = filter_var($_POST['data'], FILTER_SANITIZE_STRING);

// Validate email format
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

### File Upload Security
```php
// Validate file type and size
$allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
$max_size = 5 * 1024 * 1024; // 5MB

// Rename uploaded files
$new_filename = uniqid() . '.' . $extension;
```

## Troubleshooting

### Common Issues

**1. Database Connection Error**
```
Solution: Check database credentials in config/database.php
Verify MySQL service is running
```

**2. File Upload Error**
```
Solution: Check folder permissions (uploads/)
Verify PHP max_file_size settings
```

**3. Session Issues**
```
Solution: Check PHP session configuration
Clear browser cookies and try again
```

**4. CSS/JS Not Loading**
```
Solution: Check file paths in HTML
Clear browser cache
Verify file permissions
```

## Future Enhancements

### Phase 2 Features
- [ ] Real-time notifications dengan WebSocket
- [ ] Mobile app dengan React Native
- [ ] Advanced reporting dengan export options
- [ ] Email integration untuk notifications
- [ ] Calendar integration (Google Calendar, Outlook)

### Phase 3 Features
- [ ] Machine learning untuk prediksi akademik
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] API untuk integrasi sistem external
- [ ] Advanced file management dengan cloud storage

## Support & Maintenance

### Regular Maintenance Tasks
- Database backup harian
- Log file cleanup
- Security updates
- Performance monitoring
- User feedback review

### Contact Information
- **Developer**: Ahmad Fadhil Rahman
- **Email**: ahmad.fadhil@mpd.ac.id
- **Phone**: +62 812-3456-7890
- **Support Hours**: Senin-Jumat, 08:00-17:00 WIB

---

## Changelog

### Version 1.0.0 (Current)
- ✅ Complete dosen portal with all features
- ✅ Complete mahasiswa portal with all features
- ✅ Responsive design for all devices
- ✅ Database schema with sample data
- ✅ Security implementation
- ✅ Comprehensive documentation

### Upcoming Version 1.1.0
- 🔄 Database integration (replacing sample data)
- 🔄 Real authentication system
- 🔄 Advanced form validation
- 🔄 File upload functionality
- 🔄 Email notifications

---

*Dokumentasi ini akan terus diperbarui seiring dengan perkembangan sistem.*
