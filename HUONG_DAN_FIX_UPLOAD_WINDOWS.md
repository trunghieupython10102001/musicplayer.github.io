# Huong Dan Fix Loi Upload Tren Windows

Tai lieu nay dung de xu ly truong hop:

- Upload bai hat thanh cong
- Bai hat duoc tao trong database
- Nhung cover image khong hien thi
- Audio khong play duoc

Day la loi thuong gap khi chay du an bang Docker tren Windows, do volume mount giua host va container khong dong bo nhu mong doi.

## Dau hieu nhan biet

Ban upload trong trang admin va thay:

- thong bao upload thanh cong
- bai hat moi xuat hien trong danh sach
- nhung cover bi vo
- mo file audio bi 404 hoac khong phat duoc

## Nguyen nhan pho bien

He thong hien tai luu file upload theo co che sau:

- Tomcat ghi file vao volume shared
- Nginx doc file tu thu muc `uploads/` tren host

Neu volume mount tren Windows bi loi, ban se gap tinh trang:

- database co ban ghi moi
- nhung file that khong co trong `uploads/`
- browser khong lay duoc anh/audio

## Cach fix nhanh

### Buoc 1: cap nhat code moi nhat

```bash
git pull origin master
```

### Buoc 2: tat stack hien tai

```bash
docker compose down
```

### Buoc 3: dam bao ton tai thu muc uploads

Trong root project, can co:

```text
uploads\
uploads\covers\
uploads\songs\
```

Neu chua co, tao thu muc bang PowerShell:

```powershell
mkdir uploads
mkdir uploads\covers
mkdir uploads\songs
```

### Buoc 4: build lai sach

```bash
docker compose up -d --build --force-recreate
```

### Buoc 5: upload lai 1 bai moi

Luu y:

- hay upload mot bai moi sau khi fix
- khong dung lai row cu da bi loi tu truoc

## Kiem tra nhanh sau khi upload

### Cach 1: kiem tra API

Mo:

```text
http://localhost:8080/api/songs/list?page=1&limit=5
```

Lay `cover_url` va `audio_url` cua bai vua upload.

### Cach 2: mo truc tiep URL file

Vi du:

```text
http://localhost:8080/uploads/covers/ten-file-cover.jpeg
http://localhost:8080/uploads/songs/ten-file-song.mp3
```

Neu URL nay tra `404`, thi loi khong nam o frontend ma nam o file storage / volume mount.

### Cach 3: kiem tra file tren may Windows

Vao thu muc project va xem:

```text
uploads\covers\
uploads\songs\
```

Neu file khong co trong do, nghia la Tomcat khong ghi file vao shared folder dung cach.

## Kiem tra volume mount trong container

Chay cac lenh sau:

```bash
docker compose exec tomcat sh -lc "ls -R /data/uploads"
docker compose exec nginx sh -lc "ls -R /var/www/html/uploads"
```

### Truong hop 1

- Tomcat co file
- Nginx khong co file

=> volume mount bi lech

### Truong hop 2

- Ca Tomcat va Nginx deu khong co file

=> app chua ghi file vao shared uploads

### Truong hop 3

- Ca Tomcat va Nginx deu co file
- nhung browser van 404

=> can check lai config Nginx hoac URL file trong DB

## Kiem tra `docker-compose.yml`

Dam bao phan `tomcat` co:

```yml
volumes:
  - ./uploads:/data/uploads
```

Va phan `nginx` co:

```yml
volumes:
  - ./uploads:/var/www/html/uploads
```

## Vi tri project tren Windows

Tren Windows, Docker Desktop co the bi loi mount neu project nam o nhung thu muc nhay cam hoac dong bo cloud.

Nen dat project o vi tri don gian nhu:

```text
C:\dev\musicplayer.github.io
```

Nen tranh neu co the:

```text
C:\Users\<ten>\Desktop\...
C:\Users\<ten>\OneDrive\...
```

## Neu van bi loi

Gui 3 thong tin sau:

1. Ket qua:

```bash
docker compose ps
```

2. Ket qua:

```bash
docker compose exec tomcat sh -lc "ls -R /data/uploads"
docker compose exec nginx sh -lc "ls -R /var/www/html/uploads"
```

3. `cover_url` cua bai moi upload

Khi co 3 thong tin nay, co the xac dinh chinh xac loi nam o:

- backend upload
- volume mount
- nginx static serving
- hay browser cache

## Ket luan

Neu chay tren Windows va upload thanh cong nhung cover/audio khong hien thi, phan lon truong hop la do volume mount cua thu muc `uploads/`.

Hay:

1. `git pull`
2. tao thu muc `uploads/covers` va `uploads/songs`
3. `docker compose down`
4. `docker compose up -d --build --force-recreate`
5. upload mot bai moi de test lai
