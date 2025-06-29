<?php
// Include database connection
require_once 'koneksi.php';

// Get article ID from URL
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get article data
$article_query = "SELECT * FROM berita_akademik WHERE id = $article_id AND status = 'published'";
$article_result = mysqli_query($conn, $article_query);
$article = mysqli_fetch_assoc($article_result);

// If article not found, redirect to akademik page
if (!$article) {
    header("Location: akademik.php");
    exit();
}

// Function to format Indonesian date
function formatIndonesianDate($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

// Get related articles
$related_query = "SELECT id, judul, ringkasan, tanggal, gambar FROM berita_akademik 
                  WHERE id != $article_id AND status = 'published' 
                  ORDER BY tanggal DESC LIMIT 3";
$related_result = mysqli_query($conn, $related_query);
$related_articles = [];
while ($row = mysqli_fetch_assoc($related_result)) {
    $related_articles[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['judul']); ?> - MPD University</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/akademik.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="container">
            <nav class="breadcrumb">
                <a href="index.php">Beranda</a>
                <i class="fa-solid fa-chevron-right"></i>
                <a href="akademik.php">Informasi Akademik</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>Berita</span>
            </nav>

            <article class="article-detail">
                <header class="article-header">
                    <h1><?php echo htmlspecialchars($article['judul']); ?></h1>
                    <div class="article-meta">
                        <span><i class="fa-solid fa-calendar"></i> <?php echo formatIndonesianDate($article['tanggal']); ?></span>
                        <span><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($article['penulis']); ?></span>
                    </div>
                </header>

                <?php if ($article['gambar']): ?>
                <div class="article-featured-image">
                    <img src="assets/images/berita/<?php echo htmlspecialchars($article['gambar']); ?>" 
                         alt="<?php echo htmlspecialchars($article['judul']); ?>"
                         onerror="this.src='assets/img/blank.jpg'">
                </div>
                <?php endif; ?>

                <div class="article-body">
                    <?php echo nl2br(htmlspecialchars($article['isi'])); ?>
                </div>

                <div class="article-share">
                    <h4>Bagikan artikel ini:</h4>
                    <div class="share-buttons">
                        <a href="#" class="share-btn facebook" onclick="shareToFacebook()">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="#" class="share-btn twitter" onclick="shareToTwitter()">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="#" class="share-btn whatsapp" onclick="shareToWhatsApp()">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
            </article>

            <?php if (!empty($related_articles)): ?>
            <section class="related-articles">
                <h2>Berita Terkait</h2>
                <div class="related-grid">
                    <?php foreach ($related_articles as $related): ?>
                    <div class="related-card">
                        <div class="related-image">
                            <img src="<?php echo $related['gambar'] ? 'assets/images/berita/' . htmlspecialchars($related['gambar']) : 'assets/img/blank.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['judul']); ?>"
                                 onerror="this.src='assets/img/blank.jpg'">
                        </div>
                        <div class="related-content">
                            <h3><a href="berita.php?id=<?php echo $related['id']; ?>"><?php echo htmlspecialchars($related['judul']); ?></a></h3>
                            <p><?php echo htmlspecialchars(substr($related['ringkasan'], 0, 100)) . '...'; ?></p>
                            <small><?php echo formatIndonesianDate($related['tanggal']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <div class="back-to-list">
                <a href="akademik.php" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Informasi Akademik
                </a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        function shareToFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }

        function shareToTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent(document.title);
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400');
        }

        function shareToWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent(document.title);
            window.open(`https://wa.me/?text=${text} ${url}`, '_blank');
        }
    </script>
</body>
</html>
