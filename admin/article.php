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

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $content = mysqli_real_escape_string($conn, $_POST['content']);
            $category = mysqli_real_escape_string($conn, $_POST['category'] ?? 'Umum');
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $excerpt = mysqli_real_escape_string($conn, substr($content, 0, 200) . '...');
            
            // Check if category column exists, if not add it
            $check_column = "SHOW COLUMNS FROM articles LIKE 'category'";
            $column_result = mysqli_query($conn, $check_column);
            if (mysqli_num_rows($column_result) == 0) {
                $add_column = "ALTER TABLE articles ADD COLUMN category VARCHAR(50) DEFAULT 'Umum' AFTER excerpt";
                mysqli_query($conn, $add_column);
            }
            
            // Handle file upload
            $featured_image = null;
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
                $upload_dir = '../assets/images/articles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                $featured_image = 'article_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $featured_image;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                    // File uploaded successfully
                } else {
                    $featured_image = null;
                }
            }
            
            $insert_query = "INSERT INTO articles (title, content, excerpt, category, author_id, status, featured_image) 
                           VALUES ('$title', '$content', '$excerpt', '$category', '$user_id', '$status', '$featured_image')";
            
            if (mysqli_query($conn, $insert_query)) {
                $message = "Artikel berhasil ditambahkan!";
                $message_type = 'success';
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        } elseif ($_POST['action'] == 'edit') {
            $id = mysqli_real_escape_string($conn, $_POST['article_id']);
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $content = mysqli_real_escape_string($conn, $_POST['content']);
            $category = mysqli_real_escape_string($conn, $_POST['category']);
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $excerpt = mysqli_real_escape_string($conn, substr($content, 0, 200) . '...');
            
            // Handle file upload for edit
            $featured_image_update = '';
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
                $upload_dir = '../assets/images/articles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
                $featured_image = 'article_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $featured_image;
                
                if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
                    $featured_image_update = ", featured_image = '$featured_image'";
                }
            }
            
            $update_query = "UPDATE articles SET 
                           title = '$title', 
                           content = '$content', 
                           excerpt = '$excerpt',
                           category = '$category', 
                           status = '$status'
                           $featured_image_update
                           WHERE id = '$id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "Artikel berhasil diperbarui!";
                $message_type = 'success';
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        } elseif ($_POST['action'] == 'delete') {
            $id = mysqli_real_escape_string($conn, $_POST['article_id']);
            
            // Get image file to delete
            $image_query = "SELECT featured_image FROM articles WHERE id = '$id'";
            $image_result = mysqli_query($conn, $image_query);
            $image_data = mysqli_fetch_assoc($image_result);
            
            $delete_query = "DELETE FROM articles WHERE id = '$id'";
            if (mysqli_query($conn, $delete_query)) {
                // Delete image file if exists
                if ($image_data['featured_image'] && file_exists('../assets/images/articles/' . $image_data['featured_image'])) {
                    unlink('../assets/images/articles/' . $image_data['featured_image']);
                }
                $message = "Artikel berhasil dihapus!";
                $message_type = 'success';
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        } elseif ($_POST['action'] == 'publish') {
            $id = mysqli_real_escape_string($conn, $_POST['article_id']);
            $update_query = "UPDATE articles SET status = 'published' WHERE id = '$id'";
            
            if (mysqli_query($conn, $update_query)) {
                $message = "Artikel berhasil dipublikasikan!";
                $message_type = 'success';
            } else {
                $message = "Error: " . mysqli_error($conn);
                $message_type = 'error';
            }
        }
    }
}

// Get articles from database
$articles_query = "SELECT a.*, u.username as author_name 
                   FROM articles a 
                   LEFT JOIN users u ON a.author_id = u.id 
                   ORDER BY a.created_at DESC";
$articles_result = mysqli_query($conn, $articles_query);
$articles_data = [];
while ($row = mysqli_fetch_assoc($articles_result)) {
    $articles_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - MPD University</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
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
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
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
                <?php if (count($articles_data) > 0): ?>
                    <div class="articles-grid">
                        <?php foreach ($articles_data as $article): ?>
                            <div class="article-card">
                                <?php if ($article['featured_image']): ?>
                                    <div class="article-image">
                                        <img src="../assets/images/articles/<?php echo $article['featured_image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                        <div class="article-status">
                                            <span class="status-badge status-<?php echo $article['status']; ?>">
                                                <?php echo ucfirst($article['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="article-no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="article-content">
                                    <div class="article-meta">
                                        <span class="category">
                                            <?php echo htmlspecialchars($article['category'] ?? 'Umum'); ?>
                                        </span>
                                        <span class="date"><?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                                    </div>
                                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($article['content'], 0, 150)) . '...'; ?></p>
                                    <div class="article-actions">
                                        <button class="btn btn-sm btn-info" onclick="viewArticle(<?php echo $article['id']; ?>)">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="editArticle(<?php echo $article['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if ($article['status'] === 'draft'): ?>
                                            <button class="btn btn-sm btn-success" onclick="publishArticle(<?php echo $article['id']; ?>)">
                                                <i class="fas fa-upload"></i> Publish
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
                <?php else: ?>
                    <div class="no-articles">
                        <i class="fas fa-newspaper"></i>
                        <h3>Belum ada artikel</h3>
                        <p>Mulai buat artikel pertama Anda menggunakan form di atas.</p>
                    </div>
                <?php endif; ?>
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
            // Create edit modal or redirect to edit page
            const article = <?php echo json_encode($articles_data); ?>.find(a => a.id == id);
            if (article) {
                // Fill form with article data
                document.getElementById('title').value = article.title;
                document.getElementById('content').value = article.content;
                document.getElementById('category').value = article.category;
                document.getElementById('status').value = article.status;
                
                // Add hidden field for article ID
                let actionInput = document.querySelector('input[name="action"]');
                actionInput.value = 'edit';
                
                let idInput = document.querySelector('input[name="article_id"]');
                if (!idInput) {
                    idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'article_id';
                    document.querySelector('.article-form').appendChild(idInput);
                }
                idInput.value = id;
                
                // Update submit button text
                const submitBtn = document.querySelector('.article-form button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-edit"></i> Update Artikel';
                
                // Scroll to form
                document.querySelector('.article-form').scrollIntoView({ behavior: 'smooth' });
                document.getElementById('title').focus();
            }
        }

        function publishArticle(id) {
            if (confirm('Apakah Anda yakin ingin mempublikasikan artikel ini?')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="publish">
                    <input type="hidden" name="article_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteArticle(id) {
            if (confirm('Apakah Anda yakin ingin menghapus artikel ini? Tindakan ini tidak dapat dibatalkan.')) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="article_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function resetForm() {
            if (confirm('Apakah Anda yakin ingin mereset form? Semua data yang telah diisi akan hilang.')) {
                document.querySelector('.article-form').reset();
                
                // Reset action to add
                document.querySelector('input[name="action"]').value = 'add';
                
                // Remove article_id if exists
                const idInput = document.querySelector('input[name="article_id"]');
                if (idInput) {
                    idInput.remove();
                }
                
                // Clear image preview if exists
                clearImagePreview();
                
                // Update submit button text
                const submitBtn = document.querySelector('.article-form button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Artikel';
            }
        }

        // Enhanced image preview
        document.getElementById('featured_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.querySelector('.image-preview-container') || createPreviewContainer();
            
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('File harus berupa gambar (JPG, PNG, GIF).');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    showImagePreview(e.target.result, file.name);
                }
                reader.readAsDataURL(file);
            } else {
                clearImagePreview();
            }
        });
        
        function createPreviewContainer() {
            const container = document.createElement('div');
            container.className = 'image-preview-container';
            container.style.cssText = `
                margin-top: 0.75rem;
                padding: 1rem;
                border: 2px dashed #e5e7eb;
                border-radius: 8px;
                background: #f9fafb;
                text-align: center;
            `;
            
            const fileInput = document.getElementById('featured_image');
            fileInput.parentNode.appendChild(container);
            return container;
        }
        
        function showImagePreview(src, filename) {
            const container = document.querySelector('.image-preview-container') || createPreviewContainer();
            container.innerHTML = `
                <div class="image-preview" style="position: relative; display: inline-block;">
                    <img src="${src}" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <button type="button" onclick="clearImagePreview()" style="
                        position: absolute;
                        top: -8px;
                        right: -8px;
                        background: #ef4444;
                        color: white;
                        border: none;
                        border-radius: 50%;
                        width: 24px;
                        height: 24px;
                        cursor: pointer;
                        font-size: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">×</button>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: #6b7280;">${filename}</p>
                </div>
            `;
        }
        
        function clearImagePreview() {
            const container = document.querySelector('.image-preview-container');
            if (container) {
                container.innerHTML = '<p style="color: #9ca3af; margin: 0; font-style: italic;">Tidak ada gambar dipilih</p>';
            }
            document.getElementById('featured_image').value = '';
        }

        // Form validation
        document.querySelector('.article-form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;
            
            if (!title || !content || !category || !status) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return false;
            }
            
            if (title.length < 10) {
                e.preventDefault();
                alert('Judul artikel minimal 10 karakter.');
                return false;
            }
            
            if (content.length < 50) {
                e.preventDefault();
                alert('Konten artikel minimal 50 karakter.');
                return false;
            }
            
            // Show loading state on submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>