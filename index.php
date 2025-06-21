<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPD University - Portal Akademik</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <section id="beranda" class="hero-section">
            <div class="container">
                <div class="blob blob-primary blob-left"></div>
                <div class="blob blob-primary blob-right"></div>
                
                <div class="flex-container">
                    <div class="hero-content">
                        <h1>
                            <span class="text-primary">Selamat Datang</span> di Website
                            <br>
                            <span id="typed" class="text-primary"></span>!
                        </h1>

                        <p class="hero-description">
                            Portal informasi akademik terlengkap untuk civitas akademika MPD University.
                            <br>
                            Data mahasiswa, dosen, mata kuliah, dan informasi penting lainnya.
                        </p>

                        <div class="button-group">
                            <button class="btn btn-primary" onclick="window.location.href='akademik.php'">
                                <span>Lihat Informasi</span>
                                <i class="ri-magic-fill"></i>
                            </button>

                            <button class="btn btn-outline" onclick="window.location.href='profile.php'">
                                <span>Pelajari Lebih Lanjut</span>
                                <i class="ri-arrow-left-up-box-fill"></i>
                            </button>
                        </div>

                        <p class="small-text">
                            *Akses informasi terbaru dari kampus!
                            <br>
                            *Dapatkan pengumuman penting secara real-time!
                        </p>

                        <div class="social-icons">
                            <i class="ri-facebook-fill social-icon"></i>
                            <i class="ri-instagram-line social-icon"></i>
                            <i class="ri-twitter-fill social-icon"></i>
                            <i class="ri-youtube-fill social-icon"></i>
                        </div>
                    </div>

                    <!-- Image Side -->
                    <div class="hero-image animate-float">
                        <img src="assets/img/hero.png" alt="University Image" class="main-image">
                        <span class="blob-svg">
                            <svg class="animated-blob" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="40%" style="stop-color: #2563eb"></stop>
                                        <stop offset="60%" style="stop-color: #1e40af"></stop>
                                    </linearGradient>
                                </defs>
                                <path fill="url(#gradient)" transform="translate(100 100)">
                                    <animate attributeName="d" dur="10000ms" repeatCount="indefinite"
                                    values="M38.1,-51.2C50.7,-43.3,63.1,-33.9,64.8,-22.5C66.5,-11.1,57.4,2.3,53.7,18.6C50,34.9,51.7,54.2,43.6,64C35.6,73.7,17.8,74,-0.6,74.9C-19,75.7,-38,77.1,-46,67.3C-54,57.5,-51,36.5,-50.2,20.6C-49.4,4.7,-50.9,-6.2,-51.3,-20.3C-51.6,-34.3,-50.9,-51.7,-42.1,-60.8C-33.4,-69.9,-16.7,-70.9,-2,-68.1C12.8,-65.4,25.5,-59,38.1,-51.2Z;
                                    M49.4,-68C60.6,-59.9,63.8,-40.8,65.3,-24.1C66.9,-7.3,66.8,7.2,62,19.5C57.1,31.8,47.4,41.8,36.3,50.6C25.1,59.4,12.6,66.9,0.3,66.5C-12,66.1,-24,57.9,-37.8,49.9C-51.6,42,-67.2,34.5,-71.2,22.9C-75.2,11.4,-67.7,-4.1,-63.5,-21.7C-59.3,-39.4,-58.4,-59.3,-48.4,-67.7C-38.3,-76.1,-19.2,-73.1,0,-73.1C19.1,-73.1,38.3,-76.1,49.4,-68Z;
                                    M39.1,-53.6C50.1,-45.8,58.1,-33.6,60.5,-20.9C62.8,-8.2,59.7,5.1,54.2,16.2C48.7,27.3,40.8,36.3,31.4,47.9C21.9,59.5,11,73.8,-2.7,77.4C-16.3,81.1,-32.6,74.3,-48.2,64.7C-63.8,55.1,-78.7,42.7,-78.7,28.9C-78.6,15,-63.6,-0.4,-53,-12.4C-42.4,-24.3,-36.2,-32.8,-28.1,-41.6C-20,-50.3,-10,-59.2,2,-62C14.1,-64.8,28.1,-61.4,39.1,-53.6Z;
                                    M37.2,-50.1C49.1,-42.4,60.5,-32.9,64.1,-21C67.8,-9.1,63.8,5.2,57.9,17.5C52,29.8,44.4,40.1,34.4,51.5C24.4,62.9,12.2,75.3,-2.4,78.7C-17.1,82,-34.2,76.3,-49.3,66.6C-64.4,56.9,-77.5,43.2,-79.8,27.9C-82.1,12.5,-73.6,-4.3,-66.2,-19.8C-58.8,-35.3,-52.5,-49.4,-41.6,-57.4C-30.8,-65.4,-15.4,-67.2,-1.4,-65.3C12.6,-63.4,25.2,-57.7,37.2,-50.1Z;
                                    M36,-51C48,-40.8,60.1,-32.1,62.3,-21.2C64.6,-10.2,57.1,2.9,54,19.1C50.9,35.3,52.2,54.5,44,67.2C35.7,79.8,17.9,85.8,1,84.4C-15.9,83.1,-31.7,74.3,-46.3,63.7C-61,53.1,-74.3,40.8,-80.2,25.4C-86.2,10,-84.6,-8.5,-77,-22.9C-69.4,-37.3,-55.8,-47.7,-41.9,-57.3C-28.1,-66.8,-14.1,-75.6,-1,-74.2C12,-72.8,24,-61.2,36,-51Z;
                                    M38.1,-51.2C50.7,-43.3,63.1,-33.9,64.8,-22.5C66.5,-11.1,57.4,2.3,53.7,18.6C50,34.9,51.7,54.2,43.6,64C35.6,73.7,17.8,74,-0.6,74.9C-19,75.7,-38,77.1,-46,67.3C-54,57.5,-51,36.5,-50.2,20.6C-49.4,4.7,-50.9,-6.2,-51.3,-20.3C-51.6,-34.3,-50.9,-51.7,-42.1,-60.8C-33.4,-69.9,-16.7,-70.9,-2,-68.1C12.8,-65.4,25.5,-59,38.1,-51.2Z"></animate>
                                </path>
                            </svg>
                        </span>
                        <div class="floating-icon">
                            <img src="assets/img/graduation-cap.png" alt="Icon" class="icon-image">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Initialize Typed.js
        document.addEventListener('DOMContentLoaded', function() {
            var typed = new Typed('#typed', {
                strings: ['Akademik', 'MPD University', 'Kampus'],
                typeSpeed: 50,
                backSpeed: 50,
                loop: true
            });
        });
    </script>
</body>
</html>
