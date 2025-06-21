<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

// Sample data - replace with actual database queries
$current_semester = "Ganjil 2024/2025";
$student_nim = "2021080001";
$student_name = "Ahmad Fadhil Rahman";

// Sample attendance data
$attendance_summary = [
    'total_classes' => 128,
    'attended' => 118,
    'absent' => 6,
    'late' => 4,
    'attendance_percentage' => 92.2
];

// Sample courses with attendance details
$course_attendance = [
    [
        'id' => 1,
        'kode' => 'PWL001',
        'nama' => 'Pemrograman Web Lanjut',
        'dosen' => 'Dr. Budi Santoso',
        'sks' => 3,
        'total_pertemuan' => 16,
        'hadir' => 15,
        'tidak_hadir' => 0,
        'terlambat' => 1,
        'persentase' => 93.8,
        'status' => 'Aman',
        'min_kehadiran' => 75
    ],
    [
        'id' => 2,
        'kode' => 'DB001',
        'nama' => 'Basis Data',
        'dosen' => 'Dr. Siti Aminah',
        'sks' => 3,
        'total_pertemuan' => 16,
        'hadir' => 14,
        'tidak_hadir' => 1,
        'terlambat' => 1,
        'persentase' => 87.5,
        'status' => 'Aman',
        'min_kehadiran' => 75
    ],
    [
        'id' => 3,
        'kode' => 'RPL001',
        'nama' => 'Rekayasa Perangkat Lunak',
        'dosen' => 'Dr. Agus Priyanto',
        'sks' => 3,
        'total_pertemuan' => 16,
        'hadir' => 13,
        'tidak_hadir' => 2,
        'terlambat' => 1,
        'persentase' => 81.3,
        'status' => 'Aman',
        'min_kehadiran' => 75
    ],
    [
        'id' => 4,
        'kode' => 'AI001',
        'nama' => 'Artificial Intelligence',
        'dosen' => 'Dr. Maya Sari',
        'sks' => 3,
        'total_pertemuan' => 16,
        'hadir' => 11,
        'tidak_hadir' => 3,
        'terlambat' => 2,
        'persentase' => 68.8,
        'status' => 'Perhatian',
        'min_kehadiran' => 75
    ]
];

// Sample recent attendance history
$recent_attendance = [
    [
        'tanggal' => '2024-11-26',
        'mata_kuliah' => 'Pemrograman Web Lanjut',
        'waktu' => '08:00 - 10:30',
        'status' => 'Hadir',
        'keterangan' => 'Tepat waktu'
    ],
    [
        'tanggal' => '2024-11-25',
        'mata_kuliah' => 'Basis Data',
        'waktu' => '10:30 - 13:00',
        'status' => 'Hadir',
        'keterangan' => 'Tepat waktu'
    ],
    [
        'tanggal' => '2024-11-24',
        'mata_kuliah' => 'Artificial Intelligence',
        'waktu' => '13:00 - 15:30',
        'status' => 'Tidak Hadir',
        'keterangan' => 'Sakit'
    ],
    [
        'tanggal' => '2024-11-23',
        'mata_kuliah' => 'Rekayasa Perangkat Lunak',
        'waktu' => '08:00 - 10:30',
        'status' => 'Terlambat',
        'keterangan' => 'Terlambat 15 menit'
    ],
    [
        'tanggal' => '2024-11-22',
        'mata_kuliah' => 'Pemrograman Web Lanjut',
        'waktu' => '10:30 - 13:00',
        'status' => 'Hadir',
        'keterangan' => 'Tepat waktu'
    ]
];

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
            return 'danger';
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
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                <span class="student-info"><?php echo $student_nim . " - " . $student_name; ?></span>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['attended']; ?></h3>
                    <p>Total Hadir</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fa-solid fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['absent']; ?></h3>
                    <p>Total Tidak Hadir</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['late']; ?></h3>
                    <p>Total Terlambat</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fa-solid fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $attendance_summary['attendance_percentage']; ?>%</h3>
                    <p>Persentase Kehadiran</p>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <!-- Course Attendance Details -->
            <div class="content-section full-width">
                <div class="section-header">
                    <h2><i class="fa-solid fa-chart-bar"></i> Rekap Per Mata Kuliah</h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
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
                                        <div class="course-info">
                                            <strong><?php echo $course['kode']; ?></strong>
                                            <br><?php echo $course['nama']; ?>
                                            <span class="sks-badge"><?php echo $course['sks']; ?> SKS</span>
                                        </div>
                                    </td>
                                    <td><?php echo $course['dosen']; ?></td>
                                    <td><?php echo $course['total_pertemuan']; ?></td>
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
                                        <div class="progress-container">
                                            <div class="progress-bar">
                                                <div class="progress-fill <?php echo getAttendanceColor($course['persentase']); ?>" 
                                                     style="width: <?php echo $course['persentase']; ?>%"></div>
                                            </div>
                                            <span class="progress-text"><?php echo $course['persentase']; ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $course['persentase'] >= $course['min_kehadiran'] ? 'success' : 'danger'; ?>">
                                            <?php echo $course['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <!-- Attendance Chart -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-chart-pie"></i> Distribusi Kehadiran</h2>
                </div>
                <div class="chart-container">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>

            <!-- Recent Attendance History -->
            <div class="content-section">
                <div class="section-header">
                    <h2><i class="fa-solid fa-history"></i> Riwayat Kehadiran Terbaru</h2>
                </div>
                <div class="activity-list">
                    <?php foreach ($recent_attendance as $record): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <h4><?php echo $record['mata_kuliah']; ?></h4>
                                    <span class="badge <?php echo getStatusBadge($record['status']); ?>">
                                        <?php echo $record['status']; ?>
                                    </span>
                                </div>
                                <p class="activity-details">
                                    <i class="fa-solid fa-calendar"></i> <?php echo date('d M Y', strtotime($record['tanggal'])); ?> | 
                                    <i class="fa-solid fa-clock"></i> <?php echo $record['waktu']; ?>
                                </p>
                                <p class="activity-note"><?php echo $record['keterangan']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Attendance Guidelines -->
        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-info-circle"></i> Ketentuan Kehadiran</h2>
            </div>
            <div class="guidelines-grid">
                <div class="guideline-card">
                    <div class="guideline-icon success">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="guideline-content">
                        <h4>Kehadiran Minimal</h4>
                        <p>Mahasiswa wajib hadir minimal 75% dari total pertemuan untuk dapat mengikuti ujian akhir.</p>
                    </div>
                </div>
                <div class="guideline-card">
                    <div class="guideline-icon warning">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="guideline-content">
                        <h4>Toleransi Keterlambatan</h4>
                        <p>Keterlambatan maksimal 15 menit masih dihitung hadir. Lebih dari itu dianggap tidak hadir.</p>
                    </div>
                </div>
                <div class="guideline-card">
                    <div class="guideline-icon info">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <div class="guideline-content">
                        <h4>Izin Sakit</h4>
                        <p>Ketidakhadiran karena sakit dengan surat dokter dapat dikecualikan dari perhitungan kehadiran.</p>
                    </div>
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
