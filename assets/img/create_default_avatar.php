<?php
// Simple script to create a default avatar if it doesn't exist

$avatar_path = 'default-avatar.png';

if (!file_exists($avatar_path)) {
    // Create a simple colored circle as default avatar
    $size = 120;
    $image = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    // Create transparent background
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    
    // Create gradient colors
    $color1 = imagecolorallocate($image, 102, 126, 234); // #667eea
    $color2 = imagecolorallocate($image, 118, 75, 162);  // #764ba2
    
    // Draw gradient circle
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $distance = sqrt(pow($x - $size/2, 2) + pow($y - $size/2, 2));
            if ($distance <= $size/2) {
                $ratio = $distance / ($size/2);
                $r = 102 + ($ratio * (118 - 102));
                $g = 126 + ($ratio * (75 - 126));
                $b = 234 + ($ratio * (162 - 234));
                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }
    
    // Add user icon in the center
    $icon_color = imagecolorallocate($image, 255, 255, 255);
    
    // Draw simple user icon (circle for head, rounded rectangle for body)
    $head_size = $size * 0.3;
    $head_x = $size / 2;
    $head_y = $size * 0.35;
    
    // Head circle
    imagefilledellipse($image, $head_x, $head_y, $head_size, $head_size, $icon_color);
    
    // Body
    $body_width = $size * 0.5;
    $body_height = $size * 0.4;
    $body_x = $size / 2 - $body_width / 2;
    $body_y = $size * 0.65;
    
    imagefilledrectangle($image, $body_x, $body_y, $body_x + $body_width, $body_y + $body_height, $icon_color);
    
    // Save the image
    imagepng($image, $avatar_path);
    imagedestroy($image);
}

echo "Default avatar created successfully!";
?>
