/**
 * Unified Music Player
 * 
 * Handles all player functionality, library, favorites, and playlists
 */

// Player state
const player = {
    currentIndex: 0,
    songs: [],
    playlists: [],
    favorites: new Set(),
    isPlaying: false,
    isShuffled: false,
    isRepeating: false,
    currentView: 'player'
};

// DOM Elements
const audioPlayer = document.getElementById('audioPlayer');
const playBtn = document.getElementById('playBtn');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const shuffleBtn = document.getElementById('shuffleBtn');
const repeatBtn = document.getElementById('repeatBtn');
const progressBar = document.getElementById('progressBar');
const progressFill = document.getElementById('progressFill');
const nowPlayingTitle = document.getElementById('nowPlayingTitle');
const nowPlayingArtist = document.getElementById('nowPlayingArtist');
const nowPlayingArtwork = document.getElementById('nowPlayingArtwork');
const searchInput = document.getElementById('searchInput');

// Initialize
async function init() {
    // Check authentication
    const authCheck = await API.checkAuth();
    if (!authCheck.success || !authCheck.data.logged_in) {
        window.location.href = '/login.php';
        return;
    }

    // Load data
    await loadSongs();
    await loadFavorites();
    await loadPlaylists();

    // Setup event listeners
    setupEventListeners();

    // Render initial view
    renderRecommendedSongs();
}

// Load all songs from API
async function loadSongs() {
    try {
        const response = await API.getSongs(1, 200);
        if (response.success) {
            player.songs = response.data.songs.map(song => ({
                id: song.id,
                title: song.title,
                artist: song.artist,
                cover: song.cover_url,
                audio: song.audio_url,
                playCount: song.play_count
            }));
            
            console.log(`Loaded ${player.songs.length} songs`);
            document.getElementById('libraryCount').textContent = `${player.songs.length} songs in your library`;
        }
    } catch (error) {
        console.error('Error loading songs:', error);
        showNotification('Failed to load songs', 'error');
    }
}

// Load user favorites
async function loadFavorites() {
    try {
        const response = await API.getFavorites();
        if (response.success) {
            player.favorites = new Set(response.data.favorites.map(f => f.id));
            document.getElementById('favoritesCount').textContent = `${player.favorites.size} liked songs`;
            renderFavorites();
        }
    } catch (error) {
        console.error('Error loading favorites:', error);
    }
}

// Load user playlists
async function loadPlaylists() {
    try {
        const response = await API.getPlaylists();
        if (response.success) {
            player.playlists = response.data.playlists;
            document.getElementById('playlistsCount').textContent = `${player.playlists.length} playlists`;
            renderSidebarPlaylists();
            renderPlaylistsView();
        }
    } catch (error) {
        console.error('Error loading playlists:', error);
    }
}

// Setup event listeners
function setupEventListeners() {
    // Player controls
    playBtn.onclick = togglePlay;
    prevBtn.onclick = playPrevious;
    nextBtn.onclick = playNext;
    shuffleBtn.onclick = toggleShuffle;
    repeatBtn.onclick = toggleRepeat;
    
    // Progress bar
    progressBar.onclick = seek;
    
    // Audio events
    audioPlayer.ontimeupdate = updateProgress;
    audioPlayer.onended = handleSongEnd;
    audioPlayer.onplay = () => {
        player.isPlaying = true;
        updatePlayButton();
        nowPlayingArtwork.classList.add('rotating');
    };
    audioPlayer.onpause = () => {
        player.isPlaying = false;
        updatePlayButton();
        nowPlayingArtwork.classList.remove('rotating');
    };
    
    // Search
    let searchTimeout;
    searchInput.oninput = (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();
        
        if (query.length === 0) {
            loadSongs().then(() => renderCurrentView());
            return;
        }
        
        if (query.length < 2) return;
        
        searchTimeout = setTimeout(async () => {
            try {
                const response = await API.searchSongs(query);
                if (response.success) {
                    player.songs = response.data.songs.map(song => ({
                        id: song.id,
                        title: song.title,
                        artist: song.artist,
                        cover: song.cover_url,
                        audio: song.audio_url
                    }));
                    renderCurrentView();
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }, 500);
    };
}

// Play song by index
function playSong(index) {
    if (index < 0 || index >= player.songs.length) return;
    
    player.currentIndex = index;
    const song = player.songs[index];
    
    // Update UI
    nowPlayingTitle.textContent = song.title;
    nowPlayingArtist.textContent = song.artist;
    nowPlayingArtwork.innerHTML = `<img src="${song.cover}" alt="${song.title}">`;
    
    // Load and play audio
    audioPlayer.src = song.audio;
    audioPlayer.play();
    
    // Log play to backend
    API.logPlay(song.id);
}

// Toggle play/pause
function togglePlay() {
    if (!audioPlayer.src) {
        // Play first song if nothing is playing
        playSong(0);
        return;
    }
    
    if (player.isPlaying) {
        audioPlayer.pause();
    } else {
        audioPlayer.play();
    }
}

// Play previous song
function playPrevious() {
    let newIndex = player.currentIndex - 1;
    if (newIndex < 0) newIndex = player.songs.length - 1;
    playSong(newIndex);
}

// Play next song
function playNext() {
    let newIndex = player.currentIndex + 1;
    if (newIndex >= player.songs.length) newIndex = 0;
    playSong(newIndex);
}

// Toggle shuffle
function toggleShuffle() {
    player.isShuffled = !player.isShuffled;
    shuffleBtn.style.color = player.isShuffled ? '#32e84a' : '';
    
    if (player.isShuffled) {
        showNotification('Shuffle on', 'success');
    } else {
        showNotification('Shuffle off', 'info');
    }
}

// Toggle repeat
function toggleRepeat() {
    player.isRepeating = !player.isRepeating;
    repeatBtn.style.color = player.isRepeating ? '#32e84a' : '';
    
    if (player.isRepeating) {
        showNotification('Repeat on', 'success');
    } else {
        showNotification('Repeat off', 'info');
    }
}

// Handle song end
function handleSongEnd() {
    if (player.isRepeating) {
        audioPlayer.play();
    } else {
        playNext();
    }
}

// Seek in song
function seek(e) {
    const rect = progressBar.getBoundingClientRect();
    const pos = (e.clientX - rect.left) / rect.width;
    audioPlayer.currentTime = pos * audioPlayer.duration;
}

// Update progress bar
function updateProgress() {
    if (audioPlayer.duration) {
        const progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
        progressFill.style.width = progress + '%';
    }
}

// Update play button icon
function updatePlayButton() {
    const icon = playBtn.querySelector('i');
    icon.className = player.isPlaying ? 'fas fa-pause' : 'fas fa-play';
}

// Toggle favorite
async function toggleFavorite(songId) {
    try {
        if (player.favorites.has(songId)) {
            const response = await API.removeFavorite(songId);
            if (response.success) {
                player.favorites.delete(songId);
                showNotification('Removed from favorites', 'success');
            }
        } else {
            const response = await API.addFavorite(songId);
            if (response.success) {
                player.favorites.add(songId);
                showNotification('Added to favorites', 'success');
            }
        }
        renderCurrentView();
    } catch (error) {
        console.error('Error toggling favorite:', error);
        showNotification('Failed to update favorites', 'error');
    }
}

// Switch view
function switchView(view) {
    player.currentView = view;
    
    // Update nav buttons
    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Hide all views
    document.querySelectorAll('.view-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected view
    const viewMap = {
        'player': 'playerView',
        'library': 'libraryView',
        'favorites': 'favoritesView',
        'playlists': 'playlistsView'
    };
    
    document.getElementById(viewMap[view]).classList.add('active');
    renderCurrentView();
}

// Render current view
function renderCurrentView() {
    switch (player.currentView) {
        case 'player':
            renderRecommendedSongs();
            break;
        case 'library':
            renderLibrary();
            break;
        case 'favorites':
            renderFavorites();
            break;
        case 'playlists':
            renderPlaylistsView();
            break;
    }
}

// Render recommended songs (grid view)
function renderRecommendedSongs() {
    const container = document.getElementById('recommendedSongs');
    const songs = player.songs.slice(0, 12); // Show first 12
    
    const html = songs.map((song, index) => `
        <div class="song-card" onclick="playSong(${player.songs.indexOf(song)})" oncontextmenu="showSongContextMenu(event, ${JSON.stringify(song).replace(/"/g, '&quot;')})">
            <div class="song-card-img">
                <img src="${song.cover}" alt="${song.title}">
                <button class="song-card-play" onclick="event.stopPropagation(); playSong(${player.songs.indexOf(song)})">
                    <i class="fas fa-play"></i>
                </button>
            </div>
            <div class="song-card-actions">
                <button class="btn-favorite ${player.favorites.has(song.id) ? 'active' : ''}" 
                        onclick="event.stopPropagation(); toggleFavorite(${song.id})">
                    <i class="fas fa-heart"></i>
                </button>
                <button class="btn-favorite" 
                        onclick="event.stopPropagation(); showAddToPlaylistModal(${JSON.stringify(song).replace(/"/g, '&quot;')})">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <h3 class="song-card-title">${song.title}</h3>
            <p class="song-card-artist">${song.artist}</p>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Render library (list view)
function renderLibrary() {
    const container = document.getElementById('songsList');
    
    const html = player.songs.map((song, index) => `
        <div class="song-list-item ${index === player.currentIndex && player.isPlaying ? 'playing' : ''}" 
             onclick="playSong(${index})">
            <div class="song-list-number">${index + 1}</div>
            <div class="song-list-img">
                <img src="${song.cover}" alt="${song.title}">
            </div>
            <div class="song-list-info">
                <h4 class="song-list-title">${song.title}</h4>
                <p class="song-list-artist">${song.artist}</p>
            </div>
            <div class="song-list-actions">
                <button class="btn-favorite ${player.favorites.has(song.id) ? 'active' : ''}" 
                        onclick="event.stopPropagation(); toggleFavorite(${song.id})">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Render favorites
function renderFavorites() {
    const container = document.getElementById('favoritesSongs');
    const favoriteSongs = player.songs.filter(song => player.favorites.has(song.id));
    
    if (favoriteSongs.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-heart-broken"></i>
                <h3>No liked songs yet</h3>
                <p>Songs you like will appear here</p>
            </div>
        `;
        return;
    }
    
    const html = favoriteSongs.map(song => `
        <div class="song-card" onclick="playSong(${player.songs.indexOf(song)})">
            <div class="song-card-img">
                <img src="${song.cover}" alt="${song.title}">
                <button class="song-card-play">
                    <i class="fas fa-play"></i>
                </button>
            </div>
            <div class="song-card-actions">
                <button class="btn-favorite active" 
                        onclick="event.stopPropagation(); toggleFavorite(${song.id})">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            <h3 class="song-card-title">${song.title}</h3>
            <p class="song-card-artist">${song.artist}</p>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Render playlists view
function renderPlaylistsView() {
    const container = document.getElementById('playlistsGrid');
    
    if (player.playlists.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No playlists yet</h3>
                <p>Create your first playlist</p>
            </div>
        `;
        return;
    }
    
    const html = player.playlists.map(playlist => `
        <div class="stat-card" style="position: relative;">
            <h3>${playlist.name}</h3>
            <p class="stat-card-number">${playlist.song_count}</p>
            <p style="color: #b3b3b3; font-size: 13px; margin: 8px 0 0 0;">songs</p>
            <div style="display: flex; gap: 8px; margin-top: 12px;">
                <button class="btn-modal btn-modal-secondary" style="flex: 1; padding: 8px;" onclick="showEditPlaylistModal(${JSON.stringify(playlist).replace(/"/g, '&quot;')})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-modal btn-modal-secondary" style="flex: 1; padding: 8px;" onclick="deletePlaylist(${playlist.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Render sidebar playlists
function renderSidebarPlaylists() {
    const container = document.getElementById('sidebarPlaylists');
    
    if (player.playlists.length === 0) {
        container.innerHTML = '<div style="color: #b3b3b3; font-size: 13px; padding: 8px 16px;">No playlists yet</div>';
        return;
    }
    
    const html = player.playlists.map(playlist => `
        <div class="playlist-item">
            <i class="fas fa-music"></i>
            ${playlist.name}
        </div>
    `).join('');
    
    container.innerHTML = html;
}

// Create playlist modal
function showCreatePlaylistModal() {
    document.getElementById('createPlaylistModal').classList.add('active');
    document.getElementById('playlistNameInput').focus();
}

function closeCreatePlaylistModal() {
    document.getElementById('createPlaylistModal').classList.remove('active');
    document.getElementById('playlistNameInput').value = '';
    document.getElementById('playlistDescInput').value = '';
}

// Create playlist
async function createPlaylist() {
    const name = document.getElementById('playlistNameInput').value.trim();
    const description = document.getElementById('playlistDescInput').value.trim();
    
    if (!name) {
        showNotification('Please enter a playlist name', 'error');
        return;
    }
    
    try {
        const response = await API.createPlaylist(name, description);
        if (response.success) {
            showNotification('Playlist created!', 'success');
            closeCreatePlaylistModal();
            await loadPlaylists();
        } else {
            showNotification(response.message, 'error');
        }
    } catch (error) {
        console.error('Error creating playlist:', error);
        showNotification('Failed to create playlist', 'error');
    }
}

// Logout
async function logout() {
    if (confirm('Are you sure you want to logout?')) {
        await API.logout();
        window.location.href = '/login.php';
    }
}

// Show context menu for song
let contextMenuSong = null;

function showSongContextMenu(event, song) {
    event.preventDefault();
    event.stopPropagation();
    
    contextMenuSong = song;
    
    // Remove existing menu if any
    const existingMenu = document.querySelector('.context-menu');
    if (existingMenu) existingMenu.remove();
    
    // Create context menu
    const menu = document.createElement('div');
    menu.className = 'context-menu';
    menu.style.cssText = `
        position: fixed;
        left: ${event.clientX}px;
        top: ${event.clientY}px;
        background: #282828;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        padding: 4px 0;
        z-index: 10000;
        min-width: 200px;
    `;
    
    const menuItems = [
        { icon: 'plus', text: 'Add to Playlist', action: () => showAddToPlaylistModal(song) },
        { icon: 'heart', text: player.favorites.has(song.id) ? 'Remove from Favorites' : 'Add to Favorites', action: () => toggleFavorite(song.id) }
    ];
    
    // Add admin options
    if (window.isAdmin) {
        menuItems.push(
            { icon: 'edit', text: 'Edit Song', action: () => showEditSongModal(song) },
            { icon: 'trash', text: 'Delete Song', action: () => deleteSongConfirm(song) }
        );
    }
    
    menuItems.forEach(item => {
        const menuItem = document.createElement('div');
        menuItem.style.cssText = `
            padding: 12px 16px;
            color: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
        `;
        menuItem.innerHTML = `<i class="fas fa-${item.icon}"></i> ${item.text}`;
        menuItem.onmouseover = () => menuItem.style.background = '#3e3e3e';
        menuItem.onmouseout = () => menuItem.style.background = '';
        menuItem.onclick = () => {
            item.action();
            menu.remove();
        };
        menu.appendChild(menuItem);
    });
    
    document.body.appendChild(menu);
    
    // Close on click outside
    setTimeout(() => {
        document.addEventListener('click', function closeMenu() {
            menu.remove();
            document.removeEventListener('click', closeMenu);
        });
    }, 0);
}

// Show add to playlist modal
function showAddToPlaylistModal(song) {
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <h2>Add to Playlist</h2>
            <p style="color: #b3b3b3; margin-bottom: 20px;">${song.title} - ${song.artist}</p>
            <div id="playlistOptions" style="max-height: 300px; overflow-y: auto;">
                ${player.playlists.map(p => `
                    <div class="playlist-option" style="padding: 12px; background: #3e3e3e; border-radius: 4px; margin-bottom: 8px; cursor: pointer;" onclick="addToPlaylist(${p.id}, ${song.id})">
                        <i class="fas fa-music"></i> ${p.name} (${p.song_count} songs)
                    </div>
                `).join('')}
            </div>
            <div class="modal-buttons" style="margin-top: 20px;">
                <button class="btn-modal btn-modal-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Add song to playlist
async function addToPlaylist(playlistId, songId) {
    try {
        const response = await API.addSongToPlaylist(playlistId, songId);
        if (response.success) {
            showNotification('Added to playlist!', 'success');
            document.querySelector('.modal').remove();
            await loadPlaylists();
        } else {
            showNotification(response.message, 'error');
        }
    } catch (error) {
        console.error('Error adding to playlist:', error);
        showNotification('Failed to add to playlist', 'error');
    }
}

// Show edit song modal (admin only)
function showEditSongModal(song) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <h2>Edit Song</h2>
            <input type="text" id="editTitle" value="${song.title}" placeholder="Title">
            <input type="text" id="editArtist" value="${song.artist}" placeholder="Artist">
            <input type="text" id="editAlbum" value="${song.album || ''}" placeholder="Album">
            <input type="text" id="editGenre" value="${song.genre || ''}" placeholder="Genre">
            <div class="modal-buttons">
                <button class="btn-modal btn-modal-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                <button class="btn-modal btn-modal-primary" onclick="updateSong(${song.id})">Save</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Update song
async function updateSong(songId) {
    const title = document.getElementById('editTitle').value.trim();
    const artist = document.getElementById('editArtist').value.trim();
    const album = document.getElementById('editAlbum').value.trim();
    const genre = document.getElementById('editGenre').value.trim();
    
    if (!title || !artist) {
        showNotification('Title and artist are required', 'error');
        return;
    }
    
    try {
        const response = await API.updateSong(songId, title, artist, album, genre);
        if (response.success) {
            showNotification('Song updated!', 'success');
            document.querySelector('.modal').remove();
            await loadSongs();
            renderCurrentView();
        } else {
            showNotification(response.message, 'error');
        }
    } catch (error) {
        console.error('Error updating song:', error);
        showNotification('Failed to update song', 'error');
    }
}

// Delete song confirmation (admin only)
function deleteSongConfirm(song) {
    if (confirm(`Are you sure you want to delete "${song.title}"? This cannot be undone.`)) {
        deleteSongAction(song.id);
    }
}

// Delete song
async function deleteSongAction(songId) {
    try {
        const response = await API.deleteSong(songId);
        if (response.success) {
            showNotification('Song deleted', 'success');
            await loadSongs();
            renderCurrentView();
        } else {
            showNotification(response.message, 'error');
        }
    } catch (error) {
        console.error('Error deleting song:', error);
        showNotification('Failed to delete song', 'error');
    }
}

// Show edit playlist modal
function showEditPlaylistModal(playlist) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content">
            <h2>Edit Playlist</h2>
            <input type="text" id="editPlaylistName" value="${playlist.name}" placeholder="Playlist name">
            <input type="text" id="editPlaylistDesc" value="${playlist.description || ''}" placeholder="Description">
            <div class="modal-buttons">
                <button class="btn-modal btn-modal-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                <button class="btn-modal btn-modal-primary" onclick="updatePlaylist(${playlist.id})">Save</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Update playlist
async function updatePlaylist(playlistId) {
    const name = document.getElementById('editPlaylistName').value.trim();
    const description = document.getElementById('editPlaylistDesc').value.trim();
    
    if (!name) {
        showNotification('Playlist name is required', 'error');
        return;
    }
    
    try {
        const response = await API.updatePlaylist(playlistId, name, description);
        if (response.success) {
            showNotification('Playlist updated!', 'success');
            document.querySelector('.modal').remove();
            await loadPlaylists();
        } else {
            showNotification(response.message, 'error');
        }
    } catch (error) {
        console.error('Error updating playlist:', error);
        showNotification('Failed to update playlist', 'error');
    }
}

// Close modal on outside click
window.onclick = function(event) {
    const modal = document.getElementById('createPlaylistModal');
    if (event.target === modal) {
        closeCreatePlaylistModal();
    }
};

// Check if user is admin (set on page load from PHP)
window.isAdmin = false;

// Initialize when page loads
document.addEventListener('DOMContentLoaded', init);

