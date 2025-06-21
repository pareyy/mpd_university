-- Script untuk menambahkan fitur foto profil
-- Jalankan script ini di phpMyAdmin atau MySQL client

-- Tambahkan kolom profile_photo ke tabel users
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT 'avatar-1.jpg' AFTER alamat;

-- Update existing admin user dengan foto default
UPDATE users SET profile_photo = 'avatar-1.jpg' WHERE role = 'admin' AND (profile_photo IS NULL OR profile_photo = '');

-- Update existing dosen dengan foto default
UPDATE users SET profile_photo = 'avatar-2.jpg' WHERE role = 'dosen' AND (profile_photo IS NULL OR profile_photo = '');

-- Update existing mahasiswa dengan foto default  
UPDATE users SET profile_photo = 'avatar-3.jpg' WHERE role = 'mahasiswa' AND (profile_photo IS NULL OR profile_photo = '');

-- Tampilkan hasil untuk verifikasi
SELECT id, username, role, profile_photo FROM users WHERE role IN ('admin', 'dosen', 'mahasiswa');
