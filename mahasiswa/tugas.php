<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit();
}

// Sample data - replace with actual database queries
$current_semester = "Ganjil 2024/2025";
$student_nim = "2021080001";
$student_name = "Ahmad Fadhil Rahman";

// Sample assignment statistics
$assignment_stats = [
    'total_assignments' => 12,
    'completed' => 8,
    'pending' => 3,
    'overdue' => 1,
    'completion_rate' => 66.7
];

// Sample assignment categories
$assignment_categories = [
    [
        'name' => 'Tugas Individu',
        'count' => 7,
        'completed' => 5
    ],
    [
        'name' => 'Tugas Kelompok',
        'count' => 3,
        'completed' => 2
    ],
    [
        'name' => 'Proyek Akhir',
        'count' => 2,
        'completed' => 1
    ]
];

// Sample assignments data
$assignments = [
    [
        'id' => 1,
        'mata_kuliah' => 'Pemrograman Web Lanjut',
        'kode_mk' => 'PWL001',
        'judul' => 'Implementasi Dashboard Admin dengan PHP',
        'deskripsi' => 'Membuat dashboard admin lengkap dengan fitur CRUD, autentikasi, dan laporan.',
        'jenis' => 'Tugas Individu',
        'deadline' => '2024-12-05 23:59:00',
        'assigned_date' => '2024-11-20 08:00:00',
        'status' => 'Belum Dikerjakan',
        'nilai' => null,
        'file_tugas' => 'PWL_Tugas3_Dashboard.pdf',
        'submission_allowed' => true,
        'max_file_size' => '10MB',
        'file_types' => '.zip, .rar, .pdf',
        'priority' => 'high'
    ],
    [
        'id' => 2,
        'mata_kuliah' => 'Basis Data',
        'kode_mk' => 'DB001',
        'judul' => 'Normalisasi Database E-Commerce',
        'deskripsi' => 'Merancang dan menormalisasi database untuk sistem e-commerce dengan minimal 10 tabel.',
        'jenis' => 'Tugas Individu',
        'deadline' => '2024-12-01 23:59:00',
        'assigned_date' => '2024-11-15 10:30:00',
        'status' => 'Sedang Dikerjakan',
        'nilai' => null,
        'file_tugas' => 'DB_Tugas2_Normalisasi.pdf',
        'submission_allowed' => true,
        'max_file_size' => '5MB',
        'file_types' => '.pdf, .docx',
        'priority' => 'medium',
        'progress' => 60
    ],
    [
        'id' => 3,
        'mata_kuliah' => 'Rekayasa Perangkat Lunak',
        'kode_mk' => 'RPL001',
        'judul' => 'Analisis Kebutuhan Sistem Informasi',
        'deskripsi' => 'Membuat dokumen analisis kebutuhan untuk sistem informasi akademik.',
        'jenis' => 'Tugas Kelompok',
        'deadline' => '2024-11-28 23:59:00',
        'assigned_date' => '2024-11-10 13:00:00',
        'status' => 'Terlambat',
        'nilai' => null,
        'file_tugas' => 'RPL_Tugas1_Analisis.pdf',
        'submission_allowed' => false,
        'max_file_size' => '15MB',
        'file_types' => '.pdf, .docx, .pptx',
        'priority' => 'high'
    ],
    [
        'id' => 4,
        'mata_kuliah' => 'Artificial Intelligence',
        'kode_mk' => 'AI001',
        'judul' => 'Implementasi Algoritma Machine Learning',
        'deskripsi' => 'Mengimplementasikan algoritma klasifikasi menggunakan Python dan scikit-learn.',
        'jenis' => 'Tugas Individu',
        'deadline' => '2024-12-10 23:59:00',
        'assigned_date' => '2024-11-25 08:00:00',
        'status' => 'Belum Dikerjakan',
        'nilai' => null,
        'file_tugas' => 'AI_Tugas4_ML.pdf',
        'submission_allowed' => true,
        'max_file_size' => '20MB',
        'file_types' => '.zip, .py, .ipynb',
        'priority' => 'medium'
    ],
    [
        'id' => 5,
        'mata_kuliah' => 'Pemrograman Web Lanjut',
        'kode_mk' => 'PWL001',
        'judul' => 'REST API dengan Authentication',
        'deskripsi' => 'Membuat REST API lengkap dengan sistem autentikasi JWT.',
        'jenis' => 'Tugas Individu',
        'deadline' => '2024-11-20 23:59:00',
        'assigned_date' => '2024-11-05 08:00:00',
        'status' => 'Selesai',
        'nilai' => 88,
        'file_tugas' => 'PWL_Tugas2_API.pdf',
        'submission_allowed' => false,
        'max_file_size' => '10MB',
        'file_types' => '.zip, .pdf',
        'priority' => 'medium',
        'submitted_date' => '2024-11-19 22:30:00',
        'submitted_file' => 'Ahmad_PWL_Tugas2_API.zip'
    ]
];

function getStatusBadge($status) {
    switch ($status) {
        case 'Selesai':
            return 'success';
        case 'Sedang Dikerjakan':
            return 'warning';
        case 'Belum Dikerjakan':
            return 'info';
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
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <div class="dashboard-container">
        <div class="page-header">
            <h1><i class="fa-solid fa-tasks"></i> Manajemen Tugas</h1>
            <div class="page-info">
                <span class="semester-info"><?php echo $current_semester; ?></span>
                <span class="student-info"><?php echo $student_nim . " - " . $student_name; ?></span>
            </div>
        </div>

        <!-- Assignment Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['total_assignments']; ?></h3>
                    <p>Total Tugas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['completed']; ?></h3>
                    <p>Selesai</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $assignment_stats['pending']; ?></h3>
                    <p>Dalam Proses</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon danger">
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
            <div class="filter-section">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="statusFilter">Status:</label>
                        <select id="statusFilter" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="Belum Dikerjakan">Belum Dikerjakan</option>
                            <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Terlambat">Terlambat</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="subjectFilter">Mata Kuliah:</label>
                        <select id="subjectFilter" class="form-select">
                            <option value="">Semua Mata Kuliah</option>
                            <option value="PWL001">Pemrograman Web Lanjut</option>
                            <option value="DB001">Basis Data</option>
                            <option value="RPL001">Rekayasa Perangkat Lunak</option>
                            <option value="AI001">Artificial Intelligence</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="typeFilter">Jenis:</label>
                        <select id="typeFilter" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="Tugas Individu">Tugas Individu</option>
                            <option value="Tugas Kelompok">Tugas Kelompok</option>
                            <option value="Proyek Akhir">Proyek Akhir</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments List -->
        <div class="content-section full-width">
            <div class="section-header">
                <h2><i class="fa-solid fa-list"></i> Daftar Tugas</h2>
            </div>
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
                            </div>

                            <?php if (isset($assignment['progress'])): ?>
                                <div class="progress-section">
                                    <div class="progress-label">Progress: <?php echo $assignment['progress']; ?>%</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill warning" style="width: <?php echo $assignment['progress']; ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($assignment['status'] == 'Selesai' && isset($assignment['nilai'])): ?>
                                <div class="grade-section">
                                    <div class="grade-display">
                                        <i class="fa-solid fa-star"></i>
                                        Nilai: <strong><?php echo $assignment['nilai']; ?></strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="assignment-actions">
                            <a href="#" class="btn btn-outline" onclick="viewAssignment(<?php echo $assignment['id']; ?>)">
                                <i class="fa-solid fa-eye"></i> Lihat Detail
                            </a>
                            <a href="<?php echo '../uploads/assignments/' . $assignment['file_tugas']; ?>" class="btn btn-primary" target="_blank">
                                <i class="fa-solid fa-download"></i> Download Soal
                            </a>
                            <?php if ($assignment['submission_allowed'] && $assignment['status'] != 'Selesai'): ?>
                                <button class="btn btn-success" onclick="submitAssignment(<?php echo $assignment['id']; ?>)">
                                    <i class="fa-solid fa-upload"></i> Upload Jawaban
                                </button>
                            <?php elseif ($assignment['status'] == 'Selesai'): ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fa-solid fa-check"></i> Sudah Dikumpulkan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Assignment Categories Summary -->
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
                                <div class="progress-fill primary" style="width: <?php echo ($category['completed'] / $category['count']) * 100; ?>%"></div>
                            </div>
                            <span class="progress-percentage"><?php echo round(($category['completed'] / $category['count']) * 100); ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Assignment Submission Modal -->
    <div id="submissionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-upload"></i> Upload Jawaban Tugas</h3>
                <span class="close" onclick="closeSubmissionModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="submissionForm" enctype="multipart/form-data">
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
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-upload"></i> Upload Jawaban
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
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
            // Implementation for viewing assignment details
            alert('Viewing assignment details for ID: ' + assignmentId);
        }

        // Form submission
        document.getElementById('submissionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Simulate file upload (replace with actual AJAX call)
            alert('File berhasil diupload! (Simulasi)');
            closeSubmissionModal();
            
            // Refresh page to show updated status
            setTimeout(() => {
                location.reload();
            }, 1000);
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('submissionModal');
            if (event.target === modal) {
                closeSubmissionModal();
            }
        }
    </script>
</body>
</html>
