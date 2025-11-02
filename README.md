# Notes App - Like OneNote

Ứng dụng ghi chú đầy đủ tính năng với PHP, MySQL, Tailwind CSS, và JWT Authentication.

## Tính năng

- ✅ Authentication (JWT) - Login/Signup
- ✅ Tài khoản admin mặc định (admin/admin)
- ✅ Unique username validation
- ✅ CRUD Notes (Create, Read, Update, Delete)
- ✅ Tiêu đề, Tags, Nội dung
- ✅ Hiển thị thời gian chỉnh sửa cuối cùng
- ✅ Tìm kiếm notes
- ✅ Responsive UI với Tailwind CSS
- ✅ Podman/Docker support (MySQL container)
- ✅ Nginx + Apache support
- ✅ Development và Production environments

## Yêu cầu hệ thống

- Podman hoặc Docker
- Podman Compose hoặc Docker Compose
- PHP 8.2+
- MySQL 8.0+

## Cài đặt nhanh

### 1. Development Environment (Apache)

```powershell
# Clone hoặc tải project về

# Chạy với Podman Compose (Development mode với Apache)
podman-compose -f docker-compose.dev.yml up -d

# Hoặc với Docker Compose
docker-compose -f docker-compose.dev.yml up -d

# Truy cập ứng dụng
# http://localhost:8080
```

### 2. Production Environment (Nginx + PHP-FPM)

```powershell
# Chạy với Podman Compose (Production mode với Nginx)
podman-compose up -d

# Hoặc với Docker Compose
docker-compose up -d

# Truy cập ứng dụng
# http://localhost
```

## Cài đặt PHP dependencies

```powershell
# Vào container PHP
podman exec -it notes_php bash

# Hoặc với Docker
docker exec -it notes_php bash

# Trong container, chạy:
composer install
```

## Database Setup

Database sẽ tự động được khởi tạo khi container MySQL start lần đầu với:
- Database: `notes_app`
- User mặc định: admin/admin
- 2 notes mẫu

## Cấu hình

### Environment Variables

Chỉnh sửa trong `docker-compose.yml` hoặc `docker-compose.dev.yml`:

```yaml
environment:
  DB_HOST: mysql
  DB_NAME: notes_app
  DB_USER: notes_user
  DB_PASSWORD: notes_password
  JWT_SECRET: your-secret-key-change-this-in-production
  APP_ENV: production # hoặc development
```

### Ports

**Development (docker-compose.dev.yml):**
- Apache: http://localhost:8080
- MySQL: localhost:3307

**Production (docker-compose.yml):**
- Nginx: http://localhost:80
- MySQL: localhost:3306

## API Endpoints

### Authentication

```
POST /api/auth/login
Body: { "username": "admin", "password": "admin" }

POST /api/auth/signup
Body: { "username": "user1", "password": "pass123", "email": "user@example.com" }

GET /api/auth/verify
Headers: Authorization: Bearer {token}
```

### Notes (Requires Authentication)

```
GET /api/notes
GET /api/notes?search=keyword
GET /api/notes/{id}

POST /api/notes
Body: { "title": "Title", "content": "Content", "tags": "tag1,tag2" }

PUT /api/notes/{id}
Body: { "title": "New Title", "content": "New Content", "tags": "newtag" }

DELETE /api/notes/{id}
```

## Cấu trúc thư mục

```
.
├── api/                    # Backend API
│   ├── index.php          # API router
│   └── routes/            # API routes
│       ├── auth.php       # Authentication endpoints
│       └── notes.php      # Notes CRUD endpoints
├── config/                # Configuration files
│   ├── Database.php       # Database connection
│   ├── Auth.php          # JWT authentication
│   └── Response.php      # API response helper
├── database/              # Database files
│   └── init.sql          # Database initialization
├── public/                # Frontend
│   ├── index.html        # Main HTML
│   ├── js/
│   │   └── app.js        # Frontend JavaScript
│   └── .htaccess         # Apache rewrite rules
├── nginx/                 # Nginx configuration
│   ├── nginx.conf
│   └── default.conf
├── apache/                # Apache configuration
│   └── 000-default.conf
├── docker-compose.yml     # Production setup (Nginx)
├── docker-compose.dev.yml # Development setup (Apache)
├── Dockerfile.php         # PHP-FPM image
└── Dockerfile.apache      # Apache+PHP image
```

## Sử dụng

1. **Đăng nhập với admin:**
   - Username: `admin`
   - Password: `admin`

2. **Tạo tài khoản mới:**
   - Click "Sign up"
   - Nhập username (ít nhất 3 ký tự, unique)
   - Nhập password (ít nhất 6 ký tự)

3. **Quản lý Notes:**
   - Click "New Note" để tạo note mới
   - Click icon edit để sửa
   - Click icon trash để xóa
   - Dùng search bar để tìm kiếm

## Dừng ứng dụng

```powershell
# Development
podman-compose -f docker-compose.dev.yml down

# Production
podman-compose down
```

## Xóa volumes (reset database)

```powershell
# Development
podman-compose -f docker-compose.dev.yml down -v

# Production
podman-compose down -v
```

## Troubleshooting

### Không kết nối được database
```powershell
# Kiểm tra container MySQL đã chạy chưa
podman ps

# Xem logs
podman logs notes_mysql
```

### Permission errors
```powershell
# Chạy lại với quyền cao hơn hoặc check file permissions
```

### API không hoạt động
```powershell
# Kiểm tra PHP logs
podman logs notes_php

# Kiểm tra Nginx/Apache logs
podman logs notes_nginx
# hoặc
podman logs notes_apache_dev
```

## Bảo mật

Đối với production:
1. Đổi `JWT_SECRET` thành giá trị bí mật
2. Đổi mật khẩu MySQL
3. Đổi mật khẩu admin mặc định
4. Enable HTTPS
5. Cấu hình firewall

## License

MIT License
