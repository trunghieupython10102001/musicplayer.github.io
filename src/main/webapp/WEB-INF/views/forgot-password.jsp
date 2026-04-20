<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Music Player</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" sizes="32x32" type="image/png" href="/assets/favicon/spotify.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-music"></i> Music Player</h1>
                <p>Reset your password</p>
            </div>

            <div id="alert" class="alert"></div>

            <form id="forgotPasswordForm" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                        autofocus
                    >
                    <span class="error-message"></span>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">Send Reset Link</button>
            </form>

            <div class="auth-links">
                Remember your password? <a href="/login">Login here</a>
            </div>

            <div class="auth-links" style="margin-top: 10px;">
                Don't have an account? <a href="/register">Register here</a>
            </div>
        </div>
    </div>

    <script>
        function showAlert(message, type = 'error') {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert alert-${type} show`;

            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        document.getElementById('forgotPasswordForm').addEventListener('submit', async (event) => {
            event.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const email = document.getElementById('email').value.trim();

            if (!email) {
                showAlert('Please enter your email', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>Sending...';

            try {
                const response = await fetch('/api/auth/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(data.message, 'success');
                    document.getElementById('forgotPasswordForm').reset();
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 3000);
                } else {
                    showAlert(data.message || 'Failed to send reset link', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Send Reset Link';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Send Reset Link';
            }
        });
    </script>
</body>
</html>
