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

// Sample jadwal data (in real application, this would come from database)
$jadwal = [
    [
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:30',
        'mata_kuliah' => 'Pemrograman Web Lanjut',
        'kelas' => 'A',
        'ruang' => 'Lab 1',
        'jumlah_mahasiswa' => 30
    ],
    [
        'hari' => 'Senin',
        'jam_mulai' => '10:30',
        'jam_selesai' => '12:00',
        'mata_kuliah' => 'Database',
        'kelas' => 'B',
        'ruang' => 'Ruang 201',
        'jumlah_mahasiswa' => 25
    ],
    [
        'hari' => 'Selasa',
        'jam_mulai' => '13:00',
        'jam_selesai' => '15:30',
        'mata_kuliah' => 'Algoritma dan Struktur Data',
        'kelas' => 'C',
        'ruang' => 'Ruang 105',
        'jumlah_mahasiswa' => 35
    ],
    [
        'hari' => 'Rabu',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:30',
        'mata_kuliah' => 'Sistem Basis Data',
        'kelas' => 'A',
        'ruang' => 'Lab 2',
        'jumlah_mahasiswa' => 28
    ],
    [
        'hari' => 'Kamis',
        'jam_mulai' => '10:30',
        'jam_selesai' => '12:00',
        'mata_kuliah' => 'Pemrograman Mobile',
        'kelas' => 'B',
        'ruang' => 'Lab 3',
        'jumlah_mahasiswa' => 22
    ]
];

// Group jadwal by day
$jadwal_by_day = [];
foreach ($jadwal as $j) {
    $jadwal_by_day[$j['hari']][] = $j;
}

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar - MPD University</title>
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
                <h1><i class="fas fa-calendar"></i> Jadwal Mengajar</h1>
                <p>Jadwal mengajar minggu ini</p>
            </div>

            <!-- Weekly Schedule Overview -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-week"></i> Jadwal Mingguan</h2>
                <div class="schedule-grid">
                    <?php foreach ($hari_list as $hari): ?>
                        <div class="day-card">
                            <div class="day-header">
                                <h3><?php echo $hari; ?></h3>
                                <span class="day-count">
                                    <?php echo isset($jadwal_by_day[$hari]) ? count($jadwal_by_day[$hari]) : 0; ?> kelas
                                </span>
                            </div>
                            <div class="day-schedule">
                                <?php if (isset($jadwal_by_day[$hari])): ?>
                                    <?php foreach ($jadwal_by_day[$hari] as $j): ?>
                                        <div class="schedule-card">
                                            <div class="schedule-time">
                                                <?php echo $j['jam_mulai']; ?> - <?php echo $j['jam_selesai']; ?>
                                            </div>
                                            <div class="schedule-subject">
                                                <?php echo $j['mata_kuliah']; ?>
                                            </div>
                                            <div class="schedule-details">
                                                <span><i class="fas fa-users"></i> Kelas <?php echo $j['kelas']; ?></span>
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $j['ruang']; ?></span>
                                                <span><i class="fas fa-user-graduate"></i> <?php echo $j['jumlah_mahasiswa']; ?> mhs</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-schedule">
                                        <i class="fas fa-calendar-times"></i>
                                        <p>Tidak ada jadwal</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-day"></i> Jadwal Hari Ini</h2>
                <div class="today-schedule">
                    <?php 
                    $today = date('l');
                    $hari_indonesia = [
                        'Monday' => 'Senin',
                        'Tuesday' => 'Selasa',
                        'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis',
                        'Friday' => 'Jumat',
                        'Saturday' => 'Sabtu',
                        'Sunday' => 'Minggu'
                    ];
                    $today_indo = $hari_indonesia[$today];
                    ?>
                    
                    <?php if (isset($jadwal_by_day[$today_indo])): ?>
                        <?php foreach ($jadwal_by_day[$today_indo] as $j): ?>
                            <div class="today-schedule-item">
                                <div class="time-badge">
                                    <span class="time"><?php echo $j['jam_mulai']; ?></span>
                                    <span class="duration">
                                        <?php 
                                        $start = strtotime($j['jam_mulai']);
                                        $end = strtotime($j['jam_selesai']);
                                        $duration = ($end - $start) / 3600;
                                        echo $duration . ' jam';
                                        ?>
                                    </span>
                                </div>
                                <div class="schedule-info">
                                    <h4><?php echo $j['mata_kuliah']; ?></h4>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $j['ruang']; ?> | 
                                       <i class="fas fa-users"></i> Kelas <?php echo $j['kelas']; ?> | 
                                       <i class="fas fa-user-graduate"></i> <?php echo $j['jumlah_mahasiswa']; ?> mahasiswa</p>
                                </div>
                                <div class="schedule-actions">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-check"></i> Absensi
                                    </button>
                                    <button class="btn btn-secondary btn-sm">
                                        <i class="fas fa-file-alt"></i> Materi
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-schedule-today">
                            <i class="fas fa-calendar-check"></i>
                            <h3>Tidak ada jadwal hari ini</h3>
                            <p>Anda bebas dari mengajar hari ini. Selamat beristirahat!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Schedule Statistics -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-bar"></i> Statistik Jadwal</h2>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-text">
                            <h4>Total Kelas/Minggu</h4>
                            <p><?php echo count($jadwal); ?> kelas</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-text">
                            <h4>Total Jam Mengajar</h4>
                            <p>
                                <?php 
                                $total_hours = 0;
                                foreach ($jadwal as $j) {
                                    $start = strtotime($j['jam_mulai']);
                                    $end = strtotime($j['jam_selesai']);
                                    $total_hours += ($end - $start) / 3600;
                                }
                                echo $total_hours; 
                                ?> jam/minggu
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-text">
                            <h4>Total Mahasiswa</h4>
                            <p>
                                <?php 
                                $total_students = 0;
                                foreach ($jadwal as $j) {
                                    $total_students += $j['jumlah_mahasiswa'];
                                }
                                echo $total_students; 
                                ?> mahasiswa
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <div class="stat-text">
                            <h4>Ruang yang Digunakan</h4>
                            <p>
                                <?php 
                                $rooms = array_unique(array_column($jadwal, 'ruang'));
                                echo count($rooms); 
                                ?> ruang
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <style>
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .day-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .day-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 1rem;
            text-align: center;
        }

        .day-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.2rem;
        }

        .day-count {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .day-schedule {
            padding: 1rem;
            min-height: 200px;
        }

        .schedule-card {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #2563eb;
        }

        .schedule-card:last-child {
            margin-bottom: 0;
        }

        .schedule-time {
            font-weight: 600;
            color: #2563eb;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .schedule-subject {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .schedule-details {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .schedule-details span {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .schedule-details i {
            width: 12px;
            margin-right: 0.25rem;
        }

        .no-schedule {
            text-align: center;
            color: #9ca3af;
            padding: 2rem 0;
        }

        .no-schedule i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .today-schedule {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .today-schedule-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .today-schedule-item:last-child {
            border-bottom: none;
        }

        .time-badge {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            min-width: 100px;
        }

        .time-badge .time {
            display: block;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .time-badge .duration {
            display: block;
            font-size: 0.75rem;
            opacity: 0.9;
        }

        .schedule-info {
            flex: 1;
        }

        .schedule-info h4 {
            margin: 0 0 0.5rem 0;
            color: #374151;
        }

        .schedule-info p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .schedule-actions {
            display: flex;
            gap: 0.5rem;
        }

        .no-schedule-today {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .no-schedule-today i {
            font-size: 3rem;
            color: #10b981;
            margin-bottom: 1rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .stat-item {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-item .stat-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.5rem;
        }

        .stat-item:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .stat-item:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .stat-item:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .stat-text h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-size: 1rem;
        }

        .stat-text p {
            margin: 0;
            color: #2563eb;
            font-weight: 600;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        @media (max-width: 768px) {
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .today-schedule-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .schedule-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
