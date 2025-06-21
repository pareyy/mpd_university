<?php
// Start session
session_start();

// Check if user is logged in and is a dosen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
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

// Get dosen information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'update_profile') {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
            $nip = mysqli_real_escape_string($conn, $_POST['nip']);
            $phone = mysqli_real_escape_string($conn, $_POST['phone']);
            $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
            
            $update_query = "UPDATE users SET 
                           username = '$username',
                           email = '$email',
                           full_name = '$full_name',
                           nip = '$nip',
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
          if ($_POST['action'] == 'change_password') {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = '$user_id'";
                    
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
        
        if ($_POST['action'] == 'update_photo') {
            $selected_photo = mysqli_real_escape_string($conn, $_POST['selected_photo']);
            
            $update_query = "UPDATE users SET profile_photo = '$selected_photo' WHERE id = '$user_id'";
            
            if (mysqli_query($conn, $update_query)) {
                $success_message = "Foto profil berhasil diperbarui!";
                // Refresh user data
                $result = mysqli_query($conn, $query);
                $user = mysqli_fetch_assoc($result);
            } else {
                $error_message = "Error: " . mysqli_error($conn);
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
    <title>Profil Dosen - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_dosen.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-user"></i> Profil Dosen</h1>
                <p>Kelola informasi profil dan pengaturan akun Anda</p>
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
                <div class="profile-sidebar">
                    <div class="profile-card">                        <div class="profile-avatar">
                            <img src="<?php echo getAvatarUrl($user['profile_photo'] ?? 'avatar-1.svg'); ?>" alt="Profile Picture" id="profileImage">
                            <button class="change-photo-btn" onclick="openPhotoModal()">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h3>
                            <p class="profile-role">
                                <i class="fas fa-chalkboard-teacher"></i> Dosen
                            </p>
                            <p class="profile-nip">
                                <i class="fas fa-id-card"></i> NIP: <?php echo htmlspecialchars($user['nip'] ?? '-'); ?>
                            </p>
                        </div>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number">5</span>
                                <span class="stat-label">Mata Kuliah</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">125</span>
                                <span class="stat-label">Mahasiswa</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">3</span>
                                <span class="stat-label">Tahun Mengajar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions-sidebar">
                        <h4><i class="fas fa-bolt"></i> Aksi Cepat</h4>
                        <ul>
                            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="mata_kuliah.php"><i class="fas fa-book"></i> Mata Kuliah</a></li>
                            <li><a href="jadwal.php"><i class="fas fa-calendar"></i> Jadwal</a></li>
                            <li><a href="nilai.php"><i class="fas fa-star"></i> Nilai</a></li>
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
                                        <label for="nip">NIP</label>
                                        <input type="text" id="nip" name="nip" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['nip'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Nomor Telepon</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <input type="text" class="form-control" 
                                               value="Dosen" readonly disabled>
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
                                        <li>Mengandung karakter khusus</li>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function toggleEdit(section) {
            if (section === 'profile') {
                const form = document.getElementById('profileForm');
                const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea');
                const actions = document.getElementById('profileActions');
                
                inputs.forEach(input => {
                    if (input.name !== 'role') {
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

        function changePhoto() {
            // Implement photo change functionality
            alert('Fitur ubah foto akan segera tersedia!');
        }

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
    </script>

    <style>
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

        .profile-role, .profile-nip {
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

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

            .form-container {
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

            .quick-links {
                padding: 1rem;
            }

            .quick-links ul {
                gap: 0.5rem;
            }

            .quick-links a {
                padding: 0.75rem 1rem;
                border-radius: 8px;
                font-size: 0.9rem;
                min-height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .teaching-load {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .stat-item {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
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

            .form-container {
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

            .quick-links {
                padding: 0.75rem;
            }

            .quick-links a {
                padding: 0.625rem 0.75rem;
                font-size: 0.85rem;
                min-height: 40px;
            }

            .teaching-load {
                padding: 0.75rem;
            }

            .stat-item {
                padding: 0.75rem;
            }

            .stat-value {
                font-size: 1.25rem;
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

            .stats-grid {
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
            }        }
    </style>

    <!-- Photo Selection Modal -->
    <div id="photoModal" class="photo-modal">
        <div class="photo-modal-content">
            <div class="photo-modal-header">
                <h3>Pilih Foto Profil</h3>
                <button class="close-modal" onclick="closePhotoModal()">&times;</button>
            </div>            <div class="photo-grid">                <?php                $vector_avatars = [
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
            }
        }
    </style>

    <script>
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
    </script>
</body>
</html>
