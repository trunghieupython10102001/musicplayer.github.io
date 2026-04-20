<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%
String token = (String) request.getAttribute("token");
if (token == null) {
    token = "";
}
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Music Player</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1>Music Player</h1>
                <p>Create a new password</p>
            </div>
            <div id="alert" class="alert"></div>
            <% if (!token.isBlank()) { %>
            <form id="resetPasswordForm" class="auth-form">
                <input type="hidden" id="token" value="<%= token.replace("\"", "&quot;") %>">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" required>
                </div>
                <button type="submit" class="btn-primary" id="submitBtn">Reset Password</button>
            </form>
            <% } else { %>
            <div class="alert alert-error show">Invalid or expired link. Please request a new one.</div>
            <div class="auth-links"><a href="/forgot-password">Request New Reset Link</a></div>
            <% } %>
            <div class="auth-links">Remember your password? <a href="/login">Login here</a></div>
        </div>
    </div>
    <script>
        function showAlert(message, type = 'error') {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = `alert alert-${type} show`;
        }
        const form = document.getElementById('resetPasswordForm');
        if (form) {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                if (password !== confirmPassword) {
                    showAlert('Passwords do not match');
                    return;
                }
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Resetting...';
                const response = await fetch('/api/auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: document.getElementById('token').value, password })
                });
                const data = await response.json();
                showAlert(data.message || 'Reset complete', data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => window.location.href = '/login', 1500);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Reset Password';
                }
            });
        }
    </script>
</body>
</html>
