# Music Player MVP - Setup Guide

## Quick Start

This guide will help you get the Music Player application running on your local machine using Docker.

---

## Prerequisites

Before starting, ensure you have:

1. **Docker Desktop** installed and running
   - Download from: https://www.docker.com/products/docker-desktop
   - Minimum version: 20.10+

2. **System Requirements**
   - 4GB RAM minimum (8GB recommended)
   - 5GB free disk space
   - macOS, Windows, or Linux

---

## Installation Steps

### Step 1: Verify Docker Installation

```bash
# Check Docker is installed
docker --version

# Check Docker Compose is installed
docker-compose --version
```

### Step 2: Navigate to Project Directory

```bash
cd /Users/harry/Workspace/musicplayer.github.io
```

### Step 3: Start Docker Containers

```bash
# Start all containers in detached mode
docker-compose up -d
```

This will:
- Build the PHP container
- Start Nginx web server
- Start MySQL database
- Start phpMyAdmin
- Create necessary networks and volumes

**First time setup takes 2-3 minutes**

### Step 4: Monitor Container Startup

```bash
# Watch the logs
docker-compose logs -f

# Check container status
docker ps
```

You should see 4 containers running:
- `musicplayer_nginx`
- `musicplayer_php`
- `musicplayer_mysql`
- `musicplayer_phpmyadmin`

### Step 5: Wait for Database Initialization

The database initialization takes about 30-60 seconds. Check the logs:

```bash
docker-compose logs mysql | grep "ready for connections"
```

When you see this message twice, the database is ready.

---

## Accessing the Application

### Main Application
- URL: http://localhost:8080
- This is where users will interact with the music player

### phpMyAdmin (Database Management)
- URL: http://localhost:8081
- Server: `mysql`
- Username: `root`
- Password: `root_password`

---

## Default Accounts

### Admin Account
- Username: `admin`
- Password: `admin123`
- Email: `admin@musicplayer.com`

Use this account to:
- Upload new songs
- Access admin features
- Manage the music library

---

## Testing the Setup

### 1. Test API Endpoints

```bash
# Check if songs are loaded
curl http://localhost:8080/api/songs/list.php

# Check authentication endpoint
curl http://localhost:8080/api/auth/check.php
```

### 2. Test Admin Login

```bash
# Login as admin
curl -X POST http://localhost:8080/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"login":"admin","password":"admin123"}'
```

### 3. Verify Database

1. Open http://localhost:8081 (phpMyAdmin)
2. Login with root credentials
3. Select `musicplayer` database
4. Check tables:
   - `users` should have 1 admin user
   - `songs` should have 78 songs
   - Other tables should be empty initially

---

## Project Structure

```
musicplayer.github.io/
├── docker/                    # Docker configuration
│   ├── nginx/                 # Nginx web server config
│   ├── php/                   # PHP-FPM Dockerfile
│   └── mysql/                 # Database initialization
├── public/                    # Web root (accessible via browser)
│   ├── api/                   # REST API endpoints
│   │   ├── auth/              # Authentication
│   │   ├── songs/             # Song management
│   │   ├── playlists/         # Playlist management
│   │   ├── favorites/         # Favorites
│   │   └── admin/             # Admin functions
│   └── assets/                # Static files (CSS, JS, images, audio)
├── includes/                  # PHP includes (not web accessible)
│   ├── config.php             # Configuration
│   ├── database.php           # Database connection
│   ├── auth.php               # Authentication helpers
│   └── functions.php          # Utility functions
├── uploads/                   # User-uploaded files
└── docker-compose.yml         # Docker orchestration
```

---

## Common Commands

### Start/Stop Containers

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f php
```

### Database Operations

```bash
# Access MySQL CLI
docker exec -it musicplayer_mysql mysql -u root -p
# Password: root_password

# Backup database
docker exec musicplayer_mysql mysqldump -u root -proot_password musicplayer > backup.sql

# Restore database
docker exec -i musicplayer_mysql mysql -u root -proot_password musicplayer < backup.sql

# Reset database (delete all data and reinitialize)
docker-compose down -v
docker-compose up -d
```

### PHP Container

```bash
# Access PHP container shell
docker exec -it musicplayer_php sh

# View PHP info
docker exec musicplayer_php php -i

# Check PHP modules
docker exec musicplayer_php php -m
```

---

## Troubleshooting

### Port Already in Use

If you see errors about ports 8080, 8081, or 3306 being in use:

```bash
# Find what's using the port
lsof -i :8080

# Change ports in docker-compose.yml
# Edit the 'ports' section for each service
```

### Database Connection Errors

```bash
# Check if MySQL is running
docker ps | grep mysql

# Check MySQL logs
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql
```

### Permission Errors

```bash
# Fix upload directory permissions
chmod -R 777 uploads/

# Fix log directory permissions
mkdir -p logs && chmod -R 777 logs/
```

### Containers Won't Start

```bash
# Stop everything
docker-compose down

# Remove volumes
docker-compose down -v

# Rebuild from scratch
docker-compose up -d --build
```

### Clear Everything and Start Fresh

```bash
# Stop containers
docker-compose down

# Remove volumes (deletes database data)
docker-compose down -v

# Remove images
docker rmi musicplayer-php musicplayer-nginx

# Start fresh
docker-compose up -d --build
```

---

## API Testing

### Using cURL

```bash
# Register new user
curl -X POST http://localhost:8080/api/auth/register.php \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","email":"test@example.com","password":"password123"}'

# Login
curl -X POST http://localhost:8080/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"login":"testuser","password":"password123"}'

# Get all songs
curl http://localhost:8080/api/songs/list.php

# Search songs
curl "http://localhost:8080/api/songs/search.php?q=hello"
```

### Using Browser

Open these URLs in your browser:
- http://localhost:8080/api/songs/list.php
- http://localhost:8080/api/songs/search.php?q=adele
- http://localhost:8080/api/auth/check.php

---

## Development Workflow

### Making Changes

1. **Edit PHP files** in `public/` or `includes/`
2. **No restart needed** - changes are reflected immediately
3. **Clear browser cache** if CSS/JS doesn't update

### Adding New Songs

1. Login as admin
2. Use the admin upload API endpoint
3. Or manually add to database via phpMyAdmin

### Database Schema Changes

1. Edit `docker/mysql/init.sql`
2. Reset database: `docker-compose down -v && docker-compose up -d`

---

## Performance Optimization

### For Production

1. **Disable error display** in `includes/config.php`
2. **Enable caching** in PHP
3. **Use production Nginx config**
4. **Enable HTTPS**
5. **Set secure session cookies**
6. **Change all default passwords**

---

## Next Steps

After successful setup:

1. ✅ Create a regular user account
2. ✅ Test login/logout functionality
3. ✅ Browse the song library
4. ✅ Create a playlist
5. ✅ Add songs to favorites
6. ✅ Search for songs
7. ✅ Test admin upload (if admin)

---

## Getting Help

If you encounter issues:

1. Check the logs: `docker-compose logs -f`
2. Verify containers are running: `docker ps`
3. Check database connection in phpMyAdmin
4. Review error logs in `logs/app.log`
5. Restart containers: `docker-compose restart`

---

## Security Notes

⚠️ **Important**: This is a development setup

For production deployment:
- Change all default passwords
- Use environment variables for secrets
- Enable HTTPS with SSL certificates
- Implement rate limiting
- Add CSRF protection to forms
- Validate and sanitize all inputs
- Use secure session configuration
- Regular security updates

---

**Setup complete! Enjoy your music player! 🎵**

