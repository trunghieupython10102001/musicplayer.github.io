<?php
// /public/api/albums/list.php
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

// --- End of manual inclusions ---

try {
    $pdo = getDB();
    $query = "
        SELECT 
            album, 
            COUNT(id) as song_count,
            (SELECT cover_image FROM songs s2 WHERE s2.album = s1.album ORDER BY id LIMIT 1) as cover_image
        FROM songs s1
        WHERE album IS NOT NULL AND album != ''
        GROUP BY album
        ORDER BY album
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($albums as &$album) {
        $album['cover_url'] = get_cover_url($album['cover_image']);
    }

    echo json_encode(['success' => true, 'data' => ['albums' => $albums]]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

