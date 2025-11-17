<?php
/**
 * User Logout API
 * POST /api/auth/logout.php
 * 
 * Destroy user session and logout
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Check if user is logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Not logged in', [], 400);
}

$username = $_SESSION['username'] ?? 'Unknown';

// Logout user
logoutUser();

// Log activity
logActivity("User logged out: {$username}");

jsonResponse(true, 'Logout successful');

