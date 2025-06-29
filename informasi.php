<?php
// Start session
session_start();

// Include database connection
require_once 'koneksi.php';

// Get article ID from URL
$article_id = isset($_GET['article']) ? (int)$_GET['article'] : 0;

if ($article_id > 0) {
    // Get specific article
    $query = "SELECT a.*, u.username as author_name, u.full_name as author_full_name 
              FROM articles a 
              LEFT JOIN users u ON a.author_id = u.id 
              WHERE a.id = $article_id AND a.status = 'published'";
    $result = mysqli_query($conn, $query);
    $article = mysqli_fetch_assoc($result);
    
    if (!$article) {
        header("Location: berita.php");
        exit();
    }
} else {
    // Redirect to news page if no article specified
    header("Location: berita.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - MPD University</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .article-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .article-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .article-title {
            font-size: 2.5rem;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .article-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .article-category {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .article-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #374151;
        }
        
        .article-content p {
            margin-bottom: 1.5rem;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            transition: color 0.3s ease;
        }
        
        .back-button:hover {
            color: #5a67d8;
        }
        
        @media (max-width: 768px) {
            .article-title {
                font-size: 2rem;
            }
            
            .article-meta {
                flex-direction: column;
                gap: 1rem;
            }
            
            .article-container {
                padding: 0 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="article-container">
            <a href="berita.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Berita
            </a>
            
            <article>
                <header class="article-header">
                    <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                    
                    <div class="article-meta">
                        <span class="article-category">
                            <?php echo htmlspecialchars($article['category'] ?? 'Umum'); ?>
                        </span>
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author_full_name'] ?? $article['author_name']); ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('d F Y', strtotime($article['published_at'])); ?></span>
                    </div>
                </header>
                
                <?php if ($article['featured_image']): ?>
                    <img src="assets/images/articles/<?php echo htmlspecialchars($article['featured_image']); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                         class="article-image">
                <?php endif; ?>
                
                <div class="article-content">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
            </article>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
