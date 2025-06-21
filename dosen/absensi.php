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

// Sample data (in real application, this would come from database)
$mata_kuliah_list = [
    ['id' => 1, 'nama' => 'Pemrograman Web Lanjut', 'kelas' => 'A'],
    ['id' => 2, 'nama' => 'Database', 'kelas' => 'B'],
    ['id' => 3, 'nama' => 'Algoritma dan Struktur Data', 'kelas' => 'C']
];

$mahasiswa_data = [
    ['nim' => '2021001', 'nama' => 'Ahmad Rizki'],
    ['nim' => '2021002', 'nama' => 'Siti Aisyah'],
    ['nim' => '2021003', 'nama' => 'Budi Santoso'],
    ['nim' => '2021004', 'nama' => 'Dewi Lestari'],
    ['nim' => '2021005', 'nama' => 'Rendi Pratama'],
    ['nim' => '2021006', 'nama' => 'Maya Putri'],
    ['nim' => '2021007', 'nama' => 'Andi Wijaya'],
    ['nim' => '2021008', 'nama' => 'Lina Sari']
];

// Sample attendance history
$attendance_history = [
    ['tanggal' => '2024-06-10', 'mata_kuliah' => 'Pemrograman Web Lanjut', 'hadir' => 7, 'tidak_hadir' => 1],
    ['tanggal' => '2024-06-12', 'mata_kuliah' => 'Database', 'hadir' => 6, 'tidak_hadir' => 2],
    ['tanggal' => '2024-06-14', 'mata_kuliah' => 'Algoritma dan Struktur Data', 'hadir' => 8, 'tidak_hadir' => 0],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Absensi - MPD University</title>
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
                <h1><i class="fas fa-user-check"></i> Kelola Absensi Mahasiswa</h1>
                <p>Catat kehadiran mahasiswa dalam perkuliahan</p>
            </div>

            <!-- Today's Attendance -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-day"></i> Absensi Hari Ini</h2>
                <div class="attendance-container">
                    <div class="attendance-header">
                        <div class="course-info">
                            <select id="mata_kuliah_select" class="form-control">
                                <option value="">Pilih Mata Kuliah</option>
                                <?php foreach ($mata_kuliah_list as $mk): ?>
                                    <option value="<?php echo $mk['id']; ?>">
                                        <?php echo $mk['nama']; ?> - Kelas <?php echo $mk['kelas']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" id="tanggal_absensi" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            <button class="btn btn-primary" onclick="loadAttendance()">
                                <i class="fas fa-search"></i> Muat Data
                            </button>
                        </div>
                        <div class="attendance-actions" id="attendance-actions" style="display: none;">
                            <button class="btn btn-success" onclick="saveAttendance()">
                                <i class="fas fa-save"></i> Simpan Absensi
                            </button>
                            <button class="btn btn-secondary" onclick="markAllPresent()">
                                <i class="fas fa-check-double"></i> Tandai Semua Hadir
                            </button>
                        </div>
                    </div>

                    <div class="attendance-list" id="attendance-list" style="display: none;">
                        <div class="attendance-stats">
                            <div class="stat-item">
                                <span class="stat-label">Total Mahasiswa:</span>
                                <span class="stat-value" id="total-students"><?php echo count($mahasiswa_data); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Hadir:</span>
                                <span class="stat-value present" id="present-count">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Tidak Hadir:</span>
                                <span class="stat-value absent" id="absent-count"><?php echo count($mahasiswa_data); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Persentase Kehadiran:</span>
                                <span class="stat-value" id="attendance-percentage">0%</span>
                            </div>
                        </div>

                        <div class="students-list">
                            <?php foreach ($mahasiswa_data as $index => $mhs): ?>
                                <div class="student-item">
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="student-details">
                                            <h4><?php echo $mhs['nama']; ?></h4>
                                            <p><?php echo $mhs['nim']; ?></p>
                                        </div>
                                    </div>
                                    <div class="attendance-controls">
                                        <label class="attendance-option">
                                            <input type="radio" name="attendance_<?php echo $index; ?>" value="hadir" onchange="updateStats()">
                                            <span class="option-label present">
                                                <i class="fas fa-check"></i> Hadir
                                            </span>
                                        </label>
                                        <label class="attendance-option">
                                            <input type="radio" name="attendance_<?php echo $index; ?>" value="tidak_hadir" checked onchange="updateStats()">
                                            <span class="option-label absent">
                                                <i class="fas fa-times"></i> Tidak Hadir
                                            </span>
                                        </label>
                                        <label class="attendance-option">
                                            <input type="radio" name="attendance_<?php echo $index; ?>" value="izin" onchange="updateStats()">
                                            <span class="option-label excuse">
                                                <i class="fas fa-clock"></i> Izin
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="dashboard-section">
                <h2><i class="fas fa-history"></i> Riwayat Absensi</h2>
                <div class="history-container">
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Mata Kuliah</th>
                                    <th>Mahasiswa Hadir</th>
                                    <th>Mahasiswa Tidak Hadir</th>
                                    <th>Persentase Kehadiran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_history as $history): ?>
                                    <?php 
                                    $total = $history['hadir'] + $history['tidak_hadir'];
                                    $percentage = round(($history['hadir'] / $total) * 100, 1);
                                    ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($history['tanggal'])); ?></td>
                                        <td><?php echo $history['mata_kuliah']; ?></td>
                                        <td>
                                            <span class="count-badge present"><?php echo $history['hadir']; ?></span>
                                        </td>
                                        <td>
                                            <span class="count-badge absent"><?php echo $history['tidak_hadir']; ?></span>
                                        </td>
                                        <td>
                                            <div class="percentage-bar">
                                                <div class="percentage-fill" style="width: <?php echo $percentage; ?>%"></div>
                                                <span class="percentage-text"><?php echo $percentage; ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="viewDetails('<?php echo $history['tanggal']; ?>')">
                                                <i class="fas fa-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-line"></i> Statistik Kehadiran</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Pertemuan</h3>
                            <p class="stat-number"><?php echo count($attendance_history); ?></p>
                            <small>Pertemuan yang telah dilaksanakan</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rata-rata Kehadiran</h3>
                            <p class="stat-number">
                                <?php 
                                $total_hadir = array_sum(array_column($attendance_history, 'hadir'));
                                $total_mahasiswa = array_sum(array_map(function($h) { return $h['hadir'] + $h['tidak_hadir']; }, $attendance_history));
                                echo round(($total_hadir / $total_mahasiswa) * 100, 1);
                                ?>%
                            </p>
                            <small>Persentase kehadiran keseluruhan</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Mahasiswa Aktif</h3>
                            <p class="stat-number"><?php echo count($mahasiswa_data); ?></p>
                            <small>Mahasiswa terdaftar</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Perlu Perhatian</h3>
                            <p class="stat-number">3</p>
                            <small>Mahasiswa dengan kehadiran < 75%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function loadAttendance() {
            const mataKuliah = document.getElementById('mata_kuliah_select').value;
            const tanggal = document.getElementById('tanggal_absensi').value;
            
            if (mataKuliah && tanggal) {
                document.getElementById('attendance-list').style.display = 'block';
                document.getElementById('attendance-actions').style.display = 'flex';
                updateStats();
            } else {
                alert('Pilih mata kuliah dan tanggal terlebih dahulu!');
            }
        }

        function updateStats() {
            const totalStudents = <?php echo count($mahasiswa_data); ?>;
            let presentCount = 0;
            let absentCount = 0;
            let excuseCount = 0;

            for (let i = 0; i < totalStudents; i++) {
                const selectedOption = document.querySelector(`input[name="attendance_${i}"]:checked`);
                if (selectedOption) {
                    switch (selectedOption.value) {
                        case 'hadir':
                            presentCount++;
                            break;
                        case 'tidak_hadir':
                            absentCount++;
                            break;
                        case 'izin':
                            excuseCount++;
                            break;
                    }
                }
            }

            document.getElementById('present-count').textContent = presentCount;
            document.getElementById('absent-count').textContent = absentCount;
            
            const percentage = totalStudents > 0 ? ((presentCount / totalStudents) * 100).toFixed(1) : 0;
            document.getElementById('attendance-percentage').textContent = percentage + '%';
        }

        function markAllPresent() {
            const totalStudents = <?php echo count($mahasiswa_data); ?>;
            for (let i = 0; i < totalStudents; i++) {
                const hadirOption = document.querySelector(`input[name="attendance_${i}"][value="hadir"]`);
                if (hadirOption) {
                    hadirOption.checked = true;
                }
            }
            updateStats();
        }

        function saveAttendance() {
            // Gather attendance data
            const attendanceData = [];
            const totalStudents = <?php echo count($mahasiswa_data); ?>;
            
            for (let i = 0; i < totalStudents; i++) {
                const selectedOption = document.querySelector(`input[name="attendance_${i}"]:checked`);
                if (selectedOption) {
                    attendanceData.push({
                        student_index: i,
                        status: selectedOption.value
                    });
                }
            }
            
            // Simulate save (in real app, send to server)
            console.log('Saving attendance:', attendanceData);
            alert('Absensi berhasil disimpan!');
        }

        function viewDetails(tanggal) {
            const historyData = <?php echo json_encode($attendance_history); ?>;
            const detail = historyData.find(h => h.tanggal === tanggal);
            
            if (detail) {
                const total = detail.hadir + detail.tidak_hadir;
                const percentage = ((detail.hadir / total) * 100).toFixed(1);
                
                alert(`Detail Absensi - ${detail.tanggal}\n\n` +
                      `Mata Kuliah: ${detail.mata_kuliah}\n` +
                      `Mahasiswa Hadir: ${detail.hadir}\n` +
                      `Mahasiswa Tidak Hadir: ${detail.tidak_hadir}\n` +
                      `Total Mahasiswa: ${total}\n` +
                      `Persentase Kehadiran: ${percentage}%`);
            }
        }

        // Initialize stats on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
        });
    </script>

    <style>
        .attendance-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .attendance-header {
            padding: 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .course-info {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .course-info .form-control {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .course-info .form-control:focus {
            outline: none;
            border-color: #2563eb;
        }

        .attendance-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .attendance-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            display: block;
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #374151;
        }

        .stat-value.present {
            color: #10b981;
        }

        .stat-value.absent {
            color: #ef4444;
        }

        .students-list {
            padding: 1.5rem;
        }

        .student-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .student-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .student-details h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-size: 1rem;
        }

        .student-details p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .attendance-controls {
            display: flex;
            gap: 0.5rem;
        }

        .attendance-option {
            cursor: pointer;
        }

        .attendance-option input[type="radio"] {
            display: none;
        }

        .option-label {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .attendance-option input[type="radio"]:checked + .option-label.present {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }

        .attendance-option input[type="radio"]:checked + .option-label.absent {
            background: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .attendance-option input[type="radio"]:checked + .option-label.excuse {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        .history-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .history-table th,
        .history-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .history-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }

        .count-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .count-badge.present {
            background: #d1fae5;
            color: #065f46;
        }

        .count-badge.absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .percentage-bar {
            position: relative;
            background: #f3f4f6;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            width: 120px;
        }

        .percentage-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .percentage-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
        }        /* Ensure all form controls have exact same dimensions */
        input.form-control,
        select.form-control {
            height: 48px !important;
            padding: 0.875rem !important;
            box-sizing: border-box !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            width: 100% !important;
            font-size: 1rem !important;
            line-height: 1.2 !important;
            vertical-align: top;
        }

        select.form-control {
            padding-right: 2.5rem !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            background-color: white;
        }

        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .course-info {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .attendance-actions {
                justify-content: stretch;
            }
            
            .attendance-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0.5rem;
                margin: 0;
            }
            
            .dashboard-header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .dashboard-section {
                margin-bottom: 1.5rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.25rem;
                padding: 0 0.5rem;
            }

            .attendance-container {
                margin: 0 0.5rem 1.5rem 0.5rem;
                border-radius: 8px;
            }

            .attendance-header {
                padding: 1rem;
            }

            .course-info {
                flex-direction: column;
                gap: 1rem;
            }

            .form-control {
                padding: 1rem;
                font-size: 1rem;
                border-radius: 10px;
                height: 52px;
                width: 100%;
                box-sizing: border-box;
            }

            .attendance-actions {
                flex-direction: column;
                gap: 0.75rem;
                margin-top: 1rem;
            }
            
            .btn {
                padding: 1rem;
                font-size: 1rem;
                min-height: 48px;
                border-radius: 10px;
                justify-content: center;
                touch-action: manipulation;
            }
            
            .attendance-stats {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                padding: 1rem;
            }
            
            .student-card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }
            
            .student-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .attendance-options {
                width: 100%;
                justify-content: stretch;
                gap: 0.5rem;
            }
            
            .option-btn {
                flex: 1;
                padding: 0.75rem;
                font-size: 0.9rem;
                min-height: 44px;
            }
            
            .history-container {
                margin: 0 0.5rem;
                border-radius: 8px;
            }
            
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }

            .history-table {
                font-size: 0.85rem;
                min-width: 700px;
            }

            .history-table th,
            .history-table td {
                padding: 0.75rem 0.5rem;
                white-space: nowrap;
            }
            
            .btn-sm {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
                min-height: 40px;
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0.25rem;
            }
            
            .dashboard-header {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.25rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.1rem;
                padding: 0 0.25rem;
            }

            .attendance-container {
                margin: 0 0.25rem 1rem 0.25rem;
            }

            .attendance-header {
                padding: 0.75rem;
            }

            .form-control {
                padding: 0.875rem;
                height: 48px;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }
            
            .attendance-stats {
                padding: 0.75rem;
            }
            
            .student-card {
                padding: 0.75rem;
            }
            
            .history-container {
                margin: 0 0.25rem;
            }

            .history-table {
                font-size: 0.8rem;
                min-width: 650px;
            }
            
            .history-table th,
            .history-table td {
                padding: 0.625rem 0.375rem;
            }
            
            .btn-sm {
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
                min-height: 36px;
            }
        }

        /* Landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .course-info {
                flex-direction: row;
                gap: 0.75rem;
            }
            
            .attendance-actions {
                flex-direction: row;
                gap: 1rem;
            }
            
            .attendance-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .student-card:hover {
                transform: none;
            }
            
            .student-card:active {
                transform: scale(0.98);
            }
            
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
        }
    </style>
</body>
</html>
