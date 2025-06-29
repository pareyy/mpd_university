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
</body>
</html>