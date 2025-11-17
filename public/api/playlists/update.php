<?php
/**
 * Update Playlist API
 * PUT /api/playlists/update.php
 * 
 * Update playlist name and description
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

// Get JSON input
$input = getJsonInput();
$playlistId = isset($input['id']) ? (int)$input['id'] : 0;
$name = isset($input['name']) ? sanitizeInput($input['name']) : '';
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';

// Validate
if ($playlistId <= 0) {
    jsonResponse(false, 'Invalid playlist ID', [], 400);
}

if (empty($name)) {
    jsonResponse(false, 'Playlist name is required', [], 400);
}

if (strlen($name) > 255) {
    jsonResponse(false, 'Playlist name is too long', [], 400);
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
    jsonResponse(false, 'You do not have permission to edit this playlist', [], 403);
}

// Update playlist
$query = "UPDATE playlists SET name = ?, description = ?, updated_at = NOW() WHERE id = ?";

try {
    $result = $db->execute($query, [$name, $description, $playlistId]);
    
    if ($result) {
        logActivity("Updated playlist: {$name}");
        
        jsonResponse(true, 'Playlist updated successfully', [
            'playlist_id' => $playlistId,
            'name' => $name
        ]);
    } else {
        jsonResponse(false, 'Failed to update playlist', [], 500);
    }
} catch (Exception $e) {
    error_log("Update playlist error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

