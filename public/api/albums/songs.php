<?php
// /public/api/albums/songs.php
header('Content-Type: application/json');

// --- Manually included dependencies to resolve include path issues ---

// From includes/config.php
define('SITE_URL', 'http://localhost:8080');
define('DB_HOST', 'mysql');
define('DB_NAME', 'musicplayer');
define('DB_USER', 'musicplayer_user');
define('DB_PASS', 'musicplayer_pass');
define('DB_CHARSET', 'utf8mb4');

// From includes/database.php
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

// From includes/functions.php
function get_cover_url($coverImage) {
    if (empty($coverImage)) {
        return SITE_URL . '/assets/img/default.png';
    }
    if (strpos($coverImage, '_') !== false) {
        return SITE_URL . '/uploads/covers/' . $coverImage;
    }
    return SITE_URL . '/assets/img/' . $coverImage;
}

function get_song_url($songFile) {
    if (empty($songFile)) {
        return '';
    }
    if (strpos($songFile, '_') !== false) {
        return SITE_URL . '/uploads/songs/' . $songFile;
    }
    return SITE_URL . '/assets/audio/' . $songFile;
}

// --- End of manual inclusions ---


$albumName = isset($_GET['name']) ? trim($_GET['name']) : '';

if (empty($albumName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Album name is required.']);
    exit;
}

try {
    $pdo = getDB();
    $query = "SELECT id, title, artist, album, cover_image, file_path FROM songs WHERE album = :album ORDER BY title";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':album', $albumName, PDO::PARAM_STR);
    $stmt->execute();
    
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format URLs
    foreach ($songs as &$song) {
        $song['cover_url'] = get_cover_url($song['cover_image']);
        $song['audio_url'] = get_song_url($song['file_path']);
    }

    echo json_encode(['success' => true, 'data' => ['songs' => $songs]]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
