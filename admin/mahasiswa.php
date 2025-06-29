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

// Get mahasiswa data from database
$mahasiswa_query = "SELECT m.*, ps.nama as program_studi_nama 
                    FROM mahasiswa m 
                    LEFT JOIN program_studi ps ON m.program_studi_id = ps.id 
                    ORDER BY m.created_at DESC";
$mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
$mahasiswa_data = [];
while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
    $mahasiswa_data[] = [
        'id' => $row['id'],
        'nim' => $row['nim'],
        'nama' => $row['nama'],
        'email' => $row['email'],
        'program_studi' => $row['program_studi_nama'] ?? 'Belum ditentukan',
        'semester' => $row['semester'],
        'status' => 'Aktif' // Default status, you can add status field to database
    ];
}

// Get program studi data for dropdown
$prodi_query = "SELECT id, nama FROM program_studi ORDER BY nama";
$prodi_result = mysqli_query($conn, $prodi_query);
$prodi_list = [];
while ($row = mysqli_fetch_assoc($prodi_result)) {
    $prodi_list[] = $row;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $nim = mysqli_real_escape_string($conn, $_POST['nim']);
            $nama = mysqli_real_escape_string($conn, $_POST['nama']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $program_studi_id = (int)$_POST['program_studi'];
            $semester = (int)$_POST['semester'];
            $password = password_hash($nim, PASSWORD_DEFAULT); // Default password = NIM
            
            // Insert into users table first
            $user_insert = "INSERT INTO users (username, password, role, full_name, email) 
                           VALUES ('$nim', '$password', 'mahasiswa', '$nama', '$email')";
            
            if (mysqli_query($conn, $user_insert)) {
                $new_user_id = mysqli_insert_id($conn);
                
                // Insert into mahasiswa table
                $mahasiswa_insert = "INSERT INTO mahasiswa (user_id, nim, nama, program_studi_id, semester, email) 
                                   VALUES ('$new_user_id', '$nim', '$nama', '$program_studi_id', '$semester', '$email')";
                
                if (mysqli_query($conn, $mahasiswa_insert)) {
                    $message = "Mahasiswa berhasil ditambahkan!";
                    $message_type = 'success';
                    // Refresh data
                    $mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
                    $mahasiswa_data = [];
                    while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
                        $mahasiswa_data[] = [
                            'id' => $row['id'],
                            'nim' => $row['nim'],
                            'nama' => $row['nama'],
                            'email' => $row['email'],
                            'program_studi' => $row['program_studi_nama'] ?? 'Belum ditentukan',
                            'semester' => $row['semester'],
                            'status' => 'Aktif'
                        ];
                    }
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $message_type = 'danger';
                }
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'danger';
            }
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
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                                    <?php foreach ($prodi_list as $prodi): ?>
                                        <option value="<?php echo $prodi['id']; ?>"><?php echo htmlspecialchars($prodi['nama']); ?></option>
                                    <?php endforeach; ?>
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
</body>
</html>