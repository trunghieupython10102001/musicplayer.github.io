/**
 * API Helper Module
 * 
 * Provides easy-to-use functions for calling backend API endpoints
 */

const API = {
    baseURL: '/api',
    
    /**
     * Make a fetch request to API
     * 
     * @param {string} endpoint - API endpoint
     * @param {object} options - Fetch options
     * @returns {Promise} Response data
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const token = localStorage.getItem('authToken');
        const isFormData = options.body instanceof FormData;
        
        const defaultOptions = {
            headers: {
                ...(token ? { 'Authorization': `Bearer ${token}` } : {})
            },
            credentials: 'include' // Include cookies for session
        };

        if (!isFormData) {
            defaultOptions.headers['Content-Type'] = 'application/json';
        }
        
        const config = { ...defaultOptions, ...options };
        
        // Merge headers if provided in options
        if (options.headers) {
            config.headers = { ...defaultOptions.headers, ...options.headers };
        }
        
        try {
            const response = await fetch(url, config);
            
            // Handle 401 Unauthorized
            if (response.status === 401) {
                // If we are not on the login page, and it's not a login attempt
                if (!window.location.pathname.includes('/login') && !endpoint.includes('/login')) {
                    localStorage.removeItem('authToken');
                    // window.location.href = '/login';
                }
            }
            
            const data = await response.json();
            
            return {
                success: data.success,
                message: data.message,
                data: data.data || {},
                status: response.status
            };
        } catch (error) {
            console.error('API request failed:', error);
            return {
                success: false,
                message: 'Network error occurred',
                data: {},
                status: 0
            };
        }
    },
    
    // ===================
    // Authentication APIs
    // ===================
    
    /**
     * Register new user
     */
    async register(username, email, password) {
        return await this.request('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ username, email, password })
        });
    },
    
    /**
     * Login user
     */
    async login(login, password) {
        return await this.request('/auth/login', {
            method: 'POST',
            body: JSON.stringify({ login, password })
        });
    },
    
    /**
     * Logout user
     */
    async logout() {
        localStorage.removeItem('authToken');
        return await this.request('/auth/logout', {
            method: 'POST'
        });
    },
    
    /**
     * Check if user is logged in
     */
    async checkAuth() {
        return await this.request('/auth/check');
    },
    
    // =============
    // Songs APIs
    // =============
    
    /**
     * Get all songs
     */
    async getSongs(page = 1, limit = 20, genre = '') {
        let query = `?page=${page}&limit=${limit}`;
        if (genre) query += `&genre=${encodeURIComponent(genre)}`;
        
        return await this.request(`/songs/list${query}`);
    },
    
    /**
     * Search songs
     */
    async searchSongs(query, page = 1, limit = 20) {
        const params = `?q=${encodeURIComponent(query)}&page=${page}&limit=${limit}`;
        return await this.request(`/songs/search${params}`);
    },
    
    /**
     * Get single song
     */
    async getSong(songId) {
        return await this.request(`/songs/get?id=${songId}`);
    },
    
    /**
     * Update song (admin only)
     */
    async updateSong(songId, title, artist, album = '', genre = '', releaseYear = null) {
        return await this.request('/songs/update', {
            method: 'PUT',
            body: JSON.stringify({ 
                id: songId, 
                title, 
                artist, 
                album, 
                genre, 
                release_year: releaseYear 
            })
        });
    },
    
    /**
     * Delete song (admin only)
     */
    async deleteSong(songId) {
        return await this.request('/songs/delete', {
            method: 'DELETE',
            body: JSON.stringify({ id: songId })
        });
    },
    
    /**
     * Log song play
     */
    async logPlay(songId, durationPlayed = null) {
        return await this.request('/songs/play', {
            method: 'POST',
            body: JSON.stringify({ song_id: songId, duration_played: durationPlayed })
        });
    },
    
    // ================
    // Playlists APIs
    // ================
    
    /**
     * Get user playlists
     */
    async getPlaylists() {
        return await this.request('/playlists/list');
    },
    
    /**
     * Get playlist with songs
     */
    async getPlaylist(playlistId) {
        return await this.request(`/playlists/get?id=${playlistId}`);
    },
    
    /**
     * Create new playlist
     */
    async createPlaylist(name, description = '', isPublic = false) {
        return await this.request('/playlists/create', {
            method: 'POST',
            body: JSON.stringify({ name, description, is_public: isPublic })
        });
    },
    
    /**
     * Update playlist
     */
    async updatePlaylist(playlistId, name, description = '') {
        return await this.request('/playlists/update', {
            method: 'PUT',
            body: JSON.stringify({ id: playlistId, name, description })
        });
    },
    
    /**
     * Delete playlist
     */
    async deletePlaylist(playlistId) {
        return await this.request('/playlists/delete', {
            method: 'DELETE',
            body: JSON.stringify({ id: playlistId })
        });
    },
    
    /**
     * Add song to playlist
     */
    async addSongToPlaylist(playlistId, songId) {
        return await this.request('/playlists/add-song', {
            method: 'POST',
            body: JSON.stringify({ playlist_id: playlistId, song_id: songId })
        });
    },
    
    /**
     * Remove song from playlist
     */
    async removeSongFromPlaylist(playlistId, songId) {
        return await this.request('/playlists/remove-song', {
            method: 'DELETE',
            body: JSON.stringify({ playlist_id: playlistId, song_id: songId })
        });
    },
    
    // ===============
    // Favorites APIs
    // ===============
    
    /**
     * Get user favorites
     */
    async getFavorites() {
        return await this.request('/favorites/list');
    },
    
    /**
     * Add song to favorites
     */
    async addFavorite(songId) {
        return await this.request('/favorites/add', {
            method: 'POST',
            body: JSON.stringify({ song_id: songId })
        });
    },
    
    /**
     * Remove song from favorites
     */
    async removeFavorite(songId) {
        return await this.request('/favorites/remove', {
            method: 'DELETE',
            body: JSON.stringify({ song_id: songId })
        });
    },
    
    // ============
    // Admin APIs
    // ============
    
    /**
     * Upload new song (admin only)
     */
    async uploadSong(formData) {
        return await this.request('/admin/upload', {
            method: 'POST',
            body: formData
        });
    },

    async getAdminStats() {
        return await this.request('/admin/stats');
    },

    async getRecentActivity(limit = 10) {
        return await this.request(`/admin/activity?limit=${limit}`);
    },

    async getMostPlayed(limit = 5) {
        return await this.request(`/stats/most-played?limit=${limit}`);
    },

    async getMostLiked(limit = 5) {
        return await this.request(`/stats/most-liked?limit=${limit}`);
    },

    /**
     * Get all albums
     */
    async getAlbums() {
        return await this.request('/albums/list');
    },

    /**
     * Get songs for a specific album
     */
    async getAlbumSongs(albumName) {
        return await this.request(`/albums/songs?name=${encodeURIComponent(albumName)}`);
    }
};

// Helper function for showing notifications
function showNotification(message, type = 'info') {
    // Check if notification element exists
    let notification = document.getElementById('notification');
    
    if (!notification) {
        // Create notification element if it doesn't exist
        notification = document.createElement('div');
        notification.id = 'notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            z-index: 10000;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(notification);
    }
    
    // Set background color based on type
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        info: '#17a2b8',
        warning: '#ffc107'
    };
    
    notification.style.background = colors[type] || colors.info;
    notification.textContent = message;
    notification.style.display = 'block';
    
    // Hide after 3 seconds
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API, showNotification };
}
