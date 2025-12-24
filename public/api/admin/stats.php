<?php
/**
 * Admin Statistics API
 * Returns comprehensive statistics for the admin dashboard
 */

require_once '../../../includes/config.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/database.php';

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        exit();
    }

    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Admin access required'
        ]);
        exit();
    }

    $db = getDB();

    $stats = [
        'total_songs' => 0,
        'total_users' => 0,
        'total_plays' => 0,
        'total_favorites' => 0,
        'total_playlists' => 0,
        'recent_users' => 0,
        'top_genre' => null,
        'most_played_song' => null,
        'genre_distribution' => [],
        'recent_activity' => []
    ];

    // Total songs
    $stmt = $db->query("SELECT COUNT(*) as count FROM songs");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_songs'] = (int)$result['count'];

    // Total users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_users'] = (int)$result['count'];

    // Total plays from play_history
    $stmt = $db->query("SELECT COUNT(*) as count FROM play_history");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_plays'] = (int)$result['count'];

    // Total favorites
    $stmt = $db->query("SELECT COUNT(*) as count FROM favorites");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_favorites'] = (int)$result['count'];

    // Total playlists
    $stmt = $db->query("SELECT COUNT(*) as count FROM playlists");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_playlists'] = (int)$result['count'];

    // Recent users (last 7 days)
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['recent_users'] = (int)$result['count'];

    // Top genre
    $stmt = $db->query("
        SELECT genre, COUNT(*) as count 
        FROM songs 
        WHERE genre IS NOT NULL AND genre != '' 
        GROUP BY genre 
        ORDER BY count DESC 
        LIMIT 1
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats['top_genre'] = [
            'name' => $result['genre'],
            'count' => (int)$result['count']
        ];
    }

    // Most played song
    $stmt = $db->query("
        SELECT s.id, s.title, s.artist, COUNT(ph.id) as play_count
        FROM songs s
        LEFT JOIN play_history ph ON s.id = ph.song_id
        GROUP BY s.id
        ORDER BY play_count DESC
        LIMIT 1
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $stats['most_played_song'] = [
            'id' => (int)$result['id'],
            'title' => $result['title'],
            'artist' => $result['artist'],
            'play_count' => (int)$result['play_count']
        ];
    }

    // Genre distribution
    $stmt = $db->query("
        SELECT genre, COUNT(*) as count 
        FROM songs 
        WHERE genre IS NOT NULL AND genre != '' 
        GROUP BY genre 
        ORDER BY count DESC
    ");
    $genres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($genres as $genre) {
        $stats['genre_distribution'][] = [
            'genre' => $genre['genre'],
            'count' => (int)$genre['count']
        ];
    }

    // Recent activity (last 10 plays)
    $stmt = $db->query("
        SELECT 
            ph.played_at,
            s.title as song_title,
            s.artist as song_artist,
            u.username
        FROM play_history ph
        JOIN songs s ON ph.song_id = s.id
        JOIN users u ON ph.user_id = u.id
        ORDER BY ph.played_at DESC
        LIMIT 10
    ");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($activities as $activity) {
        $stats['recent_activity'][] = [
            'username' => $activity['username'],
            'song_title' => $activity['song_title'],
            'song_artist' => $activity['song_artist'],
            'played_at' => $activity['played_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);

} catch (Exception $e) {
    error_log("Stats API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch statistics'
    ]);
}
