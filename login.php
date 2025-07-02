<?php
// Start session at the very beginning of the file
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MPD University</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/login.css?v=<?php echo time(); ?>">
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Remixicon for additional icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <main>
        <div class="login-page">
            <div class="login-container">
                <div class="text-center">
                    <img src="assets/img/logo.png" alt="MPD University Logo" class="university-logo">
                    <h2>Login Portal</h2>
                    <p>Silahkan masuk untuk akses sistem akademik</p>
                </div>
                
                <?php
                // Display pesan ketika login gagal
                if(isset($_SESSION['login_error'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['login_error'] . '</div>';
                    unset($_SESSION['login_error']);
                }
                ?>
                
                <form action="auth/process_login.php" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <!-- Demo Accounts Card -->
                <div class="demo-accounts-card">
                    <div class="demo-header">
                        <i class="fas fa-info-circle"></i>
                        <h4>Akun Demo</h4>
                    </div>
                    <p class="demo-description">Gunakan akun berikut untuk testing sistem:</p>
                    
                    <div class="demo-accounts-grid">
                        <div class="demo-account-item">
                            <div class="demo-role">
                                <i class="fas fa-user-shield"></i>
                                <span>Administrator</span>
                            </div>
                            <div class="demo-credentials">
                                <div class="credential-item">
                                    <label>Username:</label>
                                    <span class="credential-value" onclick="copyToClipboard('admin', this)">admin</span>
                                </div>
                                <div class="credential-item">
                                    <label>Password:</label>
                                    <span class="credential-value" onclick="copyToClipboard('password', this)">password</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="demo-account-item">
                            <div class="demo-role">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Dosen</span>
                            </div>
                            <div class="demo-credentials">
                                <div class="credential-item">
                                    <label>Username:</label>
                                    <span class="credential-value" onclick="copyToClipboard('dosen1', this)">dosen1</span>
                                </div>
                                <div class="credential-item">
                                    <label>Password:</label>
                                    <span class="credential-value" onclick="copyToClipboard('password', this)">password</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="demo-account-item">
                            <div class="demo-role">
                                <i class="fas fa-user-graduate"></i>
                                <span>Mahasiswa</span>
                            </div>
                            <div class="demo-credentials">
                                <div class="credential-item">
                                    <label>Username:</label>
                                    <span class="credential-value" onclick="copyToClipboard('202243502616', this)">202243502616</span>
                                </div>
                                <div class="credential-item">
                                    <label>Password:</label>
                                    <span class="credential-value" onclick="copyToClipboard('password', this)">password</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="demo-note">
                        <i class="fas fa-lightbulb"></i>
                        <small>Klik pada username/password untuk menyalin ke clipboard</small>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(function() {
                // Visual feedback
                const originalText = element.textContent;
                element.textContent = 'Copied!';
                element.style.background = '#10b981';
                element.style.color = 'white';
                
                setTimeout(() => {
                    element.textContent = originalText;
                    element.style.background = '';
                    element.style.color = '';
                }, 1000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
        
        // Auto-fill demo credentials when clicking on demo account
        document.querySelectorAll('.demo-account-item').forEach(item => {
            item.addEventListener('click', function() {
                const usernameSpan = this.querySelector('.credential-value');
                const username = usernameSpan.textContent;
                
                document.getElementById('username').value = username;
                document.getElementById('password').value = 'password';
                
                // Add visual feedback
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });
    </script>
</body>
</html>
