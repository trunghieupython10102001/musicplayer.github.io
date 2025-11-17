<?php
/**
 * Utility Functions
 * 
 * General purpose helper functions used throughout the application
 */

require_once __DIR__ . '/config.php';

/**
 * Send JSON response and exit
 * 
 * @param bool $success Success status
 * @param string $message Message to return
 * @param array $data Additional data to return
 * @param int $httpCode HTTP status code
 */
function jsonResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Get JSON input from request body
 * 
 * @return array Decoded JSON data
 */
function getJsonInput() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?: [];
}

/**
 * Format duration from seconds to MM:SS
 * 
 * @param int $seconds Duration in seconds
 * @return string Formatted duration
 */
function formatDuration($seconds) {
    if (!$seconds) return '0:00';
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%d:%02d', $minutes, $seconds);
}

/**
 * Format file size to human readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Upload file to server
 * 
 * @param array $file $_FILES array element
 * @param string $destination Destination directory
 * @param array $allowedTypes Allowed MIME types
 * @return array ['success' => bool, 'filename' => string, 'message' => string]
 */
function uploadFile($file, $destination, $allowedTypes = []) {
    // Check if file was uploaded
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File is too large. Max size: ' . formatFileSize(MAX_FILE_SIZE)];
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Create destination directory if it doesn't exist
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
    
    return ['success' => true, 'filename' => $filename, 'message' => 'File uploaded successfully'];
}

/**
 * Delete file from server
 * 
 * @param string $filepath Full path to file
 * @return bool Success status
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get audio file duration using getID3 or similar
 * Note: This is a placeholder. Install getID3 library for real implementation
 * 
 * @param string $filepath Path to audio file
 * @return int Duration in seconds
 */
function getAudioDuration($filepath) {
    // Placeholder - returns 0
    // In production, use getID3 or similar library
    return 0;
}

/**
 * Validate and sanitize search query
 * 
 * @param string $query Search query
 * @return string Sanitized query
 */
function sanitizeSearchQuery($query) {
    $query = trim($query);
    $query = strip_tags($query);
    $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');
    return $query;
}

/**
 * Paginate results
 * 
 * @param int $totalItems Total number of items
 * @param int $itemsPerPage Items per page
 * @param int $currentPage Current page number
 * @return array Pagination data
 */
function paginate($totalItems, $itemsPerPage = 20, $currentPage = 1) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Log activity to file
 * 
 * @param string $message Message to log
 * @param string $level Log level (info, warning, error)
 */
function logActivity($message, $level = 'info') {
    $logFile = BASE_PATH . '/logs/app.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $userId = getCurrentUserId() ?? 'guest';
    $logMessage = "[{$timestamp}] [{$level}] [User: {$userId}] {$message}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Generate random string
 * 
 * @param int $length Length of string
 * @return string Random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Time ago format (e.g., "2 hours ago")
 * 
 * @param string $datetime Datetime string
 * @return string Formatted time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

/**
 * Check if request is AJAX
 * 
 * @return bool True if AJAX request
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

