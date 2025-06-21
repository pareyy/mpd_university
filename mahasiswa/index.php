<?php
// Start session
session_start();

// Check if user is logged in and is a mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Get mahasiswa information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Sample data for mahasiswa dashboard
$mata_kuliah_diambil = [
    ['nama' => 'Pemrograman Web Lanjut', 'sks' => 3, 'dosen' => 'Dr. Ahmad Rahman', 'nilai' => 'A'],
    ['nama' => 'Database', 'sks' => 3, 'dosen' => 'Prof. Siti Nurhaliza', 'nilai' => 'B+'],
    ['nama' => 'Algoritma dan Struktur Data', 'sks' => 3, 'dosen' => 'Dr. Budi Santoso', 'nilai' => 'A-'],
    ['nama' => 'Sistem Basis Data', 'sks' => 3, 'dosen' => 'Dr. Maya Putri', 'nilai' => 'B'],
    ['nama' => 'Pemrograman Mobile', 'sks' => 3, 'dosen' => 'Dr. Rendi Pratama', 'nilai' => 'A']
];

$jadwal_hari_ini = [
    ['mata_kuliah' => 'Pemrograman Web Lanjut', 'waktu' => '08:00 - 10:30', 'ruang' => 'Lab 1'],
    ['mata_kuliah' => 'Database', 'waktu' => '10:30 - 12:00', 'ruang' => 'Ruang 201']
];

$tugas_pending = [
    ['mata_kuliah' => 'Pemrograman Web Lanjut', 'judul' => 'Project Akhir Website E-Commerce', 'deadline' => '2025-06-25'],
    ['mata_kuliah' => 'Database', 'judul' => 'ERD dan Normalisasi Database', 'deadline' => '2025-06-28']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-graduation-cap"></i> Dashboard Mahasiswa</h1>
                <p>Selamat datang, <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
            </div>

            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Mata Kuliah</h3>
                        <p class="stat-number"><?php echo count($mata_kuliah_diambil); ?></p>
                        <small>Mata kuliah yang diambil semester ini</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3>IPK</h3>
                        <p class="stat-number">3.75</p>
                        <small>Indeks Prestasi Kumulatif</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Kelas Hari Ini</h3>
                        <p class="stat-number"><?php echo count($jadwal_hari_ini); ?></p>
                        <small>Jadwal kuliah hari ini</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tugas Pending</h3>
                        <p class="stat-number"><?php echo count($tugas_pending); ?></p>
                        <small>Tugas yang belum diselesaikan</small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bolt"></i> Aksi Cepat</h2>
                <div class="quick-actions">
                    <a href="mata_kuliah.php" class="action-card">
                        <i class="fas fa-book"></i>
                        <h3>Mata Kuliah</h3>
                        <p>Lihat mata kuliah dan nilai</p>
                    </a>

                    <a href="jadwal.php" class="action-card">
                        <i class="fas fa-calendar"></i>
                        <h3>Jadwal Kuliah</h3>
                        <p>Cek jadwal kuliah mingguan</p>
                    </a>

                    <a href="nilai.php" class="action-card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Transkrip Nilai</h3>
                        <p>Lihat transkrip dan IPK</p>
                    </a>

                    <a href="absensi.php" class="action-card">
                        <i class="fas fa-user-check"></i>
                        <h3>Absensi</h3>
                        <p>Rekap kehadiran kuliah</p>
                    </a>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-day"></i> Jadwal Hari Ini</h2>
                <div class="today-schedule">
                    <?php if (!empty($jadwal_hari_ini)): ?>
                        <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    <span class="time"><?php echo explode(' - ', $jadwal['waktu'])[0]; ?></span>
                                    <span class="duration"><?php echo $jadwal['waktu']; ?></span>
                                </div>
                                <div class="schedule-content">
                                    <h4><?php echo $jadwal['mata_kuliah']; ?></h4>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $jadwal['ruang']; ?></p>
                                </div>
                                <div class="schedule-status">
                                    <span class="status-badge status-upcoming">Akan Datang</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-schedule">
                            <i class="fas fa-calendar-times"></i>
                            <h3>Tidak ada jadwal hari ini</h3>
                            <p>Anda bebas dari kuliah hari ini. Gunakan waktu untuk belajar mandiri!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Assignments -->
            <div class="dashboard-section">
                <h2><i class="fas fa-tasks"></i> Tugas Pending</h2>
                <div class="assignments-list">
                    <?php foreach ($tugas_pending as $tugas): ?>
                        <div class="assignment-item">
                            <div class="assignment-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="assignment-content">
                                <h4><?php echo $tugas['judul']; ?></h4>
                                <p class="assignment-course"><?php echo $tugas['mata_kuliah']; ?></p>
                                <p class="assignment-deadline">
                                    <i class="fas fa-clock"></i> 
                                    Deadline: <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?>
                                </p>
                            </div>
                            <div class="assignment-actions">
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Submit
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Academic Progress -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-line"></i> Progress Akademik</h2>
                <div class="progress-container">
                    <div class="semester-progress">
                        <h3>Semester 5 - Progress</h3>
                        <div class="progress-stats">
                            <div class="progress-item">
                                <span class="progress-label">SKS Diambil</span>
                                <span class="progress-value">15 SKS</span>
                            </div>
                            <div class="progress-item">
                                <span class="progress-label">SKS Selesai</span>
                                <span class="progress-value">12 SKS</span>
                            </div>
                            <div class="progress-item">
                                <span class="progress-label">Progress</span>
                                <span class="progress-value">80%</span>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 80%"></div>
                        </div>
                    </div>

                    <div class="gpa-info">
                        <h3>Informasi IPK</h3>
                        <div class="gpa-details">
                            <div class="gpa-item">
                                <span class="gpa-label">IPK Semester</span>
                                <span class="gpa-value">3.85</span>
                            </div>
                            <div class="gpa-item">
                                <span class="gpa-label">IPK Kumulatif</span>
                                <span class="gpa-value">3.75</span>
                            </div>
                            <div class="gpa-item">
                                <span class="gpa-label">Total SKS</span>
                                <span class="gpa-value">95 SKS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Grades -->
            <div class="dashboard-section">
                <h2><i class="fas fa-star"></i> Nilai Terbaru</h2>
                <div class="grades-summary">
                    <?php foreach (array_slice($mata_kuliah_diambil, 0, 3) as $mk): ?>
                        <div class="grade-item">
                            <div class="grade-course">
                                <h4><?php echo $mk['nama']; ?></h4>
                                <p><?php echo $mk['sks']; ?> SKS - <?php echo $mk['dosen']; ?></p>
                            </div>
                            <div class="grade-value">
                                <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $mk['nilai']))); ?>">
                                    <?php echo $mk['nilai']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="view-all-grades">
                        <a href="nilai.php" class="btn btn-outline">
                            <i class="fas fa-chart-line"></i> Lihat Semua Nilai
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: #f8fafc;
            min-height: calc(100vh - 200px);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .today-schedule {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .schedule-item:last-child {
            border-bottom: none;
        }

        .schedule-time {
            text-align: center;
            min-width: 100px;
        }

        .schedule-time .time {
            display: block;
            font-size: 1.25rem;
            font-weight: 700;
            color: #10b981;
        }

        .schedule-time .duration {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .schedule-content {
            flex: 1;
        }

        .schedule-content h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
        }

        .schedule-content p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .schedule-status {
            min-width: 120px;
            text-align: right;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .no-schedule {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .no-schedule i {
            font-size: 3rem;
            color: #10b981;
            margin-bottom: 1rem;
        }

        .assignments-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .assignment-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .assignment-item:last-child {
            border-bottom: none;
        }

        .assignment-icon {
            background: #fef3c7;
            color: #d97706;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 1.25rem;
        }

        .assignment-content {
            flex: 1;
        }

        .assignment-content h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
        }

        .assignment-course {
            margin: 0 0 0.25rem 0;
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .assignment-deadline {
            margin: 0;
            color: #ef4444;
            font-size: 0.875rem;
        }

        .assignment-actions {
            min-width: 100px;
        }

        .progress-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .semester-progress, .gpa-info {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .progress-stats {
            display: flex;
            justify-content: space-between;
            margin: 1rem 0;
        }

        .progress-item {
            text-align: center;
        }

        .progress-label {
            display: block;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .progress-value {
            display: block;
            font-weight: 700;
            color: #374151;
            font-size: 1.25rem;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #f3f4f6;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .gpa-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .gpa-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .gpa-item:last-child {
            border-bottom: none;
        }

        .gpa-label {
            color: #6b7280;
        }

        .gpa-value {
            font-weight: 700;
            color: #10b981;
            font-size: 1.1rem;
        }

        .grades-summary {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .grade-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .grade-item:last-child {
            border-bottom: none;
        }

        .grade-course h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
        }

        .grade-course p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .grade-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1rem;
        }

        .grade-a { background: #d1fae5; color: #065f46; }
        .grade-aplus { background: #dcfce7; color: #14532d; }
        .grade-aminus { background: #bbf7d0; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-bplus { background: #bfdbfe; color: #1e3a8a; }
        .grade-bminus { background: #93c5fd; color: #1e40af; }

        .view-all-grades {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #10b981;
            color: #10b981;
        }

        .btn-outline:hover {
            background: #10b981;
            color: white;
        }

        @media (max-width: 768px) {
            .progress-container {
                grid-template-columns: 1fr;
            }
            
            .schedule-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .assignment-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .grade-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
</body>
</html>