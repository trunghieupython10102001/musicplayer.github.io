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
    <title>Dashboard - Music Player</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" sizes="32x32" type="image/png" href="/assets/favicon/spotify.png">
    <style>
        .dashboard-container { min-height: 100vh; background: #1b1b1b; padding: 80px 20px 40px; }
        .dashboard-content { max-width: 1200px; margin: 0 auto; }
        .dashboard-header { color: #fff; margin-bottom: 40px; }
        .dashboard-header h1 { font-size: 36px; margin-bottom: 8px; }
        .dashboard-header p { color: #999; font-size: 16px; }
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 40px; }
        .dashboard-card { background: #2a2a2a; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); }
        .dashboard-card h3 { color: #32e84a; font-size: 18px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
        .dashboard-card .card-content { color: #fff; }
        .stat-number { font-size: 48px; font-weight: bold; color: #32e84a; margin: 16px 0; }
        .playlist-list, .favorite-list { list-style: none; padding: 0; margin: 0; }
        .playlist-item, .favorite-item { padding: 12px; background: #333; border-radius: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
        .playlist-item:hover, .favorite-item:hover { background: #3a3a3a; }
        .item-name { color: #fff; font-weight: 600; }
        .item-meta { color: #999; font-size: 13px; }
        .btn-action { background: #32e84a; color: #000; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s ease; }
        .btn-action:hover { background: #28c23d; transform: translateY(-2px); }
        .btn-delete { background: #ff4757; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-delete:hover { background: #ff3838; }
        .empty-state { text-align: center; padding: 40px 20px; color: #999; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; }
        .loading { text-align: center; padding: 40px; color: #999; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.7); }
        .modal-content { background-color: #2a2a2a; margin: 10% auto; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; }
        .modal h2 { color: #fff; margin-bottom: 20px; }
        .modal input { width: 100%; padding: 12px; background: #333; border: 2px solid #444; border-radius: 6px; color: #fff; font-size: 14px; margin-bottom: 16px; }
        .modal input:focus { outline: none; border-color: #32e84a; }
        .modal-buttons { display: flex; gap: 12px; justify-content: flex-end; }
        .btn-secondary { background: #555; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
        .btn-secondary:hover { background: #666; }
        .top-nav { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 24px; background:#121212; border-bottom:1px solid #282828; position:sticky; top:0; z-index:20; }
        .nav-left h2 { color:#fff; margin:0; }
        .nav-center { display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
        .nav-item { color:#b3b3b3; display:flex; align-items:center; gap:8px; cursor:pointer; }
        .nav-item:hover { color:#fff; }
        .nav-right { display:flex; align-items:center; gap:12px; color:#fff; }
        .nav-btn { background:#2a2a2a; border:none; color:#fff; width:40px; height:40px; border-radius:50%; cursor:pointer; }
        .nav-btn:hover { background:#3a3a3a; }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="nav-left">
            <h2><i class="fas fa-music"></i> Music Player</h2>
        </div>
        <div class="nav-center">
            <div class="nav-item" onclick="window.location.href='/dashboard.php'">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item" onclick="window.location.href='/index.php'">
                <i class="fas fa-music"></i>
                <span>Your Library</span>
            </div>
            <div class="nav-item" onclick="window.location.href='/index.php'">
                <i class="fas fa-heart"></i>
                <span>Liked Songs</span>
            </div>
            <div class="nav-item" onclick="window.location.href='/index.php'">
                <i class="fas fa-compact-disc"></i>
                <span>Albums</span>
            </div>
        </div>
        <div class="nav-right">
            <span class="user-name"><i class="fas fa-user"></i> <%= currentUser != null ? currentUser.username() : "" %></span>
            <button onclick="window.location.href='/index.php'" class="nav-btn" title="Player">
                <i class="fas fa-play"></i>
            </button>
            <% if (currentUser != null && "admin".equals(currentUser.role())) { %>
            <button onclick="window.location.href='/admin.php'" class="nav-btn" title="Admin Panel">
                <i class="fas fa-cog"></i>
            </button>
            <% } %>
            <button onclick="logout()" class="nav-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="dashboard-header">
                <h1>Welcome back, <%= currentUser != null ? currentUser.username() : "User" %>! <span aria-hidden="true">&#128075;</span></h1>
                <p>Here's your music overview</p>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3><i class="fas fa-list"></i> My Playlists</h3>
                    <div class="card-content">
                        <div id="playlistStats" class="loading">Loading...</div>
                        <button onclick="showCreatePlaylistModal()" class="btn-action" style="margin-top: 16px;">
                            <i class="fas fa-plus"></i> Create Playlist
                        </button>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3><i class="fas fa-heart"></i> Favorite Songs</h3>
                    <div class="card-content">
                        <div id="favoriteStats" class="loading">Loading...</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <h3><i class="fas fa-folder"></i> Your Playlists</h3>
                <div id="playlistsContainer" class="loading">Loading playlists...</div>
            </div>

            <div class="dashboard-card" style="margin-top: 24px;">
                <h3><i class="fas fa-star"></i> Your Favorites</h3>
                <div id="favoritesContainer" class="loading">Loading favorites...</div>
            </div>
        </div>
    </div>

    <div id="createPlaylistModal" class="modal">
        <div class="modal-content">
            <h2>Create New Playlist</h2>
            <input type="text" id="playlistName" placeholder="Playlist name" maxlength="255">
            <input type="text" id="playlistDescription" placeholder="Description (optional)">
            <div class="modal-buttons">
                <button class="btn-secondary" onclick="closeCreatePlaylistModal()">Cancel</button>
                <button class="btn-action" onclick="createPlaylist()">Create</button>
            </div>
        </div>
    </div>

    <script src="/assets/js/api.js"></script>
    <script>
        let playlists = [];
        let favorites = [];

        async function loadDashboard() {
            await loadPlaylists();
            await loadFavorites();
        }

        async function loadPlaylists() {
            const response = await API.getPlaylists();
            if (response.success) {
                playlists = response.data.playlists || [];
                displayPlaylistStats();
                displayPlaylists();
            }
        }

        async function loadFavorites() {
            const response = await API.getFavorites();
            if (response.success) {
                favorites = response.data.favorites || [];
                displayFavoriteStats();
                displayFavorites();
            }
        }

        function displayPlaylistStats() {
            const container = document.getElementById('playlistStats');
            const totalSongs = playlists.reduce((sum, playlist) => sum + Number(playlist.song_count || 0), 0);
            container.innerHTML = '<div class="stat-number">' + playlists.length + '</div>'
                + '<div class="item-meta">' + totalSongs + ' total songs</div>';
        }

        function displayFavoriteStats() {
            const container = document.getElementById('favoriteStats');
            container.innerHTML = '<div class="stat-number">' + favorites.length + '</div>'
                + '<div class="item-meta">songs in favorites</div>';
        }

        function displayPlaylists() {
            const container = document.getElementById('playlistsContainer');
            if (playlists.length === 0) {
                container.innerHTML = '<div class="empty-state">'
                    + '<i class="fas fa-folder-open"></i>'
                    + '<p>No playlists yet. Create your first playlist!</p>'
                    + '</div>';
                return;
            }

            const items = playlists.map((playlist) => {
                const createdMeta = playlist.created_at_formatted ? ' • Created ' + playlist.created_at_formatted : '';
                return '<li class="playlist-item">'
                    + '<div>'
                    + '<div class="item-name">' + playlist.name + '</div>'
                    + '<div class="item-meta">' + playlist.song_count + ' songs' + createdMeta + '</div>'
                    + '</div>'
                    + '<button class="btn-delete" onclick="deletePlaylist(' + playlist.id + ')">'
                    + '<i class="fas fa-trash"></i>'
                    + '</button>'
                    + '</li>';
            }).join('');

            container.innerHTML = '<ul class="playlist-list">' + items + '</ul>';
        }

        function displayFavorites() {
            const container = document.getElementById('favoritesContainer');
            if (favorites.length === 0) {
                container.innerHTML = '<div class="empty-state">'
                    + '<i class="fas fa-heart-broken"></i>'
                    + '<p>No favorite songs yet. Start adding some!</p>'
                    + '</div>';
                return;
            }

            const items = favorites.slice(0, 10).map((song) => {
                return '<li class="favorite-item">'
                    + '<div>'
                    + '<div class="item-name">' + song.title + '</div>'
                    + '<div class="item-meta">' + song.artist + '</div>'
                    + '</div>'
                    + '<div class="item-meta">' + (song.added_at_formatted || '') + '</div>'
                    + '</li>';
            }).join('');

            const more = favorites.length > 10
                ? '<p style="color: #999; text-align: center; margin-top: 16px;">And ' + (favorites.length - 10) + ' more...</p>'
                : '';

            container.innerHTML = '<ul class="favorite-list">' + items + '</ul>' + more;
        }

        function showCreatePlaylistModal() {
            document.getElementById('createPlaylistModal').style.display = 'block';
        }

        function closeCreatePlaylistModal() {
            document.getElementById('createPlaylistModal').style.display = 'none';
            document.getElementById('playlistName').value = '';
            document.getElementById('playlistDescription').value = '';
        }

        async function createPlaylist() {
            const name = document.getElementById('playlistName').value.trim();
            const description = document.getElementById('playlistDescription').value.trim();

            if (!name) {
                alert('Please enter a playlist name');
                return;
            }

            const response = await API.createPlaylist(name, description, true);
            if (response.success) {
                closeCreatePlaylistModal();
                await loadPlaylists();
            } else {
                alert(response.message || 'Failed to create playlist');
            }
        }

        async function deletePlaylist(playlistId) {
            if (!confirm('Are you sure you want to delete this playlist?')) {
                return;
            }

            const response = await API.deletePlaylist(playlistId);
            if (response.success) {
                await loadPlaylists();
            } else {
                alert(response.message || 'Failed to delete playlist');
            }
        }

        async function logout() {
            if (confirm('Are you sure you want to logout?')) {
                await API.logout();
                window.location.href = '/login.php';
            }
        }

        document.addEventListener('DOMContentLoaded', loadDashboard);

        window.onclick = function(event) {
            const modal = document.getElementById('createPlaylistModal');
            if (event.target === modal) {
                closeCreatePlaylistModal();
            }
        };
    </script>
</body>
</html>
