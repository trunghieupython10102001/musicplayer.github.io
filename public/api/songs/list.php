<?php
/**
 * List Songs API
 * GET /api/songs/list.php
 * 
 * Get all songs with optional pagination
 * Query params: ?page=1&limit=20&genre=Pop
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Get query parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : SONGS_PER_PAGE;
$genre = isset($_GET['genre']) ? sanitizeInput($_GET['genre']) : '';

$offset = ($page - 1) * $limit;

// Build query
$db = Database::getInstance();

// Count total songs
$countQuery = "SELECT COUNT(*) as total FROM songs";
$countParams = [];

if ($genre) {
    $countQuery .= " WHERE genre = ?";
    $countParams[] = $genre;
}

$totalResult = $db->selectOne($countQuery, $countParams);
$totalSongs = $totalResult['total'];

// Get songs
$query = "SELECT id, title, artist, album, duration, file_path, cover_image, 
                 genre, release_year, play_count 
          FROM songs";

$params = [];

if ($genre) {
    $query .= " WHERE genre = ?";
    $params[] = $genre;
}

$query .= " ORDER BY title ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$songs = $db->select($query, $params);

// Format songs data
foreach ($songs as &$song) {
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
}

// Pagination info
$pagination = paginate($totalSongs, $limit, $page);

jsonResponse(true, 'Songs retrieved successfully', [
    'songs' => $songs,
    'pagination' => $pagination
]);

