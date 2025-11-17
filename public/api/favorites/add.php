<?php
/**
 * Add to Favorites API
 * POST /api/favorites/add.php
 * 
 * Add a song to user's favorites
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

// Get JSON input
$input = getJsonInput();
$songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;

if ($songId <= 0) {
    jsonResponse(false, 'Invalid song ID', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Check if song exists
$songExists = $db->selectOne("SELECT id, title FROM songs WHERE id = ?", [$songId]);

if (!$songExists) {
    jsonResponse(false, 'Song not found', [], 404);
}

// Check if already favorited
$alreadyFavorited = $db->selectOne(
    "SELECT id FROM favorites WHERE user_id = ? AND song_id = ?",
    [$userId, $songId]
);

if ($alreadyFavorited) {
    jsonResponse(false, 'Song already in favorites', [], 409);
}

// Add to favorites
$query = "INSERT INTO favorites (user_id, song_id, added_at) VALUES (?, ?, NOW())";

try {
    $result = $db->execute($query, [$userId, $songId]);
    
    if ($result) {
        logActivity("Added song {$songId} to favorites");
        jsonResponse(true, 'Song added to favorites', [
            'song_id' => $songId,
            'song_title' => $songExists['title']
        ]);
    } else {
        jsonResponse(false, 'Failed to add to favorites', [], 500);
    }
} catch (Exception $e) {
    error_log("Add to favorites error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

