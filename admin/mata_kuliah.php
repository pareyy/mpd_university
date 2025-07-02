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

// Get admin information (optional, for display purposes)
$user_id = $_SESSION['user_id'];

// Get all mata kuliah with related information
$mata_kuliah_query = "SELECT 
    mk.id,
    mk.kode_mk,
    mk.nama_mk,
    mk.sks,
    mk.semester,
    mk.program_studi_id,
    mk.dosen_id,
    ps.nama as program_studi_nama,
    d.nama as dosen_nama,
    (SELECT COUNT(*) FROM kelas k WHERE k.mata_kuliah_id = mk.id) as jumlah_mahasiswa
FROM mata_kuliah mk
LEFT JOIN program_studi ps ON mk.program_studi_id = ps.id
LEFT JOIN dosen d ON mk.dosen_id = d.id
ORDER BY ps.nama, mk.semester, mk.nama_mk";

$mk_result = mysqli_query($conn, $mata_kuliah_query);
$mata_kuliah_list = [];
while ($row = mysqli_fetch_assoc($mk_result)) {
    $mata_kuliah_list[] = $row;
}

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_mata_kuliah,
    SUM(mk.sks) as total_sks,
    COUNT(DISTINCT mk.program_studi_id) as total_prodi,
    COUNT(DISTINCT mk.dosen_id) as total_dosen
FROM mata_kuliah mk";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Handle form submissions (Add, Edit, Delete mata kuliah)
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_mata_kuliah'])) {
        $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
        $nama_mk = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $sks = (int)$_POST['sks'];
        $semester = (int)$_POST['semester'];
        $program_studi_id = (int)$_POST['program_studi_id'];
        $dosen_id = (int)$_POST['dosen_id'];
        
        $insert_query = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, program_studi_id, dosen_id) 
                        VALUES ('$kode_mk', '$nama_mk', $sks, $semester, $program_studi_id, $dosen_id)";
        
        if (mysqli_query($conn, $insert_query)) {
            $message = 'Mata kuliah berhasil ditambahkan!';
            $message_type = 'success';
            // Refresh data
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $message = 'Gagal menambahkan mata kuliah: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
    
    if (isset($_POST['edit_mata_kuliah'])) {
        $mk_id = (int)$_POST['mk_id'];
        $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
        $nama_mk = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $sks = (int)$_POST['sks'];
        $semester = (int)$_POST['semester'];
        $program_studi_id = (int)$_POST['program_studi_id'];
        $dosen_id = (int)$_POST['dosen_id'];
        
        $update_query = "UPDATE mata_kuliah SET 
                        kode_mk = '$kode_mk',
                        nama_mk = '$nama_mk',
                        sks = $sks,
                        semester = $semester,
                        program_studi_id = $program_studi_id,
                        dosen_id = $dosen_id
                        WHERE id = $mk_id";
        
        if (mysqli_query($conn, $update_query)) {
            $message = 'Mata kuliah berhasil diperbarui!';
            $message_type = 'success';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $message = 'Gagal memperbarui mata kuliah: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
    
    if (isset($_POST['delete_mata_kuliah'])) {
        $mk_id = (int)$_POST['mk_id'];
        
        // Check if there are students enrolled
        $check_query = "SELECT COUNT(*) as count FROM kelas WHERE mata_kuliah_id = $mk_id";
        $check_result = mysqli_query($conn, $check_query);
        $check_data = mysqli_fetch_assoc($check_result);
        
        if ($check_data['count'] > 0) {
            $message = 'Tidak dapat menghapus mata kuliah karena masih ada mahasiswa yang terdaftar!';
            $message_type = 'error';
        } else {
            $delete_query = "DELETE FROM mata_kuliah WHERE id = $mk_id";
            if (mysqli_query($conn, $delete_query)) {
                $message = 'Mata kuliah berhasil dihapus!';
                $message_type = 'success';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $message = 'Gagal menghapus mata kuliah: ' . mysqli_error($conn);
                $message_type = 'error';
            }
        }
    }
}

// Get program studi for dropdown
$prodi_query = "SELECT id, nama FROM program_studi ORDER BY nama";
$prodi_result = mysqli_query($conn, $prodi_query);
$program_studi_list = [];
while ($row = mysqli_fetch_assoc($prodi_result)) {
    $program_studi_list[] = $row;
}

// Get dosen for dropdown
$dosen_query = "SELECT id, nama FROM dosen ORDER BY nama";
$dosen_result = mysqli_query($conn, $dosen_query);
$dosen_list = [];
while ($row = mysqli_fetch_assoc($dosen_result)) {
    $dosen_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - Admin MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-book"></i> Manajemen Mata Kuliah</h1>
                <p>Kelola mata kuliah di MPD University</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fa-solid fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Mata Kuliah</h3>
                        <p class="stat-number"><?php echo $stats['total_mata_kuliah']; ?></p>
                        <small>Mata kuliah aktif</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total SKS</h3>
                        <p class="stat-number"><?php echo $stats['total_sks']; ?></p>
                        <small>SKS tersedia</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Program Studi</h3>
                        <p class="stat-number"><?php echo $stats['total_prodi']; ?></p>
                        <small>Program aktif</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Dosen Pengampu</h3>
                        <p class="stat-number"><?php echo $stats['total_dosen']; ?></p>
                        <small>Dosen aktif</small>
                    </div>
                </div>
            </div>

            <!-- Add New Course Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-plus-circle"></i> Tambah Mata Kuliah Baru</h2>
                    <button class="btn btn-primary" onclick="toggleAddForm()">
                        <i class="fas fa-plus"></i> Tambah Mata Kuliah
                    </button>
                </div>
                
                <div id="addCourseForm" class="form-container" style="display: none;">
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="kode_mk">Kode Mata Kuliah</label>
                                <input type="text" id="kode_mk" name="kode_mk" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nama_mk">Nama Mata Kuliah</label>
                                <input type="text" id="nama_mk" name="nama_mk" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="sks">SKS</label>
                                <select id="sks" name="sks" class="form-control" required>
                                    <option value="">Pilih SKS</option>
                                    <option value="1">1 SKS</option>
                                    <option value="2">2 SKS</option>
                                    <option value="3">3 SKS</option>
                                    <option value="4">4 SKS</option>
                                    <option value="6">6 SKS</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="semester">Semester</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <option value="">Pilih Semester</option>
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="program_studi_id">Program Studi</label>
                                <select id="program_studi_id" name="program_studi_id" class="form-control" required>
                                    <option value="">Pilih Program Studi</option>
                                    <?php foreach ($program_studi_list as $prodi): ?>
                                        <option value="<?php echo $prodi['id']; ?>"><?php echo $prodi['nama']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dosen_id">Dosen Pengampu</label>
                                <select id="dosen_id" name="dosen_id" class="form-control" required>
                                    <option value="">Pilih Dosen</option>
                                    <?php foreach ($dosen_list as $dosen): ?>
                                        <option value="<?php echo $dosen['id']; ?>"><?php echo $dosen['nama']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="toggleAddForm()">Batal</button>
                            <button type="submit" name="add_mata_kuliah" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan Mata Kuliah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mata Kuliah List -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-list"></i> Daftar Mata Kuliah</h2>
                    <div class="section-actions">
                        <input type="text" id="searchInput" placeholder="Cari mata kuliah..." class="search-input">
                        <select id="filterProdi" class="filter-select">
                            <option value="">Semua Program Studi</option>
                            <?php foreach ($program_studi_list as $prodi): ?>
                                <option value="<?php echo $prodi['nama']; ?>"><?php echo $prodi['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode MK</th>
                                <th>Nama Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Semester</th>
                                <th>Program Studi</th>
                                <th>Dosen</th>
                                <th>Mahasiswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="courseTableBody">
                            <?php foreach ($mata_kuliah_list as $mk): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $mk['kode_mk']; ?></span>
                                    </td>
                                    <td>
                                        <div class="course-name">
                                            <strong><?php echo $mk['nama_mk']; ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="sks-badge"><?php echo $mk['sks']; ?> SKS</span>
                                    </td>
                                    <td>
                                        <span class="semester-badge">Semester <?php echo $mk['semester']; ?></span>
                                    </td>
                                    <td><?php echo $mk['program_studi_nama']; ?></td>
                                    <td>
                                        <div class="dosen-info">
                                            <i class="fas fa-user"></i>
                                            <?php echo $mk['dosen_nama']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="student-count"><?php echo $mk['jumlah_mahasiswa']; ?> mahasiswa</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-info" onclick="viewCourse(<?php echo $mk['id']; ?>, '<?php echo addslashes($mk['kode_mk']); ?>', '<?php echo addslashes($mk['nama_mk']); ?>', <?php echo $mk['sks']; ?>, <?php echo $mk['semester']; ?>, '<?php echo addslashes($mk['program_studi_nama']); ?>', '<?php echo addslashes($mk['dosen_nama']); ?>', <?php echo $mk['jumlah_mahasiswa']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editCourse(<?php echo $mk['id']; ?>, '<?php echo addslashes($mk['kode_mk']); ?>', '<?php echo addslashes($mk['nama_mk']); ?>', <?php echo $mk['sks']; ?>, <?php echo $mk['semester']; ?>, <?php echo $mk['program_studi_id'] ?? 0; ?>, <?php echo $mk['dosen_id'] ?? 0; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete('<?php echo addslashes($mk['nama_mk']); ?>')">
                                                <input type="hidden" name="mk_id" value="<?php echo $mk['id']; ?>">
                                                <button type="submit" name="delete_mata_kuliah" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleAddForm() {
            const form = document.getElementById('addCourseForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function confirmDelete(courseName) {
            return confirm('Apakah Anda yakin ingin menghapus mata kuliah "' + courseName + '"?');
        }

        function viewCourse(id, kode_mk, nama_mk, sks, semester, program_studi, dosen_nama, jumlah_mahasiswa) {
            document.getElementById('view_kode_mk').textContent = kode_mk;
            document.getElementById('view_nama_mk').textContent = nama_mk;
            document.getElementById('view_sks').textContent = sks + ' SKS';
            document.getElementById('view_semester').textContent = 'Semester ' + semester;
            document.getElementById('view_program_studi').textContent = program_studi;
            document.getElementById('view_dosen').textContent = dosen_nama;
            document.getElementById('view_mahasiswa').textContent = jumlah_mahasiswa + ' mahasiswa';
            
            document.getElementById('viewModal').style.display = 'block';
        }

        function editCourse(id, kode_mk, nama_mk, sks, semester, program_studi_id, dosen_id) {
            document.getElementById('edit_mk_id').value = id;
            document.getElementById('edit_kode_mk').value = kode_mk;
            document.getElementById('edit_nama_mk').value = nama_mk;
            document.getElementById('edit_sks').value = sks;
            document.getElementById('edit_semester').value = semester;
            document.getElementById('edit_program_studi_id').value = program_studi_id;
            document.getElementById('edit_dosen_id').value = dosen_id;
            
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target == viewModal) {
                viewModal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }

        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('filterProdi').addEventListener('change', filterTable);

        function filterTable() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const prodiFilter = document.getElementById('filterProdi').value.toLowerCase();
            const tbody = document.getElementById('courseTableBody');
            const rows = tbody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const courseName = row.cells[1].textContent.toLowerCase();
                const courseCode = row.cells[0].textContent.toLowerCase();
                const prodi = row.cells[4].textContent.toLowerCase();

                const matchesSearch = courseName.includes(searchInput) || courseCode.includes(searchInput);
                const matchesProdi = prodiFilter === '' || prodi.includes(prodiFilter);

                if (matchesSearch && matchesProdi) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }

        // Auto-hide alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s ease-out';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        });
    </script>

    <!-- Modal View Mata Kuliah -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-eye"></i> Detail Mata Kuliah</h3>
                <span class="close" onclick="closeModal('viewModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Kode MK:</label>
                        <span id="view_kode_mk" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <label>Nama Mata Kuliah:</label>
                        <span id="view_nama_mk" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <label>SKS:</label>
                        <span id="view_sks" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <label>Semester:</label>
                        <span id="view_semester" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <label>Program Studi:</label>
                        <span id="view_program_studi" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <label>Dosen Pengampu:</label>
                        <span id="view_dosen" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <label>Jumlah Mahasiswa:</label>
                        <span id="view_mahasiswa" class="detail-value"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('viewModal')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Edit Mata Kuliah -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Mata Kuliah</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_mk_id" name="mk_id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_kode_mk">Kode MK *</label>
                            <input type="text" id="edit_kode_mk" name="kode_mk" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_nama_mk">Nama Mata Kuliah *</label>
                            <input type="text" id="edit_nama_mk" name="nama_mk" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_sks">SKS *</label>
                            <select id="edit_sks" name="sks" class="form-control" required>
                                <option value="">Pilih SKS</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="6">6</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_semester">Semester *</label>
                            <select id="edit_semester" name="semester" class="form-control" required>
                                <option value="">Pilih Semester</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_program_studi_id">Program Studi *</label>
                            <select id="edit_program_studi_id" name="program_studi_id" class="form-control" required>
                                <option value="">Pilih Program Studi</option>
                                <?php foreach ($program_studi_list as $prodi): ?>
                                    <option value="<?php echo $prodi['id']; ?>"><?php echo $prodi['nama']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_dosen_id">Dosen Pengampu *</label>
                            <select id="edit_dosen_id" name="dosen_id" class="form-control" required>
                                <option value="">Pilih Dosen</option>
                                <?php foreach ($dosen_list as $dosen): ?>
                                    <option value="<?php echo $dosen['id']; ?>"><?php echo $dosen['nama']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                    <button type="submit" name="edit_mata_kuliah" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        .modal-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            border-top: 1px solid #e5e7eb;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }

        .detail-item span {
            color: #6b7280;
            font-size: 0.95rem;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .modal-footer {
                flex-direction: column;
            }

            .modal-footer .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <?php include '../includes/footer.php'; ?>

</body>
</html>
