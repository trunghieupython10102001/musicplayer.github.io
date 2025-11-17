<?php
/**
 * Login Page
 * 
 * User authentication page
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /index.php');
    exit();
}

// Get redirect parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Music Player</title>
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
                <p>Welcome back! Please login to your account.</p>
            </div>

            <!-- Alert message -->
            <div id="alert" class="alert"></div>

            <!-- Login form -->
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="login">Username or Email</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        placeholder="Enter username or email"
                        required
                        autofocus
                    >
                    <span class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter password"
                            required
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-message"></span>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    Login
                </button>
            </form>

            <div class="auth-links">
                Don't have an account? <a href="/register.php">Register here</a>
            </div>

            <div class="auth-links" style="margin-top: 10px;">
                <small style="color: #999;">
                    Demo: admin / admin123
                </small>
            </div>
        </div>
    </div>

    <script>
        // Store redirect URL
        const redirectUrl = '<?php echo htmlspecialchars($redirect); ?>';

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Show alert message
        function showAlert(message, type = 'error') {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert alert-${type} show`;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        // Handle form submission
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const login = document.getElementById('login').value.trim();
            const password = document.getElementById('password').value;
            
            // Validate inputs
            if (!login || !password) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>Logging in...';
            
            try {
                // Send login request
                const response = await fetch('/api/auth/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ login, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Login successful! Redirecting...', 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    showAlert(data.message || 'Login failed', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Login';
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Login';
            }
        });

        // Auto-fill demo credentials on demo link click
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('demo') === '1') {
                document.getElementById('login').value = 'admin';
                document.getElementById('password').value = 'admin123';
            }
        });
    </script>
</body>
</html>

