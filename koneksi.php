<?php
// Database connection using mysqli
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mpd_university';

try {
    // Create mysqli connection
    $conn = mysqli_connect($host, $username, $password, $database);
    
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8mb4");
    
} catch(Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>