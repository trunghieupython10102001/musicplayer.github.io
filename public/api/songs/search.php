<?php
/**
 * Search Songs API
 * GET /api/songs/search.php?q=query
 * 
 * Search songs by title, artist, or album
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Get search query
$searchQuery = isset($_GET['q']) ? sanitizeSearchQuery($_GET['q']) : '';

if (empty($searchQuery)) {
    jsonResponse(false, 'Search query is required', [], 400);
}

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : SONGS_PER_PAGE;
$offset = ($page - 1) * $limit;

$db = Database::getInstance();

// Search using FULLTEXT or LIKE
$searchPattern = '%' . $searchQuery . '%';

// Count total results
$countQuery = "SELECT COUNT(*) as total FROM songs 
               WHERE title LIKE ? OR artist LIKE ? OR album LIKE ?";
$totalResult = $db->selectOne($countQuery, [$searchPattern, $searchPattern, $searchPattern]);
$totalSongs = $totalResult['total'];

// Get search results
$query = "SELECT id, title, artist, album, duration, file_path, cover_image, 
                 genre, release_year, play_count 
          FROM songs 
          WHERE title LIKE ? OR artist LIKE ? OR album LIKE ?
          ORDER BY play_count DESC, title ASC 
          LIMIT ? OFFSET ?";

$songs = $db->select($query, [
    $searchPattern, 
    $searchPattern, 
    $searchPattern, 
    $limit, 
    $offset
]);

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

jsonResponse(true, 'Search completed', [
    'query' => $searchQuery,
    'songs' => $songs,
    'pagination' => $pagination
]);

