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
// Use FULLTEXT search if possible (better performance and relevance)
// The table has: FULLTEXT idx_search (title, artist, album)

// Decode HTML entities for DB search
$term = htmlspecialchars_decode($searchQuery, ENT_QUOTES);
$term = trim($term);

// Determine search mode
// FULLTEXT works best for words >= 3 chars (default InnoDB setting)
// For very short queries, we fall back to LIKE
$useFullText = mb_strlen($term) >= 3;

$params = [];
$countParams = [];
$whereClause = "";
$orderByClause = "";
$selectFields = "id, title, artist, album, duration, file_path, cover_image, genre, release_year, play_count";

if ($useFullText) {
    // Construct Boolean Mode Query
    // Split into words and append * for prefix matching
    $words = explode(' ', $term);
    $booleanQuery = '';
    foreach ($words as $word) {
        $word = trim($word);
        if (mb_strlen($word) > 0) {
            // Clean word of boolean operators
            $word = preg_replace('/[+\-><()~*\"@]+/', '', $word);
            if (mb_strlen($word) > 0) {
                $booleanQuery .= '+' . $word . '* ';
            }
        }
    }
    $booleanQuery = trim($booleanQuery);
    
    if (!empty($booleanQuery)) {
        // Use MATCH ... AGAINST
        $whereClause = "MATCH(title, artist, album) AGAINST (? IN BOOLEAN MODE)";
        // Order by relevance (score) first, then popularity
        $selectFields .= ", MATCH(title, artist, album) AGAINST (? IN BOOLEAN MODE) as relevance";
        $orderByClause = "relevance DESC, play_count DESC";
        
        // Params for Select: where_param, select_param
        $params = [$booleanQuery, $booleanQuery];
        // Params for Count: where_param
        $countParams = [$booleanQuery];
    } else {
        // Fallback if boolean query construction failed
        $useFullText = false;
    }
}

if (!$useFullText) {
    // Fallback to LIKE for short queries
    $searchPattern = '%' . $term . '%';
    $whereClause = "(title LIKE ? OR artist LIKE ? OR album LIKE ?)";
    $orderByClause = "play_count DESC, title ASC";
    $params = [$searchPattern, $searchPattern, $searchPattern];
    $countParams = $params;
}

// Count total results
$countQuery = "SELECT COUNT(*) as total FROM songs WHERE " . $whereClause;
$totalResult = $db->selectOne($countQuery, $countParams);
$totalSongs = $totalResult['total'];

// Get search results
$query = "SELECT $selectFields 
          FROM songs 
          WHERE $whereClause
          ORDER BY $orderByClause 
          LIMIT ? OFFSET ?";

// Add pagination params
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

jsonResponse(true, 'Search completed', [
    'query' => $searchQuery,
    'songs' => $songs,
    'pagination' => $pagination,
    'search_mode' => $useFullText ? 'fulltext' : 'basic'
]);

