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
$query = "SELECT m.*, ps.nama as program_studi_nama, f.nama as fakultas_nama 
          FROM mahasiswa m 
          JOIN program_studi ps ON m.program_studi_id = ps.id 
          JOIN fakultas f ON ps.fakultas_id = f.id 
          WHERE m.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$mahasiswa = mysqli_fetch_assoc($result);

if (!$mahasiswa) {
    echo "Data mahasiswa tidak ditemukan!";
    exit();
}

// Get mata kuliah yang diambil semester ini dengan nilai
$mata_kuliah_query = "SELECT 
    mk.nama_mk, 
    mk.sks, 
    d.nama as dosen_nama,
    n.nilai_akhir,
    n.grade,
    mk.semester
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
JOIN dosen d ON mk.dosen_id = d.id
LEFT JOIN nilai n ON mk.id = n.mata_kuliah_id AND n.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
ORDER BY mk.nama_mk";

$mk_result = mysqli_query($conn, $mata_kuliah_query);
$mata_kuliah_diambil = [];
while ($row = mysqli_fetch_assoc($mk_result)) {
    $mata_kuliah_diambil[] = [
        'nama' => $row['nama_mk'],
        'sks' => $row['sks'],
        'dosen' => $row['dosen_nama'],
        'nilai' => $row['grade'] ?: 'Belum Ada',
        'semester' => $row['semester']
    ];
}

// Get jadwal hari ini
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

$jadwal_query = "SELECT 
    mk.nama_mk as mata_kuliah,
    j.jam_mulai,
    j.jam_selesai,
    j.ruang
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
JOIN jadwal j ON mk.id = j.mata_kuliah_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}' 
AND j.hari = '$today_indo'
ORDER BY j.jam_mulai";

$jadwal_result = mysqli_query($conn, $jadwal_query);
$jadwal_hari_ini = [];
while ($row = mysqli_fetch_assoc($jadwal_result)) {
    $jadwal_hari_ini[] = [
        'mata_kuliah' => $row['mata_kuliah'],
        'waktu' => substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5),
        'ruang' => $row['ruang']
    ];
}

// Calculate IPK (simplified calculation)
$ipk_query = "SELECT 
    AVG(CASE 
        WHEN n.grade = 'A' THEN 4.0
        WHEN n.grade = 'A-' THEN 3.7
        WHEN n.grade = 'B+' THEN 3.3
        WHEN n.grade = 'B' THEN 3.0
        WHEN n.grade = 'B-' THEN 2.7
        WHEN n.grade = 'C+' THEN 2.3
        WHEN n.grade = 'C' THEN 2.0
        WHEN n.grade = 'C-' THEN 1.7
        WHEN n.grade = 'D' THEN 1.0
        ELSE 0
    END) as ipk_kumulatif,
    COUNT(*) as total_mk_dinilai,
    SUM(mk.sks) as total_sks
FROM nilai n
JOIN mata_kuliah mk ON n.mata_kuliah_id = mk.id
WHERE n.mahasiswa_id = '{$mahasiswa['id']}' AND n.grade IS NOT NULL";

$ipk_result = mysqli_query($conn, $ipk_query);
$ipk_data = mysqli_fetch_assoc($ipk_result);
$ipk_kumulatif = $ipk_data['ipk_kumulatif'] ? number_format($ipk_data['ipk_kumulatif'], 2) : '0.00';
$total_sks = $ipk_data['total_sks'] ?: 0;

// Calculate current semester IPK
$semester_ipk_query = "SELECT 
    AVG(CASE 
        WHEN n.grade = 'A' THEN 4.0
        WHEN n.grade = 'A-' THEN 3.7
        WHEN n.grade = 'B+' THEN 3.3
        WHEN n.grade = 'B' THEN 3.0
        WHEN n.grade = 'B-' THEN 2.7
        WHEN n.grade = 'C+' THEN 2.3
        WHEN n.grade = 'C' THEN 2.0
        WHEN n.grade = 'C-' THEN 1.7
        WHEN n.grade = 'D' THEN 1.0
        ELSE 0
    END) as ipk_semester
FROM nilai n
JOIN mata_kuliah mk ON n.mata_kuliah_id = mk.id
WHERE n.mahasiswa_id = '{$mahasiswa['id']}' 
AND mk.semester = '{$mahasiswa['semester']}'
AND n.grade IS NOT NULL";

$semester_ipk_result = mysqli_query($conn, $semester_ipk_query);
$semester_ipk_data = mysqli_fetch_assoc($semester_ipk_result);
$ipk_semester = $semester_ipk_data['ipk_semester'] ? number_format($semester_ipk_data['ipk_semester'], 2) : '0.00';

// Get total SKS for current semester
$sks_query = "SELECT 
    SUM(mk.sks) as total_sks_semester,
    COUNT(*) as total_mk_semester
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'";

$sks_result = mysqli_query($conn, $sks_query);
$sks_data = mysqli_fetch_assoc($sks_result);
$total_sks_semester = $sks_data['total_sks_semester'] ?: 0;

// Calculate progress (based on completed courses with grades)
$completed_sks_query = "SELECT SUM(mk.sks) as completed_sks
FROM nilai n
JOIN mata_kuliah mk ON n.mata_kuliah_id = mk.id
WHERE n.mahasiswa_id = '{$mahasiswa['id']}' AND n.grade IS NOT NULL";

$completed_result = mysqli_query($conn, $completed_sks_query);
$completed_data = mysqli_fetch_assoc($completed_result);
$completed_sks = $completed_data['completed_sks'] ?: 0;

$progress_percentage = $total_sks_semester > 0 ? round(($completed_sks / $total_sks_semester) * 100) : 0;

// Sample tugas pending (would need additional tables for real implementation)
$tugas_pending = [
    ['mata_kuliah' => 'Pemrograman Web Lanjut', 'judul' => 'Project Akhir Website E-Commerce', 'deadline' => '2024-12-25'],
    ['mata_kuliah' => 'Database', 'judul' => 'ERD dan Normalisasi Database', 'deadline' => '2024-12-28']
];

// Get recent attendance statistics
$attendance_query = "SELECT 
    COUNT(*) as total_absensi,
    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir
FROM absensi a
JOIN mata_kuliah mk ON a.mata_kuliah_id = mk.id
JOIN kelas k ON mk.id = k.mata_kuliah_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
AND a.mahasiswa_id = '{$mahasiswa['id']}'";

$attendance_result = mysqli_query($conn, $attendance_query);
$attendance_data = mysqli_fetch_assoc($attendance_result);
$attendance_percentage = $attendance_data['total_absensi'] > 0 ? 
    round(($attendance_data['total_hadir'] / $attendance_data['total_absensi']) * 100) : 100;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mahasiswa_clean.css?v=<?php echo time(); ?>">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="page-header">
                <h1><i class="fas fa-graduation-cap"></i> Dashboard Mahasiswa</h1>
                <div class="page-info">
                    <div class="student-info">
                        Selamat datang, <strong><?php echo htmlspecialchars($mahasiswa['nama']); ?></strong>
                    </div>
                    <div class="semester-info">
                        <?php echo $mahasiswa['program_studi_nama']; ?> - <?php echo $mahasiswa['fakultas_nama']; ?> | Semester <?php echo $mahasiswa['semester']; ?>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($mata_kuliah_diambil); ?></h3>
                        <p>Mata Kuliah Diambil</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $ipk_kumulatif; ?></h3>
                        <p>Indeks Prestasi Kumulatif</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($jadwal_hari_ini); ?></h3>
                        <p>Kelas Hari Ini</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon danger">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $attendance_percentage; ?>%</h3>
                        <p>Persentase Kehadiran</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-bolt"></i> Aksi Cepat</h2>
                </div>
                <div class="quick-actions">
                    <a href="mata_kuliah.php" class="action-card">
                        <i class="fas fa-book"></i>
                        <div>
                            <h3>Mata Kuliah</h3>
                            <p>Lihat mata kuliah dan nilai</p>
                        </div>
                    </a>

                    <a href="jadwal.php" class="action-card">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <h3>Jadwal Kuliah</h3>
                            <p>Cek jadwal kuliah mingguan</p>
                        </div>
                    </a>

                    <a href="nilai.php" class="action-card">
                        <i class="fas fa-chart-line"></i>
                        <div>
                            <h3>Transkrip Nilai</h3>
                            <p>Lihat transkrip dan IPK</p>
                        </div>
                    </a>

                    <a href="absensi.php" class="action-card">
                        <i class="fas fa-user-check"></i>
                        <div>
                            <h3>Absensi</h3>
                            <p>Rekap kehadiran kuliah</p>
                        </div>
                    </a>

                    <a href="tugas.php" class="action-card">
                        <i class="fas fa-tasks"></i>
                        <div>
                            <h3>Tugas</h3>
                            <p>Kelola tugas dan pengumpulan</p>
                        </div>
                    </a>

                    <a href="profile.php" class="action-card">
                        <i class="fas fa-user-circle"></i>
                        <div>
                            <h3>Profile</h3>
                            <p>Kelola informasi personal</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-day"></i> Jadwal Hari Ini - <?php echo $today_indo; ?></h2>
                </div>
                <div class="schedule-content">
                    <?php if (!empty($jadwal_hari_ini)): ?>
                        <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    <span class="time"><?php echo explode(' - ', $jadwal['waktu'])[0]; ?></span>
                                    <span class="duration"><?php echo $jadwal['waktu']; ?></span>
                                </div>
                                <div class="schedule-info">
                                    <h4><?php echo $jadwal['mata_kuliah']; ?></h4>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo $jadwal['ruang']; ?></p>
                                </div>
                                <div class="schedule-status">
                                    <span class="status-badge status-upcoming">Akan Datang</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>Tidak ada jadwal hari ini</h3>
                            <p>Anda bebas dari kuliah hari ini. Gunakan waktu untuk belajar mandiri!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Assignments -->
            <?php if (!empty($tugas_pending)): ?>
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-tasks"></i> Tugas Pending</h2>
                </div>
                <div class="assignments-content">
                    <?php foreach ($tugas_pending as $tugas): ?>
                        <div class="assignment-item">
                            <div class="assignment-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="assignment-info">
                                <h4><?php echo $tugas['judul']; ?></h4>
                                <p class="assignment-course"><?php echo $tugas['mata_kuliah']; ?></p>
                                <p class="assignment-deadline">
                                    <i class="fas fa-clock"></i> 
                                    Deadline: <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?>
                                </p>
                            </div>
                            <div class="assignment-actions">
                                <a href="tugas.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Academic Progress -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> Progress Akademik</h2>
                </div>
                <div class="progress-content">
                    <div class="progress-grid">
                        <div class="progress-card">
                            <h3>Semester <?php echo $mahasiswa['semester']; ?> - Progress</h3>
                            <div class="progress-stats">
                                <div class="progress-item">
                                    <span class="progress-label">SKS Diambil</span>
                                    <span class="progress-value"><?php echo $total_sks_semester; ?> SKS</span>
                                </div>
                                <div class="progress-item">
                                    <span class="progress-label">SKS Selesai</span>
                                    <span class="progress-value"><?php echo $completed_sks; ?> SKS</span>
                                </div>
                                <div class="progress-item">
                                    <span class="progress-label">Progress</span>
                                    <span class="progress-value"><?php echo $progress_percentage; ?>%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                            </div>
                        </div>

                        <div class="gpa-card">
                            <h3>Informasi IPK</h3>
                            <div class="gpa-details">
                                <div class="gpa-item">
                                    <span class="gpa-label">IPK Semester</span>
                                    <span class="gpa-value"><?php echo $ipk_semester; ?></span>
                                </div>
                                <div class="gpa-item">
                                    <span class="gpa-label">IPK Kumulatif</span>
                                    <span class="gpa-value"><?php echo $ipk_kumulatif; ?></span>
                                </div>
                                <div class="gpa-item">
                                    <span class="gpa-label">Total SKS</span>
                                    <span class="gpa-value"><?php echo $total_sks; ?> SKS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Grades -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Nilai Terbaru</h2>
                </div>
                <div class="grades-content">
                    <?php 
                    $recent_grades = array_slice(array_filter($mata_kuliah_diambil, function($mk) {
                        return $mk['nilai'] !== 'Belum Ada';
                    }), 0, 3);
                    
                    if (!empty($recent_grades)): 
                    ?>
                        <?php foreach ($recent_grades as $mk): ?>
                            <div class="grade-item">
                                <div class="grade-info">
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
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <h3>Belum ada nilai</h3>
                            <p>Nilai mata kuliah akan muncul di sini setelah dosen memasukkan nilai.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="section-footer">
                        <a href="nilai.php" class="btn btn-primary">
                            <i class="fas fa-chart-line"></i> Lihat Semua Nilai
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>