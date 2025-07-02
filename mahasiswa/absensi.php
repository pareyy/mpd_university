<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Get mahasiswa information
$user_id = $_SESSION['user_id'];
$mahasiswa_query = "SELECT m.*, ps.nama as program_studi_nama, f.nama as fakultas_nama 
                    FROM mahasiswa m 
                    JOIN program_studi ps ON m.program_studi_id = ps.id 
                    JOIN fakultas f ON ps.fakultas_id = f.id 
                    WHERE m.user_id = '$user_id'";
$mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
$mahasiswa = mysqli_fetch_assoc($mahasiswa_result);

if (!$mahasiswa) {
    echo "Data mahasiswa tidak ditemukan!";
    exit();
}

$current_semester = "Ganjil 2024/2025";

// Get attendance summary
$attendance_summary_query = "SELECT 
    COUNT(*) as total_classes,
    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as attended,
    SUM(CASE WHEN status IN ('Alpha', 'Sakit', 'Izin') THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as late
FROM absensi a
JOIN mata_kuliah mk ON a.mata_kuliah_id = mk.id
JOIN kelas k ON mk.id = k.mata_kuliah_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
AND a.mahasiswa_id = '{$mahasiswa['id']}'";

$attendance_summary_result = mysqli_query($conn, $attendance_summary_query);
$attendance_data = mysqli_fetch_assoc($attendance_summary_result);

$attendance_summary = [
    'total_classes' => $attendance_data['total_classes'] ?: 0,
    'attended' => $attendance_data['attended'] ?: 0,
    'absent' => $attendance_data['absent'] ?: 0,
    'late' => $attendance_data['late'] ?: 0,
    'attendance_percentage' => $attendance_data['total_classes'] > 0 ? 
        round(($attendance_data['attended'] / $attendance_data['total_classes']) * 100, 1) : 0
];

// Get course attendance details
$course_attendance_query = "SELECT 
    mk.kode_mk,
    mk.nama_mk,
    mk.sks,
    d.nama as dosen_nama,
    COUNT(*) as total_pertemuan,
    SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
    SUM(CASE WHEN a.status IN ('Alpha', 'Sakit', 'Izin') THEN 1 ELSE 0 END) as tidak_hadir,
    SUM(CASE WHEN a.status = 'Terlambat' THEN 1 ELSE 0 END) as terlambat,
    ROUND((SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as persentase
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
JOIN dosen d ON mk.dosen_id = d.id
LEFT JOIN absensi a ON mk.id = a.mata_kuliah_id AND a.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
GROUP BY mk.id, mk.kode_mk, mk.nama_mk, mk.sks, d.nama
HAVING COUNT(*) > 0
ORDER BY mk.nama_mk";

$course_attendance_result = mysqli_query($conn, $course_attendance_query);
$course_attendance = [];

while ($row = mysqli_fetch_assoc($course_attendance_result)) {
    $course_attendance[] = [
        'id' => count($course_attendance) + 1,
        'kode' => $row['kode_mk'],
        'nama' => $row['nama_mk'],
        'dosen' => $row['dosen_nama'],
        'sks' => $row['sks'],
        'total_pertemuan' => $row['total_pertemuan'],
        'hadir' => $row['hadir'] ?: 0,
        'tidak_hadir' => $row['tidak_hadir'] ?: 0,
        'terlambat' => $row['terlambat'] ?: 0,
        'persentase' => $row['persentase'] ?: 0,
        'status' => ($row['persentase'] ?: 0) >= 75 ? 'Aman' : 'Perhatian',
        'min_kehadiran' => 75
    ];
}

// Get recent attendance history
$recent_attendance_query = "SELECT 
    a.tanggal,
    mk.nama_mk as mata_kuliah,
    j.jam_mulai,
    j.jam_selesai,
    a.status,
    a.keterangan
FROM absensi a
JOIN mata_kuliah mk ON a.mata_kuliah_id = mk.id
JOIN kelas k ON mk.id = k.mata_kuliah_id
LEFT JOIN jadwal j ON mk.id = j.mata_kuliah_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
AND a.mahasiswa_id = '{$mahasiswa['id']}'
ORDER BY a.tanggal DESC, j.jam_mulai DESC
LIMIT 10";

$recent_attendance_result = mysqli_query($conn, $recent_attendance_query);
$recent_attendance = [];

while ($row = mysqli_fetch_assoc($recent_attendance_result)) {
    $waktu = 'TBA';
    if ($row['jam_mulai'] && $row['jam_selesai']) {
        $waktu = substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5);
    }
    
    $recent_attendance[] = [
        'tanggal' => $row['tanggal'],
        'mata_kuliah' => $row['mata_kuliah'],
        'waktu' => $waktu,
        'status' => $row['status'],
        'keterangan' => $row['keterangan'] ?: ($row['status'] == 'Hadir' ? 'Tepat waktu' : '')
    ];
}

function getAttendanceColor($percentage) {
    if ($percentage >= 85) return 'success';
    if ($percentage >= 75) return 'warning';
    return 'danger';
}

function getStatusBadge($status) {
    switch ($status) {
        case 'Hadir':
            return 'success';
        case 'Terlambat':
            return 'warning';
        case 'Tidak Hadir':
        case 'Alpha':
            return 'danger';
        case 'Sakit':
        case 'Izin':
            return 'info';
        default:
            return 'secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi - Portal Mahasiswa MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mahasiswa_clean.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <h1><i class="fa-solid fa-user-check"></i> Rekap Absensi</h1>
            <div class="page-info">
                <span class="semester-info"><?php echo $current_semester; ?></span>
                <span class="student-info"><?php echo $mahasiswa['nim'] . " - " . $mahasiswa['nama']; ?></span>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['attended']; ?></h3>
                    <p>Total Hadir</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['absent']; ?></h3>
                    <p>Total Tidak Hadir</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['late']; ?></h3>
                    <p>Total Terlambat</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['attendance_percentage']; ?>%</h3>
                    <p>Persentase Kehadiran</p>
                </div>
            </div>
        </div>

        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-chart-bar"></i> Rekap Per Mata Kuliah</h2>
            </div>
            <?php if (!empty($course_attendance)): ?>
                <div class="courses-table-container">
                    <table class="courses-table">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Total</th>
                                <th>Hadir</th>
                                <th>Tidak Hadir</th>
                                <th>Terlambat</th>
                                <th>Persentase</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($course_attendance as $course): ?>
                                <tr>
                                    <td>
                                        <div class="course-name-cell">
                                            <div class="course-title"><?php echo $course['kode']; ?></div>
                                            <div class="course-semester"><?php echo $course['nama']; ?></div>
                                            <span class="sks-badge"><?php echo $course['sks']; ?> SKS</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="lecturer-info">
                                            <i class="fa-solid fa-user"></i>
                                            <?php echo $course['dosen']; ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo $course['total_pertemuan']; ?></strong></td>
                                    <td>
                                        <span class="badge success"><?php echo $course['hadir']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge danger"><?php echo $course['tidak_hadir']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge warning"><?php echo $course['terlambat']; ?></span>
                                    </td>
                                    <td>
                                        <div class="progress-overview">
                                            <div class="progress-bar">
                                                <div class="progress-fill <?php echo getAttendanceColor($course['persentase']); ?>" 
                                                        style="width: <?php echo $course['persentase']; ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo $course['persentase']; ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="quota-status <?php echo $course['persentase'] >= $course['min_kehadiran'] ? 'available' : 'full'; ?>">
                                            <?php echo $course['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <div class="no-content-icon">
                        <i class="fa-solid fa-calendar-times"></i>
                    </div>
                    <h3>Belum Ada Data Absensi</h3>
                    <p>Data absensi akan muncul setelah Anda mengikuti perkuliahan dan dosen mencatat kehadiran.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-grid">
            <!-- Attendance Chart -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-chart-pie"></i> Distribusi Kehadiran</h2>
                </div>
                <div class="grade-chart" style="height: 400px; position: relative;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <!-- Recent Attendance History -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-history"></i> Riwayat Kehadiran Terbaru</h2>
                </div>
                <div class="grades-content">
                    <?php if (!empty($recent_attendance)): ?>
                        <?php foreach ($recent_attendance as $record): ?>
                            <div class="grade-item">
                                <div class="grade-info">
                                    <h4><?php echo $record['mata_kuliah']; ?></h4>
                                    <p>
                                        <i class="fa-solid fa-calendar"></i> <?php echo date('d M Y', strtotime($record['tanggal'])); ?> | 
                                        <i class="fa-solid fa-clock"></i> <?php echo $record['waktu']; ?>
                                        <?php if ($record['keterangan']): ?>
                                            <br><small><?php echo $record['keterangan']; ?></small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="grade-value">
                                    <span class="grade-badge grade-<?php echo strtolower(str_replace(' ', '', $record['status'])); ?>">
                                        <?php echo $record['status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-times"></i>
                            <h3>Belum ada riwayat kehadiran</h3>
                            <p>Riwayat kehadiran akan muncul setelah Anda mengikuti perkuliahan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Attendance Guidelines -->
        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-info-circle"></i> Ketentuan Kehadiran</h2>
            </div>
            <div class="quick-actions">
                <div class="action-card">
                    <i class="fa-solid fa-check" style="color: #28a745;"></i>
                    <h3>Kehadiran Minimal</h3>
                    <p>Mahasiswa wajib hadir minimal 75% dari total pertemuan untuk dapat mengikuti ujian akhir.</p>
                </div>
                <div class="action-card">
                    <i class="fa-solid fa-clock" style="color: #ffc107;"></i>
                    <h3>Toleransi Keterlambatan</h3>
                    <p>Keterlambatan maksimal 15 menit masih dihitung hadir. Lebih dari itu dianggap tidak hadir.</p>
                </div>
                <div class="action-card">
                    <i class="fa-solid fa-file-medical" style="color: #17a2b8;"></i>
                    <h3>Izin Sakit</h3>
                    <p>Ketidakhadiran karena sakit dengan surat dokter dapat dikecualikan dari perhitungan kehadiran.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Tidak Hadir', 'Terlambat'],
                datasets: [{
                    data: [<?php echo $attendance_summary['attended']; ?>, 
                           <?php echo $attendance_summary['absent']; ?>, 
                           <?php echo $attendance_summary['late']; ?>],
                    backgroundColor: [
                        '#28a745',
                        '#dc3545',
                        '#ffc107'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
