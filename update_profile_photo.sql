-- Add profile_photo column to users table
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) DEFAULT 'avatar-1.jpg' AFTER alamat;

-- Update existing admin user with default photo
UPDATE users SET profile_photo = 'avatar-1.jpg' WHERE role = 'admin';
