<?php
/**
 * Authentication Helper Functions
 * 
 * Provides user authentication and session management (supporting both Session and JWT)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/jwt.php';

/**
 * Generate JWT for user
 * 
 * @param array $user User data
 * @return string JWT token
 */
function generateAuthToken($user) {
    $payload = [
        'sub' => $user['id'],
        'name' => $user['username'],
        'role' => $user['role'],
        'iat' => time(),
        'exp' => time() + JWT_EXPIRY
    ];
    
    return JWT::encode($payload, JWT_SECRET);
}

/**
 * Get JWT from Authorization header
 * 
 * @return string|null Token or null
 */
function getAuthTokenFromHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    
    return null;
}

/**
 * Validate JWT token
 * 
 * @param string $token
 * @return array|null Payload or null
 */
function validateAuthToken($token) {
    try {
        $payload = JWT::decode($token, JWT_SECRET);
        
        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return null;
        }
        
        return $payload;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Check if user is logged in
 * Prioritizes JWT, falls back to Session
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    // Check JWT first
    $token = getAuthTokenFromHeader();
    if ($token) {
        $payload = validateAuthToken($token);
        if ($payload) {
            return true;
        }
    }
    
    // Fallback to Session
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check JWT
    $token = getAuthTokenFromHeader();
    if ($token) {
        $payload = validateAuthToken($token);
        if ($payload && isset($payload['role']) && $payload['role'] === 'admin') {
            return true;
        }
    }
    
    // Check Session
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    // Check JWT
    $token = getAuthTokenFromHeader();
    if ($token) {
        $payload = validateAuthToken($token);
        if ($payload && isset($payload['sub'])) {
            return $payload['sub'];
        }
    }
    
    // Check Session
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    $userId = getCurrentUserId();
    
    if (!$userId) {
        return null;
    }
    
    $db = Database::getInstance();
    $query = "SELECT id, username, email, role, profile_picture, created_at 
              FROM users WHERE id = ?";
    return $db->selectOne($query, [$userId]);
}

/**
 * Login user by setting session variables
 * 
 * @param array $user User data from database
 * @return bool Success status
 */
function loginUser($user) {
    if (!$user || !isset($user['id'])) {
        return false;
    }
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['login_time'] = time();
    
    // Update last login time in database
    $db = Database::getInstance();
    $db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
    
    return true;
}

/**
 * Logout user by destroying session
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Require login - redirect to login page if not logged in
 * 
 * @param string $redirectTo URL to redirect after login
 */
function requireLogin($redirectTo = null) {
    if (!isLoggedIn()) {
        $redirect = $redirectTo ? '?redirect=' . urlencode($redirectTo) : '';
        header('Location: /login.php' . $redirect);
        exit();
    }
}

/**
 * Require admin - return error if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied. Admin privileges required.'
        ]);
        exit();
    }
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate email format
 * 
 * @param string $email Email address
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return [
            'valid' => false,
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Sanitize input to prevent XSS attacks
 * 
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if username already exists
 * 
 * @param string $username Username to check
 * @return bool True if exists, false otherwise
 */
function usernameExists($username) {
    $db = Database::getInstance();
    $query = "SELECT COUNT(*) as count FROM users WHERE username = ?";
    $result = $db->selectOne($query, [$username]);
    return $result && $result['count'] > 0;
}

/**
 * Check if email already exists
 * 
 * @param string $email Email to check
 * @return bool True if exists, false otherwise
 */
function emailExists($email) {
    $db = Database::getInstance();
    $query = "SELECT COUNT(*) as count FROM users WHERE email = ?";
    $result = $db->selectOne($query, [$email]);
    return $result && $result['count'] > 0;
}

/**
 * Generate CSRF token for form protection
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
