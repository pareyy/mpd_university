<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Get mahasiswa information
$user_id = $_SESSION['user_id'];
$mahasiswa_query = "SELECT m.*, ps.nama as program_studi_nama, f.nama as fakultas_nama 
                    FROM mahasiswa m 
                    JOIN program_studi ps ON m.program_studi_id = ps.id 
                    JOIN fakultas f ON ps.fakultas_id = f.id 
                    WHERE m.user_id = '$user_id'";
$mahasiswa_result = mysqli_query($conn, $mahasiswa_query);
$mahasiswa = mysqli_fetch_assoc($mahasiswa_result);

if (!$mahasiswa) {
    echo "Data mahasiswa tidak ditemukan!";
    exit();
}

$current_semester = "Ganjil 2024/2025";

// Handle assignment submission
$submission_message = '';
$submission_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = $_POST['assignment_id'];
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Handle file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $upload_dir = '../uploads/submissions/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['submission_file']['name'];
        $file_tmp = $_FILES['submission_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Generate unique filename
        $new_filename = $mahasiswa['nim'] . '_' . $assignment_id . '_' . time() . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Update assignment submission in database
            $submit_query = "INSERT INTO tugas_submissions (tugas_id, mahasiswa_id, file_path, notes, submitted_at) 
                           VALUES ('$assignment_id', '{$mahasiswa['id']}', '$new_filename', '$notes', NOW())
                           ON DUPLICATE KEY UPDATE 
                           file_path = '$new_filename', notes = '$notes', submitted_at = NOW()";
            
            if (mysqli_query($conn, $submit_query)) {
                $submission_message = 'Tugas berhasil dikumpulkan!';
                $submission_type = 'success';
            } else {
                $submission_message = 'Gagal menyimpan data submission: ' . mysqli_error($conn);
                $submission_type = 'error';
            }
        } else {
            $submission_message = 'Gagal mengupload file!';
            $submission_type = 'error';
        }
    }
}

// Create tugas_submissions table if not exists
$create_submissions_table = "CREATE TABLE IF NOT EXISTS tugas_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    mahasiswa_id INT NOT NULL,
    file_path VARCHAR(255),
    notes TEXT,
    nilai DECIMAL(5,2) DEFAULT NULL,
    feedback TEXT,
    submitted_at DATETIME DEFAULT NOW(),
    graded_at DATETIME NULL,
    FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id),
    CONSTRAINT unique_submission UNIQUE (tugas_id, mahasiswa_id)
)";
mysqli_query($conn, $create_submissions_table);

// Create tugas table if not exists
$create_tugas_table = "CREATE TABLE IF NOT EXISTS tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mata_kuliah_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    jenis VARCHAR(50) DEFAULT 'Tugas Individu',
    deadline DATETIME NOT NULL,
    assigned_date DATETIME DEFAULT NOW(),
    file_tugas VARCHAR(255),
    max_file_size VARCHAR(10) DEFAULT '10MB',
    file_types VARCHAR(100) DEFAULT '.pdf,.doc,.docx,.zip',
    priority VARCHAR(20) DEFAULT 'medium',
    created_at DATETIME DEFAULT NOW(),
    FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id)
)";
mysqli_query($conn, $create_tugas_table);

// Insert sample tugas data if table is empty
$check_tugas = "SELECT COUNT(*) as count FROM tugas";
$check_result = mysqli_query($conn, $check_tugas);
$check_data = mysqli_fetch_assoc($check_result);

if ($check_data['count'] == 0) {
    $sample_tugas = "INSERT INTO tugas (mata_kuliah_id, judul, deskripsi, jenis, deadline, file_tugas, priority) VALUES
    (1, 'Implementasi Dashboard Admin dengan PHP', 'Membuat dashboard admin lengkap dengan fitur CRUD, autentikasi, dan laporan.', 'Tugas Individu', '2024-12-05 23:59:00', 'PWL_Tugas3_Dashboard.pdf', 'high'),
    (2, 'Normalisasi Database E-Commerce', 'Merancang dan menormalisasi database untuk sistem e-commerce dengan minimal 10 tabel.', 'Tugas Individu', '2024-12-01 23:59:00', 'DB_Tugas2_Normalisasi.pdf', 'medium'),
    (1, 'REST API dengan Authentication', 'Membuat REST API lengkap dengan sistem autentikasi JWT.', 'Tugas Individu', '2024-11-20 23:59:00', 'PWL_Tugas2_API.pdf', 'medium'),
    (3, 'Analisis Kebutuhan Sistem Informasi', 'Membuat dokumen analisis kebutuhan untuk sistem informasi akademik.', 'Tugas Kelompok', '2024-11-28 23:59:00', 'RPL_Tugas1_Analisis.pdf', 'high'),
    (5, 'Implementasi Algoritma Machine Learning', 'Mengimplementasikan algoritma klasifikasi menggunakan Python dan scikit-learn.', 'Tugas Individu', '2024-12-10 23:59:00', 'AI_Tugas4_ML.pdf', 'medium')";
    mysqli_query($conn, $sample_tugas);
}

// Get assignments for this student
$assignments_query = "SELECT 
    t.id,
    t.judul,
    t.deskripsi,
    t.jenis,
    t.deadline,
    t.assigned_date,
    t.file_tugas,
    t.max_file_size,
    t.file_types,
    t.priority,
    mk.nama_mk as mata_kuliah,
    mk.kode_mk,
    ts.file_path as submitted_file,
    ts.notes as submission_notes,
    ts.nilai,
    ts.feedback,
    ts.submitted_at,
    ts.graded_at,
    CASE 
        WHEN ts.submitted_at IS NOT NULL AND ts.graded_at IS NOT NULL THEN 'Selesai'
        WHEN ts.submitted_at IS NOT NULL THEN 'Menunggu Penilaian'
        WHEN NOW() > t.deadline THEN 'Terlambat'
        ELSE 'Belum Dikerjakan'
    END as status
FROM tugas t
JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
JOIN kelas k ON mk.id = k.mata_kuliah_id
LEFT JOIN tugas_submissions ts ON t.id = ts.tugas_id AND ts.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
ORDER BY t.deadline ASC";

$assignments_result = mysqli_query($conn, $assignments_query);
$assignments = [];

while ($row = mysqli_fetch_assoc($assignments_result)) {
    $assignments[] = [
        'id' => $row['id'],
        'mata_kuliah' => $row['mata_kuliah'],
        'kode_mk' => $row['kode_mk'],
        'judul' => $row['judul'],
        'deskripsi' => $row['deskripsi'],
        'jenis' => $row['jenis'],
        'deadline' => $row['deadline'],
        'assigned_date' => $row['assigned_date'],
        'status' => $row['status'],
        'nilai' => $row['nilai'],
        'file_tugas' => $row['file_tugas'],
        'submission_allowed' => $row['status'] != 'Selesai' && $row['status'] != 'Menunggu Penilaian',
        'max_file_size' => $row['max_file_size'],
        'file_types' => $row['file_types'],
        'priority' => $row['priority'],
        'submitted_file' => $row['submitted_file'],
        'submitted_at' => $row['submitted_at'],
        'feedback' => $row['feedback']
    ];
}

// Calculate assignment statistics
$assignment_stats = [
    'total_assignments' => count($assignments),
    'completed' => count(array_filter($assignments, function($a) { return $a['status'] == 'Selesai'; })),
    'pending' => count(array_filter($assignments, function($a) { return in_array($a['status'], ['Belum Dikerjakan', 'Menunggu Penilaian']); })),
    'overdue' => count(array_filter($assignments, function($a) { return $a['status'] == 'Terlambat'; }))
];

$assignment_stats['completion_rate'] = $assignment_stats['total_assignments'] > 0 ? 
    round(($assignment_stats['completed'] / $assignment_stats['total_assignments']) * 100, 1) : 0;

// Get assignment categories
$categories_query = "SELECT 
    t.jenis,
    COUNT(*) as count,
    COUNT(ts.id) as completed
FROM tugas t
JOIN mata_kuliah mk ON t.mata_kuliah_id = mk.id
JOIN kelas k ON mk.id = k.mata_kuliah_id
LEFT JOIN tugas_submissions ts ON t.id = ts.tugas_id AND ts.mahasiswa_id = k.mahasiswa_id AND ts.graded_at IS NOT NULL
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
GROUP BY t.jenis";

$categories_result = mysqli_query($conn, $categories_query);
$assignment_categories = [];

while ($row = mysqli_fetch_assoc($categories_result)) {
    $assignment_categories[] = [
        'name' => $row['jenis'],
        'count' => $row['count'],
        'completed' => $row['completed']
    ];
}

function getStatusBadge($status) {
    switch ($status) {
        case 'Selesai':
            return 'success';
        case 'Menunggu Penilaian':
            return 'info';
        case 'Belum Dikerjakan':
            return 'warning';
        case 'Terlambat':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getPriorityBadge($priority) {
    switch ($priority) {
        case 'high':
            return 'danger';
        case 'medium':
            return 'warning';
        case 'low':
            return 'success';
        default:
            return 'secondary';
    }
}

function timeUntilDeadline($deadline) {
    $now = new DateTime();
    $deadline_date = new DateTime($deadline);
    $interval = $now->diff($deadline_date);
    
    if ($deadline_date < $now) {
        return 'Terlambat';
    }
    
    if ($interval->days > 0) {
        return $interval->days . ' hari lagi';
    } elseif ($interval->h > 0) {
        return $interval->h . ' jam lagi';
    } else {
        return $interval->i . ' menit lagi';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas - Portal Mahasiswa MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/mahasiswa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <h1><i class="fa-solid fa-tasks"></i> Manajemen Tugas</h1>
            <div class="page-info">
                <span class="semester-info"><?php echo $current_semester; ?></span>
                <span class="student-info"><?php echo $mahasiswa['nim'] . " - " . $mahasiswa['nama']; ?></span>
            </div>
        </div>

        <?php if ($submission_message): ?>
            <div class="alert alert-<?php echo $submission_type; ?>">
                <i class="fa-solid fa-<?php echo $submission_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $submission_message; ?>
            </div>
        <?php endif; ?>

        <!-- Assignment Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['total_assignments']; ?></h3>
                    <p>Total Tugas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['completed']; ?></h3>
                    <p>Selesai</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['pending']; ?></h3>
                    <p>Dalam Proses</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['overdue']; ?></h3>
                    <p>Terlambat</p>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-filter"></i> Filter Tugas</h2>
            </div>
            <div class="quick-actions">
                <div class="action-card">
                    <i class="fa-solid fa-list-check" style="color: #667eea;"></i>
                    <label for="statusFilter" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Status:</label>
                    <select id="statusFilter" class="form-select" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 2px solid #e5e7eb;">
                        <option value="">Semua Status</option>
                        <option value="Belum Dikerjakan">Belum Dikerjakan</option>
                        <option value="Menunggu Penilaian">Menunggu Penilaian</option>
                        <option value="Selesai">Selesai</option>
                        <option value="Terlambat">Terlambat</option>
                    </select>
                </div>
                <div class="action-card">
                    <i class="fa-solid fa-book" style="color: #10b981;"></i>
                    <label for="subjectFilter" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Mata Kuliah:</label>
                    <select id="subjectFilter" class="form-select" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 2px solid #e5e7eb;">
                        <option value="">Semua Mata Kuliah</option>
                        <?php
                        $subjects = array_unique(array_column($assignments, 'kode_mk'));
                        foreach ($subjects as $subject):
                        ?>
                            <option value="<?php echo $subject; ?>"><?php echo $subject; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="action-card">
                    <i class="fa-solid fa-tags" style="color: #f59e0b;"></i>
                    <label for="typeFilter" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Jenis:</label>
                    <select id="typeFilter" class="form-select" style="width: 100%; padding: 0.5rem; border-radius: 8px; border: 2px solid #e5e7eb;">
                        <option value="">Semua Jenis</option>
                        <option value="Tugas Individu">Tugas Individu</option>
                        <option value="Tugas Kelompok">Tugas Kelompok</option>
                        <option value="Proyek Akhir">Proyek Akhir</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Assignments List -->
        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-list"></i> Daftar Tugas</h2>
            </div>
            <?php if (!empty($assignments)): ?>
                <div class="assignments-grid">
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="assignment-card" data-status="<?php echo $assignment['status']; ?>" data-subject="<?php echo $assignment['kode_mk']; ?>" data-type="<?php echo $assignment['jenis']; ?>">
                            <div class="assignment-header">
                                <div class="assignment-title">
                                    <h3><?php echo $assignment['judul']; ?></h3>
                                    <div class="assignment-meta">
                                        <span class="subject-badge"><?php echo $assignment['kode_mk']; ?></span>
                                        <span class="type-badge"><?php echo $assignment['jenis']; ?></span>
                                        <span class="priority-badge <?php echo getPriorityBadge($assignment['priority']); ?>">
                                            <?php echo ucfirst($assignment['priority']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="assignment-status">
                                    <span class="badge <?php echo getStatusBadge($assignment['status']); ?>">
                                        <?php echo $assignment['status']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="assignment-body">
                                <p class="assignment-description"><?php echo $assignment['deskripsi']; ?></p>
                                
                                <div class="assignment-details">
                                    <div class="detail-row">
                                        <i class="fa-solid fa-calendar"></i>
                                        <span>Deadline: <?php echo date('d M Y, H:i', strtotime($assignment['deadline'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <i class="fa-solid fa-clock"></i>
                                        <span class="deadline-countdown"><?php echo timeUntilDeadline($assignment['deadline']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <i class="fa-solid fa-file"></i>
                                        <span>Format: <?php echo $assignment['file_types']; ?> (Max: <?php echo $assignment['max_file_size']; ?>)</span>
                                    </div>
                                    <?php if ($assignment['submitted_at']): ?>
                                        <div class="detail-row">
                                            <i class="fa-solid fa-upload"></i>
                                            <span>Dikumpulkan: <?php echo date('d M Y, H:i', strtotime($assignment['submitted_at'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($assignment['status'] == 'Selesai' && isset($assignment['nilai'])): ?>
                                    <div class="grade-section">
                                        <div class="grade-display">
                                            <i class="fa-solid fa-star"></i>
                                            Nilai: <strong><?php echo $assignment['nilai']; ?></strong>
                                        </div>
                                        <?php if ($assignment['feedback']): ?>
                                            <div class="feedback-section">
                                                <strong>Feedback:</strong>
                                                <p><?php echo $assignment['feedback']; ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="assignment-actions">
                                <a href="#" class="btn btn-outline" onclick="viewAssignment(<?php echo $assignment['id']; ?>)">
                                    <i class="fa-solid fa-eye"></i> Lihat Detail
                                </a>
                                <?php if ($assignment['file_tugas']): ?>
                                    <a href="<?php echo '../uploads/assignments/' . $assignment['file_tugas']; ?>" class="btn btn-primary" target="_blank">
                                        <i class="fa-solid fa-download"></i> Download Soal
                                    </a>
                                <?php endif; ?>
                                <?php if ($assignment['submission_allowed']): ?>
                                    <button class="btn btn-success" onclick="submitAssignment(<?php echo $assignment['id']; ?>)">
                                        <i class="fa-solid fa-upload"></i> Upload Jawaban
                                    </button>
                                <?php elseif ($assignment['status'] == 'Selesai' || $assignment['status'] == 'Menunggu Penilaian'): ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fa-solid fa-check"></i> Sudah Dikumpulkan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <div class="no-content-icon">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <h3>Belum Ada Tugas</h3>
                    <p>Tidak ada tugas yang tersedia untuk mata kuliah yang Anda ambil saat ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Assignment Categories Summary -->
        <?php if (!empty($assignment_categories)): ?>
        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-chart-bar"></i> Ringkasan Kategori Tugas</h2>
            </div>
            <div class="categories-grid">
                <?php foreach ($assignment_categories as $category): ?>
                    <div class="category-card">
                        <div class="category-header">
                            <h4><?php echo $category['name']; ?></h4>
                            <span class="category-count"><?php echo $category['completed']; ?>/<?php echo $category['count']; ?></span>
                        </div>
                        <div class="category-progress">
                            <div class="progress-bar">
                                <div class="progress-fill primary" style="width: <?php echo $category['count'] > 0 ? ($category['completed'] / $category['count']) * 100 : 0; ?>%"></div>
                            </div>
                            <span class="progress-percentage"><?php echo $category['count'] > 0 ? round(($category['completed'] / $category['count']) * 100) : 0; ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Assignment Submission Modal -->
    <div id="submissionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-upload"></i> Upload Jawaban Tugas</h3>
                <span class="close" onclick="closeSubmissionModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="submissionForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="assignmentId" name="assignment_id">
                    <div class="form-group">
                        <label for="submissionFile">File Jawaban:</label>
                        <input type="file" id="submissionFile" name="submission_file" class="form-control" required>
                        <small class="form-text">Format yang diizinkan: .zip, .rar, .pdf, .docx (Max: 20MB)</small>
                    </div>
                    <div class="form-group">
                        <label for="submissionNotes">Catatan (Opsional):</label>
                        <textarea id="submissionNotes" name="notes" rows="3" class="form-control" placeholder="Tambahkan catatan untuk dosen..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeSubmissionModal()">Batal</button>
                        <button type="submit" name="submit_assignment" class="btn btn-success">
                            <i class="fa-solid fa-upload"></i> Upload Jawaban
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Assignment submission modal
        function submitAssignment(assignmentId) {
            document.getElementById('assignmentId').value = assignmentId;
            document.getElementById('submissionModal').style.display = 'block';
        }

        function closeSubmissionModal() {
            document.getElementById('submissionModal').style.display = 'none';
            document.getElementById('submissionForm').reset();
        }

        function viewAssignment(assignmentId) {
            alert('Viewing assignment details for ID: ' + assignmentId);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('submissionModal');
            if (event.target === modal) {
                closeSubmissionModal();
            }
        }

        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const subjectFilter = document.getElementById('subjectFilter');
            const typeFilter = document.getElementById('typeFilter');
            const assignmentCards = document.querySelectorAll('.assignment-card');

            function filterAssignments() {
                const statusValue = statusFilter.value;
                const subjectValue = subjectFilter.value;
                const typeValue = typeFilter.value;

                assignmentCards.forEach(card => {
                    const cardStatus = card.getAttribute('data-status');
                    const cardSubject = card.getAttribute('data-subject');
                    const cardType = card.getAttribute('data-type');

                    const statusMatch = !statusValue || cardStatus === statusValue;
                    const subjectMatch = !subjectValue || cardSubject === subjectValue;
                    const typeMatch = !typeValue || cardType === typeValue;

                    if (statusMatch && subjectMatch && typeMatch) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            statusFilter.addEventListener('change', filterAssignments);
            subjectFilter.addEventListener('change', filterAssignments);
            typeFilter.addEventListener('change', filterAssignments);
        });
    </script>

</body>
</html>
