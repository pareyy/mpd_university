<?php
// Start session
session_start();

// Include database connection
require_once '../koneksi.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from form
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Query to check user
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../admin/index.php");
                    break;
                case 'dosen':
                    header("Location: ../dosen/index.php");
                    break;
                case 'mahasiswa':
                    header("Location: ../mahasiswa/index.php");
                    break;
                default:
                    header("Location: ../index.php");
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
    
    // If there's an error, redirect back to login page with error message
    $_SESSION['login_error'] = $error;
    header("Location: ../login.php");
    exit();
}
?>
