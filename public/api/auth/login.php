<?php
/**
 * User Login API
 * POST /api/auth/login.php
 * 
 * Authenticate user and create session
 */

require_once '../../../includes/config.php';
require_once '../../../includes/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', [], 405);
}

// Get JSON input
$input = getJsonInput();

// Validate required fields
$login = $input['login'] ?? ''; // Can be username or email
$password = $input['password'] ?? '';

if (empty($login) || empty($password)) {
    jsonResponse(false, 'Username/email and password are required', [], 400);
}

// Sanitize input
$login = sanitizeInput($login);

// Find user by username or email
$db = Database::getInstance();
$query = "SELECT id, username, email, password_hash, role, profile_picture 
          FROM users 
          WHERE username = ? OR email = ?";
$user = $db->selectOne($query, [$login, $login]);

// Check if user exists
if (!$user) {
    jsonResponse(false, 'Invalid credentials', [], 401);
}

// Verify password
if (!verifyPassword($password, $user['password_hash'])) {
    jsonResponse(false, 'Invalid credentials', [], 401);
}

// Login user (set session)
if (loginUser($user)) {
    // Log activity
    logActivity("User logged in: {$user['username']}");
    
    // Generate JWT
    $token = generateAuthToken($user);
    
    jsonResponse(true, 'Login successful', [
        'token' => $token,
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'role' => $user['role'],
        'profile_picture' => $user['profile_picture']
    ]);
} else {
    jsonResponse(false, 'Login failed', [], 500);
}

