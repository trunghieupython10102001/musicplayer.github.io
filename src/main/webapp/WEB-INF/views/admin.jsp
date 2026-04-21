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
        .insight-grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:20px; }
        .mini-list { display:flex; flex-direction:column; gap:10px; }
        .mini-item { display:flex; justify-content:space-between; gap:12px; padding:12px 14px; background:#333; border-radius:10px; color:#fff; }
        .mini-item strong { display:block; font-size:14px; }
        .mini-item span { color:#9aa0a6; font-size:12px; }
        .activity-list { display:flex; flex-direction:column; gap:12px; }
        .activity-item { display:flex; align-items:flex-start; gap:12px; padding:14px; background:#333; border-radius:12px; color:#fff; }
        .activity-icon { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#202020; color:#32e84a; flex-shrink:0; }
        .activity-content strong { display:block; margin-bottom:4px; }
        .activity-content span { color:#9aa0a6; font-size:12px; display:block; }
        .song-table-wrap { overflow:auto; border-radius:12px; border:1px solid #333; }
        .song-table { width:100%; border-collapse:collapse; min-width:760px; }
        .song-table th, .song-table td { padding:14px 12px; text-align:left; border-bottom:1px solid #333; color:#fff; }
        .song-table th { background:#202020; color:#b3b3b3; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
        .song-table tr:hover td { background:#2f2f2f; }
        .song-cell-title { display:flex; align-items:center; gap:12px; }
        .song-cell-cover { width:44px; height:44px; border-radius:8px; object-fit:cover; background:#1a1a1a; }
        .song-meta { display:flex; flex-direction:column; gap:3px; }
        .song-meta strong { font-size:14px; }
        .song-meta span { color:#9aa0a6; font-size:12px; }
        .song-actions { display:flex; gap:8px; }
        .btn-icon { width:34px; height:34px; border:none; border-radius:9px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:13px; }
        .btn-edit { background:#f1c40f; color:#111; }
        .btn-delete-song { background:#ff4757; color:#fff; }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:16px; }
        .toolbar input { width:100%; max-width:320px; padding:12px 14px; background:#333; border:1px solid #444; border-radius:8px; color:#fff; }
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.72); z-index:1000; align-items:center; justify-content:center; padding:20px; }
        .modal.active { display:flex; }
        .modal-box { width:100%; max-width:580px; background:#222; border:1px solid #333; border-radius:16px; padding:24px; }
        .modal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        .modal-header h3 { color:#fff; margin:0; }
        .btn-close { width:36px; height:36px; border:none; border-radius:50%; background:#333; color:#fff; cursor:pointer; }
        .btn-close:hover { background:#444; }
        .alert { padding: 16px; border-radius: 8px; margin-bottom: 20px; display: none; }
        .alert.show { display: block; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .top-nav { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 24px; background:#121212; border-bottom:1px solid #282828; position:sticky; top:0; z-index:20; }
        .top-nav h2 { color:#fff; margin:0; }
        .nav-right { display:flex; align-items:center; gap:12px; color:#fff; }
        .nav-btn { background:#2a2a2a; border:none; color:#fff; width:40px; height:40px; border-radius:50%; cursor:pointer; }
        .nav-btn:hover { background:#3a3a3a; }
        @media (max-width: 768px) { .form-row, .insight-grid { grid-template-columns: 1fr; } }
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
                <div class="insight-grid">
                    <div>
                        <h3 style="color:#fff;margin:20px 0 12px;">Top nghe nhieu</h3>
                        <div id="mostPlayedList" class="mini-list"></div>
                    </div>
                    <div>
                        <h3 style="color:#fff;margin:20px 0 12px;">Top yeu thich</h3>
                        <div id="mostLikedList" class="mini-list"></div>
                    </div>
                </div>
                <div style="margin-top:24px;">
                    <h3 style="color:#fff;margin:0 0 12px;">Recent activity</h3>
                    <div id="recentActivityList" class="activity-list"></div>
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

            <div class="admin-card">
                <h2><i class="fas fa-music"></i> Quan ly bai hat</h2>
                <div class="toolbar">
                    <input id="songSearchInput" placeholder="Tim bai hat, ca si, album...">
                </div>
                <div class="song-table-wrap">
                    <table class="song-table">
                        <thead>
                            <tr>
                                <th>Bai hat</th>
                                <th>Album</th>
                                <th>The loai</th>
                                <th>Nam</th>
                                <th>Luot nghe</th>
                                <th>Thao tac</th>
                            </tr>
                        </thead>
                        <tbody id="songTableBody">
                            <tr><td colspan="6" style="color:#9aa0a6;">Dang tai danh sach bai hat...</td></tr>
                        </tbody>
                    </table>
                </div>

                <div id="editSongModal" class="modal">
                    <div class="modal-box">
                        <div class="modal-header">
                            <h3>Chinh sua bai hat</h3>
                            <button class="btn-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
                        </div>
                    <div class="form-row">
                        <div class="form-field"><label for="editTitle">Title</label><input id="editTitle"></div>
                        <div class="form-field"><label for="editArtist">Artist</label><input id="editArtist"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-field"><label for="editAlbum">Album</label><input id="editAlbum"></div>
                        <div class="form-field"><label for="editGenre">Genre</label><input id="editGenre"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-field"><label for="editYear">Release Year</label><input id="editYear" type="number" min="1900" max="2100"></div>
                    </div>
                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:16px;">
                        <button class="btn-upload" style="background:#555;color:#fff;box-shadow:none;" onclick="closeEditModal()">Huy</button>
                        <button class="btn-upload" style="padding:12px 22px;" onclick="saveSongEdit()">Luu thay doi</button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/api.js?v=3"></script>
    <script>
        let allSongs = [];
        let filteredSongs = [];
        let editingSongId = null;

        function activityIcon(type) {
            switch (type) {
                case 'song_upload':
                    return 'fa-upload';
                case 'user_register':
                    return 'fa-user-plus';
                case 'song_play':
                    return 'fa-play';
                case 'favorite_add':
                    return 'fa-heart';
                default:
                    return 'fa-bolt';
            }
        }

        function showAlert(message, type = 'success') {
            const alert = document.getElementById('alert');
            alert.textContent = message;
            alert.className = 'alert alert-' + type + ' show';
            setTimeout(() => alert.classList.remove('show'), 5000);
        }

        async function loadStats() {
            const [statsResponse, mostPlayedResponse, mostLikedResponse] = await Promise.all([
                API.getAdminStats(),
                API.getMostPlayed(5),
                API.getMostLiked(5)
            ]);

            if (!statsResponse.success) {
                showAlert(statsResponse.message || 'Failed to load admin stats', 'error');
                return;
            }
            const stats = statsResponse.data || {};
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

            renderMiniList('mostPlayedList', mostPlayedResponse.success ? mostPlayedResponse.data : [], 'play_count', 'luot nghe');
            renderMiniList('mostLikedList', mostLikedResponse.success ? mostLikedResponse.data : [], 'likes_count', 'luot thich');

            const activityResponse = await API.getRecentActivity(10);
            renderRecentActivity(activityResponse.success ? activityResponse.data.items || [] : []);
        }

        function renderRecentActivity(items) {
            const target = document.getElementById('recentActivityList');
            if (!items || items.length === 0) {
                target.innerHTML = '<div class="activity-item"><div class="activity-content"><strong>Chua co hoat dong</strong><span>Du lieu se hien thi khi nguoi dung tuong tac voi he thong.</span></div></div>';
                return;
            }

            target.innerHTML = items.map((item) => {
                return '<div class="activity-item">'
                    + '<div class="activity-icon"><i class="fas ' + activityIcon(item.type) + '"></i></div>'
                    + '<div class="activity-content">'
                    + '<strong>' + item.title + '</strong>'
                    + '<span>' + item.subtitle + '</span>'
                    + '<span>' + item.time_ago + '</span>'
                    + '</div>'
                    + '</div>';
            }).join('');
        }

        function renderMiniList(targetId, items, countKey, label) {
            const target = document.getElementById(targetId);
            if (!items || items.length === 0) {
                target.innerHTML = '<div class="mini-item"><span>Chua co du lieu</span></div>';
                return;
            }

            target.innerHTML = items.map((item) => {
                const count = item[countKey] == null ? 0 : item[countKey];
                return '<div class="mini-item">'
                    + '<div><strong>' + item.title + '</strong><span>' + item.artist + '</span></div>'
                    + '<div style="text-align:right;"><strong>' + count + '</strong><span>' + label + '</span></div>'
                    + '</div>';
            }).join('');
        }

        function bindFileName(inputId, outputId) {
            document.getElementById(inputId).addEventListener('change', (event) => {
                const file = event.target.files && event.target.files[0];
                document.getElementById(outputId).textContent = file ? file.name : '';
            });
        }

        bindFileName('audio_file', 'audioFileName');
        bindFileName('cover_image', 'coverFileName');

        async function loadSongs() {
            const response = await API.getSongs(1, 500);
            if (!response.success) {
                showAlert(response.message || 'Khong the tai danh sach bai hat', 'error');
                return;
            }
            allSongs = response.data.songs || [];
            filteredSongs = [...allSongs];
            renderSongTable();
        }

        function renderSongTable() {
            const tbody = document.getElementById('songTableBody');
            if (filteredSongs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="color:#9aa0a6;">Khong tim thay bai hat nao.</td></tr>';
                return;
            }

            tbody.innerHTML = filteredSongs.map((song) => {
                return '<tr>'
                    + '<td><div class="song-cell-title">'
                    + '<img class="song-cell-cover" src="' + song.cover_url + '" alt="cover">'
                    + '<div class="song-meta"><strong>' + song.title + '</strong><span>' + song.artist + '</span></div>'
                    + '</div></td>'
                    + '<td>' + (song.album || '-') + '</td>'
                    + '<td>' + (song.genre || '-') + '</td>'
                    + '<td>' + (song.release_year || '-') + '</td>'
                    + '<td>' + (song.play_count || 0) + '</td>'
                    + '<td><div class="song-actions">'
                    + '<button class="btn-icon btn-edit" title="Sua" onclick="openEditModal(' + song.id + ')"><i class="fas fa-pen"></i></button>'
                    + '<button class="btn-icon btn-delete-song" title="Xoa" onclick="deleteSongRow(' + song.id + ', \'' + escapeJs(song.title) + '\')"><i class="fas fa-trash"></i></button>'
                    + '</div></td>'
                    + '</tr>';
            }).join('');
        }

        function escapeJs(value) {
            return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        }

        function openEditModal(songId) {
            const song = allSongs.find((item) => item.id === songId);
            if (!song) {
                return;
            }
            editingSongId = songId;
            document.getElementById('editTitle').value = song.title || '';
            document.getElementById('editArtist').value = song.artist || '';
            document.getElementById('editAlbum').value = song.album || '';
            document.getElementById('editGenre').value = song.genre || '';
            document.getElementById('editYear').value = song.release_year || '';
            document.getElementById('editSongModal').classList.add('active');
        }

        function closeEditModal() {
            editingSongId = null;
            document.getElementById('editSongModal').classList.remove('active');
        }

        async function saveSongEdit() {
            if (!editingSongId) {
                return;
            }
            const result = await API.updateSong(
                editingSongId,
                document.getElementById('editTitle').value.trim(),
                document.getElementById('editArtist').value.trim(),
                document.getElementById('editAlbum').value.trim(),
                document.getElementById('editGenre').value.trim(),
                document.getElementById('editYear').value ? Number(document.getElementById('editYear').value) : null
            );

            if (!result.success) {
                showAlert(result.message || 'Khong the cap nhat bai hat', 'error');
                return;
            }

            showAlert('Da cap nhat bai hat', 'success');
            closeEditModal();
            await loadSongs();
        }

        async function deleteSongRow(songId, title) {
            if (!confirm('Ban co chac chan muon xoa bai hat: ' + title + '?')) {
                return;
            }

            const result = await API.deleteSong(songId);
            if (!result.success) {
                showAlert(result.message || 'Khong the xoa bai hat', 'error');
                return;
            }

            showAlert('Da xoa bai hat', 'success');
            await loadStats();
            await loadSongs();
        }

        document.getElementById('songSearchInput').addEventListener('input', (event) => {
            const keyword = event.target.value.trim().toLowerCase();
            filteredSongs = allSongs.filter((song) => {
                return [song.title, song.artist, song.album, song.genre]
                    .filter(Boolean)
                    .some((value) => String(value).toLowerCase().includes(keyword));
            });
            renderSongTable();
        });

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
                    await loadSongs();
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

        document.addEventListener('DOMContentLoaded', async () => {
            await loadStats();
            await loadSongs();
        });

        document.getElementById('editSongModal').addEventListener('click', (event) => {
            if (event.target.id === 'editSongModal') {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
