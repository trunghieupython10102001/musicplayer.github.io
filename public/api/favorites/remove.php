<?php
/**
 * Remove from Favorites API
 * DELETE /api/favorites/remove.php?id=1
 * 
 * Remove a song from user's favorites
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

// Get song ID from query or JSON
$songId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($songId <= 0) {
    $input = getJsonInput();
    $songId = isset($input['song_id']) ? (int)$input['song_id'] : 0;
}

if ($songId <= 0) {
    jsonResponse(false, 'Invalid song ID', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Remove from favorites
$result = $db->execute(
    "DELETE FROM favorites WHERE user_id = ? AND song_id = ?",
    [$userId, $songId]
);

if ($result) {
    logActivity("Removed song {$songId} from favorites");
    jsonResponse(true, 'Song removed from favorites');
} else {
    jsonResponse(false, 'Song not found in favorites', [], 404);
}

