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
    <!-- Typed.js for text animation -->
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.0.12"></script>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="container">
            <div class="text-center mb-4">
                <img src="assets/img/logo.jpg" alt="MPD University Logo" class="school-logo">
                <h1 class="mt-3">MPD University</h1>
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
                <ul class="hero-description">
                    <li>Fakultas Teknik (Teknik Informatika, Teknik Elektro, Teknik Sipil)</li>
                    <li>Fakultas Ekonomi dan Bisnis (Manajemen, Akuntansi, Ekonomi Pembangunan)</li>
                    <li>Fakultas Ilmu Komputer (Sistem Informasi, Ilmu Komputer, Teknologi Informasi)</li>
                    <li>Fakultas Kedokteran dan Ilmu Kesehatan (Kedokteran, Keperawatan, Farmasi)</li>
                    <li>Fakultas Humaniora (Sastra, Ilmu Komunikasi, Hubungan Internasional)</li>
                </ul>
            </div>
            
            <!-- <div class="mt-5">
                <h3 class="text-primary">Dosen Terkemuka</h3>
                <p class="hero-description">Berikut adalah beberapa dosen terkemuka kami yang memiliki dedikasi dan keahlian luar biasa di bidangnya masing-masing.</p>
                
                <div class="faculty-slider-container">
                    <div class="faculty-slider-track" id="facultySliderTrack">
                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/dosen1.jpg" alt="Prof. Dr. Ahmad Fauzi">
                            </div>
                            <div class="faculty-info">
                                <h4>Prof. Dr. Ahmad Fauzi</h4>
                                <p class="faculty-title">Dekan Fakultas Teknik</p>
                                <p class="faculty-specialty">Spesialisasi: Teknik Elektro</p>
                                <p class="faculty-description">
                                    Memiliki pengalaman mengajar lebih dari 20 tahun di bidang teknik elektro dan telah menerbitkan puluhan jurnal internasional.
                                </p>
                            </div>
                        </div>

                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/dosen2.jpg" alt="Dr. Siti Rahma">
                            </div>
                            <div class="faculty-info">
                                <h4>Dr. Siti Rahma, M.Sc.</h4>
                                <p class="faculty-title">Ketua Program Studi Ilmu Komputer</p>
                                <p class="faculty-specialty">Spesialisasi: Kecerdasan Buatan</p>
                                <p class="faculty-description">
                                    Peneliti aktif di bidang machine learning dan computer vision dengan berbagai kolaborasi riset internasional.
                                </p>
                            </div>
                        </div>

                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/dosen3.jpg" alt="Dr. Budi Santoso">
                            </div>
                            <div class="faculty-info">
                                <h4>Prof. Dr. Budi Santoso</h4>
                                <p class="faculty-title">Dekan Fakultas Ilmu Data dan Kecerdasan Buatan</p>
                                <p class="faculty-specialty">Spesialisasi: Deep Learning</p>
                                <p class="faculty-description">
                                    Dikenal sebagai pakar AI nasional dengan pengalaman industri sebelumnya di Google dan Microsoft Research.
                                </p>
                            </div>
                        </div>

                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/dosen4.jpg" alt="Dr. Dewi Putri">
                            </div>
                            <div class="faculty-info">
                                <h4>Dr. Dewi Putri, Ph.D.</h4>
                                <p class="faculty-title">Ketua Program Studi Ekonomi</p>
                                <p class="faculty-specialty">Spesialisasi: Ekonomi Makro</p>
                                <p class="faculty-description">
                                    Alumni Harvard University yang aktif dalam penelitian tentang ekonomi pembangunan dan kebijakan publik.
                                </p>
                            </div>
                        </div>

                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/dosen5.jpg" alt="Dr. Rudi Hartanto">
                            </div>
                            <div class="faculty-info">
                                <h4>Dr. Rudi Hartanto</h4>
                                <p class="faculty-title">Dosen Teknik Informatika</p>
                                <p class="faculty-specialty">Spesialisasi: Cyber Security</p>
                                <p class="faculty-description">
                                    Berpengalaman sebagai konsultan keamanan siber untuk berbagai institusi pemerintah dan perusahaan besar.
                                </p>
                            </div>
                        </div>
                        
                        <div class="faculty-card">
                            <div class="faculty-image">
                                <img src="assets/img/faculty/dosen6.jpg" alt="Dr. Hendra Wijaya">
                            </div>
                            <div class="faculty-info">
                                <h4>Dr. Hendra Wijaya, M.T.</h4>
                                <p class="faculty-title">Dosen Teknik Elektro</p>
                                <p class="faculty-specialty">Spesialisasi: Energi Terbarukan</p>
                                <p class="faculty-description">
                                    Memimpin tim penelitian di bidang teknologi energi terbarukan dengan beberapa paten terdaftar.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="faculty-slider-navigation">
                        <button class="slider-button prev" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                        <div class="slider-dots" id="sliderDots">
                            
                        </div>
                        <button class="slider-button next" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div> -->
            
            <div class="text-center mt-5 pt-4 border-top">
                <p>Jl. Pendidikan No. 123, Kota Makmur | Telp: (021) 1234-5678 | Email: info@mpduniversity.ac.id</p>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const track = document.getElementById('facultySliderTrack');
            const cards = track.querySelectorAll('.faculty-card');
            const dotsContainer = document.getElementById('sliderDots');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            let currentSlide = 0;
            let slidesToShow = getCardsToShow();
            
            function createDots() {
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
                
                prevBtn.disabled = currentSlide === 0;
                nextBtn.disabled = currentSlide === maxSlide;
            }
            
            prevBtn.addEventListener('click', () => {
                goToSlide(currentSlide - 1);
            });
            
            nextBtn.addEventListener('click', () => {
                goToSlide(currentSlide + 1);
            });
            
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