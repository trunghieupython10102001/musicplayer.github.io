# Music Player - JSP Migration

A music streaming platform built with JSP/Servlets, MySQL, and vanilla JavaScript, running on Docker with Tomcat and Nginx.

## Features

### MVP (Minimum Viable Product)
- 🔐 User Authentication (Register/Login/Password Reset)
- 🎵 Music Library with 78+ songs
- 📝 Custom Playlists
- ❤️ Favorites System
- 📊 Play History Tracking
- 🔍 Search Functionality
- 👨‍💼 Admin Panel for Song Management
- 📧 Email Notifications (Password Reset)

## Tech Stack

### Frontend
- HTML5, CSS3, JavaScript (Vanilla)
- Responsive Design
- AJAX/Fetch API

### Backend
- Java 17
- JSP / Jakarta Servlets on Tomcat 10
- Maven WAR packaging
- MySQL 8.0
- REST-style API with legacy `.php` route compatibility

### Infrastructure
- Docker & Docker Compose
- Nginx Web Server
- phpMyAdmin
- MailHog (Email Testing)

## Project Structure

```
musicplayer/
├── docker/
│   ├── mysql/                  # Database init script
│   ├── nginx/                  # Reverse proxy config
│   └── tomcat/                 # Tomcat image build
├── public/
│   └── assets/                 # Static frontend assets
├── src/
│   └── main/
│       ├── java/               # Servlets, services, config, DB access
│       ├── resources/          # Application properties
│       └── webapp/             # JSP views and web.xml
├── uploads/                    # User uploads
├── pom.xml                     # Maven build
└── docker-compose.yml          # Docker orchestration
```

## Getting Started

## Tai Lieu Tieng Viet

- Huong dan nhanh: `HUONG_DAN_NHANH_VIETNAM.md`
- Huong dan cai dat chi tiet: `HUONG_DAN_CAI_DAT_VIETNAM.md`

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
- MailHog (Email Testing): http://localhost:8025

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
3. Forgot password? Use `/forgot-password.php` to reset
4. Browse songs in the library
5. Create playlists
6. Add songs to favorites
7. View your play history

### For Admins
1. Login with admin credentials
2. Access admin panel at `/admin.php`
3. Upload new songs with metadata
4. Manage existing songs

## API Endpoints

The Java migration preserves the legacy `.php` route surface so the existing frontend JavaScript continues to work.

### Authentication
- `POST /api/auth/register.php` - Register new user
- `POST /api/auth/login.php` - Login
- `POST /api/auth/logout.php` - Logout
- `GET /api/auth/check.php` - Check session
- `POST /api/auth/forgot-password.php` - Request password reset
- `POST /api/auth/reset-password.php` - Reset password with token

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

### Restart Tomcat
```bash
docker-compose restart tomcat
```

### Rebuild containers
```bash
docker-compose up -d --build
```

### Access MySQL CLI
```bash
docker exec -it musicplayer_mysql mysql -u root -p
```

### Access Tomcat container
```bash
docker exec -it musicplayer_tomcat sh
```

## Development

### Database Schema
See `docker/mysql/init.sql` for complete schema

### Adding New Features
1. Create or update Java code in `src/main/java/`
2. Add or update JSP views in `src/main/webapp/WEB-INF/views/`
3. Add frontend logic in `public/assets/js/`
4. Update database schema in `docker/mysql/init.sql` if needed
5. Test through Docker

### File Upload Limits
- Max file size: 100MB
- Allowed formats: MP3, WAV, OGG
- Upload directory inside the deployed app: `/uploads/songs/`

## Troubleshooting

### Container won't start
```bash
docker-compose down
docker-compose up -d --build
```

### Database connection error
- Check if MySQL container is running: `docker ps`
- Check logs: `docker-compose logs mysql`

### JSP/Tomcat issue
- Rebuild the app image: `docker-compose build tomcat`
- Check logs: `docker-compose logs tomcat`

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

### Completed Features
- [x] Password reset token flow
- [x] JSP/Servlet migration with Tomcat runtime

### Future Features
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
