<?php
/**
 * Reset Password Page
 * 
 * User can reset their password using a valid reset token
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

if (isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

$token = isset($_GET['token']) ? sanitizeInput($_GET['token']) : '';
$tokenValid = false;
$user = null;

if ($token) {
    $user = verifyPasswordResetToken($token);
    $tokenValid = $user !== null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Music Player</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" sizes="32x32" type="image/png" href="https://open.scdn.co/cdn/images/favicon32.a19b4f5b.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>🎵 Music Player</h1>
                <p>Create a new password</p>
            </div>

            <div id="alert" class="alert"></div>

            <?php if ($tokenValid): ?>
                <form id="resetPasswordForm" class="auth-form">
                    <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-field">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Enter new password"
                                required
                                autofocus
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <div class="password-field">
                            <input 
                                type="password" 
                                id="confirmPassword" 
                                name="confirmPassword" 
                                placeholder="Confirm new password"
                                required
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="error-message"></span>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-error show" style="margin-bottom: 20px;">
                    <strong>Invalid or Expired Link</strong><br>
                    This password reset link is invalid or has expired. Please request a new one.
                </div>
                <a href="/forgot-password.php" class="btn-primary" style="display: block; text-align: center;">
                    Request New Reset Link
                </a>
            <?php endif; ?>

            <div class="auth-links">
                Remember your password? <a href="/login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleBtn = event.target.closest('.password-toggle');
            const icon = toggleBtn.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function showAlert(message, type = 'error') {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert alert-${type} show`;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        const form = document.getElementById('resetPasswordForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitBtn');
                const token = document.getElementById('token').value;
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (!password || !confirmPassword) {
                    showAlert('Please fill in all fields', 'error');
                    return;
                }
                
                if (password !== confirmPassword) {
                    showAlert('Passwords do not match', 'error');
                    return;
                }
                
                if (password.length < 6) {
                    showAlert('Password must be at least 6 characters long', 'error');
                    return;
                }
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span>Resetting...';
                
                try {
                    const response = await fetch('/api/auth/reset-password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ token, password })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showAlert(data.message, 'success');
                        
                        setTimeout(() => {
                            window.location.href = '/login.php';
                        }, 2000);
                    } else {
                        showAlert(data.message || 'Failed to reset password', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Reset Password';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('An error occurred. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Reset Password';
                }
            });
        }
    </script>
</body>
</html>
