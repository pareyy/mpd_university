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

// Get mata kuliah semester ini yang diambil mahasiswa
$mata_kuliah_query = "SELECT 
    mk.kode_mk,
    mk.nama_mk,
    mk.sks,
    mk.semester,
    d.nama as dosen_nama,
    j.hari,
    j.jam_mulai,
    j.jam_selesai,
    j.ruang,
    j.kelas,
    n.nilai_akhir,
    n.grade,
    CASE 
        WHEN n.grade IS NOT NULL THEN 'Selesai'
        ELSE 'Aktif'
    END as status
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
JOIN dosen d ON mk.dosen_id = d.id
LEFT JOIN jadwal j ON mk.id = j.mata_kuliah_id
LEFT JOIN nilai n ON mk.id = n.mata_kuliah_id AND n.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
ORDER BY mk.nama_mk";

$mk_result = mysqli_query($conn, $mata_kuliah_query);
$mata_kuliah_semester_ini = [];
while ($row = mysqli_fetch_assoc($mk_result)) {
    $jadwal_text = '';
    if ($row['hari'] && $row['jam_mulai'] && $row['jam_selesai']) {
        $jadwal_text = $row['hari'] . ', ' . substr($row['jam_mulai'], 0, 5) . '-' . substr($row['jam_selesai'], 0, 5);
    } else {
        $jadwal_text = 'Belum dijadwalkan';
    }
    
    $mata_kuliah_semester_ini[] = [
        'kode' => $row['kode_mk'],
        'nama' => $row['nama_mk'],
        'sks' => $row['sks'],
        'dosen' => $row['dosen_nama'],
        'ruang' => $row['ruang'] ?: 'TBA',
        'jadwal' => $jadwal_text,
        'nilai' => $row['grade'] ?: 'Belum Ada',
        'status' => $row['status'],
        'kelas' => $row['kelas'] ?: 'A'
    ];
}

// Get mata kuliah tersedia (yang belum diambil mahasiswa)
$available_mk_query = "SELECT 
    mk.id,
    mk.kode_mk,
    mk.nama_mk,
    mk.sks,
    mk.semester,
    d.nama as dosen_nama,
    (SELECT COUNT(*) FROM kelas k2 WHERE k2.mata_kuliah_id = mk.id) as terisi
FROM mata_kuliah mk
JOIN dosen d ON mk.dosen_id = d.id
WHERE mk.program_studi_id = '{$mahasiswa['program_studi_id']}'
AND mk.id NOT IN (
    SELECT mata_kuliah_id FROM kelas WHERE mahasiswa_id = '{$mahasiswa['id']}'
)
AND mk.semester <= '{$mahasiswa['semester']}'
ORDER BY mk.semester, mk.nama_mk
LIMIT 10";

$available_result = mysqli_query($conn, $available_mk_query);
$mata_kuliah_tersedia = [];
while ($row = mysqli_fetch_assoc($available_result)) {
    $mata_kuliah_tersedia[] = [
        'id' => $row['id'],
        'kode' => $row['kode_mk'],
        'nama' => $row['nama_mk'],
        'sks' => $row['sks'],
        'dosen' => $row['dosen_nama'],
        'prasyarat' => 'Tidak ada', // Default value since column doesn't exist in database
        'kuota' => 30, // Default quota, you can add this column to mata_kuliah table
        'terisi' => $row['terisi']
    ];
}

// Calculate IPK
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
    SUM(mk.sks) as total_sks_lulus
FROM nilai n
JOIN mata_kuliah mk ON n.mata_kuliah_id = mk.id
WHERE n.mahasiswa_id = '{$mahasiswa['id']}' AND n.grade IS NOT NULL AND n.grade != 'E'";

$ipk_result = mysqli_query($conn, $ipk_query);
$ipk_data = mysqli_fetch_assoc($ipk_result);
$ipk_kumulatif = $ipk_data['ipk_kumulatif'] ? number_format($ipk_data['ipk_kumulatif'], 2) : '0.00';
$total_sks_lulus = $ipk_data['total_sks_lulus'] ?: 0;

// Calculate semester IPK (current enrolled courses)
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
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
LEFT JOIN nilai n ON mk.id = n.mata_kuliah_id AND n.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
AND n.grade IS NOT NULL";

$semester_ipk_result = mysqli_query($conn, $semester_ipk_query);
$semester_ipk_data = mysqli_fetch_assoc($semester_ipk_result);
$ipk_semester = $semester_ipk_data['ipk_semester'] ? number_format($semester_ipk_data['ipk_semester'], 2) : '0.00';

// Calculate progress
$total_sks_semester = array_sum(array_column($mata_kuliah_semester_ini, 'sks'));
$completed_courses = array_filter($mata_kuliah_semester_ini, function($mk) {
    return $mk['nilai'] !== 'Belum Ada';
});
$progress_percentage = count($mata_kuliah_semester_ini) > 0 ? 
    round((count($completed_courses) / count($mata_kuliah_semester_ini)) * 100) : 0;

// Handle course enrollment
if ($_POST && isset($_POST['enroll_course'])) {
    $course_id = $_POST['course_id'];
    
    // Check if already enrolled
    $check_query = "SELECT id FROM kelas WHERE mata_kuliah_id = '$course_id' AND mahasiswa_id = '{$mahasiswa['id']}'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        // Enroll student
        $enroll_query = "INSERT INTO kelas (mata_kuliah_id, mahasiswa_id) VALUES ('$course_id', '{$mahasiswa['id']}')";
        if (mysqli_query($conn, $enroll_query)) {
            echo "<script>alert('Berhasil mendaftar mata kuliah!'); window.location.reload();</script>";
        } else {
            echo "<script>alert('Gagal mendaftar mata kuliah!');</script>";
        }
    } else {
        echo "<script>alert('Anda sudah terdaftar di mata kuliah ini!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mahasiswa_clean.css?v=<?php echo time(); ?>">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="page-header">
                <h1><i class="fas fa-book"></i> Mata Kuliah</h1>
                <div class="page-info">
                    <div class="course-info">
                        Kelola mata kuliah untuk <?php echo $mahasiswa['nama']; ?> - <?php echo $mahasiswa['program_studi_nama']; ?>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($mata_kuliah_semester_ini); ?></h3>
                        <p>Mata Kuliah Diambil</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_sks_semester; ?></h3>
                        <p>Total SKS</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $ipk_semester; ?></h3>
                        <p>IPK Semester</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $progress_percentage; ?>%</h3>
                        <p>Progress</p>
                    </div>
                </div>
            </div>

            <!-- Current Courses -->
            <div class="dashboard-section">
                <h2><i class="fas fa-book-open"></i> Mata Kuliah Semester Ini</h2>
                <?php if (!empty($mata_kuliah_semester_ini)): ?>
                    <div class="courses-grid">
                        <?php foreach ($mata_kuliah_semester_ini as $mk): ?>
                            <div class="course-card">
                                <div class="course-header">
                                    <div class="course-code">
                                        <span class="badge badge-primary"><?php echo $mk['kode']; ?></span>
                                        <span class="course-sks"><?php echo $mk['sks']; ?> SKS</span>
                                    </div>
                                    <div class="course-grade">
                                        <?php if ($mk['nilai'] !== 'Belum Ada'): ?>
                                            <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $mk['nilai']))); ?>">
                                                <?php echo $mk['nilai']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="grade-badge grade-pending">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="course-content">
                                    <h3><?php echo $mk['nama']; ?></h3>
                                    <p class="course-lecturer">
                                        <i class="fas fa-user-tie"></i> <?php echo $mk['dosen']; ?>
                                    </p>
                                    <p class="course-schedule">
                                        <i class="fas fa-clock"></i> <?php echo $mk['jadwal']; ?>
                                    </p>
                                    <p class="course-room">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo $mk['ruang']; ?> | Kelas <?php echo $mk['kelas']; ?>
                                    </p>
                                    <p class="course-status">
                                        <i class="fas fa-info-circle"></i> Status: <?php echo $mk['status']; ?>
                                    </p>
                                </div>
                                <div class="course-actions">
                                    <button class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    <button class="btn btn-secondary btn-sm">
                                        <i class="fas fa-file-alt"></i> Materi
                                    </button>
                                    <button class="btn btn-warning btn-sm">
                                        <i class="fas fa-tasks"></i> Tugas
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-courses">
                        <i class="fas fa-book-open"></i>
                        <h3>Belum Ada Mata Kuliah</h3>
                        <p>Anda belum mendaftar mata kuliah untuk semester ini.</p>
                        <button class="btn btn-primary">Daftar Mata Kuliah</button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Available Courses -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Mata Kuliah Tersedia</h2>
                <div class="available-courses">
                    <?php if (!empty($mata_kuliah_tersedia)): ?>
                        <div class="courses-table-container">
                            <table class="courses-table">
                                <thead>
                                    <tr>
                                        <th>Kode MK</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>SKS</th>
                                        <th>Dosen</th>
                                        <th>Prasyarat</th>
                                        <th>Kuota</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mata_kuliah_tersedia as $mk): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-secondary"><?php echo $mk['kode']; ?></span>
                                            </td>
                                            <td><?php echo $mk['nama']; ?></td>
                                            <td><?php echo $mk['sks']; ?> SKS</td>
                                            <td><?php echo $mk['dosen']; ?></td>
                                            <td><?php echo $mk['prasyarat']; ?></td>
                                            <td>
                                                <div class="quota-info">
                                                    <span class="quota-text"><?php echo $mk['terisi']; ?>/<?php echo $mk['kuota']; ?></span>
                                                    <div class="quota-bar">
                                                        <div class="quota-fill" style="width: <?php echo ($mk['terisi']/$mk['kuota'])*100; ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($mk['terisi'] < $mk['kuota']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $mk['id']; ?>">
                                                        <button type="submit" name="enroll_course" class="btn btn-success btn-sm" 
                                                                onclick="return confirm('Apakah Anda yakin ingin mendaftar mata kuliah <?php echo $mk['nama']; ?>?')">
                                                            <i class="fas fa-plus"></i> Daftar
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-times"></i> Penuh
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-available-courses">
                            <i class="fas fa-graduation-cap"></i>
                            <h3>Tidak Ada Mata Kuliah Tersedia</h3>
                            <p>Semua mata kuliah untuk program studi dan semester Anda sudah diambil atau belum tersedia.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Academic Progress -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Progress Akademik</h2>
                <div class="progress-overview">
                    <div class="semester-summary">
                        <h3>Ringkasan Semester</h3>
                        <div class="summary-stats">
                            <div class="summary-item">
                                <span class="summary-label">SKS Diambil:</span>
                                <span class="summary-value"><?php echo $total_sks_semester; ?> SKS</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">IPK Semester:</span>
                                <span class="summary-value"><?php echo $ipk_semester; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">IPK Kumulatif:</span>
                                <span class="summary-value"><?php echo $ipk_kumulatif; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Total SKS Lulus:</span>
                                <span class="summary-value"><?php echo $total_sks_lulus; ?> SKS</span>
                            </div>
                        </div>
                    </div>

                    <div class="grade-distribution">
                        <h3>Distribusi Nilai</h3>
                        <div class="grade-chart">
                            <?php
                            $grades = array_filter(array_column($mata_kuliah_semester_ini, 'nilai'), function($grade) {
                                return $grade !== 'Belum Ada';
                            });
                            
                            if (!empty($grades)):
                                $grade_counts = array_count_values($grades);
                                foreach ($grade_counts as $grade => $count):
                                    $percentage = ($count / count($grades)) * 100;
                            ?>
                                    <div class="grade-bar">
                                        <div class="grade-info">
                                            <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $grade))); ?>">
                                                <?php echo $grade; ?>
                                            </span>
                                            <span class="grade-count"><?php echo $count; ?> mata kuliah</span>
                                        </div>
                                        <div class="bar-container">
                                            <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                        <span class="percentage"><?php echo round($percentage, 1); ?>%</span>
                                    </div>
                            <?php 
                                endforeach;
                            else: 
                            ?>
                                <div class="no-grades">
                                    <i class="fas fa-chart-bar"></i>
                                    <p>Belum ada nilai yang tersedia untuk ditampilkan.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>
