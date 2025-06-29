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

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
            $nama_mk = mysqli_real_escape_string($conn, $_POST['nama_mk']);
            $sks = (int)$_POST['sks'];
            $semester = (int)$_POST['semester'];
            $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
            
            $insert_query = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, deskripsi, dosen_id) 
                           VALUES ('$kode_mk', '$nama_mk', $sks, $semester, '$deskripsi', $dosen_id)";
            
            if (mysqli_query($conn, $insert_query)) {
                $success_message = "Mata kuliah berhasil ditambahkan!";
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        } elseif ($_POST['action'] == 'edit' && isset($_POST['mk_id'])) {
            $mk_id = (int)$_POST['mk_id'];
            $kode_mk = mysqli_real_escape_string($conn, $_POST['kode_mk']);
            $nama_mk = mysqli_real_escape_string($conn, $_POST['nama_mk']);
            $sks = (int)$_POST['sks'];
            $semester = (int)$_POST['semester'];
            $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
            
            $update_query = "UPDATE mata_kuliah SET 
                           kode_mk = '$kode_mk', 
                           nama_mk = '$nama_mk', 
                           sks = $sks, 
                           semester = $semester, 
                           deskripsi = '$deskripsi',
                           updated_at = NOW()
                           WHERE id = $mk_id AND dosen_id = $dosen_id";
            
            if (mysqli_query($conn, $update_query)) {
                $success_message = "Mata kuliah berhasil diperbarui!";
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['mk_id'])) {
            $mk_id = (int)$_POST['mk_id'];
            
            $delete_query = "DELETE FROM mata_kuliah WHERE id = $mk_id AND dosen_id = $dosen_id";
            
            if (mysqli_query($conn, $delete_query)) {
                $success_message = "Mata kuliah berhasil dihapus!";
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}

// Get mata kuliah data for current dosen with additional info from jadwal
$mata_kuliah_query = "SELECT mk.*, 
                             COUNT(k.mahasiswa_id) as jumlah_mahasiswa,
                             j.ruang, j.hari, 
                             CONCAT(j.jam_mulai, '-', j.jam_selesai) as jam
                      FROM mata_kuliah mk 
                      LEFT JOIN kelas k ON mk.id = k.mata_kuliah_id
                      LEFT JOIN jadwal j ON mk.id = j.mata_kuliah_id
                      WHERE mk.dosen_id = $dosen_id 
                      GROUP BY mk.id
                      ORDER BY mk.created_at DESC";
$mata_kuliah_result = mysqli_query($conn, $mata_kuliah_query);
$mata_kuliah_list = [];
while ($row = mysqli_fetch_assoc($mata_kuliah_result)) {
    $mata_kuliah_list[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mata Kuliah - MPD University</title>
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
                <div class="form-container form-spacious">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row form-row-spacious">
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
                        
                        <div class="form-row form-row-spacious">
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
                        
                        <div class="form-group form-group-spacious">
                            <label for="deskripsi">Deskripsi Mata Kuliah</label>
                            <textarea id="deskripsi" name="deskripsi" class="form-control" rows="4" 
                                    placeholder="Masukkan deskripsi singkat mata kuliah..."></textarea>
                        </div>
                        
                        <div class="form-actions form-actions-spacious">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Mata Kuliah
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mata Kuliah Cards -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Mata Kuliah</h2>
                <div class="mata-kuliah-grid">
                    <?php foreach ($mata_kuliah_list as $mk): ?>
                        <div class="mk-card">
                            <div class="mk-header">
                                <div class="mk-code"><?php echo htmlspecialchars($mk['kode_mk']); ?></div>
                                <div class="mk-semester">Semester <?php echo $mk['semester']; ?></div>
                            </div>
                            <div class="mk-body">
                                <h3><?php echo htmlspecialchars($mk['nama_mk']); ?></h3>
                                <p class="mk-description"><?php echo htmlspecialchars(substr($mk['deskripsi'] ?? 'Tidak ada deskripsi', 0, 80)) . '...'; ?></p>
                                <div class="mk-details">
                                    <div class="detail-item">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span><?php echo $mk['sks']; ?> SKS</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $mk['jumlah_mahasiswa'] ?: 0; ?> Mahasiswa</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo htmlspecialchars(($mk['hari'] ? $mk['hari'] . ', ' : '') . ($mk['jam'] ?: 'Belum dijadwalkan')); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mk-actions">
                                <button class="btn btn-sm btn-warning" onclick="editMataKuliah(<?php echo $mk['id']; ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMataKuliah(<?php echo $mk['id']; ?>)" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        const mkData = <?php echo json_encode($mata_kuliah_list); ?>;

        function editMataKuliah(id) {
            const mk = mkData.find(item => item.id == id);
            
            if (mk) {
                // Fill form with existing data
                document.getElementById('kode_mk').value = mk.kode_mk;
                document.getElementById('nama_mk').value = mk.nama_mk;
                document.getElementById('sks').value = mk.sks;
                document.getElementById('semester').value = mk.semester;
                document.getElementById('deskripsi').value = mk.deskripsi || '';
                
                // Change form action to edit
                document.querySelector('input[name="action"]').value = 'edit';
                
                // Add or update hidden mk_id input
                let mkIdInput = document.querySelector('input[name="mk_id"]');
                if (!mkIdInput) {
                    mkIdInput = document.createElement('input');
                    mkIdInput.type = 'hidden';
                    mkIdInput.name = 'mk_id';
                    document.querySelector('form').appendChild(mkIdInput);
                }
                mkIdInput.value = id;
                
                // Change button text
                document.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save"></i> Update Mata Kuliah';
                
                // Scroll to form
                document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
            }
        }

        function deleteMataKuliah(id) {
            const mk = mkData.find(item => item.id == id);
            if (mk && confirm(`Apakah Anda yakin ingin menghapus mata kuliah "${mk.nama_mk}"?`)) {
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
</body>
</html>