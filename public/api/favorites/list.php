<?php
/**
 * List Favorite Songs API
 * GET /api/favorites/list.php
 * 
 * Get all favorite songs for the current user
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Get user's favorite songs
$query = "SELECT s.id, s.title, s.artist, s.album, s.duration, s.file_path, 
                 s.cover_image, s.genre, s.play_count, f.added_at
          FROM favorites f
          JOIN songs s ON f.song_id = s.id
          WHERE f.user_id = ?
          ORDER BY f.added_at DESC";

$favorites = $db->select($query, [$userId]);

// Format data
foreach ($favorites as &$favorite) {
    $favorite['duration_formatted'] = formatDuration($favorite['duration']);
    $favorite['cover_url'] = ASSETS_URL . '/img/' . $favorite['cover_image'];
    $favorite['audio_url'] = ASSETS_URL . '/audio/' . $favorite['file_path'];
    $favorite['added_at_formatted'] = timeAgo($favorite['added_at']);
}

jsonResponse(true, 'Favorites retrieved successfully', [
    'favorites' => $favorites,
    'count' => count($favorites)
]);

