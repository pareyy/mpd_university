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

// Sample jadwal data
$jadwal_mingguan = [
    'Senin' => [
        [
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:30',
            'mata_kuliah' => 'Pemrograman Web Lanjut',
            'dosen' => 'Dr. Ahmad Rahman',
            'ruang' => 'Lab 1',
            'kelas' => 'A'
        ],
        [
            'jam_mulai' => '10:30',
            'jam_selesai' => '12:00',
            'mata_kuliah' => 'Database',
            'dosen' => 'Prof. Siti Nurhaliza',
            'ruang' => 'Ruang 201',
            'kelas' => 'B'
        ]
    ],
    'Selasa' => [
        [
            'jam_mulai' => '13:00',
            'jam_selesai' => '15:30',
            'mata_kuliah' => 'Algoritma dan Struktur Data',
            'dosen' => 'Dr. Budi Santoso',
            'ruang' => 'Ruang 105',
            'kelas' => 'C'
        ]
    ],
    'Rabu' => [
        [
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:30',
            'mata_kuliah' => 'Sistem Basis Data',
            'dosen' => 'Dr. Maya Putri',
            'ruang' => 'Lab 2',
            'kelas' => 'A'
        ]
    ],
    'Kamis' => [
        [
            'jam_mulai' => '10:30',
            'jam_selesai' => '12:00',
            'mata_kuliah' => 'Pemrograman Mobile',
            'dosen' => 'Dr. Rendi Pratama',
            'ruang' => 'Lab 3',
            'kelas' => 'B'
        ]
    ],
    'Jumat' => [],
    'Sabtu' => []
];

$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// Get today's schedule
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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-calendar"></i> Jadwal Kuliah</h1>
                <p>Jadwal kuliah mingguan dan agenda harian Anda</p>
            </div>

            <!-- Today's Schedule Highlight -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-day"></i> Jadwal Hari Ini - <?php echo $today_indo; ?></h2>
                <div class="today-highlight">
                    <?php if (isset($jadwal_mingguan[$today_indo]) && !empty($jadwal_mingguan[$today_indo])): ?>
                        <div class="today-schedule-grid">
                            <?php foreach ($jadwal_mingguan[$today_indo] as $jadwal): ?>
                                <div class="schedule-highlight-card">
                                    <div class="schedule-time-badge">
                                        <span class="time"><?php echo $jadwal['jam_mulai']; ?></span>
                                        <span class="duration">
                                            <?php 
                                            $start = strtotime($jadwal['jam_mulai']);
                                            $end = strtotime($jadwal['jam_selesai']);
                                            $duration = ($end - $start) / 3600;
                                            echo $duration . ' jam';
                                            ?>
                                        </span>
                                    </div>
                                    <div class="schedule-details">
                                        <h3><?php echo $jadwal['mata_kuliah']; ?></h3>
                                        <p class="schedule-lecturer">
                                            <i class="fas fa-user-tie"></i> <?php echo $jadwal['dosen']; ?>
                                        </p>
                                        <p class="schedule-location">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo $jadwal['ruang']; ?> | Kelas <?php echo $jadwal['kelas']; ?>
                                        </p>
                                        <p class="schedule-time-range">
                                            <i class="fas fa-clock"></i> <?php echo $jadwal['jam_mulai']; ?> - <?php echo $jadwal['jam_selesai']; ?>
                                        </p>
                                    </div>
                                    <div class="schedule-actions">
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-map-marked-alt"></i> Petunjuk Arah
                                        </button>
                                        <button class="btn btn-secondary btn-sm">
                                            <i class="fas fa-bell"></i> Set Reminder
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-schedule-today">
                            <i class="fas fa-calendar-check"></i>
                            <h3>Tidak ada jadwal kuliah hari ini</h3>
                            <p>Anda bebas dari kuliah hari ini. Manfaatkan waktu untuk belajar mandiri atau mengerjakan tugas!</p>
                            <button class="btn btn-primary">
                                <i class="fas fa-book"></i> Lihat Materi Kuliah
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Weekly Schedule Grid -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-week"></i> Jadwal Mingguan</h2>
                <div class="weekly-schedule">
                    <div class="schedule-grid">
                        <?php foreach ($hari_list as $hari): ?>
                            <div class="day-column">
                                <div class="day-header">
                                    <h3><?php echo $hari; ?></h3>
                                    <span class="day-count">
                                        <?php echo isset($jadwal_mingguan[$hari]) ? count($jadwal_mingguan[$hari]) : 0; ?> kelas
                                    </span>
                                </div>
                                <div class="day-content">
                                    <?php if (isset($jadwal_mingguan[$hari]) && !empty($jadwal_mingguan[$hari])): ?>
                                        <?php foreach ($jadwal_mingguan[$hari] as $jadwal): ?>
                                            <div class="schedule-item">
                                                <div class="schedule-time">
                                                    <?php echo $jadwal['jam_mulai']; ?> - <?php echo $jadwal['jam_selesai']; ?>
                                                </div>
                                                <div class="schedule-course">
                                                    <?php echo $jadwal['mata_kuliah']; ?>
                                                </div>
                                                <div class="schedule-info">
                                                    <span><i class="fas fa-user-tie"></i> <?php echo $jadwal['dosen']; ?></span>
                                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo $jadwal['ruang']; ?></span>
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
                            <p>
                                <?php 
                                $total_classes = 0;
                                foreach ($jadwal_mingguan as $day_schedule) {
                                    $total_classes += count($day_schedule);
                                }
                                echo $total_classes; 
                                ?> kelas
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-text">
                            <h4>Total Jam Kuliah</h4>
                            <p>
                                <?php 
                                $total_hours = 0;
                                foreach ($jadwal_mingguan as $day_schedule) {
                                    foreach ($day_schedule as $schedule) {
                                        $start = strtotime($schedule['jam_mulai']);
                                        $end = strtotime($schedule['jam_selesai']);
                                        $total_hours += ($end - $start) / 3600;
                                    }
                                }
                                echo $total_hours; 
                                ?> jam/minggu
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
                                $rooms = [];
                                foreach ($jadwal_mingguan as $day_schedule) {
                                    foreach ($day_schedule as $schedule) {
                                        $rooms[] = $schedule['ruang'];
                                    }
                                }
                                echo count(array_unique($rooms)); 
                                ?> ruang
                            </p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-text">
                            <h4>Hari Tersibuk</h4>
                            <p>
                                <?php 
                                $max_classes = 0;
                                $busiest_day = '';
                                foreach ($jadwal_mingguan as $day => $schedule) {
                                    if (count($schedule) > $max_classes) {
                                        $max_classes = count($schedule);
                                        $busiest_day = $day;
                                    }
                                }
                                echo $busiest_day ?: 'Tidak ada'; 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Classes -->
            <div class="dashboard-section">
                <h2><i class="fas fa-clock"></i> Kelas Mendatang</h2>
                <div class="upcoming-classes">
                    <?php
                    // Get next 3 upcoming classes
                    $upcoming = [];
                    $current_time = time();
                    $current_day = date('w'); // 0 = Sunday, 1 = Monday, etc.
                    
                    // Sample upcoming classes
                    $next_classes = [
                        ['hari' => 'Senin', 'jam' => '08:00', 'mata_kuliah' => 'Pemrograman Web Lanjut', 'ruang' => 'Lab 1'],
                        ['hari' => 'Selasa', 'jam' => '13:00', 'mata_kuliah' => 'Algoritma dan Struktur Data', 'ruang' => 'Ruang 105'],
                        ['hari' => 'Rabu', 'jam' => '08:00', 'mata_kuliah' => 'Sistem Basis Data', 'ruang' => 'Lab 2']
                    ];
                    ?>
                    
                    <div class="upcoming-list">
                        <?php foreach ($next_classes as $class): ?>
                            <div class="upcoming-item">
                                <div class="upcoming-time">
                                    <span class="day"><?php echo $class['hari']; ?></span>
                                    <span class="time"><?php echo $class['jam']; ?></span>
                                </div>
                                <div class="upcoming-details">
                                    <h4><?php echo $class['mata_kuliah']; ?></h4>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $class['ruang']; ?></p>
                                </div>
                                <div class="upcoming-countdown">
                                    <span class="countdown-text">2 hari lagi</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <style>
        .today-highlight {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .today-schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .schedule-highlight-card {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .schedule-time-badge {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .schedule-time-badge .time {
            display: block;
            font-size: 2rem;
            font-weight: 700;
        }

        .schedule-time-badge .duration {
            display: block;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .schedule-details h3 {
            margin: 0 0 1rem 0;
            font-size: 1.25rem;
        }

        .schedule-details p {
            margin: 0.5rem 0;
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .schedule-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .schedule-actions .btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .schedule-actions .btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .no-schedule-today {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .no-schedule-today i {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1rem;
        }

        .no-schedule-today h3 {
            margin: 0 0 1rem 0;
            color: #374151;
        }

        .weekly-schedule {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            min-height: 400px;
        }

        .day-column {
            border-right: 1px solid #e5e7eb;
        }

        .day-column:last-child {
            border-right: none;
        }

        .day-header {
            background: #f8fafc;
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .day-header h3 {
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-size: 1rem;
        }

        .day-count {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .day-content {
            padding: 1rem;
            min-height: 300px;
        }

        .schedule-item {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            transition: transform 0.2s ease;
        }

        .schedule-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .schedule-time {
            font-size: 0.75rem;
            color: #059669;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .schedule-course {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .schedule-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .schedule-info span {
            font-size: 0.75rem;
            color: #6b7280;
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
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1.5rem;
        }

        .stat-text h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-size: 1rem;
        }

        .stat-text p {
            margin: 0;
            color: #10b981;
            font-weight: 600;
        }

        .upcoming-classes {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
        }

        .upcoming-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .upcoming-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .upcoming-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #10b981;
        }

        .upcoming-time {
            text-align: center;
            min-width: 80px;
        }

        .upcoming-time .day {
            display: block;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .upcoming-time .time {
            display: block;
            font-size: 1.25rem;
            font-weight: 700;
            color: #10b981;
        }

        .upcoming-details {
            flex: 1;
        }

        .upcoming-details h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
        }

        .upcoming-details p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .upcoming-countdown {
            text-align: right;
            min-width: 100px;
        }

        .countdown-text {
            background: #fef3c7;
            color: #92400e;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .day-column {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .day-column:last-child {
                border-bottom: none;
            }
            
            .today-schedule-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .upcoming-item {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
        }
    </style>
</body>
</html>
