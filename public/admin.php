<?php
/**
 * Admin Panel
 * 
 * Admin-only page for uploading and managing songs
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

// Require login and admin role
requireLogin('/admin.php');

// Check if user is admin
if (!isAdmin()) {
    header('Location: /index.php');
    exit();
}

$user = getCurrentUser();
?>
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
    <link rel="icon" sizes="32x32" type="image/png" href="https://open.scdn.co/cdn/images/favicon32.a19b4f5b.png">
    <style>
        /* Admin panel styles */
        .admin-container {
            min-height: 100vh;
            background: #1b1b1b;
            padding: 80px 20px 40px;
        }
        
        .admin-content {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .admin-header {
            color: #fff;
            margin-bottom: 40px;
        }
        
        .admin-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: #32e84a;
        }
        
        .admin-card {
            background: #2a2a2a;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            margin-bottom: 24px;
        }
        
        .admin-card h2 {
            color: #32e84a;
            font-size: 22px;
            margin-bottom: 24px;
        }
        
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-field label {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-field input,
        .form-field select {
            padding: 12px 16px;
            background: #333;
            border: 2px solid #444;
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
        }
        
        .form-field input:focus,
        .form-field select:focus {
            outline: none;
            border-color: #32e84a;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            background: #333;
            border: 2px dashed #555;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #999;
        }
        
        .file-input-label:hover {
            border-color: #32e84a;
            background: #3a3a3a;
            color: #32e84a;
        }
        
        .file-input-label i {
            font-size: 24px;
        }
        
        input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        
        .file-name {
            color: #fff;
            margin-top: 8px;
            font-size: 13px;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #32e84a 0%, #28c23d 100%);
            color: #000;
            border: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(50, 232, 74, 0.4);
        }
        
        .btn-upload:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .upload-progress {
            margin-top: 20px;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #333;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #32e84a;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }
        
        .stat-box {
            background: #333;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #32e84a;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #999;
            font-size: 13px;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-left">
            <h2>🎵 Admin Panel</h2>
        </div>
        <div class="nav-center"></div>
        <div class="nav-right">
            <span class="user-name">👤 <?php echo htmlspecialchars($user['username']); ?> (Admin)</span>
            <button onclick="window.location.href='/index.php'" class="nav-btn" title="Player">
                <i class="fas fa-music"></i>
            </button>
            <button onclick="window.location.href='/dashboard.php'" class="nav-btn" title="Dashboard">
                <i class="fas fa-home"></i>
            </button>
            <button onclick="logout()" class="nav-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-cog"></i> Admin Panel</h1>
                <p style="color: #999;">Manage your music library</p>
            </div>

            <!-- Statistics -->
            <div class="admin-card">
                <h2>Statistics</h2>
                <div class="stats-grid" id="statsContainer">
                    <div class="stat-box">
                        <div class="stat-number" id="totalSongs">-</div>
                        <div class="stat-label">Total Songs</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="totalUsers">-</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number" id="totalPlays">-</div>
                        <div class="stat-label">Total Plays</div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="admin-card">
                <h2>Upload New Song</h2>
                
                <div id="uploadAlert" class="alert"></div>
                
                <form id="uploadForm" class="upload-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="title">Song Title *</label>
                            <input type="text" id="title" name="title" required placeholder="Enter song title">
                        </div>
                        
                        <div class="form-field">
                            <label for="artist">Artist *</label>
                            <input type="text" id="artist" name="artist" required placeholder="Enter artist name">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <label for="album">Album</label>
                            <input type="text" id="album" name="album" placeholder="Enter album name">
                        </div>
                        
                        <div class="form-field">
                            <label for="genre">Genre</label>
                            <select id="genre" name="genre">
                                <option value="">Select genre</option>
                                <option value="Pop">Pop</option>
                                <option value="Rock">Rock</option>
                                <option value="Hip Hop">Hip Hop</option>
                                <option value="Electronic">Electronic</option>
                                <option value="V-Pop">V-Pop</option>
                                <option value="Latin Pop">Latin Pop</option>
                                <option value="Funk">Funk</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="releaseYear">Release Year</label>
                        <input type="number" id="releaseYear" name="release_year" min="1900" max="2099" placeholder="2024">
                    </div>
                    
                    <div class="form-field">
                        <label>Audio File (MP3) *</label>
                        <div class="file-input-wrapper">
                            <label for="audioFile" class="file-input-label">
                                <i class="fas fa-music"></i>
                                <span>Choose audio file</span>
                            </label>
                            <input type="file" id="audioFile" name="audio_file" accept="audio/mpeg,audio/mp3" required>
                        </div>
                        <div id="audioFileName" class="file-name"></div>
                    </div>
                    
                    <div class="form-field">
                        <label>Cover Image (JPG/PNG) *</label>
                        <div class="file-input-wrapper">
                            <label for="coverImage" class="file-input-label">
                                <i class="fas fa-image"></i>
                                <span>Choose cover image</span>
                            </label>
                            <input type="file" id="coverImage" name="cover_image" accept="image/jpeg,image/jpg,image/png" required>
                        </div>
                        <div id="coverFileName" class="file-name"></div>
                    </div>
                    
                    <button type="submit" class="btn-upload" id="uploadBtn">
                        <i class="fas fa-upload"></i> Upload Song
                    </button>
                    
                    <div class="upload-progress" id="uploadProgress">
                        <p style="color: #fff; margin-bottom: 8px;">Uploading...</p>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Manage Songs -->
            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h2>Manage Songs</h2>
                    <div class="search-box" style="flex: 1; max-width: 300px;">
                        <input type="text" id="songSearch" placeholder="Search songs..." style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #444; background: #333; color: white;">
                    </div>
                </div>
                
                <div class="songs-list-container" style="overflow-x: auto;">
                    <table class="songs-table" style="width: 100%; border-collapse: collapse; color: #ddd;">
                        <thead>
                            <tr style="border-bottom: 1px solid #444; text-align: left;">
                                <th style="padding: 10px;">Image</th>
                                <th style="padding: 10px;">Title</th>
                                <th style="padding: 10px;">Artist</th>
                                <th style="padding: 10px;">Album</th>
                                <th style="padding: 10px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="songsTableBody">
                            <!-- Songs will be loaded here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination" id="paginationControls" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                    <!-- Pagination controls -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Song Modal -->
    <div id="editModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; overflow-y: auto;">
        <div class="modal-content" style="background: #2a2a2a; margin: 50px auto; padding: 30px; width: 90%; max-width: 600px; border-radius: 12px; position: relative; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
            <span class="close-modal" onclick="closeEditModal()" style="position: absolute; top: 15px; right: 20px; color: #aaa; font-size: 28px; cursor: pointer; font-weight: bold;">&times;</span>
            <h2 style="color: #32e84a; margin-bottom: 20px;">Edit Song</h2>
            
            <div id="editAlert" class="alert"></div>

            <form id="editForm" class="upload-form">
                <input type="hidden" id="editSongId">
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="editTitle">Song Title *</label>
                        <input type="text" id="editTitle" name="title" required>
                    </div>
                    
                    <div class="form-field">
                        <label for="editArtist">Artist *</label>
                        <input type="text" id="editArtist" name="artist" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <label for="editAlbum">Album</label>
                        <input type="text" id="editAlbum" name="album">
                    </div>
                    
                    <div class="form-field">
                        <label for="editGenre">Genre</label>
                        <select id="editGenre" name="genre">
                            <option value="">Select genre</option>
                            <option value="Pop">Pop</option>
                            <option value="Rock">Rock</option>
                            <option value="Hip Hop">Hip Hop</option>
                            <option value="Electronic">Electronic</option>
                            <option value="V-Pop">V-Pop</option>
                            <option value="Latin Pop">Latin Pop</option>
                            <option value="Funk">Funk</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-field">
                    <label for="editReleaseYear">Release Year</label>
                    <input type="number" id="editReleaseYear" name="release_year" min="1900" max="2099">
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-upload" id="saveBtn" style="flex: 1;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="button" class="btn-upload" onclick="closeEditModal()" style="flex: 1; background: #444; color: white;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="/assets/js/api.js?v=<?php echo time(); ?>"></script>
    <script>
        // File input handlers
        document.getElementById('audioFile').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            document.getElementById('audioFileName').textContent = fileName ? `Selected: ${fileName}` : '';
        });

        document.getElementById('coverImage').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            document.getElementById('coverFileName').textContent = fileName ? `Selected: ${fileName}` : '';
        });

        // Show alert
        function showAlert(message, type) {
            const alert = document.getElementById('uploadAlert');
            alert.textContent = message;
            alert.className = `alert alert-${type} show`;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }

        // Load statistics
        async function loadStats() {
            try {
                const songsResponse = await API.getSongs(1, 1);
                if (songsResponse.success) {
                    const totalSongs = songsResponse.data.pagination.total_items;
                    document.getElementById('totalSongs').textContent = totalSongs;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Handle upload form
        document.getElementById('uploadForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadProgress = document.getElementById('uploadProgress');
            const progressFill = document.getElementById('progressFill');
            
            // Validate files
            const audioFile = document.getElementById('audioFile').files[0];
            const coverImage = document.getElementById('coverImage').files[0];
            
            if (!audioFile || !coverImage) {
                showAlert('Please select both audio file and cover image', 'error');
                return;
            }
            
            // Validate file types
            if (!audioFile.type.includes('audio')) {
                showAlert('Audio file must be MP3 format', 'error');
                return;
            }
            
            if (!coverImage.type.includes('image')) {
                showAlert('Cover image must be JPG or PNG', 'error');
                return;
            }
            
            // Prepare form data
            const formData = new FormData(this);
            
            // Disable button and show progress
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadProgress.style.display = 'block';
            progressFill.style.width = '30%';
            
            try {
                // Upload song
                const response = await fetch('/api/admin/upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                progressFill.style.width = '70%';
                
                const data = await response.json();
                
                progressFill.style.width = '100%';
                
                if (data.success) {
                    showAlert('Song uploaded successfully!', 'success');
                    
                    // Reset form
                    document.getElementById('uploadForm').reset();
                    document.getElementById('audioFileName').textContent = '';
                    document.getElementById('coverFileName').textContent = '';
                    
                    // Reload stats
                    await loadStats();
                } else {
                    showAlert(data.message || 'Upload failed', 'error');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showAlert('An error occurred during upload', 'error');
            } finally {
                // Re-enable button and hide progress
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Song';
                
                setTimeout(() => {
                    uploadProgress.style.display = 'none';
                    progressFill.style.width = '0%';
                }, 1000);
            }
        });

        // Logout
        async function logout() {
            if (confirm('Are you sure you want to logout?')) {
                await API.logout();
                window.location.href = '/login.php';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadSongs();
        });

        // --- Song Management ---
        let currentPage = 1;
        let currentSearch = '';
        let songsCache = [];

        // Debounce search
        let searchTimeout;
        document.getElementById('songSearch').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentSearch = e.target.value;
                currentPage = 1;
                loadSongs();
            }, 500);
        });

        async function loadSongs() {
            const tbody = document.getElementById('songsTableBody');
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">Loading...</td></tr>';
            
            try {
                let response;
                if (currentSearch) {
                    response = await API.searchSongs(currentSearch, currentPage, 10);
                } else {
                    response = await API.getSongs(currentPage, 10);
                }
                
                if (response.success) {
                    const songs = response.data.songs;
                    songsCache = songs; // Cache for editing
                    renderSongs(songs);
                    renderPagination(response.data.pagination);
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #ff5555;">Failed to load songs</td></tr>';
                }
            } catch (error) {
                console.error('Error loading songs:', error);
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #ff5555;">Error loading songs</td></tr>';
            }
        }

        function renderSongs(songs) {
            const tbody = document.getElementById('songsTableBody');
            if (songs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #999;">No songs found</td></tr>';
                return;
            }
            
            tbody.innerHTML = songs.map(song => `
                <tr style="border-bottom: 1px solid #333; background: rgba(255,255,255,0.02);">
                    <td style="padding: 10px;">
                        <img src="${song.cover_url}" alt="Cover" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px;">${escapeHtml(song.title)}</td>
                    <td style="padding: 10px;">${escapeHtml(song.artist)}</td>
                    <td style="padding: 10px; color: #999;">${escapeHtml(song.album || '-')}</td>
                    <td style="padding: 10px; text-align: center;">
                        <button onclick="openEditModal(${song.id})" class="btn-icon" title="Edit" style="background: none; border: none; color: #32e84a; cursor: pointer; margin-right: 10px;">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteSong(${song.id}, '${escapeHtml(song.title.replace(/'/g, "\\'"))}')" class="btn-icon" title="Delete" style="background: none; border: none; color: #ff5555; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function renderPagination(pagination) {
            const container = document.getElementById('paginationControls');
            const totalPages = pagination.total_pages;
            
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // Previous
            html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} 
                    style="padding: 8px 12px; background: #333; border: none; color: white; border-radius: 4px; cursor: pointer; ${currentPage === 1 ? 'opacity: 0.5; cursor: not-allowed;' : ''}">
                    <i class="fas fa-chevron-left"></i>
                </button>`;
                
            // Page numbers (simplified)
            html += `<span style="color: #fff; align-self: center;">Page ${currentPage} of ${totalPages}</span>`;
            
            // Next
            html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} 
                    style="padding: 8px 12px; background: #333; border: none; color: white; border-radius: 4px; cursor: pointer; ${currentPage === totalPages ? 'opacity: 0.5; cursor: not-allowed;' : ''}">
                    <i class="fas fa-chevron-right"></i>
                </button>`;
                
            container.innerHTML = html;
        }

        function changePage(page) {
            if (page < 1) return;
            currentPage = page;
            loadSongs();
        }

        // Edit functions
        function openEditModal(id) {
            const song = songsCache.find(s => s.id == id);
            if (!song) return;
            
            document.getElementById('editSongId').value = song.id;
            document.getElementById('editTitle').value = song.title;
            document.getElementById('editArtist').value = song.artist;
            document.getElementById('editAlbum').value = song.album || '';
            document.getElementById('editGenre').value = song.genre || '';
            document.getElementById('editReleaseYear').value = song.release_year || '';
            
            document.getElementById('editAlert').className = 'alert';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('editSongId').value;
            const title = document.getElementById('editTitle').value;
            const artist = document.getElementById('editArtist').value;
            const album = document.getElementById('editAlbum').value;
            const genre = document.getElementById('editGenre').value;
            const releaseYear = document.getElementById('editReleaseYear').value;
            
            const saveBtn = document.getElementById('saveBtn');
            const editAlert = document.getElementById('editAlert');
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            try {
                const response = await API.updateSong(id, title, artist, album, genre, releaseYear);
                
                if (response.success) {
                    editAlert.textContent = 'Song updated successfully!';
                    editAlert.className = 'alert alert-success show';
                    
                    // Reload songs to show changes
                    loadSongs();
                    
                    setTimeout(() => {
                        closeEditModal();
                    }, 1000);
                } else {
                    editAlert.textContent = response.message || 'Update failed';
                    editAlert.className = 'alert alert-error show';
                }
            } catch (error) {
                console.error('Update error:', error);
                editAlert.textContent = 'An error occurred';
                editAlert.className = 'alert alert-error show';
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            }
        });

        // Delete function
        async function deleteSong(id, title) {
            if (confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
                try {
                    const response = await API.deleteSong(id);
                    if (response.success) {
                        // Refresh list
                        loadSongs();
                        // Refresh stats
                        loadStats();
                        alert('Song deleted successfully');
                    } else {
                        alert(response.message || 'Failed to delete song');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    alert('An error occurred while deleting the song');
                }
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }

    </script>
</body>
</html>

