-- Migration: Create password_reset_tokens table
-- Date: 2024-12-25
-- Description: Adds password reset functionality to the application

-- Check if table exists and create if not
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean up expired tokens (optional, can be run periodically)
-- DELETE FROM password_reset_tokens WHERE expires_at < NOW();

SELECT 'Password reset tokens table created successfully!' as message;
