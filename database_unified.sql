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
    email VARCHAR(100),
    phone VARCHAR(20),
    alamat TEXT,
    profile_photo VARCHAR(255) DEFAULT 'avatar-1.jpg',
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

-- Tabel articles (untuk berita/artikel)
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    author_id INT,
    status VARCHAR(20) DEFAULT 'published',
    featured_image VARCHAR(255),
    published_at DATETIME DEFAULT NOW(),
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (author_id) REFERENCES users(id)
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
('202143502611', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Ahmad Rizki', 'ahmad.rizki@student.mpd.ac.id'),
('202143502612', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Siti Aisyah', 'siti.aisyah@student.mpd.ac.id');

-- Insert sample dosen
INSERT INTO dosen (user_id, nidn, nama, bidang_keahlian, fakultas_id, email, phone) VALUES
((SELECT id FROM users WHERE username = 'dosen1'), '0101018801', 'Dr. Rudi Hartanto, M.Kom.', 'Pemrograman Web', 2, 'rudi.hartanto@mpd.ac.id', '081234567890'),
((SELECT id FROM users WHERE username = 'dosen2'), '0102018802', 'Dr. Sari Dewi, M.Kom.', 'Database', 2, 'sari.dewi@mpd.ac.id', '081234567891');

-- Insert additional sample dosen users
INSERT INTO users (username, password, role, full_name, nip, email, phone) VALUES
('dosen3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Ahmad Rahman, M.Kom.', '198501012010011001', 'ahmad.rahman@mpd.ac.id', '081234567892'),
('dosen4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Prof. Dr. Siti Nurhaliza, M.T.', '198203152009012002', 'siti.nurhaliza@mpd.ac.id', '081234567893'),
('dosen5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Budi Santoso, M.Sc.', '197809102008011003', 'budi.santoso@mpd.ac.id', '081234567894');

-- Insert additional sample dosen
INSERT INTO dosen (user_id, nidn, nama, bidang_keahlian, fakultas_id, email, phone) VALUES
((SELECT id FROM users WHERE username = 'dosen3'), '0101018803', 'Dr. Ahmad Rahman, M.Kom.', 'Web Development', 2, 'ahmad.rahman@mpd.ac.id', '081234567892'),
((SELECT id FROM users WHERE username = 'dosen4'), '0102018804', 'Prof. Dr. Siti Nurhaliza, M.T.', 'Database Systems', 2, 'siti.nurhaliza@mpd.ac.id', '081234567893'),
((SELECT id FROM users WHERE username = 'dosen5'), '0103018805', 'Dr. Budi Santoso, M.Sc.', 'Artificial Intelligence', 2, 'budi.santoso@mpd.ac.id', '081234567894');

-- Insert additional sample users (dosen)
INSERT INTO users (username, password, role, full_name, nip, email, phone) VALUES
('dosen6', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Maya Sari, M.Kom.', '198405082009012004', 'maya.sari@mpd.ac.id', '081234567895'),
('dosen7', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Prof. Dr. Bambang Susilo, M.T.', '197706152008011005', 'bambang.susilo@mpd.ac.id', '081234567896'),
('dosen8', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Lisa Permata, M.Si.', '198103252010012006', 'lisa.permata@mpd.ac.id', '081234567897'),
('dosen9', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Agus Salim, M.Si.', '197912102007011007', 'agus.salim@mpd.ac.id', '081234567898'),
('dosen10', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Dewi Kartika, M.M.', '198208172009012008', 'dewi.kartika@mpd.ac.id', '081234567899'),
('dosen11', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Ridwan Kamil, M.Ak.', '197804122008011009', 'ridwan.kamil@mpd.ac.id', '081234567800'),
('dosen12', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen', 'Dr. Andi Pratama, M.T.', '198509282010011010', 'andi.pratama@mpd.ac.id', '081234567801');

-- Insert additional sample dosen
INSERT INTO dosen (user_id, nidn, nama, bidang_keahlian, fakultas_id, email, phone) VALUES
((SELECT id FROM users WHERE username = 'dosen6'), '0104018806', 'Dr. Maya Sari, M.Kom.', 'Data Science', 5, 'maya.sari@mpd.ac.id', '081234567895'),
((SELECT id FROM users WHERE username = 'dosen7'), '0105018807', 'Prof. Dr. Bambang Susilo, M.T.', 'Teknik Elektro', 1, 'bambang.susilo@mpd.ac.id', '081234567896'),
((SELECT id FROM users WHERE username = 'dosen8'), '0106018808', 'Dr. Lisa Permata, M.Si.', 'Matematika', 3, 'lisa.permata@mpd.ac.id', '081234567897'),
((SELECT id FROM users WHERE username = 'dosen9'), '0107018809', 'Dr. Agus Salim, M.Si.', 'Fisika', 3, 'agus.salim@mpd.ac.id', '081234567898'),
((SELECT id FROM users WHERE username = 'dosen10'), '0108018810', 'Dr. Dewi Kartika, M.M.', 'Manajemen', 4, 'dewi.kartika@mpd.ac.id', '081234567899'),
((SELECT id FROM users WHERE username = 'dosen11'), '0109018811', 'Dr. Ridwan Kamil, M.Ak.', 'Akuntansi', 4, 'ridwan.kamil@mpd.ac.id', '081234567800'),
((SELECT id FROM users WHERE username = 'dosen12'), '0110018812', 'Dr. Andi Pratama, M.T.', 'Artificial Intelligence', 5, 'andi.pratama@mpd.ac.id', '081234567801');

-- Insert sample mahasiswa
INSERT INTO mahasiswa (user_id, nim, nama, program_studi_id, semester, email) VALUES
((SELECT id FROM users WHERE username = '202143502611'), '202143502611', 'Ahmad Rizki', 1, 5, 'ahmad.rizki@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202143502612'), '202143502612', 'Siti Aisyah', 1, 5, 'siti.aisyah@student.mpd.ac.id');

-- Insert additional sample mahasiswa users
INSERT INTO users (username, password, role, full_name, email) VALUES
('202143502613', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Budi Santoso', 'budi.santoso@student.mpd.ac.id'),
('202143502614', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Diana Putri', 'diana.putri@student.mpd.ac.id'),
('202143502615', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Eko Prasetyo', 'eko.prasetyo@student.mpd.ac.id'),
('202243502616', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Fitri Handayani', 'fitri.handayani@student.mpd.ac.id'),
('202243502617', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Galih Permana', 'galih.permana@student.mpd.ac.id'),
('202243502618', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Hana Wijaya', 'hana.wijaya@student.mpd.ac.id'),
('202343502619', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Indra Kusuma', 'indra.kusuma@student.mpd.ac.id'),
('202343502620', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Joko Widodo', 'joko.widodo@student.mpd.ac.id'),
('202343502621', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Karina Sari', 'karina.sari@student.mpd.ac.id'),
('202343502622', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Lukman Hakim', 'lukman.hakim@student.mpd.ac.id'),
('202043502623', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Maya Angelina', 'maya.angelina@student.mpd.ac.id'),
('202043502624', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa', 'Nanda Pratama', 'nanda.pratama@student.mpd.ac.id');

-- Insert additional sample mahasiswa
INSERT INTO mahasiswa (user_id, nim, nama, program_studi_id, semester, email) VALUES
((SELECT id FROM users WHERE username = '202143502613'), '202143502613', 'Budi Santoso', 1, 5, 'budi.santoso@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202143502614'), '202143502614', 'Diana Putri', 2, 5, 'diana.putri@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202143502615'), '202143502615', 'Eko Prasetyo', 1, 5, 'eko.prasetyo@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202243502616'), '202243502616', 'Fitri Handayani', 2, 3, 'fitri.handayani@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202243502617'), '202243502617', 'Galih Permana', 1, 3, 'galih.permana@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202243502618'), '202243502618', 'Hana Wijaya', 3, 3, 'hana.wijaya@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202343502619'), '202343502619', 'Indra Kusuma', 1, 1, 'indra.kusuma@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202343502620'), '202343502620', 'Joko Widodo', 2, 1, 'joko.widodo@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202343502621'), '202343502621', 'Karina Sari', 4, 1, 'karina.sari@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202343502622'), '202343502622', 'Lukman Hakim', 1, 1, 'lukman.hakim@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202043502623'), '202043502623', 'Maya Angelina', 1, 7, 'maya.angelina@student.mpd.ac.id'),
((SELECT id FROM users WHERE username = '202043502624'), '202043502624', 'Nanda Pratama', 2, 7, 'nanda.pratama@student.mpd.ac.id');

-- Insert sample mata kuliah
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, deskripsi, dosen_id, program_studi_id) VALUES
('PWL001', 'Pemrograman Web Lanjut', 3, 5, 'Mata kuliah yang membahas pengembangan aplikasi web dengan teknologi modern', 1, 1),
('DB001', 'Database', 3, 4, 'Mata kuliah yang membahas konsep dan implementasi database', 2, 1),
('ASD001', 'Algoritma dan Struktur Data', 3, 3, 'Mata kuliah yang membahas algoritma dan struktur data fundamental', 1, 1);

-- Insert additional sample mata kuliah
INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, deskripsi, dosen_id, program_studi_id) VALUES
('RPL001', 'Rekayasa Perangkat Lunak', 3, 6, 'Mata kuliah yang membahas metodologi pengembangan perangkat lunak', 3, 1),
('AI001', 'Kecerdasan Buatan', 3, 7, 'Mata kuliah yang membahas konsep dan implementasi AI', 5, 1),
('MTK001', 'Matematika Dasar', 3, 1, 'Mata kuliah yang membahas konsep dasar matematika untuk teknik', 8, 1),
('FIS001', 'Fisika Dasar', 3, 1, 'Mata kuliah yang membahas konsep dasar fisika untuk teknik', 9, 1),
('ENG001', 'Bahasa Inggris Teknik', 2, 1, 'Mata kuliah bahasa Inggris untuk keperluan teknik', 1, 1),
('PROG001', 'Pemrograman Dasar', 4, 2, 'Mata kuliah pengenalan pemrograman komputer', 3, 1),
('WEB001', 'Pemrograman Web', 3, 4, 'Mata kuliah pemrograman web dasar', 1, 1),
('MOBILE001', 'Pemrograman Mobile', 3, 6, 'Mata kuliah pemrograman aplikasi mobile', 5, 1),
('NET001', 'Jaringan Komputer', 3, 5, 'Mata kuliah yang membahas konsep jaringan komputer', 3, 1),
('SEC001', 'Keamanan Sistem', 3, 7, 'Mata kuliah keamanan sistem informasi', 5, 1),
('MAN001', 'Manajemen Dasar', 3, 1, 'Mata kuliah konsep dasar manajemen', 10, 7),
('ACC001', 'Akuntansi Dasar', 3, 1, 'Mata kuliah dasar-dasar akuntansi', 11, 8),
('STAT001', 'Statistika', 3, 2, 'Mata kuliah statistika dan analisis data', 6, 9),
('ML001', 'Machine Learning', 3, 6, 'Mata kuliah machine learning dan AI', 12, 10);

-- Insert sample jadwal
INSERT INTO jadwal (mata_kuliah_id, hari, jam_mulai, jam_selesai, ruang, kelas) VALUES
(1, 'Senin', '08:00:00', '10:30:00', 'Lab 1', 'A'),
(2, 'Senin', '10:30:00', '12:00:00', 'Ruang 201', 'B'),
(3, 'Selasa', '13:00:00', '15:30:00', 'Ruang 105', 'C'),
(4, 'Rabu', '08:00:00', '10:30:00', 'Lab 2', 'A'),
(5, 'Kamis', '13:00:00', '15:30:00', 'Ruang 301', 'B'),
(6, 'Senin', '13:00:00', '15:30:00', 'Ruang 102', 'A'),
(7, 'Selasa', '08:00:00', '10:30:00', 'Lab 3', 'B'),
(8, 'Selasa', '10:30:00', '13:00:00', 'Ruang 203', 'A'),
(9, 'Rabu', '13:00:00', '15:30:00', 'Lab 1', 'C'),
(10, 'Kamis', '08:00:00', '10:30:00', 'Ruang 104', 'A'),
(11, 'Kamis', '10:30:00', '13:00:00', 'Ruang 205', 'B'),
(12, 'Jumat', '08:00:00', '10:30:00', 'Ruang 301', 'A'),
(13, 'Jumat', '10:30:00', '13:00:00', 'Lab 2', 'B'),
(14, 'Senin', '15:30:00', '18:00:00', 'Ruang 302', 'C'),
(15, 'Selasa', '15:30:00', '18:00:00', 'Ruang 303', 'A'),
(16, 'Rabu', '08:00:00', '10:30:00', 'Lab 4', 'B'),
(17, 'Kamis', '15:30:00', '18:00:00', 'Ruang 304', 'C');

-- Insert sample enrollment
INSERT INTO kelas (mata_kuliah_id, mahasiswa_id) VALUES
(1, 1), (1, 2),
(2, 1), (2, 2),
(3, 1), (3, 2),
(4, 1), (4, 2),
(5, 1), (5, 2),
-- Mahasiswa semester 5
(1, 3), (1, 4), (1, 5),
(2, 3), (2, 4), (2, 5),
(3, 3), (3, 4), (3, 5),
(4, 3), (4, 4), (4, 5),
(5, 3), (5, 4), (5, 5),
-- Mahasiswa semester 3
(6, 6), (6, 7), (6, 8),
(7, 6), (7, 7), (7, 8),
(10, 6), (10, 7), (10, 8),
-- Mahasiswa semester 1
(6, 9), (6, 10), (6, 11), (6, 12),
(7, 9), (7, 10), (7, 11), (7, 12),
(8, 9), (8, 10), (8, 11), (8, 12),
-- Mahasiswa semester 7
(11, 13), (11, 14),
(12, 13), (12, 14);

-- Insert data pengumuman dari data_dump.sql
INSERT INTO pengumuman (judul, isi, tanggal) VALUES
('Jadwal Pendaftaran Semester Ganjil 2023/2024', 'Pendaftaran mata kuliah untuk semester ganjil tahun akademik 2023/2024 akan dibuka pada tanggal **1 Agustus 2023** dan ditutup pada tanggal **15 Agustus 2023**. Mahasiswa diharapkan berkonsultasi dengan dosen pembimbing akademik sebelum melakukan pendaftaran.\n\nProses pendaftaran dilakukan melalui portal akademik dengan menggunakan akun masing-masing.', '2023-07-15'),
('Pembayaran Uang Kuliah Semester Ganjil', 'Batas waktu pembayaran uang kuliah semester ganjil tahun akademik 2023/2024 adalah **25 Juli 2023**. Pembayaran dapat dilakukan melalui transfer bank atau langsung di bagian keuangan universitas.\n\nMahasiswa yang belum melakukan pembayaran hingga batas waktu yang ditentukan tidak dapat melakukan pendaftaran mata kuliah.', '2023-07-10'),
('Jadwal UAS Semester Genap 2022/2023', 'Ujian Akhir Semester (UAS) untuk semester genap tahun akademik 2022/2023 akan dilaksanakan pada tanggal 19-30 Juni 2023. Jadwal detil untuk setiap mata kuliah dapat dilihat pada portal akademik.\n\nMahasiswa diwajibkan hadir 30 menit sebelum ujian dimulai dan membawa kartu ujian.', '2023-06-05'),
('Libur Semester Genap 2022/2023', 'Libur semester genap tahun akademik 2022/2023 akan dimulai pada tanggal 1 Juli 2023 hingga 13 Agustus 2023. Selama masa libur, layanan administrasi kampus tetap buka dengan jam operasional khusus.\n\nSilahkan cek jadwal layanan pada website resmi atau media sosial MPD University.', '2023-06-20'),
('Perubahan Jadwal Seminar Proposal', 'Diinformasikan kepada seluruh mahasiswa bahwa terdapat perubahan jadwal seminar proposal untuk Program Studi Teknik Informatika dan Sistem Informasi. Jadwal yang semula pada 5-9 Juni 2023 diundur menjadi 12-16 Juni 2023.\n\nHarap mahasiswa memperhatikan perubahan jadwal ini.', '2023-06-01'),
('Pendaftaran Beasiswa Prestasi 2024', 'Dibuka pendaftaran beasiswa prestasi untuk mahasiswa dengan IPK minimal 3.5. Pendaftaran dibuka hingga 30 November 2024. Silahkan mengakses portal beasiswa untuk informasi lebih lanjut.', '2024-10-15'),
('Jadwal Wisuda Periode II 2024', 'Wisuda periode II tahun 2024 akan dilaksanakan pada tanggal 15 Desember 2024. Bagi mahasiswa yang akan wisuda mohon melengkapi persyaratan administrasi sebelum 1 Desember 2024.', '2024-11-01'),
('Pembaruan Sistem Portal Akademik', 'Portal akademik akan mengalami pembaruan sistem pada tanggal 20-21 November 2024. Selama periode tersebut, akses portal akan terbatas. Mohon maaf atas ketidaknyamanannya.', '2024-11-10'),
('Workshop Teknologi Terbaru', 'Akan diadakan workshop mengenai teknologi AI dan Machine Learning pada tanggal 25 November 2024. Pendaftaran dibuka untuk semua mahasiswa dan dosen. Tempat terbatas.', '2024-11-12');

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
('2024-02-05', 'Awal Perkuliahan Semester Genap', 'Perkuliahan dimulai untuk semester genap tahun akademik 2023/2024.', 'perkuliahan'),
('2024-11-20', 'Workshop AI dan Machine Learning', 'Workshop teknologi terbaru untuk mahasiswa dan dosen', 'workshop'),
('2024-11-25', 'Seminar Nasional Teknologi', 'Seminar nasional dengan pembicara dari industri', 'seminar'),
('2024-12-01', 'Batas Akhir Pendaftaran Wisuda', 'Batas waktu pendaftaran untuk wisuda periode II', 'pendaftaran'),
('2024-12-15', 'Wisuda Periode II', 'Upacara wisuda untuk lulusan periode II tahun 2024', 'wisuda'),
('2025-01-08', 'Awal Semester Genap 2024/2025', 'Mulai perkuliahan semester genap', 'perkuliahan'),
('2025-01-20', 'Batas Akhir Registrasi Ulang', 'Batas waktu registrasi ulang mahasiswa', 'registrasi');

-- Insert data berita akademik
INSERT INTO berita_akademik (judul, isi, tanggal, gambar, ringkasan, penulis, status) VALUES
('MPD University Raih Akreditasi Unggul', 'MPD University berhasil meraih status Akreditasi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2023-2028. Pencapaian ini merupakan hasil dari upaya seluruh civitas akademika dalam meningkatkan kualitas pendidikan tinggi.\n\nPenilaian akreditasi meliputi sembilan kriteria, termasuk visi misi, tata kelola, mahasiswa, sumber daya manusia, keuangan, pendidikan, penelitian, pengabdian kepada masyarakat, dan luaran-capaian. MPD University berhasil mendapatkan nilai sangat baik pada semua aspek tersebut.\n\nRektor MPD University, Prof. Dr. Ahmad Fauzi, menyampaikan apresiasi kepada seluruh pihak yang telah berkontribusi dalam pencapaian ini. "Akreditasi Unggul ini menjadi bukti komitmen kami dalam memberikan pendidikan tinggi berkualitas," ujarnya.', '2023-07-05', 'akreditasi.jpg', 'MPD University berhasil meraih status Akreditasi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2023-2028.', 'Tim Humas MPD University', 'published'),
('Program Beasiswa Baru untuk Mahasiswa Berprestasi', 'MPD University bekerja sama dengan lima perusahaan teknologi terkemuka meluncurkan program beasiswa baru untuk mahasiswa berprestasi di bidang teknologi dan sains. Program beasiswa ini akan mencakup biaya kuliah penuh dan tunjangan bulanan selama masa studi.\n\nProgram ini terbuka untuk mahasiswa dari Fakultas Teknik, Fakultas Ilmu Komputer, dan Fakultas Sains dengan IPK minimal 3.50. Selain prestasi akademik, seleksi juga akan mempertimbangkan portfolio proyek dan keterlibatan dalam kegiatan pengembangan teknologi.\n\n"Kami ingin mendukung talenta-talenta terbaik untuk berkembang tanpa terkendala masalah finansial," kata Dr. Siti Rahma, Wakil Rektor Bidang Kemahasiswaan.\n\nPendaftaran program beasiswa akan dibuka pada 1 Agustus 2023 dan berakhir pada 30 Agustus 2023.', '2023-06-28', 'beasiswa.jpg', 'MPD University bekerja sama dengan industri terkemuka meluncurkan program beasiswa baru untuk mahasiswa berprestasi di bidang teknologi dan sains.', 'Departemen Beasiswa', 'published'),
('Tim Robotika MPD University Juara Nasional', 'Tim robotika MPD University berhasil meraih juara pertama dalam kompetisi robotika nasional yang diselenggarakan di Jakarta. Tim yang terdiri dari 5 mahasiswa Teknik Informatika ini berhasil mengalahkan 50 tim dari universitas terkemuka di Indonesia.\n\nKompetisi ini menguji kemampuan dalam merancang robot otonom yang dapat menyelesaikan berbagai tantangan. Prestasi ini merupakan yang ketiga kalinya MPD University meraih juara dalam kompetisi sejenis.', '2024-11-01', 'robotika.jpg', 'Tim robotika MPD University meraih juara pertama kompetisi robotika nasional', 'Tim Media MPD', 'published'),
('Kerjasama Internasional dengan Universitas Jepang', 'MPD University menandatangani MoU dengan Tokyo Institute of Technology untuk program pertukaran mahasiswa dan dosen. Program ini akan memberikan kesempatan bagi mahasiswa terbaik untuk belajar di Jepang selama satu semester.\n\nSelain itu, akan ada program penelitian bersama di bidang teknologi dan engineering. Kerjasama ini diharapkan dapat meningkatkan kualitas pendidikan dan penelitian di MPD University.', '2024-10-28', 'kerjasama_jepang.jpg', 'MPD University menjalin kerjasama dengan Tokyo Institute of Technology', 'Humas MPD', 'published'),
('Launching Program Studi Cybersecurity', 'MPD University resmi meluncurkan program studi baru yaitu Cybersecurity yang akan mulai menerima mahasiswa pada tahun akademik 2025/2026. Program studi ini dirancang untuk menjawab kebutuhan industri akan tenaga ahli keamanan siber.\n\nKurikulum disusun dengan melibatkan praktisi industri dan menggunakan laboratorium berstandard internasional.', '2024-11-05', 'cybersecurity.jpg', 'Program studi Cybersecurity resmi diluncurkan untuk tahun akademik 2025/2026', 'Bagian Akademik', 'published');

-- Insert sample articles (untuk admin)
INSERT INTO articles (title, content, excerpt, author_id, status, published_at) VALUES
('Selamat Datang di Sistem Akademik MPD University', 'Sistem Akademik MPD University telah resmi diluncurkan dengan berbagai fitur terdepan untuk mendukung kegiatan akademik mahasiswa, dosen, dan staff administrasi. Sistem ini menyediakan platform terintegrasi untuk manajemen perkuliahan, nilai, absensi, dan berbagai layanan akademik lainnya.\n\nDengan teknologi terkini, kami berkomitmen memberikan pengalaman terbaik bagi seluruh civitas akademika dalam mengakses informasi dan layanan akademik.', 'Sistem Akademik MPD University diluncurkan dengan fitur-fitur terdepan untuk mendukung kegiatan akademik.', 1, 'published', NOW()),
('Panduan Penggunaan Portal Akademik', 'Portal akademik MPD University dirancang dengan antarmuka yang user-friendly dan mudah diakses. Artikel ini akan memandu Anda dalam menggunakan berbagai fitur yang tersedia, mulai dari login hingga mengakses nilai dan jadwal perkuliahan.\n\nSetiap user memiliki dashboard yang disesuaikan dengan perannya masing-masing, baik sebagai mahasiswa, dosen, maupun admin.', 'Panduan lengkap penggunaan portal akademik MPD University untuk semua user.', 1, 'published', NOW()),
('Kebijakan Akademik Terbaru 2024', 'MPD University telah mengimplementasikan beberapa kebijakan akademik terbaru yang mulai berlaku pada tahun akademik 2024. Kebijakan ini mencakup sistem penilaian, absensi, dan berbagai prosedur akademik lainnya yang ditujukan untuk meningkatkan kualitas pendidikan.\n\nSemua civitas akademika diharapkan memahami dan mengikuti kebijakan-kebijakan yang telah ditetapkan.', 'Kebijakan akademik terbaru MPD University yang berlaku mulai tahun 2024.', 1, 'published', NOW()),
('Tips Sukses Kuliah Online', 'Menghadapi era digital, MPD University menyediakan berbagai tips dan strategi untuk mahasiswa agar dapat sukses dalam perkuliahan online. Artikel ini membahas tentang manajemen waktu, teknologi yang dibutuhkan, dan cara efektif mengikuti kuliah virtual.\n\nKuliah online memerlukan disiplin dan strategi khusus agar dapat mencapai hasil pembelajaran yang optimal.', 'Tips dan strategi sukses mengikuti perkuliahan online di MPD University.', 1, 'published', NOW()),
('Prestasi Mahasiswa MPD University 2024', 'Mahasiswa MPD University kembali meraih berbagai prestasi gemilang di tingkat nasional dan internasional. Dari kompetisi programming, penelitian ilmiah, hingga inovasi teknologi, mahasiswa kami terus menunjukkan dedikasi dan kemampuan terbaik.\n\nPrestasi-prestasi ini membuktikan kualitas pendidikan dan pembinaan yang diberikan oleh MPD University.', 'Berbagai prestasi gemilang yang diraih mahasiswa MPD University di tahun 2024.', 1, 'published', NOW());

-- ===================================
-- TABEL ADMIN DAN SISTEM
-- ===================================

-- Tabel sistem pengaturan/konfigurasi
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_description VARCHAR(255),
    updated_by INT,
    updated_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Tabel log aktivitas admin
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Tabel notifikasi sistem
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (recipient_id) REFERENCES users(id)
);

-- Insert sample system settings
INSERT INTO system_settings (setting_key, setting_value, setting_description, updated_by) VALUES
('site_name', 'MPD University - Sistem Akademik', 'Nama website sistem akademik', 1),
('site_description', 'Portal akademik terpadu untuk mahasiswa, dosen, dan administrasi MPD University', 'Deskripsi website', 1),
('academic_year', '2024/2025', 'Tahun akademik aktif', 1),
('semester_active', 'Ganjil', 'Semester aktif saat ini', 1),
('registration_open', 'true', 'Status pendaftaran mata kuliah', 1),
('max_sks_per_semester', '24', 'Maksimal SKS per semester', 1),
('min_attendance_percentage', '75', 'Minimal persentase kehadiran', 1),
('late_payment_fee', '50000', 'Denda keterlambatan pembayaran', 1),
('contact_email', 'admin@mpd.ac.id', 'Email kontak utama', 1),
('contact_phone', '021-1234567', 'Nomor telepon kontak', 1);

-- Insert sample notifications untuk admin
INSERT INTO notifications (recipient_id, title, message, type, is_read) VALUES
(1, 'Selamat Datang, Administrator!', 'Selamat datang di sistem akademik MPD University. Anda memiliki akses penuh untuk mengelola semua aspek sistem.', 'welcome', FALSE),
(1, 'Data Mahasiswa Diperbarui', 'Terdapat 5 data mahasiswa baru yang telah ditambahkan ke sistem.', 'info', FALSE),
(1, 'Laporan Mingguan Tersedia', 'Laporan aktivitas sistem minggu ini telah tersedia untuk diunduh.', 'report', FALSE);

-- Insert sample admin logs
INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES
(1, 'LOGIN', 'Administrator berhasil login ke sistem', '127.0.0.1'),
(1, 'CREATE_USER', 'Menambahkan user dosen baru: Dr. Sari Dewi', '127.0.0.1'),
(1, 'UPDATE_SETTINGS', 'Memperbarui pengaturan sistem akademik', '127.0.0.1'),
(1, 'VIEW_REPORTS', 'Mengakses laporan data mahasiswa', '127.0.0.1'),
(1, 'BACKUP_DATABASE', 'Melakukan backup database sistem', '127.0.0.1'),
(1, 'CREATE_MAHASISWA', 'Menambahkan mahasiswa baru: Budi Santoso (2021003)', '127.0.0.1'),
(1, 'CREATE_DOSEN', 'Menambahkan dosen baru: Dr. Maya Sari', '127.0.0.1'),
(1, 'UPDATE_JADWAL', 'Memperbarui jadwal mata kuliah Pemrograman Web', '127.0.0.1'),
(1, 'DELETE_ARTIKEL', 'Menghapus artikel draft yang sudah tidak relevan', '127.0.0.1'),
(1, 'EXPORT_DATA', 'Mengexport data mahasiswa untuk laporan', '127.0.0.1'),
(1, 'IMPORT_DATA', 'Mengimport data nilai dari file Excel', '127.0.0.1'),
(1, 'CHANGE_PASSWORD', 'Mengubah password untuk user dosen3', '127.0.0.1'),
(1, 'SYSTEM_MAINTENANCE', 'Melakukan maintenance rutin sistem', '127.0.0.1');

-- Insert sample nilai
INSERT INTO nilai (mata_kuliah_id, mahasiswa_id, tugas1, tugas2, uts, uas, nilai_akhir, grade) VALUES
(1, 1, 85.0, 88.0, 82.0, 86.0, 85.25, 'A'),
(1, 2, 78.0, 82.0, 75.0, 80.0, 78.75, 'B'),
(2, 1, 90.0, 88.0, 85.0, 89.0, 88.0, 'A'),
(2, 2, 82.0, 85.0, 78.0, 83.0, 82.0, 'B'),
-- Semester 5 students
(1, 3, 80.0, 85.0, 78.0, 82.0, 81.25, 'B'),
(1, 4, 92.0, 88.0, 90.0, 91.0, 90.25, 'A'),
(1, 5, 75.0, 78.0, 72.0, 76.0, 75.25, 'B'),
(2, 3, 85.0, 82.0, 80.0, 84.0, 82.75, 'B'),
(2, 4, 88.0, 90.0, 85.0, 87.0, 87.5, 'A'),
(2, 5, 79.0, 76.0, 78.0, 80.0, 78.25, 'B'),
-- Semester 3 students
(6, 6, 87.0, 89.0, 84.0, 86.0, 86.5, 'A'),
(6, 7, 82.0, 85.0, 80.0, 83.0, 82.5, 'B'),
(6, 8, 90.0, 92.0, 88.0, 89.0, 89.75, 'A'),
-- Semester 7 students
(11, 13, 95.0, 93.0, 92.0, 94.0, 93.5, 'A'),
(11, 14, 88.0, 86.0, 84.0, 87.0, 86.25, 'A');

-- Insert sample absensi data
INSERT INTO absensi (mata_kuliah_id, mahasiswa_id, tanggal, status, keterangan) VALUES
(1, 1, '2024-01-15', 'Hadir', ''),
(1, 2, '2024-01-15', 'Hadir', ''),
(1, 1, '2024-01-22', 'Hadir', ''),
(1, 2, '2024-01-22', 'Alpha', 'Tidak ada keterangan'),
(2, 1, '2024-01-16', 'Hadir', ''),
(2, 2, '2024-01-16', 'Sakit', 'Surat dokter'),
-- Today's attendance
(1, 3, CURDATE(), 'Hadir', ''),
(1, 4, CURDATE(), 'Alpha', ''),
(1, 5, CURDATE(), 'Hadir', ''),
(2, 3, CURDATE(), 'Hadir', ''),
(2, 4, CURDATE(), 'Hadir', ''),
(2, 5, CURDATE(), 'Sakit', 'Surat dokter'),
(6, 6, CURDATE(), 'Hadir', ''),
(6, 7, CURDATE(), 'Hadir', ''),
(6, 8, CURDATE(), 'Hadir', ''),
(7, 6, CURDATE(), 'Izin', 'Keperluan keluarga'),
(7, 7, CURDATE(), 'Hadir', ''),
(7, 8, CURDATE(), 'Hadir', ''),
-- Yesterday's attendance
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Hadir', ''),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Hadir', ''),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Alpha', ''),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Hadir', ''),
(1, 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Sakit', 'Demam'),
-- Week ago attendance
(2, 1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Hadir', ''),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Hadir', ''),
(2, 3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Hadir', ''),
(2, 4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Izin', 'Acara keluarga'),
(2, 5, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Hadir', '');

-- Update some jadwal to have today's schedule
UPDATE jadwal SET hari = DAYNAME(CURDATE()) WHERE id IN (1, 3, 5);

-- Insert system overview settings
INSERT INTO system_settings (setting_key, setting_value, setting_description, updated_by) VALUES
('dashboard_refresh_interval', '300', 'Dashboard auto refresh interval in seconds', 1),
('show_real_time_stats', 'true', 'Enable real-time statistics on dashboard', 1),
('attendance_tracking', 'true', 'Enable attendance tracking system', 1),
('grade_notification', 'true', 'Send notifications when grades are updated', 1);

-- Create a view for dashboard statistics (optional enhancement)
CREATE OR REPLACE VIEW dashboard_stats AS
SELECT 
    'attendance_today' as metric,
    ROUND(
        (SELECT COUNT(*) FROM absensi WHERE status = 'Hadir' AND tanggal = CURDATE()) * 100.0 / 
        NULLIF((SELECT COUNT(*) FROM absensi WHERE tanggal = CURDATE()), 0), 
        1
    ) as value,
    '%' as unit,
    'Persentase kehadiran mahasiswa hari ini' as description
UNION ALL
SELECT 
    'assignment_completion' as metric,
    ROUND(
        (SELECT COUNT(*) FROM nilai WHERE tugas1 IS NOT NULL AND tugas2 IS NOT NULL) * 100.0 / 
        NULLIF((SELECT COUNT(*) FROM kelas), 0), 
        1
    ) as value,
    '%' as unit,
    'Persentase tugas yang telah dikumpulkan' as description
UNION ALL
SELECT 
    'grades_input' as metric,
    ROUND(
        (SELECT COUNT(*) FROM nilai WHERE nilai_akhir IS NOT NULL) * 100.0 / 
        NULLIF((SELECT COUNT(*) FROM kelas), 0), 
        1
    ) as value,
    '%' as unit,
    'Persentase nilai yang telah diinput' as description
UNION ALL
SELECT 
    'active_schedule' as metric,
    ROUND(
        (SELECT COUNT(*) FROM jadwal WHERE hari = DAYNAME(CURDATE())) * 100.0 / 
        NULLIF((SELECT COUNT(*) FROM jadwal), 0), 
        1
    ) as value,
    '%' as unit,
    'Persentase jadwal yang aktif hari ini' as description;