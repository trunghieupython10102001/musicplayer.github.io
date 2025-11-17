<?php
/**
 * Update Song API (Admin Only)
 * PUT /api/songs/update.php
 * 
 * Update song metadata
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in and be admin
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

requireAdmin();

// Get JSON input
$input = getJsonInput();
$songId = isset($input['id']) ? (int)$input['id'] : 0;
$title = isset($input['title']) ? sanitizeInput($input['title']) : '';
$artist = isset($input['artist']) ? sanitizeInput($input['artist']) : '';
$album = isset($input['album']) ? sanitizeInput($input['album']) : '';
$genre = isset($input['genre']) ? sanitizeInput($input['genre']) : '';
$releaseYear = isset($input['release_year']) ? (int)$input['release_year'] : null;

// Validate
if ($songId <= 0) {
    jsonResponse(false, 'Invalid song ID', [], 400);
}

if (empty($title) || empty($artist)) {
    jsonResponse(false, 'Title and artist are required', [], 400);
}

$db = Database::getInstance();

// Check if song exists
$song = $db->selectOne("SELECT id FROM songs WHERE id = ?", [$songId]);

if (!$song) {
    jsonResponse(false, 'Song not found', [], 404);
}

// Update song
$query = "UPDATE songs SET 
          title = ?, 
          artist = ?, 
          album = ?, 
          genre = ?, 
          release_year = ?,
          updated_at = NOW()
          WHERE id = ?";

try {
    $result = $db->execute($query, [
        $title,
        $artist,
        $album,
        $genre,
        $releaseYear,
        $songId
    ]);
    
    if ($result) {
        logActivity("Updated song: {$title}");
        
        jsonResponse(true, 'Song updated successfully', [
            'song_id' => $songId,
            'title' => $title,
            'artist' => $artist
        ]);
    } else {
        jsonResponse(false, 'Failed to update song', [], 500);
    }
} catch (Exception $e) {
    error_log("Update song error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

