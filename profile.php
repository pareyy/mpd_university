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
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/profile.css?v=<?php echo time(); ?>">
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
            console.log('DOM Content Loaded - Initializing Faculty Slider');
            
            const track = document.getElementById('facultySliderTrack');
            const cards = track ? track.querySelectorAll('.faculty-card') : [];
            const dotsContainer = document.getElementById('sliderDots');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const container = document.querySelector('.faculty-slider-container');
            
            console.log('Faculty cards found:', cards.length);
            
            if (cards.length === 0) {
                console.log('No faculty cards found, exiting slider initialization');
                return;
            }
            
            let currentSlide = 0;
            let slidesToShow = getCardsToShow();
            let autoSlideInterval;
            
            function getCardsToShow() {
                const width = window.innerWidth;
                if (width <= 768) return 1;
                if (width <= 1024) return 2;
                return Math.min(3, cards.length); // Don't show more cards than available
            }
            
            function getCardWidth() {
                if (!container) return 300;
                
                const containerWidth = container.offsetWidth;
                const containerPadding = 64; // 2rem padding on each side
                const gapWidth = 24; // 1.5rem gap between cards
                
                const availableWidth = containerWidth - containerPadding;
                const totalGaps = (slidesToShow - 1) * gapWidth;
                const cardWidth = Math.floor((availableWidth - totalGaps) / slidesToShow);
                
                console.log('Container width:', containerWidth);
                console.log('Available width:', availableWidth);
                console.log('Cards to show:', slidesToShow);
                console.log('Calculated card width:', cardWidth);
                
                return Math.max(250, cardWidth); // Minimum width of 250px
            }
            
            function updateCardStyles() {
                const cardWidth = getCardWidth();
                console.log('Updating card width to:', cardWidth + 'px');
                
                cards.forEach((card, index) => {
                    card.style.width = cardWidth + 'px';
                    card.style.flexShrink = '0';
                    card.style.flexGrow = '0';
                    card.style.minWidth = cardWidth + 'px';
                    card.style.maxWidth = cardWidth + 'px';
                });
                
                // Update track width to accommodate all cards
                const gapWidth = 24;
                const totalWidth = (cards.length * cardWidth) + ((cards.length - 1) * gapWidth);
                track.style.width = totalWidth + 'px';
                
                console.log('Track width set to:', totalWidth + 'px');
            }
            
            function createDots() {
                if (!dotsContainer) return;
                dotsContainer.innerHTML = '';
                
                const maxSlides = Math.max(1, Math.ceil(cards.length / slidesToShow));
                console.log('Creating', maxSlides, 'dots for', cards.length, 'cards');
                
                for (let i = 0; i < maxSlides; i++) {
                    const dot = document.createElement('span');
                    dot.classList.add('slider-dot');
                    if (i === currentSlide) dot.classList.add('active');
                    dot.dataset.slide = i;
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
            }
            
            function goToSlide(slideIndex) {
                const maxSlides = Math.max(1, Math.ceil(cards.length / slidesToShow));
                const maxSlide = maxSlides - 1;
                
                currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
                
                console.log('Going to slide:', currentSlide, 'of', maxSlide, '(total slides:', maxSlides, ')');
                console.log('Total cards:', cards.length, 'Cards per slide:', slidesToShow);
                
                const cardWidth = getCardWidth();
                const gapWidth = 24;
                
                // Calculate exact offset based on current slide
                let offset = 0;
                if (currentSlide > 0) {
                    const slideWidth = slidesToShow * cardWidth + (slidesToShow - 1) * gapWidth;
                    offset = -(currentSlide * slideWidth);
                }
                
                console.log('Card width:', cardWidth, 'Gap width:', gapWidth, 'Offset:', offset);
                
                // Apply transform with error handling
                try {
                    track.style.transform = `translateX(${offset}px)`;
                    console.log('Transform applied successfully');
                } catch (error) {
                    console.error('Error applying transform:', error);
                }
                
                // Update dots
                const dots = dotsContainer ? dotsContainer.querySelectorAll('.slider-dot') : [];
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === currentSlide);
                });
                
                // Update button states
                if (prevBtn) {
                    prevBtn.disabled = currentSlide === 0;
                    prevBtn.style.opacity = currentSlide === 0 ? '0.5' : '1';
                }
                if (nextBtn) {
                    nextBtn.disabled = currentSlide === maxSlide;
                    nextBtn.style.opacity = currentSlide === maxSlide ? '0.5' : '1';
                }
            }
            
            function initSlider() {
                console.log('Initializing slider with', cards.length, 'cards');
                slidesToShow = getCardsToShow();
                console.log('Cards to show:', slidesToShow);
                
                // Set initial track styles
                track.style.display = 'flex';
                track.style.gap = '1.5rem';
                track.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                track.style.alignItems = 'stretch';
                
                updateCardStyles();
                createDots();
                goToSlide(0);
                
                console.log('Slider initialization complete');
            }
            
            // Event listeners
            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    console.log('Previous button clicked');
                    goToSlide(currentSlide - 1);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    console.log('Next button clicked');
                    goToSlide(currentSlide + 1);
                });
            }
            
            // Handle window resize
            let resizeTimeout;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    console.log('Window resized, re-initializing slider');
                    const newSlidesToShow = getCardsToShow();
                    if (newSlidesToShow !== slidesToShow) {
                        slidesToShow = newSlidesToShow;
                        currentSlide = 0; // Reset to first slide when layout changes
                        initSlider();
                    } else {
                        updateCardStyles();
                        const maxSlides = Math.ceil(cards.length / slidesToShow);
                        goToSlide(Math.min(currentSlide, maxSlides - 1));
                    }
                }, 300);
            });
            
            // Auto-slide functionality
            function startAutoSlide() {
                stopAutoSlide();
                autoSlideInterval = setInterval(() => {
                    const maxSlides = Math.ceil(cards.length / slidesToShow);
                    const nextSlideIndex = currentSlide < maxSlides - 1 ? currentSlide + 1 : 0;
                    goToSlide(nextSlideIndex);
                }, 6000); // Increased to 6 seconds for better UX
            }
            
            function stopAutoSlide() {
                if (autoSlideInterval) {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = null;
                }
            }
            
            // Pause auto-slide on hover
            if (container) {
                container.addEventListener('mouseenter', stopAutoSlide);
                container.addEventListener('mouseleave', startAutoSlide);
            }
            
            // Initialize slider with small delay to ensure DOM is ready
            setTimeout(() => {
                // Double check that elements exist before initializing
                if (cards.length > 0 && container) {
                    console.log('Starting slider initialization...');
                    initSlider();
                    
                    // Only start auto-slide if we have more than one slide
                    const maxSlides = Math.ceil(cards.length / slidesToShow);
                    if (maxSlides > 1) {
                        startAutoSlide();
                        console.log('Auto-slide started');
                    } else {
                        console.log('Only one slide, auto-slide disabled');
                        // Hide navigation if only one slide
                        if (prevBtn) prevBtn.style.display = 'none';
                        if (nextBtn) nextBtn.style.display = 'none';
                        if (dotsContainer) dotsContainer.style.display = 'none';
                    }
                } else {
                    console.error('Slider initialization failed: missing elements');
                    console.log('Cards count:', cards.length);
                    console.log('Container exists:', !!container);
                }
            }, 200); // Slightly longer delay for stability
        });
    </script>
</body>
</html>