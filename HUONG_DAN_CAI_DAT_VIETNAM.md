# Huong Dan Cai Dat Chi Tiet

Tai lieu nay mo ta cach cai dat, khoi dong, test va xu ly su co cho du an `Music Player` sau khi da migrate sang `JSP/Servlet`.

## Tong quan kien truc

He thong hien tai su dung:

- `Nginx` lam reverse proxy
- `Tomcat 10` de chay `JSP/Servlet`
- `MySQL 8` de luu du lieu
- `MailHog` de test email reset password
- `phpMyAdmin` de xem va quan ly database

Muc tieu cua ban cai dat local:

1. Khoi dong duoc stack bang Docker
2. Dang nhap duoc vao he thong
3. Phat duoc nhac
4. Upload duoc bai hat moi bang admin
5. Nhan duoc email reset password trong MailHog

## Yeu cau he thong

- Docker Desktop
- Toi thieu 4GB RAM trong luc chay Docker
- Khoang 5GB trong o dia trong

Kiem tra:

```bash
docker --version
docker compose version
```

## Cau truc thu muc hien tai

```text
musicplayer.github.io/
├── docker/
│   ├── mysql/                  # Script khoi tao schema va seed data
│   ├── nginx/                  # Cau hinh reverse proxy
│   └── tomcat/                 # Dockerfile build ung dung Java
├── public/
│   └── assets/                 # CSS, JS, image, audio mac dinh
├── src/
│   └── main/
│       ├── java/               # Servlet, service, config, DB
│       ├── resources/          # app.properties
│       └── webapp/             # JSP views, WEB-INF/web.xml
├── uploads/                    # File upload thuc te duoc Nginx phuc vu
├── pom.xml                     # Maven build file
└── docker-compose.yml          # Khai bao service
```

## Cach khoi dong

### Buoc 1: di vao thu muc du an

```bash
cd /Users/harry/Workspace/musicplayer.github.io
```

### Buoc 2: start stack

```bash
docker compose up -d
```

### Buoc 3: kiem tra service

```bash
docker compose ps
```

Ban can thay cac service sau o trang thai `Up`:

- `nginx`
- `tomcat`
- `mysql`
- `phpmyadmin`
- `mailhog`

## URL can nho

- App: `http://localhost:8080`
- Login: `http://localhost:8080/login`
- Index: `http://localhost:8080/index`
- Dashboard: `http://localhost:8080/dashboard`
- Admin: `http://localhost:8080/admin`
- phpMyAdmin: `http://localhost:8081`
- MailHog: `http://localhost:8025`

Luu y quan trong:

- Luon dung `http://localhost:8080`
- Khong bo mat `:8080`

## Tai khoan va test mac dinh

### Admin

- Username: `admin`
- Password: `admin123`

### Database

- Host trong phpMyAdmin: `mysql`
- User: `root`
- Password: `root_password`

## Cach test nhanh sau khi cai dat

### Test 1: dang nhap

1. Mo `http://localhost:8080/login?demo=1`
2. Dang nhap bang admin
3. Ban phai vao duoc `index`

### Test 2: phat nhac

1. Chon mot bai hat trong danh sach
2. Kiem tra cover hien thi
3. Kiem tra audio phat duoc

### Test 3: favorites

1. Them bai hat vao `Liked Songs`
2. Refresh trang
3. Bai hat van con trong danh sach yeu thich

### Test 4: admin upload

1. Mo `http://localhost:8080/admin`
2. Upload file mp3 va cover image
3. Bai hat moi phai:
   - hien cover
   - play duoc
   - metadata hien dung, ke ca tieng Viet

### Test 5: reset password

1. Chon `Forgot password`
2. Nhap email tai khoan ton tai
3. Mo `http://localhost:8025`
4. Mo email moi nhat
5. Bam link reset

## Cac lenh hay dung

### Xem log toan bo

```bash
docker compose logs -f
```

### Xem log Tomcat

```bash
docker compose logs -f tomcat
```

### Xem log Nginx

```bash
docker compose logs -f nginx
```

### Build lai ung dung Java

```bash
docker compose build tomcat
docker compose up -d --force-recreate tomcat nginx
```

### Stop stack

```bash
docker compose down
```

### Xoa du lieu DB va tao lai tu dau

```bash
docker compose down -v
docker compose up -d
```

## Giai thich mot so thanh phan quan trong

### Nginx

- nghe cong `8080` tren may local
- proxy request dong sang Tomcat
- phuc vu file tinh trong `public/assets`
- phuc vu file upload trong `uploads/`

### Tomcat

- chay app Java/JSP
- xu ly page routes nhu `login`, `index`, `admin`
- xu ly API routes nhu `/api/auth/login`

### Uploads

File upload moi se duoc luu trong:

- `uploads/songs/`
- `uploads/covers/`

Nginx se phuc vu cac file nay qua URL:

- `/uploads/songs/...`
- `/uploads/covers/...`

### MailHog

- dung de nhan email test locally
- email reset password se xuat hien tai `http://localhost:8025`

## Xu ly su co thuong gap

### 1. Khong vao duoc app

Kiem tra:

```bash
docker compose ps
```

Neu `nginx` khong chay, xem log:

```bash
docker compose logs nginx
```

### 2. Khong dang nhap duoc admin

- Dung dung URL: `http://localhost:8080`
- Khong mix `localhost` voi `127.0.0.1`
- Thu xoa cookie trinh duyet roi dang nhap lai

### 3. Admin upload thanh cong nhung khong play duoc

Trong phien ban hien tai, fix nay da duoc xu ly.
Neu van gap loi:

1. Upload lai mot bai hat moi
2. Kiem tra bai moi upload, khong dung row upload cu bi loi

### 4. Quen mat khau khong thay email

Kiem tra:

```bash
docker compose logs tomcat
docker compose logs mailhog
```

Va mo:

- `http://localhost:8025`

### 5. Du lieu tieng Viet bi loi khi upload

Phien ban hien tai da duoc fix UTF-8 cho multipart request.
Neu van thay metadata loi, hay upload mot bai moi sau khi refresh trang admin.

### 6. Muon xem database

Mo `http://localhost:8081` va dang nhap:

- host: `mysql`
- user: `root`
- password: `root_password`

## Ghi chu cuoi

- He thong da duoc migrate khoi PHP sang `JSP/Servlet`
- He thong hien tai dung route khong co duoi `.php`
- Phan PHP cu da duoc xoa khoi repo
- Trang thai hien tai phu hop de dev va test local bang Docker
