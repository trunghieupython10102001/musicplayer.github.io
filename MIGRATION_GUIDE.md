# Database Migration Guide

## Running Migrations on a New Machine

If you're setting up this project on a new machine and encountering database-related errors, you may need to run migrations.

### Common Issues

#### "Failed to generate reset token"
This error occurs when the `password_reset_tokens` table doesn't exist in your database.

**Solution:**

1. **Using Docker:**
   ```bash
   docker-compose exec mysql mysql -u musicplayer -pmusicplayer123 musicplayer < docker/mysql/migrations/001_create_password_reset_tokens.sql
   ```

2. **Using MySQL CLI directly:**
   ```bash
   mysql -u musicplayer -pmusicplayer123 musicplayer < docker/mysql/migrations/001_create_password_reset_tokens.sql
   ```

3. **Using phpMyAdmin or MySQL Workbench:**
   - Open the migration file: `docker/mysql/migrations/001_create_password_reset_tokens.sql`
   - Copy the SQL content
   - Execute it in your database tool

### Verifying the Migration

After running the migration, verify the table was created:

```sql
SHOW TABLES LIKE 'password_reset_tokens';
DESCRIBE password_reset_tokens;
```

### Full Database Initialization

If you're starting fresh, you can run the complete initialization script:

```bash
docker-compose exec mysql mysql -u musicplayer -pmusicplayer123 musicplayer < docker/mysql/init.sql
```

This will create all tables including:
- users
- songs
- playlists
- playlist_songs
- favorites
- play_history
- song_stats
- password_reset_tokens

### Troubleshooting

**Error: Table already exists**
- This is safe to ignore. The migration uses `CREATE TABLE IF NOT EXISTS`.

**Error: Foreign key constraint fails**
- Make sure the `users` table exists first
- Run the full `init.sql` script

**Error: Access denied**
- Check your database credentials in `includes/config.php`
- Ensure the database user has CREATE and INSERT privileges

### Manual Table Creation

If migrations don't work, you can manually create the table by running this SQL:

```sql
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
```

### Checking Server Logs

If you continue to have issues, check the PHP error logs:

**Docker:**
```bash
docker-compose logs php
```

**Apache/Nginx:**
```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

The logs will show detailed error messages that can help diagnose the issue.
