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
                
                <div class="announcement-card">
                    <h3 class="announcement-title">Jadwal Pendaftaran Semester Ganjil 2024/2025</h3>
                    <div class="announcement-date">
                        <i class="fa-solid fa-calendar"></i> 15 Juli 2025
                    </div>
                    <div class="announcement-content">
                        <p>Pendaftaran mata kuliah untuk semester ganjil tahun akademik 2024/2025 akan dibuka pada tanggal <strong>1 Agustus 2025</strong> dan ditutup pada tanggal <strong>15 Agustus 2025</strong>. Mahasiswa diharapkan berkonsultasi dengan dosen pembimbing akademik sebelum melakukan pendaftaran.</p>
                        <p>Proses pendaftaran dilakukan melalui portal akademik dengan menggunakan akun masing-masing.</p>
                    </div>
                </div>
                
                <div class="announcement-card">
                    <h3 class="announcement-title">Pembayaran Uang Kuliah Semester Ganjil</h3>
                    <div class="announcement-date">
                        <i class="fa-solid fa-calendar"></i> 10 Juli 2025
                    </div>
                    <div class="announcement-content">
                        <p>Batas waktu pembayaran uang kuliah semester ganjil tahun akademik 2024/2025 adalah <strong>25 Juli 2025</strong>. Pembayaran dapat dilakukan melalui transfer bank atau langsung di bagian keuangan universitas.</p>
                        <p>Mahasiswa yang belum melakukan pembayaran hingga batas waktu yang ditentukan tidak dapat melakukan pendaftaran mata kuliah.</p>
                    </div>
                </div>
            </section>
            
            <section class="info-section">
                <h2 class="section-title"><i class="fa-solid fa-calendar-alt mr-2"></i> Kalender Akademik</h2>
                
                <div class="calendar-item">
                    <div class="calendar-date">
                        <span class="day">1</span>
                        <span class="month">Agt</span>
                    </div>
                    <div class="calendar-info">
                        <h4>Pendaftaran Mata Kuliah Semester Ganjil</h4>
                        <p>Pendaftaran mata kuliah untuk mahasiswa angkatan 2021-2025 melalui portal akademik.</p>
                    </div>
                </div>
                
                <div class="calendar-item">
                    <div class="calendar-date">
                        <span class="day">20</span>
                        <span class="month">Agt</span>
                    </div>
                    <div class="calendar-info">
                        <h4>Orientasi Mahasiswa Baru</h4>
                        <p>Pengenalan lingkungan kampus dan sistem akademik untuk mahasiswa baru angkatan 2025.</p>
                    </div>
                </div>
                
                <div class="calendar-item">
                    <div class="calendar-date">
                        <span class="day">28</span>
                        <span class="month">Agt</span>
                    </div>
                    <div class="calendar-info">
                        <h4>Awal Perkuliahan Semester Ganjil</h4>
                        <p>Perkuliahan dimulai untuk semua fakultas dan program studi, baik untuk kelas reguler maupun kelas malam.</p>
                    </div>
                </div>
                
                <div class="calendar-item">
                    <div class="calendar-date">
                        <span class="day">15</span>
                        <span class="month">Okt</span>
                    </div>
                    <div class="calendar-info">
                        <h4>Ujian Tengah Semester</h4>
                        <p>Periode ujian tengah semester untuk semua mata kuliah semester ganjil.</p>
                    </div>
                </div>
            </section>
            
            <section class="info-section">
                <h2 class="section-title"><i class="fa-solid fa-newspaper mr-2"></i> Berita Akademik Terbaru</h2>
                
                <div class="articles-container" id="articlesContainer">
                    <!-- Artikel akan dimuat secara dinamis disini -->
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
        // Contoh data artikel
        const articles = [
            {
                id: 1,
                title: 'MPD University Raih Akreditasi Unggul',
                date: '5 Juli 2025',
                image: 'assets/img/blank.jpg',
                excerpt: 'MPD University berhasil meraih status Akreditasi Unggul dari Badan Akreditasi Nasional Perguruan Tinggi (BAN-PT) untuk periode 2025-2028.'
            },
            {
                id: 2,
                title: 'Program Beasiswa Baru untuk Mahasiswa Berprestasi',
                date: '28 Juni 2025',
                image: 'assets/img/blank.jpg',
                excerpt: 'MPD University bekerja sama dengan industri terkemuka meluncurkan program beasiswa baru untuk mahasiswa berprestasi di bidang teknologi dan sains.'
            },
            {
                id: 3,
                title: 'Fakultas Baru: Ilmu Data dan Kecerdasan Buatan',
                date: '15 Juni 2025',
                image: 'assets/img/blank.jpg',
                excerpt: 'Mulai tahun akademik 2024/2025, MPD University membuka Fakultas baru yang berfokus pada Ilmu Data dan Kecerdasan Buatan.'
            },
            {
                id: 4,
                title: 'Seminar Internasional: Teknologi Pendidikan di Era Digital',
                date: '10 Juni 2025',
                image: 'assets/img/blank.jpg',
                excerpt: 'MPD University menyelenggarakan seminar internasional dengan pembicara dari berbagai negara untuk membahas perkembangan teknologi pendidikan.'
            },
            {
                id: 5,
                title: 'Penelitian Dosen MPD University Dipublikasikan di Jurnal Internasional',
                date: '5 Juni 2025',
                image: 'assets/img/blank.jpg',
                excerpt: 'Penelitian tentang energi terbarukan yang dilakukan oleh tim dosen MPD University berhasil dipublikasikan dalam jurnal internasional bergengsi.'
            },
            {
                id: 6,
                title: 'Peringatan Dies Natalis ke-33 MPD University',
                date: '1 Juni 2025',
                image: 'assets/img/blank.jpg',
                excerpt: 'MPD University merayakan ulang tahun ke-33 dengan berbagai kegiatan akademik dan sosial yang melibatkan seluruh civitas akademika.'
            }
        ];

        const articlesPerPage = 3;
        let currentPage = 1;
        const totalPages = Math.ceil(articles.length / articlesPerPage);

        document.getElementById('totalPages').textContent = totalPages;

        function createArticleCard(article) {
            return `
                <div class="article-card">
                    <div class="article-image">
                        <img src="${article.image}" alt="${article.title}">
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
            
            const startIndex = (currentPage - 1) * articlesPerPage;
            const endIndex = Math.min(startIndex + articlesPerPage, articles.length);
            const currentArticles = articles.slice(startIndex, endIndex);
            
            currentArticles.forEach(article => {
                container.innerHTML += createArticleCard(article);
            });
            
            document.getElementById('currentPage').textContent = currentPage;
            document.getElementById('prevButton').disabled = currentPage === 1;
            document.getElementById('nextButton').disabled = currentPage === totalPages;
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
