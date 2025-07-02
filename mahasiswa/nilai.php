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

// Get transkrip data from database
$transkrip_query = "SELECT 
    mk.semester,
    mk.kode_mk,
    mk.nama_mk,
    mk.sks,
    n.tugas1,
    n.tugas2,
    n.uts,
    n.uas,
    n.nilai_akhir,
    n.grade,
    CASE 
        WHEN n.grade = 'A' THEN 4.0
        WHEN n.grade = 'A-' THEN 3.7
        WHEN n.grade = 'B+' THEN 3.3
        WHEN n.grade = 'B' THEN 3.0
        WHEN n.grade = 'B-' THEN 2.7
        WHEN n.grade = 'C+' THEN 2.3
        WHEN n.grade = 'C' THEN 2.0
        WHEN n.grade = 'C-' THEN 1.7
        WHEN n.grade = 'D' THEN 1.0
        ELSE 0
    END as bobot
FROM kelas k
JOIN mata_kuliah mk ON k.mata_kuliah_id = mk.id
LEFT JOIN nilai n ON mk.id = n.mata_kuliah_id AND n.mahasiswa_id = k.mahasiswa_id
WHERE k.mahasiswa_id = '{$mahasiswa['id']}'
ORDER BY mk.semester, mk.nama_mk";

$transkrip_result = mysqli_query($conn, $transkrip_query);
$transkrip_data = [];

while ($row = mysqli_fetch_assoc($transkrip_result)) {
    $semester_name = 'Semester ' . $row['semester'];
    if (!isset($transkrip_data[$semester_name])) {
        $transkrip_data[$semester_name] = [];
    }
    
    $transkrip_data[$semester_name][] = [
        'kode' => $row['kode_mk'],
        'mata_kuliah' => $row['nama_mk'],
        'sks' => $row['sks'],
        'nilai' => $row['grade'] ?: 'Belum Ada',
        'bobot' => $row['bobot']
    ];
}

// Calculate semester and cumulative GPA
function calculateGPA($grades) {
    $total_points = 0;
    $total_sks = 0;
    
    foreach ($grades as $grade) {
        if ($grade['nilai'] !== 'Belum Ada') {
            $total_points += $grade['sks'] * $grade['bobot'];
            $total_sks += $grade['sks'];
        }
    }
    
    return $total_sks > 0 ? round($total_points / $total_sks, 2) : 0;
}

function calculateCumulativeGPA($all_grades) {
    $total_points = 0;
    $total_sks = 0;
    
    foreach ($all_grades as $semester_grades) {
        foreach ($semester_grades as $grade) {
            if ($grade['nilai'] !== 'Belum Ada') {
                $total_points += $grade['sks'] * $grade['bobot'];
                $total_sks += $grade['sks'];
            }
        }
    }
    
    return $total_sks > 0 ? round($total_points / $total_sks, 2) : 0;
}

$cumulative_gpa = calculateCumulativeGPA($transkrip_data);
$total_sks = 0;
$total_sks_lulus = 0;

foreach ($transkrip_data as $semester_grades) {
    foreach ($semester_grades as $grade) {
        $total_sks += $grade['sks'];
        if ($grade['nilai'] !== 'Belum Ada' && $grade['nilai'] !== 'E') {
            $total_sks_lulus += $grade['sks'];
        }
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
    <link rel="stylesheet" href="../assets/css/mahasiswa_clean.css?v=<?php echo time(); ?>">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_mahasiswa.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="page-header">
                <h1><i class="fas fa-book"></i> Transkrip Nilai</h1>
                <div class="page-info">
                    <div class="course-info">
                        Transkrip akademik untuk <?php echo $mahasiswa['nama']; ?> - <?php echo $mahasiswa['program_studi_nama']; ?>
                    </div>
                </div>
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
                                <h4>Total SKS Diambil</h4>
                                <p><?php echo $total_sks; ?> SKS</p>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h4>SKS Lulus</h4>
                                <p><?php echo $total_sks_lulus; ?> SKS</p>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Semester Aktif</h4>
                                <p>Semester <?php echo $mahasiswa['semester']; ?></p>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Progress Studi</h4>
                                <p><?php echo round(($total_sks_lulus / 144) * 100); ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Semester Tabs -->
            <div class="dashboard-section">
                <h2><i class="fas fa-book-open"></i> Transkrip Per Semester</h2>
                <div class="transcript-container">
                    <?php if (!empty($transkrip_data)): ?>
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
                                                        <?php if ($grade['nilai'] !== 'Belum Ada'): ?>
                                                            <span class="grade-badge grade-<?php echo strtolower(str_replace('+', 'plus', str_replace('-', 'minus', $grade['nilai']))); ?>">
                                                                <?php echo $grade['nilai']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="grade-badge grade-pending">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $grade['bobot']; ?></td>
                                                    <td class="mutu-value"><?php echo $grade['nilai'] !== 'Belum Ada' ? $grade['sks'] * $grade['bobot'] : '-'; ?></td>
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
                    <?php else: ?>
                        <div class="no-transcript">
                            <i class="fas fa-chart-line"></i>
                            <h3>Belum Ada Data Transkrip</h3>
                            <p>Data nilai dan transkrip akan muncul setelah Anda mengambil mata kuliah dan dosen memasukkan nilai.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grade Analysis -->
            <?php if (!empty($transkrip_data)): ?>
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
                                    if ($grade['nilai'] !== 'Belum Ada') {
                                        $all_grades[] = $grade['nilai'];
                                    }
                                }
                            }
                            
                            if (!empty($all_grades)):
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
                            else: 
                            ?>
                                <div class="no-grades">
                                    <i class="fas fa-chart-bar"></i>
                                    <p>Belum ada nilai yang tersedia untuk dianalisis.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="gpa-trend">
                        <h3>Trend IPK Per Semester</h3>
                        <div class="trend-chart">
                            <div class="trend-line">
                                <?php 
                                $semester_gpas = [];
                                foreach ($transkrip_data as $semester => $grades) {
                                    $semester_gpas[] = calculateGPA($grades);
                                }
                                ?>
                                
                                <?php foreach ($semester_gpas as $index => $gpa): ?>
                                    <div class="trend-point" style="height: <?php echo max(($gpa / 4) * 100, 10); ?>%">
                                        <span class="trend-value"><?php echo $gpa; ?></span>
                                        <span class="trend-label">Sem <?php echo $index + 1; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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
            grid-template-columns: minmax(300px, 400px) 1fr;
            gap: 2rem;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            align-items: start;
        }

        .gpa-card {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            position: relative;
            min-width: 0; /* Prevents overflow */
            box-sizing: border-box;
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

        .grade-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .no-transcript {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .no-transcript i {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1rem;
        }

        .no-transcript h3 {
            margin: 0 0 1rem 0;
            color: #374151;
        }

        @media (max-width: 1024px) {
            .academic-summary {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .summary-stats {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .academic-summary {
                grid-template-columns: 1fr;
                padding: 1.5rem;
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
