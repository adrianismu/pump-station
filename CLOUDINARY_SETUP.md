# Setup Cloudinary untuk Upload Gambar di Railway

## Mengapa Perlu Cloudinary?

Railway menggunakan **ephemeral filesystem**, artinya file yang diupload ke server akan hilang setiap kali aplikasi restart atau redeploy. Oleh karena itu, kita perlu menggunakan external storage seperti Cloudinary untuk menyimpan gambar secara permanen.

## Langkah-langkah Setup

### 1. Buat Akun Cloudinary

1. Kunjungi [cloudinary.com](https://cloudinary.com)
2. Daftar akun gratis (free tier: 25GB storage, 25GB bandwidth/bulan)
3. Setelah login, buka Dashboard untuk mendapatkan credentials

### 2. Dapatkan Credentials Cloudinary

Di Dashboard Cloudinary, Anda akan menemukan:
- **Cloud Name**: nama unik cloud Anda
- **API Key**: kunci API untuk autentikasi
- **API Secret**: secret key untuk autentikasi

### 3. Set Environment Variables di Railway

1. Buka project Railway Anda
2. Masuk ke tab **Variables**
3. Tambahkan environment variables berikut:

```bash
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### 4. Deploy Ulang Aplikasi

Setelah menambahkan environment variables, Railway akan otomatis redeploy aplikasi.

## Cara Kerja Sistem Upload

### Automatic Fallback System

Aplikasi ini menggunakan sistem fallback otomatis:

1. **Primary**: Upload ke Cloudinary (jika configured)
2. **Fallback**: Upload ke local storage (jika Cloudinary gagal)

### File yang Dimodifikasi

1. **`config/filesystems.php`** - Menambahkan disk Cloudinary
2. **`app/Services/ImageUploadService.php`** - Service untuk handle upload
3. **`app/Http/Controllers/PublicController.php`** - Menggunakan service baru
4. **`start.sh`** - Menambahkan `storage:link` command

## Testing Upload

### Test di Local Development

```bash
# Install dependencies
composer install

# Set environment variables di .env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

# Test upload
php artisan serve
```

### Test di Production (Railway)

1. Buka aplikasi di Railway URL
2. Masuk ke halaman **Laporan Publik**
3. Coba upload gambar saat membuat laporan
4. Gambar akan tersimpan di Cloudinary dan dapat diakses secara permanen

## Monitoring & Troubleshooting

### Check Cloudinary Usage

1. Login ke Cloudinary Dashboard
2. Lihat **Media Library** untuk file yang diupload
3. Monitor usage di **Analytics**

### Debug Upload Issues

Jika upload gagal, check:

1. **Railway Logs**: `railway logs`
2. **Environment Variables**: Pastikan credentials benar
3. **File Size**: Maksimal 2MB per gambar
4. **File Format**: Hanya JPEG, PNG, JPG, GIF

### Log Messages

- `"Cloudinary upload failed"` - Upload ke Cloudinary gagal, fallback ke local
- `"Image deletion failed"` - Gagal menghapus gambar
- `"Creating storage link"` - Membuat symbolic link untuk storage

## Keuntungan Cloudinary

1. **Persistent Storage** - File tidak hilang saat redeploy
2. **CDN Global** - Loading gambar lebih cepat
3. **Auto Optimization** - Kompresi dan format otomatis
4. **Transformations** - Resize, crop, filter otomatis
5. **Free Tier** - 25GB storage gratis

## Backup Strategy

Jika ingin backup dari Cloudinary:

```bash
# Download semua gambar dari Cloudinary
# Gunakan Cloudinary Admin API atau tools seperti cloudinary-cli
```

## Alternatif Storage

Jika tidak ingin menggunakan Cloudinary, alternatif lain:

1. **AWS S3** - Lebih kompleks setup
2. **Google Cloud Storage** - Perlu setup service account
3. **DigitalOcean Spaces** - Compatible dengan S3 API
4. **Supabase Storage** - Gratis dengan limitasi

## Support

Jika ada masalah dengan setup Cloudinary:

1. Check dokumentasi: [cloudinary.com/documentation](https://cloudinary.com/documentation)
2. Railway support: [railway.app/help](https://railway.app/help)
3. Laravel Cloudinary package: [github.com/cloudinary-labs/cloudinary-laravel](https://github.com/cloudinary-labs/cloudinary-laravel) 