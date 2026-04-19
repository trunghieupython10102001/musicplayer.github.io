# Huong Dan Nhanh

Tai lieu nay huong dan khoi dong nhanh du an `Music Player` phien ban `JSP/Servlet + Tomcat` tren may local bang Docker.

## 1. Dieu kien can

- Da cai `Docker Desktop`
- Docker dang chay
- Co the dung `docker compose`

Kiem tra nhanh:

```bash
docker --version
docker compose version
```

## 2. Khoi dong du an

Trong thu muc du an, chay:

```bash
docker compose up -d
```

Lan dau tien co the mat 1-3 phut de build image va khoi tao database.

## 3. Kiem tra container

```bash
docker compose ps
```

Ban se thay cac service chinh dang chay:

- `musicplayer_nginx`
- `musicplayer_tomcat`
- `musicplayer_mysql`
- `musicplayer_phpmyadmin`
- `musicplayer_mailhog`

## 4. Truy cap ung dung

- Ung dung chinh: `http://localhost:8080`
- Dang nhap: `http://localhost:8080/login.php`
- Trang admin: `http://localhost:8080/admin.php`
- phpMyAdmin: `http://localhost:8081`
- MailHog: `http://localhost:8025`

## 5. Tai khoan mac dinh

Tai khoan admin:

- Username: `admin`
- Password: `admin123`

## 6. Kiem tra nhanh cac chuc nang

Sau khi dang nhap bang admin, ban co the test nhanh:

1. Mo `http://localhost:8080/index.php`
2. Chon mot bai hat de phat nhac
3. Thu tim kiem bai hat
4. Them bai hat vao `Liked Songs`
5. Mo `http://localhost:8080/admin.php`
6. Upload mot bai hat moi
7. Neu quen mat khau, thu `Forgot password` va xem email tai MailHog

## 7. Lenh hay dung

### Xem log

```bash
docker compose logs -f
```

### Xem log rieng Tomcat

```bash
docker compose logs -f tomcat
```

### Dung he thong

```bash
docker compose down
```

### Build lai he thong

```bash
docker compose up -d --build
```

## 8. Neu gap loi

### Trang khong vao duoc

Dam bao ban dang mo dung cong:

- Dung: `http://localhost:8080`
- Khong dung: `http://localhost`

### Email quen mat khau khong thay

Mo:

- `http://localhost:8025`

Day la giao dien MailHog de xem email test.

### Muon reset toan bo database

```bash
docker compose down -v
docker compose up -d
```

## 9. Ghi chu

- Du an hien tai da bo hoan toan implementation PHP cu
- Runtime hien tai la `Nginx + Tomcat + JSP/Servlet + MySQL`
- Cac route dang `*.php` van duoc giu lai de frontend cu tiep tuc hoat dong
