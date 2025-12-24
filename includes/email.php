<?php
/**
 * Email Service
 * 
 * Handles sending emails for password resets and notifications
 */

require_once __DIR__ . '/config.php';

class EmailService {
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        $this->fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'noreply@musicplayer.local';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'Music Player';
    }
    
    /**
     * Send password reset email
     * 
     * @param string $toEmail Recipient email
     * @param string $userName User's name
     * @param string $resetLink Full reset link URL
     * @return bool Success status
     */
    public function sendPasswordResetEmail($toEmail, $userName, $resetLink) {
        $subject = "Reset Your Music Player Password";
        
        $htmlBody = $this->getPasswordResetTemplate($userName, $resetLink);
        $textBody = $this->getPasswordResetTextTemplate($userName, $resetLink);
        
        return $this->send($toEmail, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send email
     * 
     * @param string $toEmail Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @param string $textBody Plain text email body
     * @return bool Success status
     */
    private function send($toEmail, $subject, $htmlBody, $textBody) {
        $headers = $this->getHeaders();
        
        $body = "--{$this->getBoundary()}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $textBody . "\r\n\r\n";
        
        $body .= "--{$this->getBoundary()}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        
        $body .= "--{$this->getBoundary()}--\r\n";
        
        return mail($toEmail, $subject, $body, $headers);
    }
    
    /**
     * Get email headers
     * 
     * @return string Headers string
     */
    private function getHeaders() {
        $boundary = $this->getBoundary();
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "X-Mailer: Music Player\r\n";
        return $headers;
    }
    
    /**
     * Get boundary string for multipart email
     * 
     * @return string Boundary string
     */
    private function getBoundary() {
        return 'boundary_' . md5(time());
    }
    
    /**
     * Get password reset HTML email template
     * 
     * @param string $userName User's name
     * @param string $resetLink Reset link
     * @return string HTML email body
     */
    private function getPasswordResetTemplate($userName, $resetLink) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1DB954; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .footer { background-color: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
        .button { display: inline-block; background-color: #1DB954; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 3px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎵 Music Player</h1>
        </div>
        
        <div class="content">
            <p>Hi {$userName},</p>
            
            <p>We received a request to reset your password. Click the button below to create a new password:</p>
            
            <center>
                <a href="{$resetLink}" class="button">Reset Password</a>
            </center>
            
            <p>Or copy and paste this link in your browser:</p>
            <p style="word-break: break-all; background-color: #f0f0f0; padding: 10px; border-radius: 3px;">
                {$resetLink}
            </p>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong> This link will expire in 1 hour. If you didn't request a password reset, please ignore this email or contact support.
            </div>
            
            <p>Best regards,<br>The Music Player Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; 2025 Music Player. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get password reset plain text email template
     * 
     * @param string $userName User's name
     * @param string $resetLink Reset link
     * @return string Plain text email body
     */
    private function getPasswordResetTextTemplate($userName, $resetLink) {
        return <<<TEXT
Hi {$userName},

We received a request to reset your password. Click the link below to create a new password:

{$resetLink}

This link will expire in 1 hour. If you didn't request a password reset, please ignore this email or contact support.

Best regards,
The Music Player Team

---
This is an automated email. Please do not reply to this message.
© 2025 Music Player. All rights reserved.
TEXT;
    }
}

/**
 * Send password reset email
 * 
 * @param string $email User email
 * @param string $userName User name
 * @param string $resetToken Reset token
 * @return bool Success status
 */
function sendPasswordResetEmail($email, $userName, $resetToken) {
    $resetLink = "http://localhost:8080/reset-password.php?token=" . urlencode($resetToken);
    
    $emailService = new EmailService();
    return $emailService->sendPasswordResetEmail($email, $userName, $resetLink);
}
