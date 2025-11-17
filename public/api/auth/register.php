<?php
/**
 * User Registration API
 * POST /api/auth/register.php
 * 
 * Register a new user account
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
$username = $input['username'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    jsonResponse(false, 'All fields are required', [], 400);
}

// Sanitize inputs
$username = sanitizeInput($username);
$email = sanitizeInput($email);

// Validate username length
if (strlen($username) < 3 || strlen($username) > 50) {
    jsonResponse(false, 'Username must be between 3 and 50 characters', [], 400);
}

// Validate email format
if (!validateEmail($email)) {
    jsonResponse(false, 'Invalid email format', [], 400);
}

// Validate password
$passwordValidation = validatePassword($password);
if (!$passwordValidation['valid']) {
    jsonResponse(false, $passwordValidation['message'], [], 400);
}

// Check if username already exists
if (usernameExists($username)) {
    jsonResponse(false, 'Username already taken', [], 409);
}

// Check if email already exists
if (emailExists($email)) {
    jsonResponse(false, 'Email already registered', [], 409);
}

// Hash password
$passwordHash = hashPassword($password);

// Insert new user into database
$db = Database::getInstance();
$query = "INSERT INTO users (username, email, password_hash, role, created_at) 
          VALUES (?, ?, ?, 'user', NOW())";

try {
    $result = $db->execute($query, [$username, $email, $passwordHash]);
    
    if ($result) {
        $userId = $db->lastInsertId();
        
        // Log activity
        logActivity("New user registered: {$username}");
        
        jsonResponse(true, 'Registration successful', [
            'user_id' => $userId,
            'username' => $username,
            'email' => $email
        ], 201);
    } else {
        jsonResponse(false, 'Registration failed', [], 500);
    }
} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(false, 'An error occurred during registration', [], 500);
}

