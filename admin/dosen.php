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

// Sample dosen data
$dosen_data = [
    ['id' => 1, 'nip' => '198501012010011001', 'nama' => 'Dr. Ahmad Rahman, M.Kom', 'email' => 'ahmad.rahman@univ.ac.id', 'bidang_keahlian' => 'Web Development', 'status' => 'Aktif'],
    ['id' => 2, 'nip' => '198203152009012002', 'nama' => 'Prof. Dr. Siti Nurhaliza, M.T', 'email' => 'siti.nurhaliza@univ.ac.id', 'bidang_keahlian' => 'Database Systems', 'status' => 'Aktif'],
    ['id' => 3, 'nip' => '197809102008011003', 'nama' => 'Dr. Budi Santoso, M.Sc', 'email' => 'budi.santoso@univ.ac.id', 'bidang_keahlian' => 'Artificial Intelligence', 'status' => 'Aktif'],
    ['id' => 4, 'nip' => '198712252012012004', 'nama' => 'Dr. Maya Putri, M.Kom', 'email' => 'maya.putri@univ.ac.id', 'bidang_keahlian' => 'Software Engineering', 'status' => 'Cuti'],
    ['id' => 5, 'nip' => '199001102015011005', 'nama' => 'Rendi Pratama, M.T', 'email' => 'rendi.pratama@univ.ac.id', 'bidang_keahlian' => 'Mobile Development', 'status' => 'Aktif']
];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $message = "Dosen berhasil ditambahkan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'edit') {
            $message = "Data dosen berhasil diperbarui!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'delete') {
            $message = "Dosen berhasil dihapus!";
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
    <title>Kelola Dosen - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-chalkboard-teacher"></i> Kelola Dosen</h1>
                <p>Manajemen data dosen dan tenaga pengajar</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Lecturer Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-user-plus"></i> Tambah Dosen Baru</h2>
                <div class="form-container">
                    <form method="POST" class="lecturer-form">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nip">NIP</label>
                                <input type="text" id="nip" name="nip" class="form-control" required>
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
                                <label for="bidang_keahlian">Bidang Keahlian</label>
                                <select id="bidang_keahlian" name="bidang_keahlian" class="form-control" required>
                                    <option value="">Pilih Bidang Keahlian</option>
                                    <option value="Web Development">Web Development</option>
                                    <option value="Mobile Development">Mobile Development</option>
                                    <option value="Database Systems">Database Systems</option>
                                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                                    <option value="Software Engineering">Software Engineering</option>
                                    <option value="Network Security">Network Security</option>
                                    <option value="Data Science">Data Science</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Cuti">Cuti</option>
                                    <option value="Non-Aktif">Non-Aktif</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Dosen
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lecturers List -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Dosen</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Bidang Keahlian</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dosen_data as $dosen): ?>
                                    <tr>
                                        <td><?php echo $dosen['nip']; ?></td>
                                        <td><?php echo $dosen['nama']; ?></td>
                                        <td><?php echo $dosen['email']; ?></td>
                                        <td><?php echo $dosen['bidang_keahlian']; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($dosen['status']); ?>">
                                                <?php echo $dosen['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewLecturer(<?php echo $dosen['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editLecturer(<?php echo $dosen['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteLecturer(<?php echo $dosen['id']; ?>)">
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
        function viewLecturer(id) {
            alert('Lihat detail dosen ID: ' + id);
        }

        function editLecturer(id) {
            alert('Edit dosen ID: ' + id);
        }

        function deleteLecturer(id) {
            if (confirm('Apakah Anda yakin ingin menghapus dosen ini?')) {
                alert('Hapus dosen ID: ' + id);
            }
        }

        function resetForm() {
            document.querySelector('.lecturer-form').reset();
        }
    </script>

    <style>
        /* Reuse same styles as mahasiswa page with some modifications */
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
            min-width: 800px;
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
                min-width: 750px;
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
                min-width: 700px;
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
                min-width: 650px;
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