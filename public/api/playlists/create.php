<?php
/**
 * Create Playlist API
 * POST /api/playlists/create.php
 * 
 * Create a new playlist for the current user
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
$name = isset($input['name']) ? sanitizeInput($input['name']) : '';
$description = isset($input['description']) ? sanitizeInput($input['description']) : '';
$isPublic = isset($input['is_public']) ? (int)(bool)$input['is_public'] : 0;

// Validate
if (empty($name)) {
    jsonResponse(false, 'Playlist name is required', [], 400);
}

if (strlen($name) > 255) {
    jsonResponse(false, 'Playlist name is too long', [], 400);
}

$userId = getCurrentUserId();
$db = Database::getInstance();

// Insert playlist
$query = "INSERT INTO playlists (user_id, name, description, is_public, created_at) 
          VALUES (?, ?, ?, ?, NOW())";

try {
    $result = $db->execute($query, [$userId, $name, $description, $isPublic]);
    
    if ($result) {
        $playlistId = $db->lastInsertId();
        
        // Log activity
        logActivity("Created playlist: {$name}");
        
        jsonResponse(true, 'Playlist created successfully', [
            'playlist_id' => $playlistId,
            'name' => $name
        ], 201);
    } else {
        jsonResponse(false, 'Failed to create playlist', [], 500);
    }
} catch (Exception $e) {
    error_log("Create playlist error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred', [], 500);
}

