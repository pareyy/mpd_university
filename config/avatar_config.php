<?php
/**
 * Avatar Configuration
 * Centralized avatar management for the application
 */

class AvatarConfig {
    // Avatar configuration constants
    const AVATAR_SIZE = 150;
    const API_BASE_URL = 'https://api.dicebear.com/8.x/lorelei/svg';
    const DEFAULT_MOOD = 'happy';
    
    // Avatar definitions
    private static $avatars = [
        'avatar-1.svg' => ['seed' => 'admin1', 'backgroundColor' => '667eea'],
        'avatar-2.svg' => ['seed' => 'admin2', 'backgroundColor' => 'e74c3c'],
        'avatar-3.svg' => ['seed' => 'admin3', 'backgroundColor' => '3498db'],
        'avatar-4.svg' => ['seed' => 'admin4', 'backgroundColor' => 'f39c12'],
        'avatar-5.svg' => ['seed' => 'admin5', 'backgroundColor' => '9b59b6'],
        'avatar-6.svg' => ['seed' => 'admin6', 'backgroundColor' => '1abc9c'],
        'avatar-7.svg' => ['seed' => 'admin7', 'backgroundColor' => 'e67e22'],
        'avatar-8.svg' => ['seed' => 'admin8', 'backgroundColor' => '34495e'],
        'avatar-9.svg' => ['seed' => 'admin9', 'backgroundColor' => 'e91e63'],
        'avatar-10.svg' => ['seed' => 'admin10', 'backgroundColor' => 'ff6b6b'],
        'avatar-11.svg' => ['seed' => 'admin11', 'backgroundColor' => '4ecdc4'],
        'avatar-12.svg' => ['seed' => 'admin12', 'backgroundColor' => 'ffe66d'],
        'avatar-13.svg' => ['seed' => 'admin13', 'backgroundColor' => '6c5ce7'],
        'avatar-14.svg' => ['seed' => 'admin14', 'backgroundColor' => 'a55eea'],
        'avatar-15.svg' => ['seed' => 'admin15', 'backgroundColor' => '26de81'],
        'avatar-16.svg' => ['seed' => 'admin16', 'backgroundColor' => '2bcbba'],
        'avatar-17.svg' => ['seed' => 'admin17', 'backgroundColor' => 'fd79a8'],
        'avatar-18.svg' => ['seed' => 'admin18', 'backgroundColor' => 'fdcb6e'],
        'avatar-19.svg' => ['seed' => 'admin19', 'backgroundColor' => 'e17055'],
        'avatar-20.svg' => ['seed' => 'admin20', 'backgroundColor' => '81ecec']
    ];
    
    /**
     * Get all available avatars
     * @return array
     */
    public static function getAvatars() {
        return self::$avatars;
    }
    
    /**
     * Get avatar names only
     * @return array
     */
    public static function getAvatarNames() {
        return array_keys(self::$avatars);
    }
    
    /**
     * Build avatar URL from configuration
     * @param string $avatar_name
     * @return string
     */
    public static function buildAvatarUrl($avatar_name) {
        if (!isset(self::$avatars[$avatar_name])) {
            $avatar_name = 'avatar-1.svg'; // Default fallback
        }
        
        $config = self::$avatars[$avatar_name];
        
        $params = http_build_query([
            'seed' => $config['seed'],
            'size' => self::AVATAR_SIZE,
            'backgroundColor' => $config['backgroundColor'],
            'mood' => self::DEFAULT_MOOD
        ]);
        
        return self::API_BASE_URL . '?' . $params;
    }
    
    /**
     * Check if avatar name is valid
     * @param string $avatar_name
     * @return bool
     */
    public static function isValidAvatar($avatar_name) {
        return isset(self::$avatars[$avatar_name]);
    }
    
    /**
     * Get default avatar name
     * @return string
     */
    public static function getDefaultAvatar() {
        return 'avatar-1.svg';
    }
}
