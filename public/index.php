<?php
/**
 * Main Music Player Page
 * 
 * Unified interface with player, playlists, and favorites
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login to access player
requireLogin('/index.php');

// Get current user
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Music Player - <?php echo htmlspecialchars($user['username']); ?></title>
	<link rel="stylesheet" href="./assets/css/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<link rel="icon" sizes="32x32" type="image/png" href="./assets/favicon/spotify.png">
	<style>
		/* Modern unified layout */
		body {
			margin: 0;
			padding: 0;
			overflow: hidden;
		}

		.main-container {
			display: flex;
			height: 100vh;
			background: #121212;
		}

		/* Sidebar */
		.sidebar {
			width: 280px;
			background: #000;
			padding: 24px 16px;
			overflow-y: auto;
			border-right: 1px solid #282828;
		}

		.sidebar-header {
			margin-bottom: 32px;
		}

		.sidebar-header h1 {
			color: #32e84a;
			font-size: 24px;
			margin: 0 0 8px 0;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.sidebar-user {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 12px;
			background: #1a1a1a;
			border-radius: 8px;
			margin-bottom: 24px;
		}

		.sidebar-user-avatar {
			width: 40px;
			height: 40px;
			background: linear-gradient(135deg, #32e84a, #1db954);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 18px;
		}

		.sidebar-user-info h3 {
			color: #fff;
			margin: 0;
			font-size: 14px;
		}

		.sidebar-user-info p {
			color: #b3b3b3;
			margin: 4px 0 0 0;
			font-size: 12px;
		}

		.sidebar-nav {
			list-style: none;
			padding: 0;
			margin: 0 0 32px 0;
		}

		.sidebar-nav li {
			margin-bottom: 8px;
		}

		.sidebar-nav button {
			width: 100%;
			padding: 12px 16px;
			background: transparent;
			border: none;
			color: #b3b3b3;
			text-align: left;
			cursor: pointer;
			border-radius: 6px;
			font-size: 14px;
			font-weight: 600;
			transition: all 0.2s;
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.sidebar-nav button:hover {
			color: #fff;
			background: #1a1a1a;
		}

		.sidebar-nav button.active {
			color: #fff;
			background: #282828;
		}

		.sidebar-nav button i {
			font-size: 20px;
			width: 24px;
		}

		.sidebar-section {
			margin-bottom: 24px;
		}

		.sidebar-section-title {
			color: #b3b3b3;
			font-size: 12px;
			font-weight: 700;
			text-transform: uppercase;
			margin-bottom: 12px;
			letter-spacing: 0.1em;
		}

		.playlist-item {
			padding: 8px 16px;
			color: #b3b3b3;
			font-size: 14px;
			cursor: pointer;
			border-radius: 4px;
			transition: all 0.2s;
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.playlist-item:hover {
			color: #fff;
			background: #1a1a1a;
		}

		.playlist-item i {
			font-size: 16px;
		}

		.btn-create-playlist {
			width: 100%;
			padding: 10px 16px;
			background: transparent;
			border: 2px dashed #404040;
			color: #b3b3b3;
			border-radius: 8px;
			cursor: pointer;
			font-size: 13px;
			font-weight: 600;
			transition: all 0.2s;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		.btn-create-playlist:hover {
			border-color: #32e84a;
			color: #32e84a;
		}

		/* Main Content */
		.main-content {
			flex: 1;
			display: flex;
			flex-direction: column;
			overflow: hidden;
		}

		/* Top Bar */
		.top-bar {
			height: 64px;
			background: rgba(0,0,0,0.8);
			backdrop-filter: blur(10px);
			padding: 0 32px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			border-bottom: 1px solid #282828;
		}

		.search-container {
			flex: 1;
			max-width: 500px;
		}

		.search-input {
			width: 100%;
			padding: 10px 40px 10px 16px;
			background: #fff;
			border: none;
			border-radius: 24px;
			font-size: 14px;
			color: #000;
		}

		.search-input:focus {
			outline: none;
			box-shadow: 0 0 0 2px #32e84a;
		}

		.top-bar-actions {
			display: flex;
			align-items: center;
			gap: 16px;
		}

		.btn-logout {
			padding: 8px 24px;
			background: transparent;
			border: 1px solid #fff;
			color: #fff;
			border-radius: 24px;
			cursor: pointer;
			font-size: 13px;
			font-weight: 600;
			transition: all 0.2s;
		}

		.btn-logout:hover {
			transform: scale(1.05);
			background: #fff;
			color: #000;
		}

		/* Content Area */
		.content-area {
			flex: 1;
			overflow-y: auto;
			padding: 32px;
		}

		.view-section {
			display: none;
		}

		.view-section.active {
			display: block;
		}

		.view-header {
			margin-bottom: 32px;
		}

		.view-header h2 {
			color: #fff;
			font-size: 48px;
			font-weight: 900;
			margin: 0 0 8px 0;
		}

		.view-header p {
			color: #b3b3b3;
			font-size: 14px;
			margin: 0;
		}

		/* Player Section (Now Playing) */
		.now-playing-card {
			background: linear-gradient(180deg, #1e3a5f 0%, #0d1b2a 100%);
			border-radius: 16px;
			padding: 48px;
			margin-bottom: 32px;
			display: flex;
			align-items: center;
			gap: 48px;
		}

		.now-playing-artwork {
			width: 280px;
			height: 280px;
			border-radius: 12px;
			box-shadow: 0 20px 60px rgba(0,0,0,0.5);
			position: relative;
			overflow: hidden;
		}

		.now-playing-artwork img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.now-playing-artwork.rotating img {
			animation: rotate 20s linear infinite;
		}

		@keyframes rotate {
			from { transform: rotate(0deg); }
			to { transform: rotate(360deg); }
		}

		.now-playing-info {
			flex: 1;
		}

		.now-playing-title {
			color: #fff;
			font-size: 64px;
			font-weight: 900;
			margin: 0 0 12px 0;
			line-height: 1;
		}

		.now-playing-artist {
			color: #b3b3b3;
			font-size: 24px;
			margin: 0 0 32px 0;
		}

		.player-controls {
			display: flex;
			align-items: center;
			gap: 24px;
			margin-bottom: 24px;
		}

		.control-btn {
			background: transparent;
			border: none;
			color: #fff;
			cursor: pointer;
			font-size: 24px;
			transition: all 0.2s;
			padding: 8px;
		}

		.control-btn:hover {
			color: #32e84a;
			transform: scale(1.1);
		}

		.control-btn-play {
			width: 64px;
			height: 64px;
			background: #32e84a;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 28px;
			color: #000;
		}

		.control-btn-play:hover {
			background: #1db954;
			transform: scale(1.05);
			color: #000;
		}

		.progress-bar {
			width: 100%;
			height: 8px;
			background: #404040;
			border-radius: 4px;
			cursor: pointer;
			position: relative;
			overflow: hidden;
		}

		.progress-fill {
			height: 100%;
			background: #32e84a;
			width: 0%;
			border-radius: 4px;
			transition: width 0.1s;
		}

		/* Song Grid */
		.songs-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
			gap: 24px;
		}

		.song-card {
			background: #181818;
			padding: 16px;
			border-radius: 8px;
			cursor: pointer;
			transition: all 0.2s;
			position: relative;
		}

		.song-card:hover {
			background: #282828;
			transform: translateY(-4px);
		}

		.song-card-img {
			width: 100%;
			aspect-ratio: 1;
			border-radius: 8px;
			margin-bottom: 16px;
			position: relative;
			overflow: hidden;
		}

		.song-card-img img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.song-card-play {
			position: absolute;
			bottom: 8px;
			right: 8px;
			width: 48px;
			height: 48px;
			background: #32e84a;
			border-radius: 50%;
			border: none;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 20px;
			color: #000;
			opacity: 0;
			transform: translateY(8px);
			transition: all 0.2s;
			cursor: pointer;
		}

		.song-card:hover .song-card-play {
			opacity: 1;
			transform: translateY(0);
		}

		.song-card-title {
			color: #fff;
			font-size: 16px;
			font-weight: 700;
			margin: 0 0 4px 0;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.song-card-artist {
			color: #b3b3b3;
			font-size: 14px;
			margin: 0;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.song-card-actions {
			position: absolute;
			top: 16px;
			right: 16px;
			display: flex;
			gap: 8px;
		}

		.btn-favorite {
			background: rgba(0,0,0,0.7);
			border: none;
			width: 32px;
			height: 32px;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			color: #fff;
			font-size: 14px;
			opacity: 0;
			transition: all 0.2s;
		}

		.song-card:hover .btn-favorite {
			opacity: 1;
		}

		.btn-favorite.active {
			color: #ff4757;
			opacity: 1;
		}

		.btn-favorite:hover {
			transform: scale(1.1);
		}

		/* List View */
		.songs-list {
			background: #181818;
			border-radius: 8px;
			overflow: hidden;
		}

		.song-list-item {
			display: flex;
			align-items: center;
			padding: 12px 16px;
			gap: 16px;
			cursor: pointer;
			transition: all 0.2s;
			border-bottom: 1px solid #282828;
		}

		.song-list-item:hover {
			background: #282828;
		}

		.song-list-item.playing {
			background: #1a5236;
		}

		.song-list-number {
			color: #b3b3b3;
			font-size: 16px;
			width: 32px;
			text-align: center;
		}

		.song-list-img {
			width: 48px;
			height: 48px;
			border-radius: 4px;
		}

		.song-list-img img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.song-list-info {
			flex: 1;
			min-width: 0;
		}

		.song-list-title {
			color: #fff;
			font-size: 16px;
			font-weight: 600;
			margin: 0 0 4px 0;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.song-list-artist {
			color: #b3b3b3;
			font-size: 14px;
			margin: 0;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
		}

		.song-list-actions {
			display: flex;
			gap: 16px;
			align-items: center;
		}

		/* Stats Cards */
		.stats-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 16px;
			margin-bottom: 32px;
		}

		.stat-card {
			background: linear-gradient(135deg, #1e3a5f, #0d1b2a);
			padding: 24px;
			border-radius: 12px;
		}

		.stat-card h3 {
			color: #b3b3b3;
			font-size: 12px;
			font-weight: 600;
			text-transform: uppercase;
			margin: 0 0 8px 0;
		}

		.stat-card-number {
			color: #fff;
			font-size: 36px;
			font-weight: 900;
			margin: 0;
		}

		/* Empty State */
		.empty-state {
			text-align: center;
			padding: 64px 32px;
		}

		.empty-state i {
			font-size: 64px;
			color: #404040;
			margin-bottom: 24px;
		}

		.empty-state h3 {
			color: #fff;
			font-size: 24px;
			margin: 0 0 8px 0;
		}

		.empty-state p {
			color: #b3b3b3;
			margin: 0;
		}

		/* Modal */
		.modal {
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0,0,0,0.8);
			z-index: 1000;
			align-items: center;
			justify-content: center;
		}

		.modal.active {
			display: flex;
		}

		.modal-content {
			background: #282828;
			border-radius: 12px;
			padding: 32px;
			width: 90%;
			max-width: 500px;
		}

		.modal-content h2 {
			color: #fff;
			margin: 0 0 24px 0;
		}

		.modal-content input {
			width: 100%;
			padding: 12px;
			background: #3e3e3e;
			border: 1px solid #535353;
			border-radius: 4px;
			color: #fff;
			font-size: 14px;
			margin-bottom: 16px;
		}

		.modal-content input:focus {
			outline: none;
			border-color: #32e84a;
		}

		.modal-buttons {
			display: flex;
			gap: 12px;
			justify-content: flex-end;
		}

		.btn-modal {
			padding: 12px 32px;
			border: none;
			border-radius: 24px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.2s;
		}

		.btn-modal-primary {
			background: #32e84a;
			color: #000;
		}

		.btn-modal-primary:hover {
			background: #1db954;
		}

		.btn-modal-secondary {
			background: transparent;
			color: #fff;
			border: 1px solid #fff;
		}

		.btn-modal-secondary:hover {
			background: rgba(255,255,255,0.1);
		}

		/* Audio Element */
		audio {
			display: none;
		}

		/* Scrollbar */
		::-webkit-scrollbar {
			width: 12px;
		}

		::-webkit-scrollbar-track {
			background: #121212;
		}

		::-webkit-scrollbar-thumb {
			background: #282828;
			border-radius: 6px;
		}

		::-webkit-scrollbar-thumb:hover {
			background: #3e3e3e;
		}

		/* Responsive */
		@media (max-width: 768px) {
			.sidebar {
				display: none;
			}

			.now-playing-card {
				flex-direction: column;
				padding: 24px;
				gap: 24px;
			}

			.now-playing-artwork {
				width: 200px;
				height: 200px;
			}

			.now-playing-title {
				font-size: 32px;
			}

			.now-playing-artist {
				font-size: 18px;
			}
		}
	</style>
</head>
<body>
	<div class="main-container">
		<!-- Sidebar -->
		<aside class="sidebar">
			<div class="sidebar-header">
				<h1><i class="fas fa-music"></i> Music Player</h1>
			</div>

			<div class="sidebar-user">
				<div class="sidebar-user-avatar">
					<?php echo strtoupper(substr($user['username'], 0, 1)); ?>
				</div>
				<div class="sidebar-user-info">
					<h3><?php echo htmlspecialchars($user['username']); ?></h3>
					<p><?php echo ucfirst($user['role']); ?></p>
				</div>
			</div>

			<ul class="sidebar-nav">
				<li><button class="nav-item active" onclick="switchView('player')"><i class="fas fa-play-circle"></i> Now Playing</button></li>
				<li><button class="nav-item" onclick="switchView('library')"><i class="fas fa-music"></i> Your Library</button></li>
				<li><button class="nav-item" onclick="switchView('favorites')"><i class="fas fa-heart"></i> Liked Songs</button></li>
				<li><button class="nav-item" onclick="switchView('playlists')"><i class="fas fa-list"></i> Playlists</button></li>
			</ul>

			<div class="sidebar-section">
				<div class="sidebar-section-title">Your Playlists</div>
				<div id="sidebarPlaylists">
					<div style="color: #b3b3b3; font-size: 13px; padding: 8px 16px;">Loading...</div>
				</div>
				<button class="btn-create-playlist" onclick="showCreatePlaylistModal()">
					<i class="fas fa-plus"></i> Create Playlist
				</button>
			</div>

			<?php if ($user['role'] === 'admin'): ?>
			<ul class="sidebar-nav" style="margin-top: 32px; padding-top: 16px; border-top: 1px solid #282828;">
				<li><button class="nav-item" onclick="window.location.href='/admin.php'"><i class="fas fa-cog"></i> Admin Panel</button></li>
			</ul>
			<?php endif; ?>
		</aside>

		<!-- Main Content -->
		<main class="main-content">
			<!-- Top Bar -->
			<div class="top-bar">
				<div class="search-container">
					<input type="text" id="searchInput" class="search-input" placeholder="Search for songs or artists...">
				</div>
				<div class="top-bar-actions">
					<button class="btn-logout" onclick="logout()">
						<i class="fas fa-sign-out-alt"></i> Logout
					</button>
				</div>
			</div>

			<!-- Content Area -->
			<div class="content-area">
				<!-- Now Playing View -->
				<div id="playerView" class="view-section active">
					<div class="now-playing-card">
						<div class="now-playing-artwork" id="nowPlayingArtwork">
							<img src="./assets/img/Attention.jpeg" alt="Album Art">
						</div>
						<div class="now-playing-info">
							<h1 class="now-playing-title" id="nowPlayingTitle">Select a song</h1>
							<p class="now-playing-artist" id="nowPlayingArtist">Start playing music</p>
							
							<div class="player-controls">
								<button class="control-btn" id="shuffleBtn" title="Shuffle">
									<i class="fas fa-random"></i>
								</button>
								<button class="control-btn" id="prevBtn" title="Previous">
									<i class="fas fa-step-backward"></i>
								</button>
								<button class="control-btn control-btn-play" id="playBtn" title="Play">
									<i class="fas fa-play"></i>
								</button>
								<button class="control-btn" id="nextBtn" title="Next">
									<i class="fas fa-step-forward"></i>
								</button>
								<button class="control-btn" id="repeatBtn" title="Repeat">
									<i class="fas fa-redo"></i>
								</button>
							</div>

							<div class="progress-bar" id="progressBar">
								<div class="progress-fill" id="progressFill"></div>
							</div>
						</div>
					</div>

					<div class="view-header">
						<h2>Recommended for you</h2>
						<p>Based on your listening history</p>
					</div>

					<div class="songs-grid" id="recommendedSongs"></div>
				</div>

				<!-- Library View -->
				<div id="libraryView" class="view-section">
					<div class="view-header">
						<h2>Your Library</h2>
						<p id="libraryCount">Loading songs...</p>
					</div>

					<div class="songs-list" id="songsList"></div>
				</div>

				<!-- Favorites View -->
				<div id="favoritesView" class="view-section">
					<div class="view-header">
						<h2>Liked Songs</h2>
						<p id="favoritesCount">Your favorite tracks</p>
					</div>

					<div class="songs-grid" id="favoritesSongs"></div>
				</div>

				<!-- Playlists View -->
				<div id="playlistsView" class="view-section">
					<div class="view-header">
						<h2>Your Playlists</h2>
						<p id="playlistsCount">Create and manage playlists</p>
					</div>

					<div class="stats-grid" id="playlistsGrid"></div>
				</div>
			</div>
		</main>
	</div>

	<!-- Audio Element -->
	<audio id="audioPlayer"></audio>

	<!-- Create Playlist Modal -->
	<div class="modal" id="createPlaylistModal">
		<div class="modal-content">
			<h2>Create New Playlist</h2>
			<input type="text" id="playlistNameInput" placeholder="Playlist name" maxlength="255">
			<input type="text" id="playlistDescInput" placeholder="Description (optional)">
			<div class="modal-buttons">
				<button class="btn-modal btn-modal-secondary" onclick="closeCreatePlaylistModal()">Cancel</button>
				<button class="btn-modal btn-modal-primary" onclick="createPlaylist()">Create</button>
			</div>
		</div>
	</div>

	<!-- Load Scripts -->
	<script>
		// Set admin status from PHP
		window.isAdmin = <?php echo json_encode($user['role'] === 'admin'); ?>;
	</script>
	<script src="./assets/js/api.js?v=<?php echo time(); ?>"></script>
	<script src="./assets/js/player.js?v=<?php echo time(); ?>"></script>
</body>
</html>
