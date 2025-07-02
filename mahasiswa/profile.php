<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Function to get avatar URL
function getAvatarUrl($photo_name) {
    // Define available vector avatars (flat illustration style like the 9 characters shown)
    $vector_avatars = [
        'avatar-1.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character1&size=150&backgroundColor=f8f9fa&clothing=cardigan,blazer,dress&clothingColor=ff6b6b,4ecdc4,ffe66d',
        'avatar-2.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character2&size=150&backgroundColor=f8f9fa&clothing=hoodie,shirt,cardigan&clothingColor=ff6b6b,2c3e50,4ecdc4',
        'avatar-3.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character3&size=150&backgroundColor=f8f9fa&clothing=stripedShirt,hoodie,cardigan&clothingColor=2c3e50,ff6b6b,ffe66d',
        'avatar-4.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character4&size=150&backgroundColor=f8f9fa&clothing=dress,blouse,cardigan&clothingColor=ff6b6b,4ecdc4,2c3e50',
        'avatar-5.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character5&size=150&backgroundColor=f8f9fa&clothing=hoodie,shirt,blazer&clothingColor=ffe66d,ff6b6b,4ecdc4',
        'avatar-6.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character6&size=150&backgroundColor=f8f9fa&clothing=dress,cardigan,blouse&clothingColor=ff6b6b,2c3e50,4ecdc4',
        'avatar-7.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character7&size=150&backgroundColor=f8f9fa&clothing=hoodie,cardigan,shirt&clothingColor=ff6b6b,ffe66d,2c3e50',
        'avatar-8.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character8&size=150&backgroundColor=f8f9fa&clothing=blazer,dress,cardigan&clothingColor=2c3e50,ff6b6b,4ecdc4',
        'avatar-9.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character9&size=150&backgroundColor=f8f9fa&clothing=hoodie,shirt,cardigan&clothingColor=ffe66d,ff6b6b,2c3e50'
    ];
    
    // Return vector avatar URL if available, otherwise return default
    return isset($vector_avatars[$photo_name]) ? $vector_avatars[$photo_name] : $vector_avatars['avatar-1.svg'];
}

// Get mahasiswa information
$user_id = $_SESSION['user_id'];

// Get complete mahasiswa profile data
$profile_query = "SELECT 
    u.*, 
    m.nim, 
    m.nama, 
    m.semester,
    ps.nama as program_studi_nama, 
    f.nama as fakultas_nama,
    d.nama as dosen_wali
FROM users u
JOIN mahasiswa m ON u.id = m.user_id
JOIN program_studi ps ON m.program_studi_id = ps.id
JOIN fakultas f ON ps.fakultas_id = f.id
LEFT JOIN dosen d ON ps.kaprodi = d.nama
WHERE u.id = '$user_id'";

$result = mysqli_query($conn, $profile_query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Data mahasiswa tidak ditemukan!";
    exit();
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
        $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
        $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
        $agama = mysqli_real_escape_string($conn, $_POST['agama']);
        $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
        $email_pribadi = mysqli_real_escape_string($conn, $_POST['email_pribadi']);
        $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
        $kewarganegaraan = mysqli_real_escape_string($conn, $_POST['kewarganegaraan']);
        
        // Update users table
        $update_user_query = "UPDATE users SET 
            full_name = '$nama_lengkap', 
            phone = '$no_telepon',
            alamat = '$alamat'
            WHERE id = '$user_id'";
        
        // Update mahasiswa table
        $update_mahasiswa_query = "UPDATE mahasiswa SET 
            nama = '$nama_lengkap',
            phone = '$no_telepon',
            alamat = '$alamat'
            WHERE user_id = '$user_id'";
        
        if (mysqli_query($conn, $update_user_query) && mysqli_query($conn, $update_mahasiswa_query)) {
            $message = 'Profil berhasil diperbarui!';
            $message_type = 'success';
            
            // Refresh user data
            $result = mysqli_query($conn, $profile_query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $message = 'Gagal memperbarui profil: ' . mysqli_error($conn);
            $message_type = 'error';
        }
        
    } elseif (isset($_POST['change_password'])) {
        // Handle password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $message = 'Password baru dan konfirmasi password tidak cocok!';
            $message_type = 'error';
        } else {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_password_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
                
                if (mysqli_query($conn, $update_password_query)) {
                    $message = 'Password berhasil diubah!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengubah password: ' . mysqli_error($conn);
                    $message_type = 'error';
                }
            } else {
                $message = 'Password saat ini tidak benar!';
                $message_type = 'error';
            }
        }
        
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_photo') {
        $selected_photo = mysqli_real_escape_string($conn, $_POST['selected_photo']);
        
        $update_query = "UPDATE users SET profile_photo = '$selected_photo' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $message = "Foto profil berhasil diperbarui!";
            $message_type = 'success';
            // Refresh user data
            $result = mysqli_query($conn, $profile_query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// Calculate academic progress
$academic_query = "SELECT 
    COUNT(*) as total_mk_diambil,
    SUM(mk.sks) as total_sks_diambil,
    COUNT(CASE WHEN n.grade IS NOT NULL THEN 1 END) as mk_selesai,
    SUM(CASE WHEN n.grade IS NOT NULL THEN mk.sks END) as sks_lulus,
    AVG(CASE 
        WHEN n.grade = 'A' THEN 4.0
        WHEN n.grade = 'A-' THEN 3.7
        WHEN n.grade = 'B+' THEN 3.3
        WHEN n.grade = 'B' THEN 3.0
        WHEN n.grade = 'B-' THEN 2.7
        WHEN n.grade = 'C+' THEN 2.3
        WHEN n.grade = 'C' THEN 2.0
        WHEN n.grade = 'C-' THEN 1.7
        WHEN n.grade = 'D' THEN 1.0
        ELSE NULL
    END) as ipk
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
LEFT JOIN nilai n ON mk.id = n.mata_kuliah_id AND n.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = (SELECT id FROM mahasiswa WHERE user_id = '$user_id')";

$academic_result = mysqli_query($conn, $academic_query);
$academic_data = mysqli_fetch_assoc($academic_result);

$academic_summary = [
    'ipk' => $academic_data['ipk'] ? number_format($academic_data['ipk'], 2) : '0.00',
    'sks_tempuh' => $academic_data['total_sks_diambil'] ?: 0,
    'sks_lulus' => $academic_data['sks_lulus'] ?: 0,
    'total_sks' => 144, // Standard curriculum requirement
    'semester_registrasi' => $user['semester'],
    'prestasi_akademik' => [
        'Mahasiswa Aktif',
        'Program Studi ' . $user['program_studi_nama']
    ]
];

// Student data with real database values
$student_data = [
    'nim' => $user['nim'],
    'nama_lengkap' => $user['nama'],
    'program_studi' => $user['program_studi_nama'],
    'fakultas' => $user['fakultas_nama'],
    'angkatan' => '20' . substr($user['nim'], 0, 2),
    'semester_aktif' => $user['semester'],
    'status_mahasiswa' => 'Aktif',
    'email' => $user['email'],
    'email_pribadi' => $user['email'],
    'no_telepon' => $user['phone'] ?: '',
    'alamat' => $user['alamat'] ?: '',
    'tempat_lahir' => 'Jakarta', // Default, could be added to database
    'tanggal_lahir' => '2003-05-15', // Default, could be added to database
    'jenis_kelamin' => 'Laki-laki', // Default, could be added to database
    'agama' => 'Islam', // Default, could be added to database
    'kewarganegaraan' => 'Indonesia', // Default, could be added to database
    'dosen_wali' => $user['dosen_wali'] ?: 'Belum Ditentukan',
    'foto_profil' => getAvatarUrl($user['profile_photo'] ?? 'avatar-1.svg')
];

// Emergency contact (could be expanded in database)
$emergency_contact = [
    'nama' => 'Orang Tua',
    'hubungan' => 'Orang Tua',
    'no_telepon' => '',
    'alamat' => $user['alamat'] ?: ''
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Portal Mahasiswa MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/mahasiswa_clean.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <h1><i class="fa-solid fa-user"></i> Profil Mahasiswa</h1>
            <div class="page-info">
                <span class="student-info"><?php echo $student_data['nim'] . " - " . $student_data['nama_lengkap']; ?></span>
                <span class="status-badge success"><?php echo $student_data['status_mahasiswa']; ?></span>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fa-solid fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Profile Overview -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-id-card"></i> Informasi Dasar</h2>
                </div>
                <div class="profile-overview">                    <div class="profile-photo">
                        <img src="<?php echo getAvatarUrl($user['profile_photo'] ?? 'avatar-1.svg'); ?>" alt="Foto Profil" id="profileImage">
                        <div class="photo-actions">
                            <button class="btn btn-small btn-primary" onclick="openPhotoModal()">
                                <i class="fa-solid fa-camera"></i> Ubah Foto
                            </button>
                        </div>
                    </div>
                    <div class="profile-basic-info">
                        <h3><?php echo $student_data['nama_lengkap']; ?></h3>
                        <p class="nim"><?php echo $student_data['nim']; ?></p>
                        <div class="basic-details">
                            <div class="detail-item">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <span><?php echo $student_data['program_studi']; ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fa-solid fa-building"></i>
                                <span><?php echo $student_data['fakultas']; ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fa-solid fa-calendar"></i>
                                <span>Angkatan <?php echo $student_data['angkatan']; ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>Semester <?php echo $student_data['semester_aktif']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Summary -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-chart-line"></i> Ringkasan Akademik</h2>
                </div>
                <div class="academic-stats">
                    <div class="stat-item">
                        <div class="stat-icon primary">
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="stat-details">
                            <h4><?php echo $academic_summary['ipk']; ?></h4>
                            <p>IPK</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon success">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h4><?php echo $academic_summary['sks_lulus']; ?></h4>
                            <p>SKS Lulus</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon warning">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="stat-details">
                            <h4><?php echo $academic_summary['sks_tempuh']; ?></h4>
                            <p>SKS Tempuh</p>
                        </div>
                    </div>
                </div>
                <div class="progress-section">
                    <div class="progress-label">
                        Progress Studi: <?php echo round(($academic_summary['sks_lulus'] / $academic_summary['total_sks']) * 100); ?>%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill primary" style="width: <?php echo ($academic_summary['sks_lulus'] / $academic_summary['total_sks']) * 100; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <?php echo $academic_summary['sks_lulus']; ?> / <?php echo $academic_summary['total_sks']; ?> SKS
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Tabs -->
        <div class="content-section full-width">
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-btn active" onclick="openTab(event, 'personalInfo')">
                        <i class="fa-solid fa-user"></i> Data Pribadi
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'contactInfo')">
                        <i class="fa-solid fa-address-book"></i> Kontak
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'academicInfo')">
                        <i class="fa-solid fa-graduation-cap"></i> Akademik
                    </button>
                    <button class="tab-btn" onclick="openTab(event, 'changePassword')">
                        <i class="fa-solid fa-lock"></i> Ubah Password
                    </button>
                </div>

                <!-- Personal Information Tab -->
                <div id="personalInfo" class="tab-content active">
                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($student_data['nama_lengkap']); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nim">NIM</label>
                                <input type="text" id="nim" name="nim" value="<?php echo $student_data['nim']; ?>" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" id="tempat_lahir" name="tempat_lahir" value="<?php echo $student_data['tempat_lahir']; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $student_data['tanggal_lahir']; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <select id="jenis_kelamin" name="jenis_kelamin" class="form-control">
                                    <option value="Laki-laki" <?php echo $student_data['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo $student_data['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="agama">Agama</label>
                                <select id="agama" name="agama" class="form-control">
                                    <option value="Islam" <?php echo $student_data['agama'] == 'Islam' ? 'selected' : ''; ?>>Islam</option>
                                    <option value="Kristen" <?php echo $student_data['agama'] == 'Kristen' ? 'selected' : ''; ?>>Kristen</option>
                                    <option value="Katolik" <?php echo $student_data['agama'] == 'Katolik' ? 'selected' : ''; ?>>Katolik</option>
                                    <option value="Hindu" <?php echo $student_data['agama'] == 'Hindu' ? 'selected' : ''; ?>>Hindu</option>
                                    <option value="Buddha" <?php echo $student_data['agama'] == 'Buddha' ? 'selected' : ''; ?>>Buddha</option>
                                    <option value="Konghucu" <?php echo $student_data['agama'] == 'Konghucu' ? 'selected' : ''; ?>>Konghucu</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fa-solid fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Contact Information Tab -->
                <div id="contactInfo" class="tab-content">
                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="alamat">Alamat</label>
                                <textarea id="alamat" name="alamat" rows="3" class="form-control"><?php echo $student_data['alamat']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="email_institusi">Email Institusi</label>
                                <input type="email" id="email_institusi" name="email_institusi" value="<?php echo $student_data['email']; ?>" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email_pribadi">Email Pribadi</label>
                                <input type="email" id="email_pribadi" name="email_pribadi" value="<?php echo $student_data['email_pribadi']; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="no_telepon">Nomor Telepon</label>
                                <input type="tel" id="no_telepon" name="no_telepon" value="<?php echo $student_data['no_telepon']; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="kewarganegaraan">Kewarganegaraan</label>
                                <input type="text" id="kewarganegaraan" name="kewarganegaraan" value="<?php echo $student_data['kewarganegaraan']; ?>" class="form-control">
                            </div>
                        </div>
                        
                        <div class="section-divider">
                            <h3><i class="fa-solid fa-phone"></i> Kontak Darurat</h3>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="emergency_name">Nama Kontak Darurat</label>
                                <input type="text" id="emergency_name" name="emergency_name" value="<?php echo $emergency_contact['nama']; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="emergency_relation">Hubungan</label>
                                <input type="text" id="emergency_relation" name="emergency_relation" value="<?php echo $emergency_contact['hubungan']; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="emergency_phone">Nomor Telepon</label>
                                <input type="tel" id="emergency_phone" name="emergency_phone" value="<?php echo $emergency_contact['no_telepon']; ?>" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fa-solid fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Academic Information Tab -->
                <div id="academicInfo" class="tab-content">
                    <div class="academic-details">
                        <div class="detail-section">
                            <h3><i class="fa-solid fa-graduation-cap"></i> Informasi Akademik</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Program Studi:</label>
                                    <span><?php echo $student_data['program_studi']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Fakultas:</label>
                                    <span><?php echo $student_data['fakultas']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Angkatan:</label>
                                    <span><?php echo $student_data['angkatan']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Semester Aktif:</label>
                                    <span><?php echo $student_data['semester_aktif']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Status:</label>
                                    <span class="badge success"><?php echo $student_data['status_mahasiswa']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Dosen Wali:</label>
                                    <span><?php echo $student_data['dosen_wali']; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h3><i class="fa-solid fa-trophy"></i> Prestasi Akademik</h3>
                            <div class="achievement-list">
                                <?php foreach ($academic_summary['prestasi_akademik'] as $prestasi): ?>
                                    <div class="achievement-item">
                                        <i class="fa-solid fa-medal"></i>
                                        <span><?php echo $prestasi; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Tab -->
                <div id="changePassword" class="tab-content">
                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="current_password">Password Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="password-requirements">
                            <h4><i class="fa-solid fa-shield-alt"></i> Persyaratan Password</h4>
                            <ul>
                                <li>Minimal 8 karakter</li>
                                <li>Mengandung huruf besar dan kecil</li>
                                <li>Mengandung minimal 1 angka</li>
                                <li>Mengandung minimal 1 karakter khusus</li>
                            </ul>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="fa-solid fa-key"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>    <!-- Photo Selection Modal -->
    <div id="photoModal" class="photo-modal">
        <div class="photo-modal-content">
            <div class="photo-modal-header">
                <h3>Pilih Foto Profil</h3>
                <button class="close-modal" onclick="closePhotoModal()">&times;</button>
            </div>
            <div class="photo-grid">
                <?php
                $vector_avatars = [
                    'avatar-1.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character1&size=150&backgroundColor=f8f9fa&clothing=cardigan,blazer,dress&clothingColor=ff6b6b,4ecdc4,ffe66d',
                    'avatar-2.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character2&size=150&backgroundColor=f8f9fa&clothing=hoodie,shirt,cardigan&clothingColor=ff6b6b,2c3e50,4ecdc4',
                    'avatar-3.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character3&size=150&backgroundColor=f8f9fa&clothing=stripedShirt,hoodie,cardigan&clothingColor=2c3e50,ff6b6b,ffe66d',
                    'avatar-4.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character4&size=150&backgroundColor=f8f9fa&clothing=dress,blouse,cardigan&clothingColor=ff6b6b,4ecdc4,2c3e50',
                    'avatar-5.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character5&size=150&backgroundColor=f8f9fa&clothing=hoodie,shirt,blazer&clothingColor=ffe66d,ff6b6b,4ecdc4',
                    'avatar-6.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character6&size=150&backgroundColor=f8f9fa&clothing=dress,cardigan,blouse&clothingColor=ff6b6b,2c3e50,4ecdc4',
                    'avatar-7.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character7&size=150&backgroundColor=f8f9fa&clothing=hoodie,cardigan,shirt&clothingColor=ff6b6b,ffe66d,2c3e50',
                    'avatar-8.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character8&size=150&backgroundColor=f8f9fa&clothing=blazer,dress,cardigan&clothingColor=2c3e50,ff6b6b,4ecdc4',
                    'avatar-9.svg' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=character9&size=150&backgroundColor=f8f9fa&clothing=hoodie,shirt,cardigan&clothingColor=ffe66d,ff6b6b,2c3e50'
                ];
                
                $index = 0;
                foreach ($vector_avatars as $filename => $url) {
                    echo "<div class='photo-option' id='photo-{$index}' data-filename='{$filename}' data-url='{$url}'>";
                    echo "<img src='{$url}' alt='Avatar Option'>";
                    echo "<div class='photo-overlay'>";
                    echo "<i class='fas fa-check'></i>";
                    echo "</div>";
                    echo "</div>";
                    $index++;
                }
                ?>
            </div>
            <div class="photo-modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePhotoModal()">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveSelectedPhoto()" id="savePhotoBtn" disabled>Simpan</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for photo update -->
    <form id="photoUpdateForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_photo">
        <input type="hidden" name="selected_photo" id="selectedPhotoInput">
    </form>

    <script>
        // Photo Modal Functions
        let selectedPhoto = null;

        function openPhotoModal() {
            document.getElementById('photoModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Reset selection
            document.querySelectorAll('.photo-option').forEach(option => {
                option.classList.remove('selected');
            });
            selectedPhoto = null;
            document.getElementById('savePhotoBtn').disabled = true;
        }

        function selectPhoto(filename, url) {
            // Remove previous selection
            document.querySelectorAll('.photo-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Find and select the clicked photo
            const photoOptions = document.querySelectorAll('.photo-option');
            photoOptions.forEach(option => {
                if (option.onclick.toString().includes(filename)) {
                    option.classList.add('selected');
                }
            });
            
            selectedPhoto = filename;
            document.getElementById('savePhotoBtn').disabled = false;
        }

        function saveSelectedPhoto() {
            if (selectedPhoto) {
                document.getElementById('selectedPhotoInput').value = selectedPhoto;
                document.getElementById('photoUpdateForm').submit();
            }
        }

        // Event delegation for photo selection
        document.addEventListener('DOMContentLoaded', function() {
            const photoGrid = document.querySelector('.photo-grid');
            if (photoGrid) {
                photoGrid.addEventListener('click', function(event) {
                    const photoOption = event.target.closest('.photo-option');
                    if (photoOption) {
                        const filename = photoOption.getAttribute('data-filename');
                        const url = photoOption.getAttribute('data-url');
                        
                        // Remove previous selection
                        document.querySelectorAll('.photo-option').forEach(option => {
                            option.classList.remove('selected');
                        });
                        
                        // Add selection to clicked photo
                        photoOption.classList.add('selected');
                        
                        selectedPhoto = filename;
                        document.getElementById('savePhotoBtn').disabled = false;
                    }
                });
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('photoModal');
            if (event.target === modal) {
                closePhotoModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePhotoModal();
            }
        });

        // Tab functionality
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        // Password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
