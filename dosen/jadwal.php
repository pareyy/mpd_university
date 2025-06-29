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
$query = "SELECT u.*, d.id as dosen_id FROM users u 
          LEFT JOIN dosen d ON u.id = d.user_id 
          WHERE u.id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$dosen_id = $user['dosen_id'];

if (!$dosen_id) {
    die("Error: Data dosen tidak ditemukan.");
}

// Get jadwal data from database for this dosen
$jadwal_query = "SELECT j.*, mk.kode_mk, mk.nama_mk, mk.sks,
                        COUNT(k.mahasiswa_id) as jumlah_mahasiswa
                 FROM jadwal j
                 LEFT JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
                 LEFT JOIN kelas k ON mk.id = k.mata_kuliah_id
                 WHERE mk.dosen_id = '$dosen_id'
                 GROUP BY j.id
                 ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai";
$jadwal_result = mysqli_query($conn, $jadwal_query);
$jadwal = [];
while ($row = mysqli_fetch_assoc($jadwal_result)) {
    $jadwal[] = [
        'id' => $row['id'],
        'hari' => $row['hari'],
        'jam_mulai' => date('H:i', strtotime($row['jam_mulai'])),
        'jam_selesai' => date('H:i', strtotime($row['jam_selesai'])),
        'mata_kuliah' => $row['nama_mk'],
        'kode_mk' => $row['kode_mk'],
        'kelas' => $row['kelas'],
        'ruang' => $row['ruang'],
        'sks' => $row['sks'],
        'jumlah_mahasiswa' => $row['jumlah_mahasiswa'] ?: 0
    ];
}

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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/dosen.css">
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
                                <?php if (isset($jadwal_by_day[$hari]) && !empty($jadwal_by_day[$hari])): ?>
                                    <?php foreach ($jadwal_by_day[$hari] as $j): ?>
                                        <div class="schedule-card">
                                            <div class="schedule-time">
                                                <?php echo $j['jam_mulai']; ?> - <?php echo $j['jam_selesai']; ?>
                                            </div>
                                            <div class="schedule-content">
                                                <div class="schedule-subject">
                                                    <?php echo htmlspecialchars($j['mata_kuliah']); ?>
                                                </div>
                                                <div class="schedule-code">
                                                    <?php echo htmlspecialchars($j['kode_mk']); ?>
                                                </div>
                                                <div class="schedule-details">
                                                    <span><i class="fas fa-users"></i> Kelas <?php echo htmlspecialchars($j['kelas']); ?></span>
                                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($j['ruang']); ?></span>
                                                    <span><i class="fas fa-user-graduate"></i> <?php echo $j['jumlah_mahasiswa']; ?> mhs</span>
                                                    <span><i class="fas fa-graduation-cap"></i> <?php echo $j['sks']; ?> SKS</span>
                                                </div>
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
                    
                    <?php if (isset($jadwal_by_day[$today_indo]) && !empty($jadwal_by_day[$today_indo])): ?>
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
                                    <h4><?php echo htmlspecialchars($j['mata_kuliah']); ?></h4>
                                    <p>
                                        <i class="fas fa-code"></i> <?php echo htmlspecialchars($j['kode_mk']); ?> | 
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($j['ruang']); ?> | 
                                        <i class="fas fa-users"></i> Kelas <?php echo htmlspecialchars($j['kelas']); ?> | 
                                        <i class="fas fa-user-graduate"></i> <?php echo $j['jumlah_mahasiswa']; ?> mahasiswa
                                    </p>
                                </div>
                                <div class="schedule-actions">
                                    <button class="btn btn-primary btn-sm" onclick="goToAbsensi(<?php echo $j['id']; ?>)">
                                        <i class="fas fa-user-check"></i> Absensi
                                    </button>
                                    <button class="btn btn-secondary btn-sm" onclick="goToMateri(<?php echo $j['id']; ?>)">
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

    <script>
        function goToAbsensi(jadwalId) {
            // Redirect to absensi page with jadwal ID
            window.location.href = 'absensi.php?jadwal_id=' + jadwalId;
        }

        function goToMateri(jadwalId) {
            // Redirect to materi page with jadwal ID
            window.location.href = 'materi.php?jadwal_id=' + jadwalId;
        }
    </script>
</body>
</html>