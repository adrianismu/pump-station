<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\EducationContent;
use App\Models\PumpHouse;
use App\Models\Report;
use Illuminate\Database\Seeder;

class PumpHouseSeeder extends Seeder
{
    public function run(): void
    {
        // Create pump houses for Surabaya
        $pumpHouses = [
            [
                'name' => 'Rumah Pompa Wonorejo',
                'address' => 'Jl. Wonorejo Timur, Rungkut, Surabaya',
                'lat' => -7.3119,
                'lng' => 112.8016,
                'status' => 'Aktif',
                'capacity' => '5000 m³/jam',
                'pump_count' => 8,
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=2069&auto=format&fit=crop',
                'built_year' => 2015,
                'manager_name' => 'Budi Santoso',
                'contact_phone' => '081234567890',
                'contact_email' => 'budi.s@surabaya.go.id',
                'staff_count' => 12,
                'last_updated' => now()->subMinutes(5),
            ],
            [
                'name' => 'Rumah Pompa Kalidami',
                'address' => 'Jl. Kalidami, Gubeng, Surabaya',
                'lat' => -7.2789,
                'lng' => 112.7681,
                'status' => 'Perlu Perhatian',
                'capacity' => '3500 m³/jam',
                'pump_count' => 6,
                'image' => 'https://images.unsplash.com/photo-1574019927486-12cf57a7f3b6?q=80&w=2070&auto=format&fit=crop',
                'built_year' => 2010,
                'manager_name' => 'Siti Rahayu',
                'contact_phone' => '081298765432',
                'contact_email' => 'siti.r@surabaya.go.id',
                'staff_count' => 8,
                'last_updated' => now()->subMinutes(15),
            ],
            [
                'name' => 'Rumah Pompa Greges',
                'address' => 'Jl. Greges, Asemrowo, Surabaya',
                'lat' => -7.2371,
                'lng' => 112.7104,
                'status' => 'Aktif',
                'capacity' => '4200 m³/jam',
                'pump_count' => 7,
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?q=80&w=2070&auto=format&fit=crop',
                'built_year' => 2012,
                'manager_name' => 'Ahmad Hidayat',
                'contact_phone' => '081387654321',
                'contact_email' => 'ahmad.h@surabaya.go.id',
                'staff_count' => 10,
                'last_updated' => now()->subMinutes(30),
            ],
            [
                'name' => 'Rumah Pompa Morokrembangan',
                'address' => 'Jl. Morokrembangan, Krembangan, Surabaya',
                'lat' => -7.2288,
                'lng' => 112.7256,
                'status' => 'Aktif',
                'capacity' => '6000 m³/jam',
                'pump_count' => 10,
                'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?q=80&w=2070&auto=format&fit=crop',
                'built_year' => 2014,
                'manager_name' => 'Dewi Lestari',
                'contact_phone' => '081345678901',
                'contact_email' => 'dewi.l@surabaya.go.id',
                'staff_count' => 15,
                'last_updated' => now()->subMinutes(45),
            ],
            [
                'name' => 'Rumah Pompa Kenjeran',
                'address' => 'Jl. Kenjeran, Bulak, Surabaya',
                'lat' => -7.2333,
                'lng' => 112.7900,
                'status' => 'Tidak Aktif',
                'capacity' => '3000 m³/jam',
                'pump_count' => 5,
                'image' => 'https://images.unsplash.com/photo-1590496794008-383c8070b257?q=80&w=2069&auto=format&fit=crop',
                'built_year' => 2008,
                'manager_name' => 'Rudi Hartono',
                'contact_phone' => '081256789012',
                'contact_email' => 'rudi.h@surabaya.go.id',
                'staff_count' => 7,
                'last_updated' => now()->subHours(1),
            ],
        ];

        foreach ($pumpHouses as $pumpHouseData) {
            PumpHouse::create($pumpHouseData);
        }

        // Create alerts
        $alerts = [
            [
                'pump_house_id' => 1,
                'title' => 'Ketinggian Air Kritis di Rumah Pompa Wonorejo',
                'description' => 'Ketinggian air telah mencapai level kritis. Pompa tambahan telah diaktifkan. Warga di sekitar area diharapkan waspada dan bersiap untuk kemungkinan evakuasi.',
                'severity' => 'Kritis',
                'water_level' => '2.8 meter (Kritis)',
                'pump_status' => '8/8 Aktif',
                'rainfall' => '45 mm/jam',
                'is_active' => true,
                'recipients' => json_encode(['admin@pumpstation.com']),
                'created_at' => now()->subHours(2),
            ],
            [
                'pump_house_id' => 2,
                'title' => 'Kerusakan Pompa di Rumah Pompa Kalidami',
                'description' => 'Pompa #3 mengalami kerusakan dan tidak dapat beroperasi. Tim teknisi sedang dalam perjalanan untuk perbaikan. Kapasitas pompa berkurang sementara.',
                'severity' => 'Peringatan',
                'water_level' => '1.5 meter (Normal)',
                'pump_status' => '5/6 Aktif',
                'rainfall' => '15 mm/jam',
                'is_active' => true,
                'recipients' => json_encode(['admin@pumpstation.com']),
                'created_at' => now()->subHours(10),
            ],
            [
                'pump_house_id' => 3,
                'title' => 'Pemeliharaan Terjadwal Rumah Pompa Greges',
                'description' => 'Pemeliharaan rutin terjadwal akan dilakukan pada tanggal 15 April 2025. Beberapa pompa akan dinonaktifkan sementara selama proses pemeliharaan.',
                'severity' => 'Informasi',
                'water_level' => '1.2 meter (Normal)',
                'pump_status' => '7/7 Aktif',
                'rainfall' => '5 mm/jam',
                'is_active' => false,
                'recipients' => json_encode(['admin@pumpstation.com']),
                'created_at' => now()->subHours(24),
            ],
            [
                'pump_house_id' => 4,
                'title' => 'Potensi Banjir di Sekitar Rumah Pompa Morokrembangan',
                'description' => 'Prakiraan cuaca menunjukkan potensi hujan lebat dalam 6 jam ke depan. Warga di sekitar area diharapkan waspada terhadap kemungkinan banjir.',
                'severity' => 'Peringatan',
                'water_level' => '1.8 meter (Waspada)',
                'pump_status' => '10/10 Aktif',
                'rainfall' => '25 mm/jam',
                'is_active' => true,
                'recipients' => json_encode(['admin@pumpstation.com']),
                'created_at' => now()->subHours(36),
            ],
            [
                'pump_house_id' => 5,
                'title' => 'Pembersihan Saluran Air Rumah Pompa Kenjeran',
                'description' => 'Pembersihan saluran air masuk sedang dilakukan untuk mengatasi penumpukan sampah. Operasional pompa tetap normal selama proses pembersihan.',
                'severity' => 'Informasi',
                'water_level' => '1.0 meter (Normal)',
                'pump_status' => '5/5 Aktif',
                'rainfall' => '10 mm/jam',
                'is_active' => false,
                'recipients' => json_encode(['admin@pumpstation.com']),
                'created_at' => now()->subHours(48),
            ],
        ];

        foreach ($alerts as $alertData) {
            Alert::create($alertData);
        }

        // Create reports
        $reports = [
            [
                'pump_house_id' => 1,
                'title' => 'Banjir di Sekitar Rumah Pompa Wonorejo',
                'description' => 'Terjadi banjir setinggi 50cm di sekitar rumah pompa. Air tidak surut selama 3 jam terakhir. Mohon segera ditindaklanjuti.',
                'status' => 'Belum Ditanggapi',
                'reporter_name' => 'Budi Santoso',
                'reporter_phone' => '081234567890',
                'reporter_email' => 'budi.s@gmail.com',
                'location' => 'Jl. Wonorejo Timur, Rungkut, Surabaya',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?q=80&w=2084&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1547683905-f686c993aae5?q=80&w=2080&auto=format&fit=crop'
                ]),
                'created_at' => now()->subHours(4),
            ],
            [
                'pump_house_id' => 2,
                'title' => 'Pompa Tidak Berfungsi di Rumah Pompa Kalidami',
                'description' => 'Salah satu pompa terlihat tidak berfungsi dan mengeluarkan suara bising. Mohon segera diperiksa untuk mencegah kerusakan lebih lanjut.',
                'status' => 'Sedang Diproses',
                'reporter_name' => 'Siti Rahayu',
                'reporter_phone' => '081298765432',
                'reporter_email' => 'siti.r@gmail.com',
                'location' => 'Jl. Kalidami, Gubeng, Surabaya',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=2069&auto=format&fit=crop'
                ]),
                'created_at' => now()->subHours(12),
            ],
            [
                'pump_house_id' => 3,
                'title' => 'Sampah Menumpuk di Saluran Air',
                'description' => 'Terdapat tumpukan sampah yang menghalangi aliran air di saluran masuk rumah pompa. Hal ini berpotensi menyebabkan banjir jika tidak segera dibersihkan.',
                'status' => 'Selesai',
                'reporter_name' => 'Ahmad Hidayat',
                'reporter_phone' => '081387654321',
                'reporter_email' => 'ahmad.h@gmail.com',
                'location' => 'Jl. Greges, Asemrowo, Surabaya',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1621451537084-482c73073a0f?q=80&w=2070&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1605600659873-d808a13e4d9a?q=80&w=2070&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?q=80&w=2070&auto=format&fit=crop'
                ]),
                'created_at' => now()->subHours(24),
            ],
            [
                'pump_house_id' => 4,
                'title' => 'Kerusakan Pagar Rumah Pompa',
                'description' => 'Pagar pembatas rumah pompa rusak dan berpotensi membahayakan warga yang melintas. Mohon segera diperbaiki.',
                'status' => 'Belum Ditanggapi',
                'reporter_name' => 'Dewi Lestari',
                'reporter_phone' => '081345678901',
                'reporter_email' => 'dewi.l@gmail.com',
                'location' => 'Jl. Morokrembangan, Krembangan, Surabaya',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1627669729462-a5cb027872db?q=80&w=2070&auto=format&fit=crop'
                ]),
                'created_at' => now()->subHours(36),
            ],
            [
                'pump_house_id' => 5,
                'title' => 'Genangan Air di Jalan Utama',
                'description' => 'Terjadi genangan air yang cukup dalam di jalan utama dekat rumah pompa. Diduga karena saluran air tersumbat.',
                'status' => 'Sedang Diproses',
                'reporter_name' => 'Rudi Hartono',
                'reporter_phone' => '081256789012',
                'reporter_email' => 'rudi.h@gmail.com',
                'location' => 'Jl. Kenjeran, Bulak, Surabaya',
                'images' => json_encode([
                    'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?q=80&w=2084&auto=format&fit=crop'
                ]),
                'created_at' => now()->subHours(48),
            ],
        ];

        foreach ($reports as $reportData) {
            Report::create($reportData);
        }

        // Create education content
        $educationContents = [
            [
                'title' => 'Mengenal Fungsi dan Cara Kerja Rumah Pompa',
                'description' => 'Artikel ini menjelaskan secara detail tentang fungsi utama rumah pompa dalam sistem pengendalian banjir di Surabaya. Pembaca akan memahami bagaimana rumah pompa bekerja untuk mengurangi risiko banjir.',
                'type' => 'Artikel',
                'content' => 'Rumah pompa merupakan infrastruktur vital dalam sistem pengendalian banjir di Surabaya. Fungsi utamanya adalah memompa air dari daerah rendah ke daerah yang lebih tinggi atau ke saluran pembuangan utama. Sistem ini bekerja secara otomatis berdasarkan sensor ketinggian air yang terpasang di berbagai titik strategis.',
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?q=80&w=2069&auto=format&fit=crop',
                'date' => now()->subDays(2),

                'created_at' => now()->subDays(2),
            ],
            [
                'title' => 'Panduan Menghadapi Banjir untuk Masyarakat',
                'description' => 'Video tutorial yang memberikan panduan praktis bagi masyarakat tentang langkah-langkah yang harus dilakukan sebelum, saat, dan setelah banjir terjadi.',
                'type' => 'Video',
                'content' => 'Video ini memberikan panduan lengkap menghadapi banjir: 1) Persiapan sebelum banjir (siapkan tas darurat, kenali jalur evakuasi), 2) Saat banjir (tetap tenang, hindari air mengalir, cari tempat tinggi), 3) Setelah banjir (periksa kerusakan, bersihkan rumah, waspadai penyakit).',
                'video_url' => 'https://www.youtube.com/watch?v=example1',
                'image' => 'https://images.unsplash.com/photo-1574019927486-12cf57a7f3b6?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(4),

                'created_at' => now()->subDays(4),
            ],
            [
                'title' => 'Peta Sebaran Rumah Pompa di Surabaya',
                'description' => 'Infografis yang menampilkan peta sebaran rumah pompa di seluruh wilayah Surabaya beserta kapasitas dan area cakupannya.',
                'type' => 'Infografis',
                'content' => 'Infografis ini menampilkan 25 rumah pompa utama di Surabaya yang tersebar di 5 wilayah: Surabaya Utara (8 unit), Surabaya Timur (6 unit), Surabaya Selatan (4 unit), Surabaya Barat (4 unit), dan Surabaya Pusat (3 unit). Total kapasitas pompa mencapai 150 m³/detik.',
                'infographic_url' => 'https://example.com/infographic1.pdf',
                'image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(6),

                'created_at' => now()->subDays(6),
            ],
            [
                'title' => 'Sejarah Pembangunan Rumah Pompa di Surabaya',
                'description' => 'Artikel yang mengulas sejarah pembangunan rumah pompa di Surabaya sejak era kolonial hingga saat ini, termasuk perkembangan teknologi yang digunakan.',
                'type' => 'Artikel',
                'content' => 'Pembangunan rumah pompa di Surabaya dimulai pada era kolonial Belanda tahun 1920-an. Awalnya menggunakan pompa manual, kemudian berkembang menjadi pompa diesel (1950-an), pompa listrik (1980-an), hingga sistem otomatis berbasis sensor modern (2000-an). Kini Surabaya memiliki 25 rumah pompa dengan teknologi terdepan.',
                'image' => 'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(9),

                'created_at' => now()->subDays(9),
            ],
            [
                'title' => 'Cara Melaporkan Masalah Banjir ke Pihak Berwenang',
                'description' => 'Video tutorial yang menjelaskan cara melaporkan masalah banjir atau kerusakan infrastruktur rumah pompa kepada pihak berwenang dengan cepat dan efektif.',
                'type' => 'Video',
                'content' => 'Tutorial melaporkan masalah banjir: 1) Gunakan aplikasi Pump Station untuk laporan cepat, 2) Sertakan foto/video kondisi, 3) Berikan lokasi yang jelas, 4) Cantumkan kontak yang bisa dihubungi, 5) Pantau status laporan melalui aplikasi. Laporan akan ditanggapi maksimal 2x24 jam.',
                'video_url' => 'https://www.youtube.com/watch?v=example2',
                'image' => 'https://images.unsplash.com/photo-1590496794008-383c8070b257?q=80&w=2069&auto=format&fit=crop',
                'date' => now()->subDays(11),

                'created_at' => now()->subDays(11),
            ],
            [
                'title' => 'Teknologi Modern dalam Sistem Rumah Pompa',
                'description' => 'Artikel yang membahas teknologi terbaru yang digunakan dalam sistem rumah pompa modern, termasuk otomatisasi dan sistem peringatan dini.',
                'type' => 'Artikel',
                'content' => 'Teknologi modern rumah pompa meliputi: 1) Sensor IoT untuk monitoring real-time, 2) Sistem otomatis berbasis AI, 3) Integrasi dengan data cuaca, 4) Aplikasi mobile untuk kontrol jarak jauh, 5) Sistem backup power otomatis, 6) Predictive maintenance menggunakan machine learning.',
                'image' => 'https://images.unsplash.com/photo-1621451537084-482c73073a0f?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(13),

                'created_at' => now()->subDays(13),
            ],
            [
                'title' => 'Dampak Perubahan Iklim terhadap Sistem Drainase Kota',
                'description' => 'Artikel yang menganalisis dampak perubahan iklim terhadap sistem drainase kota dan bagaimana rumah pompa berperan dalam adaptasi perubahan iklim.',
                'type' => 'Artikel',
                'content' => 'Perubahan iklim menyebabkan curah hujan ekstrem meningkat 30% dalam 20 tahun terakhir. Rumah pompa modern dirancang dengan kapasitas 150% dari kebutuhan normal untuk mengantisipasi hal ini. Sistem adaptasi meliputi: early warning system, kapasitas pompa yang dapat ditingkatkan, dan integrasi dengan sistem drainase kota.',
                'image' => 'https://images.unsplash.com/photo-1605600659873-d808a13e4d9a?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(16),

                'created_at' => now()->subDays(16),
            ],
            [
                'title' => 'Infografis Komponen Utama Rumah Pompa',
                'description' => 'Infografis yang menjelaskan komponen-komponen utama dalam rumah pompa beserta fungsinya masing-masing.',
                'type' => 'Infografis',
                'content' => 'Komponen utama rumah pompa: 1) Pompa sentrifugal (memompa air), 2) Motor listrik (penggerak pompa), 3) Panel kontrol (mengatur operasi), 4) Sensor level air (deteksi ketinggian), 5) Genset backup (sumber listrik cadangan), 6) Saluran inlet/outlet (jalur air masuk/keluar).',
                'infographic_url' => 'https://example.com/infographic2.pdf',
                'image' => 'https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(19),

                'created_at' => now()->subDays(19),
            ],
            [
                'title' => 'Simulasi Banjir dan Peran Rumah Pompa',
                'description' => 'Video simulasi yang menunjukkan bagaimana rumah pompa bekerja dalam mengatasi banjir dengan berbagai skenario curah hujan.',
                'type' => 'Video',
                'content' => 'Simulasi menunjukkan 3 skenario: 1) Hujan ringan (10mm/jam) - pompa standby, 2) Hujan sedang (25mm/jam) - 50% pompa aktif, 3) Hujan lebat (50mm/jam) - semua pompa aktif + sistem darurat. Video mendemonstrasikan efektivitas sistem dalam mencegah banjir pada berbagai kondisi cuaca.',
                'video_url' => 'https://www.youtube.com/watch?v=example3',
                'image' => 'https://images.unsplash.com/photo-1627669729462-a5cb027872db?q=80&w=2070&auto=format&fit=crop',
                'date' => now()->subDays(22),

                'created_at' => now()->subDays(22),
            ],
        ];

        foreach ($educationContents as $contentData) {
            EducationContent::create($contentData);
        }
    }
}
