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

// Sample mata kuliah data
$mata_kuliah_data = [
    ['id' => 1, 'kode' => 'PWL001', 'nama' => 'Pemrograman Web Lanjut', 'sks' => 3, 'semester' => 5, 'program_studi' => 'Teknik Informatika', 'dosen' => 'Dr. Ahmad Rahman, M.Kom'],
    ['id' => 2, 'kode' => 'DB001', 'nama' => 'Basis Data Lanjut', 'sks' => 3, 'semester' => 4, 'program_studi' => 'Sistem Informasi', 'dosen' => 'Prof. Dr. Siti Nurhaliza, M.T'],
    ['id' => 3, 'kode' => 'AI001', 'nama' => 'Artificial Intelligence', 'sks' => 3, 'semester' => 6, 'program_studi' => 'Teknik Informatika', 'dosen' => 'Dr. Budi Santoso, M.Sc'],
    ['id' => 4, 'kode' => 'RPL001', 'nama' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'semester' => 5, 'program_studi' => 'Teknik Informatika', 'dosen' => 'Dr. Maya Putri, M.Kom'],
    ['id' => 5, 'kode' => 'MD001', 'nama' => 'Pemrograman Mobile', 'sks' => 3, 'semester' => 6, 'program_studi' => 'Teknik Informatika', 'dosen' => 'Rendi Pratama, M.T']
];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $message = "Mata kuliah berhasil ditambahkan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'edit') {
            $message = "Data mata kuliah berhasil diperbarui!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'delete') {
            $message = "Mata kuliah berhasil dihapus!";
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
    <title>Kelola Mata Kuliah - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-book"></i> Kelola Mata Kuliah</h1>
                <p>Manajemen mata kuliah dan kurikulum akademik</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Course Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Tambah Mata Kuliah Baru</h2>
                <div class="form-container">
                    <form method="POST" class="course-form">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kode">Kode Mata Kuliah</label>
                                <input type="text" id="kode" name="kode" class="form-control" required placeholder="Contoh: PWL001">
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama Mata Kuliah</label>
                                <input type="text" id="nama" name="nama" class="form-control" required placeholder="Nama mata kuliah">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="sks">SKS</label>
                                <select id="sks" name="sks" class="form-control" required>
                                    <option value="">Pilih SKS</option>
                                    <option value="1">1 SKS</option>
                                    <option value="2">2 SKS</option>
                                    <option value="3">3 SKS</option>
                                    <option value="4">4 SKS</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semester">Semester</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <option value="">Pilih Semester</option>
                                    <?php for($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="program_studi">Program Studi</label>
                                <select id="program_studi" name="program_studi" class="form-control" required>
                                    <option value="">Pilih Program Studi</option>
                                    <option value="Teknik Informatika">Teknik Informatika</option>
                                    <option value="Sistem Informasi">Sistem Informasi</option>
                                    <option value="Teknik Komputer">Teknik Komputer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dosen">Dosen Pengampu</label>
                                <select id="dosen" name="dosen" class="form-control" required>
                                    <option value="">Pilih Dosen</option>
                                    <option value="Dr. Ahmad Rahman, M.Kom">Dr. Ahmad Rahman, M.Kom</option>
                                    <option value="Prof. Dr. Siti Nurhaliza, M.T">Prof. Dr. Siti Nurhaliza, M.T</option>
                                    <option value="Dr. Budi Santoso, M.Sc">Dr. Budi Santoso, M.Sc</option>
                                    <option value="Dr. Maya Putri, M.Kom">Dr. Maya Putri, M.Kom</option>
                                    <option value="Rendi Pratama, M.T">Rendi Pratama, M.T</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="deskripsi">Deskripsi</label>
                                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi mata kuliah..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Mata Kuliah
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Courses List -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Mata Kuliah</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Semester</th>
                                    <th>Program Studi</th>
                                    <th>Dosen</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mata_kuliah_data as $mk): ?>
                                    <tr>
                                        <td><strong><?php echo $mk['kode']; ?></strong></td>
                                        <td><?php echo $mk['nama']; ?></td>
                                        <td>
                                            <span class="sks-badge"><?php echo $mk['sks']; ?> SKS</span>
                                        </td>
                                        <td><?php echo $mk['semester']; ?></td>
                                        <td><?php echo $mk['program_studi']; ?></td>
                                        <td><?php echo $mk['dosen']; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewCourse(<?php echo $mk['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editCourse(<?php echo $mk['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteCourse(<?php echo $mk['id']; ?>)">
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
        function viewCourse(id) {
            alert('Lihat detail mata kuliah ID: ' + id);
        }

        function editCourse(id) {
            alert('Edit mata kuliah ID: ' + id);
        }

        function deleteCourse(id) {
            if (confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')) {
                alert('Hapus mata kuliah ID: ' + id);
            }
        }

        function resetForm() {
            document.querySelector('.course-form').reset();
        }
    </script>

    <style>
        /* Course management specific styles */
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
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
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
            min-width: 900px;
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

        .sks-badge {
            background: #e0e7ff;
            color: #3730a3;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
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
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .form-control {
                padding: 1rem;
                font-size: 1rem;
                border-radius: 10px;
            }

            textarea.form-control {
                min-height: 100px;
                padding: 1rem;
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
                min-width: 800px;
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
            
            .sks-badge {
                padding: 0.2rem 0.6rem;
                font-size: 0.8rem;
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
            }
            
            textarea.form-control {
                min-height: 80px;
                padding: 0.875rem;
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
                min-width: 750px;
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
            
            .sks-badge {
                padding: 0.15rem 0.5rem;
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
                min-width: 700px;
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