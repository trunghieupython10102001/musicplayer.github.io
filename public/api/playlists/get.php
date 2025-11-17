<?php
/**
 * Get Playlist Songs API
 * GET /api/playlists/get.php?id=1
 * 
 * Get all songs in a playlist
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

// Get playlist ID
$playlistId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($playlistId <= 0) {
    jsonResponse(false, 'Invalid playlist ID', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Get playlist details
$playlist = $db->selectOne(
    "SELECT p.*, COUNT(ps.song_id) as song_count
     FROM playlists p
     LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
     WHERE p.id = ?
     GROUP BY p.id",
    [$playlistId]
);

if (!$playlist) {
    jsonResponse(false, 'Playlist not found', [], 404);
}

// Check permissions
if ($playlist['user_id'] != $userId && !$playlist['is_public']) {
    jsonResponse(false, 'You do not have permission to view this playlist', [], 403);
}

// Get songs in playlist
$query = "SELECT s.*, ps.position, ps.added_at
          FROM playlist_songs ps
          JOIN songs s ON ps.song_id = s.id
          WHERE ps.playlist_id = ?
          ORDER BY ps.position ASC";

$songs = $db->select($query, [$playlistId]);

// Format songs data
foreach ($songs as &$song) {
    $song['duration_formatted'] = formatDuration($song['duration']);
    
    // Check if it's an uploaded file
    $isUploaded = strpos($song['cover_image'], '_') !== false;
    
    if ($isUploaded) {
        $song['cover_url'] = UPLOAD_URL . '/covers/' . $song['cover_image'];
        $song['audio_url'] = UPLOAD_URL . '/songs/' . $song['file_path'];
    } else {
        $song['cover_url'] = ASSETS_URL . '/img/' . $song['cover_image'];
        $song['audio_url'] = ASSETS_URL . '/audio/' . $song['file_path'];
    }
}

jsonResponse(true, 'Playlist retrieved successfully', [
    'playlist' => $playlist,
    'songs' => $songs
]);

