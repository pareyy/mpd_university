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

// Sample articles data
$articles_data = [
    [
        'id' => 1,
        'title' => 'Pendaftaran Mahasiswa Baru 2024/2025',
        'content' => 'MPD University membuka pendaftaran mahasiswa baru untuk tahun akademik 2024/2025. Pendaftaran dimulai dari tanggal 1 Januari hingga 31 Maret 2024.',
        'category' => 'Pendaftaran',
        'status' => 'Published',
        'featured_image' => 'pendaftaran.jpg',
        'created_at' => '2024-01-15',
        'author' => 'Admin'
    ],
    [
        'id' => 2,
        'title' => 'Seminar Teknologi AI dan Machine Learning',
        'content' => 'Program Studi Teknik Informatika mengadakan seminar nasional tentang perkembangan AI dan Machine Learning di era digital.',
        'category' => 'Event',
        'status' => 'Published',
        'featured_image' => 'seminar-ai.jpg',
        'created_at' => '2024-02-10',
        'author' => 'Admin'
    ],
    [
        'id' => 3,
        'title' => 'Jadwal Ujian Tengah Semester Genap 2023/2024',
        'content' => 'Pengumuman jadwal Ujian Tengah Semester (UTS) untuk semester genap tahun akademik 2023/2024.',
        'category' => 'Akademik',
        'status' => 'Draft',
        'featured_image' => 'uts-schedule.jpg',
        'created_at' => '2024-03-05',
        'author' => 'Admin'
    ],
    [
        'id' => 4,
        'title' => 'Program Magang Industri Semester 6',
        'content' => 'Informasi lengkap mengenai program magang industri untuk mahasiswa semester 6 di berbagai perusahaan teknologi.',
        'category' => 'Magang',
        'status' => 'Published',
        'featured_image' => 'magang.jpg',
        'created_at' => '2024-02-28',
        'author' => 'Admin'
    ]
];

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $message = "Artikel berhasil ditambahkan!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'edit') {
            $message = "Artikel berhasil diperbarui!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'delete') {
            $message = "Artikel berhasil dihapus!";
            $message_type = 'success';
        } elseif ($_POST['action'] == 'publish') {
            $message = "Artikel berhasil dipublikasikan!";
            $message_type = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav_admin.php'; ?>

    <main>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-newspaper"></i> Kelola Artikel</h1>
                <p>Manajemen artikel dan berita untuk halaman informasi</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add Article Form -->
            <div class="dashboard-section">
                <h2><i class="fas fa-plus-circle"></i> Tambah Artikel Baru</h2>
                <div class="form-container">
                    <form method="POST" class="article-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="title">Judul Artikel</label>
                                <input type="text" id="title" name="title" class="form-control" required placeholder="Masukkan judul artikel">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Kategori</label>
                                <select id="category" name="category" class="form-control" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Pendaftaran">Pendaftaran</option>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Event">Event</option>
                                    <option value="Magang">Magang</option>
                                    <option value="Pengumuman">Pengumuman</option>
                                    <option value="Beasiswa">Beasiswa</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Draft">Draft</option>
                                    <option value="Published">Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="featured_image">Gambar Utama</label>
                                <input type="file" id="featured_image" name="featured_image" class="form-control" accept="image/*">
                                <small class="form-text">Format yang didukung: JPG, PNG, GIF (Max: 2MB)</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="content">Konten Artikel</label>
                                <textarea id="content" name="content" class="form-control" rows="10" required placeholder="Tulis konten artikel di sini..."></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Artikel
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Articles List -->
            <div class="dashboard-section">
                <h2><i class="fas fa-list"></i> Daftar Artikel</h2>
                <div class="articles-grid">
                    <?php foreach ($articles_data as $article): ?>
                        <div class="article-card">
                            <div class="article-image">
                                <img src="../assets/img/<?php echo $article['featured_image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" onerror="this.src='../assets/img/default-article.jpg'">
                                <div class="article-status">
                                    <span class="status-badge status-<?php echo strtolower($article['status']); ?>">
                                        <?php echo $article['status']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="article-content">
                                <div class="article-meta">
                                    <span class="category"><?php echo $article['category']; ?></span>
                                    <span class="date"><?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p><?php echo substr(htmlspecialchars($article['content']), 0, 120) . '...'; ?></p>
                                <div class="article-actions">
                                    <button class="btn btn-sm btn-info" onclick="viewArticle(<?php echo $article['id']; ?>)">
                                        <i class="fas fa-eye"></i> Lihat
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editArticle(<?php echo $article['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if ($article['status'] == 'Draft'): ?>
                                        <button class="btn btn-sm btn-success" onclick="publishArticle(<?php echo $article['id']; ?>)">
                                            <i class="fas fa-share"></i> Publish
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteArticle(<?php echo $article['id']; ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function viewArticle(id) {
            // Open article in new tab/window
            window.open('../informasi.php?article=' + id, '_blank');
        }

        function editArticle(id) {
            // Implementation for editing article
            alert('Edit artikel ID: ' + id);
        }

        function publishArticle(id) {
            if (confirm('Apakah Anda yakin ingin mempublikasikan artikel ini?')) {
                // Implementation for publishing article
                alert('Publish artikel ID: ' + id);
            }
        }

        function deleteArticle(id) {
            if (confirm('Apakah Anda yakin ingin menghapus artikel ini? Tindakan ini tidak dapat dibatalkan.')) {
                // Implementation for deleting article
                alert('Hapus artikel ID: ' + id);
            }
        }

        function resetForm() {
            document.querySelector('.article-form').reset();
        }

        // Preview image upload
        document.getElementById('featured_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create preview if needed
                    console.log('Image selected:', file.name);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

    <style>
        /* Article management specific styles */
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-text {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .article-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .article-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .article-card:hover .article-image img {
            transform: scale(1.05);
        }

        .article-status {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .article-content {
            padding: 1.5rem;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .category {
            background: #e0e7ff;
            color: #3730a3;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .date {
            color: #6b7280;
        }

        .article-content h3 {
            font-size: 1.1rem;
            margin: 0 0 1rem 0;
            color: #374151;
            font-weight: 600;
            line-height: 1.3;
        }

        .article-content p {
            color: #6b7280;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .article-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 6px;
            white-space: nowrap;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }        /* Mobile responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }
            
            .articles-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
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

            .form-container {
                padding: 1rem;
                margin: 0 0.5rem 1.5rem 0.5rem;
                border-radius: 8px;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .form-control {
                padding: 1rem;
                font-size: 1rem;
                border-radius: 10px;
            }
            
            .form-control[type="file"] {
                padding: 0.75rem;
            }

            textarea.form-control {
                min-height: 120px;
                resize: vertical;
            }

            .form-actions {
                flex-direction: column;
                gap: 0.75rem;
                margin-top: 1rem;
            }
            
            .btn {
                padding: 1rem;
                font-size: 1rem;
                min-height: 48px;
                border-radius: 10px;
                justify-content: center;
                touch-action: manipulation;
            }

            .articles-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0 0.5rem;
            }
            
            .article-card {
                border-radius: 8px;
            }

            .article-image {
                height: 180px;
            }
            
            .article-content {
                padding: 1rem;
            }
            
            .article-content h3 {
                font-size: 1rem;
                line-height: 1.4;
                margin-bottom: 0.75rem;
            }
            
            .article-content p {
                font-size: 0.9rem;
                line-height: 1.4;
                margin-bottom: 1rem;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }
            
            .status {
                font-size: 0.8rem;
                padding: 0.25rem 0.6rem;
            }
            
            .category {
                font-size: 0.8rem;
                padding: 0.25rem 0.6rem;
            }
            
            .date {
                font-size: 0.85rem;
            }

            .article-actions {
                justify-content: space-between;
                gap: 0.5rem;
            }

            .btn-sm {
                flex: 1;
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
                min-height: 40px;
                text-align: center;
                border-radius: 8px;
            }
            
            .alert {
                margin: 0 0.5rem 1.5rem 0.5rem;
                padding: 1rem;
                border-radius: 8px;
                font-size: 0.9rem;
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

            .form-container {
                padding: 0.75rem;
                margin: 0 0.25rem 1rem 0.25rem;
            }
            
            .form-control {
                padding: 0.875rem;
                font-size: 1rem;
            }
            
            textarea.form-control {
                min-height: 100px;
            }
            
            .btn {
                padding: 0.875rem;
                font-size: 0.9rem;
            }

            .articles-grid {
                padding: 0 0.25rem;
                gap: 0.75rem;
            }
            
            .article-card {
                border-radius: 6px;
            }
            
            .article-image {
                height: 160px;
            }

            .article-content {
                padding: 0.75rem;
            }
            
            .article-content h3 {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }
            
            .article-content p {
                font-size: 0.85rem;
                margin-bottom: 0.75rem;
                -webkit-line-clamp: 2;
            }
            
            .article-meta {
                gap: 0.4rem;
                margin-bottom: 0.75rem;
            }

            .article-actions {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-sm {
                flex: none;
                width: 100%;
                padding: 0.75rem;
                font-size: 0.85rem;
                min-height: 44px;
            }
            
            .status,
            .category {
                font-size: 0.75rem;
                padding: 0.2rem 0.5rem;
            }
            
            .date {
                font-size: 0.8rem;
            }
            
            .alert {
                margin: 0 0.25rem 1rem 0.25rem;
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 360px) {
            .dashboard-header h1 {
                font-size: 1.1rem;
            }
            
            .article-image {
                height: 140px;
            }
            
            .article-content h3 {
                font-size: 0.9rem;
            }
        }

        /* Landscape orientation */
        @media (max-width: 768px) and (orientation: landscape) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }
            
            .form-actions {
                flex-direction: row;
                gap: 1rem;
            }
            
            .articles-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .article-actions {
                flex-direction: row;
                gap: 0.5rem;
            }
            
            .btn-sm {
                flex: 1;
            }
        }

        /* Touch improvements */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover,
            .article-card:hover {
                transform: none;
            }
            
            .btn:active {
                transform: scale(0.98);
            }
            
            .article-card:active {
                transform: scale(0.98);
            }
            
            .form-control:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            }
        }
    </style>
</body>
</html>