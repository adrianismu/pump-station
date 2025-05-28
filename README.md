# Sistem Manajemen Rumah Pompa (Pump Station Management System)

Sistem monitoring dan manajemen rumah pompa untuk pencegahan banjir dengan fitur real-time monitoring, pelaporan publik, dan sistem peringatan dini.

## 🚀 Fitur Utama

### 📊 Dashboard Monitoring
- Real-time monitoring status rumah pompa
- Visualisasi data ketinggian air dengan chart interaktif
- Statistik pompa aktif/rusak
- Integrasi data cuaca dari Open-Meteo API
- Analisis risiko banjir berdasarkan cuaca dan ketinggian air

### 💧 Manajemen Ketinggian Air
- Input data ketinggian air real-time
- Riwayat data historis dengan grafik
- Sistem alert otomatis berdasarkan threshold
- Export data untuk analisis lebih lanjut

### 🗺️ Peta Interaktif
- Visualisasi lokasi rumah pompa dengan Leaflet.js
- Status real-time setiap rumah pompa
- Informasi detail dengan popup interaktif

### 📝 Sistem Laporan Publik
- Masyarakat dapat melaporkan masalah
- Upload foto sebagai bukti
- Tracking status penanganan
- Sistem notifikasi untuk admin

### 🔔 Sistem Peringatan & Notifikasi
- Alert otomatis berdasarkan ketinggian air
- Notifikasi real-time untuk admin
- Sistem severity (Normal, Waspada, Peringatan, Kritis)
- Riwayat tindakan yang diambil

### 📚 Konten Edukasi
- Artikel tentang pencegahan banjir
- Video edukasi
- Infografis informatif
- Sistem tagging dan pencarian

### 👥 Multi-Role System
- **Admin**: Akses penuh ke semua fitur
- **Petugas**: Input data, respond laporan, manage alerts

## 🛠️ Teknologi yang Digunakan

### Backend
- **Laravel 12** - PHP Framework
- **Inertia.js** - Modern monolith approach
- **MySQL/MariaDB** - Database
- **Laravel Sanctum** - Authentication
- **Spatie Laravel Permission** - Role & Permission management

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Tailwind CSS** - Utility-first CSS framework
- **Shadcn/ui** - Modern UI components
- **Chart.js** - Data visualization
- **Leaflet.js** - Interactive maps
- **Lucide Vue** - Beautiful icons

### External APIs
- **Open-Meteo API** - Weather data (no API key required)

## 📋 Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL/MariaDB
- Git

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd pump-station
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
Edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pump_station
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Database Migration & Seeding
```bash
# Run migrations and seed data
php artisan migrate:fresh --seed
```

### 6. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Start Development Server
```bash
php artisan serve
```

Aplikasi akan berjalan di `http://localhost:8000`

## 👤 Default User Accounts

### Admin
- **Email**: admin@pumpstation.com
- **Password**: password

### Petugas
- **Email**: budi.santoso@pumpstation.com
- **Password**: password

## 📁 Struktur Project

```
pump-station/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Admin controllers
│   │   ├── Auth/            # Authentication
│   │   └── WaterLevelController.php
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/            # Data seeders
├── resources/
│   ├── js/
│   │   ├── Components/      # Vue components
│   │   ├── Layouts/         # Layout components
│   │   └── Pages/           # Page components
│   └── css/                # Stylesheets
└── routes/
    ├── web.php             # Web routes
    └── api.php             # API routes
```

## 🔧 Konfigurasi

### Weather API
Aplikasi menggunakan Open-Meteo API yang tidak memerlukan API key. Jika ingin menggunakan provider lain, edit konfigurasi di:
```php
// app/Services/WeatherService.php
```

### Alert Thresholds
Konfigurasi threshold ketinggian air dapat diubah di:
```php
// app/Http/Controllers/WaterLevelController.php
private function checkAndCreateAlert($pumpHouse, $waterLevel)
{
    // Critical level (above 2.5m)
    if ($waterLevel >= 2.5) {
        // ...
    }
    // Warning level (above 2.0m)
    elseif ($waterLevel >= 2.0) {
        // ...
    }
}
```

## 📊 Data Dummy

Project ini sudah dilengkapi dengan data dummy yang realistis:
- 5 Rumah pompa di area Surabaya
- Data historis ketinggian air 30 hari terakhir
- Sample alerts dan reports
- Konten edukasi lengkap
- User accounts dengan role berbeda

## 🔄 Development Workflow

### 1. Frontend Development
```bash
# Watch for changes
npm run dev

# Build for production
npm run build
```

### 2. Backend Development
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run tests
php artisan test
```

### 3. Database Management
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create new seeder
php artisan make:seeder TableNameSeeder
```

## 🚀 Deployment

### Production Setup
1. Set environment to production in `.env`:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize for production:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

3. Set proper file permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

Untuk pertanyaan atau dukungan, silakan hubungi:
- Email: support@pumpstation.com
- GitHub Issues: [Create an issue](../../issues)

## 🙏 Acknowledgments

- Laravel Framework
- Vue.js Community
- Tailwind CSS
- Open-Meteo API
- Shadcn/ui Components
