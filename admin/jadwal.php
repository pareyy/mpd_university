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

// Get mata kuliah data for dropdown
$mata_kuliah_query = "SELECT mk.id, mk.kode_mk, mk.nama_mk, d.nama as dosen_nama 
                      FROM mata_kuliah mk 
                      LEFT JOIN dosen d ON mk.dosen_id = d.id 
                      ORDER BY mk.kode_mk";
$mata_kuliah_result = mysqli_query($conn, $mata_kuliah_query);
$mata_kuliah_list = [];
while ($row = mysqli_fetch_assoc($mata_kuliah_result)) {
    $mata_kuliah_list[] = $row;
}

// Get dosen data for dropdown
$dosen_query = "SELECT d.id, d.nama, d.nidn FROM dosen d ORDER BY d.nama";
$dosen_result = mysqli_query($conn, $dosen_query);
$dosen_list = [];
while ($row = mysqli_fetch_assoc($dosen_result)) {
    $dosen_list[] = $row;
}

// Days of the week
$hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

// Time slots
$jam_list = [
    '08:00-10:00', '10:00-12:00', '13:00-15:00', '15:00-17:00'
];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $mata_kuliah_id = mysqli_real_escape_string($conn, $_POST['mata_kuliah']);
            $hari = mysqli_real_escape_string($conn, $_POST['hari']);
            $jam_parts = explode('-', $_POST['jam']);
            $jam_mulai = mysqli_real_escape_string($conn, $jam_parts[0]);
            $jam_selesai = mysqli_real_escape_string($conn, $jam_parts[1]);
            $ruang = mysqli_real_escape_string($conn, $_POST['ruangan']);
            $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
            
            // Check for conflicts
            $conflict_query = "SELECT * FROM jadwal 
                              WHERE hari = '$hari' 
                              AND ((jam_mulai <= '$jam_mulai' AND jam_selesai > '$jam_mulai') 
                                   OR (jam_mulai < '$jam_selesai' AND jam_selesai >= '$jam_selesai')
                                   OR (jam_mulai >= '$jam_mulai' AND jam_selesai <= '$jam_selesai'))
                              AND (ruang = '$ruang' OR mata_kuliah_id IN (
                                  SELECT id FROM mata_kuliah WHERE dosen_id = (
                                      SELECT dosen_id FROM mata_kuliah WHERE id = '$mata_kuliah_id'
                                  )
                              ))";
            $conflict_result = mysqli_query($conn, $conflict_query);
            
            if (mysqli_num_rows($conflict_result) > 0) {
                $message = "Jadwal bentrok! Periksa kembali waktu, ruangan, atau dosen.";
                $message_type = 'error';
            } else {
                $insert_query = "INSERT INTO jadwal (mata_kuliah_id, hari, jam_mulai, jam_selesai, ruang, kelas) 
                               VALUES ('$mata_kuliah_id', '$hari', '$jam_mulai', '$jam_selesai', '$ruang', '$kelas')";
                
                if (mysqli_query($conn, $insert_query)) {
                    $message = "Jadwal berhasil ditambahkan!";
                    $message_type = 'success';
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] == 'edit' && isset($_POST['jadwal_id'])) {
            $jadwal_id = (int)$_POST['jadwal_id'];
            $mata_kuliah_id = mysqli_real_escape_string($conn, $_POST['mata_kuliah']);
            $hari = mysqli_real_escape_string($conn, $_POST['hari']);
            $jam_parts = explode('-', $_POST['jam']);
            $jam_mulai = mysqli_real_escape_string($conn, $jam_parts[0]);
            $jam_selesai = mysqli_real_escape_string($conn, $jam_parts[1]);
            $ruang = mysqli_real_escape_string($conn, $_POST['ruangan']);
            $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
            
            $update_query = "UPDATE jadwal SET 
                           mata_kuliah_id = '$mata_kuliah_id',
                           hari = '$hari',
                           jam_mulai = '$jam_mulai',
                           jam_selesai = '$jam_selesai',
                           ruang = '$ruang',
                           kelas = '$kelas'
                           WHERE id = $jadwal_id";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "Jadwal berhasil diperbarui!";
                $message_type = 'success';
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['jadwal_id'])) {
            $jadwal_id = (int)$_POST['jadwal_id'];
            
            $delete_query = "DELETE FROM jadwal WHERE id = $jadwal_id";
            
            if (mysqli_query($conn, $delete_query)) {
                $message = "Jadwal berhasil dihapus!";
                $message_type = 'success';
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        }
    }
}

// Get schedule data from database
$jadwal_query = "SELECT j.*, mk.kode_mk, mk.nama_mk, d.nama as dosen_nama,
                        COUNT(k.mahasiswa_id) as jumlah_mahasiswa
                 FROM jadwal j
                 LEFT JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
                 LEFT JOIN dosen d ON mk.dosen_id = d.id
                 LEFT JOIN kelas k ON mk.id = k.mata_kuliah_id
                 GROUP BY j.id
                 ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai";
$jadwal_result = mysqli_query($conn, $jadwal_query);
$jadwal_data = [];
while ($row = mysqli_fetch_assoc($jadwal_result)) {
    $jadwal_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-calendar-alt"></i> Jadwal Kuliah</h1>
                <p>Manajemen jadwal perkuliahan dan pengaturan waktu</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Schedule Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Tambah Jadwal Baru</h2>
                <div class="form-container">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="mata_kuliah">Mata Kuliah</label>
                                <select id="mata_kuliah" name="mata_kuliah" class="form-control" required>
                                    <option value="">Pilih Mata Kuliah</option>
                                    <?php foreach ($mata_kuliah_list as $mk): ?>
                                        <option value="<?php echo $mk['id']; ?>">
                                            <?php echo htmlspecialchars($mk['kode_mk'] . ' - ' . $mk['nama_mk'] . ' (' . $mk['dosen_nama'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dosen">Dosen Pengampu</label>
                                <select id="dosen" name="dosen" class="form-control" disabled>
                                    <option value="">Pilih mata kuliah terlebih dahulu</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="hari">Hari</label>
                                <select id="hari" name="hari" class="form-control" required>
                                    <option value="">Pilih Hari</option>
                                    <?php foreach ($hari_list as $hari): ?>
                                        <option value="<?php echo $hari; ?>"><?php echo $hari; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="jam">Waktu</label>
                                <select id="jam" name="jam" class="form-control" required>
                                    <option value="">Pilih Waktu</option>
                                    <?php foreach ($jam_list as $jam): ?>
                                        <option value="<?php echo $jam; ?>"><?php echo $jam; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ruangan">Ruangan</label>
                                <select id="ruangan" name="ruangan" class="form-control" required>
                                    <option value="">Pilih Ruangan</option>
                                    <option value="Lab. Komputer 1">Lab. Komputer 1</option>
                                    <option value="Lab. Komputer 2">Lab. Komputer 2</option>
                                    <option value="Lab. Jaringan">Lab. Jaringan</option>
                                    <option value="Ruang 201">Ruang 201</option>
                                    <option value="Ruang 301">Ruang 301</option>
                                    <option value="Ruang 401">Ruang 401</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="kelas">Kelas</label>
                                <select id="kelas" name="kelas" class="form-control" required>
                                    <option value="">Pilih Kelas</option>
                                    <option value="TI-3A">TI-3A</option>
                                    <option value="TI-3B">TI-3B</option>
                                    <option value="TI-5A">TI-5A</option>
                                    <option value="TI-5B">TI-5B</option>
                                    <option value="SI-4A">SI-4A</option>
                                    <option value="SI-4B">SI-4B</option>
                                    <option value="SI-6A">SI-6A</option>
                                    <option value="TK-5B">TK-5B</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Jadwal
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Schedule Calendar View -->
            <div class="dashboard-section">
                <h2><i class="fas fa-calendar-week"></i> Jadwal Mingguan</h2>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="calendar-nav">
                            <button class="btn btn-outline" onclick="previousWeek()">
                                <i class="fas fa-chevron-left"></i> Minggu Sebelumnya
                            </button>
                            <h3 id="week-display">Minggu: 23 - 29 Juni 2025</h3>
                            <button class="btn btn-outline" onclick="nextWeek()">
                                Minggu Selanjutnya <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="calendar-grid">
                        <div class="time-column">
                            <div class="time-header">
                                <i class="fas fa-clock"></i> Waktu
                            </div>
                            <?php foreach ($jam_list as $jam): ?>
                                <div class="time-slot">
                                    <strong><?php echo $jam; ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php foreach ($hari_list as $hari): ?>
                            <div class="day-column">
                                <div class="day-header">
                                    <i class="fas fa-calendar-day"></i> <?php echo $hari; ?>
                                </div>
                                <?php 
                                for ($i = 0; $i < count($jam_list); $i++) {
                                    $found = false;
                                    foreach ($jadwal_data as $jadwal) {
                                        $jadwal_time = date('H:i', strtotime($jadwal['jam_mulai'])) . '-' . date('H:i', strtotime($jadwal['jam_selesai']));
                                        if ($jadwal['hari'] === $hari && $jadwal_time === $jam_list[$i]) {
                                            // Determine schedule type for styling
                                            $schedule_class = '';
                                            if (strpos($jadwal['kelas'], 'TI') !== false) {
                                                $schedule_class = 'ti-schedule';
                                            } elseif (strpos($jadwal['kelas'], 'SI') !== false) {
                                                $schedule_class = 'si-schedule';
                                            } elseif (strpos($jadwal['kelas'], 'TK') !== false) {
                                                $schedule_class = 'tk-schedule';
                                            }
                                            
                                            echo '<div class="schedule-item ' . $schedule_class . '" onclick="showScheduleDetail(' . $jadwal['id'] . ')" title="Klik untuk detail">';
                                            echo '<div class="schedule-title">' . htmlspecialchars($jadwal['kode_mk']) . '</div>';
                                            echo '<div class="schedule-title">' . htmlspecialchars($jadwal['nama_mk']) . '</div>';
                                            echo '<div class="schedule-info"><i class="fas fa-user-tie"></i> ' . htmlspecialchars($jadwal['dosen_nama']) . '</div>';
                                            echo '<div class="schedule-room"><i class="fas fa-door-open"></i> ' . htmlspecialchars($jadwal['ruang']) . '</div>';
                                            echo '<div class="schedule-class"><i class="fas fa-users"></i> ' . htmlspecialchars($jadwal['kelas']) . '</div>';
                                            echo '</div>';
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        echo '<div class="empty-slot" onclick="addScheduleToSlot(\'' . $hari . '\', \'' . $jam_list[$i] . '\')" title="Klik untuk menambah jadwal"></div>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Calendar Legend -->
                    <div class="calendar-legend" style="padding: 1rem 2rem; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <h4 style="margin: 0 0 0.75rem 0; color: #374151; font-size: 0.9rem; font-weight: 600;">
                            <i class="fas fa-info-circle"></i> Keterangan:
                        </h4>
                        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 16px; height: 16px; background: linear-gradient(135deg, #f0f7ff 0%, #e6f3ff 100%); border: 1px solid #3b82f6; border-radius: 4px;"></div>
                                <span style="font-size: 0.8rem; color: #64748b;">Teknik Informatika</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 16px; height: 16px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #10b981; border-radius: 4px;"></div>
                                <span style="font-size: 0.8rem; color: #64748b;">Sistem Informasi</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 16px; height: 16px; background: linear-gradient(135deg, #fef7f0 0%, #fed7aa 100%); border: 1px solid #f97316; border-radius: 4px;"></div>
                                <span style="font-size: 0.8rem; color: #64748b;">Teknik Komputer</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 16px; height: 16px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px;"></div>
                                <span style="font-size: 0.8rem; color: #64748b;">Slot Kosong</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule List -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Jadwal</h2>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mata Kuliah</th>
                                    <th>Dosen</th>
                                    <th>Hari</th>
                                    <th>Waktu</th>
                                    <th>Ruangan</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwal_data as $jadwal): ?>
                                    <tr>
                                        <td>
                                            <div class="course-info">
                                                <strong><?php echo htmlspecialchars($jadwal['nama_mk']); ?></strong>
                                                <small><?php echo htmlspecialchars($jadwal['kode_mk']); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($jadwal['dosen_nama']); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                        <td><?php echo htmlspecialchars(date('H:i', strtotime($jadwal['jam_mulai'])) . ' - ' . date('H:i', strtotime($jadwal['jam_selesai']))); ?></td>
                                        <td><?php echo htmlspecialchars($jadwal['ruang']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($jadwal['kelas']); ?></span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-warning" onclick="editSchedule(<?php echo $jadwal['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $jadwal['id']; ?>)">
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
        const jadwalData = <?php echo json_encode($jadwal_data); ?>;
        const mataKuliahData = <?php echo json_encode($mata_kuliah_list); ?>;

        // Update dosen dropdown when mata kuliah is selected
        document.getElementById('mata_kuliah').addEventListener('change', function() {
            const selectedMK = mataKuliahData.find(mk => mk.id == this.value);
            const dosenSelect = document.getElementById('dosen');
            
            dosenSelect.innerHTML = '<option value="">Pilih mata kuliah terlebih dahulu</option>';
            
            if (selectedMK) {
                dosenSelect.innerHTML = `<option value="${selectedMK.dosen_nama}">${selectedMK.dosen_nama}</option>`;
                dosenSelect.disabled = false;
            } else {
                dosenSelect.disabled = true;
            }
        });

        function editSchedule(id) {
            const jadwal = jadwalData.find(item => item.id == id);
            
            if (jadwal) {
                // Fill form with existing data
                document.getElementById('mata_kuliah').value = jadwal.mata_kuliah_id;
                document.getElementById('mata_kuliah').dispatchEvent(new Event('change'));
                document.getElementById('hari').value = jadwal.hari;
                document.getElementById('jam').value = jadwal.jam_mulai.substring(0,5) + '-' + jadwal.jam_selesai.substring(0,5);
                document.getElementById('ruangan').value = jadwal.ruang;
                document.getElementById('kelas').value = jadwal.kelas;
                
                // Change form action to edit
                document.querySelector('input[name="action"]').value = 'edit';
                
                // Add or update hidden jadwal_id input
                let jadwalIdInput = document.querySelector('input[name="jadwal_id"]');
                if (!jadwalIdInput) {
                    jadwalIdInput = document.createElement('input');
                    jadwalIdInput.type = 'hidden';
                    jadwalIdInput.name = 'jadwal_id';
                    document.querySelector('form').appendChild(jadwalIdInput);
                }
                jadwalIdInput.value = id;
                
                // Change button text
                document.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save"></i> Update Jadwal';
                
                // Scroll to form
                document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
            }
        }

        function deleteSchedule(id) {
            const jadwal = jadwalData.find(item => item.id == id);
            if (jadwal && confirm(`Apakah Anda yakin ingin menghapus jadwal "${jadwal.nama_mk}"?`)) {
                // Create a form to submit delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="jadwal_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetForm() {
            document.querySelector('.schedule-form').reset();
            document.querySelector('input[name="action"]').value = 'add';
            document.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-plus"></i> Tambah Jadwal';
            
            // Remove edit id if exists
            const editId = document.querySelector('input[name="jadwal_id"]');
            if (editId) {
                editId.remove();
            }
            
            // Reset dosen dropdown
            document.getElementById('dosen').disabled = true;
            document.getElementById('dosen').innerHTML = '<option value="">Pilih mata kuliah terlebih dahulu</option>';
        }

        function previousWeek() {
            // Get current week display
            const weekDisplay = document.getElementById('week-display');
            const currentText = weekDisplay.textContent;
            
            // For now, just update the display with a notification
            showNotification('Navigasi ke minggu sebelumnya', 'info');
            
            // In a real implementation, you would:
            // 1. Calculate previous week dates
            // 2. Update the week display
            // 3. Fetch new schedule data from server
            // 4. Update the calendar grid
        }

        function nextWeek() {
            // Get current week display
            const weekDisplay = document.getElementById('week-display');
            const currentText = weekDisplay.textContent;
            
            // For now, just update the display with a notification
            showNotification('Navigasi ke minggu selanjutnya', 'info');
            
            // In a real implementation, you would:
            // 1. Calculate next week dates
            // 2. Update the week display
            // 3. Fetch new schedule data from server
            // 4. Update the calendar grid
        }

        function showScheduleDetail(id) {
            const jadwal = jadwalData.find(item => item.id == id);
            
            if (jadwal) {
                const detailHtml = `
                    <div style="padding: 1rem;">
                        <h3 style="margin: 0 0 1rem 0; color: #374151;">${jadwal.nama_mk}</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <p style="margin: 0.5rem 0;"><strong>Kode MK:</strong> ${jadwal.kode_mk}</p>
                                <p style="margin: 0.5rem 0;"><strong>Dosen:</strong> ${jadwal.dosen_nama}</p>
                                <p style="margin: 0.5rem 0;"><strong>Kelas:</strong> ${jadwal.kelas}</p>
                            </div>
                            <div>
                                <p style="margin: 0.5rem 0;"><strong>Hari:</strong> ${jadwal.hari}</p>
                                <p style="margin: 0.5rem 0;"><strong>Waktu:</strong> ${jadwal.jam_mulai.substring(0,5)} - ${jadwal.jam_selesai.substring(0,5)}</p>
                                <p style="margin: 0.5rem 0;"><strong>Ruangan:</strong> ${jadwal.ruang}</p>
                            </div>
                        </div>
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0.5rem 0;"><strong>Mahasiswa Terdaftar:</strong> ${jadwal.jumlah_mahasiswa || 0} orang</p>
                        </div>
                        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                            <button onclick="editSchedule(${jadwal.id}); closeModal();" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteSchedule(${jadwal.id}); closeModal();" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                `;
                
                showModal('Detail Jadwal', detailHtml);
            }
        }

        function addScheduleToSlot(hari, jam) {
            // Fill form with selected day and time
            document.getElementById('hari').value = hari;
            document.getElementById('jam').value = jam;
            
            // Scroll to form
            document.querySelector('.form-container').scrollIntoView({ behavior: 'smooth' });
            
            // Highlight the form
            const formContainer = document.querySelector('.form-container');
            formContainer.style.border = '2px solid #667eea';
            formContainer.style.backgroundColor = '#f0f7ff';
            
            setTimeout(() => {
                formContainer.style.border = '';
                formContainer.style.backgroundColor = '';
            }, 3000);
            
            showNotification(`Form telah diisi untuk ${hari}, ${jam}. Silakan lengkapi data lainnya.`, 'info');
        }

        function showModal(title, content) {
            // Create modal if it doesn't exist
            let modal = document.getElementById('scheduleModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'scheduleModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content" style="background: white; margin: 5% auto; padding: 0; border-radius: 12px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                        <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem 2rem; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 1.25rem;" id="modalTitle"></h3>
                            <span class="close" onclick="closeModal()" style="font-size: 2rem; cursor: pointer; background: none; border: none; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; transition: background 0.3s ease;">&times;</span>
                        </div>
                        <div class="modal-body" id="modalBody"></div>
                    </div>
                `;
                modal.style.cssText = `
                    display: none;
                    position: fixed;
                    z-index: 1000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.6);
                    backdrop-filter: blur(4px);
                `;
                document.body.appendChild(modal);
                
                // Close modal when clicking outside
                modal.onclick = function(event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                };
            }
            
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalBody').innerHTML = content;
            modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `notification alert-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#d1fae5' : type === 'error' ? '#fee2e2' : '#dbeafe'};
                color: ${type === 'success' ? '#065f46' : type === 'error' ? '#991b1b' : '#1e40af'};
                border: 1px solid ${type === 'success' ? '#a7f3d0' : type === 'error' ? '#fca5a5' : '#93c5fd'};
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                z-index: 1001;
                max-width: 400px;
                animation: slideInNotification 0.3s ease-out;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: inherit; margin-left: auto; padding: 0 0.25rem;">&times;</button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        // Add CSS for notification animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInNotification {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            .close:hover {
                background: rgba(255, 255, 255, 0.2) !important;
            }
        `;
        document.head.appendChild(style);

        // Initialize calendar interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add tooltips to schedule items
            const scheduleItems = document.querySelectorAll('.schedule-item');
            scheduleItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.zIndex = '10';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.zIndex = '1';
                });
            });
            
            // Add keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'ArrowLeft') {
                    e.preventDefault();
                    previousWeek();
                } else if (e.ctrlKey && e.key === 'ArrowRight') {
                    e.preventDefault();
                    nextWeek();
                } else if (e.key === 'Escape') {
                    closeModal();
                }
            });
            
            // Update current week display with proper date
            updateCurrentWeek();
        });
        
        function updateCurrentWeek() {
            const now = new Date();
            const startOfWeek = new Date(now);
            startOfWeek.setDate(now.getDate() - now.getDay() + 1); // Monday
            
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6); // Sunday
            
            const formatDate = (date) => {
                return date.getDate().toString().padStart(2, '0') + ' ' + 
                       ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
                        'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'][date.getMonth()] + ' ' + 
                       date.getFullYear();
            };
            
            const weekDisplay = document.getElementById('week-display');
            if (weekDisplay) {
                weekDisplay.textContent = `Minggu: ${formatDate(startOfWeek)} - ${formatDate(endOfWeek)}`;
            }
        }
        
        // Enhanced form validation
        function validateScheduleForm() {
            const form = document.querySelector('.schedule-form');
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#ef4444';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e5e7eb';
                }
            });
            
            if (!isValid) {
                showNotification('Mohon lengkapi semua field yang wajib diisi', 'error');
            }
            
            return isValid;
        }
        
        // Add form validation to submit button
        document.querySelector('.schedule-form').addEventListener('submit', function(e) {
            if (!validateScheduleForm()) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>