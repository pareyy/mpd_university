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
            margin-bottom: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
            font-size: 0.9rem;
            text-align: left;
            display: block;
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

        textarea.form-control {
            height: auto !important;
            min-height: 80px;
            resize: vertical;
            padding: 0.875rem !important;
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
            
            .form-container {
                padding: 1.5rem;
            }
            
            .mata-kuliah-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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

            textarea.form-control {
                height: auto;
                min-height: 100px;
                padding: 1rem;
            }

            .btn {
                padding: 1rem;
                font-size: 1rem;
                min-height: 48px;
                border-radius: 10px;
                justify-content: center;
                touch-action: manipulation;
                margin-bottom: 0.5rem;
            }
            
            .mata-kuliah-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.5rem;
            }
            
            .mk-card {
                border-radius: 8px;
            }
            
            .mk-header {
                padding: 1rem;
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .mk-body {
                padding: 1rem;
            }
            
            .mk-details {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .mk-actions {
                flex-direction: column;
                gap: 0.5rem;
                padding: 1rem;
            }
            
            .btn-sm {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
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

            textarea.form-control {
                min-height: 80px;
                padding: 0.875rem;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }
            
            .mata-kuliah-grid {
                padding: 0 0.25rem;
            }
            
            .mk-header {
                padding: 0.75rem;
            }
            
            .mk-body {
                padding: 0.75rem;
            }
            
            .mk-body h3 {
                font-size: 1.1rem;
            }
            
            .mk-actions {
                padding: 0.75rem;
            }
            
            .alert {
                margin: 0 0.25rem 1rem 0.25rem;
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
            
            .mata-kuliah-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .mk-card:hover {
                transform: none;
            }
            
            .mk-card:active {
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
