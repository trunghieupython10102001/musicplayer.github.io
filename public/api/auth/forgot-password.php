<?php
/**
 * Forgot Password Request Endpoint
 * 
 * POST /api/auth/forgot-password.php
 * Generates a password reset token for the user
 */

require_once '/var/www/includes/config.php';
require_once '/var/www/includes/database.php';
require_once '/var/www/includes/auth.php';
require_once '/var/www/includes/email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Email is required'
        ]);
        exit();
    }
    
    $email = sanitizeInput($data['email']);
    
    if (!validateEmail($email)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit();
    }
    
    $db = Database::getInstance();
    $user = $db->selectOne("SELECT id, username, email FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with this email, a reset link will be sent'
        ]);
        exit();
    }
    
    $token = generatePasswordResetToken($user['id']);
    
    if (!$token) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate reset token'
        ]);
        exit();
    }
    
    $emailSent = sendPasswordResetEmail($user['email'], $user['username'], $token);
    
    logActivity("Password reset requested for user: {$user['username']}", 'info');
    
    echo json_encode([
        'success' => true,
        'message' => 'If an account exists with this email, a reset link has been sent',
        'data' => [
            'email_sent' => $emailSent
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    logActivity("Password reset error: " . $e->getMessage(), 'error');
}
