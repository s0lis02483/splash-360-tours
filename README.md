# Splash360 Tours

A complete multi-tenant SaaS platform for creating and publishing 360° virtual tours for the real estate sector.

![PHP](https://img.shields.io/badge/PHP-7.0%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)
![License](https://img.shields.io/badge/license-MIT-green)

## Features

### Core Features
- **Multi-Tenant Architecture**: Complete tenant isolation with subscription management
- **360° Virtual Tours**: Create immersive virtual tours with panoramic images
- **Property Management**: Manage real estate properties with details and images
- **Interactive Hotspots**: Navigation, info, and link hotspots within 360° scenes
- **Analytics Dashboard**: Track tour views and engagement
- **Subscription System**: Multiple plans with usage limits and quotas
- **Public API**: REST API for embedding tours on external websites
- **Responsive Design**: Works on desktop, tablet, and mobile devices

### Technical Features
- Custom lightweight MVC framework (no external dependencies)
- Secure authentication with password hashing
- CSRF protection on all forms
- Tenant data isolation at database level
- File upload with validation and security
- Pagination and filtering
- RESTful API with API key authentication

## Requirements

- **PHP**: 7.0 or higher (compatible up to 8.x)
- **MySQL**: 5.7 or higher
- **Web Server**: Apache or Nginx
- **PHP Extensions**:
  - PDO
  - pdo_mysql
  - gd (for image handling)
  - mbstring
  - fileinfo

## Installation

### 1. Clone or Download the Project

```bash
git clone <repository-url> splash360tours
cd splash360tours
```

### 2. Configure Environment

Copy the environment example file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```
DB_HOST=localhost
DB_NAME=splash360tours
DB_USER=root
DB_PASS=your_password
```

### 3. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE splash360tours CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 4. Import Database Schema

```bash
mysql -u root -p splash360tours < database.sql
```

### 5. Set Permissions

```bash
chmod 755 -R storage/
chmod 755 -R public/
```

### 6. Configure Web Server

#### Apache

Create a virtual host or use `.htaccess` (already included in `public/.htaccess`).

Example virtual host:

```apache
<VirtualHost *:80>
    ServerName splash360.local
    DocumentRoot /path/to/splash360tours/public

    <Directory /path/to/splash360tours/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splash360_error.log
    CustomLog ${APACHE_LOG_DIR}/splash360_access.log combined
</VirtualHost>
```

Enable mod_rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

```nginx
server {
    listen 80;
    server_name splash360.local;
    root /path/to/splash360tours/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 7. Access the Application

Navigate to: `http://localhost` or `http://splash360.local`

## Demo Credentials

The database includes demo data with the following login credentials:

### Platform Admin
- **Email**: admin@splash360.com
- **Password**: password

### Tenant 1 (Luxury Realty Group)
- **Email**: john@luxuryrealty.com
- **Password**: password

### Tenant 2 (Coastal Properties Inc)
- **Email**: emma@coastalprops.com
- **Password**: password

## Usage

### Creating a Virtual Tour

1. **Login** to your tenant account
2. **Create a Property**: Go to Properties → Add Property
3. **Create a Tour**: Navigate to the property → Create Tour
4. **Add Scenes**: Upload 360° panoramic images (equirectangular format)
5. **Add Hotspots**: Create interactive hotspots within scenes
   - **Navigation**: Link to another scene
   - **Info**: Display information popup
   - **Link**: Open external URL
6. **Publish**: Set tour status to "Published" and mark as public
7. **Share**: Copy the public URL to share with clients

### Managing Subscriptions

1. Go to **Subscription** in the main menu
2. View current plan and usage statistics
3. **Change Plan**: Select from available plans
4. **Pay Invoices**: Process dummy payments for testing

### Using the Public API

#### Authentication

Include your API key in the request header:

```
X-API-KEY: your_api_key_here
```

Find your API key in the tenant settings (platform admin can view all keys).

#### Endpoints

**Health Check**
```
GET /api/health
```

Response:
```json
{
  "success": true,
  "message": "API is running",
  "version": "1.0.0"
}
```

**List Tours**
```
GET /api/tours
Headers: X-API-KEY: your_api_key
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Penthouse Virtual Tour",
      "slug": "modern-downtown-penthouse-tour",
      "description": "Explore every corner...",
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

**Get Single Tour**
```
GET /api/tours/{slug}
Headers: X-API-KEY: your_api_key
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Penthouse Virtual Tour",
    "property_title": "Modern Downtown Penthouse",
    "scenes": [
      {
        "id": 1,
        "name": "Living Room",
        "image_url": "http://localhost/storage/uploads/scenes/penthouse_living.jpg",
        "initial_yaw": 0,
        "initial_pitch": 0,
        "hotspots": [...]
      }
    ]
  }
}
```

## Project Structure

```
splash360tours/
├── app/
│   ├── controllers/       # Application controllers
│   ├── core/             # Core framework classes
│   ├── helpers/          # Helper functions
│   ├── models/           # Data models
│   └── views/            # View templates
├── config/               # Configuration files
├── public/               # Public web root
│   ├── assets/          # CSS, JS, images
│   ├── index.php        # Application entry point
│   └── .htaccess        # Apache rewrite rules
├── storage/             # File storage
│   └── uploads/         # Uploaded files
├── tests/               # Test files
├── database.sql         # Database schema and seed data
├── .env.example         # Environment variables template
└── README.md           # This file
```

## Architecture

### MVC Pattern

- **Models**: Handle database operations and business logic
- **Views**: Render HTML templates
- **Controllers**: Process requests and coordinate models/views

### Multi-Tenancy

- Single database with `tenant_id` column in all relevant tables
- Automatic tenant filtering in base Model class
- Subscription-based access control with usage limits

### Security

- Password hashing using PHP's `password_hash()`
- CSRF tokens on all forms
- Prepared statements for all SQL queries
- File upload validation (type, size, path traversal prevention)
- Input sanitization and validation

## Testing

Run functional tests:

```bash
php tests/AuthTest.php
php tests/TenantIsolationTest.php
php tests/ApiTest.php
```

### Test Checklist

- [ ] User registration and login
- [ ] Property CRUD operations
- [ ] Tour creation and publishing
- [ ] Scene upload and management
- [ ] Hotspot creation and functionality
- [ ] Tenant data isolation
- [ ] Subscription limit enforcement
- [ ] API authentication and endpoints
- [ ] 360° viewer basic functionality
- [ ] Analytics tracking

## Deployment

### Production Checklist

1. **Environment**
   - Set `APP_ENV=production` in `.env`
   - Set `APP_DEBUG=false`
   - Use strong database credentials

2. **Security**
   - Enable HTTPS
   - Set secure session cookies
   - Configure proper file permissions
   - Disable PHP error display

3. **Performance**
   - Enable PHP OPcache
   - Configure MySQL query cache
   - Use CDN for static assets
   - Enable GZIP compression

4. **Backup**
   - Regular database backups
   - Backup uploaded files in `/storage`

5. **Monitoring**
   - Set up error logging
   - Monitor disk space for uploads
   - Track API usage

## Troubleshooting

### Database Connection Fails
- Verify credentials in `.env`
- Check MySQL service is running
- Ensure database exists

### 404 Errors on All Pages
- Enable mod_rewrite (Apache)
- Check `.htaccess` file exists in `/public`
- Verify DocumentRoot points to `/public`

### Upload Fails
- Check `/storage/uploads` permissions (755)
- Verify `upload_max_filesize` in php.ini
- Check available disk space

### 360° Viewer Not Working
- Ensure images are equirectangular format
- Check browser console for JavaScript errors
- Verify image paths are accessible

## Support

For issues, questions, or contributions, please open an issue on the repository.

## License

MIT License - feel free to use this project for commercial or personal purposes.

## Credits

Built with PHP, MySQL, and vanilla JavaScript. No external frameworks required.

---

**Splash360 Tours** - Making real estate virtual tours accessible to everyone.
