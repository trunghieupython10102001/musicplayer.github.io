# Music Player - Full Stack Web Application

A modern music streaming platform built with PHP, MySQL, and vanilla JavaScript, running on Docker.

## Features

### MVP (Minimum Viable Product)
- 🔐 User Authentication (Register/Login)
- 🎵 Music Library with 78+ songs
- 📝 Custom Playlists
- ❤️ Favorites System
- 📊 Play History Tracking
- 🔍 Search Functionality
- 👨‍💼 Admin Panel for Song Management

## Tech Stack

### Frontend
- HTML5, CSS3, JavaScript (Vanilla)
- Responsive Design
- AJAX/Fetch API

### Backend
- PHP 8.2 (PHP-FPM)
- MySQL 8.0
- RESTful API

### Infrastructure
- Docker & Docker Compose
- Nginx Web Server
- phpMyAdmin

## Project Structure

```
musicplayer/
├── docker/                     # Docker configuration
│   ├── nginx/                  # Nginx config
│   ├── php/                    # PHP Dockerfile
│   └── mysql/                  # Database init script
├── public/                     # Web root
│   ├── api/                    # API endpoints
│   ├── assets/                 # Static files
│   └── *.php                   # Web pages
├── includes/                   # PHP includes
├── uploads/                    # User uploads
└── docker-compose.yml          # Docker orchestration
```

## Getting Started

### Prerequisites
- Docker Desktop installed
- Docker Compose
- 2GB free disk space

### Installation

1. **Clone the repository**
```bash
git clone <repo-url>
cd musicplayer
```

2. **Start Docker containers**
```bash
docker-compose up -d
```

3. **Wait for initialization** (first time takes 1-2 minutes)
```bash
docker-compose logs -f
```

4. **Access the application**
- Music Player: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### Default Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`
- Email: `admin@musicplayer.com`

**Database (phpMyAdmin):**
- Server: `mysql`
- Username: `root`
- Password: `root_password`

## Usage

### For Users
1. Register a new account at `/register.php`
2. Login at `/login.php`
3. Browse songs in the library
4. Create playlists
5. Add songs to favorites
6. View your play history

### For Admins
1. Login with admin credentials
2. Access admin panel at `/admin.php`
3. Upload new songs with metadata
4. Manage existing songs

## API Endpoints

### Authentication
- `POST /api/auth/register.php` - Register new user
- `POST /api/auth/login.php` - Login
- `POST /api/auth/logout.php` - Logout
- `GET /api/auth/check.php` - Check session

### Songs
- `GET /api/songs/list.php` - List all songs
- `GET /api/songs/search.php?q=query` - Search songs
- `GET /api/songs/get.php?id=1` - Get song details
- `POST /api/songs/play.php` - Log play event

### Playlists
- `GET /api/playlists/list.php` - Get user playlists
- `POST /api/playlists/create.php` - Create playlist
- `DELETE /api/playlists/delete.php` - Delete playlist
- `POST /api/playlists/add-song.php` - Add song to playlist
- `DELETE /api/playlists/remove-song.php` - Remove song

### Favorites
- `GET /api/favorites/list.php` - Get favorites
- `POST /api/favorites/add.php` - Add to favorites
- `DELETE /api/favorites/remove.php` - Remove from favorites

## Docker Commands

### Start containers
```bash
docker-compose up -d
```

### Stop containers
```bash
docker-compose down
```

### View logs
```bash
docker-compose logs -f
```

### Restart specific service
```bash
docker-compose restart php
```

### Rebuild containers
```bash
docker-compose up -d --build
```

### Access MySQL CLI
```bash
docker exec -it musicplayer_mysql mysql -u root -p
```

### Access PHP container
```bash
docker exec -it musicplayer_php sh
```

## Development

### Database Schema
See `docker/mysql/init.sql` for complete schema

### Adding New Features
1. Create API endpoint in `/public/api/`
2. Add frontend logic in `/public/assets/js/`
3. Update database if needed
4. Test thoroughly

### File Upload Limits
- Max file size: 100MB
- Allowed formats: MP3, WAV, OGG
- Upload directory: `/uploads/songs/`

## Troubleshooting

### Container won't start
```bash
docker-compose down
docker-compose up -d --build
```

### Database connection error
- Check if MySQL container is running: `docker ps`
- Check logs: `docker-compose logs mysql`

### Permission issues
```bash
chmod -R 777 uploads/
```

### Reset database
```bash
docker-compose down -v
docker-compose up -d
```

## Security Notes

⚠️ **Important**: This is a development setup. For production:

1. Change all default passwords
2. Use environment variables for sensitive data
3. Enable HTTPS with SSL certificates
4. Implement rate limiting
5. Add CSRF protection
6. Validate and sanitize all inputs
7. Use secure session configuration

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## License

This project is open source and available under the MIT License.

## Roadmap

### Future Features
- [ ] Password reset functionality
- [ ] Email verification
- [ ] Social features (follow users)
- [ ] Comments and ratings
- [ ] Advanced search filters
- [ ] Lyrics display
- [ ] Audio equalizer
- [ ] Mobile app (PWA)
- [ ] Real-time notifications
- [ ] Analytics dashboard

## Support

For issues and questions:
- Open an issue on GitHub
- Check existing documentation
- Review API endpoints

---

**Enjoy your music! 🎵**

