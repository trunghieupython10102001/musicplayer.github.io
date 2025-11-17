<?php
/**
 * Log Song Play API
 * POST /api/songs/play.php
 * 
 * Log when a user plays a song (updates play count and history)
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
$durationPlayed = isset($input['duration_played']) ? (int)$input['duration_played'] : null;

if ($songId <= 0) {
    jsonResponse(false, 'Invalid song ID', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

try {
    // Check if song exists
    $songExists = $db->selectOne("SELECT id FROM songs WHERE id = ?", [$songId]);
    
    if (!$songExists) {
        jsonResponse(false, 'Song not found', [], 404);
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Increment play count
    $db->execute("UPDATE songs SET play_count = play_count + 1 WHERE id = ?", [$songId]);
    
    // Log to play history
    $db->execute(
        "INSERT INTO play_history (user_id, song_id, duration_played, played_at) 
         VALUES (?, ?, ?, NOW())",
        [$userId, $songId, $durationPlayed]
    );
    
    // Commit transaction
    $db->commit();
    
    jsonResponse(true, 'Play logged successfully');
    
} catch (Exception $e) {
    $db->rollback();
    error_log("Play log error: " . $e->getMessage());
    jsonResponse(false, 'Failed to log play', [], 500);
}

