<nav>
    <div class="navbar-container">
        <div class="navbar-logo">
            <i class="fa-solid fa-university"></i> MPD University
        </div>
        <div class="navbar-actions">
            <div class="navbar-menu" id="navbarMenu">
                <a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-house"></i> Beranda</a>
                <a href="profile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-building-columns"></i> Profile</a>
                <a href="akademik.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'akademik.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-info-circle"></i> Informasi</a>
                <a href="login.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'login.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-sign-in-alt"></i> Login</a>
            </div>
            <div class="hamburger" id="hamburgerBtn" aria-label="Menu" tabindex="0">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>
