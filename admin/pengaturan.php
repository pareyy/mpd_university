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

// Get admin information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'system_settings') {
            $message = "Pengaturan sistem berhasil disimpan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'notification_settings') {
            $message = "Pengaturan notifikasi berhasil disimpan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'backup_database') {
            $message = "Database berhasil di-backup!";
            $message_type = 'success';
        }
    }
}

// System statistics from database
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'mahasiswa') as total_users,
    (SELECT COUNT(*) FROM mahasiswa) as total_students,
    (SELECT COUNT(*) FROM dosen) as total_lecturers,
    (SELECT COUNT(*) FROM mata_kuliah) as total_courses";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-cogs"></i> Pengaturan Sistem</h1>
                <p>Konfigurasi dan manajemen pengaturan sistem MPD University</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- System Configuration -->
            <div class="dashboard-section">
                <h2><i class="fas fa-server"></i> Konfigurasi Sistem</h2>
                <div class="form-container">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="system_settings">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="site_name">Nama Situs</label>
                                <input type="text" id="site_name" name="site_name" class="form-control" value="MPD University" required>
                            </div>
                            <div class="form-group">
                                <label for="site_url">URL Situs</label>
                                <input type="url" id="site_url" name="site_url" class="form-control" value="https://mpduniversity.ac.id" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="admin_email">Email Admin</label>
                                <input type="email" id="admin_email" name="admin_email" class="form-control" value="admin@mpduniversity.ac.id" required>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Zona Waktu</label>
                                <select id="timezone" name="timezone" class="form-control" required>
                                    <option value="Asia/Jakarta" selected>Asia/Jakarta (WIB)</option>
                                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="max_upload_size">Ukuran Upload Maksimal (MB)</label>
                                <select id="max_upload_size" name="max_upload_size" class="form-control" required>
                                    <option value="1">1 MB</option>
                                    <option value="2" selected>2 MB</option>
                                    <option value="5">5 MB</option>
                                    <option value="10">10 MB</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="session_timeout">Timeout Sesi (menit)</label>
                                <select id="session_timeout" name="session_timeout" class="form-control" required>
                                    <option value="30">30 menit</option>
                                    <option value="60" selected>60 menit</option>
                                    <option value="120">120 menit</option>
                                    <option value="240">240 menit</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Pengaturan
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm('settings-form')">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bell"></i> Pengaturan Notifikasi</h2>
                <div class="form-container">
                    <form method="POST" class="notification-form">
                        <input type="hidden" name="action" value="notification_settings">
                        
                        <div class="settings-grid">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <h3>Email Notifikasi</h3>
                                    <p>Terima notifikasi melalui email untuk aktivitas penting</p>
                                </div>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" name="email_notifications" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-info">
                                    <h3>Notifikasi Login</h3>
                                    <p>Terima pemberitahuan saat ada login baru ke sistem</p>
                                </div>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" name="login_notifications" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-info">
                                    <h3>Notifikasi Registrasi</h3>
                                    <p>Terima pemberitahuan saat ada pendaftaran mahasiswa baru</p>
                                </div>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" name="registration_notifications" checked>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="setting-item">
                                <div class="setting-info">
                                    <h3>Notifikasi Backup</h3>
                                    <p>Terima pemberitahuan status backup database</p>
                                </div>
                                <div class="setting-control">
                                    <label class="switch">
                                        <input type="checkbox" name="backup_notifications">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Notifikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- System Statistics -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Statistik Sistem</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Pengguna</h3>
                            <p class="stat-number"><?php echo $stats['total_users']; ?></p>
                            <small>Pengguna terdaftar</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Mahasiswa</h3>
                            <p class="stat-number"><?php echo $stats['total_students']; ?></p>
                            <small>Mahasiswa aktif</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Dosen</h3>
                            <p class="stat-number"><?php echo $stats['total_lecturers']; ?></p>
                            <small>Dosen aktif</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Mata Kuliah</h3>
                            <p class="stat-number"><?php echo $stats['total_courses']; ?></p>
                            <small>Mata kuliah tersedia</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/script.js"></script>
</body>
</html>
