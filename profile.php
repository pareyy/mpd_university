<?php
// Include database connection
require_once 'koneksi.php';

// Get fakultas and program studi data
$fakultas_query = "SELECT f.*, COUNT(ps.id) as total_prodi 
                   FROM fakultas f 
                   LEFT JOIN program_studi ps ON f.id = ps.fakultas_id 
                   GROUP BY f.id 
                   ORDER BY f.nama";
$fakultas_result = mysqli_query($conn, $fakultas_query);
$fakultas_data = [];
while ($row = mysqli_fetch_assoc($fakultas_result)) {
    $fakultas_data[] = $row;
}

// Get program studi data grouped by fakultas
$prodi_query = "SELECT ps.*, f.nama as fakultas_nama 
                FROM program_studi ps 
                JOIN fakultas f ON ps.fakultas_id = f.id 
                ORDER BY f.nama, ps.nama";
$prodi_result = mysqli_query($conn, $prodi_query);
$prodi_data = [];
while ($row = mysqli_fetch_assoc($prodi_result)) {
    $prodi_data[$row['fakultas_nama']][] = $row;
}

// Get dosen data for featured faculty
$dosen_query = "SELECT d.*, f.nama as fakultas_nama 
                FROM dosen d 
                JOIN fakultas f ON d.fakultas_id = f.id 
                ORDER BY d.created_at ASC 
                LIMIT 6";
$dosen_result = mysqli_query($conn, $dosen_query);
$dosen_data = [];
while ($row = mysqli_fetch_assoc($dosen_result)) {
    $dosen_data[] = $row;
}

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM mahasiswa) as total_mahasiswa,
    (SELECT COUNT(*) FROM dosen) as total_dosen,
    (SELECT COUNT(*) FROM fakultas) as total_fakultas,
    (SELECT COUNT(*) FROM program_studi) as total_prodi";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Universitas - MPD University</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/profile.css">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="container">
            <div class="text-center mb-4">
                <img src="assets/img/logo.png" alt="MPD University Logo" class="school-logo">
                <h1 class="mt-3">MPD University</h1>
                <p class="hero-subtitle">Membangun Masa Depan Melalui Pendidikan Berkualitas</p>
            </div>

            <div>
                <h3 class="text-primary">Tentang Universitas Kami</h3>
                <p class="hero-description">
                    MPD University didirikan pada tahun 1990 dengan visi menjadi pusat pendidikan tinggi terkemuka yang menghasilkan lulusan berprestasi di berbagai bidang keilmuan dan profesional. Kami menyediakan lingkungan akademik yang kondusif dan inovatif untuk mengembangkan potensi setiap mahasiswa.
                </p>
                <p class="hero-description">
                    Dengan staf pengajar profesional berpengalaman dan fasilitas modern, kami berkomitmen untuk memberikan pendidikan berkualitas tinggi. Program kami dirancang untuk mempersiapkan mahasiswa menghadapi tantangan global dan menjadi pemimpin di bidangnya masing-masing.
                </p>
                
                <h4 class="mt-4 text-primary">Visi</h4>
                <p class="hero-description">
                    Menjadi universitas unggulan yang menghasilkan lulusan berkompeten, beretika, dan berdaya saing global dalam pengembangan ilmu pengetahuan dan teknologi.
                </p>
                
                <h4 class="mt-4 text-primary">Misi</h4>
                <ul class="hero-description">
                    <li>Menyelenggarakan pendidikan tinggi berkualitas dengan pendekatan komprehensif</li>
                    <li>Mengembangkan riset dan inovasi yang berkontribusi pada kemajuan ilmu pengetahuan dan teknologi</li>
                    <li>Melaksanakan pengabdian kepada masyarakat berdasarkan hasil penelitian untuk kemajuan bangsa</li>
                    <li>Membangun kemitraan strategis dengan berbagai institusi dalam dan luar negeri</li>
                    <li>Menciptakan lingkungan akademik yang mendukung kreativitas, inovasi dan kewirausahaan</li>
                </ul>
                
                <h4 class="mt-4 text-primary">Fakultas & Program Studi</h4>
                <div class="fakultas-grid">
                    <?php foreach ($fakultas_data as $fakultas): ?>
                    <div class="fakultas-card">
                        <div class="fakultas-header">
                            <h5><?php echo htmlspecialchars($fakultas['nama']); ?></h5>
                            <span class="prodi-count"><?php echo $fakultas['total_prodi']; ?> Program Studi</span>
                        </div>
                        <div class="fakultas-content">
                            <p class="dekan-info">
                                <strong>Dekan:</strong> <?php echo htmlspecialchars($fakultas['dekan']); ?>
                            </p>
                            <?php if (isset($prodi_data[$fakultas['nama']])): ?>
                            <div class="prodi-list">
                                <strong>Program Studi:</strong>
                                <ul>
                                    <?php foreach ($prodi_data[$fakultas['nama']] as $prodi): ?>
                                    <li><?php echo htmlspecialchars($prodi['nama']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if (!empty($dosen_data)): ?>
            <div class="mt-5">
                <h3 class="text-primary">Dosen Terkemuka</h3>
                <p class="hero-description">Berikut adalah beberapa dosen terkemuka kami yang memiliki dedikasi dan keahlian luar biasa di bidangnya masing-masing.</p>
                
                <div class="faculty-slider-container">
                    <div class="faculty-slider-track" id="facultySliderTrack">
                        <?php foreach ($dosen_data as $dosen): ?>
                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/default-avatar.jpg" alt="<?php echo htmlspecialchars($dosen['nama']); ?>" onerror="this.src='assets/img/blank.jpg'">
                            </div>
                            <div class="faculty-info">
                                <h4><?php echo htmlspecialchars($dosen['nama']); ?></h4>
                                <p class="faculty-title"><?php echo htmlspecialchars($dosen['fakultas_nama']); ?></p>
                                <p class="faculty-specialty">Spesialisasi: <?php echo htmlspecialchars($dosen['bidang_keahlian']); ?></p>
                                <p class="faculty-nidn">NIDN: <?php echo htmlspecialchars($dosen['nidn']); ?></p>
                                <div class="faculty-contact">
                                    <?php if ($dosen['email']): ?>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($dosen['email']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($dosen['phone']): ?>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($dosen['phone']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="faculty-slider-navigation">
                        <button class="slider-button prev" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                        <div class="slider-dots" id="sliderDots"></div>
                        <button class="slider-button next" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-5 pt-4 border-top">
                <p>Jl. Pendidikan No. 123, Kota Makmur | Telp: (021) 1234-5678 | Email: info@mpduniversity.ac.id</p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.getElementById('facultySliderTrack');
            const cards = track ? track.querySelectorAll('.faculty-card') : [];
            const dotsContainer = document.getElementById('sliderDots');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            if (cards.length === 0) return;
            
            let currentSlide = 0;
            let slidesToShow = getCardsToShow();
            
            function createDots() {
                if (!dotsContainer) return;
                dotsContainer.innerHTML = '';
                const numDots = Math.ceil(cards.length / slidesToShow);
                
                for (let i = 0; i < numDots; i++) {
                    const dot = document.createElement('span');
                    dot.classList.add('slider-dot');
                    if (i === currentSlide) dot.classList.add('active');
                    dot.dataset.slide = i;
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
            }
            
            function getCardsToShow() {
                if (window.innerWidth < 768) {
                    return 1;
                } else if (window.innerWidth < 1024) {
                    return 2;
                } else {
                    return 3;
                }
            }
            
            function goToSlide(slideIndex) {
                const maxSlide = Math.ceil(cards.length / slidesToShow) - 1;
                currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
                
                const slideWidth = cards[0].offsetWidth + parseInt(getComputedStyle(cards[0]).marginRight);
                const offset = -currentSlide * slideWidth * slidesToShow;
                
                track.style.transform = `translateX(${offset}px)`;
                
                const dots = dotsContainer.querySelectorAll('.slider-dot');
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === currentSlide);
                });
                
                if (prevBtn) prevBtn.disabled = currentSlide === 0;
                if (nextBtn) nextBtn.disabled = currentSlide === maxSlide;
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    goToSlide(currentSlide - 1);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    goToSlide(currentSlide + 1);
                });
            }
            
            function initSlider() {
                slidesToShow = getCardsToShow();
                
                cards.forEach(card => {
                    card.style.width = `calc(100% / ${slidesToShow} - 20px)`;
                });
                
                createDots();
                goToSlide(0);
            }
            
            window.addEventListener('resize', () => {
                const newSlidesToShow = getCardsToShow();
                if (newSlidesToShow !== slidesToShow) {
                    initSlider();
                }
            });
            
            initSlider();
        });
    </script>
</body>
</html>