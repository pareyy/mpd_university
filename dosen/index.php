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
$query = "SELECT u.*, d.id as dosen_id, d.bidang_keahlian 
          FROM users u 
          LEFT JOIN dosen d ON u.id = d.user_id 
          WHERE u.id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$dosen_id = $user['dosen_id'];

// Get statistics from database
$stats = ['mata_kuliah' => 0, 'mahasiswa' => 0, 'jadwal_hari_ini' => 0, 'tugas_pending' => 0];

if ($dosen_id) {
    // Get mata kuliah count
    $mk_query = "SELECT COUNT(*) as total FROM mata_kuliah WHERE dosen_id = '$dosen_id'";
    $mk_result = mysqli_query($conn, $mk_query);
    $mk_count = mysqli_fetch_assoc($mk_result);
    $stats['mata_kuliah'] = $mk_count['total'];

    // Get total mahasiswa
    $mhs_query = "SELECT COUNT(DISTINCT k.mahasiswa_id) as total 
                  FROM mata_kuliah mk 
                  LEFT JOIN kelas k ON mk.id = k.mata_kuliah_id 
                  WHERE mk.dosen_id = '$dosen_id'";
    $mhs_result = mysqli_query($conn, $mhs_query);
    $mhs_count = mysqli_fetch_assoc($mhs_result);
    $stats['mahasiswa'] = $mhs_count['total'];

    // Get today's schedule
    $today = date('l');
    $hari_indonesia = [
        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
    ];
    $today_indo = $hari_indonesia[$today] ?? '';
    
    if ($today_indo) {
        $today_query = "SELECT COUNT(*) as total 
                       FROM jadwal j 
                       LEFT JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id 
                       WHERE mk.dosen_id = '$dosen_id' AND j.hari = '$today_indo'";
        $today_result = mysqli_query($conn, $today_query);
        $today_count = mysqli_fetch_assoc($today_result);
        $stats['jadwal_hari_ini'] = $today_count['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/dosen.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav_dosen.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Dosen</h1>
                <p>Selamat datang, <strong><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></strong></p>
            </div>

            <!-- Dashboard Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Mata Kuliah</h3>
                        <p class="stat-number"><?php echo $stats['mata_kuliah']; ?></p>
                        <small>Total mata kuliah yang diampu</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Mahasiswa</h3>
                        <p class="stat-number"><?php echo $stats['mahasiswa']; ?></p>
                        <small>Mahasiswa yang mengikuti kelas</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Jadwal Hari Ini</h3>
                        <p class="stat-number"><?php echo $stats['jadwal_hari_ini']; ?></p>
                        <small>Kelas yang harus diajar</small>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tugas Pending</h3>
                        <p class="stat-number"><?php echo $stats['tugas_pending']; ?></p>
                        <small>Tugas yang perlu dinilai</small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <h2><i class="fas fa-bolt"></i> Aksi Cepat</h2>
                <div class="quick-actions">
                    <a href="mata_kuliah.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="action-content">
                            <h3>Kelola Mata Kuliah</h3>
                            <p>Tambah, edit, atau hapus mata kuliah</p>
                        </div>
                    </a>

                    <a href="jadwal.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="action-content">
                            <h3>Lihat Jadwal</h3>
                            <p>Cek jadwal mengajar minggu ini</p>
                        </div>
                    </a>

                    <a href="nilai.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="action-content">
                            <h3>Input Nilai</h3>
                            <p>Masukkan nilai mahasiswa</p>
                        </div>
                    </a>

                    <a href="absensi.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="action-content">
                            <h3>Absensi</h3>
                            <p>Kelola absensi mahasiswa</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activities and Schedule -->
            <div class="dashboard-row">
                <div class="dashboard-section activities-section">
                    <h2><i class="fas fa-clock"></i> Aktivitas Terbaru</h2>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Mata kuliah baru ditambahkan</h4>
                                <p>Pemrograman Web Lanjut - 3 SKS</p>
                                <small>2 jam yang lalu</small>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Nilai mahasiswa diperbarui</h4>
                                <p>Database - Kelas A</p>
                                <small>5 jam yang lalu</small>
                            </div>
                        </div>

                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <h4>Absensi kelas selesai</h4>
                                <p>Algoritma dan Struktur Data</p>
                                <small>1 hari yang lalu</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Schedule -->
                <div class="dashboard-section schedule-section">
                    <h2><i class="fas fa-calendar-week"></i> Jadwal Mendatang</h2>
                    <div class="schedule-list">
                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time">08:00</span>
                                <span class="date">Senin</span>
                            </div>
                            <div class="schedule-content">
                                <h4>Pemrograman Web Lanjut</h4>
                                <p><i class="fas fa-map-marker-alt"></i> Ruang Lab 1</p>
                                <span class="class-info">Kelas A - 30 mahasiswa</span>
                            </div>
                        </div>

                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time">10:00</span>
                                <span class="date">Senin</span>
                            </div>
                            <div class="schedule-content">
                                <h4>Database</h4>
                                <p><i class="fas fa-map-marker-alt"></i> Ruang 201</p>
                                <span class="class-info">Kelas B - 25 mahasiswa</span>
                            </div>
                        </div>

                        <div class="schedule-item">
                            <div class="schedule-time">
                                <span class="time">13:00</span>
                                <span class="date">Selasa</span>
                            </div>
                            <div class="schedule-content">
                                <h4>Algoritma dan Struktur Data</h4>
                                <p><i class="fas fa-map-marker-alt"></i> Ruang 105</p>
                                <span class="class-info">Kelas C - 35 mahasiswa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>