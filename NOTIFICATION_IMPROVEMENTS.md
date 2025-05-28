# Perbaikan Halaman Detail Notifikasi

## Ringkasan Perbaikan

Halaman detail notifikasi telah diperbaiki untuk memiliki tampilan yang lebih rapi dengan penerapan warna label yang sesuai dengan threshold dan grafik yang berfungsi dengan baik.

## Perubahan yang Dilakukan

### 1. Controller (NotificationsController.php)

#### Perbaikan Relasi Threshold (FIXED)
- **Error**: `Call to undefined relationship [thresholdSetting] on model [App\Models\PumpHouseThresholdSetting]`
- **Solusi**: Model `PumpHouseThresholdSetting` menyimpan data threshold langsung, bukan sebagai relasi
- **Perbaikan**: Menghapus `->with('thresholdSetting')` dan menggunakan field langsung dari model

#### Penambahan Data untuk Grafik
- **Water Level History**: Mengambil data ketinggian air 3 bulan terakhir
- **Pump House Thresholds**: Mengambil threshold khusus untuk rumah pompa
- **Global Thresholds**: Mengambil threshold global sebagai fallback
- **Format Data**: Data diformat dengan timestamp untuk chart dan nilai float

```php
// Get pump house specific thresholds (FIXED)
$thresholds = \App\Models\PumpHouseThresholdSetting::where('pump_house_id', $alert->pump_house_id)
    ->where('is_active', true)
    ->orderBy('water_level', 'asc')
    ->get()
    ->map(function ($setting) {
        return [
            'id' => $setting->id,
            'name' => $setting->name,
            'label' => $setting->label,
            'water_level' => (float) $setting->water_level,
            'color' => $setting->color,
            'severity' => $setting->severity,
            'description' => $setting->description,
        ];
    });

// Get water level history for the pump house (last 3 months)
$waterLevelHistory = \App\Models\WaterLevelHistory::where('pump_house_id', $alert->pump_house_id)
    ->where('recorded_at', '>=', now()->subMonths(3))
    ->orderBy('recorded_at', 'asc')
    ->get()
    ->map(function ($record) {
        return [
            'id' => $record->id,
            'water_level' => (float) $record->water_level,
            'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
            'timestamp' => $record->recorded_at->timestamp * 1000, // For chart
        ];
    });
```

#### Perbaikan Format Data Alert
- Menghilangkan format string pada `water_level`, `pump_status`, `rainfall`
- Menambahkan `lat` dan `lng` untuk koordinat peta
- Memperbaiki format tanggal untuk frontend

### 2. Frontend (Show.vue)

#### Status Cards dengan Warna Threshold
- **Ketinggian Saat Ini**: Menampilkan nilai dengan indikator warna
- **Status Air**: Menampilkan status dengan icon dan warna sesuai threshold
- **Animasi**: Indikator berkedip untuk menarik perhatian

```vue
<!-- Current Water Level Card -->
<Card class="status-card">
  <CardContent>
    <div class="flex items-center gap-3">
      <div 
        class="w-3 h-3 rounded-full water-level-indicator"
        :style="{ backgroundColor: waterLevelStatusInfo.color }"
      ></div>
      <div>
        <div class="text-2xl font-bold">{{ currentWaterLevel }}m</div>
        <p class="text-xs text-muted-foreground">
          {{ waterLevelStatusInfo.label }}
        </p>
      </div>
    </div>
  </CardContent>
</Card>
```

#### Grafik dengan ChartWithTimeFilter
- **Time Filters**: 24H, 7D, 1M, 2M, 3M
- **Threshold Toggle**: Dapat menampilkan/menyembunyikan garis threshold
- **Loading States**: Indikator loading saat memfilter data
- **Responsive**: Menyesuaikan ukuran layar

#### Threshold Legend dengan Warna
- **Color Indicators**: Bulatan warna sesuai threshold
- **Label Berwarna**: Nama threshold dengan warna yang sesuai
- **Hover Effects**: Efek hover untuk interaktivitas
- **Deskripsi**: Informasi lengkap setiap threshold

```vue
<div class="text-sm font-medium" :style="{ color: threshold.color }">
  {{ threshold.label }}
</div>
```

### 3. Composable (useWaterLevelUtils.js)

#### Perbaikan getStatusWithThresholds
- **Return Object Lengkap**: text, label, variant, color, description
- **Threshold Color**: Menggunakan warna dari database threshold
- **Fallback Handling**: Menangani kasus tidak ada threshold

```javascript
return {
  text: exceededThreshold.label || exceededThreshold.name,
  label: exceededThreshold.label || exceededThreshold.name,
  variant: variantMap[exceededThreshold.severity] || 'default',
  color: exceededThreshold.color || '#10b981',
  description: exceededThreshold.description || 'Status berdasarkan threshold'
}
```

### 4. Styling dan UX

#### CSS Enhancements
- **Print Styles**: Optimasi untuk pencetakan
- **Hover Effects**: Efek hover pada cards dan threshold items
- **Animations**: Animasi pulse untuk indikator status
- **Responsive**: Layout yang responsif untuk berbagai ukuran layar

```css
.water-level-indicator {
  animation: pulse 2s infinite;
}

.status-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
```

## Fitur yang Ditambahkan

### 1. Status Cards
- Menampilkan ketinggian air saat ini dengan warna threshold
- Status air dengan icon dan deskripsi
- Animasi visual untuk menarik perhatian

### 2. Grafik Interaktif
- Time filter untuk periode data
- Toggle threshold lines
- Loading states
- Responsive design

### 3. Threshold Legend
- Daftar semua threshold dengan warna
- Informasi level dan deskripsi
- Hover effects untuk UX yang lebih baik

### 4. Sidebar Informatif
- Informasi lengkap notifikasi
- Legenda threshold
- Quick actions (print, share, detail pump house)

## Data yang Dibutuhkan

### Props dari Controller
```javascript
{
  alert: Object,           // Data notifikasi
  waterLevelHistory: Array, // Data ketinggian air 3 bulan
  thresholds: Array,       // Threshold khusus pump house
  globalThresholds: Array  // Threshold global
}
```

### Computed Properties
```javascript
const activeThresholds = computed(() => {
  return props.thresholds.length > 0 ? props.thresholds : props.globalThresholds
})

const currentWaterLevel = computed(() => {
  if (props.waterLevelHistory.length > 0) {
    return props.waterLevelHistory[props.waterLevelHistory.length - 1].water_level
  }
  return 0.00
})

const waterLevelStatusInfo = computed(() => {
  const status = getStatusWithThresholds(currentWaterLevel.value, activeThresholds.value)
  return status
})
```

## Testing

### Test File (test_notification.php)
File test sederhana untuk memverifikasi:
- Data alert tersedia
- Water level history ada
- Threshold settings berfungsi
- Relasi database bekerja

### Hasil Test
```
Testing Threshold Fix
====================

Pump House ID: 1
Threshold Count: 4

- Normal: 0.00m (Color: #10b981, Severity: low)
- Kritis: 2.50m (Color: #ef4444, Severity: high)  
- Peringatan: 3.00m (Color: #f59e0b, Severity: medium)
- Darurat: 3.00m (Color: #dc2626, Severity: critical)

Global Thresholds Count: 4

✅ Threshold fix test completed successfully!
```

## Komponen yang Digunakan

### UI Components
- `Card`, `CardContent`, `CardHeader`, `CardTitle`, `CardDescription`
- `Button`, `Separator`, `StatusBadge`
- `ChartWithTimeFilter`, `TimeFilter`

### Icons
- `ChevronLeft`, `MapPin`, `Calendar`, `Droplets`
- `Printer`, `Share`, `Home`, `Settings`

### Composables
- `useDateUtils`: Format tanggal dan waktu
- `useWaterLevelUtils`: Utility untuk status air
- `useTimeFilter`: Filter waktu untuk grafik

## Hasil Akhir

Halaman detail notifikasi sekarang memiliki:
1. **Tampilan yang Rapi**: Layout 2/3 untuk konten utama, 1/3 untuk sidebar
2. **Warna Threshold**: Semua elemen menggunakan warna sesuai threshold
3. **Grafik Berfungsi**: ChartWithTimeFilter dengan data real
4. **Interaktivitas**: Hover effects, animations, responsive design
5. **Print Ready**: Optimasi untuk pencetakan
6. **Informasi Lengkap**: Status, threshold legend, quick actions

Perbaikan ini membuat halaman detail notifikasi konsisten dengan halaman lain dalam sistem dan memberikan pengalaman pengguna yang lebih baik dengan visualisasi data yang jelas dan informatif.

## Troubleshooting

### Error: Call to undefined relationship [thresholdSetting]

**Problem**: Error terjadi karena controller mencoba menggunakan relasi `thresholdSetting` yang tidak ada di model `PumpHouseThresholdSetting`.

**Root Cause**: Model `PumpHouseThresholdSetting` menyimpan data threshold langsung di tabelnya sendiri, bukan sebagai relasi ke tabel `threshold_settings`.

**Solution**: 
1. Hapus `->with('thresholdSetting')` dari query
2. Gunakan field langsung dari model: `$setting->name`, `$setting->label`, dll.
3. Tambahkan filter `->where('is_active', true)` dan ordering `->orderBy('water_level', 'asc')`

**Before (Error)**:
```php
$thresholds = \App\Models\PumpHouseThresholdSetting::where('pump_house_id', $alert->pump_house_id)
    ->with('thresholdSetting') // ❌ Relasi tidak ada
    ->get()
    ->map(function ($setting) {
        return [
            'name' => $setting->thresholdSetting->name, // ❌ Error
            'label' => $setting->thresholdSetting->label, // ❌ Error
            // ...
        ];
    });
```

**After (Fixed)**:
```php
$thresholds = \App\Models\PumpHouseThresholdSetting::where('pump_house_id', $alert->pump_house_id)
    ->where('is_active', true) // ✅ Filter aktif
    ->orderBy('water_level', 'asc') // ✅ Urutan
    ->get()
    ->map(function ($setting) {
        return [
            'name' => $setting->name, // ✅ Field langsung
            'label' => $setting->label, // ✅ Field langsung
            'color' => $setting->color, // ✅ Field langsung
            // ...
        ];
    });
```

### Struktur Database

**Table: pump_house_threshold_settings**
```sql
- id (primary key)
- pump_house_id (foreign key to pump_houses)
- name (string: 'normal', 'warning', 'critical', 'emergency')
- label (string: 'Normal', 'Peringatan', 'Kritis', 'Darurat')
- water_level (decimal: threshold level)
- color (string: hex color code)
- severity (string: 'low', 'medium', 'high', 'critical')
- is_active (boolean)
- description (text)
```

**Table: threshold_settings** (Global defaults)
```sql
- id (primary key)
- name, label, water_level, color, severity, is_active, description
```

### Verification Steps

1. **Check Model Structure**:
   ```bash
   php artisan tinker --execute="echo App\Models\PumpHouseThresholdSetting::where('pump_house_id', 1)->count();"
   ```

2. **Test Data Mapping**:
   ```php
   $thresholds = App\Models\PumpHouseThresholdSetting::where('pump_house_id', 1)
       ->where('is_active', true)
       ->get();
   
   foreach ($thresholds as $t) {
       echo $t->label . ': ' . $t->water_level . 'm (' . $t->color . ')' . PHP_EOL;
   }
   ```

3. **Verify Controller Response**:
   - Akses halaman `/admin/notifications/{id}`
   - Periksa Network tab di browser untuk melihat data yang dikirim
   - Pastikan tidak ada error 500 dan data threshold muncul 