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

// Sample statistics for reports
$statistics = [
    'total_mahasiswa' => 150,
    'total_dosen' => 25,
    'total_mata_kuliah' => 35,
    'total_artikel' => 12,
    'mahasiswa_aktif' => 142,
    'mahasiswa_cuti' => 8,
    'dosen_aktif' => 23,
    'dosen_cuti' => 2
];

// Sample monthly data for charts
$monthly_data = [
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    'mahasiswa_baru' => [15, 23, 18, 12, 20, 25],
    'artikel_published' => [3, 5, 2, 4, 3, 6]
];

// Sample program studi distribution
$program_studi_data = [
    ['name' => 'Teknik Informatika', 'count' => 85, 'percentage' => 56.7],
    ['name' => 'Sistem Informasi', 'count' => 45, 'percentage' => 30.0],
    ['name' => 'Teknik Komputer', 'count' => 20, 'percentage' => 13.3]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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

    <style>
        /* Reports specific styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            flex-shrink: 0;
        }

        .stat-info h3 {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #374151;
            margin: 0 0 0.25rem 0;
        }

        .stat-positive {
            color: #10b981;
            font-weight: 600;
        }

        .stat-neutral {
            color: #f59e0b;
            font-weight: 600;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .chart-card h3 {
            margin: 0 0 1rem 0;
            color: #374151;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-wrapper {
            height: 300px;
            position: relative;
        }

        .program-distribution {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .distribution-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .distribution-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .prodi-name {
            font-weight: 600;
            color: #374151;
        }

        .prodi-count {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .distribution-bar {
            position: relative;
            height: 24px;
            background: #f3f4f6;
            border-radius: 12px;
            overflow: hidden;
        }

        .distribution-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .distribution-percentage {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
        }

        .export-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .export-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .export-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }

        .export-content {
            flex: 1;
        }

        .export-content h3 {
            margin: 0 0 0.5rem 0;
            color: #374151;
            font-weight: 600;
        }

        .export-content p {
            margin: 0 0 1rem 0;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .export-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .charts-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .export-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0.5rem;
                margin: 0;
            }
            
            .dashboard-header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            
            .dashboard-section {
                margin-bottom: 1.5rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.25rem;
                padding: 0 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
                padding: 0 0.5rem;
            }

            .stat-card {
                padding: 1rem;
                flex-direction: row;
                text-align: left;
                gap: 0.75rem;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
                flex-shrink: 0;
            }

            .stat-number {
                font-size: 1.75rem;
            }
            
            .stat-info h3 {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }
            
            .stat-info small {
                font-size: 0.75rem;
            }

            .charts-container {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.5rem;
            }

            .chart-card {
                padding: 1rem;
                border-radius: 8px;
            }
            
            .chart-card h3 {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .chart-wrapper {
                height: 250px;
            }
            
            .program-distribution {
                gap: 0.75rem;
            }
            
            .distribution-item {
                padding: 0.75rem;
            }
            
            .prodi-name {
                font-size: 0.9rem;
            }
            
            .prodi-count {
                font-size: 0.8rem;
            }
            
            .distribution-percentage {
                font-size: 0.8rem;
            }

            .export-container {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.5rem;
            }

            .export-card {
                padding: 1rem;
                flex-direction: row;
                text-align: left;
                gap: 1rem;
                border-radius: 8px;
            }
            
            .export-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
                flex-shrink: 0;
            }
            
            .export-content {
                flex: 1;
            }
            
            .export-content h3 {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }
            
            .export-content p {
                font-size: 0.85rem;
                margin-bottom: 0.75rem;
            }

            .export-actions {
                justify-content: flex-start;
                gap: 0.5rem;
                flex-wrap: wrap;
            }
            
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.85rem;
                min-height: 40px;
                border-radius: 8px;
                flex: 1;
                min-width: 80px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0.25rem;
            }
            
            .dashboard-header {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.25rem;
            }
            
            .dashboard-section h2 {
                font-size: 1.1rem;
                padding: 0 0.25rem;
            }

            .stats-grid {
                padding: 0 0.25rem;
                gap: 0.5rem;
            }

            .stat-card {
                padding: 0.75rem;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }

            .charts-container {
                padding: 0 0.25rem;
                gap: 0.75rem;
            }

            .chart-card {
                padding: 0.75rem;
                border-radius: 6px;
            }
            
            .chart-card h3 {
                font-size: 1rem;
                margin-bottom: 0.75rem;
            }

            .chart-wrapper {
                height: 200px;
            }
            
            .distribution-item {
                padding: 0.5rem;
            }

            .export-container {
                padding: 0 0.25rem;
                gap: 0.75rem;
            }

            .export-card {
                padding: 0.75rem;
                flex-direction: column;
                text-align: center;
                gap: 0.75rem;
            }
            
            .export-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
                margin: 0 auto;
            }
            
            .export-content h3 {
                font-size: 0.95rem;
            }
            
            .export-content p {
                font-size: 0.8rem;
                margin-bottom: 1rem;
            }

            .export-actions {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
                justify-content: center;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.85rem;
                min-height: 44px;
                flex: none;
                width: 100%;
            }
        }

        @media (max-width: 360px) {
            .dashboard-header h1 {
                font-size: 1.1rem;
            }
            
            .chart-wrapper {
                height: 180px;
            }
        }

        /* Landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .export-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .export-card {
                flex-direction: row;
                text-align: left;
            }
            
            .export-actions {
                flex-direction: row;
                gap: 0.5rem;
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover,
            .stat-card:hover,
            .chart-card:hover,
            .export-card:hover {
                transform: none;
            }
            
            .btn:active {
                transform: scale(0.98);
            }
            
            .stat-card:active,
            .chart-card:active,
            .export-card:active {
                transform: scale(0.98);
            }
        }
    </style>
</body>
</html>