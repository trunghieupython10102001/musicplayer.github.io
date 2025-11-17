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
        </div>
    </div>

    <script src="/assets/js/api.js"></script>
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
        document.addEventListener('DOMContentLoaded', loadStats);
    </script>
</body>
</html>

