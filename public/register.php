<?php
/**
 * Registration Page
 * 
 * New user registration
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Music Player</title>
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
                <p>Create your account to start listening.</p>
            </div>

            <!-- Alert message -->
            <div id="alert" class="alert"></div>

            <!-- Registration form -->
            <form id="registerForm" class="auth-form">
                <div class="form-group" id="usernameGroup">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Choose a username"
                        required
                        autofocus
                        minlength="3"
                        maxlength="50"
                    >
                    <span class="error-message">Username must be 3-50 characters</span>
                </div>

                <div class="form-group" id="emailGroup">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required
                    >
                    <span class="error-message">Please enter a valid email</span>
                </div>

                <div class="form-group" id="passwordGroup">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Choose a password"
                            required
                            minlength="6"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-message">Password must be at least 6 characters</span>
                </div>

                <div class="form-group" id="confirmPasswordGroup">
                    <label for="confirmPassword">Confirm Password</label>
                    <div class="password-field">
                        <input 
                            type="password" 
                            id="confirmPassword" 
                            name="confirmPassword" 
                            placeholder="Confirm your password"
                            required
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-message">Passwords do not match</span>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    Create Account
                </button>
            </form>

            <div class="auth-links">
                Already have an account? <a href="/login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.parentElement.querySelector('.password-toggle');
            const toggleIcon = toggleBtn.querySelector('i');
            
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

        // Validate field
        function validateField(fieldId, isValid) {
            const group = document.getElementById(fieldId + 'Group');
            if (isValid) {
                group.classList.remove('error');
                group.classList.add('success');
            } else {
                group.classList.remove('success');
                group.classList.add('error');
            }
        }

        // Real-time validation
        document.getElementById('username').addEventListener('input', (e) => {
            const value = e.target.value;
            validateField('username', value.length >= 3 && value.length <= 50);
        });

        document.getElementById('email').addEventListener('input', (e) => {
            const value = e.target.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            validateField('email', emailRegex.test(value));
        });

        document.getElementById('password').addEventListener('input', (e) => {
            const value = e.target.value;
            validateField('password', value.length >= 6);
            
            // Also check confirm password
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (confirmPassword) {
                validateField('confirmPassword', value === confirmPassword);
            }
        });

        document.getElementById('confirmPassword').addEventListener('input', (e) => {
            const password = document.getElementById('password').value;
            const confirmPassword = e.target.value;
            validateField('confirmPassword', password === confirmPassword);
        });

        // Handle form submission
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validate inputs
            if (!username || !email || !password || !confirmPassword) {
                showAlert('Please fill in all fields', 'error');
                return;
            }
            
            if (username.length < 3 || username.length > 50) {
                showAlert('Username must be 3-50 characters', 'error');
                return;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email', 'error');
                return;
            }
            
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match', 'error');
                return;
            }
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>Creating account...';
            
            try {
                // Send registration request
                const response = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('Account created successfully! Redirecting to login...', 'success');
                    
                    // Redirect to login page
                    setTimeout(() => {
                        window.location.href = '/login.php';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Registration failed', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Create Account';
                }
            } catch (error) {
                console.error('Registration error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Create Account';
            }
        });
    </script>
</body>
</html>

