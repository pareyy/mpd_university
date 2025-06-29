<?php
// Include database connection
require_once 'koneksi.php';

// Get pengumuman data
$pengumuman_query = "SELECT * FROM pengumuman ORDER BY tanggal DESC LIMIT 5";
$pengumuman_result = mysqli_query($conn, $pengumuman_query);
$pengumuman_data = [];
while ($row = mysqli_fetch_assoc($pengumuman_result)) {
    $pengumuman_data[] = $row;
}

// Get kalender akademik data
$kalender_query = "SELECT * FROM kalender_akademik WHERE tanggal >= CURDATE() ORDER BY tanggal ASC LIMIT 8";
$kalender_result = mysqli_query($conn, $kalender_query);
$kalender_data = [];
while ($row = mysqli_fetch_assoc($kalender_result)) {
    $kalender_data[] = $row;
}

// Get berita akademik data
$berita_query = "SELECT * FROM berita_akademik WHERE status = 'published' ORDER BY tanggal DESC";
$berita_result = mysqli_query($conn, $berita_query);
$berita_data = [];
while ($row = mysqli_fetch_assoc($berita_result)) {
    $berita_data[] = $row;
}

// Function to format Indonesian date
function formatIndonesianDate($date) {
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

function formatShortDate($date) {
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp)];
    return ['day' => $day, 'month' => $month];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Akademik - MPD University</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/akademik.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="container">
            <h1 class="text-center mb-4">Informasi Akademik</h1>
            
            <section class="info-section">
                <h2 class="section-title"><i class="fa-solid fa-bullhorn mr-2"></i> Pengumuman Penting</h2>
                
                <?php foreach ($pengumuman_data as $pengumuman): ?>
                <div class="announcement-card">
                    <h3 class="announcement-title"><?php echo htmlspecialchars($pengumuman['judul']); ?></h3>
                    <div class="announcement-date">
                        <i class="fa-solid fa-calendar"></i> <?php echo formatIndonesianDate($pengumuman['tanggal']); ?>
                    </div>
                    <div class="announcement-content">
                        <?php 
                        // Convert markdown-style formatting to HTML
                        $content = htmlspecialchars($pengumuman['isi']);
                        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
                        $content = nl2br($content);
                        echo $content;
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($pengumuman_data)): ?>
                <div class="announcement-card">
                    <h3 class="announcement-title">Tidak ada pengumuman saat ini</h3>
                    <div class="announcement-content">
                        <p>Silakan periksa kembali nanti untuk pengumuman terbaru.</p>
                    </div>
                </div>
                <?php endif; ?>
            </section>
            
            <section class="info-section">
                <h2 class="section-title"><i class="fa-solid fa-calendar-alt mr-2"></i> Kalender Akademik</h2>
                
                <?php foreach ($kalender_data as $kalender): ?>
                <?php $dateFormat = formatShortDate($kalender['tanggal']); ?>
                <div class="calendar-item">
                    <div class="calendar-date">
                        <span class="day"><?php echo $dateFormat['day']; ?></span>
                        <span class="month"><?php echo $dateFormat['month']; ?></span>
                    </div>
                    <div class="calendar-info">
                        <h4><?php echo htmlspecialchars($kalender['judul']); ?></h4>
                        <p><?php echo htmlspecialchars($kalender['deskripsi']); ?></p>
                        <span class="calendar-category"><?php echo ucfirst($kalender['kategori']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($kalender_data)): ?>
                <div class="calendar-item">
                    <div class="calendar-date">
                        <span class="day">-</span>
                        <span class="month">-</span>
                    </div>
                    <div class="calendar-info">
                        <h4>Tidak ada agenda mendatang</h4>
                        <p>Silakan periksa kembali nanti untuk agenda akademik terbaru.</p>
                    </div>
                </div>
                <?php endif; ?>
            </section>
            
            <section class="info-section">
                <h2 class="section-title"><i class="fa-solid fa-newspaper mr-2"></i> Berita Akademik Terbaru</h2>
                
                <div class="articles-container" id="articlesContainer">
                    <!-- Articles will be loaded dynamically -->
                </div>
                
                <div class="article-navigation">
                    <button id="prevButton" class="nav-button" disabled>
                        <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                    </button>
                    <div class="page-indicator">
                        Halaman &nbsp;<span id="currentPage">1</span> / <span id="totalPages">1</span>
                    </div>
                    <button id="nextButton" class="nav-button">
                        Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Convert PHP array to JavaScript
        const articles = <?php echo json_encode(array_map(function($item) {
            return [
                'id' => $item['id'],
                'title' => $item['judul'],
                'date' => formatIndonesianDate($item['tanggal']),
                'image' => $item['gambar'] ? 'assets/images/berita/' . $item['gambar'] : 'assets/img/blank.jpg',
                'excerpt' => $item['ringkasan']
            ];
        }, $berita_data)); ?>;

        const articlesPerPage = 3;
        let currentPage = 1;
        const totalPages = Math.ceil(articles.length / articlesPerPage);

        document.getElementById('totalPages').textContent = totalPages;

        function createArticleCard(article) {
            return `
                <div class="article-card">
                    <div class="article-image">
                        <img src="${article.image}" alt="${article.title}" onerror="this.src='assets/img/blank.jpg'">
                    </div>
                    <div class="article-content">
                        <h3 class="article-title">${article.title}</h3>
                        <div class="article-date">${article.date}</div>
                        <p class="article-excerpt">${article.excerpt}</p>
                        <a href="berita.php?id=${article.id}" class="article-link">Baca Selengkapnya <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            `;
        }

        function displayArticles() {
            const container = document.getElementById('articlesContainer');
            container.innerHTML = '';
            
            if (articles.length === 0) {
                container.innerHTML = `
                    <div class="no-articles">
                        <i class="fa-solid fa-newspaper"></i>
                        <h3>Belum ada berita</h3>
                        <p>Silakan periksa kembali nanti untuk berita akademik terbaru.</p>
                    </div>
                `;
                document.querySelector('.article-navigation').style.display = 'none';
                return;
            }
            
            const startIndex = (currentPage - 1) * articlesPerPage;
            const endIndex = Math.min(startIndex + articlesPerPage, articles.length);
            const currentArticles = articles.slice(startIndex, endIndex);
            
            currentArticles.forEach(article => {
                container.innerHTML += createArticleCard(article);
            });
            
            document.getElementById('currentPage').textContent = currentPage;
            document.getElementById('prevButton').disabled = currentPage === 1;
            document.getElementById('nextButton').disabled = currentPage === totalPages;
            
            if (totalPages <= 1) {
                document.querySelector('.article-navigation').style.display = 'none';
            }
        }

        document.getElementById('prevButton').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                displayArticles();
                document.querySelector('.info-section:last-child').scrollIntoView({ behavior: 'smooth' });
            }
        });

        document.getElementById('nextButton').addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                displayArticles();
                document.querySelector('.info-section:last-child').scrollIntoView({ behavior: 'smooth' });
            }
        });

        displayArticles();
    </script>
</body>
</html>
