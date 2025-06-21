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
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <img src="../assets/img/blank.jpg" alt="Profile Picture" id="profileImage">
                            <button class="change-photo-btn" onclick="changePhoto()">
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
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
        }

        .form-control.editable {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .form-control[readonly] {
            background: #f9fafb;
            color: #6b7280;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .password-requirements {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .password-requirements h4 {
            margin: 0 0 0.5rem 0;
            color: #0369a1;
            font-size: 0.875rem;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.5rem;
            color: #0369a1;
            font-size: 0.875rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-item label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .info-item span {
            color: #6b7280;
            font-size: 1rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            width: fit-content;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .alert {
            padding: 1rem;
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

        @media (max-width: 1024px) {
            .profile-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .profile-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .profile-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
