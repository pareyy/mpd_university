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

// Sample data for mata kuliah - replace with actual database queries
$mata_kuliah_list = [
    [
        'id' => 1,
        'kode_mk' => 'PWL001',
        'nama_mk' => 'Pemrograman Web Lanjut',
        'sks' => 3,
        'semester' => 5,
        'deskripsi' => 'Mata kuliah ini membahas pengembangan aplikasi web menggunakan framework modern seperti Laravel, CodeIgniter, dan teknologi web terkini.',
        'jumlah_mahasiswa' => 28,
        'ruangan' => 'Lab Komputer 1',
        'hari' => 'Senin',
        'jam' => '08:00-10:30'
    ],
    [
        'id' => 2,
        'kode_mk' => 'DB001',
        'nama_mk' => 'Basis Data Lanjut',
        'sks' => 3,
        'semester' => 4,
        'deskripsi' => 'Mata kuliah yang membahas konsep lanjutan basis data termasuk optimasi query, stored procedure, trigger, dan database administration.',
        'jumlah_mahasiswa' => 32,
        'ruangan' => 'Lab Database',
        'hari' => 'Rabu',
        'jam' => '10:30-13:00'
    ],
    [
        'id' => 3,
        'kode_mk' => 'RPL001',
        'nama_mk' => 'Rekayasa Perangkat Lunak',
        'sks' => 3,
        'semester' => 5,
        'deskripsi' => 'Mata kuliah yang membahas metodologi pengembangan perangkat lunak, SDLC, UML, dan manajemen proyek software.',
        'jumlah_mahasiswa' => 25,
        'ruangan' => 'Ruang Kelas A',
        'hari' => 'Jumat',
        'jam' => '13:00-15:30'
    ],
    [
        'id' => 4,
        'kode_mk' => 'AI001',
        'nama_mk' => 'Artificial Intelligence',
        'sks' => 3,
        'semester' => 6,
        'deskripsi' => 'Mata kuliah yang membahas konsep dasar AI, machine learning, neural networks, dan implementasi algoritma AI.',
        'jumlah_mahasiswa' => 20,
        'ruangan' => 'Lab AI',
        'hari' => 'Selasa',
        'jam' => '08:00-10:30'
    ]
];

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // Simulate adding new mata kuliah
            $success_message = "Mata kuliah berhasil ditambahkan!";
        } elseif ($_POST['action'] == 'edit') {
            // Simulate editing mata kuliah
            $success_message = "Mata kuliah berhasil diperbarui!";
        } elseif ($_POST['action'] == 'delete') {
            // Simulate deleting mata kuliah
            $success_message = "Mata kuliah berhasil dihapus!";
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
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_dosen.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-book"></i> Kelola Mata Kuliah</h1>
                <p>Kelola mata kuliah yang Anda ampu</p>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Add New Mata Kuliah Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus"></i> Tambah Mata Kuliah Baru</h2>
                <div class="form-container">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="kode_mk">Kode Mata Kuliah</label>
                                <input type="text" id="kode_mk" name="kode_mk" class="form-control" required 
                                       placeholder="Contoh: PWL001">
                            </div>
                            
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
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nama_mk">Nama Mata Kuliah</label>
                                <input type="text" id="nama_mk" name="nama_mk" class="form-control" required 
                                       placeholder="Contoh: Pemrograman Web Lanjut">
                            </div>
                            
                            <div class="form-group">
                                <label for="semester">Semester</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <option value="">Pilih Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                    <option value="3">Semester 3</option>
                                    <option value="4">Semester 4</option>
                                    <option value="5">Semester 5</option>
                                    <option value="6">Semester 6</option>
                                    <option value="7">Semester 7</option>
                                    <option value="8">Semester 8</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Mata Kuliah</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4" 
                                    placeholder="Masukkan deskripsi singkat mata kuliah..."></textarea>
                        </div>
                          <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Mata Kuliah
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Reset Form
                        </button>
                    </form>
                </div>
            </div>            <!-- Mata Kuliah Cards -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Mata Kuliah</h2>
                <div class="mata-kuliah-grid">
                    <?php foreach ($mata_kuliah_list as $mk): ?>
                        <div class="mk-card">
                            <div class="mk-header">
                                <div class="mk-code"><?php echo $mk['kode_mk']; ?></div>
                                <div class="mk-semester">Semester <?php echo $mk['semester']; ?></div>
                            </div>
                            <div class="mk-body">
                                <h3><?php echo $mk['nama_mk']; ?></h3>
                                <p class="mk-description"><?php echo substr($mk['deskripsi'], 0, 120) . '...'; ?></p>
                                <div class="mk-details">
                                    <div class="detail-item">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span><?php echo $mk['sks']; ?> SKS</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $mk['jumlah_mahasiswa']; ?> Mahasiswa</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo $mk['ruangan']; ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $mk['hari'] . ', ' . $mk['jam']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mk-actions">
                                <button class="btn btn-sm btn-primary" onclick="viewDetails(<?php echo $mk['id']; ?>)">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editMataKuliah(<?php echo $mk['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMataKuliah(<?php echo $mk['id']; ?>)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>    <script>
        function editMataKuliah(id) {
            // Get mata kuliah data
            const mkData = <?php echo json_encode($mata_kuliah_list); ?>;
            const mk = mkData.find(item => item.id === id);
            
            if (mk) {
                // Fill form with existing data
                document.getElementById('kode_mk').value = mk.kode_mk;
                document.getElementById('nama_mk').value = mk.nama_mk;
                document.getElementById('sks').value = mk.sks;
                document.getElementById('semester').value = mk.semester;
                document.getElementById('deskripsi').value = mk.deskripsi;
                
                // Change form action to edit
                document.querySelector('input[name="action"]').value = 'edit';
                document.querySelector('input[name="mk_id"]') || (() => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'mk_id';
                    hiddenInput.value = id;
                    document.querySelector('form').appendChild(hiddenInput);
                })();
                
                // Change button text
                document.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save"></i> Update Mata Kuliah';
                
                // Scroll to form
                document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
            }
        }

        function deleteMataKuliah(id) {
            if (confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?')) {
                // Create a form to submit delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="mk_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewDetails(id) {
            const mkData = <?php echo json_encode($mata_kuliah_list); ?>;
            const mk = mkData.find(item => item.id === id);
            
            if (mk) {
                alert(`Detail Mata Kuliah:\n\n` +
                     `Kode: ${mk.kode_mk}\n` +
                     `Nama: ${mk.nama_mk}\n` +
                     `SKS: ${mk.sks}\n` +
                     `Semester: ${mk.semester}\n` +
                     `Mahasiswa: ${mk.jumlah_mahasiswa}\n` +
                     `Ruangan: ${mk.ruangan}\n` +
                     `Jadwal: ${mk.hari}, ${mk.jam}\n\n` +
                     `Deskripsi: ${mk.deskripsi}`);
            }
        }

        function resetForm() {
            document.querySelector('form').reset();
            document.querySelector('input[name="action"]').value = 'add';
            document.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-plus"></i> Tambah Mata Kuliah';
            
            // Remove edit id if exists
            const editId = document.querySelector('input[name="mk_id"]');
            if (editId) {
                editId.remove();
            }
        }
    </script>

    <style>
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 600;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
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
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }        /* Mata Kuliah Cards */
        .mata-kuliah-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .mk-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .mk-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .mk-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mk-code {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .mk-semester {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .mk-body {
            padding: 1.5rem;
        }

        .mk-body h3 {
            margin: 0 0 1rem 0;
            color: #1f2937;
            font-size: 1.25rem;
        }

        .mk-description {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .mk-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #374151;
            font-size: 0.875rem;
        }

        .detail-item i {
            color: #6b7280;
            width: 16px;
        }

        .mk-actions {
            display: flex;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }

        .alert {
            padding: 1rem;
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

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                font-size: 0.875rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
