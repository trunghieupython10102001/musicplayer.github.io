<?php
/**
 * Most Liked Songs API
 * GET /api/stats/most-liked.php
 * 
 * Get the most liked (favorited) songs
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($limit <= 0) {
    $limit = 10;
}

$db = Database::getInstance();

try {
    $query = "
        SELECT s.id, s.title, s.artist, s.cover_image, st.likes_count
        FROM songs s
        JOIN song_stats st ON s.id = st.song_id
        ORDER BY st.likes_count DESC
        LIMIT ?
    ";
    
    $songs = $db->select($query, [$limit]);
    
    jsonResponse(true, 'Most liked songs retrieved successfully', $songs);
    
} catch (Exception $e) {
    error_log("Most liked songs error: " . $e->getMessage());
    jsonResponse(false, 'Failed to retrieve most liked songs', [], 500);
}
