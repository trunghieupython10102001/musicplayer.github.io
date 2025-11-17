<?php
/**
 * List User Playlists API
 * GET /api/playlists/list.php
 * 
 * Get all playlists for the current user
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

// Get user's playlists with song count
$query = "SELECT p.id, p.name, p.description, p.is_public, p.cover_image, 
                 p.created_at, p.updated_at,
                 COUNT(ps.song_id) as song_count
          FROM playlists p
          LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
          WHERE p.user_id = ?
          GROUP BY p.id
          ORDER BY p.created_at DESC";

$playlists = $db->select($query, [$userId]);

// Format data
foreach ($playlists as &$playlist) {
    $playlist['created_at_formatted'] = timeAgo($playlist['created_at']);
}

jsonResponse(true, 'Playlists retrieved successfully', [
    'playlists' => $playlists
]);

