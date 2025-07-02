<nav>
    <div class="navbar-container">
        <div class="navbar-logo">
            <i class="fa-solid fa-graduation-cap"></i> Portal Mahasiswa
        </div>
        <div class="navbar-actions">
            <div class="navbar-menu" id="navbarMenu">
                <a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="mata_kuliah.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'mata_kuliah.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-book"></i> Mata Kuliah
                </a>
                <a href="jadwal.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'jadwal.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-calendar"></i> Jadwal
                </a>
                <a href="nilai.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'nilai.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-chart-line"></i> Nilai
                </a>
                <a href="absensi.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'absensi.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-user-check"></i> Absensi
                </a>
                <a href="tugas.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'tugas.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-tasks"></i> Tugas
                </a>
                <a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>>
                    <i class="fa-solid fa-user"></i> Profil
                </a>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
            <div class="hamburger" id="hamburgerBtn" aria-label="Menu" tabindex="0">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navbarMenu = document.getElementById('navbarMenu');

    hamburgerBtn.addEventListener('click', function() {
        navbarMenu.classList.toggle('active');
        hamburgerBtn.classList.toggle('active');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!hamburgerBtn.contains(event.target) && !navbarMenu.contains(event.target)) {
            navbarMenu.classList.remove('active');
            hamburgerBtn.classList.remove('active');
        }
    });
});
</script>
