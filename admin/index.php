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

// Sample statistics data
$total_mahasiswa = 150;
$total_dosen = 25;
$total_mata_kuliah = 35;
$total_kelas = 12;

// Recent activities
$recent_activities = [
    ['action' => 'Mahasiswa baru terdaftar', 'user' => 'Ahmad Rizki (2024001)', 'time' => '5 menit yang lalu'],
    ['action' => 'Dosen mengupdate jadwal', 'user' => 'Dr. Siti Nurhaliza', 'time' => '15 menit yang lalu'],
    ['action' => 'Nilai mata kuliah diperbarui', 'user' => 'Prof. Budi Santoso', 'time' => '30 menit yang lalu'],
    ['action' => 'Mahasiswa mengumpulkan tugas', 'user' => 'Maya Putri (2023025)', 'time' => '1 jam yang lalu']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
                <p>Selamat datang, <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            </div>

            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Mahasiswa</h3>
                        <p class="stat-number"><?php echo $total_mahasiswa; ?></p>
                        <small>Mahasiswa aktif</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Dosen</h3>
                        <p class="stat-number"><?php echo $total_dosen; ?></p>
                        <small>Dosen aktif</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Mata Kuliah</h3>
                        <p class="stat-number"><?php echo $total_mata_kuliah; ?></p>
                        <small>Total mata kuliah</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Kelas</h3>
                        <p class="stat-number"><?php echo $total_kelas; ?></p>
                        <small>Kelas aktif</small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bolt"></i> Aksi Cepat</h2>
                <div class="quick-actions">
                    <a href="mahasiswa.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="action-content">
                            <h3>Kelola Mahasiswa</h3>
                            <p>Tambah, edit, atau hapus data mahasiswa</p>
                        </div>
                    </a>

                    <a href="dosen.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="action-content">
                            <h3>Kelola Dosen</h3>
                            <p>Manajemen data dosen dan pengajar</p>
                        </div>
                    </a>

                    <a href="mata_kuliah.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="action-content">
                            <h3>Mata Kuliah</h3>
                            <p>Kelola mata kuliah dan kurikulum</p>
                        </div>
                    </a>

                    <a href="jadwal.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="action-content">
                            <h3>Jadwal Kuliah</h3>
                            <p>Atur jadwal perkuliahan</p>
                        </div>
                    </a>

                    <a href="laporan.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-content">
                            <h3>Laporan</h3>
                            <p>Lihat statistik dan laporan sistem</p>
                        </div>
                    </a>

                    <a href="pengaturan.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="action-content">
                            <h3>Pengaturan</h3>
                            <p>Konfigurasi sistem dan preferensi</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="dashboard-section">
                <h2><i class="fas fa-clock"></i> Aktivitas Terbaru</h2>
                <div class="activity-list">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="activity-content">
                                <h4><?php echo htmlspecialchars($activity['action']); ?></h4>
                                <p><strong><?php echo htmlspecialchars($activity['user']); ?></strong></p>
                                <small><?php echo htmlspecialchars($activity['time']); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- System Overview -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Ringkasan Sistem</h2>
                <div class="overview-grid">
                    <div class="overview-card">
                        <h3>Kehadiran Hari Ini</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 87%"></div>
                        </div>
                        <p>87% mahasiswa hadir</p>
                    </div>

                    <div class="overview-card">
                        <h3>Tugas Terkumpul</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 92%"></div>
                        </div>
                        <p>92% tugas terkumpul</p>
                    </div>

                    <div class="overview-card">
                        <h3>Nilai Terinput</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 78%"></div>
                        </div>
                        <p>78% nilai sudah diinput</p>
                    </div>

                    <div class="overview-card">
                        <h3>Jadwal Aktif</h3>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 95%"></div>
                        </div>
                        <p>95% jadwal berjalan</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>    <style>
        /* Mobile Responsive Styles for Admin Dashboard */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8fafc;
            min-height: calc(100vh - 200px);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .dashboard-header p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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
            font-size: 2rem;
            font-weight: 700;
            color: #374151;
            margin: 0 0 0.25rem 0;
        }

        .stat-info small {
            color: #9ca3af;
            font-size: 0.8rem;
        }

        .dashboard-section {
            margin-bottom: 2rem;
        }

        .dashboard-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #374151;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }

        .action-icon {
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

        .action-content {
            flex: 1;
        }

        .action-content h3 {
            font-size: 1.1rem;
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-weight: 600;
        }

        .action-content p {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.4;
        }

        .activity-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-content h4 {
            font-size: 0.95rem;
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-weight: 600;
        }

        .activity-content p {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0 0 0.25rem 0;
        }

        .activity-content small {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .overview-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .overview-card h3 {
            font-size: 1rem;
            margin: 0 0 1rem 0;
            color: #374151;
            font-weight: 600;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #f3f4f6;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .overview-card p {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
        }/* Mobile Responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
                margin: 0;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .overview-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .action-card {
                padding: 1.25rem;
            }
        }        @media (max-width: 768px) {
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
            
            .dashboard-header p {
                font-size: 0.9rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .stat-card {
                padding: 1rem;
                flex-direction: row;
                text-align: left;
                gap: 0.75rem;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
                flex-shrink: 0;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
            
            .stat-info h3 {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }
            
            .stat-info small {
                font-size: 0.75rem;
            }
            
            .dashboard-section {
                margin-bottom: 1.5rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.25rem;
                padding: 0 0.5rem;
                margin-bottom: 1rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 0 0.5rem;
            }
            
            .action-card {
                padding: 1rem;
                flex-direction: row;
                text-align: left;
                gap: 0.75rem;
            }
            
            .action-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
                flex-shrink: 0;
            }
            
            .action-content h3 {
                font-size: 1rem;
                margin-bottom: 0.25rem;
            }
            
            .action-content p {
                font-size: 0.8rem;
                line-height: 1.3;
            }
            
            .activity-list {
                margin: 0 0.5rem;
                border-radius: 8px;
            }
            
            .activity-item {
                padding: 0.75rem 1rem;
                flex-direction: row;
                text-align: left;
                gap: 0.75rem;
            }
            
            .activity-icon {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
                flex-shrink: 0;
            }
            
            .activity-content h4 {
                font-size: 0.9rem;
                margin-bottom: 0.25rem;
            }
            
            .activity-content p {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
            }
            
            .activity-content small {
                font-size: 0.7rem;
            }
            
            .overview-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 0 0.5rem;
            }
            
            .overview-card {
                padding: 1rem;
            }
            
            .overview-card h3 {
                font-size: 0.9rem;
                margin-bottom: 0.75rem;
            }
            
            .overview-card p {
                font-size: 0.8rem;
            }
            
            .progress-bar {
                height: 6px;
                margin-bottom: 0.5rem;
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
            
            .stat-card {
                padding: 0.75rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.1rem;
                padding: 0 0.25rem;
            }
            
            .action-card {
                padding: 0.75rem;
            }
            
            .action-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .activity-item {
                padding: 0.625rem 0.75rem;
            }
            
            .overview-card {
                padding: 0.75rem;
            }
        }

        /* Landscape mobile optimization */
        @media (max-width: 768px) and (orientation: landscape) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .overview-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover,
            .action-card:hover {
                transform: none;
            }
            
            .stat-card:active,
            .action-card:active {
                transform: scale(0.98);
            }
        }
    </style>
</body>
</html>
