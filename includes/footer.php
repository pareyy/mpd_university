<footer>
    &copy; <?= date('Y') ?> MPD University - Portal Akademik | <a href="https://github.com/TobyG74" target="_blank">TobyG74</a>
</footer>

<script>
    // Responsive navbar toggle
    const hamburger = document.getElementById('hamburgerBtn');
    const menu = document.getElementById('navbarMenu');
    
    hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('open');
    });
    
    // Close menu when link clicked (on mobile)
    document.querySelectorAll('.navbar-menu a').forEach(link => {
        link.addEventListener('click', () => {
            if(window.innerWidth <= 800) menu.classList.remove('open');
        });
    });
    
    // Close menu when click outside (on mobile)
    document.addEventListener('click', function(e) {
        if(window.innerWidth > 800) return;
        if (!menu.contains(e.target) && !hamburger.contains(e.target)) {
            menu.classList.remove('open');
        }
    });
</script>