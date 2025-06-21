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

// Sample mahasiswa data
$mahasiswa_data = [
    ['id' => 1, 'nim' => '2021001', 'nama' => 'Ahmad Rizki Pratama', 'email' => 'ahmad.rizki@email.com', 'program_studi' => 'Teknik Informatika', 'semester' => 5, 'status' => 'Aktif'],
    ['id' => 2, 'nim' => '2021002', 'nama' => 'Siti Aisyah Putri', 'email' => 'siti.aisyah@email.com', 'program_studi' => 'Sistem Informasi', 'semester' => 5, 'status' => 'Aktif'],
    ['id' => 3, 'nim' => '2021003', 'nama' => 'Budi Santoso', 'email' => 'budi.santoso@email.com', 'program_studi' => 'Teknik Informatika', 'semester' => 3, 'status' => 'Aktif'],
    ['id' => 4, 'nim' => '2020001', 'nama' => 'Dewi Lestari', 'email' => 'dewi.lestari@email.com', 'program_studi' => 'Sistem Informasi', 'semester' => 7, 'status' => 'Aktif'],
    ['id' => 5, 'nim' => '2019001', 'nama' => 'Rendi Pratama', 'email' => 'rendi.pratama@email.com', 'program_studi' => 'Teknik Informatika', 'semester' => 8, 'status' => 'Cuti']
];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $message = "Mahasiswa berhasil ditambahkan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'edit') {
            $message = "Data mahasiswa berhasil diperbarui!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'delete') {
            $message = "Mahasiswa berhasil dihapus!";
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
    <title>Kelola Mahasiswa - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-users"></i> Kelola Mahasiswa</h1>
                <p>Manajemen data mahasiswa dan informasi akademik</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Student Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-user-plus"></i> Tambah Mahasiswa Baru</h2>
                <div class="form-container">
                    <form method="POST" class="student-form">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nim">NIM</label>
                                <input type="text" id="nim" name="nim" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama Lengkap</label>
                                <input type="text" id="nama" name="nama" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="program_studi">Program Studi</label>
                                <select id="program_studi" name="program_studi" class="form-control" required>
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Teknik Komputer">Teknik Komputer</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="semester">Semester</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <option value="">Pilih Semester</option>
                                    <?php for($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="Non-Aktif">Non-Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Mahasiswa
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Students List -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Mahasiswa</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Program Studi</th>
                                    <th>Semester</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mahasiswa_data as $mhs): ?>
                                    <tr>
                                        <td><?php echo $mhs['nim']; ?></td>
                                        <td><?php echo $mhs['nama']; ?></td>
                                        <td><?php echo $mhs['email']; ?></td>
                                        <td><?php echo $mhs['program_studi']; ?></td>
                                        <td><?php echo $mhs['semester']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($mhs['status']); ?>">
                                                <?php echo $mhs['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewStudent(<?php echo $mhs['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editStudent(<?php echo $mhs['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $mhs['id']; ?>)">
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
        function viewStudent(id) {
            // Implementation for viewing student details
            alert('Lihat detail mahasiswa ID: ' + id);
        }

        function editStudent(id) {
            // Implementation for editing student
            alert('Edit mahasiswa ID: ' + id);
        }

        function deleteStudent(id) {
            if (confirm('Apakah Anda yakin ingin menghapus mahasiswa ini?')) {
                // Implementation for deleting student
                alert('Hapus mahasiswa ID: ' + id);
            }
        }

        function resetForm() {
            document.querySelector('.student-form').reset();
        }
    </script>

    <style>
        /* Add specific styles for mahasiswa page */
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }        .form-row {
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
        }        .form-control select {
            height: 48px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            background-color: white;
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

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .badge-aktif {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-cuti {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-non-aktif {
            background: #fee2e2;
            color: #991b1b;
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
        }        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 1.5rem;
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
            }            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
                align-items: stretch;
            }

            .form-group {
                width: 100%;
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
                width: 100%;
                box-sizing: border-box;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }

            .table-container {
                margin: 0 0.25rem;
                border-radius: 6px;
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
            
            .badge {
                padding: 0.2rem 0.5rem;
                font-size: 0.75rem;
            }
            
            .alert {
                margin: 0 0.25rem 1rem 0.25rem;
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .dashboard-header h1 {
                font-size: 1.1rem;
            }
            
            .data-table {
                min-width: 600px;
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
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover {
                transform: none;
            }
            
            .btn:active {
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