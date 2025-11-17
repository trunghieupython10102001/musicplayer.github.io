# 🚀 Quick Start Guide

Get your music player running in **3 simple steps**!

---

## Step 1: Start Docker

```bash
cd /Users/harry/Workspace/musicplayer.github.io
docker-compose up -d
```

**Wait 1-2 minutes** for initialization (first time only).

---

## Step 2: Verify It's Running

Check container status:
```bash
docker ps
```

You should see 4 containers running:
- `musicplayer_nginx`
- `musicplayer_php`
- `musicplayer_mysql`
- `musicplayer_phpmyadmin`

---

## Step 3: Open in Browser

### Login as Admin
**URL:** http://localhost:8080/login.php

**Credentials:**
- Username: `admin`
- Password: `admin123`

### Or Create New Account
**URL:** http://localhost:8080/register.php

---

## 🎵 What You Can Do

### Main Player
http://localhost:8080/index.php
- Browse 78 songs
- Play music
- Search songs
- Add to favorites
- Create playlists

### Dashboard
http://localhost:8080/dashboard.php
- View your playlists
- Manage favorites
- Create new playlists

### Admin Panel (Admin Only)
http://localhost:8080/admin.php
- Upload new songs
- View statistics
- Manage library

### Database GUI
http://localhost:8081
- Server: `mysql`
- Username: `root`
- Password: `root_password`

---

## 🛑 Stop Everything

```bash
docker-compose down
```

---

## 🔄 Restart

```bash
docker-compose restart
```

---

## 🆘 Having Issues?

### Songs not loading?
```bash
# Check MySQL logs
docker-compose logs mysql

# Wait for: "ready for connections"
```

### Port already in use?
```bash
# Stop other services on port 8080
lsof -i :8080
```

### Need to reset everything?
```bash
docker-compose down -v
docker-compose up -d
```

---

## 📚 More Documentation

- **Setup Guide**: `SETUP_GUIDE.md` - Detailed setup instructions
- **MVP Plan**: `MVP_PLAN.md` - Feature specifications
- **Project Summary**: `PROJECT_SUMMARY.md` - What was built
- **Complete Guide**: `IMPLEMENTATION_COMPLETE.md` - Full details

---

## ✅ Quick Test

After starting:
1. Open http://localhost:8080/login.php
2. Login with `admin` / `admin123`
3. Click on any song to play
4. Use search bar to find songs
5. Click heart icon to favorite

---

**That's it! You're ready to rock! 🎸**

