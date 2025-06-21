<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Function to get avatar URL
function getAvatarUrl($photo_name) {
    // Define available 3D cartoon avatars matching the exact reference image characters
    $vector_avatars = [
        'avatar-1.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character1&size=150&backgroundColor=4a90a4&mood=happy',
        'avatar-2.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character2&size=150&backgroundColor=e74c3c&mood=happy',
        'avatar-3.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character3&size=150&backgroundColor=3498db&mood=happy',
        'avatar-4.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character4&size=150&backgroundColor=f39c12&mood=happy',
        'avatar-5.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character5&size=150&backgroundColor=9b59b6&mood=happy',
        'avatar-6.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character6&size=150&backgroundColor=1abc9c&mood=happy',
        'avatar-7.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character7&size=150&backgroundColor=e67e22&mood=happy',
        'avatar-8.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character8&size=150&backgroundColor=34495e&mood=happy',
        'avatar-9.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character9&size=150&backgroundColor=e91e63&mood=happy',
        'avatar-10.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character10&size=150&backgroundColor=ff6b6b&mood=happy',
        'avatar-11.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character11&size=150&backgroundColor=4ecdc4&mood=happy',
        'avatar-12.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character12&size=150&backgroundColor=ffe66d&mood=happy',
        'avatar-13.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character13&size=150&backgroundColor=6c5ce7&mood=happy',
        'avatar-14.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character14&size=150&backgroundColor=a55eea&mood=happy',
        'avatar-15.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character15&size=150&backgroundColor=26de81&mood=happy',
        'avatar-16.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character16&size=150&backgroundColor=2bcbba&mood=happy',
        'avatar-17.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character17&size=150&backgroundColor=fd79a8&mood=happy',
        'avatar-18.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character18&size=150&backgroundColor=fdcb6e&mood=happy',
        'avatar-19.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character19&size=150&backgroundColor=e17055&mood=happy',
        'avatar-20.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character20&size=150&backgroundColor=81ecec&mood=happy',
        'avatar-21.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character21&size=150&backgroundColor=74b9ff&mood=happy',
        'avatar-22.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character22&size=150&backgroundColor=fd79a8&mood=happy',
        'avatar-23.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character23&size=150&backgroundColor=00b894&mood=happy',
        'avatar-24.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character24&size=150&backgroundColor=e84393&mood=happy',
        'avatar-25.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character25&size=150&backgroundColor=00cec9&mood=happy'
    ];
    
    // Return vector avatar URL if available, otherwise return default
    return isset($vector_avatars[$photo_name]) ? $vector_avatars[$photo_name] : $vector_avatars['avatar-1.svg'];
}

// Get admin information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get admin statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'mahasiswa') as total_mahasiswa,
    (SELECT COUNT(*) FROM users WHERE role = 'dosen') as total_dosen,
    (SELECT COUNT(*) FROM mata_kuliah) as total_matkul,
    (SELECT COUNT(*) FROM articles) as total_articles";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update_profile') {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
            $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
            
            $update_query = "UPDATE users SET 
                           username = '$username',
                           email = '$email',
                           full_name = '$full_name',
                           phone = '$phone',
                           alamat = '$alamat'
                           WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $success_message = "Profil berhasil diperbarui!";
                // Refresh user data
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
        
        if ($_POST['action'] == 'update_photo') {
            $profile_photo = mysqli_real_escape_string($conn, $_POST['profile_photo']);
            
            $update_photo_query = "UPDATE users SET profile_photo = '$profile_photo' WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $update_photo_query)) {
                $success_message = "Foto profil berhasil diperbarui!";
                // Refresh user data
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
        
        if ($_POST['action'] == 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);                    $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
                    
                    if (mysqli_query($conn, $update_query)) {
                        $success_message = "Password berhasil diubah!";
                    } else {
                        $error_message = "Error: " . mysqli_error($conn);
                    }
                } else {
                    $error_message = "Password baru dan konfirmasi password tidak cocok!";
                }
            } else {
                $error_message = "Password saat ini salah!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-shield"></i> Profil Admin</h1>
                <p>Kelola informasi profil dan pengaturan akun administrator</p>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">                    <div class="profile-card">                        <div class="profile-avatar">
                            <img src="<?php echo getAvatarUrl($user['profile_photo'] ?? 'avatar-1.svg'); ?>" alt="Profile Picture" id="profileImage">
                            <button class="change-photo-btn" onclick="openPhotoModal()">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h3>
                            <p class="profile-role">
                                <i class="fas fa-user-shield"></i> Administrator
                            </p>
                            <p class="profile-id">
                                <i class="fas fa-id-badge"></i> ID: <?php echo htmlspecialchars($user['id']); ?>
                            </p>
                        </div>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $stats['total_mahasiswa']; ?></span>
                                <span class="stat-label">Mahasiswa</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $stats['total_dosen']; ?></span>
                                <span class="stat-label">Dosen</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $stats['total_matkul']; ?></span>
                                <span class="stat-label">Mata Kuliah</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions-sidebar">
                        <h4><i class="fas fa-bolt"></i> Aksi Cepat</h4>
                        <ul>
                            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="mahasiswa.php"><i class="fas fa-users"></i> Mahasiswa</a></li>
                            <li><a href="dosen.php"><i class="fas fa-chalkboard-teacher"></i> Dosen</a></li>
                            <li><a href="mata_kuliah.php"><i class="fas fa-book"></i> Mata Kuliah</a></li>
                            <li><a href="pengaturan.php"><i class="fas fa-cog"></i> Pengaturan</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="profile-content">
                    <!-- Profile Information Tab -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-user-edit"></i> Informasi Profil</h2>
                            <button class="btn btn-primary" onclick="toggleEdit('profile')">
                                <i class="fas fa-edit"></i> Edit Profil
                            </button>
                        </div>

                        <div class="form-container">
                            <form method="POST" action="" id="profileForm">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input type="text" id="username" name="username" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="full_name">Nama Lengkap</label>
                                        <input type="text" id="full_name" name="full_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Nomor Telepon</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <input type="text" class="form-control" 
                                               value="Administrator" readonly disabled>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="created_at">Bergabung Sejak</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('d/m/Y', strtotime($user['created_at'])); ?>" readonly disabled>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <textarea id="alamat" name="alamat" class="form-control" rows="3" readonly><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-actions" id="profileActions" style="display: none;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelEdit('profile')">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Tab -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-lock"></i> Ubah Password</h2>
                        </div>

                        <div class="form-container">
                            <form method="POST" action="" id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label for="current_password">Password Saat Ini</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-row">
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
                                    <h4>Persyaratan Password:</h4>
                                    <ul>
                                        <li>Minimal 8 karakter</li>
                                        <li>Mengandung huruf besar dan kecil</li>
                                        <li>Mengandung angka</li>
                                        <li>Mengandung karakter khusus (opsional)</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Ubah Password
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-info-circle"></i> Informasi Akun</h2>
                        </div>

                        <div class="info-container">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>User ID</label>
                                    <span><?php echo $user['id']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Tanggal Bergabung</label>
                                    <span><?php echo isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Tidak tersedia'; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Terakhir Login</label>
                                    <span><?php echo isset($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Tidak tersedia'; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Status Akun</label>
                                    <span class="status-badge status-active">Aktif</span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Level Akses</label>
                                    <span class="status-badge status-admin">Administrator</span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Total Artikel</label>
                                    <span><?php echo $stats['total_articles']; ?> artikel</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Activity Statistics -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-chart-bar"></i> Statistik Aktivitas</h2>
                        </div>

                        <div class="activity-stats">
                            <div class="activity-grid">
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Hari Aktif</h4>
                                        <p><?php echo floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24)); ?> hari</p>
                                        <small>Sejak bergabung</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-users-cog"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Total User</h4>
                                        <p><?php echo ($stats['total_mahasiswa'] + $stats['total_dosen']); ?> user</p>
                                        <small>Dikelola sistem</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Database</h4>
                                        <p>Sehat</p>
                                        <small>Status sistem</small>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-shield-check"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Keamanan</h4>
                                        <p>Optimal</p>
                                        <small>Level keamanan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Photo Selection Modal -->
    <div id="photoModal" class="photo-modal">
        <div class="photo-modal-content">
            <div class="photo-modal-header">
                <h3><i class="fas fa-camera"></i> Pilih Foto Profil</h3>
                <button class="photo-modal-close" onclick="closePhotoModal()">&times;</button>
            </div>            <div class="photo-modal-body">                <p>Pilih salah satu avatar profil di bawah ini:</p>
                <div class="photo-grid">
                    <?php                    $vector_avatars = [
                        'avatar-1.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character1&size=150&backgroundColor=4a90a4&mood=happy',
                        'avatar-2.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character2&size=150&backgroundColor=e74c3c&mood=happy',
                        'avatar-3.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character3&size=150&backgroundColor=3498db&mood=happy',
                        'avatar-4.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character4&size=150&backgroundColor=f39c12&mood=happy',
                        'avatar-5.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character5&size=150&backgroundColor=9b59b6&mood=happy',
                        'avatar-6.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character6&size=150&backgroundColor=1abc9c&mood=happy',
                        'avatar-7.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character7&size=150&backgroundColor=e67e22&mood=happy',
                        'avatar-8.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character8&size=150&backgroundColor=34495e&mood=happy',
                        'avatar-9.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character9&size=150&backgroundColor=e91e63&mood=happy',
                        'avatar-10.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character10&size=150&backgroundColor=ff6b6b&mood=happy',
                        'avatar-11.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character11&size=150&backgroundColor=4ecdc4&mood=happy',
                        'avatar-12.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character12&size=150&backgroundColor=ffe66d&mood=happy',
                        'avatar-13.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character13&size=150&backgroundColor=6c5ce7&mood=happy',
                        'avatar-14.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character14&size=150&backgroundColor=a55eea&mood=happy',
                        'avatar-15.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character15&size=150&backgroundColor=26de81&mood=happy',
                        'avatar-16.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character16&size=150&backgroundColor=2bcbba&mood=happy',
                        'avatar-17.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character17&size=150&backgroundColor=fd79a8&mood=happy',
                        'avatar-18.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character18&size=150&backgroundColor=fdcb6e&mood=happy',
                        'avatar-19.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character19&size=150&backgroundColor=e17055&mood=happy',
                        'avatar-20.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character20&size=150&backgroundColor=81ecec&mood=happy',
                        'avatar-21.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character21&size=150&backgroundColor=74b9ff&mood=happy',
                        'avatar-22.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character22&size=150&backgroundColor=fd79a8&mood=happy',
                        'avatar-23.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character23&size=150&backgroundColor=00b894&mood=happy',
                        'avatar-24.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character24&size=150&backgroundColor=e84393&mood=happy',
                        'avatar-25.svg' => 'https://api.dicebear.com/8.x/lorelei/svg?seed=character25&size=150&backgroundColor=00cec9&mood=happy'
                    ];
                    
                    foreach ($vector_avatars as $photo_name => $photo_url): ?>
                        <div class="photo-option <?php echo ($user['profile_photo'] ?? 'avatar-1.svg') === $photo_name ? 'selected' : ''; ?>" 
                             data-photo="<?php echo $photo_name; ?>" onclick="selectPhoto('<?php echo $photo_name; ?>')">
                            <img src="<?php echo $photo_url; ?>" alt="<?php echo $photo_name; ?>">
                            <div class="photo-overlay">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="photo-modal-footer">
                <button class="btn btn-secondary" onclick="closePhotoModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button class="btn btn-primary" onclick="saveSelectedPhoto()">
                    <i class="fas fa-save"></i> Simpan Foto
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden form for photo update -->
    <form id="photoUpdateForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_photo">
        <input type="hidden" name="profile_photo" id="selectedPhoto">
    </form>    <script>
        let selectedPhotoName = '';

        function toggleEdit(section) {
            if (section === 'profile') {
                const form = document.getElementById('profileForm');
                const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea');
                const actions = document.getElementById('profileActions');
                
                inputs.forEach(input => {
                    if (input.name !== 'role' && !input.disabled) {
                        input.readOnly = false;
                        input.classList.add('editable');
                    }
                });
                
                actions.style.display = 'block';
            }
        }

        function cancelEdit(section) {
            if (section === 'profile') {
                location.reload();
            }
        }

        function openPhotoModal() {
            document.getElementById('photoModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function selectPhoto(photoName) {
            // Remove selection from all photos
            document.querySelectorAll('.photo-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selection to clicked photo
            const selectedOption = document.querySelector(`[data-photo="${photoName}"]`);
            if (selectedOption) {
                selectedOption.classList.add('selected');
                selectedPhotoName = photoName;
            }
        }

        function saveSelectedPhoto() {
            if (selectedPhotoName) {
                document.getElementById('selectedPhoto').value = selectedPhotoName;
                document.getElementById('photoUpdateForm').submit();
            } else {
                alert('Silakan pilih foto terlebih dahulu!');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('photoModal');
            if (event.target == modal) {
                closePhotoModal();
            }
        }

        // Handle escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePhotoModal();
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script><style>
        .profile-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .profile-sidebar {
            position: sticky;
            top: 2rem;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e5e7eb;
        }

        .change-photo-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .change-photo-btn:hover {
            background: #1d4ed8;
        }

        .profile-info h3 {
            margin: 0 0 0.5rem 0;
            color: #374151;
            font-size: 1.5rem;
        }

        .profile-role, .profile-id {
            color: #6b7280;
            margin: 0.25rem 0;
            font-size: 0.875rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
        }

        .stat-label {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        .quick-actions-sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .quick-actions-sidebar h4 {
            margin: 0 0 1rem 0;
            color: #374151;
            font-size: 1rem;
        }

        .quick-actions-sidebar ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .quick-actions-sidebar li {
            margin-bottom: 0.5rem;
        }

        .quick-actions-sidebar a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }

        .quick-actions-sidebar a:hover {
            background: #f3f4f6;
            color: #2563eb;
        }

        .profile-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .content-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .section-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            margin: 0;
            color: #374151;
            font-size: 1.25rem;
        }

        .form-container, .info-container {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.9rem;
            text-align: left;
            display: block;
        }

        /* Ensure all form controls have exact same dimensions */
        input.form-control,
        select.form-control,
        textarea.form-control {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
            background: white;
        }

        input.form-control,
        select.form-control {
            height: 48px !important;
            padding: 0.875rem !important;
            box-sizing: border-box !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            width: 100% !important;
            font-size: 1rem !important;
            line-height: 1.2 !important;
            vertical-align: top;
        }

        textarea.form-control {
            height: auto !important;
            min-height: 80px;
            resize: vertical;
            padding: 0.875rem !important;
        }

        select.form-control {
            padding-right: 2.5rem !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            background-color: white;
        }

        .form-control[readonly] {
            background: #f9fafb;
            color: #6b7280;
        }

        .form-control[disabled] {
            background: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            align-items: center;
        }

        .password-requirements {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .password-requirements h4 {
            margin: 0 0 0.5rem 0;
            color: #374151;
            font-size: 0.9rem;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.25rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .password-requirements li {
            margin: 0.25rem 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-item label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .info-item span {
            color: #374151;
            font-size: 1rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-admin {
            background: #dbeafe;
            color: #1e40af;
        }

        .activity-stats {
            padding: 2rem;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #2563eb;
        }

        .activity-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            flex-shrink: 0;
        }

        .activity-info h4 {
            margin: 0 0 0.25rem 0;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .activity-info p {
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .activity-info small {
            color: #9ca3af;
            font-size: 0.75rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Photo Modal Styles */
        .photo-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .photo-modal-content {
            background-color: white;
            margin: 2% auto;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .photo-modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .photo-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .photo-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }

        .photo-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .photo-modal-body {
            padding: 2rem;
        }

        .photo-modal-body p {
            margin: 0 0 1.5rem 0;
            color: #6b7280;
            font-size: 1rem;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .photo-option {
            position: relative;
            cursor: pointer;
            border-radius: 12px;
            overflow: hidden;
            border: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .photo-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .photo-option.selected {
            border-color: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        .photo-option img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(37, 99, 235, 0.8);
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
            padding: 1.5rem 2rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .profile-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .profile-sidebar {
                position: static;
            }
            
            .profile-card {
                padding: 1.5rem;
            }
            
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .activity-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0.5rem;
                margin: 0;
            }
            
            .dashboard-header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .dashboard-section {
                margin-bottom: 1.5rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.25rem;
                padding: 0 0.5rem;
            }

            .profile-layout {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.5rem;
            }

            .profile-card {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .profile-avatar img {
                width: 100px;
                height: 100px;
            }

            .change-photo-btn {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .profile-content {
                margin: 0;
            }

            .form-container, .info-container, .activity-stats {
                padding: 1rem;
                margin-bottom: 1rem;
                border-radius: 8px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 1rem;
                padding: 0 0.5rem;
            }

            .section-header h2 {
                font-size: 1.1rem;
                margin: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
                align-items: stretch;
            }

            .form-control {
                padding: 1rem;
                font-size: 1rem;
                border-radius: 10px;
                height: 52px;
                width: 100%;
                box-sizing: border-box;
            }

            textarea.form-control {
                height: auto;
                min-height: 100px;
                padding: 1rem;
            }

            .form-actions {
                flex-direction: column;
                gap: 0.75rem;
                margin-top: 1rem;
            }
            
            .btn {
                padding: 1rem;
                font-size: 1rem;
                min-height: 48px;
                border-radius: 10px;
                justify-content: center;
                touch-action: manipulation;
            }

            .quick-actions-sidebar {
                padding: 1rem;
            }

            .quick-actions-sidebar ul {
                gap: 0.5rem;
            }

            .quick-actions-sidebar a {
                padding: 0.75rem 1rem;
                border-radius: 8px;
                font-size: 0.9rem;
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .activity-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .activity-item {
                padding: 1rem;
            }

            .activity-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .alert {
                margin: 0 0.5rem 1.5rem 0.5rem;
                padding: 1rem;
                border-radius: 8px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0.25rem;
            }
            
            .dashboard-header {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.25rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.1rem;
                padding: 0 0.25rem;
            }

            .profile-layout {
                padding: 0 0.25rem;
            }

            .profile-card {
                padding: 0.75rem;
            }

            .profile-avatar img {
                width: 80px;
                height: 80px;
            }

            .change-photo-btn {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }

            .form-container, .info-container, .activity-stats {
                padding: 0.75rem;
            }

            .section-header {
                padding: 0 0.25rem;
            }

            .section-header h2 {
                font-size: 1rem;
            }

            .form-control {
                padding: 0.875rem;
                height: 48px;
            }

            textarea.form-control {
                min-height: 80px;
                padding: 0.875rem;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }

            .quick-actions-sidebar {
                padding: 0.75rem;
            }

            .quick-actions-sidebar a {
                padding: 0.625rem 0.75rem;
                font-size: 0.85rem;
                min-height: 40px;
            }

            .activity-item {
                padding: 0.75rem;
            }

            .activity-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }

            .alert {
                margin: 0 0.25rem 1rem 0.25rem;
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
            
            .form-actions {
                flex-direction: row;
                gap: 1rem;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .activity-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .profile-card:hover {
                transform: none;
            }
            
            .profile-card:active {
                transform: scale(0.98);
            }
            
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
        }

        /* Mobile responsive for photo modal */
        @media (max-width: 768px) {
            .photo-modal-content {
                margin: 5% auto;
                width: 95%;
                max-height: 85vh;
            }

            .photo-modal-header {
                padding: 1rem 1.5rem;
            }

            .photo-modal-header h3 {
                font-size: 1.1rem;
            }

            .photo-modal-close {
                width: 35px;
                height: 35px;
                font-size: 1.5rem;
            }

            .photo-modal-body {
                padding: 1.5rem;
            }

            .photo-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 0.75rem;
            }

            .photo-option img {
                height: 100px;
            }

            .photo-modal-footer {
                padding: 1rem 1.5rem;
                flex-direction: column;
            }

            .photo-modal-footer .btn {
                width: 100%;
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            .photo-modal-content {
                margin: 10% auto;
                width: 98%;
            }

            .photo-modal-header {
                padding: 0.75rem 1rem;
            }

            .photo-modal-body {
                padding: 1rem;
            }

            .photo-grid {
                grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
                gap: 0.5rem;
            }

            .photo-option img {
                height: 80px;
            }

            .photo-overlay i {
                font-size: 1.5rem;
            }

            .photo-modal-footer {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</body>
</html>
