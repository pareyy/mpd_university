# Database Terpadu - Dokumentasi

## Gambaran Umum

Database ini menggabungkan semua skema database yang sebelumnya terpisah menjadi satu struktur database yang terpadu dan konsisten untuk Sistem Akademik MPD University.

## Perubahan Utama

### 1. Penggabungan Database Files
- `database.sql` - Database dasar
- `database_dosen.sql` - Database sistem dosen  
- `data_dump.sql` - Data sample dan tabel tambahan

### 2. Struktur Database Baru

#### Tabel Utama Sistem
- **users** - Authentication dan profil dasar pengguna
- **fakultas** - Data fakultas
- **program_studi** - Data program studi dengan relasi ke fakultas

#### Tabel Dosen
- **dosen** - Profil lengkap dosen dengan relasi ke users dan fakultas

#### Tabel Mahasiswa  
- **mahasiswa** - Profil lengkap mahasiswa dengan relasi ke users dan program_studi

#### Tabel Akademik
- **mata_kuliah** - Data mata kuliah dengan relasi ke dosen dan program_studi
- **jadwal** - Jadwal mengajar
- **kelas** - Relasi mahasiswa dengan mata kuliah (enrollment)
- **nilai** - Sistem penilaian mahasiswa
- **absensi** - Sistem absensi mahasiswa

#### Tabel Konten & Informasi
- **pengumuman** - Pengumuman kampus
- **kalender_akademik** - Kalender kegiatan akademik
- **berita_akademik** - Berita dan artikel akademik
- **galeri** - Galeri foto kampus
- **kontak** - Pesan kontak dari pengunjung

## Relasi Database

### Relasi Utama:
1. **users** → **dosen** (one-to-one)
2. **users** → **mahasiswa** (one-to-one) 
3. **fakultas** → **program_studi** (one-to-many)
4. **fakultas** → **dosen** (one-to-many)
5. **program_studi** → **mahasiswa** (one-to-many)
6. **program_studi** → **mata_kuliah** (one-to-many)
7. **dosen** → **mata_kuliah** (one-to-many)
8. **mata_kuliah** → **jadwal** (one-to-many)
9. **mata_kuliah** + **mahasiswa** → **kelas** (many-to-many)
10. **mata_kuliah** + **mahasiswa** → **nilai** (one-to-one per mahasiswa per mata kuliah)
11. **mata_kuliah** + **mahasiswa** → **absensi** (one-to-many)

## Fitur Baru

### 1. Konsistensi Data
- Semua tabel menggunakan foreign key constraints
- Cascade delete untuk menjaga integritas data
- Unique constraints untuk mencegah duplikasi

### 2. Timestamps
- Semua tabel memiliki `created_at`
- Tabel yang sering diupdate memiliki `updated_at`
- Tracking `last_login` untuk users

### 3. Enum Values
- Status yang konsisten menggunakan ENUM
- Hari dalam jadwal menggunakan ENUM
- Kategori kalender akademik menggunakan ENUM

### 4. Struktur yang Lebih Fleksibel
- Tabel dosen dan mahasiswa terpisah dari users untuk profil yang lebih lengkap
- Program studi terhubung dengan fakultas
- Mata kuliah terhubung dengan dosen dan program studi

## Cara Menggunakan

### 1. Database Baru (Fresh Install)
```sql
-- Jalankan file ini untuk membuat database baru
SOURCE database_unified.sql;
```

### 2. Migrasi dari Database Lama
```sql
-- 1. Backup database lama terlebih dahulu
-- 2. Jalankan script migrasi
SOURCE migrate_to_unified.sql;
```

## Sample Data

Database sudah termasuk sample data:
- 5 Fakultas
- 10 Program Studi  
- 2 Dosen
- 2 Mahasiswa
- 3 Mata Kuliah
- Pengumuman dan berita akademik
- Kalender akademik

## Update Kode PHP

Setelah migrasi database, pastikan untuk mengupdate:

1. **Query SQL** - Sesuaikan nama kolom dan tabel
2. **Foreign Key References** - Gunakan relasi yang baru
3. **Join Statements** - Manfaatkan relasi yang sudah ada
4. **Validation** - Sesuaikan dengan constraint baru

## Contoh Query

### Get Dosen dengan Fakultas
```sql
SELECT d.nama, d.nidn, f.nama as fakultas 
FROM dosen d 
JOIN fakultas f ON d.fakultas_id = f.id;
```

### Get Mahasiswa dengan Program Studi dan Fakultas
```sql
SELECT m.nama, m.nim, ps.nama as program_studi, f.nama as fakultas
FROM mahasiswa m
JOIN program_studi ps ON m.program_studi_id = ps.id
JOIN fakultas f ON ps.fakultas_id = f.id;
```

### Get Mata Kuliah dengan Dosen dan Program Studi
```sql
SELECT mk.nama_mk, mk.kode_mk, d.nama as dosen, ps.nama as program_studi
FROM mata_kuliah mk
JOIN dosen d ON mk.dosen_id = d.id
JOIN program_studi ps ON mk.program_studi_id = ps.id;
```

## Backup dan Maintenance

1. **Regular Backup** - Backup database secara rutin
2. **Index Optimization** - Monitor performance query
3. **Data Cleanup** - Bersihkan data yang tidak diperlukan secara berkala
4. **Foreign Key Check** - Pastikan integritas referensi terjaga

## Troubleshooting

### Error Foreign Key Constraint
- Pastikan data parent sudah ada sebelum insert data child
- Check apakah ada data orphan yang perlu dibersihkan

### Error Duplicate Entry
- Check unique constraints
- Gunakan INSERT IGNORE atau ON DUPLICATE KEY UPDATE jika diperlukan

### Performance Issues
- Tambahkan index pada kolom yang sering di-query
- Optimize join queries
- Consider partitioning untuk tabel besar
