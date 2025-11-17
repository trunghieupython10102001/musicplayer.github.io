<?php
/**
 * Get Single Song API
 * GET /api/songs/get.php?id=1
 * 
 * Get detailed information about a single song
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Get song ID
$songId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($songId <= 0) {
    jsonResponse(false, 'Invalid song ID', [], 400);
}

$db = Database::getInstance();

// Get song details
$query = "SELECT id, title, artist, album, duration, file_path, cover_image, 
                 genre, release_year, play_count, created_at, updated_at
          FROM songs 
          WHERE id = ?";

$song = $db->selectOne($query, [$songId]);

if (!$song) {
    jsonResponse(false, 'Song not found', [], 404);
}

// Format song data
$song['duration_formatted'] = formatDuration($song['duration']);

// Check if it's an uploaded file (has underscore and timestamp) or original file
$isUploaded = strpos($song['cover_image'], '_') !== false;

if ($isUploaded) {
    // New uploaded files are in /uploads/
    $song['cover_url'] = UPLOAD_URL . '/covers/' . $song['cover_image'];
    $song['audio_url'] = UPLOAD_URL . '/songs/' . $song['file_path'];
} else {
    // Original files are in /assets/
    $song['cover_url'] = ASSETS_URL . '/img/' . $song['cover_image'];
    $song['audio_url'] = ASSETS_URL . '/audio/' . $song['file_path'];
}

jsonResponse(true, 'Song retrieved successfully', [
    'song' => $song
]);

