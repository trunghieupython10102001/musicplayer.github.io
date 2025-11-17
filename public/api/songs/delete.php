<?php
/**
 * Delete Song API (Admin Only)
 * DELETE /api/songs/delete.php
 * 
 * Delete a song from the library
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in and be admin
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

requireAdmin();

// Get song ID from query or JSON
$songId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($songId <= 0) {
    $input = getJsonInput();
    $songId = isset($input['id']) ? (int)$input['id'] : 0;
}

if ($songId <= 0) {
    jsonResponse(false, 'Invalid song ID', [], 400);
}

$db = Database::getInstance();

// Get song details for cleanup
$song = $db->selectOne(
    "SELECT id, title, file_path, cover_image FROM songs WHERE id = ?",
    [$songId]
);

if (!$song) {
    jsonResponse(false, 'Song not found', [], 404);
}

try {
    // Delete song from database (cascade will delete related records)
    $result = $db->execute("DELETE FROM songs WHERE id = ?", [$songId]);
    
    if ($result) {
        // Try to delete uploaded files if they exist
        $isUploaded = strpos($song['cover_image'], '_') !== false;
        
        if ($isUploaded) {
            $audioPath = AUDIO_PATH . '/' . $song['file_path'];
            $coverPath = COVER_PATH . '/' . $song['cover_image'];
            
            if (file_exists($audioPath)) {
                deleteFile($audioPath);
            }
            
            if (file_exists($coverPath)) {
                deleteFile($coverPath);
            }
        }
        
        logActivity("Deleted song: {$song['title']}");
        
        jsonResponse(true, 'Song deleted successfully', [
            'song_id' => $songId
        ]);
    } else {
        jsonResponse(false, 'Failed to delete song', [], 500);
    }
} catch (Exception $e) {
    error_log("Delete song error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

