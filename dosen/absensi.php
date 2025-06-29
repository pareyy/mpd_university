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
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'save_attendance') {
            $mata_kuliah_id = (int)$_POST['mata_kuliah_id'];
            $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
            $attendance_data = $_POST['attendance'];
            
            foreach ($attendance_data as $mahasiswa_id => $data) {
                $status = mysqli_real_escape_string($conn, $data['status']);
                $keterangan = mysqli_real_escape_string($conn, $data['keterangan'] ?? '');
                
                // Insert or update absensi
                $absensi_query = "INSERT INTO absensi (mata_kuliah_id, mahasiswa_id, tanggal, status, keterangan)
                                 VALUES ($mata_kuliah_id, $mahasiswa_id, '$tanggal', '$status', '$keterangan')
                                 ON DUPLICATE KEY UPDATE
                                 status = '$status', keterangan = '$keterangan'";
                
                mysqli_query($conn, $absensi_query);
            }
            
            $message = "Absensi berhasil disimpan!";
            $message_type = 'success';
        }
    }
}

// Get selected mata kuliah data
$selected_mk_id = isset($_GET['mk_id']) ? (int)$_GET['mk_id'] : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
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
    
    // Get mahasiswa data with their attendance
    $mahasiswa_query = "SELECT m.id, m.nim, m.nama,
                              a.status, a.keterangan
                       FROM kelas k
                       JOIN mahasiswa m ON k.mahasiswa_id = m.id
                       LEFT JOIN absensi a ON k.mata_kuliah_id = a.mata_kuliah_id 
                                           AND k.mahasiswa_id = a.mahasiswa_id 
                                           AND a.tanggal = '$selected_date'
                       WHERE k.mata_kuliah_id = $selected_mk_id
                       ORDER BY m.nama";
    $mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
    
    while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
        $mahasiswa_data[] = $row;
    }
}

// Get attendance statistics
$attendance_stats = [];
if ($selected_mk_id > 0) {
    $stats_query = "SELECT 
                      COUNT(DISTINCT a.mahasiswa_id) as total_records,
                      SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                      SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                      SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as izin,
                      SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as alpha
                    FROM absensi a
                    WHERE a.mata_kuliah_id = $selected_mk_id 
                    AND a.tanggal = '$selected_date'";
    $stats_result = mysqli_query($conn, $stats_query);
    $attendance_stats = mysqli_fetch_assoc($stats_result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Mahasiswa - MPD University</title>
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
                <h1><i class="fas fa-user-check"></i> Absensi Mahasiswa</h1>
                <p>Kelola absensi mahasiswa untuk setiap pertemuan</p>
            </div>

            <!-- Alert Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-filter"></i> Filter Mata Kuliah & Tanggal</h2>
                    <button class="btn btn-primary" onclick="loadAbsensi()" id="loadBtn" disabled>
                        <i class="fas fa-search"></i> Tampilkan Data
                    </button>
                </div>
                
                <div class="filter-container filter-enhanced">
                    <div class="filter-group filter-group-enhanced">
                        <label for="mata_kuliah_filter">Mata Kuliah</label>
                        <select id="mata_kuliah_filter" class="form-control" onchange="enableLoadButton()">
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
                    <div class="filter-group filter-group-enhanced">
                        <label for="tanggal_filter">Tanggal</label>
                        <input type="date" id="tanggal_filter" class="form-control" 
                               value="<?php echo $selected_date; ?>" max="<?php echo date('Y-m-d'); ?>" onchange="enableLoadButton()">
                    </div>
                </div>
            </div>

            <!-- Attendance Input Section -->
            <?php if ($selected_mk_id > 0 && !empty($mahasiswa_data)): ?>
            <div class="dashboard-section" id="attendance-section">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Absensi - <?php echo $selected_mk_name; ?></h2>
                    <div class="attendance-date">
                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($selected_date)); ?>
                    </div>
                </div>
                
                <form method="POST" id="attendanceForm">
                    <input type="hidden" name="action" value="save_attendance">
                    <input type="hidden" name="mata_kuliah_id" value="<?php echo $selected_mk_id; ?>">
                    <input type="hidden" name="tanggal" value="<?php echo $selected_date; ?>">
                    
                    <div class="attendance-container">
                        <div class="table-responsive">
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Status Kehadiran</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($mahasiswa_data as $mhs): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $mhs['nim']; ?></td>
                                            <td><?php echo $mhs['nama']; ?></td>
                                            <td>
                                                <div class="status-radio-group">
                                                    <label class="radio-label">
                                                        <input type="radio" name="attendance[<?php echo $mhs['id']; ?>][status]" 
                                                               value="Hadir" <?php echo ($mhs['status'] == 'Hadir' || !$mhs['status']) ? 'checked' : ''; ?>>
                                                        <span class="status-hadir">Hadir</span>
                                                    </label>
                                                    <label class="radio-label">
                                                        <input type="radio" name="attendance[<?php echo $mhs['id']; ?>][status]" 
                                                               value="Sakit" <?php echo ($mhs['status'] == 'Sakit') ? 'checked' : ''; ?>>
                                                        <span class="status-sakit">Sakit</span>
                                                    </label>
                                                    <label class="radio-label">
                                                        <input type="radio" name="attendance[<?php echo $mhs['id']; ?>][status]" 
                                                               value="Izin" <?php echo ($mhs['status'] == 'Izin') ? 'checked' : ''; ?>>
                                                        <span class="status-izin">Izin</span>
                                                    </label>
                                                    <label class="radio-label">
                                                        <input type="radio" name="attendance[<?php echo $mhs['id']; ?>][status]" 
                                                               value="Alpha" <?php echo ($mhs['status'] == 'Alpha') ? 'checked' : ''; ?>>
                                                        <span class="status-alpha">Alpha</span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control keterangan-input" 
                                                       name="attendance[<?php echo $mhs['id']; ?>][keterangan]"
                                                       value="<?php echo htmlspecialchars($mhs['keterangan'] ?? ''); ?>"
                                                       placeholder="Keterangan tambahan...">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan Absensi
                            </button>
                            <button type="button" class="btn btn-warning" onclick="markAllPresent()">
                                <i class="fas fa-check-double"></i> Tandai Semua Hadir
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Attendance Statistics -->
            <div class="dashboard-section" id="stats-section">
                <h2><i class="fas fa-chart-pie"></i> Statistik Kehadiran</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon stat-hadir">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Hadir</h3>
                            <p class="stat-number"><?php echo $attendance_stats['hadir'] ?? 0; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stat-sakit">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Sakit</h3>
                            <p class="stat-number"><?php echo $attendance_stats['sakit'] ?? 0; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stat-izin">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Izin</h3>
                            <p class="stat-number"><?php echo $attendance_stats['izin'] ?? 0; ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stat-alpha">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Alpha</h3>
                            <p class="stat-number"><?php echo $attendance_stats['alpha'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function enableLoadButton() {
            const selectedCourse = document.getElementById('mata_kuliah_filter').value;
            const selectedDate = document.getElementById('tanggal_filter').value;
            const loadBtn = document.getElementById('loadBtn');
            loadBtn.disabled = !(selectedCourse && selectedDate);
        }

        function loadAbsensi() {
            const selectedCourse = document.getElementById('mata_kuliah_filter').value;
            const selectedDate = document.getElementById('tanggal_filter').value;
            
            if (selectedCourse && selectedDate) {
                window.location.href = 'absensi.php?mk_id=' + selectedCourse + '&date=' + selectedDate;
            } else {
                alert('Silakan pilih mata kuliah dan tanggal terlebih dahulu.');
            }
        }

        function markAllPresent() {
            const hadirRadios = document.querySelectorAll('input[type="radio"][value="Hadir"]');
            hadirRadios.forEach(radio => {
                radio.checked = true;
            });
        }

        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset semua data absensi?')) {
                document.getElementById('attendanceForm').reset();
            }
        }
    </script>
</body>
</html>