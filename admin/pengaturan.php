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

// System statistics
$stats = [
    'total_users' => 50,
    'total_students' => 150,
    'total_lecturers' => 25,
    'total_courses' => 35
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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

            <!-- Database Management -->
            <div class="dashboard-section">
                <h2><i class="fas fa-database"></i> Manajemen Database</h2>
                <div class="management-grid">
                    <div class="management-card">
                        <div class="management-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <div class="management-content">
                            <h3>Backup Database</h3>
                            <p>Buat backup database untuk keamanan data</p>
                            <form method="POST" style="margin-top: 1rem;">
                                <input type="hidden" name="action" value="backup_database">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-download"></i> Backup Sekarang
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="management-card">
                        <div class="management-icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="management-content">
                            <h3>Restore Database</h3>
                            <p>Pulihkan database dari file backup</p>
                            <form enctype="multipart/form-data" style="margin-top: 1rem;">
                                <input type="file" accept=".sql" class="form-control" style="margin-bottom: 0.5rem;">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-upload"></i> Restore Database
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="management-card">
                        <div class="management-icon">
                            <i class="fas fa-broom"></i>
                        </div>
                        <div class="management-content">
                            <h3>Bersihkan Cache</h3>
                            <p>Hapus file cache untuk meningkatkan performa</p>
                            <button type="button" class="btn btn-info" onclick="clearCache()" style="margin-top: 1rem;">
                                <i class="fas fa-broom"></i> Bersihkan Cache
                            </button>
                        </div>
                    </div>

                    <div class="management-card">
                        <div class="management-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="management-content">
                            <h3>Optimasi Database</h3>
                            <p>Optimasi tabel database untuk performa terbaik</p>
                            <button type="button" class="btn btn-primary" onclick="optimizeDatabase()" style="margin-top: 1rem;">
                                <i class="fas fa-tools"></i> Optimasi Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function resetForm(formClass) {
            document.querySelector('.' + formClass).reset();
        }

        function clearCache() {
            if (confirm('Apakah Anda yakin ingin membersihkan cache?')) {
                alert('Cache berhasil dibersihkan!');
            }
        }

        function optimizeDatabase() {
            if (confirm('Apakah Anda yakin ingin mengoptimasi database?')) {
                alert('Database berhasil dioptimasi!');
            }
        }
    </script>

    <style>
        /* Settings page specific styles */
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
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

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.9rem;
            text-align: left;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
            height: 48px;
            line-height: 1.2;
        }

        /* Ensure all form controls have exact same dimensions */
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

        select.form-control {
            padding-right: 2.5rem !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            background-color: white;
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
        }

        .settings-grid {
            display: grid;
            gap: 1rem;
        }

        .setting-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fafbfc;
        }

        .setting-info h3 {
            font-size: 1rem;
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-weight: 600;
        }

        .setting-info p {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.4;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #667eea;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
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

        .stat-info {
            flex: 1;
        }

        .stat-info h3 {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #374151;
            margin: 0 0 0.25rem 0;
        }

        .stat-info small {
            color: #9ca3af;
            font-size: 0.8rem;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .management-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .management-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .management-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }

        .management-content {
            flex: 1;
        }

        .management-content h3 {
            font-size: 1.1rem;
            margin: 0 0 0.5rem 0;
            color: #374151;
            font-weight: 600;
        }

        .management-content p {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0 0 1rem 0;
            line-height: 1.4;
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

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .management-grid {
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

            .form-container {
                padding: 1rem;
                margin: 0 0.5rem 1.5rem 0.5rem;
                border-radius: 8px;
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
            
            .setting-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }
            
            .setting-control {
                align-self: flex-end;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 0 0.5rem;
            }
            
            .management-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 0 0.5rem;
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

            .form-container {
                padding: 0.75rem;
                margin: 0 0.25rem 1rem 0.25rem;
            }

            .form-control {
                padding: 0.875rem;
                height: 48px;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }
            
            .management-card {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .stats-grid,
            .management-grid {
                padding: 0 0.25rem;
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
            
            .management-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover,
            .management-card:hover {
                transform: none;
            }
            
            .stat-card:active,
            .management-card:active {
                transform: scale(0.98);
            }
            
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
        }
    </style>
</body>
</html>
