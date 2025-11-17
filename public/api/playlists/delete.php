<?php
/**
 * Delete Playlist API
 * DELETE /api/playlists/delete.php?id=1
 * 
 * Delete a user's playlist
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

// Get playlist ID from query or JSON input
$playlistId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($playlistId <= 0) {
    $input = getJsonInput();
    $playlistId = isset($input['id']) ? (int)$input['id'] : 0;
}

if ($playlistId <= 0) {
    jsonResponse(false, 'Invalid playlist ID', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Check if playlist belongs to user
$playlist = $db->selectOne(
    "SELECT id, name, user_id FROM playlists WHERE id = ?", 
    [$playlistId]
);

if (!$playlist) {
    jsonResponse(false, 'Playlist not found', [], 404);
}

if ($playlist['user_id'] != $userId) {
    jsonResponse(false, 'You do not have permission to delete this playlist', [], 403);
}

// Delete playlist (cascade will delete playlist_songs)
$result = $db->execute("DELETE FROM playlists WHERE id = ?", [$playlistId]);

if ($result) {
    logActivity("Deleted playlist: {$playlist['name']}");
    jsonResponse(true, 'Playlist deleted successfully');
} else {
    jsonResponse(false, 'Failed to delete playlist', [], 500);
}

