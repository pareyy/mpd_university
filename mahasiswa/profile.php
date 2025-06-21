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
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $message = 'Profil berhasil diperbarui!';
        $message_type = 'success';
    } elseif (isset($_POST['change_password'])) {
        // Handle password change
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $message = 'Password baru dan konfirmasi password tidak cocok!';
            $message_type = 'error';
        } else {
            $message = 'Password berhasil diubah!';
            $message_type = 'success';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_photo') {
        $selected_photo = mysqli_real_escape_string($conn, $_POST['selected_photo']);
        
        $update_query = "UPDATE users SET profile_photo = '$selected_photo' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $message = "Foto profil berhasil diperbarui!";
            $message_type = 'success';
            // Refresh user data
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// Sample student data - replace with actual database queries
$student_data = [
    'nim' => $user['nim'] ?? '2021080001',
    'nama_lengkap' => $user['full_name'] ?? 'Ahmad Fadhil Rahman',
    'program_studi' => 'Teknik Informatika',
    'fakultas' => 'Fakultas Teknologi Informasi',
    'angkatan' => '2021',
    'semester_aktif' => '6',
    'status_mahasiswa' => 'Aktif',
    'email' => $user['email'] ?? 'ahmad.fadhil@student.mpd.ac.id',
    'email_pribadi' => 'ahmad.fadhil@gmail.com',
    'no_telepon' => $user['phone'] ?? '081234567890',
    'alamat' => $user['alamat'] ?? 'Jl. Sudirman No. 123, Jakarta Pusat',
    'tempat_lahir' => 'Jakarta',
    'tanggal_lahir' => '2003-05-15',
    'jenis_kelamin' => 'Laki-laki',
    'agama' => 'Islam',
    'kewarganegaraan' => 'Indonesia',
    'dosen_wali' => 'Dr. Budi Santoso, M.Kom',
    'foto_profil' => getAvatarUrl($user['profile_photo'] ?? 'avatar-1.svg')
];

// Academic summary
$academic_summary = [
    'ipk' => 3.65,
    'sks_tempuh' => 108,
    'sks_lulus' => 102,
    'total_sks' => 144,
    'semester_registrasi' => 6,
    'prestasi_akademik' => [
        'Dean\'s List Semester 5',
        'Juara 2 Programming Contest 2023',
        'Best Student Award 2022'
    ]
];

// Emergency contact
$emergency_contact = [
    'nama' => 'Siti Rahman (Ibu)',
    'hubungan' => 'Orang Tua',
    'no_telepon' => '081234567891',
    'alamat' => 'Jl. Sudirman No. 123, Jakarta Pusat'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Portal Mahasiswa MPD University</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $student_data['nama_lengkap']; ?>" class="form-control">
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
            </div>            <div class="photo-grid">                <?php                $vector_avatars = [
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
                
                foreach ($vector_avatars as $filename => $url) {
                    echo "<div class='photo-option' onclick='selectPhoto(\"$filename\", \"$url\")'>";
                    echo "<img src='$url' alt='Avatar Option'>";
                    echo "<div class='photo-overlay'>";
                    echo "<i class='fas fa-check'></i>";
                    echo "</div>";
                    echo "</div>";
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

    <style>
        /* Photo Modal Styles */
        .photo-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease-out;
        }

        .photo-modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease-out;
        }

        .photo-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .photo-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            color: white;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .close-modal:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 16px;
            padding: 24px;
            max-height: 400px;
            overflow-y: auto;
        }

        .photo-option {
            position: relative;
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
        }

        .photo-option:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .photo-option.selected {
            border-color: #667eea;
            transform: scale(1.05);
        }

        .photo-option img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(102, 126, 234, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .photo-option.selected .photo-overlay {
            opacity: 1;
        }

        .photo-overlay i {
            color: white;
            font-size: 2rem;
        }

        .photo-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background-color: #f8f9fa;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.6;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .photo-modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .photo-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 12px;
                padding: 16px;
            }

            .photo-modal-header,
            .photo-modal-footer {
                padding: 16px;
            }        }
    </style>

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
            
            // Add selection to clicked photo
            event.currentTarget.classList.add('selected');
            
            selectedPhoto = filename;
            document.getElementById('savePhotoBtn').disabled = false;
        }

        function saveSelectedPhoto() {
            if (selectedPhoto) {
                document.getElementById('selectedPhotoInput').value = selectedPhoto;
                document.getElementById('photoUpdateForm').submit();
            }
        }

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

        // Photo upload functionality
        function changePhoto() {
            document.getElementById('photoModal').style.display = 'block';
        }

        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
            document.getElementById('photoForm').reset();
            document.getElementById('photoPreview').style.display = 'none';
        }

        // Photo preview
        document.getElementById('photoFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Photo form submission
        document.getElementById('photoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Simulate photo upload (replace with actual AJAX call)
            alert('Foto profil berhasil diupload! (Simulasi)');
            closePhotoModal();
            
            // Update profile image
            const newImageSrc = document.getElementById('previewImage').src;
            document.getElementById('profileImage').src = newImageSrc;
        });

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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('photoModal');
            if (event.target === modal) {
                closePhotoModal();
            }
        }
    </script>
</body>
</html>
