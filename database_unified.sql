-- Database Terpadu untuk Sistem Akademik MPD University
-- Menggabungkan semua skema database menjadi satu

-- ===================================
-- TABEL UTAMA SISTEM
-- ===================================

-- Tabel users (untuk authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    full_name VARCHAR(100),
    nip VARCHAR(20),
    email VARCHAR(100),    phone VARCHAR(20),
    alamat TEXT,
    created_at DATETIME DEFAULT NOW(),
    last_login DATETIME NULL
);

-- Tabel fakultas
CREATE TABLE fakultas (
    id INT AUTO_INCREMENT PRIMARY KEY,    nama VARCHAR(100) NOT NULL,
    dekan VARCHAR(100),
    created_at DATETIME DEFAULT NOW()
);

-- Tabel program studi
CREATE TABLE program_studi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,    fakultas_id INT,
    kaprodi VARCHAR(100),
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (fakultas_id) REFERENCES fakultas(id)
);

-- ===================================
-- TABEL DOSEN
-- ===================================

-- Tabel dosen (profile lengkap dosen)
CREATE TABLE dosen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    nidn VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    bidang_keahlian VARCHAR(50),    fakultas_id INT,
    email VARCHAR(100),
    phone VARCHAR(20),
    alamat TEXT,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (fakultas_id) REFERENCES fakultas(id)
);

-- ===================================
-- TABEL MAHASISWA
-- ===================================

-- Tabel mahasiswa (unified from both schemas)
CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    program_studi_id INT,
    semester INT,    email VARCHAR(100),
    phone VARCHAR(20),
    alamat TEXT,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (program_studi_id) REFERENCES program_studi(id)
);

-- ===================================
-- TABEL MATA KULIAH DAN AKADEMIK
-- ===================================

-- Tabel mata kuliah (unified schema)
CREATE TABLE mata_kuliah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(10) UNIQUE NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    semester INT NOT NULL,
    deskripsi TEXT,    dosen_id INT NOT NULL,
    program_studi_id INT,
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (dosen_id) REFERENCES dosen(id),
    FOREIGN KEY (program_studi_id) REFERENCES program_studi(id)
);

-- Tabel jadwal mengajar
CREATE TABLE jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    hari VARCHAR(10) NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,    ruang VARCHAR(50) NOT NULL,
    kelas VARCHAR(10) NOT NULL,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id)
);

-- Tabel kelas (relasi mahasiswa dengan mata kuliah)
CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,    mata_kuliah_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id),
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    CONSTRAINT unique_enrollment UNIQUE (mata_kuliah_id, mahasiswa_id)
);

-- Tabel nilai
CREATE TABLE nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    tugas1 DECIMAL(5,2) DEFAULT NULL,
    tugas2 DECIMAL(5,2) DEFAULT NULL,
    uts DECIMAL(5,2) DEFAULT NULL,
    uas DECIMAL(5,2) DEFAULT NULL,    nilai_akhir DECIMAL(5,2) DEFAULT NULL,
    grade CHAR(1) DEFAULT NULL,
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id),
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    CONSTRAINT unique_grade UNIQUE (mata_kuliah_id, mahasiswa_id)
);

-- Tabel absensi
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,    tanggal DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    keterangan TEXT,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id),
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    CONSTRAINT unique_attendance UNIQUE (mata_kuliah_id, mahasiswa_id, tanggal)
);

-- ===================================
-- TABEL KONTEN DAN INFORMASI
-- ===================================

-- Tabel pengumuman
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT,
    tanggal DATE NOT NULL,
    created_at DATETIME DEFAULT NOW()
);

-- Tabel kalender akademik
CREATE TABLE kalender_akademik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(20) NOT NULL,
    created_at DATETIME DEFAULT NOW()
);

-- Tabel berita akademik
CREATE TABLE berita_akademik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    tanggal DATE NOT NULL,
    gambar VARCHAR(255),
    ringkasan TEXT,
    penulis VARCHAR(100),
    status VARCHAR(20) DEFAULT 'published',
    created_at DATETIME DEFAULT NOW()
);

-- Tabel galeri
CREATE TABLE galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100),
    gambar VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    created_at DATETIME DEFAULT NOW()
);

-- Tabel kontak/pesan
CREATE TABLE kontak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    pesan TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'baru',
    created_at DATETIME DEFAULT NOW()
);

-- ===================================
-- DATA SAMPLE
-- ===================================

-- Insert sample fakultas
INSERT INTO fakultas (nama, dekan) VALUES
('Fakultas Teknik', 'Prof. Dr. Ir. Ahmad Subandi, M.T.'),
('Fakultas Ilmu Komputer', 'Dr. Budi Rahardjo, M.Kom.'),
('Fakultas Sains', 'Prof. Dr. Siti Nurhaliza, M.Si.'),
('Fakultas Ekonomi', 'Dr. Andi Wijayanto, M.M.'),
('Fakultas Ilmu Data dan Kecerdasan Buatan', 'Prof. Dr. Budi Santoso, M.T.');

-- Insert sample program studi
INSERT INTO program_studi (nama, fakultas_id, kaprodi) VALUES
('Teknik Informatika', 2, 'Dr. Rudi Hartanto, M.Kom.'),
('Sistem Informasi', 2, 'Dr. Sari Dewi, M.Kom.'),
('Teknik Elektro', 1, 'Dr. Ir. Bambang Susilo, M.T.'),
('Teknik Mesin', 1, 'Prof. Dr. Ir. Hendra Gunawan, M.T.'),
('Matematika', 3, 'Dr. Lisa Permata, M.Si.'),
('Fisika', 3, 'Prof. Dr. Agus Salim, M.Si.'),
('Manajemen', 4, 'Dr. Dewi Kartika, M.M.'),
('Akuntansi', 4, 'Dr. Ridwan Kamil, M.Ak.'),
('Ilmu Data', 5, 'Dr. Maya Sari, M.Kom.'),
('Kecerdasan Buatan', 5, 'Dr. Andi Pratama, M.T.');

-- Insert sample users
INSERT INTO users (username, password, role, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', 'admin@mpd.ac.id'),
('dosen1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Rudi Hartanto, M.Kom.', 'rudi.hartanto@mpd.ac.id'),
('dosen2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Sari Dewi, M.Kom.', 'sari.dewi@mpd.ac.id'),
('mhs2021001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Ahmad Rizki', 'ahmad.rizki@student.mpd.ac.id'),
('mhs2021002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Siti Aisyah', 'siti.aisyah@student.mpd.ac.id');

-- Insert sample dosen
INSERT INTO dosen (user_id, nidn, nama, bidang_keahlian, fakultas_id, email, phone) VALUES
((SELECT id FROM users WHERE username = 'dosen1'), '0101018801', 'Dr. Rudi Hartanto, M.Kom.', 'Pemrograman Web', 2, 'rudi.hartanto@mpd.ac.id', '081234567890'),
((SELECT id FROM users WHERE username = 'dosen2'), '0102018802', 'Dr. Sari Dewi, M.Kom.', 'Database', 2, 'sari.dewi@mpd.ac.id', '081234567891');

-- Insert sample mahasiswa
INSERT INTO mahasiswa (user_id, nim, nama, program_studi_id, semester, email) VALUES
((SELECT id FROM users WHERE username = 'mhs2021001'), '2021001', 'Ahmad Rizki', 1, 5, 'ahmad.rizki@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = 'mhs2021002'), '2021002', 'Siti Aisyah', 1, 5, 'siti.aisyah@student.mpd.ac.id');

-- Insert sample mata kuliah
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, deskripsi, dosen_id, program_studi_id) VALUES
('PWL001', 'Pemrograman Web Lanjut', 3, 5, 'Mata kuliah yang membahas pengembangan aplikasi web dengan teknologi modern', 1, 1),
('DB001', 'Database', 3, 4, 'Mata kuliah yang membahas konsep dan implementasi database', 2, 1),
('ASD001', 'Algoritma dan Struktur Data', 3, 3, 'Mata kuliah yang membahas algoritma dan struktur data fundamental', 1, 1);

-- Insert sample jadwal
INSERT INTO jadwal (mata_kuliah_id, hari, jam_mulai, jam_selesai, ruang, kelas) VALUES
(1, 'Senin', '08:00:00', '10:30:00', 'Lab 1', 'A'),
(2, 'Senin', '10:30:00', '12:00:00', 'Ruang 201', 'B'),
(3, 'Selasa', '13:00:00', '15:30:00', 'Ruang 105', 'C');

-- Insert sample enrollment
INSERT INTO kelas (mata_kuliah_id, mahasiswa_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2),
(3, 1), (3, 2);

-- Insert data pengumuman dari data_dump.sql
INSERT INTO pengumuman (judul, isi, tanggal) VALUES
('Jadwal Pendaftaran Semester Ganjil 2023/2024', 'Pendaftaran mata kuliah untuk semester ganjil tahun akademik 2023/2024 akan dibuka pada tanggal **1 Agustus 2023** dan ditutup pada tanggal **15 Agustus 2023**. Mahasiswa diharapkan berkonsultasi dengan dosen pembimbing akademik sebelum melakukan pendaftaran.\n\nProses pendaftaran dilakukan melalui portal akademik dengan menggunakan akun masing-masing.', '2023-07-15'),
('Pembayaran Uang Kuliah Semester Ganjil', 'Batas waktu pembayaran uang kuliah semester ganjil tahun akademik 2023/2024 adalah **25 Juli 2023**. Pembayaran dapat dilakukan melalui transfer bank atau langsung di bagian keuangan universitas.\n\nMahasiswa yang belum melakukan pembayaran hingga batas waktu yang ditentukan tidak dapat melakukan pendaftaran mata kuliah.', '2023-07-10'),
('Jadwal UAS Semester Genap 2022/2023', 'Ujian Akhir Semester (UAS) untuk semester genap tahun akademik 2022/2023 akan dilaksanakan pada tanggal 19-30 Juni 2023. Jadwal detil untuk setiap mata kuliah dapat dilihat pada portal akademik.\n\nMahasiswa diwajibkan hadir 30 menit sebelum ujian dimulai dan membawa kartu ujian.', '2023-06-05'),
('Libur Semester Genap 2022/2023', 'Libur semester genap tahun akademik 2022/2023 akan dimulai pada tanggal 1 Juli 2023 hingga 13 Agustus 2023. Selama masa libur, layanan administrasi kampus tetap buka dengan jam operasional khusus.\n\nSilahkan cek jadwal layanan pada website resmi atau media sosial MPD University.', '2023-06-20'),
('Perubahan Jadwal Seminar Proposal', 'Diinformasikan kepada seluruh mahasiswa bahwa terdapat perubahan jadwal seminar proposal untuk Program Studi Teknik Informatika dan Sistem Informasi. Jadwal yang semula pada 5-9 Juni 2023 diundur menjadi 12-16 Juni 2023.\n\nHarap mahasiswa memperhatikan perubahan jadwal ini.', '2023-06-01');

-- Insert data kalender akademik
INSERT INTO kalender_akademik (tanggal, judul, deskripsi, kategori) VALUES
('2023-08-01', 'Pendaftaran Mata Kuliah Semester Ganjil', 'Pendaftaran mata kuliah untuk mahasiswa angkatan 2021-2023 melalui portal akademik.', 'pendaftaran'),
('2023-08-20', 'Orientasi Mahasiswa Baru', 'Pengenalan lingkungan kampus dan sistem akademik untuk mahasiswa baru angkatan 2023.', 'perkuliahan'),
('2023-08-28', 'Awal Perkuliahan Semester Ganjil', 'Perkuliahan dimulai untuk semua fakultas dan program studi, baik untuk kelas reguler maupun kelas malam.', 'perkuliahan'),
('2023-10-15', 'Ujian Tengah Semester', 'Periode ujian tengah semester untuk semua mata kuliah semester ganjil.', 'ujian'),
('2023-12-15', 'Ujian Akhir Semester', 'Periode ujian akhir semester untuk semua mata kuliah semester ganjil.', 'ujian'),
('2023-12-30', 'Batas Akhir Penyerahan Nilai', 'Batas waktu bagi dosen untuk menyerahkan nilai akhir mata kuliah semester ganjil.', 'lainnya'),
('2024-01-10', 'Pengumuman Nilai Semester Ganjil', 'Pengumuman nilai semester ganjil dan penerbitan KHS untuk semua mahasiswa.', 'lainnya'),
('2024-01-15', 'Pendaftaran Mata Kuliah Semester Genap', 'Pendaftaran mata kuliah semester genap untuk semua mahasiswa.', 'pendaftaran'),
('2024-01-25', 'Wisuda Periode I', 'Wisuda periode pertama tahun akademik 2023/2024.', 'wisuda'),
('2024-02-05', 'Awal Perkuliahan Semester Genap', 'Perkuliahan dimulai untuk semester genap tahun akademik 2023/2024.', 'perkuliahan');

-- Insert data berita akademik
INSERT INTO berita_akademik (judul, isi, tanggal, gambar, ringkasan, penulis, status) VALUES
('MPD University Raih Akreditasi Unggul', 'MPD University berhasil meraih status Akreditasi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2023-2028. Pencapaian ini merupakan hasil dari upaya seluruh civitas akademika dalam meningkatkan kualitas pendidikan tinggi.\n\nPenilaian akreditasi meliputi sembilan kriteria, termasuk visi misi, tata kelola, mahasiswa, sumber daya manusia, keuangan, pendidikan, penelitian, pengabdian kepada masyarakat, dan luaran-capaian. MPD University berhasil mendapatkan nilai sangat baik pada semua aspek tersebut.\n\nRektor MPD University, Prof. Dr. Ahmad Fauzi, menyampaikan apresiasi kepada seluruh pihak yang telah berkontribusi dalam pencapaian ini. "Akreditasi Unggul ini menjadi bukti komitmen kami dalam memberikan pendidikan tinggi berkualitas," ujarnya.', '2023-07-05', 'akreditasi.jpg', 'MPD University berhasil meraih status Akreditasi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2023-2028.', 'Tim Humas MPD University', 'published'),
('Program Beasiswa Baru untuk Mahasiswa Berprestasi', 'MPD University bekerja sama dengan lima perusahaan teknologi terkemuka meluncurkan program beasiswa baru untuk mahasiswa berprestasi di bidang teknologi dan sains. Program beasiswa ini akan mencakup biaya kuliah penuh dan tunjangan bulanan selama masa studi.\n\nProgram ini terbuka untuk mahasiswa dari Fakultas Teknik, Fakultas Ilmu Komputer, dan Fakultas Sains dengan IPK minimal 3.50. Selain prestasi akademik, seleksi juga akan mempertimbangkan portfolio proyek dan keterlibatan dalam kegiatan pengembangan teknologi.\n\n"Kami ingin mendukung talenta-talenta terbaik untuk berkembang tanpa terkendala masalah finansial," kata Dr. Siti Rahma, Wakil Rektor Bidang Kemahasiswaan.\n\nPendaftaran program beasiswa akan dibuka pada 1 Agustus 2023 dan berakhir pada 30 Agustus 2023.', '2023-06-28', 'beasiswa.jpg', 'MPD University bekerja sama dengan industri terkemuka meluncurkan program beasiswa baru untuk mahasiswa berprestasi di bidang teknologi dan sains.', 'Departemen Beasiswa', 'published');
