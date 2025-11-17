# Music Player MVP Plan

## Project Overview
Transform the static music player into a dynamic full-stack web application with user authentication, playlists, and favorites.

## MVP Scope (Minimum Viable Product)

### Core Features - Phase 1

#### 1. User Authentication
- Register new account
- Login/Logout
- Session management
- Protected routes

#### 2. Music Library
- Display all available songs from database
- Play songs from database
- Search songs by title or artist
- Basic player controls (play, pause, next, previous)

#### 3. User Features
- Create personal playlists
- Add/remove songs to playlists
- Mark songs as favorites
- View play history

#### 4. Basic Admin
- Simple admin login
- Upload new songs
- View song list

---

## Technology Stack

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling (keep existing design)
- **JavaScript (Vanilla)** - Interactivity

### Backend
- **PHP 8.2** - Server-side logic
- **MySQL 8.0** - Database
- **Nginx** - Web server

### Infrastructure
- **Docker** - Containerization
- **Docker Compose** - Multi-container orchestration

---

## Database Schema (MVP)

### Tables

1. **users**
   - Basic user information
   - Authentication credentials

2. **songs**
   - Song metadata
   - File paths

3. **playlists**
   - User-created playlists

4. **playlist_songs**
   - Many-to-many relationship

5. **favorites**
   - User favorite songs

6. **play_history**
   - Track listening history

---

## API Endpoints (MVP)

### Authentication
- `POST /api/auth/register.php` - Create new user
- `POST /api/auth/login.php` - Login user
- `POST /api/auth/logout.php` - Logout user
- `GET /api/auth/check.php` - Check if logged in

### Songs
- `GET /api/songs/list.php` - Get all songs
- `GET /api/songs/search.php?q=query` - Search songs
- `GET /api/songs/get.php?id=1` - Get single song
- `POST /api/songs/play.php` - Log play event

### Playlists
- `GET /api/playlists/list.php` - Get user playlists
- `POST /api/playlists/create.php` - Create playlist
- `DELETE /api/playlists/delete.php?id=1` - Delete playlist
- `POST /api/playlists/add-song.php` - Add song to playlist
- `DELETE /api/playlists/remove-song.php` - Remove song from playlist

### Favorites
- `GET /api/favorites/list.php` - Get user favorites
- `POST /api/favorites/add.php` - Add to favorites
- `DELETE /api/favorites/remove.php?id=1` - Remove from favorites

### Admin
- `POST /api/admin/upload.php` - Upload new song

---

## Project Structure

```
musicplayer/
├── docker-compose.yml              # Docker orchestration
├── docker/
│   ├── nginx/
│   │   └── default.conf            # Nginx configuration
│   ├── php/
│   │   └── Dockerfile              # PHP-FPM container
│   └── mysql/
│       └── init.sql                # Database initialization
├── public/                         # Web root
│   ├── index.php                   # Main player page
│   ├── login.php                   # Login page
│   ├── register.php                # Registration page
│   ├── dashboard.php               # User dashboard
│   ├── admin.php                   # Admin panel
│   ├── api/                        # API endpoints
│   │   ├── auth/
│   │   │   ├── register.php
│   │   │   ├── login.php
│   │   │   ├── logout.php
│   │   │   └── check.php
│   │   ├── songs/
│   │   │   ├── list.php
│   │   │   ├── search.php
│   │   │   ├── get.php
│   │   │   └── play.php
│   │   ├── playlists/
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── delete.php
│   │   │   ├── add-song.php
│   │   │   └── remove-song.php
│   │   ├── favorites/
│   │   │   ├── list.php
│   │   │   ├── add.php
│   │   │   └── remove.php
│   │   └── admin/
│   │       └── upload.php
│   └── assets/
│       ├── css/
│       │   ├── style.css           # Existing styles
│       │   └── auth.css            # Auth page styles
│       ├── js/
│       │   ├── app.js              # Existing player
│       │   ├── api.js              # API helper functions
│       │   ├── auth.js             # Authentication logic
│       │   └── player.js           # Enhanced player
│       ├── audio/                  # Music files
│       └── img/                    # Album covers
├── includes/
│   ├── config.php                  # Configuration
│   ├── database.php                # Database connection
│   ├── auth.php                    # Auth helpers
│   └── functions.php               # Utility functions
├── uploads/                        # User uploads
│   ├── songs/
│   └── covers/
└── README.md                       # Documentation
```

---

## Implementation Steps

### Step 1: Docker Setup ✓
- Create docker-compose.yml
- Configure Nginx
- Set up PHP-FPM
- Configure MySQL

### Step 2: Database Setup ✓
- Create database schema
- Insert existing songs data
- Create admin user

### Step 3: Core Backend ✓
- Database connection
- Authentication system
- Session management
- API structure

### Step 4: Frontend Integration
- Update existing player to use API
- Create login/register pages
- Add authentication checks
- Implement playlist UI

### Step 5: Testing & Deployment
- Test all features
- Fix bugs
- Documentation
- Deploy

---

## MVP Features NOT Included (Future)

- Password reset
- Email verification
- Social features (following users)
- Comments/reviews
- Advanced search filters
- Audio equalizer
- Lyrics display
- Mobile app
- Real-time updates
- Analytics dashboard
- Multiple file format support

---

## Success Criteria

MVP is complete when:
1. Users can register and login
2. Users can browse and play songs
3. Users can create playlists
4. Users can favorite songs
5. Admin can upload new songs
6. Everything runs in Docker
7. Data persists in MySQL

---

## Timeline Estimate

- **Day 1**: Docker setup + Database design
- **Day 2**: Backend API (auth + songs)
- **Day 3**: Backend API (playlists + favorites)
- **Day 4**: Frontend integration
- **Day 5**: Testing + Polish

Total: ~5 days for MVP

---

## Security Notes

- Use prepared statements (prevent SQL injection)
- Hash passwords with bcrypt
- Validate all inputs
- Use HTTPS in production
- Sanitize file uploads
- Implement CSRF protection
- Set secure session cookies

---

## Getting Started

```bash
# Clone repository
git clone <repo-url>
cd musicplayer

# Start Docker containers
docker-compose up -d

# Access application
http://localhost:8080

# Access phpMyAdmin
http://localhost:8081
```

---

**Status**: Ready to implement
**Next Step**: Create Docker configuration files

