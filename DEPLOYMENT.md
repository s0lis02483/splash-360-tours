# Deployment Guide

## Production Deployment Checklist

### Pre-Deployment

- [ ] Review and test all features in staging environment
- [ ] Run all test suites
- [ ] Update `.env` with production credentials
- [ ] Backup existing database (if upgrading)
- [ ] Review security settings
- [ ] Check PHP version compatibility
- [ ] Verify all required PHP extensions are installed

### Server Requirements

#### Minimum Specifications
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD (+ additional for uploads)
- **PHP**: 7.0 - 8.2
- **MySQL**: 5.7+
- **Web Server**: Apache 2.4+ or Nginx 1.18+

#### Recommended Specifications
- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 50GB+ SSD
- **PHP**: 8.1
- **MySQL**: 8.0
- **Web Server**: Nginx 1.20+

### Step-by-Step Deployment

#### 1. Server Preparation (Ubuntu 20.04/22.04)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and required extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-gd \
    php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip

# Install MySQL
sudo apt install -y mysql-server

# Install Nginx
sudo apt install -y nginx

# Install Git
sudo apt install -y git
```

#### 2. MySQL Configuration

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database
sudo mysql
```

```sql
CREATE DATABASE splash360tours CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'splash360'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON splash360tours.* TO 'splash360'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. Application Deployment

```bash
# Create application directory
sudo mkdir -p /var/www/splash360tours
cd /var/www/splash360tours

# Clone or upload application files
git clone <repository-url> .

# Or upload via SCP/SFTP
# scp -r ./splash360tours/* user@server:/var/www/splash360tours/

# Set ownership
sudo chown -R www-data:www-data /var/www/splash360tours

# Set permissions
sudo find /var/www/splash360tours -type d -exec chmod 755 {} \;
sudo find /var/www/splash360tours -type f -exec chmod 644 {} \;
sudo chmod -R 755 /var/www/splash360tours/storage
sudo chmod -R 755 /var/www/splash360tours/public/assets
```

#### 4. Environment Configuration

```bash
# Create .env file
cp .env.example .env
nano .env
```

```env
APP_NAME="Splash360 Tours"
APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_NAME=splash360tours
DB_USER=splash360
DB_PASS=strong_password_here

MAIL_FROM=noreply@yourdomain.com
MAIL_FROM_NAME="Splash360 Tours"
```

#### 5. Database Import

```bash
mysql -u splash360 -p splash360tours < database.sql
```

#### 6. Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/splash360tours
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/splash360tours/public;
    index index.php;

    # SSL Configuration (use Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # File Upload Limits
    client_max_body_size 20M;

    # Logging
    access_log /var/log/nginx/splash360_access.log;
    error_log /var/log/nginx/splash360_error.log;

    # Main Location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP Processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Static Assets Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Storage files
    location /storage {
        alias /var/www/splash360tours/storage;
        try_files $uri $uri/ =404;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/splash360tours /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 7. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically
# Test renewal:
sudo certbot renew --dry-run
```

#### 8. PHP-FPM Optimization

```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

```ini
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M

; Production settings
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Security
expose_php = Off
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

### Post-Deployment

#### 1. Verify Installation

- [ ] Access homepage
- [ ] Test login with demo credentials
- [ ] Create test property
- [ ] Upload test tour
- [ ] View public tour
- [ ] Test API endpoints

#### 2. Set Up Monitoring

**Install monitoring tools:**

```bash
# Install monitoring
sudo apt install -y htop iotop
```

**Create monitoring script:**

```bash
#!/bin/bash
# /usr/local/bin/check_disk_space.sh

THRESHOLD=80
CURRENT=$(df /var/www/splash360tours/storage | grep / | awk '{ print $5}' | sed 's/%//g')

if [ "$CURRENT" -gt "$THRESHOLD" ]; then
    echo "Disk space warning: ${CURRENT}% used" | mail -s "Disk Space Alert" admin@yourdomain.com
fi
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/check_disk_space.sh

# Add to cron (daily check)
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/check_disk_space.sh") | crontab -
```

#### 3. Set Up Backups

**Database Backup Script:**

```bash
#!/bin/bash
# /usr/local/bin/backup_database.sh

BACKUP_DIR="/var/backups/splash360"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="splash360tours"
DB_USER="splash360"
DB_PASS="strong_password_here"

mkdir -p $BACKUP_DIR

# Create backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_backup_$DATE.sql.gz"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/backup_database.sh

# Add to cron (daily at 3 AM)
(crontab -l 2>/dev/null; echo "0 3 * * * /usr/local/bin/backup_database.sh") | crontab -
```

**Storage Backup:**

```bash
#!/bin/bash
# /usr/local/bin/backup_storage.sh

BACKUP_DIR="/var/backups/splash360"
DATE=$(date +%Y%m%d)
STORAGE_DIR="/var/www/splash360tours/storage/uploads"

mkdir -p $BACKUP_DIR

# Create tarball
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C /var/www/splash360tours storage/uploads

# Keep only last 7 days
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +7 -delete

echo "Storage backup completed: storage_$DATE.tar.gz"
```

#### 4. Performance Tuning

**Enable OPcache:**

```bash
sudo nano /etc/php/8.1/fpm/conf.d/10-opcache.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

**MySQL Optimization:**

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 64M
max_connections = 200
```

```bash
sudo systemctl restart mysql
```

### Maintenance

#### Regular Tasks

**Weekly:**
- Review error logs
- Check disk space
- Review analytics

**Monthly:**
- Update PHP/MySQL if needed
- Review and optimize database
- Security audit

**Quarterly:**
- Full system backup
- Performance review
- Security updates

### Scaling Strategies

#### Horizontal Scaling

1. **Load Balancer Setup**
   - Use Nginx as load balancer
   - Multiple application servers
   - Shared storage (NFS or S3-compatible)
   - Centralized session storage (Redis)

2. **Database Replication**
   - Master-slave MySQL replication
   - Read replicas for analytics queries

#### Vertical Scaling

- Increase server resources (CPU, RAM)
- Optimize database queries
- Implement caching layer (Redis/Memcached)

### Troubleshooting

#### Application Errors

```bash
# Check PHP error log
sudo tail -f /var/log/php/error.log

# Check Nginx error log
sudo tail -f /var/log/nginx/splash360_error.log

# Check PHP-FPM status
sudo systemctl status php8.1-fpm
```

#### Database Issues

```bash
# Check MySQL status
sudo systemctl status mysql

# Check slow query log
sudo mysql
> SHOW VARIABLES LIKE 'slow_query_log%';
```

#### Performance Issues

```bash
# Check server load
htop

# Check disk I/O
sudo iotop

# Check database performance
mysqltuner
```

## Conclusion

Following this deployment guide ensures a secure, performant, and maintainable production environment for Splash360 Tours. Regular monitoring and maintenance are essential for long-term success.
