# Sistem Dashboard Dosen - MPD University

Sistem manajemen akademik khusus untuk dosen yang memungkinkan pengelolaan mata kuliah, jadwal, nilai, dan absensi mahasiswa.

## 🚀 Fitur Utama

### 1. Dashboard Utama
- **Statistik Overview**: Menampilkan ringkasan mata kuliah, total mahasiswa, jadwal hari ini, dan tugas pending
- **Aksi Cepat**: Akses langsung ke fitur-fitur utama
- **Aktivitas Terbaru**: Timeline aktivitas terkini
- **Jadwal Mendatang**: Preview jadwal mengajar

### 2. Manajemen Mata Kuliah (`mata_kuliah.php`)
- ➕ Tambah mata kuliah baru
- 📝 Edit informasi mata kuliah
- 🗑️ Hapus mata kuliah
- 📋 Daftar lengkap mata kuliah yang diampu
- 🔍 Filter dan pencarian

### 3. Jadwal Mengajar (`jadwal.php`)
- 📅 **Jadwal Mingguan**: Grid view jadwal per hari
- 📍 **Jadwal Hari Ini**: Detail jadwal dengan aksi cepat
- 📊 **Statistik Jadwal**: Total jam mengajar, mahasiswa, dan ruang
- 🕒 Informasi waktu, ruang, dan jumlah mahasiswa

### 4. Input Nilai (`nilai.php`)
- 🎯 **Filter Mata Kuliah**: Pilih mata kuliah untuk input nilai
- 📊 **Input Nilai Komprehensif**: 
  - Tugas 1 (20%)
  - Tugas 2 (20%) 
  - UTS (30%)
  - UAS (30%)
- 🔄 **Kalkulasi Otomatis**: Nilai akhir dan grade
- 📈 **Statistik Nilai**: Distribusi grade dan rata-rata kelas

### 5. Manajemen Absensi (`absensi.php`)
- ✅ **Absensi Hari Ini**: Interface untuk mencatat kehadiran
- 📋 **Tiga Status**: Hadir, Tidak Hadir, Izin
- 📊 **Statistik Real-time**: Persentase kehadiran langsung
- 📚 **Riwayat Absensi**: History absensi per mata kuliah
- 🔄 **Aksi Bulk**: Tandai semua hadir sekaligus

### 6. Profil Dosen (`profile.php`)
- 👤 **Informasi Profil**: Data lengkap dosen
- 🔧 **Edit Profil**: Update informasi personal
- 🔐 **Ubah Password**: Keamanan akun
- 📊 **Statistik Personal**: Mata kuliah dan mahasiswa diampu

## 🛠️ Struktur File

```
dosen/
├── index.php              # Dashboard utama
├── mata_kuliah.php         # Manajemen mata kuliah
├── jadwal.php             # Jadwal mengajar
├── nilai.php              # Input dan kelola nilai
├── absensi.php            # Manajemen absensi
├── profile.php            # Profil dosen
└── includes/
    └── nav_dosen.php      # Navigasi khusus dosen
```

## 🎨 Fitur UI/UX

### Responsive Design
- 📱 **Mobile-First**: Tampilan optimal di semua perangkat
- 🖥️ **Desktop-Optimized**: Layout grid yang efisien
- 📊 **Interactive Charts**: Visualisasi data yang menarik

### Modern Interface
- 🎨 **Gradient Design**: Warna modern dengan gradasi
- 💫 **Smooth Animations**: Transisi halus untuk UX yang baik
- 🔍 **Clear Typography**: Font yang mudah dibaca
- 📋 **Card-based Layout**: Organisasi konten yang terstruktur

### Navigation
- 🧭 **Sticky Navigation**: Navigasi tetap terlihat saat scroll
- 🍔 **Mobile Menu**: Hamburger menu untuk perangkat mobile
- 🔄 **Active States**: Indikator halaman aktif
- 🚪 **Quick Logout**: Tombol logout yang mudah diakses

## 📊 Database Schema

### Tabel Utama:
- `mata_kuliah` - Data mata kuliah
- `jadwal` - Jadwal mengajar
- `mahasiswa` - Data mahasiswa
- `kelas` - Relasi mahasiswa-mata kuliah
- `nilai` - Sistem penilaian
- `absensi` - Data kehadiran

### Relationships:
- Dosen ↔ Mata Kuliah (One-to-Many)
- Mata Kuliah ↔ Jadwal (One-to-Many)
- Mata Kuliah ↔ Mahasiswa (Many-to-Many via kelas)
- Mata Kuliah + Mahasiswa ↔ Nilai (One-to-One)
- Mata Kuliah + Mahasiswa ↔ Absensi (One-to-Many)

## 🔧 Setup dan Instalasi

### 1. Database Setup
```sql
-- Jalankan script SQL
mysql -u username -p database_name < database_dosen.sql
```

### 2. Konfigurasi
```php
// Update koneksi.php sesuai database Anda
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";
```

### 3. File Structure
```
PWL_TA/
├── dosen/              # Folder sistem dosen
├── assets/css/         # Stylesheet
├── includes/           # File include umum
├── auth/              # Sistem autentikasi
└── koneksi.php        # Koneksi database
```

## 🔐 Sistem Keamanan

### Authentication & Authorization
- ✅ **Session Management**: Kontrol sesi pengguna
- 🔒 **Role-based Access**: Akses berdasarkan role (dosen)
- 🛡️ **SQL Injection Protection**: Sanitasi input
- 🔐 **Password Hashing**: Enkripsi password yang aman

### Data Validation
- ✅ **Client-side Validation**: Validasi JavaScript
- 🔍 **Server-side Validation**: Validasi PHP
- 🧹 **Input Sanitization**: Pembersihan data input
- ⚡ **XSS Protection**: Perlindungan cross-site scripting

## 📱 Responsiveness

### Breakpoints:
- 📱 **Mobile**: < 768px
- 📟 **Tablet**: 768px - 1024px  
- 🖥️ **Desktop**: > 1024px

### Adaptive Features:
- 🔄 **Flexible Grid**: Layout yang menyesuaikan layar
- 📋 **Collapsible Tables**: Tabel yang dapat dilipat di mobile
- 🍔 **Mobile Navigation**: Menu hamburger untuk mobile
- 📊 **Responsive Charts**: Grafik yang adaptive

## 🎯 Best Practices

### Code Organization
- 📁 **Modular Structure**: Pemisahan file berdasarkan fungsi
- 🔄 **Reusable Components**: Komponen yang dapat digunakan ulang
- 📝 **Clean Code**: Kode yang mudah dibaca dan maintain
- 💬 **Documentation**: Komentar dan dokumentasi yang lengkap

### Performance
- ⚡ **Optimized CSS**: Stylesheet yang efisien
- 🔄 **Minimal HTTP Requests**: Pengurangan request server
- 📦 **CDN Integration**: Pemanfaatan CDN untuk library
- 🗜️ **Compressed Assets**: Asset yang teroptimasi

## 🚀 Pengembangan Selanjutnya

### Fitur yang Direncanakan:
- 📊 **Advanced Analytics**: Dashboard analitik yang lebih detail
- 📱 **Mobile App**: Aplikasi mobile native
- 🔔 **Real-time Notifications**: Notifikasi real-time
- 📤 **Export/Import**: Ekspor data ke Excel/PDF
- 🤖 **AI Integration**: Integrasi AI untuk analisis data

### Improvements:
- 🔍 **Advanced Search**: Pencarian yang lebih canggih
- 📈 **Better Reporting**: Sistem laporan yang lebih lengkap
- 🎨 **Theme Customization**: Kustomisasi tema
- 🌐 **Multi-language**: Dukungan multi bahasa

## 👥 Tim Pengembangan

Sistem ini dikembangkan sebagai bagian dari proyek Pemrograman Web Lanjut (PWL) untuk meningkatkan efisiensi pengelolaan akademik dosen.

## 📄 Lisensi

Dikembangkan untuk tujuan pendidikan - MPD University © 2024
