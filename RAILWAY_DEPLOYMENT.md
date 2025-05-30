# Panduan Deployment Laravel ke Railway dengan MySQL

## Langkah-langkah Deployment

### 1. Persiapan Project

Project Anda sudah siap dengan konfigurasi berikut:
- `railway.json` - Konfigurasi deployment Railway
- `nixpacks.toml` - Build configuration  
- `start.sh` - Startup script
- `Procfile` - Process file

### 2. Setup Database MySQL di Railway

1. Buka Railway dashboard (https://railway.app)
2. Buat project baru atau gunakan project existing
3. Tambahkan MySQL service:
   - Klik "Add Service" → "Database" → "MySQL"
   - Railway akan otomatis membuat database MySQL

### 3. Environment Variables di Railway

Masuk ke service Laravel Anda dan tambahkan environment variables berikut:

```env
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:rp9jE7P33C4I7xT1QxAIB/zWM8S6rMqOrxxqlz0q5XA=
APP_DEBUG=false
APP_URL=${{RAILWAY_PUBLIC_DOMAIN}}

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration (otomatis dari MySQL service)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### 4. Deploy ke Railway

#### Opsi 1: Deploy via Git (Recommended)

1. Push code ke repository GitHub:
```bash
git add .
git commit -m "Prepare for Railway deployment"
git push origin main
```

2. Di Railway dashboard:
   - Klik "New Project"
   - Pilih "Deploy from GitHub repo"
   - Pilih repository Anda
   - Railway akan otomatis mendeteksi Laravel dan melakukan build

#### Opsi 2: Deploy via Railway CLI

1. Install Railway CLI:
```bash
npm install -g @railway/cli
```

2. Login dan deploy:
```bash
railway login
railway init
railway up
```

### 5. Post-Deployment

Setelah deployment berhasil:

1. Database akan otomatis di-migrate melalui `start.sh`
2. Aplikasi akan tersedia di URL yang diberikan Railway
3. Check logs untuk memastikan semua berjalan dengan baik

### 6. Troubleshooting

#### Database Connection Issues:
- Pastikan MySQL service sudah running
- Cek environment variables database sudah benar
- Lihat logs untuk error connection

#### Build Issues:
- Pastikan `composer.json` dan `package.json` sudah benar
- Check memory limits jika build gagal
- Pastikan dependencies PHP dan Node.js compatible

#### Permission Issues:
- Script `start.sh` akan otomatis set permissions
- Pastikan storage dan bootstrap/cache writable

### 7. Monitoring

Railway menyediakan:
- Real-time logs
- Metrics dashboard
- Automatic scaling
- Health checks

### 8. Custom Domain (Optional)

Jika ingin menggunakan custom domain:
1. Buka service settings di Railway
2. Tambahkan custom domain
3. Update DNS records sesuai instruksi Railway
4. Update APP_URL environment variable

## Tips Optimasi

1. **Caching**: Laravel akan otomatis cache config, routes, dan views saat deployment
2. **Database**: Gunakan connection pooling untuk performa optimal  
3. **Static Assets**: Railway akan serve static files dengan efisien
4. **Environment**: Selalu gunakan `APP_ENV=production` untuk performa terbaik

## Keamanan

1. Pastikan `APP_DEBUG=false` di production
2. Gunakan HTTPS (Railway provides otomatis)
3. Set proper session dan cache configuration
4. Review environment variables untuk sensitive data 