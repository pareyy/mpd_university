<?php
/**
 * User Management Class
 * Handles all user-related database operations
 */

class UserManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Get user by ID with error handling
     * @param int $user_id
     * @return array|null
     * @throws Exception
     */
    public function getUserById($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Database execute error: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("UserManager::getUserById - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update user profile
     * @param int $user_id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateProfile($user_id, $data) {
        try {
            // Validate required fields
            $required_fields = ['username', 'email', 'full_name'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Field '$field' is required");
                }
            }
            
            $stmt = $this->conn->prepare(
                "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?"
            );
            
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssssi", 
                $data['username'], 
                $data['email'], 
                $data['full_name'], 
                $data['phone'], 
                $data['address'], 
                $user_id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Database execute error: " . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("UserManager::updateProfile - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update user password
     * @param int $user_id
     * @param string $hashed_password
     * @return bool
     * @throws Exception
     */
    public function updatePassword($user_id, $hashed_password) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $stmt->bind_param("si", $hashed_password, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Database execute error: " . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("UserManager::updatePassword - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update user profile photo
     * @param int $user_id
     * @param string $photo_name
     * @return bool
     * @throws Exception
     */
    public function updatePhoto($user_id, $photo_name) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET profile_photo = ?, updated_at = NOW() WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $this->conn->error);
            }
            
            $stmt->bind_param("si", $photo_name, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Database execute error: " . $stmt->error);
            }
            
            return $stmt->affected_rows > 0;
        } catch (Exception $e) {
            error_log("UserManager::updatePhoto - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get system statistics
     * @return array
     * @throws Exception
     */
    public function getSystemStats() {
        try {
            $query = "SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'mahasiswa') as total_users,
                (SELECT COUNT(*) FROM mahasiswa) as total_students,
                (SELECT COUNT(*) FROM dosen) as total_lecturers,
                (SELECT COUNT(*) FROM mata_kuliah) as total_courses";
                
            $result = $this->conn->query($query);
            if (!$result) {
                throw new Exception("Database query error: " . $this->conn->error);
            }
            
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("UserManager::getSystemStats - " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if username exists (excluding current user)
     * @param string $username
     * @param int $exclude_user_id
     * @return bool
     */
    public function usernameExists($username, $exclude_user_id = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
            $params = [$username];
            $types = "s";
            
            if ($exclude_user_id) {
                $query .= " AND id != ?";
                $params[] = $exclude_user_id;
                $types .= "i";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("UserManager::usernameExists - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists (excluding current user)
     * @param string $email
     * @param int $exclude_user_id
     * @return bool
     */
    public function emailExists($email, $exclude_user_id = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $params = [$email];
            $types = "s";
            
            if ($exclude_user_id) {
                $query .= " AND id != ?";
                $params[] = $exclude_user_id;
                $types .= "i";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("UserManager::emailExists - " . $e->getMessage());
            return false;
        }
    }
}
