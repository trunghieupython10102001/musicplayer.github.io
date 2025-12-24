<?php
/**
 * Reset Password Endpoint
 * 
 * POST /api/auth/reset-password.php
 * Resets user password using a valid reset token
 */

require_once '/var/www/includes/config.php';
require_once '/var/www/includes/database.php';
require_once '/var/www/includes/auth.php';

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
    
    if (!$data || !isset($data['token']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token and password are required'
        ]);
        exit();
    }
    
    $token = sanitizeInput($data['token']);
    $password = $data['password'];
    
    $user = verifyPasswordResetToken($token);
    
    if (!$user) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ]);
        exit();
    }
    
    $validation = validatePassword($password);
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $validation['message']
        ]);
        exit();
    }
    
    if (resetUserPassword($user['id'], $password)) {
        logActivity("Password reset successful for user: {$user['username']}", 'info');
        
        echo json_encode([
            'success' => true,
            'message' => 'Password has been reset successfully. You can now login with your new password.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to reset password'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    logActivity("Password reset error: " . $e->getMessage(), 'error');
}
