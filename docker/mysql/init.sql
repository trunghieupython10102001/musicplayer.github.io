-- Music Player Database Schema
-- MySQL initialization script

-- Set charset and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Use the database
USE musicplayer;

-- ============================================
-- Table: users
-- Stores user account information
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    profile_picture VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: songs
-- Stores song metadata and file information
-- ============================================
CREATE TABLE IF NOT EXISTS songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist VARCHAR(255) NOT NULL,
    album VARCHAR(255) DEFAULT NULL,
    duration INT DEFAULT NULL COMMENT 'Duration in seconds',
    file_path VARCHAR(500) NOT NULL,
    cover_image VARCHAR(500) DEFAULT NULL,
    genre VARCHAR(100) DEFAULT NULL,
    release_year INT DEFAULT NULL,
    play_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_artist (artist),
    INDEX idx_genre (genre),
    FULLTEXT idx_search (title, artist, album)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: playlists
-- Stores user-created playlists
-- ============================================
CREATE TABLE IF NOT EXISTS playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    cover_image VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: playlist_songs
-- Many-to-many relationship between playlists and songs
-- ============================================
CREATE TABLE IF NOT EXISTS playlist_songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT NOT NULL,
    song_id INT NOT NULL,
    position INT DEFAULT 0 COMMENT 'Order of song in playlist',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_playlist_song (playlist_id, song_id),
    INDEX idx_playlist_id (playlist_id),
    INDEX idx_song_id (song_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: favorites
-- Stores user favorite songs
-- ============================================
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    song_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_song (user_id, song_id),
    INDEX idx_user_id (user_id),
    INDEX idx_song_id (song_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: play_history
-- Tracks user listening history
-- ============================================
CREATE TABLE IF NOT EXISTS play_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    song_id INT NOT NULL,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_played INT DEFAULT NULL COMMENT 'How long the song was played in seconds',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_song_id (song_id),
    INDEX idx_played_at (played_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Admin User
-- Username: admin
-- Password: admin123 (hashed with bcrypt)
-- ============================================
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@musicplayer.com', '$2y$10$jYjQhlw45UNgwGYto1xPaOv4W0IjE4tfzyOPJS2A9eLkLoXZkj4D.', 'admin');

-- ============================================
-- Insert Existing Songs from Current Project
-- Mapping all 78 songs from assets/audio folder
-- ============================================

-- English Songs
INSERT INTO songs (title, artist, album, file_path, cover_image, genre) VALUES
('Attention', 'Charlie Puth', NULL, 'Attention.mp3', 'Attention.jpeg', 'Pop'),
('Blank Space', 'Taylor Swift', NULL, 'Blank space.mp3', 'Blank space.jpeg', 'Pop'),
('Dusk Till Dawn', 'Zayn ft. Sia', NULL, 'Dusk till down.mp3', 'Dusk till down.jpeg', 'Pop'),
('Girls Like You', 'Maroon 5', NULL, 'Girls like you.mp3', 'Girls like you.jpeg', 'Pop'),
('Hello', 'Adele', NULL, 'Hello.mp3', 'Hello.jpeg', 'Pop'),
('Despacito', 'Luis Fonsi ft. Daddy Yankee', NULL, 'Despacito.mp3', 'Despacito.jpeg', 'Latin Pop'),
('Believer', 'Imagine Dragons', NULL, 'Believer.mp3', 'Believer.jpeg', 'Rock'),
('Call Me Maybe', 'Carly Rae Jepsen', NULL, 'Call me maybe.mp3', 'Call me maybe.jpeg', 'Pop'),
('Cheap Thrills', 'Sia', NULL, 'Cheap Thrills.mp3', 'Cheap Thrills.jpeg', 'Pop'),
('Counting Stars', 'OneRepublic', NULL, 'Counting stars.mp3', 'Counting stars.jpeg', 'Pop'),
('Dance Monkey', 'Tones and I', NULL, 'Dance Monkey.mp3', 'Dance Monkey.jpeg', 'Pop'),
('Happier', 'Ed Sheeran', NULL, 'Happier.mp3', 'Happier.jpeg', 'Pop'),
('Havana', 'Camila Cabello', NULL, 'Havana.mp3', 'Havana.jpeg', 'Pop'),
('Hymn For The Weekend', 'Coldplay', NULL, 'Hymn For The Weekend.mp3', 'Hymn For The Weekend.jpeg', 'Pop'),
('Just Give Me a Reason', 'P!nk ft. Nate Ruess', NULL, 'Just give me a reason.mp3', 'Just give me a reason.jpeg', 'Pop'),
('Just The Way You Are', 'Bruno Mars', NULL, 'Just the way you are.mp3', 'Just the way you are.jpeg', 'Pop'),
('Lalala', 'Naughty Boy ft. Sam Smith', NULL, 'Lalala.mp3', 'Lalala.jpeg', 'Pop'),
('Lemon Tree', 'Fool\'s Garden', NULL, 'Lemon tree.mp3', 'Lemon tree.jpeg', 'Pop'),
('Love Is Gone', 'Slander', NULL, 'Love is gone.mp3', 'Love is gone.jpeg', 'Electronic'),
('Love Yourself', 'Justin Bieber', NULL, 'Love Yourself.mp3', 'Love Yourself.jpeg', 'Pop'),
('Maps', 'Maroon 5', NULL, 'Maps.mp3', 'Maps.jpeg', 'Pop'),
('My Love', 'Westlife', NULL, 'My Love.mp3', 'My Love.jpeg', 'Pop'),
('New Rules', 'Dua Lipa', NULL, 'New Rules.mp3', 'New Rules.jpeg', 'Pop'),
('One More Night', 'Maroon 5', NULL, 'One more night.mp3', 'One more night.jpeg', 'Pop'),
('Photograph', 'Ed Sheeran', NULL, 'Photograph.mp3', 'Photograph.jpeg', 'Pop'),
('Roar', 'Katy Perry', NULL, 'Road.mp3', 'Road.jpeg', 'Pop'),
('Rolling in the Deep', 'Adele', NULL, 'Rolling in the deep.mp3', 'Rolling in the deep.jpeg', 'Pop'),
('See You Again', 'Wiz Khalifa ft. Charlie Puth', NULL, 'See you again.mp3', 'See you again.jpeg', 'Hip Hop'),
('Señorita', 'Camila Cabello & Shawn Mendes', NULL, 'Senorita.mp3', 'Senorita.jpeg', 'Pop'),
('Set Fire to the Rain', 'Adele', NULL, 'Set fire to the rain.mp3', 'Set fire to the rain.jpeg', 'Pop'),
('Shape of You', 'Ed Sheeran', NULL, 'Shape of you.mp3', 'Shape of you.jpeg', 'Pop'),
('She Will Be Loved', 'Maroon 5', NULL, 'She will be loved.mp3', 'She will be loved.jpeg', 'Pop'),
('Someone Like You', 'Adele', NULL, 'Someone like you.mp3', 'Someone like you.jpeg', 'Pop'),
('Something Just Like This', 'The Chainsmokers & Coldplay', NULL, 'Something just like this.mp3', 'Something just like this.jpeg', 'Electronic'),
('Sugar', 'Maroon 5', NULL, 'Sugar.mp3', 'Sugar.jpeg', 'Pop'),
('Talking to the Moon', 'Bruno Mars', NULL, 'Talking to the moon.mp3', 'Talking to the moon.jpeg', 'Pop'),
('That Girl', 'Olly Murs', NULL, 'That girl.mp3', 'That girl.jpeg', 'Pop'),
('Titanium', 'David Guetta ft. Sia', NULL, 'Titanium.mp3', 'Titanium.jpeg', 'Electronic'),
('Treat You Better', 'Shawn Mendes', NULL, 'Treat you better.mp3', 'Treat you better.jpeg', 'Pop'),
('Uptown Funk', 'Mark Ronson ft. Bruno Mars', NULL, 'Uptown Funk.mp3', 'Uptown Funk.jpeg', 'Funk'),
('Waiting for Love', 'Avicii', NULL, 'Waitting for love.mp3', 'Waitting for love.jpeg', 'Electronic'),
('We Don\'t Talk Anymore', 'Charlie Puth ft. Selena Gomez', NULL, 'We Don''t Talk Anymore.mp3', 'We Don''t Talk Anymore.jpeg', 'Pop'),
('What Makes You Beautiful', 'One Direction', NULL, 'What makes you beautiful.mp3', 'What makes you beautiful.jpeg', 'Pop'),
('When I Was Your Man', 'Bruno Mars', NULL, 'When I was your man.mp3', 'When I was your man.jpeg', 'Pop');

-- Vietnamese Songs
INSERT INTO songs (title, artist, album, file_path, cover_image, genre) VALUES
('1 Phút', 'Andiez', NULL, '1 phut.mp3', '1 phut.jpeg', 'V-Pop'),
('Ai Là Người Thương Em', 'Quân A.P', NULL, 'Ai la nguoi thuong em.mp3', 'Ai la nguoi thuong em.jpeg', 'V-Pop'),
('Ai Mang Cô Đơn Đi', 'K-ICM ft. APJ', NULL, 'Ai mang co don di.mp3', 'Ai mang co don di.jpeg', 'V-Pop'),
('Bạc Phận', 'Jack & K-ICM', NULL, 'Bac phan.mp3', 'Bac phan.jpeg', 'V-Pop'),
('Bài Này Chill Phết', 'Đen Vâu ft. MIN', NULL, 'Bai nay chill phet.mp3', 'Bai nay chill phet.jpeg', 'V-Pop'),
('Bánh Mì Không', 'Đạt G & Du Uyên', NULL, 'Banh mi khong.mp3', 'Banh mi khong.jpeg', 'V-Pop'),
('Bông Hoa Đẹp Nhất', 'Quân A.P', NULL, 'Bong hoa dep nhat.mp3', 'Bong hoa dep nhat.jpeg', 'V-Pop'),
('Buồn Làm Chi Em Ơi', 'Hoài Lâm', NULL, 'Buon lam chi em oi.mp3', 'Buon lam chi em oi.jpeg', 'V-Pop'),
('Chiều Hôm Ấy', 'Jaykii', NULL, 'Chieu hom ay.mp3', 'Chieu hom ay.jpeg', 'V-Pop'),
('Đã Lỡ Yêu Em Nhiều', 'JustaTee', NULL, 'Da lo yeu em nhieu.mp3', 'Da lo yeu em nhieu.jpeg', 'V-Pop'),
('Đưa Nhau Đi Trốn', 'Đen Vâu ft. Linh Cáo', NULL, 'Dua nhau di tron.mp3', 'Dua nhau di tron.jpeg', 'V-Pop'),
('Hai Triệu Năm', 'Đen Vâu ft. Biên', NULL, 'Hai trieu nam.mp3', 'Hai trieu nam.jpeg', 'V-Pop'),
('Hết Thương Cạn Nhớ', 'Đức Phúc', NULL, 'Het thuong can nho.mp3', 'Het thuong can nho.jpeg', 'V-Pop'),
('Hoa Hải Đường', 'Jack', NULL, 'Hoa hai duong.mp3', 'Hoa hai duong.jpeg', 'V-Pop'),
('Hoa Nở Không Màu', 'Hoài Lâm', NULL, 'Hoa no khong mau.mp3', 'Hoa no khong mau.jpeg', 'V-Pop'),
('HongKong1', 'Nguyễn Trọng Tài x San Ji', NULL, 'HongKong1.mp3', 'HongKong1.jpeg', 'V-Rap'),
('Lạ Lùng', 'Vũ', NULL, 'La lung.mp3', 'La lung.jpeg', 'V-Pop'),
('Loving Sunny', 'Kimmese ft. Đen Vâu', NULL, 'Loving sunny.mp3', 'Loving sunny.jpeg', 'V-Pop'),
('Một Bước Yêu Vạn Dặm Đau', 'Mr. Siro', NULL, 'Mot buoc yeu van dam dau.mp3', 'Mot buoc yeu van dam dau.jpeg', 'V-Pop'),
('Một Đêm Say', 'Thịnh Suy', NULL, 'Mot dem say.mp3', 'Mot dem say.jpeg', 'V-Pop'),
('Mượn Rượu Tỏ Tình', 'Big Daddy ft. Emily', NULL, 'Muon ruou to tinh.mp3', 'Muon ruou to tinh.jpeg', 'V-Pop'),
('Nắm Lấy Tay Anh', 'Tuấn Hưng', NULL, 'Nam lay tay anh.mp3', 'Nam lay tay anh.jpeg', 'V-Pop'),
('Nàng Thơ', 'Đình Dũng', NULL, 'Nang tho.mp3', 'Nang tho.jpeg', 'V-Pop'),
('Ngắm Hoa Lệ Rơi', 'Châu Khải Phong', NULL, 'Ngam hoa le roi.mp3', 'Ngam hoa le roi.jpeg', 'V-Pop'),
('Ngày Mai Em Đi', 'Lê Hiếu & Soobin Hoàng Sơn', NULL, 'Ngay mai em di.mp3', 'Ngay mai em di.jpeg', 'V-Pop'),
('Người Lạ Ơi', 'Karik & Orange', NULL, 'Nguoi la oi.mp3', 'Nguoi la oi.jpeg', 'V-Pop'),
('Sai Người Sai Thời Điểm', 'Thanh Hưng', NULL, 'Sai nguoi sai thoi diem.mp3', 'Sai nguoi sai thoi diem.jpeg', 'V-Pop'),
('Sóng Gió', 'Jack & K-ICM', NULL, 'Song gio.mp3', 'Song gio.jpeg', 'V-Pop'),
('Thằng Điên', 'JustaTee ft. Phương Ly', NULL, 'Thang dien.mp3', 'Thang dien.jpeg', 'V-Pop'),
('Thay Tôi Yêu Cô Ấy', 'Thanh Hưng', NULL, 'Thay toi yeu co ay.mp3', 'Thay toi yeu co ay.jpeg', 'V-Pop'),
('Thì Thôi', 'Reddy', NULL, 'Thi thoi.mp3', 'Thi thoi.jpeg', 'V-Pop'),
('Tình Đắng Như Ly Cà Phê', 'Nân & Ngơ', NULL, 'Tinh dang nhu ly ca phe.mp3', 'Tinh dang nhu ly ca phe.jpeg', 'V-Pop'),
('Trốn Tìm', 'Đen Vâu ft. MTV Band', NULL, 'Tron tim.mp3', 'Tron tim.jpeg', 'V-Pop'),
('Vô Cùng', 'Phan Duy Anh', NULL, 'Vo cung.mp3', 'Vo cung.jpeg', 'V-Pop');

-- ============================================
-- Create Indexes for Better Performance
-- ============================================
-- Already created above with table definitions

-- ============================================
-- Success Message
-- ============================================
SELECT 'Database initialization completed successfully!' as message;
SELECT CONCAT('Total songs inserted: ', COUNT(*)) as count FROM songs;
SELECT CONCAT('Admin user created: admin@musicplayer.com (password: admin123)') as admin_info;

