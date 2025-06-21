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

// Sample transcript data
$transkrip_data = [
    'Semester 1' => [
        ['kode' => 'MAT101', 'mata_kuliah' => 'Matematika Dasar', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'FIS101', 'mata_kuliah' => 'Fisika Dasar', 'sks' => 3, 'nilai' => 'B+', 'bobot' => 3.5],
        ['kode' => 'KIM101', 'mata_kuliah' => 'Kimia Dasar', 'sks' => 3, 'nilai' => 'A-', 'bobot' => 3.7],
        ['kode' => 'ING101', 'mata_kuliah' => 'Bahasa Inggris', 'sks' => 2, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'PWK101', 'mata_kuliah' => 'Pengantar Komputer', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0]
    ],
    'Semester 2' => [
        ['kode' => 'MAT201', 'mata_kuliah' => 'Kalkulus', 'sks' => 4, 'nilai' => 'B+', 'bobot' => 3.5],
        ['kode' => 'ALG201', 'mata_kuliah' => 'Aljabar Linear', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'PRG201', 'mata_kuliah' => 'Pemrograman Dasar', 'sks' => 4, 'nilai' => 'A-', 'bobot' => 3.7],
        ['kode' => 'STA201', 'mata_kuliah' => 'Statistika', 'sks' => 3, 'nilai' => 'B', 'bobot' => 3.0],
        ['kode' => 'AGM201', 'mata_kuliah' => 'Agama', 'sks' => 2, 'nilai' => 'A', 'bobot' => 4.0]
    ],
    'Semester 3' => [
        ['kode' => 'ASD301', 'mata_kuliah' => 'Algoritma dan Struktur Data', 'sks' => 4, 'nilai' => 'A-', 'bobot' => 3.7],
        ['kode' => 'OOP301', 'mata_kuliah' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'MDS301', 'mata_kuliah' => 'Matematika Diskrit', 'sks' => 3, 'nilai' => 'B+', 'bobot' => 3.5],
        ['kode' => 'BDT301', 'mata_kuliah' => 'Basis Data', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'JAR301', 'mata_kuliah' => 'Jaringan Komputer', 'sks' => 3, 'nilai' => 'B+', 'bobot' => 3.5]
    ],
    'Semester 4' => [
        ['kode' => 'RPL401', 'mata_kuliah' => 'Rekayasa Perangkat Lunak', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'SBD401', 'mata_kuliah' => 'Sistem Basis Data', 'sks' => 3, 'nilai' => 'B', 'bobot' => 3.0],
        ['kode' => 'SO401', 'mata_kuliah' => 'Sistem Operasi', 'sks' => 3, 'nilai' => 'A-', 'bobot' => 3.7],
        ['kode' => 'PWB401', 'mata_kuliah' => 'Pemrograman Web', 'sks' => 4, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'HCI401', 'mata_kuliah' => 'Human Computer Interaction', 'sks' => 3, 'nilai' => 'B+', 'bobot' => 3.5]
    ],
    'Semester 5' => [
        ['kode' => 'PWL501', 'mata_kuliah' => 'Pemrograman Web Lanjut', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'DB501', 'mata_kuliah' => 'Database', 'sks' => 3, 'nilai' => 'B+', 'bobot' => 3.5],
        ['kode' => 'AI501', 'mata_kuliah' => 'Artificial Intelligence', 'sks' => 3, 'nilai' => 'A-', 'bobot' => 3.7],
        ['kode' => 'PM501', 'mata_kuliah' => 'Pemrograman Mobile', 'sks' => 3, 'nilai' => 'A', 'bobot' => 4.0],
        ['kode' => 'KWU501', 'mata_kuliah' => 'Kewirausahaan', 'sks' => 2, 'nilai' => 'A', 'bobot' => 4.0]
    ]
];

// Calculate semester and cumulative GPA
function calculateGPA($grades) {
    $total_points = 0;
    $total_sks = 0;
    
    foreach ($grades as $grade) {
        $total_points += $grade['sks'] * $grade['bobot'];
        $total_sks += $grade['sks'];
    }
    
    return $total_sks > 0 ? round($total_points / $total_sks, 2) : 0;
}

function calculateCumulativeGPA($all_grades) {
    $total_points = 0;
    $total_sks = 0;
    
    foreach ($all_grades as $semester_grades) {
        foreach ($semester_grades as $grade) {
            $total_points += $grade['sks'] * $grade['bobot'];
            $total_sks += $grade['sks'];
        }
    }
    
    return $total_sks > 0 ? round($total_points / $total_sks, 2) : 0;
}

$cumulative_gpa = calculateCumulativeGPA($transkrip_data);
$total_sks = 0;
foreach ($transkrip_data as $semester_grades) {
    foreach ($semester_grades as $grade) {
        $total_sks += $grade['sks'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai - MPD University</title>
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
                <h1><i class="fas fa-chart-line"></i> Transkrip Nilai</h1>
                <p>Transkrip akademik dan riwayat nilai Anda</p>
            </div>

            <!-- Academic Summary -->
            <div class="dashboard-section">
                <h2><i class="fas fa-trophy"></i> Ringkasan Akademik</h2>
                <div class="academic-summary">
                    <div class="summary-card gpa-card">
                        <div class="summary-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="summary-content">
                            <h3>IPK Kumulatif</h3>
                            <p class="gpa-value"><?php echo $cumulative_gpa; ?></p>
                            <small>Dari skala 4.0</small>
                        </div>
                        <div class="gpa-badge">
                            <?php 
                            if ($cumulative_gpa >= 3.5) echo '<span class="badge badge-excellent">Sangat Baik</span>';
                            elseif ($cumulative_gpa >= 3.0) echo '<span class="badge badge-good">Baik</span>';
                            elseif ($cumulative_gpa >= 2.5) echo '<span class="badge badge-fair">Cukup</span>';
                            else echo '<span class="badge badge-poor">Kurang</span>';
                            ?>
                        </div>
                    </div>

                    <div class="summary-stats">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Total SKS</h4>
                                <p><?php echo $total_sks; ?> SKS</p>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Semester Aktif</h4>
                                <p>Semester 5</p>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Progress Studi</h4>
                                <p><?php echo round(($total_sks / 144) * 100); ?>%</p>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Perkiraan Lulus</h4>
                                <p>2026</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Semester Tabs -->
            <div class="dashboard-section">
                <h2><i class="fas fa-book-open"></i> Transkrip Per Semester</h2>
                <div class="transcript-container">
                    <div class="semester-tabs">
                        <?php $i = 1; foreach ($transkrip_data as $semester => $grades): ?>
                            <button class="tab-button <?php echo $i === 1 ? 'active' : ''; ?>" 
                                    onclick="showSemester('semester<?php echo $i; ?>')">
                                <?php echo $semester; ?>
                            </button>
                        <?php $i++; endforeach; ?>
                    </div>

                    <?php $i = 1; foreach ($transkrip_data as $semester => $grades): ?>
                        <div id="semester<?php echo $i; ?>" class="semester-content <?php echo $i === 1 ? 'active' : ''; ?>">
                            <div class="semester-header">
                                <h3><?php echo $semester; ?></h3>
                                <div class="semester-stats">
                                    <span class="semester-gpa">
                                        IPK: <?php echo calculateGPA($grades); ?>
                                    </span>
                                    <span class="semester-sks">
                                        SKS: <?php echo array_sum(array_column($grades, 'sks')); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grades-table-container">
                                <table class="grades-table">
                                    <thead>
                                        <tr>
                                            <th>Kode MK</th>
                                            <th>Mata Kuliah</th>
                                            <th>SKS</th>
                                            <th>Nilai</th>
                                            <th>Bobot</th>
                                            <th>Mutu (SKS × Bobot)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grades as $grade): ?>
                                            <tr>
                                                <td>
                                                    <span class="course-code"><?php echo $grade['kode']; ?></span>
                                                </td>
                                                <td><?php echo $grade['mata_kuliah']; ?></td>
                                                <td><?php echo $grade['sks']; ?></td>
                                                <td>
                                                    <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $grade['nilai']))); ?>">
                                                        <?php echo $grade['nilai']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $grade['bobot']; ?></td>
                                                <td class="mutu-value"><?php echo $grade['sks'] * $grade['bobot']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="summary-row">
                                            <td colspan="2"><strong>Total</strong></td>
                                            <td><strong><?php echo array_sum(array_column($grades, 'sks')); ?></strong></td>
                                            <td colspan="2"><strong>IPK Semester</strong></td>
                                            <td><strong><?php echo calculateGPA($grades); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>

            <!-- Grade Analysis -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Analisis Nilai</h2>
                <div class="grade-analysis">
                    <div class="grade-distribution">
                        <h3>Distribusi Nilai</h3>
                        <div class="distribution-chart">
                            <?php
                            $all_grades = [];
                            foreach ($transkrip_data as $semester_grades) {
                                foreach ($semester_grades as $grade) {
                                    $all_grades[] = $grade['nilai'];
                                }
                            }
                            $grade_counts = array_count_values($all_grades);
                            $total_courses = count($all_grades);
                            
                            $grade_order = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E'];
                            foreach ($grade_order as $grade):
                                if (isset($grade_counts[$grade])):
                                    $percentage = ($grade_counts[$grade] / $total_courses) * 100;
                            ?>
                                <div class="distribution-item">
                                    <div class="distribution-label">
                                        <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $grade))); ?>">
                                            <?php echo $grade; ?>
                                        </span>
                                        <span><?php echo $grade_counts[$grade]; ?> mata kuliah</span>
                                    </div>
                                    <div class="distribution-bar">
                                        <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="percentage"><?php echo round($percentage, 1); ?>%</span>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>

                    <div class="gpa-trend">
                        <h3>Trend IPK</h3>
                        <div class="trend-chart">
                            <div class="trend-line">
                                <?php 
                                $semester_gpas = [];
                                foreach ($transkrip_data as $semester => $grades) {
                                    $semester_gpas[] = calculateGPA($grades);
                                }
                                $max_gpa = max($semester_gpas);
                                ?>
                                
                                <?php foreach ($semester_gpas as $index => $gpa): ?>
                                    <div class="trend-point" style="height: <?php echo ($gpa / 4) * 100; ?>%">
                                        <span class="trend-value"><?php echo $gpa; ?></span>
                                        <span class="trend-label">Sem <?php echo $index + 1; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="dashboard-section">
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="printTranscript()">
                        <i class="fas fa-print"></i> Cetak Transkrip
                    </button>
                    <button class="btn btn-secondary" onclick="downloadPDF()">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </button>
                    <button class="btn btn-success" onclick="exportExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function showSemester(semesterId) {
            // Hide all semester contents
            const contents = document.querySelectorAll('.semester-content');
            contents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected semester
            document.getElementById(semesterId).classList.add('active');
            event.target.classList.add('active');
        }

        function printTranscript() {
            window.print();
        }

        function downloadPDF() {
            alert('Fitur download PDF akan segera tersedia!');
        }

        function exportExcel() {
            alert('Fitur export Excel akan segera tersedia!');
        }
    </script>

    <style>
        .academic-summary {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 2rem;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .gpa-card {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            position: relative;
        }

        .summary-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .gpa-value {
            font-size: 3rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .gpa-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-excellent { background: #fef3c7; color: #92400e; }
        .badge-good { background: #d1fae5; color: #065f46; }
        .badge-fair { background: #fed7aa; color: #ea580c; }
        .badge-poor { background: #fee2e2; color: #dc2626; }

        .summary-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .stat-icon {
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 1.25rem;
        }

        .stat-content h4 {
            margin: 0 0 0.25rem 0;
            color: #374151;
            font-size: 0.875rem;
        }

        .stat-content p {
            margin: 0;
            color: #10b981;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .transcript-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .semester-tabs {
            display: flex;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .tab-button {
            flex: 1;
            padding: 1rem;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #6b7280;
        }

        .tab-button.active {
            background: white;
            color: #10b981;
            border-bottom: 3px solid #10b981;
        }

        .tab-button:hover {
            background: #f3f4f6;
        }

        .semester-content {
            display: none;
            padding: 2rem;
        }

        .semester-content.active {
            display: block;
        }

        .semester-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .semester-stats {
            display: flex;
            gap: 1rem;
        }

        .semester-gpa, .semester-sks {
            background: #f0fdf4;
            color: #166534;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .grades-table-container {
            overflow-x: auto;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
        }

        .grades-table th,
        .grades-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .grades-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }

        .course-code {
            background: #f3f4f6;
            color: #374151;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.875rem;
        }

        .grade-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .grade-a { background: #d1fae5; color: #065f46; }
        .grade-aplus { background: #dcfce7; color: #14532d; }
        .grade-aminus { background: #bbf7d0; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-bplus { background: #bfdbfe; color: #1e3a8a; }
        .grade-bminus { background: #93c5fd; color: #1e40af; }

        .mutu-value {
            font-weight: 600;
            color: #10b981;
        }

        .summary-row {
            background: #f8fafc;
            font-weight: 600;
        }

        .grade-analysis {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .grade-distribution, .gpa-trend {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .distribution-chart {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .distribution-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .distribution-label {
            min-width: 150px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .distribution-bar {
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
        }

        .trend-chart {
            height: 200px;
            position: relative;
        }

        .trend-line {
            display: flex;
            align-items: end;
            height: 100%;
            gap: 1rem;
        }

        .trend-point {
            flex: 1;
            background: linear-gradient(to top, #10b981, #34d399);
            border-radius: 4px 4px 0 0;
            position: relative;
            min-height: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .trend-value {
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .trend-label {
            position: absolute;
            bottom: -25px;
            color: #6b7280;
            font-size: 0.75rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .academic-summary {
                grid-template-columns: 1fr;
            }
            
            .summary-stats {
                grid-template-columns: 1fr;
            }
            
            .semester-tabs {
                flex-direction: column;
            }
            
            .grade-analysis {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media print {
            .dashboard-header,
            .semester-tabs,
            .action-buttons {
                display: none;
            }
            
            .semester-content {
                display: block !important;
                page-break-after: always;
            }
        }
    </style>
</body>
</html>
