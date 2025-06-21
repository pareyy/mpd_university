-- Script migrasi untuk memindahkan dari database lama ke struktur database terpadu
-- Jalankan script ini setelah membuat database_unified.sql

-- ===================================
-- BACKUP DATA EXISTING (jika ada)
-- ===================================

-- Backup data users yang sudah ada
CREATE TEMPORARY TABLE IF NOT EXISTS temp_users_backup AS 
SELECT * FROM users WHERE 1=0;

INSERT INTO temp_users_backup 
SELECT * FROM users;

-- ===================================
-- MIGRASI DATA
-- ===================================

-- Update struktur tabel users yang sudah ada
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) AFTER username,
ADD COLUMN IF NOT EXISTS nip VARCHAR(20) AFTER full_name,
ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER nip,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email,
ADD COLUMN IF NOT EXISTS alamat TEXT AFTER phone,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL;

-- Tambahkan UNIQUE constraint ke username jika belum ada
ALTER TABLE users ADD CONSTRAINT unique_username UNIQUE (username);

-- Update tabel mahasiswa yang sudah ada untuk menambah relasi dengan users
ALTER TABLE mahasiswa 
ADD COLUMN IF NOT EXISTS user_id INT UNIQUE AFTER id,
ADD COLUMN IF NOT EXISTS program_studi_id INT AFTER program_studi,
ADD COLUMN IF NOT EXISTS semester INT AFTER program_studi_id,
ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER semester,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD CONSTRAINT fk_mahasiswa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_mahasiswa_prodi FOREIGN KEY (program_studi_id) REFERENCES program_studi(id) ON DELETE SET NULL;

-- Update tabel dosen untuk struktur yang baru
ALTER TABLE dosen 
ADD COLUMN IF NOT EXISTS user_id INT UNIQUE AFTER id,
ADD COLUMN IF NOT EXISTS fakultas_id INT AFTER bidang_keahlian,
ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER fakultas_id,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email,
ADD COLUMN IF NOT EXISTS alamat TEXT AFTER phone,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD CONSTRAINT fk_dosen_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_dosen_fakultas FOREIGN KEY (fakultas_id) REFERENCES fakultas(id) ON DELETE SET NULL;

-- Update tabel mata_kuliah untuk struktur yang baru
ALTER TABLE mata_kuliah 
CHANGE COLUMN kode kode_mk VARCHAR(10),
CHANGE COLUMN nama_matkul nama_mk VARCHAR(100),
ADD COLUMN IF NOT EXISTS deskripsi TEXT AFTER semester,
ADD COLUMN IF NOT EXISTS dosen_id INT AFTER deskripsi,
ADD COLUMN IF NOT EXISTS program_studi_id INT AFTER dosen_id,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD CONSTRAINT fk_mata_kuliah_dosen FOREIGN KEY (dosen_id) REFERENCES dosen(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_mata_kuliah_prodi FOREIGN KEY (program_studi_id) REFERENCES program_studi(id) ON DELETE SET NULL;

-- Update tabel pengumuman
ALTER TABLE pengumuman 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update tabel galeri
ALTER TABLE galeri 
ADD COLUMN IF NOT EXISTS deskripsi TEXT AFTER gambar,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update tabel kontak
ALTER TABLE kontak 
ADD COLUMN IF NOT EXISTS status ENUM('baru', 'dibaca', 'direspon') DEFAULT 'baru' AFTER pesan,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update tabel fakultas
ALTER TABLE fakultas 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update tabel program_studi
ALTER TABLE program_studi 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ===================================
-- BERSIHKAN DATA DUPLICATE
-- ===================================

-- Hapus duplikasi jika ada
DELETE t1 FROM fakultas t1
INNER JOIN fakultas t2 
WHERE t1.id < t2.id AND t1.nama = t2.nama;

DELETE t1 FROM program_studi t1
INNER JOIN program_studi t2 
WHERE t1.id < t2.id AND t1.nama = t2.nama AND t1.fakultas_id = t2.fakultas_id;

-- ===================================
-- UPDATE DATA YANG SUDAH ADA
-- ===================================

-- Update program_studi_id di mahasiswa berdasarkan nama program_studi
UPDATE mahasiswa m 
JOIN program_studi ps ON m.program_studi = ps.nama 
SET m.program_studi_id = ps.id 
WHERE m.program_studi_id IS NULL;

-- Update fakultas_id di dosen berdasarkan nama fakultas
UPDATE dosen d 
JOIN fakultas f ON d.fakultas = f.nama 
SET d.fakultas_id = f.id 
WHERE d.fakultas_id IS NULL;

-- ===================================
-- PESAN MIGRASI
-- ===================================

SELECT 'Migrasi database berhasil dilakukan!' as status;
SELECT 'Pastikan untuk mengupdate kode PHP yang menggunakan struktur database lama' as catatan;
