<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Get admin information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Sample schedule data
$jadwal_data = [
    [
        'id' => 1,
        'mata_kuliah' => 'Pemrograman Web Lanjut',
        'kode' => 'PWL001',
        'dosen' => 'Dr. Ahmad Sudirman',
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'ruangan' => 'Lab. Komputer 1',
        'semester' => '5',
        'kelas' => 'TI-5A'
    ],
    [
        'id' => 2,
        'mata_kuliah' => 'Database Management',
        'kode' => 'DBM001',
        'dosen' => 'Prof. Siti Nurhaliza',
        'hari' => 'Selasa',
        'jam_mulai' => '10:00',
        'jam_selesai' => '12:00',
        'ruangan' => 'Ruang 201',
        'semester' => '4',
        'kelas' => 'SI-4B'
    ],
    [
        'id' => 3,
        'mata_kuliah' => 'Algoritma dan Struktur Data',
        'kode' => 'ASD001',
        'dosen' => 'Dr. Budi Santoso',
        'hari' => 'Rabu',
        'jam_mulai' => '13:00',
        'jam_selesai' => '15:00',
        'ruangan' => 'Lab. Komputer 2',
        'semester' => '3',
        'kelas' => 'TI-3A'
    ],
    [
        'id' => 4,
        'mata_kuliah' => 'Sistem Informasi Manajemen',
        'kode' => 'SIM001',
        'dosen' => 'Dr. Maya Putri',
        'hari' => 'Kamis',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:00',
        'ruangan' => 'Ruang 301',
        'semester' => '6',
        'kelas' => 'SI-6A'
    ],
    [
        'id' => 5,
        'mata_kuliah' => 'Jaringan Komputer',
        'kode' => 'JK001',
        'dosen' => 'Prof. Rendi Pratama',
        'hari' => 'Jumat',
        'jam_mulai' => '14:00',
        'jam_selesai' => '16:00',
        'ruangan' => 'Lab. Jaringan',
        'semester' => '5',
        'kelas' => 'TK-5B'
    ]
];

// Days of the week
$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// Time slots
$jam_list = [
    '08:00-10:00', '10:00-12:00', '13:00-15:00', '15:00-17:00'
];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $message = "Jadwal berhasil ditambahkan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'edit') {
            $message = "Jadwal berhasil diperbarui!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'delete') {
            $message = "Jadwal berhasil dihapus!";
            $message_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-calendar-alt"></i> Jadwal Kuliah</h1>
                <p>Manajemen jadwal perkuliahan dan pengaturan waktu</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Schedule Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Tambah Jadwal Baru</h2>
                <div class="form-container">
                    <form method="POST" class="schedule-form">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="mata_kuliah">Mata Kuliah</label>
                                <select id="mata_kuliah" name="mata_kuliah" class="form-control" required>
                                    <option value="">Pilih Mata Kuliah</option>
                                    <option value="PWL001">PWL001 - Pemrograman Web Lanjut</option>
                                    <option value="DBM001">DBM001 - Database Management</option>
                                    <option value="ASD001">ASD001 - Algoritma dan Struktur Data</option>
                                    <option value="SIM001">SIM001 - Sistem Informasi Manajemen</option>
                                    <option value="JK001">JK001 - Jaringan Komputer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dosen">Dosen Pengampu</label>
                                <select id="dosen" name="dosen" class="form-control" required>
                                    <option value="">Pilih Dosen</option>
                                    <option value="Dr. Ahmad Sudirman">Dr. Ahmad Sudirman</option>
                                    <option value="Prof. Siti Nurhaliza">Prof. Siti Nurhaliza</option>
                                    <option value="Dr. Budi Santoso">Dr. Budi Santoso</option>
                                    <option value="Dr. Maya Putri">Dr. Maya Putri</option>
                                    <option value="Prof. Rendi Pratama">Prof. Rendi Pratama</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="hari">Hari</label>
                                <select id="hari" name="hari" class="form-control" required>
                                    <option value="">Pilih Hari</option>
                                    <?php foreach ($hari_list as $hari): ?>
                                        <option value="<?php echo $hari; ?>"><?php echo $hari; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jam">Waktu</label>
                                <select id="jam" name="jam" class="form-control" required>
                                    <option value="">Pilih Waktu</option>
                                    <?php foreach ($jam_list as $jam): ?>
                                        <option value="<?php echo $jam; ?>"><?php echo $jam; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ruangan">Ruangan</label>
                                <select id="ruangan" name="ruangan" class="form-control" required>
                                    <option value="">Pilih Ruangan</option>
                                    <option value="Lab. Komputer 1">Lab. Komputer 1</option>
                                    <option value="Lab. Komputer 2">Lab. Komputer 2</option>
                                    <option value="Lab. Jaringan">Lab. Jaringan</option>
                                    <option value="Ruang 201">Ruang 201</option>
                                    <option value="Ruang 301">Ruang 301</option>
                                    <option value="Ruang 401">Ruang 401</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="kelas">Kelas</label>
                                <select id="kelas" name="kelas" class="form-control" required>
                                    <option value="">Pilih Kelas</option>
                                    <option value="TI-3A">TI-3A</option>
                                    <option value="TI-3B">TI-3B</option>
                                    <option value="TI-5A">TI-5A</option>
                                    <option value="TI-5B">TI-5B</option>
                                    <option value="SI-4A">SI-4A</option>
                                    <option value="SI-4B">SI-4B</option>
                                    <option value="SI-6A">SI-6A</option>
                                    <option value="TK-5B">TK-5B</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Jadwal
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Schedule Calendar View -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-week"></i> Jadwal Mingguan</h2>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="calendar-nav">
                            <button class="btn btn-outline" onclick="previousWeek()">
                                <i class="fas fa-chevron-left"></i> Minggu Sebelumnya
                            </button>
                            <h3>Minggu: 17 - 23 Juni 2024</h3>
                            <button class="btn btn-outline" onclick="nextWeek()">
                                Minggu Selanjutnya <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="calendar-grid">
                        <div class="time-column">
                            <div class="time-header">Waktu</div>
                            <div class="time-slot">08:00-10:00</div>
                            <div class="time-slot">10:00-12:00</div>
                            <div class="time-slot">13:00-15:00</div>
                            <div class="time-slot">15:00-17:00</div>
                        </div>
                        
                        <?php foreach ($hari_list as $hari): ?>
                            <div class="day-column">
                                <div class="day-header"><?php echo $hari; ?></div>
                                <?php 
                                for ($i = 0; $i < 4; $i++) {
                                    $found = false;
                                    foreach ($jadwal_data as $jadwal) {
                                        $jadwal_time = $jadwal['jam_mulai'] . '-' . $jadwal['jam_selesai'];
                                        if ($jadwal['hari'] === $hari && $jadwal_time === $jam_list[$i]) {
                                            echo '<div class="schedule-item">';
                                            echo '<div class="schedule-title">' . htmlspecialchars($jadwal['mata_kuliah']) . '</div>';
                                            echo '<div class="schedule-info">' . htmlspecialchars($jadwal['dosen']) . '</div>';
                                            echo '<div class="schedule-room">' . htmlspecialchars($jadwal['ruangan']) . '</div>';
                                            echo '<div class="schedule-class">' . htmlspecialchars($jadwal['kelas']) . '</div>';
                                            echo '</div>';
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        echo '<div class="empty-slot"></div>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Schedule List -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Jadwal</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Ruangan</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwal_data as $jadwal): ?>
                                    <tr>
                                        <td>
                                            <div class="course-info">
                                                <strong><?php echo htmlspecialchars($jadwal['mata_kuliah']); ?></strong>
                                                <small><?php echo htmlspecialchars($jadwal['kode']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($jadwal['dosen']); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal['jam_mulai'] . ' - ' . $jadwal['jam_selesai']); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($jadwal['kelas']); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewSchedule(<?php echo $jadwal['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editSchedule(<?php echo $jadwal['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $jadwal['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function viewSchedule(id) {
            alert('Lihat detail jadwal ID: ' + id);
        }

        function editSchedule(id) {
            alert('Edit jadwal ID: ' + id);
        }

        function deleteSchedule(id) {
            if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                alert('Hapus jadwal ID: ' + id);
            }
        }

        function resetForm() {
            document.querySelector('.schedule-form').reset();
        }

        function previousWeek() {
            alert('Navigasi ke minggu sebelumnya');
        }

        function nextWeek() {
            alert('Navigasi ke minggu selanjutnya');
        }
    </script>

    <style>
        /* Schedule page specific styles */
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.9rem;
            text-align: left;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
            height: 48px;
            line-height: 1.2;
        }

        /* Ensure all form controls have exact same dimensions */
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

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        /* Calendar Styles */
        .calendar-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .calendar-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calendar-nav h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: 120px repeat(6, 1fr);
            gap: 1px;
            background: #e5e7eb;
        }

        .time-column,
        .day-column {
            background: white;
        }

        .time-header,
        .day-header {
            background: #f8fafc;
            padding: 1rem;
            font-weight: 600;
            color: #374151;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .time-slot {
            padding: 1rem;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
            background: #f8fafc;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .schedule-item {
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: 0.25rem;
            border-radius: 6px;
            font-size: 0.8rem;
            height: 110px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .schedule-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 0.85rem;
        }

        .schedule-info,
        .schedule-room,
        .schedule-class {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-bottom: 0.2rem;
        }

        .empty-slot {
            height: 120px;
            background: #fafbfc;
            margin: 0.25rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .data-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }

        .course-info strong {
            display: block;
            color: #374151;
            font-size: 0.95rem;
        }

        .course-info small {
            color: #6b7280;
            font-size: 0.8rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .calendar-grid {
                grid-template-columns: 100px repeat(6, 1fr);
            }
            
            .calendar-nav {
                flex-direction: column;
                gap: 1rem;
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

            .form-container {
                padding: 1rem;
                margin: 0 0.5rem 1.5rem 0.5rem;
                border-radius: 8px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
                align-items: stretch;
            }

            .form-control {
                padding: 1rem;
                font-size: 1rem;
                border-radius: 10px;
                height: 52px;
                width: 100%;
                box-sizing: border-box;
            }

            .form-actions {
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

            .calendar-container {
                margin: 0 0.5rem 1.5rem 0.5rem;
                border-radius: 8px;
            }

            .calendar-grid {
                grid-template-columns: 80px repeat(3, 1fr);
                font-size: 0.8rem;
            }

            .schedule-item {
                font-size: 0.7rem;
                padding: 0.5rem;
                height: 100px;
            }

            .schedule-title {
                font-size: 0.75rem;
            }

            .schedule-info,
            .schedule-room,
            .schedule-class {
                font-size: 0.65rem;
            }

            .time-slot {
                height: 100px;
                font-size: 0.75rem;
                padding: 0.5rem;
            }

            .empty-slot {
                height: 100px;
            }

            .table-container {
                margin: 0 0.5rem;
                border-radius: 8px;
            }
            
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }

            .data-table {
                font-size: 0.85rem;
                min-width: 700px;
            }

            .data-table th,
            .data-table td {
                padding: 0.75rem 0.5rem;
                white-space: nowrap;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.5rem;
                min-width: 80px;
            }
            
            .btn-sm {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
                min-height: 40px;
                width: 100%;
                justify-content: center;
            }
            
            .alert {
                margin: 0 0.5rem 1.5rem 0.5rem;
                padding: 1rem;
                border-radius: 8px;
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

            .form-container {
                padding: 0.75rem;
                margin: 0 0.25rem 1rem 0.25rem;
            }

            .form-control {
                padding: 0.875rem;
                height: 48px;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }

            .calendar-container {
                margin: 0 0.25rem 1rem 0.25rem;
            }

            .calendar-grid {
                grid-template-columns: 70px repeat(2, 1fr);
            }

            .calendar-header {
                padding: 1rem;
            }

            .calendar-nav h3 {
                font-size: 1rem;
            }

            .btn-outline {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            .table-container {
                margin: 0 0.25rem;
            }

            .data-table {
                font-size: 0.8rem;
                min-width: 650px;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.625rem 0.375rem;
            }
            
            .btn-sm {
                padding: 0.625rem 0.75rem;
                font-size: 0.8rem;
                min-height: 36px;
            }
            
            .alert {
                margin: 0 0.25rem 1rem 0.25rem;
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Hide some columns on mobile for better readability */
        @media (max-width: 768px) {
            .calendar-grid .day-column:nth-child(n+6) {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .calendar-grid .day-column:nth-child(n+5) {
                display: none;
            }
        }

        /* Landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
            
            .form-actions {
                flex-direction: row;
                gap: 1rem;
            }

            .calendar-grid {
                grid-template-columns: 80px repeat(6, 1fr);
            }
            
            .calendar-grid .day-column {
                display: block;
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover,
            .btn-outline:hover {
                transform: none;
            }
            
            .btn:active,
            .btn-outline:active {
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
