<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include required files
require_once '../koneksi.php';
require_once '../config/avatar_config.php';
require_once '../classes/UserManager.php';

// Constants
define('MIN_PASSWORD_LENGTH', 6);

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validatePassword($password) {
    return strlen($password) >= MIN_PASSWORD_LENGTH;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function handleResponse($success, $message) {
    return [
        'success' => $success,
        'message' => $message
    ];
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$csrf_token = generateCSRFToken();

// Initialize user manager
$userManager = new UserManager($conn);

// Get admin information
$user_id = $_SESSION['user_id'];

try {
    $user = $userManager->getUserById($user_id);
    if (!$user) {
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
} catch (Exception $e) {
    error_log("Error getting user: " . $e->getMessage());
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$response = ['success' => true, 'message' => ''];

// Form processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $response = handleResponse(false, "Token keamanan tidak valid!");
    } else {
        $action = $_POST['action'];
    
    try {
        switch ($action) {
            case 'update_profile':
                // Validate and sanitize input
                $profileData = [
                    'username' => sanitizeInput($_POST['username']),
                    'email' => trim($_POST['email']),
                    'full_name' => sanitizeInput($_POST['full_name']),
                    'phone' => sanitizeInput($_POST['phone']),
                    'address' => sanitizeInput($_POST['address'])
                ];
                
                // Validate email
                if (!validateEmail($profileData['email'])) {
                    throw new Exception("Format email tidak valid!");
                }
                
                // Check for duplicate username (excluding current user)
                if ($userManager->usernameExists($profileData['username'], $user_id)) {
                    throw new Exception("Username sudah digunakan!");
                }
                
                // Check for duplicate email (excluding current user)
                if ($userManager->emailExists($profileData['email'], $user_id)) {
                    throw new Exception("Email sudah digunakan!");
                }
                
                if ($userManager->updateProfile($user_id, $profileData)) {
                    $response = handleResponse(true, "Profil berhasil diperbarui!");
                    $user = $userManager->getUserById($user_id); // Refresh user data
                } else {
                    throw new Exception("Tidak ada perubahan yang disimpan!");
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Validation
                if (!validatePassword($new_password)) {
                    throw new Exception("Password minimal " . MIN_PASSWORD_LENGTH . " karakter!");
                }
                
                if ($new_password !== $confirm_password) {
                    throw new Exception("Password baru dan konfirmasi password tidak cocok!");
                }
                
                if (!password_verify($current_password, $user['password'])) {
                    throw new Exception("Password saat ini tidak benar!");
                }
                
                // Check if new password is different from current
                if (password_verify($new_password, $user['password'])) {
                    throw new Exception("Password baru harus berbeda dari password saat ini!");
                }
                
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                if ($userManager->updatePassword($user_id, $hashed_password)) {
                    $response = handleResponse(true, "Password berhasil diubah!");
                } else {
                    throw new Exception("Gagal mengubah password!");
                }
                break;
                
            case 'update_photo':
                $selected_photo = sanitizeInput($_POST['selected_photo']);
                
                if (!AvatarConfig::isValidAvatar($selected_photo)) {
                    throw new Exception("Pilihan foto tidak valid!");
                }
                
                if ($userManager->updatePhoto($user_id, $selected_photo)) {
                    $response = handleResponse(true, "Foto profil berhasil diperbarui!");
                    $user = $userManager->getUserById($user_id); // Refresh user data
                } else {
                    throw new Exception("Gagal memperbarui foto profil!");
                }
                break;
                
            default:
                throw new Exception("Aksi tidak valid!");
        }        } catch (Exception $e) {
            $response = handleResponse(false, $e->getMessage());
        }
    }
}

// Get system statistics
try {
    $stats = $userManager->getSystemStats();
} catch (Exception $e) {
    error_log("Error getting stats: " . $e->getMessage());
    $stats = [
        'total_users' => 0,
        'total_students' => 0,
        'total_lecturers' => 0,
        'total_courses' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-user-circle"></i> Profil Admin</h1>
                <p>Kelola informasi profil dan pengaturan akun Anda</p>
            </div>

            <!-- Alert Messages -->
            <?php if (!empty($response['message'])): ?>
                <div class="alert <?php echo $response['success'] ? 'alert-success' : 'alert-danger'; ?>">
                    <i class="fas <?php echo $response['success'] ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i> 
                    <?php echo htmlspecialchars($response['message']); ?>
                </div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- Profile Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-card">
                        <div class="profile-avatar">
                            <img src="<?php echo AvatarConfig::buildAvatarUrl($user['profile_photo'] ?? AvatarConfig::getDefaultAvatar()); ?>" alt="Profile Picture" id="profileImage">
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
                                <i class="fas fa-id-card"></i> ID: <?php echo htmlspecialchars($user['id']); ?>
                            </p>
                            <p class="profile-email">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <p class="profile-join-date">
                                <i class="fas fa-calendar"></i> Bergabung <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                            </p>
                        </div>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $stats['total_students'] ?: 0; ?></span>
                                <span class="stat-label">Mahasiswa</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $stats['total_lecturers'] ?: 0; ?></span>
                                <span class="stat-label">Dosen</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $stats['total_courses'] ?: 0; ?></span>
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
                            <li><a href="jadwal.php"><i class="fas fa-calendar"></i> Jadwal</a></li>
                            <li><a href="pengaturan.php"><i class="fas fa-cogs"></i> Pengaturan</a></li>
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

                        <div class="form-container form-spacious">
                            <form method="POST" action="" id="profileForm">
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="form-row form-row-enhanced">
                                    <div class="form-group form-group-enhanced">
                                        <label for="username">Username</label>
                                        <input type="text" id="username" name="username" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group form-group-enhanced">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-row form-row-enhanced">
                                    <div class="form-group form-group-enhanced">
                                        <label for="full_name">Nama Lengkap</label>
                                        <input type="text" id="full_name" name="full_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                                    </div>
                                    
                                    <div class="form-group form-group-enhanced">
                                        <label for="phone">Nomor Telepon</label>
                                        <input type="tel" id="phone" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group form-group-enhanced">
                                    <label for="address">Alamat</label>
                                    <textarea id="address" name="address" class="form-control" rows="4" readonly><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-actions form-actions-enhanced" id="profileActions" style="display: none;">
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

                        <div class="form-container form-spacious">
                            <form method="POST" action="" id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                
                                <div class="form-group form-group-enhanced">
                                    <label for="current_password">Password Saat Ini</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-row form-row-enhanced">
                                    <div class="form-group form-group-enhanced">
                                        <label for="new_password">Password Baru</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                                    </div>
                                    
                                    <div class="form-group form-group-enhanced">
                                        <label for="confirm_password">Konfirmasi Password Baru</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                                    </div>
                                </div>
                                
                                <div class="password-requirements">
                                    <h4>Persyaratan Password:</h4>
                                    <ul>
                                        <li>Minimal 6 karakter</li>
                                        <li>Mengandung huruf dan angka</li>
                                        <li>Hindari menggunakan informasi pribadi</li>
                                        <li>Gunakan kombinasi huruf besar dan kecil</li>
                                    </ul>
                                </div>
                                
                                <div class="form-actions form-actions-enhanced">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key"></i> Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Activity Statistics -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2><i class="fas fa-chart-bar"></i> Statistik Aktivitas</h2>
                        </div>

                        <div class="activity-stats">
                            <div class="activity-grid">
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Login Terakhir</h4>
                                        <p><?php echo date('d M Y, H:i'); ?></p>
                                        <small>Hari ini</small>
                                    </div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Total Pengguna</h4>
                                        <p><?php echo $stats['total_users']; ?></p>
                                        <small>Pengguna aktif</small>
                                    </div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Akun Aktif</h4>
                                        <p><?php echo date('j'); ?> Hari</p>
                                        <small>Sejak bergabung</small>
                                    </div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Keamanan</h4>
                                        <p>Aktif</p>
                                        <small>Sistem aman</small>
                                    </div>
                                </div>
                            </div>
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
                                    <label>Terakhir Update</label>
                                    <span><?php echo isset($user['updated_at']) ? date('d/m/Y H:i', strtotime($user['updated_at'])) : 'Belum pernah'; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Status Akun</label>
                                    <span class="status-badge status-admin">Administrator</span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Role</label>
                                    <span class="status-badge status-active"><?php echo ucfirst($user['role']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <label>Tingkat Akses</label>
                                    <span>Full Access</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Photo Selection Modal -->
    <div id="photoModal" class="photo-modal">
        <div class="photo-modal-content">
            <div class="photo-modal-header">
                <h3>Pilih Foto Profil</h3>
                <button class="close-modal" onclick="closePhotoModal()">&times;</button>
            </div>
            <div class="photo-grid">
                <?php
                foreach (AvatarConfig::getAvatarNames() as $filename) {
                    $url = AvatarConfig::buildAvatarUrl($filename);
                    echo "<div class='photo-option' onclick='selectPhoto(\"" . htmlspecialchars($filename) . "\", \"" . htmlspecialchars($url) . "\")'>";
                    echo "<img src='" . htmlspecialchars($url) . "' alt='Avatar Option' loading='lazy'>";
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
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="selected_photo" id="selectedPhotoInput">
    </form>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Application state
        const App = {
            selectedPhoto: null,
            
            // Initialize the application
            init() {
                this.bindEvents();
                this.setupFormValidation();
            },
            
            // Bind all event listeners
            bindEvents() {
                // Modal events
                document.addEventListener('keydown', this.handleKeyDown.bind(this));
                window.addEventListener('click', this.handleWindowClick.bind(this));
                
                // Form validation
                const confirmPassword = document.getElementById('confirm_password');
                if (confirmPassword) {
                    confirmPassword.addEventListener('input', this.validatePasswordMatch);
                }
            },
            
            // Setup form validation
            setupFormValidation() {
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    form.addEventListener('submit', this.validateForm);
                });
            },
            
            // Handle keyboard events
            handleKeyDown(event) {
                if (event.key === 'Escape') {
                    this.closePhotoModal();
                }
            },
            
            // Handle window clicks (for modal closing)
            handleWindowClick(event) {
                const modal = document.getElementById('photoModal');
                if (event.target === modal) {
                    this.closePhotoModal();
                }
            },
            
            // Toggle edit mode for profile
            toggleEdit(section) {
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
            },
            
            // Cancel edit mode
            cancelEdit(section) {
                if (section === 'profile') {
                    location.reload();
                }
            },
            
            // Photo modal methods
            openPhotoModal() {
                const modal = document.getElementById('photoModal');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                modal.setAttribute('aria-hidden', 'false');
            },
            
            closePhotoModal() {
                const modal = document.getElementById('photoModal');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                modal.setAttribute('aria-hidden', 'true');
                
                // Reset selection
                this.resetPhotoSelection();
            },
            
            resetPhotoSelection() {
                document.querySelectorAll('.photo-option').forEach(option => {
                    option.classList.remove('selected');
                });
                this.selectedPhoto = null;
                document.getElementById('savePhotoBtn').disabled = true;
            },
            
            // Photo selection
            selectPhoto(filename, url) {
                // Remove previous selection
                document.querySelectorAll('.photo-option').forEach(option => {
                    option.classList.remove('selected');
                });
                
                // Add selection to clicked photo
                event.currentTarget.classList.add('selected');
                
                this.selectedPhoto = filename;
                document.getElementById('savePhotoBtn').disabled = false;
            },
            
            // Save selected photo
            saveSelectedPhoto() {
                if (!this.selectedPhoto) {
                    alert('Silakan pilih foto terlebih dahulu');
                    return;
                }
                
                document.getElementById('selectedPhotoInput').value = this.selectedPhoto;
                document.getElementById('photoUpdateForm').submit();
            },
            
            // Form validation
            validateForm(event) {
                const form = event.target;
                const action = form.querySelector('[name="action"]')?.value;
                
                if (action === 'change_password') {
                    const newPassword = form.querySelector('[name="new_password"]').value;
                    const confirmPassword = form.querySelector('[name="confirm_password"]').value;
                    
                    if (newPassword.length < <?php echo MIN_PASSWORD_LENGTH; ?>) {
                        event.preventDefault();
                        alert('Password minimal <?php echo MIN_PASSWORD_LENGTH; ?> karakter');
                        return false;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        event.preventDefault();
                        alert('Password baru dan konfirmasi password tidak cocok');
                        return false;
                    }
                }
                
                return true;
            },
            
            // Password confirmation validation
            validatePasswordMatch() {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = this.value;
                
                if (newPassword !== confirmPassword) {
                    this.setCustomValidity('Password tidak cocok');
                } else {
                    this.setCustomValidity('');
                }
            }
        };
        
        // Global functions for backward compatibility
        function toggleEdit(section) { App.toggleEdit(section); }
        function cancelEdit(section) { App.cancelEdit(section); }
        function openPhotoModal() { App.openPhotoModal(); }
        function closePhotoModal() { App.closePhotoModal(); }
        function selectPhoto(filename, url) { App.selectPhoto(filename, url); }
        function saveSelectedPhoto() { App.saveSelectedPhoto(); }
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            App.init();
        });
    </script>
</body>
</html>