<?php
// Start session
session_start();

// Check if user is logged in and is a mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once '../koneksi.php';

// Get mahasiswa information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Sample data for mata kuliah
$mata_kuliah_semester_ini = [
    [
        'kode' => 'PWL001',
        'nama' => 'Pemrograman Web Lanjut',
        'sks' => 3,
        'dosen' => 'Dr. Ahmad Rahman',
        'ruang' => 'Lab 1',
        'jadwal' => 'Senin, 08:00-10:30',
        'nilai' => 'A',
        'status' => 'Aktif'
    ],
    [
        'kode' => 'DB001',
        'nama' => 'Database',
        'sks' => 3,
        'dosen' => 'Prof. Siti Nurhaliza',
        'ruang' => 'Ruang 201',
        'jadwal' => 'Selasa, 10:30-12:00',
        'nilai' => 'B+',
        'status' => 'Aktif'
    ],
    [
        'kode' => 'ASD001',
        'nama' => 'Algoritma dan Struktur Data',
        'sks' => 3,
        'dosen' => 'Dr. Budi Santoso',
        'ruang' => 'Ruang 105',
        'jadwal' => 'Rabu, 13:00-15:30',
        'nilai' => 'A-',
        'status' => 'Aktif'
    ],
    [
        'kode' => 'SBD001',
        'nama' => 'Sistem Basis Data',
        'sks' => 3,
        'dosen' => 'Dr. Maya Putri',
        'ruang' => 'Lab 2',
        'jadwal' => 'Kamis, 08:00-10:30',
        'nilai' => 'B',
        'status' => 'Aktif'
    ],
    [
        'kode' => 'PM001',
        'nama' => 'Pemrograman Mobile',
        'sks' => 3,
        'dosen' => 'Dr. Rendi Pratama',
        'ruang' => 'Lab 3',
        'jadwal' => 'Jumat, 10:30-12:00',
        'nilai' => 'A',
        'status' => 'Aktif'
    ]
];

$mata_kuliah_tersedia = [
    [
        'kode' => 'AI001',
        'nama' => 'Artificial Intelligence',
        'sks' => 3,
        'dosen' => 'Dr. Sarah Johnson',
        'prasyarat' => 'Algoritma dan Struktur Data',
        'kuota' => 30,
        'terisi' => 15
    ],
    [
        'kode' => 'ML001',
        'nama' => 'Machine Learning',
        'sks' => 3,
        'dosen' => 'Dr. Michael Chen',
        'prasyarat' => 'Matematika Diskrit',
        'kuota' => 25,
        'terisi' => 20
    ],
    [
        'kode' => 'SE001',
        'nama' => 'Software Engineering',
        'sks' => 3,
        'dosen' => 'Prof. Lisa Wang',
        'prasyarat' => 'Pemrograman Web Lanjut',
        'kuota' => 35,
        'terisi' => 28
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mata Kuliah - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-book"></i> Mata Kuliah</h1>
                <p>Kelola mata kuliah yang Anda ambil dan daftar mata kuliah yang tersedia</p>
            </div>

            <!-- Summary Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Mata Kuliah Diambil</h3>
                        <p class="stat-number"><?php echo count($mata_kuliah_semester_ini); ?></p>
                        <small>Semester ini</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total SKS</h3>
                        <p class="stat-number"><?php echo array_sum(array_column($mata_kuliah_semester_ini, 'sks')); ?></p>
                        <small>SKS semester ini</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3>IPK Semester</h3>
                        <p class="stat-number">3.85</p>
                        <small>Semester 5</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Progress</h3>
                        <p class="stat-number">80%</p>
                        <small>Semester completion</small>
                    </div>
                </div>
            </div>

            <!-- Current Courses -->
            <div class="dashboard-section">
                <h2><i class="fas fa-book-open"></i> Mata Kuliah Semester Ini</h2>
                <div class="courses-grid">
                    <?php foreach ($mata_kuliah_semester_ini as $mk): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-code">
                                    <span class="badge badge-primary"><?php echo $mk['kode']; ?></span>
                                    <span class="course-sks"><?php echo $mk['sks']; ?> SKS</span>
                                </div>
                                <div class="course-grade">
                                    <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $mk['nilai']))); ?>">
                                        <?php echo $mk['nilai']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="course-content">
                                <h3><?php echo $mk['nama']; ?></h3>
                                <p class="course-lecturer">
                                    <i class="fas fa-user-tie"></i> <?php echo $mk['dosen']; ?>
                                </p>
                                <p class="course-schedule">
                                    <i class="fas fa-clock"></i> <?php echo $mk['jadwal']; ?>
                                </p>
                                <p class="course-room">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $mk['ruang']; ?>
                                </p>
                            </div>
                            <div class="course-actions">
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <button class="btn btn-secondary btn-sm">
                                    <i class="fas fa-file-alt"></i> Materi
                                </button>
                                <button class="btn btn-warning btn-sm">
                                    <i class="fas fa-tasks"></i> Tugas
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Available Courses -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Mata Kuliah Tersedia</h2>
                <div class="available-courses">
                    <div class="courses-table-container">
                        <table class="courses-table">
                            <thead>
                                <tr>
                                    <th>Kode MK</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Dosen</th>
                                    <th>Prasyarat</th>
                                    <th>Kuota</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mata_kuliah_tersedia as $mk): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-secondary"><?php echo $mk['kode']; ?></span>
                                        </td>
                                        <td><?php echo $mk['nama']; ?></td>
                                        <td><?php echo $mk['sks']; ?> SKS</td>
                                        <td><?php echo $mk['dosen']; ?></td>
                                        <td><?php echo $mk['prasyarat']; ?></td>
                                        <td>
                                            <div class="quota-info">
                                                <span class="quota-text"><?php echo $mk['terisi']; ?>/<?php echo $mk['kuota']; ?></span>
                                                <div class="quota-bar">
                                                    <div class="quota-fill" style="width: <?php echo ($mk['terisi']/$mk['kuota'])*100; ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($mk['terisi'] < $mk['kuota']): ?>
                                                <button class="btn btn-success btn-sm" onclick="enrollCourse('<?php echo $mk['kode']; ?>')">
                                                    <i class="fas fa-plus"></i> Daftar
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-times"></i> Penuh
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Academic Progress -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Progress Akademik</h2>
                <div class="progress-overview">
                    <div class="semester-summary">
                        <h3>Ringkasan Semester</h3>
                        <div class="summary-stats">
                            <div class="summary-item">
                                <span class="summary-label">SKS Diambil:</span>
                                <span class="summary-value"><?php echo array_sum(array_column($mata_kuliah_semester_ini, 'sks')); ?> SKS</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">IPK Semester:</span>
                                <span class="summary-value">3.85</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">IPK Kumulatif:</span>
                                <span class="summary-value">3.75</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Total SKS Lulus:</span>
                                <span class="summary-value">95 SKS</span>
                            </div>
                        </div>
                    </div>

                    <div class="grade-distribution">
                        <h3>Distribusi Nilai</h3>
                        <div class="grade-chart">
                            <?php
                            $grades = array_count_values(array_column($mata_kuliah_semester_ini, 'nilai'));
                            foreach ($grades as $grade => $count):
                                $percentage = ($count / count($mata_kuliah_semester_ini)) * 100;
                            ?>
                                <div class="grade-bar">
                                    <div class="grade-info">
                                        <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $grade))); ?>">
                                            <?php echo $grade; ?>
                                        </span>
                                        <span class="grade-count"><?php echo $count; ?> mata kuliah</span>
                                    </div>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="percentage"><?php echo round($percentage, 1); ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function enrollCourse(kode) {
            if (confirm('Apakah Anda yakin ingin mendaftar mata kuliah ' + kode + '?')) {
                // Implement enrollment functionality
                alert('Pendaftaran mata kuliah ' + kode + ' berhasil!');
            }
        }
    </script>

    <style>
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .course-header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .course-code {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-sks {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .course-content {
            padding: 1.5rem;
        }

        .course-content h3 {
            margin: 0 0 1rem 0;
            color: #374151;
            font-size: 1.1rem;
        }

        .course-content p {
            margin: 0.5rem 0;
            color: #6b7280;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-actions {
            padding: 1rem 1.5rem;
            background: #f8fafc;
            display: flex;
            gap: 0.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .badge-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .courses-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .courses-table {
            width: 100%;
            border-collapse: collapse;
        }

        .courses-table th,
        .courses-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .courses-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }

        .quota-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .quota-text {
            font-size: 0.875rem;
            color: #374151;
        }

        .quota-bar {
            width: 80px;
            height: 6px;
            background: #f3f4f6;
            border-radius: 3px;
            overflow: hidden;
        }

        .quota-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .progress-overview {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .semester-summary, .grade-distribution {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .semester-summary h3, .grade-distribution h3 {
            margin: 0 0 1.5rem 0;
            color: #374151;
        }

        .summary-stats {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: #6b7280;
            font-weight: 500;
        }

        .summary-value {
            color: #10b981;
            font-weight: 700;
        }

        .grade-chart {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .grade-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .grade-info {
            min-width: 120px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .grade-count {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .bar-container {
            flex: 1;
            height: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .percentage {
            min-width: 60px;
            text-align: right;
            font-weight: 600;
            color: #374151;
        }

        .grade-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .grade-a { background: #d1fae5; color: #065f46; }
        .grade-aplus { background: #dcfce7; color: #14532d; }
        .grade-aminus { background: #bbf7d0; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-bplus { background: #bfdbfe; color: #1e3a8a; }
        .grade-bminus { background: #93c5fd; color: #1e40af; }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .progress-overview {
                grid-template-columns: 1fr;
            }
            
            .course-actions {
                flex-direction: column;
            }
            
            .courses-table {
                font-size: 0.875rem;
            }
        }
    </style>
</body>
</html>
