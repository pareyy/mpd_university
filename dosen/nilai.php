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

// Get mata kuliah list from database
$mata_kuliah_query = "SELECT mk.id, mk.nama_mk, mk.kode_mk, j.kelas
                      FROM mata_kuliah mk
                      LEFT JOIN jadwal j ON mk.id = j.mata_kuliah_id
                      WHERE mk.dosen_id = '$dosen_id'
                      GROUP BY mk.id
                      ORDER BY mk.nama_mk";
$mata_kuliah_result = mysqli_query($conn, $mata_kuliah_query);
$mata_kuliah_list = [];
while ($row = mysqli_fetch_assoc($mata_kuliah_result)) {
    $mata_kuliah_list[] = $row;
}

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'save_grades') {
        $mata_kuliah_id = (int)$_POST['mata_kuliah_id'];
        $grades_data = $_POST['grades'];
        
        foreach ($grades_data as $mahasiswa_id => $grades) {
            $tugas1 = (float)$grades['tugas1'];
            $tugas2 = (float)$grades['tugas2'];
            $uts = (float)$grades['uts'];
            $uas = (float)$grades['uas'];
            
            // Calculate final grade
            $nilai_akhir = round(($tugas1 * 0.2) + ($tugas2 * 0.2) + ($uts * 0.3) + ($uas * 0.3), 2);
            
            // Determine grade letter
            if ($nilai_akhir >= 85) $grade = 'A';
            elseif ($nilai_akhir >= 70) $grade = 'B';
            elseif ($nilai_akhir >= 60) $grade = 'C';
            elseif ($nilai_akhir >= 50) $grade = 'D';
            else $grade = 'E';
            
            // Insert or update nilai
            $nilai_query = "INSERT INTO nilai (mata_kuliah_id, mahasiswa_id, tugas1, tugas2, uts, uas, nilai_akhir, grade, updated_at)
                           VALUES ($mata_kuliah_id, $mahasiswa_id, $tugas1, $tugas2, $uts, $uas, $nilai_akhir, '$grade', NOW())
                           ON DUPLICATE KEY UPDATE
                           tugas1 = $tugas1, tugas2 = $tugas2, uts = $uts, uas = $uas,
                           nilai_akhir = $nilai_akhir, grade = '$grade', updated_at = NOW()";
            
            mysqli_query($conn, $nilai_query);
        }
        
        $message = "Nilai berhasil disimpan!";
        $message_type = 'success';
    }
}

// Get selected mata kuliah data
$selected_mk_id = isset($_GET['mk_id']) ? (int)$_GET['mk_id'] : 0;
$mahasiswa_data = [];
$selected_mk_name = '';

if ($selected_mk_id > 0) {
    // Get mata kuliah name
    $mk_name_query = "SELECT mk.nama_mk, mk.kode_mk, j.kelas 
                      FROM mata_kuliah mk 
                      LEFT JOIN jadwal j ON mk.id = j.mata_kuliah_id 
                      WHERE mk.id = $selected_mk_id AND mk.dosen_id = '$dosen_id'";
    $mk_name_result = mysqli_query($conn, $mk_name_query);
    $mk_data = mysqli_fetch_assoc($mk_name_result);
    $selected_mk_name = $mk_data ? $mk_data['nama_mk'] . ' - Kelas ' . ($mk_data['kelas'] ?? 'A') : '';
    
    // Get mahasiswa data with their grades
    $mahasiswa_query = "SELECT m.id, m.nim, m.nama,
                              n.tugas1, n.tugas2, n.uts, n.uas, n.nilai_akhir, n.grade
                       FROM kelas k
                       JOIN mahasiswa m ON k.mahasiswa_id = m.id
                       LEFT JOIN nilai n ON k.mata_kuliah_id = n.mata_kuliah_id AND k.mahasiswa_id = n.mahasiswa_id
                       WHERE k.mata_kuliah_id = $selected_mk_id
                       ORDER BY m.nama";
    $mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
    
    while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
        $mahasiswa_data[] = $row;
    }
}

// Calculate final grade function
function hitungNilaiAkhir($tugas1, $tugas2, $uts, $uas) {
    return round(($tugas1 * 0.2) + ($tugas2 * 0.2) + ($uts * 0.3) + ($uas * 0.3), 2);
}

function getGradeLetter($nilai) {
    if ($nilai >= 85) return 'A';
    elseif ($nilai >= 70) return 'B';
    elseif ($nilai >= 60) return 'C';
    elseif ($nilai >= 50) return 'D';
    else return 'E';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/dosen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_dosen.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-star"></i> Input Nilai Mahasiswa</h1>
                <p>Kelola dan input nilai mahasiswa</p>
            </div>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-filter"></i> Filter Mata Kuliah</h2>
                <div class="filter-container">
                    <select id="mata_kuliah_filter" class="form-control" onchange="loadMahasiswa()">
                        <option value="">Pilih Mata Kuliah</option>
                        <?php foreach ($mata_kuliah_list as $mk): ?>
                            <option value="<?php echo $mk['id']; ?>" 
                                    <?php echo ($selected_mk_id == $mk['id']) ? 'selected' : ''; ?>>
                                <?php echo $mk['kode_mk']; ?> - <?php echo $mk['nama_mk']; ?> 
                                <?php echo $mk['kelas'] ? '- Kelas ' . $mk['kelas'] : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Grade Input Section -->
            <?php if ($selected_mk_id > 0 && !empty($mahasiswa_data)): ?>
            <div class="dashboard-section" id="grade-section">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Input Nilai - <?php echo $selected_mk_name; ?></h2>
                </div>
                
                <form method="POST" id="gradesForm">
                    <input type="hidden" name="action" value="save_grades">
                    <input type="hidden" name="mata_kuliah_id" value="<?php echo $selected_mk_id; ?>">
                    
                    <div class="grades-container">
                        <div class="table-responsive">
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Tugas 1<br><small>(20%)</small></th>
                                        <th>Tugas 2<br><small>(20%)</small></th>
                                        <th>UTS<br><small>(30%)</small></th>
                                        <th>UAS<br><small>(30%)</small></th>
                                        <th>Nilai Akhir</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mahasiswa_data as $mhs): ?>
                                        <?php 
                                        $tugas1 = $mhs['tugas1'] ?? 0;
                                        $tugas2 = $mhs['tugas2'] ?? 0;
                                        $uts = $mhs['uts'] ?? 0;
                                        $uas = $mhs['uas'] ?? 0;
                                        $nilai_akhir = $mhs['nilai_akhir'] ?? hitungNilaiAkhir($tugas1, $tugas2, $uts, $uas);
                                        $grade = $mhs['grade'] ?? getGradeLetter($nilai_akhir);
                                        $status = $nilai_akhir >= 60 ? 'Lulus' : 'Tidak Lulus';
                                        ?>
                                        <tr>
                                            <td><?php echo $mhs['nim']; ?></td>
                                            <td><?php echo $mhs['nama']; ?></td>
                                            <td>
                                                <input type="number" class="grade-input" 
                                                       name="grades[<?php echo $mhs['id']; ?>][tugas1]"
                                                       value="<?php echo $tugas1; ?>" 
                                                       min="0" max="100" step="0.01"
                                                       onchange="calculateFinalGrade(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="grade-input" 
                                                       name="grades[<?php echo $mhs['id']; ?>][tugas2]"
                                                       value="<?php echo $tugas2; ?>" 
                                                       min="0" max="100" step="0.01"
                                                       onchange="calculateFinalGrade(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="grade-input" 
                                                       name="grades[<?php echo $mhs['id']; ?>][uts]"
                                                       value="<?php echo $uts; ?>" 
                                                       min="0" max="100" step="0.01"
                                                       onchange="calculateFinalGrade(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="grade-input" 
                                                       name="grades[<?php echo $mhs['id']; ?>][uas]"
                                                       value="<?php echo $uas; ?>" 
                                                       min="0" max="100" step="0.01"
                                                       onchange="calculateFinalGrade(this)">
                                            </td>
                                            <td class="final-grade"><?php echo $nilai_akhir; ?></td>
                                            <td>
                                                <span class="grade-badge grade-<?php echo strtolower($grade); ?>">
                                                    <?php echo $grade; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $nilai_akhir >= 60 ? 'status-pass' : 'status-fail'; ?>">
                                                    <?php echo $status; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan Semua Nilai
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Grade Statistics -->
            <div class="dashboard-section" id="stats-section">
                <h2><i class="fas fa-chart-pie"></i> Statistik Nilai</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Mahasiswa</h3>
                            <p class="stat-number"><?php echo count($mahasiswa_data); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Lulus</h3>
                            <p class="stat-number">
                                <?php 
                                $lulus = 0;
                                foreach ($mahasiswa_data as $mhs) {
                                    $nilai = $mhs['nilai_akhir'] ?? 0;
                                    if ($nilai >= 60) $lulus++;
                                }
                                echo $lulus;
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tidak Lulus</h3>
                            <p class="stat-number"><?php echo count($mahasiswa_data) - $lulus; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rata-rata Nilai</h3>
                            <p class="stat-number">
                                <?php 
                                if (count($mahasiswa_data) > 0) {
                                    $total = 0;
                                    foreach ($mahasiswa_data as $mhs) {
                                        $total += $mhs['nilai_akhir'] ?? 0;
                                    }
                                    echo round($total / count($mahasiswa_data), 1);
                                } else {
                                    echo "0";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function loadMahasiswa() {
            const selectedCourse = document.getElementById('mata_kuliah_filter').value;
            if (selectedCourse) {
                window.location.href = 'nilai.php?mk_id=' + selectedCourse;
            } else {
                window.location.href = 'nilai.php';
            }
        }

        function calculateFinalGrade(input) {
            const row = input.closest('tr');
            const grades = row.querySelectorAll('.grade-input');
            
            const tugas1 = parseFloat(grades[0].value) || 0;
            const tugas2 = parseFloat(grades[1].value) || 0;
            const uts = parseFloat(grades[2].value) || 0;
            const uas = parseFloat(grades[3].value) || 0;
            
            const finalGrade = Math.round((tugas1 * 0.2) + (tugas2 * 0.2) + (uts * 0.3) + (uas * 0.3) * 100) / 100;
            
            row.querySelector('.final-grade').textContent = finalGrade;
            
            // Update grade letter
            let gradeLetter = '';
            if (finalGrade >= 85) gradeLetter = 'A';
            else if (finalGrade >= 70) gradeLetter = 'B';
            else if (finalGrade >= 60) gradeLetter = 'C';
            else if (finalGrade >= 50) gradeLetter = 'D';
            else gradeLetter = 'E';
            
            const gradeBadge = row.querySelector('.grade-badge');
            gradeBadge.textContent = gradeLetter;
            gradeBadge.className = `grade-badge grade-${gradeLetter.toLowerCase()}`;
            
            // Update status
            const status = finalGrade >= 60 ? 'Lulus' : 'Tidak Lulus';
            const statusBadge = row.querySelector('.status-badge');
            statusBadge.textContent = status;
            statusBadge.className = `status-badge ${finalGrade >= 60 ? 'status-pass' : 'status-fail'}`;
        }

        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset semua nilai?')) {
                document.getElementById('gradesForm').reset();
            }
        }
    </script>
</body>
</html>