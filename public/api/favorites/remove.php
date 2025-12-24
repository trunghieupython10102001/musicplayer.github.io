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

try {
    $db->beginTransaction();

    // Check if the favorite exists before deleting
    $favoriteExists = $db->selectOne(
        "SELECT id FROM favorites WHERE user_id = ? AND song_id = ?",
        [$userId, $songId]
    );

    if (!$favoriteExists) {
        $db->rollback();
        jsonResponse(false, 'Song not found in favorites', [], 404);
        return;
    }

    // Remove from favorites
    $result = $db->execute(
        "DELETE FROM favorites WHERE user_id = ? AND song_id = ?",
        [$userId, $songId]
    );
    
    // Decrement likes count in song_stats table
    $db->execute(
        "UPDATE song_stats SET likes_count = GREATEST(0, likes_count - 1) WHERE song_id = ?",
        [$songId]
    );

    $db->commit();

    logActivity("Removed song {$songId} from favorites");
    jsonResponse(true, 'Song removed from favorites');

} catch (Exception $e) {
    $db->rollback();
    error_log("Remove from favorites error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

