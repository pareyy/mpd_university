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

// Sample data for nilai (in real application, this would come from database)
$mata_kuliah_list = [
    ['id' => 1, 'nama' => 'Pemrograman Web Lanjut', 'kelas' => 'A'],
    ['id' => 2, 'nama' => 'Database', 'kelas' => 'B'],
    ['id' => 3, 'nama' => 'Algoritma dan Struktur Data', 'kelas' => 'C']
];

$mahasiswa_data = [
    ['nim' => '2021001', 'nama' => 'Ahmad Rizki', 'tugas1' => 85, 'tugas2' => 78, 'uts' => 82, 'uas' => 88],
    ['nim' => '2021002', 'nama' => 'Siti Aisyah', 'tugas1' => 92, 'tugas2' => 85, 'uts' => 89, 'uas' => 91],
    ['nim' => '2021003', 'nama' => 'Budi Santoso', 'tugas1' => 78, 'tugas2' => 82, 'uts' => 75, 'uas' => 80],
    ['nim' => '2021004', 'nama' => 'Dewi Lestari', 'tugas1' => 88, 'tugas2' => 90, 'uts' => 85, 'uas' => 87],
    ['nim' => '2021005', 'nama' => 'Rendi Pratama', 'tugas1' => 75, 'tugas2' => 70, 'uts' => 78, 'uas' => 82]
];

// Calculate final grade
function hitungNilaiAkhir($tugas1, $tugas2, $uts, $uas) {
    return round(($tugas1 * 0.2) + ($tugas2 * 0.2) + ($uts * 0.3) + ($uas * 0.3));
}

function getGradeLetter($nilai) {
    if ($nilai >= 85) return 'A';
    elseif ($nilai >= 70) return 'B';
    elseif ($nilai >= 60) return 'C';
    elseif ($nilai >= 50) return 'D';
    else return 'E';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - MPD University</title>
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
                <h1><i class="fas fa-star"></i> Input Nilai Mahasiswa</h1>
                <p>Kelola dan input nilai mahasiswa</p>
            </div>

            <!-- Filter Section -->
            <div class="dashboard-section">
                <h2><i class="fas fa-filter"></i> Filter Mata Kuliah</h2>
                <div class="filter-container">
                    <select id="mata_kuliah_filter" class="form-control">
                        <option value="">Pilih Mata Kuliah</option>
                        <?php foreach ($mata_kuliah_list as $mk): ?>
                            <option value="<?php echo $mk['id']; ?>">
                                <?php echo $mk['nama']; ?> - Kelas <?php echo $mk['kelas']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" onclick="loadMahasiswa()">
                        <i class="fas fa-search"></i> Tampilkan Data
                    </button>
                </div>
            </div>

            <!-- Grade Input Section -->
            <div class="dashboard-section" id="grade-section" style="display: none;">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Input Nilai - <span id="selected-course">Pemrograman Web Lanjut - Kelas A</span></h2>
                    <button class="btn btn-success" onclick="saveAllGrades()">
                        <i class="fas fa-save"></i> Simpan Semua Nilai
                    </button>
                </div>
                
                <div class="grades-container">
                    <div class="table-responsive">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Tugas 1<br><small>(20%)</small></th>
                                    <th>Tugas 2<br><small>(20%)</small></th>
                                    <th>UTS<br><small>(30%)</small></th>
                                    <th>UAS<br><small>(30%)</small></th>
                                    <th>Nilai Akhir</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="grades-tbody">
                                <?php foreach ($mahasiswa_data as $mhs): ?>
                                    <?php 
                                    $nilai_akhir = hitungNilaiAkhir($mhs['tugas1'], $mhs['tugas2'], $mhs['uts'], $mhs['uas']);
                                    $grade = getGradeLetter($nilai_akhir);
                                    $status = $nilai_akhir >= 60 ? 'Lulus' : 'Tidak Lulus';
                                    ?>
                                    <tr>
                                        <td><?php echo $mhs['nim']; ?></td>
                                        <td><?php echo $mhs['nama']; ?></td>
                                        <td>
                                            <input type="number" class="grade-input" 
                                                   value="<?php echo $mhs['tugas1']; ?>" 
                                                   min="0" max="100" 
                                                   onchange="calculateFinalGrade(this)">
                                        </td>
                                        <td>
                                            <input type="number" class="grade-input" 
                                                   value="<?php echo $mhs['tugas2']; ?>" 
                                                   min="0" max="100" 
                                                   onchange="calculateFinalGrade(this)">
                                        </td>
                                        <td>
                                            <input type="number" class="grade-input" 
                                                   value="<?php echo $mhs['uts']; ?>" 
                                                   min="0" max="100" 
                                                   onchange="calculateFinalGrade(this)">
                                        </td>
                                        <td>
                                            <input type="number" class="grade-input" 
                                                   value="<?php echo $mhs['uas']; ?>" 
                                                   min="0" max="100" 
                                                   onchange="calculateFinalGrade(this)">
                                        </td>
                                        <td class="final-grade"><?php echo $nilai_akhir; ?></td>
                                        <td>
                                            <span class="grade-badge grade-<?php echo strtolower($grade); ?>">
                                                <?php echo $grade; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $nilai_akhir >= 60 ? 'status-pass' : 'status-fail'; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Grade Statistics -->
            <div class="dashboard-section" id="stats-section" style="display: none;">
                <h2><i class="fas fa-chart-pie"></i> Statistik Nilai</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Mahasiswa</h3>
                            <p class="stat-number" id="total-students"><?php echo count($mahasiswa_data); ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Lulus</h3>
                            <p class="stat-number" id="pass-count">
                                <?php 
                                $lulus = 0;
                                foreach ($mahasiswa_data as $mhs) {
                                    $nilai = hitungNilaiAkhir($mhs['tugas1'], $mhs['tugas2'], $mhs['uts'], $mhs['uas']);
                                    if ($nilai >= 60) $lulus++;
                                }
                                echo $lulus;
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Tidak Lulus</h3>
                            <p class="stat-number" id="fail-count">
                                <?php echo count($mahasiswa_data) - $lulus; ?>
                            </p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rata-rata Nilai</h3>
                            <p class="stat-number" id="average-grade">
                                <?php 
                                $total = 0;
                                foreach ($mahasiswa_data as $mhs) {
                                    $total += hitungNilaiAkhir($mhs['tugas1'], $mhs['tugas2'], $mhs['uts'], $mhs['uas']);
                                }
                                echo round($total / count($mahasiswa_data), 1);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div class="grade-distribution">
                    <h3>Distribusi Grade</h3>
                    <div class="grade-bars">
                        <?php
                        $grade_count = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
                        foreach ($mahasiswa_data as $mhs) {
                            $nilai = hitungNilaiAkhir($mhs['tugas1'], $mhs['tugas2'], $mhs['uts'], $mhs['uas']);
                            $grade = getGradeLetter($nilai);
                            $grade_count[$grade]++;
                        }
                        
                        foreach ($grade_count as $grade => $count):
                            $percentage = ($count / count($mahasiswa_data)) * 100;
                        ?>
                            <div class="grade-bar">
                                <div class="grade-label">
                                    <span class="grade-badge grade-<?php echo strtolower($grade); ?>"><?php echo $grade; ?></span>
                                    <span><?php echo $count; ?> mahasiswa</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill grade-<?php echo strtolower($grade); ?>" 
                                         style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="percentage"><?php echo round($percentage, 1); ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function loadMahasiswa() {
            const selectedCourse = document.getElementById('mata_kuliah_filter').value;
            const gradeSection = document.getElementById('grade-section');
            const statsSection = document.getElementById('stats-section');
            
            if (selectedCourse) {
                gradeSection.style.display = 'block';
                statsSection.style.display = 'block';
                // Update course name in the header
                const courseName = document.getElementById('mata_kuliah_filter').selectedOptions[0].text;
                document.getElementById('selected-course').textContent = courseName;
            } else {
                gradeSection.style.display = 'none';
                statsSection.style.display = 'none';
            }
        }

        function calculateFinalGrade(input) {
            const row = input.closest('tr');
            const grades = row.querySelectorAll('.grade-input');
            
            const tugas1 = parseFloat(grades[0].value) || 0;
            const tugas2 = parseFloat(grades[1].value) || 0;
            const uts = parseFloat(grades[2].value) || 0;
            const uas = parseFloat(grades[3].value) || 0;
            
            const finalGrade = Math.round((tugas1 * 0.2) + (tugas2 * 0.2) + (uts * 0.3) + (uas * 0.3));
            
            row.querySelector('.final-grade').textContent = finalGrade;
            
            // Update grade letter
            let gradeLetter = '';
            if (finalGrade >= 85) gradeLetter = 'A';
            else if (finalGrade >= 70) gradeLetter = 'B';
            else if (finalGrade >= 60) gradeLetter = 'C';
            else if (finalGrade >= 50) gradeLetter = 'D';
            else gradeLetter = 'E';
            
            const gradeBadge = row.querySelector('.grade-badge');
            gradeBadge.textContent = gradeLetter;
            gradeBadge.className = `grade-badge grade-${gradeLetter.toLowerCase()}`;
            
            // Update status
            const status = finalGrade >= 60 ? 'Lulus' : 'Tidak Lulus';
            const statusBadge = row.querySelector('.status-badge');
            statusBadge.textContent = status;
            statusBadge.className = `status-badge ${finalGrade >= 60 ? 'status-pass' : 'status-fail'}`;
        }

        function saveAllGrades() {
            // Implement save functionality
            alert('Nilai berhasil disimpan!');
        }
    </script>

    <style>
        .filter-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 1rem;
            align-items: end;
        }

        .filter-container .form-control {
            flex: 1;
            max-width: 400px;
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            flex: 1;
            margin: 0;
        }

        .grades-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .grades-table th,
        .grades-table td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }

        .grades-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .grades-table th small {
            color: #6b7280;
            font-weight: 400;
        }

        .grade-input {
            width: 80px;
            padding: 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
        }

        .grade-input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .final-grade {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2563eb;
        }

        .grade-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .grade-a { background: #d1fae5; color: #065f46; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-c { background: #fef3c7; color: #92400e; }
        .grade-d { background: #fed7aa; color: #ea580c; }
        .grade-e { background: #fee2e2; color: #dc2626; }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .status-pass {
            background: #d1fae5;
            color: #065f46;
        }

        .status-fail {
            background: #fee2e2;
            color: #dc2626;
        }

        .grade-distribution {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
        }

        .grade-distribution h3 {
            margin: 0 0 1.5rem 0;
            color: #374151;
        }

        .grade-bars {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .grade-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .grade-label {
            min-width: 150px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .progress-bar {
            flex: 1;
            height: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            transition: width 0.3s ease;
        }

        .progress-fill.grade-a { background: #10b981; }
        .progress-fill.grade-b { background: #3b82f6; }
        .progress-fill.grade-c { background: #f59e0b; }
        .progress-fill.grade-d { background: #f97316; }
        .progress-fill.grade-e { background: #ef4444; }

        .percentage {
            min-width: 60px;
            text-align: right;
            font-weight: 600;
            color: #374151;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .section-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .grades-table {
                font-size: 0.875rem;
            }
            
            .grade-input {
                width: 60px;
            }
        }
    </style>
</body>
</html>
