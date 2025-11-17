<?php
/**
 * Upload Song API (Admin Only)
 * POST /api/admin/upload.php
 * 
 * Upload a new song with metadata (multipart/form-data)
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Set proper content type for file upload
header('Content-Type: application/json; charset=utf-8');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// User must be logged in and be admin
if (!isLoggedIn()) {
    jsonResponse(false, 'Authentication required', [], 401);
}

requireAdmin();

// Get form data
$title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
$artist = isset($_POST['artist']) ? sanitizeInput($_POST['artist']) : '';
$album = isset($_POST['album']) ? sanitizeInput($_POST['album']) : '';
$genre = isset($_POST['genre']) ? sanitizeInput($_POST['genre']) : '';
$releaseYear = isset($_POST['release_year']) ? (int)$_POST['release_year'] : null;

// Validate required fields
if (empty($title) || empty($artist)) {
    jsonResponse(false, 'Title and artist are required', [], 400);
}

// Check if files are uploaded
if (!isset($_FILES['audio_file']) || !isset($_FILES['cover_image'])) {
    jsonResponse(false, 'Audio file and cover image are required', [], 400);
}

$db = Database::getInstance();

try {
    // Upload audio file
    $audioUpload = uploadFile($_FILES['audio_file'], AUDIO_PATH, ALLOWED_AUDIO_TYPES);
    
    if (!$audioUpload['success']) {
        jsonResponse(false, 'Audio upload failed: ' . $audioUpload['message'], [], 400);
    }
    
    // Upload cover image
    $imageUpload = uploadFile($_FILES['cover_image'], COVER_PATH, ALLOWED_IMAGE_TYPES);
    
    if (!$imageUpload['success']) {
        // Delete uploaded audio file if image upload fails
        deleteFile(AUDIO_PATH . '/' . $audioUpload['filename']);
        jsonResponse(false, 'Image upload failed: ' . $imageUpload['message'], [], 400);
    }
    
    // Get audio duration (placeholder - implement with getID3 in production)
    $duration = getAudioDuration(AUDIO_PATH . '/' . $audioUpload['filename']);
    
    // Insert song into database
    $query = "INSERT INTO songs (title, artist, album, duration, file_path, cover_image, 
                                 genre, release_year, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $result = $db->execute($query, [
        $title,
        $artist,
        $album,
        $duration,
        $audioUpload['filename'],
        $imageUpload['filename'],
        $genre,
        $releaseYear
    ]);
    
    if ($result) {
        $songId = $db->lastInsertId();
        
        // Log activity
        logActivity("Uploaded new song: {$title} by {$artist}");
        
        jsonResponse(true, 'Song uploaded successfully', [
            'song_id' => $songId,
            'title' => $title,
            'artist' => $artist,
            'audio_file' => $audioUpload['filename'],
            'cover_image' => $imageUpload['filename']
        ], 201);
    } else {
        // Delete uploaded files if database insert fails
        deleteFile(AUDIO_PATH . '/' . $audioUpload['filename']);
        deleteFile(COVER_PATH . '/' . $imageUpload['filename']);
        jsonResponse(false, 'Failed to save song to database', [], 500);
    }
    
} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred during upload', [], 500);
}

