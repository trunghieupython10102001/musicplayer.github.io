<?php
/**
 * Add Song to Playlist API
 * POST /api/playlists/add-song.php
 * 
 * Add a song to a user's playlist
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
$playlistId = isset($input['playlist_id']) ? (int)$input['playlist_id'] : 0;
$songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;

if ($playlistId <= 0 || $songId <= 0) {
    jsonResponse(false, 'Invalid playlist or song ID', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Check if playlist belongs to user
$playlist = $db->selectOne(
    "SELECT id, user_id FROM playlists WHERE id = ?", 
    [$playlistId]
);

if (!$playlist) {
    jsonResponse(false, 'Playlist not found', [], 404);
}

if ($playlist['user_id'] != $userId) {
    jsonResponse(false, 'You do not have permission to modify this playlist', [], 403);
}

// Check if song exists
$songExists = $db->selectOne("SELECT id FROM songs WHERE id = ?", [$songId]);

if (!$songExists) {
    jsonResponse(false, 'Song not found', [], 404);
}

// Check if song already in playlist
$alreadyExists = $db->selectOne(
    "SELECT id FROM playlist_songs WHERE playlist_id = ? AND song_id = ?",
    [$playlistId, $songId]
);

if ($alreadyExists) {
    jsonResponse(false, 'Song already in playlist', [], 409);
}

// Get next position
$positionResult = $db->selectOne(
    "SELECT COALESCE(MAX(position), -1) + 1 as next_position 
     FROM playlist_songs 
     WHERE playlist_id = ?",
    [$playlistId]
);
$position = $positionResult['next_position'];

// Add song to playlist
$query = "INSERT INTO playlist_songs (playlist_id, song_id, position, added_at) 
          VALUES (?, ?, ?, NOW())";

try {
    $result = $db->execute($query, [$playlistId, $songId, $position]);
    
    if ($result) {
        logActivity("Added song {$songId} to playlist {$playlistId}");
        jsonResponse(true, 'Song added to playlist successfully', [
            'playlist_id' => $playlistId,
            'song_id' => $songId
        ]);
    } else {
        jsonResponse(false, 'Failed to add song to playlist', [], 500);
    }
} catch (Exception $e) {
    error_log("Add song to playlist error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

