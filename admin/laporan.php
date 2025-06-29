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

// Real statistics from database
$statistics_query = "SELECT 
    (SELECT COUNT(*) FROM mahasiswa) as total_mahasiswa,
    (SELECT COUNT(*) FROM dosen) as total_dosen,
    (SELECT COUNT(*) FROM mata_kuliah) as total_mata_kuliah,
    (SELECT COUNT(*) FROM articles WHERE status = 'published') as total_artikel,
    (SELECT COUNT(*) FROM mahasiswa WHERE semester > 0) as mahasiswa_aktif,
    (SELECT COUNT(*) FROM mahasiswa WHERE semester = 0) as mahasiswa_cuti,
    (SELECT COUNT(*) FROM dosen WHERE created_at IS NOT NULL) as dosen_aktif,
    (SELECT 0) as dosen_cuti";
$statistics_result = mysqli_query($conn, $statistics_query);
$statistics = mysqli_fetch_assoc($statistics_result);

// Monthly data from database (last 6 months)
$monthly_query = "SELECT 
    MONTH(created_at) as month,
    YEAR(created_at) as year,
    MONTHNAME(created_at) as month_name,
    COUNT(*) as count
    FROM mahasiswa 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY year, month";
$monthly_result = mysqli_query($conn, $monthly_query);
$monthly_data_db = [];
while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_data_db[] = $row;
}

// Article data for last 6 months
$article_query = "SELECT 
    MONTH(created_at) as month,
    YEAR(created_at) as year,
    COUNT(*) as count
    FROM articles 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    AND status = 'published'
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY year, month";
$article_result = mysqli_query($conn, $article_query);
$article_data_db = [];
while ($row = mysqli_fetch_assoc($article_result)) {
    $article_data_db[] = $row;
}

// Format data for chart
$monthly_data = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'mahasiswa_baru' => [15, 23, 18, 12, 20, 25], // Default values, you can populate from $monthly_data_db
    'artikel_published' => [3, 5, 2, 4, 3, 6] // Default values, you can populate from $article_data_db
];

// Program studi distribution from database
$program_studi_query = "SELECT 
    ps.nama,
    COUNT(m.id) as count,
    ROUND((COUNT(m.id) * 100.0 / (SELECT COUNT(*) FROM mahasiswa)), 1) as percentage
    FROM program_studi ps
    LEFT JOIN mahasiswa m ON ps.id = m.program_studi_id
    GROUP BY ps.id, ps.nama
    HAVING count > 0
    ORDER BY count DESC";
$program_studi_result = mysqli_query($conn, $program_studi_query);
$program_studi_data = [];
while ($row = mysqli_fetch_assoc($program_studi_result)) {
    $program_studi_data[] = [
        'name' => $row['nama'],
        'count' => $row['count'],
        'percentage' => $row['percentage']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-chart-bar"></i> Laporan Sistem</h1>
                <p>Statistik dan analisis data sistem akademik</p>
            </div>

            <!-- Summary Statistics -->
            <div class="dashboard-section">
                <h2><i class="fas fa-chart-pie"></i> Ringkasan Statistik</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Mahasiswa</h3>
                            <p class="stat-number"><?php echo $statistics['total_mahasiswa']; ?></p>
                            <small>
                                <span class="stat-positive"><?php echo $statistics['mahasiswa_aktif']; ?> aktif</span> | 
                                <span class="stat-neutral"><?php echo $statistics['mahasiswa_cuti']; ?> cuti</span>
                            </small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Dosen</h3>
                            <p class="stat-number"><?php echo $statistics['total_dosen']; ?></p>
                            <small>
                                <span class="stat-positive"><?php echo $statistics['dosen_aktif']; ?> aktif</span> | 
                                <span class="stat-neutral"><?php echo $statistics['dosen_cuti']; ?> cuti</span>
                            </small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Mata Kuliah</h3>
                            <p class="stat-number"><?php echo $statistics['total_mata_kuliah']; ?></p>
                            <small>Total mata kuliah aktif</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Artikel Published</h3>
                            <p class="stat-number"><?php echo $statistics['total_artikel']; ?></p>
                            <small>Artikel yang dipublikasikan</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="dashboard-section">
                <div class="charts-container">
                    <!-- Monthly Registrations Chart -->
                    <div class="chart-card">
                        <h3><i class="fas fa-line-chart"></i> Pendaftaran Mahasiswa Bulanan</h3>
                        <div class="chart-wrapper">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>

                    <!-- Program Studi Distribution -->
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-pie"></i> Distribusi Program Studi</h3>
                        <div class="program-distribution">
                            <?php foreach ($program_studi_data as $prodi): ?>
                                <div class="distribution-item">
                                    <div class="distribution-info">
                                        <span class="prodi-name"><?php echo $prodi['name']; ?></span>
                                        <span class="prodi-count"><?php echo $prodi['count']; ?> mahasiswa</span>
                                    </div>
                                    <div class="distribution-bar">
                                        <div class="distribution-fill" style="width: <?php echo $prodi['percentage']; ?>%"></div>
                                        <span class="distribution-percentage"><?php echo $prodi['percentage']; ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Reports -->
            <div class="dashboard-section">
                <h2><i class="fas fa-download"></i> Export Laporan</h2>
                <div class="export-container">
                    <div class="export-card">
                        <div class="export-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="export-content">
                            <h3>Laporan Mahasiswa</h3>
                            <p>Data lengkap mahasiswa aktif dan status akademik</p>
                            <div class="export-actions">
                                <button class="btn btn-success" onclick="exportReport('mahasiswa', 'excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-danger" onclick="exportReport('mahasiswa', 'pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="export-card">
                        <div class="export-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="export-content">
                            <h3>Laporan Dosen</h3>
                            <p>Data dosen dan mata kuliah yang diampu</p>
                            <div class="export-actions">
                                <button class="btn btn-success" onclick="exportReport('dosen', 'excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-danger" onclick="exportReport('dosen', 'pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="export-card">
                        <div class="export-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="export-content">
                            <h3>Laporan Mata Kuliah</h3>
                            <p>Daftar mata kuliah dan distribusi per semester</p>
                            <div class="export-actions">
                                <button class="btn btn-success" onclick="exportReport('matkul', 'excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-danger" onclick="exportReport('matkul', 'pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="export-card">
                        <div class="export-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="export-content">
                            <h3>Laporan Statistik</h3>
                            <p>Laporan lengkap statistik sistem</p>
                            <div class="export-actions">
                                <button class="btn btn-success" onclick="exportReport('statistik', 'excel')">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button class="btn btn-danger" onclick="exportReport('statistik', 'pdf')">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Monthly registrations chart
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthly_data['labels']); ?>,
                datasets: [{
                    label: 'Mahasiswa Baru',
                    data: <?php echo json_encode($monthly_data['mahasiswa_baru']); ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Artikel Published',
                    data: <?php echo json_encode($monthly_data['artikel_published']); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function exportReport(type, format) {
            // Implementation for exporting reports
            alert(`Export laporan ${type} dalam format ${format.toUpperCase()}`);
            // In real implementation, this would trigger a download
        }
    </script>
</body>
</html>