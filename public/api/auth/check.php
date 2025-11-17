<?php
/**
 * Check Authentication Status API
 * GET /api/auth/check.php
 * 
 * Check if user is currently logged in
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Check if user is logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    
    jsonResponse(true, 'User is logged in', [
        'logged_in' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'profile_picture' => $user['profile_picture']
        ]
    ]);
} else {
    jsonResponse(true, 'User is not logged in', [
        'logged_in' => false
    ]);
}

