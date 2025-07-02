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

// Get dosen data from unified database
$dosen_query = "SELECT d.*, u.username, u.email, u.created_at, f.nama as fakultas_nama
                FROM dosen d
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN fakultas f ON d.fakultas_id = f.id
                ORDER BY d.created_at DESC";
$dosen_result = mysqli_query($conn, $dosen_query);
$dosen_data = [];
while ($row = mysqli_fetch_assoc($dosen_result)) {
    $dosen_data[] = [
        'id' => $row['id'],
        'nip' => $row['nidn'],
        'nama' => $row['nama'],
        'email' => $row['email'],
        'bidang_keahlian' => $row['bidang_keahlian'],
        'fakultas' => $row['fakultas_nama'],
        'status' => 'Aktif' // Default status
    ];
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $nip = mysqli_real_escape_string($conn, $_POST['nip']);
            $nama = mysqli_real_escape_string($conn, $_POST['nama']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $bidang_keahlian = mysqli_real_escape_string($conn, $_POST['bidang_keahlian']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Insert into users table first
            $user_insert = "INSERT INTO users (username, password, role, full_name, email) 
                           VALUES ('$nip', '$password', 'dosen', '$nama', '$email')";
            
            if (mysqli_query($conn, $user_insert)) {
                $new_user_id = mysqli_insert_id($conn);
                
                // Insert into dosen table
                $dosen_insert = "INSERT INTO dosen (user_id, nidn, nama, bidang_keahlian, email) 
                               VALUES ('$new_user_id', '$nip', '$nama', '$bidang_keahlian', '$email')";
                
                if (mysqli_query($conn, $dosen_insert)) {
                    $message = "Dosen berhasil ditambahkan!";
                    $message_type = 'success';
                    
                    // Refresh data after insert
                    $dosen_result = mysqli_query($conn, $dosen_query);
                    $dosen_data = [];
                    while ($row = mysqli_fetch_assoc($dosen_result)) {
                        $dosen_data[] = [
                            'id' => $row['id'],
                            'nip' => $row['nidn'],
                            'nama' => $row['nama'],
                            'email' => $row['email'],
                            'bidang_keahlian' => $row['bidang_keahlian'],
                            'fakultas' => $row['fakultas_nama'],
                            'status' => 'Aktif'
                        ];
                    }
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $message_type = 'error';
                }
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        }
        
        elseif ($_POST['action'] == 'edit') {
            $dosen_id = (int)$_POST['dosen_id'];
            $nip = mysqli_real_escape_string($conn, $_POST['nip']);
            $nama = mysqli_real_escape_string($conn, $_POST['nama']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $bidang_keahlian = mysqli_real_escape_string($conn, $_POST['bidang_keahlian']);
            
            // Update dosen table
            $update_dosen = "UPDATE dosen SET 
                            nidn = '$nip', 
                            nama = '$nama', 
                            email = '$email', 
                            bidang_keahlian = '$bidang_keahlian' 
                            WHERE id = $dosen_id";
            
            if (mysqli_query($conn, $update_dosen)) {
                // Update users table if user_id exists
                $get_user_id = "SELECT user_id FROM dosen WHERE id = $dosen_id";
                $user_result = mysqli_query($conn, $get_user_id);
                $user_data = mysqli_fetch_assoc($user_result);
                
                if ($user_data && $user_data['user_id']) {
                    $update_user = "UPDATE users SET 
                                   username = '$nip', 
                                   full_name = '$nama', 
                                   email = '$email' 
                                   WHERE id = " . $user_data['user_id'];
                    mysqli_query($conn, $update_user);
                }
                
                $message = "Data dosen berhasil diperbarui!";
                $message_type = 'success';
                
                // Refresh data
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        }
        
        elseif ($_POST['action'] == 'delete') {
            $dosen_id = (int)$_POST['dosen_id'];
            
            // Get user_id before deleting
            $get_user_id = "SELECT user_id FROM dosen WHERE id = $dosen_id";
            $user_result = mysqli_query($conn, $get_user_id);
            $user_data = mysqli_fetch_assoc($user_result);
            
            // Delete from dosen table
            $delete_dosen = "DELETE FROM dosen WHERE id = $dosen_id";
            
            if (mysqli_query($conn, $delete_dosen)) {
                // Delete from users table if user_id exists
                if ($user_data && $user_data['user_id']) {
                    $delete_user = "DELETE FROM users WHERE id = " . $user_data['user_id'];
                    mysqli_query($conn, $delete_user);
                }
                
                $message = "Dosen berhasil dihapus!";
                $message_type = 'success';
                
                // Refresh data
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
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
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                                <label for="nip">NIP/NIDN</label>
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
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <!-- Placeholder for consistency -->
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
                                    <th>NIP/NIDN</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Bidang Keahlian</th>
                                    <th>Fakultas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dosen_data as $dosen): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dosen['nip']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['email']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['bidang_keahlian']); ?></td>
                                        <td><?php echo htmlspecialchars($dosen['fakultas'] ?? 'Belum ditentukan'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewLecturer(<?php echo $dosen['id']; ?>, '<?php echo addslashes($dosen['nama']); ?>', '<?php echo addslashes($dosen['nip']); ?>', '<?php echo addslashes($dosen['email']); ?>', '<?php echo addslashes($dosen['bidang_keahlian']); ?>', '<?php echo addslashes($dosen['fakultas'] ?? 'Belum ditentukan'); ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editLecturer(<?php echo $dosen['id']; ?>, '<?php echo addslashes($dosen['nama']); ?>', '<?php echo addslashes($dosen['nip']); ?>', '<?php echo addslashes($dosen['email']); ?>', '<?php echo addslashes($dosen['bidang_keahlian']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete('<?php echo addslashes($dosen['nama']); ?>')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="dosen_id" value="<?php echo $dosen['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
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
        </div>
    </main>

    <!-- View Lecturer Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user"></i> Detail Dosen</h3>
                <span class="close" onclick="closeModal('viewModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>NIP/NIDN:</label>
                        <span id="viewNip"></span>
                    </div>
                    <div class="detail-item">
                        <label>Nama Lengkap:</label>
                        <span id="viewNama"></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span id="viewEmail"></span>
                    </div>
                    <div class="detail-item">
                        <label>Bidang Keahlian:</label>
                        <span id="viewBidang"></span>
                    </div>
                    <div class="detail-item">
                        <label>Fakultas:</label>
                        <span id="viewFakultas"></span>
                    </div>
                    <div class="detail-item">
                        <label>Status:</label>
                        <span class="badge badge-success">Aktif</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('viewModal')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Edit Lecturer Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Dosen</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="dosen_id" id="editDosenId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNip">NIP/NIDN</label>
                            <input type="text" id="editNip" name="nip" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editNama">Nama Lengkap</label>
                            <input type="text" id="editNama" name="nama" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editEmail">Email</label>
                            <input type="email" id="editEmail" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="editBidang">Bidang Keahlian</label>
                            <select id="editBidang" name="bidang_keahlian" class="form-control" required>
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
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button class="btn btn-primary" onclick="document.getElementById('editForm').submit()">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        function viewLecturer(id, nama, nip, email, bidang, fakultas) {
            document.getElementById('viewNip').textContent = nip;
            document.getElementById('viewNama').textContent = nama;
            document.getElementById('viewEmail').textContent = email;
            document.getElementById('viewBidang').textContent = bidang;
            document.getElementById('viewFakultas').textContent = fakultas;
            
            document.getElementById('viewModal').style.display = 'block';
        }

        function editLecturer(id, nama, nip, email, bidang) {
            document.getElementById('editDosenId').value = id;
            document.getElementById('editNip').value = nip;
            document.getElementById('editNama').value = nama;
            document.getElementById('editEmail').value = email;
            document.getElementById('editBidang').value = bidang;
            
            document.getElementById('editModal').style.display = 'block';
        }

        function confirmDelete(nama) {
            return confirm('Apakah Anda yakin ingin menghapus dosen "' + nama + '"?\n\nPerhatian: Tindakan ini tidak dapat dibatalkan!');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function resetForm() {
            document.querySelector('.lecturer-form').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === viewModal) {
                viewModal.style.display = 'none';
            }
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal('viewModal');
                closeModal('editModal');
            }
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>