<?php
/**
 * Remove Song from Playlist API
 * DELETE /api/playlists/remove-song.php
 * 
 * Remove a song from a user's playlist
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

// Get input from query or JSON
$playlistId = isset($_GET['playlist_id']) ? (int)$_GET['playlist_id'] : 0;
$songId = isset($_GET['song_id']) ? (int)$_GET['song_id'] : 0;

if ($playlistId <= 0 || $songId <= 0) {
    $input = getJsonInput();
    $playlistId = isset($input['playlist_id']) ? (int)$input['playlist_id'] : $playlistId;
    $songId = isset($input['song_id']) ? (int)$input['song_id'] : $songId;
}

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

// Remove song from playlist
$result = $db->execute(
    "DELETE FROM playlist_songs WHERE playlist_id = ? AND song_id = ?",
    [$playlistId, $songId]
);

if ($result) {
    logActivity("Removed song {$songId} from playlist {$playlistId}");
    jsonResponse(true, 'Song removed from playlist successfully');
} else {
    jsonResponse(false, 'Song not found in playlist', [], 404);
}

