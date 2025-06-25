<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <a href="index.php">
                <img src="../assets/img/logo.png" alt="MPD University">
                <span>MPD Admin</span>
            </a>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="mahasiswa.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Mahasiswa</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="dosen.php" class="nav-link">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Dosen</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="mata_kuliah.php" class="nav-link">
                    <i class="fas fa-book"></i>
                    <span>Mata Kuliah</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="jadwal.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal</span>
                </a>
            </li>            <li class="nav-item">
                <a href="laporan.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="article.php" class="nav-link">
                    <i class="fas fa-newspaper"></i>
                    <span>Artikel</span>
                </a>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="pengaturan.php"><i class="fas fa-cogs"></i> Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </li>
        </ul>
        
        <div class="hamburger">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </div>
    </div>
</nav>

<style>
/* Admin Navigation Styles */
.navbar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.nav-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
}

.nav-logo a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: white;
    font-weight: 700;
    font-size: 1.25rem;
    padding: 1rem 0;
}

.nav-logo img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
}

.nav-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    gap: 0.5rem;
}

.nav-item {
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 8px;
    font-weight: 500;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    text-decoration: none;
}

.nav-link i {
    font-size: 1rem;
}

.dropdown {
    position: relative;
}

.dropdown-toggle {
    cursor: pointer;
}

.dropdown-arrow {
    margin-left: 0.5rem;
    transition: transform 0.3s ease;
}

.dropdown.active .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
    list-style: none;
    padding: 0.5rem 0;
    margin: 0;
}

.dropdown.active .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-menu li {
    padding: 0;
}

.dropdown-menu a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    transition: background 0.3s ease;
}

.dropdown-menu a:hover {
    background: #f3f4f6;
    color: #374151;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    border: none;
    margin: 0.5rem 0;
}

.hamburger {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 0.5rem;
}

.hamburger .bar {
    width: 25px;
    height: 3px;
    background: white;
    margin: 2px 0;
    transition: 0.3s;
    border-radius: 2px;
}

.hamburger.active .bar:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.hamburger.active .bar:nth-child(2) {
    opacity: 0;
}

.hamburger.active .bar:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

/* Mobile Navigation */
@media screen and (max-width: 768px) {
    .nav-container {
        padding: 0 1rem;
    }
    
    .hamburger {
        display: flex;
    }
    
    .nav-menu {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 100%;
        text-align: center;
        transition: 0.3s;
        box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
        padding: 1rem 0;
        gap: 0;
        align-items: stretch;
    }
    
    .nav-menu.active {
        left: 0;
    }
    
    .nav-item {
        width: 100%;
    }
    
    .nav-link {
        padding: 1rem 2rem;
        border-radius: 0;
        justify-content: flex-start;
        width: 100%;
        box-sizing: border-box;
    }
    
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .dropdown-menu {
        position: static;
        background: rgba(255, 255, 255, 0.1);
        box-shadow: none;
        border-radius: 0;
        transform: none;
        opacity: 1;
        visibility: visible;
        display: none;
        margin: 0;
    }
    
    .dropdown.active .dropdown-menu {
        display: block;
    }
    
    .dropdown-menu a {
        color: white;
        padding: 0.75rem 3rem;
        font-size: 0.9rem;
    }
    
    .dropdown-menu a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .dropdown-divider {
        background: rgba(255, 255, 255, 0.2);
        margin: 0.25rem 2rem;
    }
    
    .nav-logo a {
        padding: 1rem 0;
        font-size: 1.1rem;
    }
    
    .nav-logo img {
        width: 35px;
        height: 35px;
    }
}

@media screen and (max-width: 480px) {
    .nav-container {
        padding: 0 0.5rem;
    }
    
    .nav-logo a {
        font-size: 1rem;
        gap: 0.5rem;
    }
    
    .nav-logo img {
        width: 30px;
        height: 30px;
    }
    
    .nav-link {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .nav-link i {
        font-size: 0.9rem;
    }
    
    .dropdown-menu a {
        padding: 0.625rem 2.5rem;
        font-size: 0.85rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    const dropdown = document.querySelector('.dropdown');
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    // Mobile menu toggle
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-link:not(.dropdown-toggle)').forEach(link => {
        link.addEventListener('click', () => {
            if (hamburger) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    });

    // Dropdown functionality
    if (dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!document.querySelector('.nav-container').contains(e.target)) {
            if (hamburger) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            if (hamburger) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        }
    });
});
</script>
