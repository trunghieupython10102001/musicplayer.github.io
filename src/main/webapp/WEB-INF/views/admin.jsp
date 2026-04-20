<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%@ page import="com.musicplayer.model.User" %>
<%
User currentUser = (User) request.getAttribute("currentUser");
%>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Music Player</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" sizes="32x32" type="image/png" href="/assets/favicon/spotify.png">
    <style>
        .admin-container { min-height: 100vh; background: #1b1b1b; padding: 80px 20px 40px; }
        .admin-content { max-width: 900px; margin: 0 auto; }
        .admin-header { color: #fff; margin-bottom: 40px; }
        .admin-header h1 { font-size: 32px; margin-bottom: 8px; color: #32e84a; }
        .admin-card { background: linear-gradient(145deg, #2a2a2a 0%, #252525 100%); border-radius: 16px; padding: 30px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4); margin-bottom: 24px; border: 1px solid rgba(50, 232, 74, 0.1); }
        .admin-card h2 { color: #32e84a; font-size: 24px; margin-bottom: 24px; font-weight: 700; display:flex; align-items:center; gap:10px; }
        .upload-form { display: flex; flex-direction: column; gap: 20px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-field { display: flex; flex-direction: column; gap: 8px; }
        .form-field label { color: #fff; font-weight: 600; font-size: 14px; }
        .form-field input, .form-field select { padding: 12px 16px; background: #333; border: 2px solid #444; border-radius: 8px; color: #fff; font-size: 14px; }
        .form-field input:focus, .form-field select:focus { outline: none; border-color: #32e84a; }
        .file-input-wrapper { position: relative; overflow: hidden; display: inline-block; width: 100%; }
        .file-input-label { display: flex; align-items: center; justify-content: center; gap: 12px; padding: 16px; background: #333; border: 2px dashed #555; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; color: #999; }
        .file-input-label:hover { border-color: #32e84a; background: #3a3a3a; color: #32e84a; }
        .file-input-label i { font-size: 24px; }
        input[type="file"] { position: absolute; left: -9999px; }
        .file-name { color: #fff; margin-top: 8px; font-size: 13px; }
        .btn-upload { background: linear-gradient(135deg, #32e84a 0%, #28c23d 100%); color: #000; border: none; padding: 16px 32px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s ease; margin-top: 10px; }
        .btn-upload:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(50, 232, 74, 0.4); }
        .btn-upload:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .upload-progress { margin-top: 20px; display: none; }
        .progress-bar { width: 100%; height: 8px; background: #333; border-radius: 4px; overflow: hidden; }
        .progress-fill { height: 100%; background: #32e84a; width: 0%; transition: width 0.3s ease; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; }
        .stat-box { background: linear-gradient(135deg, #333 0%, #2d2d2d 100%); padding: 24px 20px; border-radius: 12px; text-align: center; }
        .stat-number { font-size: 40px; font-weight: 800; color: #32e84a; margin-bottom: 8px; line-height: 1.2; }
        .stat-label { color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 20px; display: none; }
        .alert.show { display: block; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .top-nav { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 24px; background:#121212; border-bottom:1px solid #282828; position:sticky; top:0; z-index:20; }
        .top-nav h2 { color:#fff; margin:0; }
        .nav-right { display:flex; align-items:center; gap:12px; color:#fff; }
        .nav-btn { background:#2a2a2a; border:none; color:#fff; width:40px; height:40px; border-radius:50%; cursor:pointer; }
        .nav-btn:hover { background:#3a3a3a; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="top-nav">
        <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
        <div class="nav-right">
            <span><i class="fas fa-user"></i> <%= currentUser != null ? currentUser.username() : "admin" %></span>
            <button onclick="window.location.href='/index'" class="nav-btn" title="Player"><i class="fas fa-music"></i></button>
            <button onclick="window.location.href='/dashboard'" class="nav-btn" title="Dashboard"><i class="fas fa-chart-bar"></i></button>
            <button onclick="logout()" class="nav-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-content">
            <div class="admin-header">
                <h1>Admin Panel</h1>
                <p>Upload and manage your music library.</p>
            </div>

            <div id="alert" class="alert"></div>

            <div class="admin-card">
                <h2><i class="fas fa-chart-line"></i> Overview</h2>
                <div id="statsGrid" class="stats-grid">
                    <div class="stat-box"><div class="stat-number">-</div><div class="stat-label">Loading</div></div>
                </div>
            </div>

            <div class="admin-card">
                <h2><i class="fas fa-upload"></i> Upload Song</h2>
                <form id="uploadForm" class="upload-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="title">Title</label>
                            <input id="title" name="title" required>
                        </div>
                        <div class="form-field">
                            <label for="artist">Artist</label>
                            <input id="artist" name="artist" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="album">Album</label>
                            <input id="album" name="album">
                        </div>
                        <div class="form-field">
                            <label for="genre">Genre</label>
                            <input id="genre" name="genre">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="release_year">Release Year</label>
                            <input id="release_year" name="release_year" type="number" min="1900" max="2100">
                        </div>
                        <div class="form-field">
                            <label>Audio File</label>
                            <div class="file-input-wrapper">
                                <label class="file-input-label" for="audio_file"><i class="fas fa-file-audio"></i><span>Choose audio file</span></label>
                                <input id="audio_file" type="file" name="audio_file" accept="audio/*" required>
                            </div>
                            <div id="audioFileName" class="file-name"></div>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Cover Image</label>
                        <div class="file-input-wrapper">
                            <label class="file-input-label" for="cover_image"><i class="fas fa-image"></i><span>Choose cover image</span></label>
                            <input id="cover_image" type="file" name="cover_image" accept="image/*" required>
                        </div>
                        <div id="coverFileName" class="file-name"></div>
                    </div>
                    <button type="submit" class="btn-upload" id="uploadBtn">Upload Song</button>
                    <div id="uploadProgress" class="upload-progress">
                        <div class="progress-bar"><div id="progressFill" class="progress-fill"></div></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/assets/js/api.js?v=2"></script>
    <script>
        function showAlert(message, type = 'success') {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = 'alert alert-' + type + ' show';
            setTimeout(() => alert.classList.remove('show'), 5000);
        }

        async function loadStats() {
            const response = await fetch('/api/admin/stats', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            if (!data.success) {
                showAlert(data.message || 'Failed to load admin stats', 'error');
                return;
            }
            const stats = data.data || {};
            const totalSongs = stats.total_songs == null ? 0 : stats.total_songs;
            const totalUsers = stats.total_users == null ? 0 : stats.total_users;
            const totalPlays = stats.total_plays == null ? 0 : stats.total_plays;
            const totalFavorites = stats.total_favorites == null ? 0 : stats.total_favorites;
            const totalPlaylists = stats.total_playlists == null ? 0 : stats.total_playlists;
            const recentUsers = stats.recent_users == null ? 0 : stats.recent_users;

            document.getElementById('statsGrid').innerHTML = ''
                + '<div class="stat-box"><div class="stat-number">' + totalSongs + '</div><div class="stat-label">Songs</div></div>'
                + '<div class="stat-box"><div class="stat-number">' + totalUsers + '</div><div class="stat-label">Users</div></div>'
                + '<div class="stat-box"><div class="stat-number">' + totalPlays + '</div><div class="stat-label">Plays</div></div>'
                + '<div class="stat-box"><div class="stat-number">' + totalFavorites + '</div><div class="stat-label">Favorites</div></div>'
                + '<div class="stat-box"><div class="stat-number">' + totalPlaylists + '</div><div class="stat-label">Playlists</div></div>'
                + '<div class="stat-box"><div class="stat-number">' + recentUsers + '</div><div class="stat-label">Recent Users</div></div>';
        }

        function bindFileName(inputId, outputId) {
            document.getElementById(inputId).addEventListener('change', (event) => {
                const file = event.target.files && event.target.files[0];
                document.getElementById(outputId).textContent = file ? file.name : '';
            });
        }

        bindFileName('audio_file', 'audioFileName');
        bindFileName('cover_image', 'coverFileName');

        document.getElementById('uploadForm').addEventListener('submit', async (event) => {
            event.preventDefault();
            const uploadBtn = document.getElementById('uploadBtn');
            const progress = document.getElementById('uploadProgress');
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            progress.style.display = 'block';
            document.getElementById('progressFill').style.width = '60%';

            try {
                const formData = new FormData(event.target);
                const result = await API.uploadSong(formData);
                if (result.success) {
                    document.getElementById('progressFill').style.width = '100%';
                    showAlert(result.message || 'Song uploaded successfully', 'success');
                    event.target.reset();
                    document.getElementById('audioFileName').textContent = '';
                    document.getElementById('coverFileName').textContent = '';
                    await loadStats();
                } else {
                    showAlert(result.message || 'Upload failed', 'error');
                }
            } catch (error) {
                console.error('Upload failed', error);
                showAlert('Upload failed. Please try again.', 'error');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload Song';
                setTimeout(() => {
                    progress.style.display = 'none';
                    document.getElementById('progressFill').style.width = '0%';
                }, 800);
            }
        });

        async function logout() {
            await API.logout();
            window.location.href = '/login';
        }

        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>
