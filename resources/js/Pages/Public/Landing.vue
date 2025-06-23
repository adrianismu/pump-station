<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
      <!-- Background Image -->
      <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://pbs.twimg.com/media/D7uytYAUwAAiUu3?format=jpg&name=large')"
      ></div>
      
      <!-- Overlay for better text readability -->
      <div class="absolute inset-0 bg-gradient-to-br from-blue-900/70 via-blue-800/60 to-cyan-900/70"></div>
      
      <!-- Content -->
      <div class="relative z-10 max-w-7xl mx-auto px-4 py-20 md:py-32">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16 items-center">
          <!-- Hero Content -->
          <div class="space-y-6">
            <div class="space-y-4">
              <h1 class="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl text-white">
                Sistem Monitoring
                <span class="text-cyan-300">Rumah Pompa</span>
              </h1>
              <p class="text-xl text-blue-100 max-w-lg">
                Platform terpadu untuk monitoring rumah pompa, pelaporan kondisi, dan edukasi pencegahan banjir untuk masyarakat Surabaya.
              </p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4">
              <Button size="lg" as="a" :href="route('public.reports')" class="text-lg px-8 bg-white text-blue-900 hover:bg-blue-50 border-0 font-semibold shadow-lg">
                <FileText class="mr-2 h-5 w-5" />
                Buat Laporan
              </Button>
              <Button size="lg" variant="outline" as="a" :href="route('public.map')" class="text-lg px-8 bg-white text-blue-900 hover:bg-blue-50 border-0 font-semibold shadow-lg">
                <Map class="mr-2 h-5 w-5" />
                Lihat Peta
              </Button>
            </div>
          </div>

          <!-- Hero Visual -->
          <div class="relative">
            <div class="grid grid-cols-2 gap-4">
              <Card class="p-6 bg-white/10 text-white backdrop-blur-md border border-white/20 shadow-xl hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-white/80">Total Rumah Pompa</p>
                    <p class="text-3xl font-bold text-white">{{ stats.total_pump_houses }}</p>
                  </div>
                  <div class="bg-white/20 p-2 rounded-lg">
                    <Home class="h-8 w-8 text-white" />
                  </div>
                </div>
              </Card>
              
              <Card class="p-6 bg-white/10 text-white backdrop-blur-md border border-white/20 shadow-xl hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-white/80">Pompa Aktif</p>
                    <p class="text-3xl font-bold text-white">{{ stats.total_pumps }}</p>
                  </div>
                  <div class="bg-green-500/30 p-2 rounded-lg">
                    <Activity class="h-8 w-8 text-green-200" />
                  </div>
                </div>
              </Card>
              
              <Card class="p-6 bg-white/10 text-white backdrop-blur-md border border-white/20 shadow-xl hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-white/80">Laporan Minggu Ini</p>
                    <p class="text-3xl font-bold text-white">{{ stats.recent_reports }}</p>
                  </div>
                  <div class="bg-orange-500/30 p-2 rounded-lg">
                    <FileText class="h-8 w-8 text-orange-200" />
                  </div>
                </div>
              </Card>
              
              <Card class="p-6 bg-white/10 text-white backdrop-blur-md border border-white/20 shadow-xl hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-white/80">Status Aktif</p>
                    <p class="text-3xl font-bold text-white">{{ stats.active_pump_houses }}</p>
                  </div>
                  <div class="bg-purple-500/30 p-2 rounded-lg">
                    <CheckCircle class="h-8 w-8 text-purple-200" />
                  </div>
                </div>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Weather Forecast Section -->
    <section class="py-20 bg-gradient-to-br from-blue-50 to-cyan-50">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold mb-4">Prakiraan Cuaca Rumah Pompa</h2>
          <p class="text-xl text-muted-foreground">
            Pantau kondisi cuaca di lokasi rumah pompa untuk antisipasi banjir
          </p>
        </div>

        <!-- Weather Loading State -->
        <div v-if="isLoadingWeather" class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
          <p class="text-muted-foreground">Mengambil data cuaca...</p>
        </div>

        <!-- Weather Cards -->
        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 max-w-7xl mx-auto">
          <Card 
            v-for="pumpHouse in pumpHousesWithWeather.slice(0, 8)" 
            :key="pumpHouse.id"
            class="overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-105"
          >
            <div class="p-6">
              <!-- Header -->
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="font-semibold text-sm line-clamp-1">{{ pumpHouse.name }}</h3>
                  <p class="text-xs text-muted-foreground">{{ pumpHouse.location }}</p>
                </div>
                <div class="flex items-center">
                  <component 
                    v-if="pumpHouse.weather?.icon"
                    :is="getWeatherIconComponent(pumpHouse.weather.icon)" 
                    class="h-8 w-8 text-blue-600"
                  />
                  <Cloud v-else class="h-8 w-8 text-gray-400" />
                </div>
              </div>

              <!-- Current Weather -->
              <div v-if="pumpHouse.weather" class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-2xl font-bold">{{ Math.round(pumpHouse.weather.temperature) }}°C</span>
                  <Badge :variant="getRainfallBadgeVariant(pumpHouse.weather.rainfall)">
                    {{ pumpHouse.weather.precipitationFormatted }}
                  </Badge>
                </div>
                
                <div class="text-sm text-muted-foreground">
                  <p>{{ pumpHouse.weather.description }}</p>
                  <p>{{ pumpHouse.weather.precipitationIntensity }}</p>
                  <p>Kelembaban: {{ pumpHouse.weather.humidity }}%</p>
                </div>

                <!-- 3 Hour Forecast -->
                <div class="border-t pt-3">
                  <p class="text-xs font-medium text-muted-foreground mb-2">3 Jam Mendatang</p>
                  <div class="flex justify-between text-xs">
                    <div 
                      v-for="(hour, index) in pumpHouse.weather.hourly?.slice(1, 4)" 
                      :key="index"
                      class="text-center"
                    >
                      <p class="text-muted-foreground">{{ formatHour(index + 1) }}</p>
                      <component 
                        :is="getWeatherIconComponent(getWeatherIcon(hour.weather_code))" 
                        class="h-4 w-4 mx-auto my-1 text-blue-500"
                      />
                      <p class="font-medium">{{ Math.round(hour.temperature_2m) }}°</p>
                      <p class="text-blue-600">{{ hour.precipitationFormatted || formatRainfall(hour.precipitation || 0) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Risk Level -->
                <div class="flex items-center justify-between pt-2 border-t">
                  <span class="text-xs text-muted-foreground">Risiko Banjir:</span>
                  <Badge :variant="getFloodRiskVariant(pumpHouse.weather.floodRisk)">
                    {{ pumpHouse.weather.floodRisk }}
                  </Badge>
                </div>
              </div>

              <!-- Weather Error State -->
              <div v-else class="text-center py-4">
                <Cloud class="h-12 w-12 text-gray-300 mx-auto mb-2" />
                <p class="text-sm text-muted-foreground">Data cuaca tidak tersedia</p>
              </div>
            </div>
          </Card>
        </div>

        <!-- View All Weather Button -->
        <div class="text-center mt-8">
          <Button as="a" :href="route('public.map')" size="lg" variant="outline">
            <Map class="mr-2 h-5 w-5" />
            Lihat Semua di Peta
          </Button>
        </div>
      </div>
    </section>

    <!-- Recent Alerts Section -->
    <section v-if="recentAlerts.length > 0" class="py-20">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold mb-4">Peringatan Terkini</h2>
          <p class="text-xl text-muted-foreground">
            Informasi terbaru tentang kondisi rumah pompa
          </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
          <Card 
            v-for="alert in recentAlerts.slice(0, 3)" 
            :key="alert.id"
            class="p-6"
          >
            <div class="flex items-start gap-4">
              <div :class="[
                'w-10 h-10 rounded-lg flex items-center justify-center',
                alert.severity === 'Kritis' ? 'bg-red-100' : 
                alert.severity === 'Peringatan' ? 'bg-yellow-100' : 'bg-blue-100'
              ]">
                <AlertCircle v-if="alert.severity === 'Kritis'" class="h-5 w-5 text-red-600" />
                <AlertTriangle v-else-if="alert.severity === 'Peringatan'" class="h-5 w-5 text-yellow-600" />
                <Info v-else class="h-5 w-5 text-blue-600" />
              </div>
              <div class="flex-1">
                <h3 class="font-semibold mb-1">{{ alert.title }}</h3>
                <p class="text-sm text-muted-foreground mb-2">{{ alert.pump_house_name }}</p>
                <p class="text-sm">{{ alert.description }}</p>
                <p class="text-xs text-muted-foreground mt-2">
                  {{ formatTimeAgo(alert.created_at) }}
                </p>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </section>

    <!-- Education Preview -->
    <section class="py-20 bg-muted/30">
      <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold mb-4">Konten Edukasi Terbaru</h2>
          <p class="text-xl text-muted-foreground">
            Pelajari lebih lanjut tentang pencegahan banjir dan rumah pompa
          </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
          <Card 
            v-for="content in educationContent.slice(0, 3)" 
            :key="content.id"
            class="overflow-hidden hover:shadow-lg transition-shadow"
          >
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <Badge :variant="content.type === 'artikel' ? 'default' : content.type === 'video' ? 'secondary' : 'outline'">
                  {{ content.type }}
                </Badge>
              </div>
              <h3 class="font-semibold mb-2 line-clamp-2">{{ content.title }}</h3>
              <p class="text-sm text-muted-foreground mb-4 line-clamp-3">
                {{ content.content.substring(0, 150) }}...
              </p>
              <Button variant="outline" size="sm" as="a" :href="route('public.education.detail', content.id)">
                Baca Selengkapnya
              </Button>
            </div>
          </Card>
        </div>

        <div class="text-center mt-8">
          <Button as="a" :href="route('public.education')" size="lg">
            Lihat Semua Konten Edukasi
          </Button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { 
  FileText, 
  Map, 
  BookOpen, 
  Bell, 
  Home, 
  Activity, 
  CheckCircle,
  AlertCircle,
  AlertTriangle,
  Info,
  Cloud,
  Sun,
  CloudRain,
  CloudSnow,
  CloudLightning,
  CloudDrizzle,
  CloudSun,
  CloudFog
} from 'lucide-vue-next'
import { 
  getWeatherData, 
  getWeatherDataPublic,
  getWeatherDescription, 
  formatRainfall, 
  getRainfallIntensity,
  getWeatherIcon 
} from '@/services/weatherService'

defineOptions({ layout: PublicLayout })

const props = defineProps({
  pumpHouses: Array,
  recentAlerts: Array,
  educationContent: Array,
  stats: Object,
})

const isLoadingWeather = ref(true)
const pumpHousesWithWeather = ref([])

const formatTimeAgo = (dateString) => {
  if (!dateString) return "Tidak ada data"
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return `${diffInSeconds} detik yang lalu`
  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) return `${diffInMinutes} menit yang lalu`
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours} jam yang lalu`
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 30) return `${diffInDays} hari yang lalu`
  const diffInMonths = Math.floor(diffInDays / 30)
  return `${diffInMonths} bulan yang lalu`
}

const getWeatherIconComponent = (iconName) => {
  const iconMap = {
    'Sun': Sun,
    'Cloud': Cloud,
    'CloudRain': CloudRain,
    'CloudSnow': CloudSnow,
    'CloudLightning': CloudLightning,
    'CloudDrizzle': CloudDrizzle,
    'CloudSun': CloudSun,
    'CloudFog': CloudFog,
  }
  return iconMap[iconName] || Cloud
}

const getRainfallBadgeVariant = (rainfall) => {
  if (rainfall > 20) return 'destructive'
  if (rainfall > 10) return 'secondary'
  if (rainfall > 4) return 'outline'
  return 'default'
}

const getFloodRiskVariant = (risk) => {
  if (risk === 'Tinggi') return 'destructive'
  if (risk === 'Sedang') return 'secondary'
  return 'default'
}

const calculateFloodRisk = (rainfall, weatherCode) => {
  // Calculate flood risk based on rainfall and weather conditions
  if (rainfall > 20 || [95, 96, 99].includes(weatherCode)) return 'Tinggi'
  if (rainfall > 10 || [80, 81, 82].includes(weatherCode)) return 'Sedang'
  return 'Rendah'
}

const formatHour = (hoursFromNow) => {
  const now = new Date()
  const futureTime = new Date(now.getTime() + hoursFromNow * 60 * 60 * 1000)
  return futureTime.toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false 
  })
}

const fetchWeatherForPumpHouses = async () => {
  if (!props.pumpHouses?.length) {
    isLoadingWeather.value = false
    return
  }

  isLoadingWeather.value = true
  const weatherPromises = props.pumpHouses.map(async (pumpHouse) => {
    try {
      // Use public weather endpoint for landing page (note: PumpHouse uses lat/lng columns)
      const weatherData = await getWeatherDataPublic(pumpHouse.lat, pumpHouse.lng)
      if (weatherData) {
        const currentWeather = weatherData.current
        const rainfall = currentWeather.precipitation || currentWeather.rain || 0
        
        return {
          ...pumpHouse,
          weather: {
            temperature: currentWeather.temperature_2m || 0,
            temperature_2m: currentWeather.temperature_2m || 0,
            humidity: currentWeather.relative_humidity_2m || 0,
            rainfall: rainfall,
            // Use backend-provided formatted precipitation data
            precipitationFormatted: currentWeather.precipitation_formatted || formatRainfall(rainfall),
            precipitationIntensity: currentWeather.precipitation_intensity || getRainfallIntensity(rainfall),
            // Use backend-provided descriptions and icons or fallback to local functions
            description: currentWeather.weather_description || getWeatherDescription(currentWeather.weather_code),
            icon: currentWeather.weather_icon || getWeatherIcon(currentWeather.weather_code),
            floodRisk: weatherData.flood_risk || calculateFloodRisk(rainfall, currentWeather.weather_code),
            hourly: weatherData.hourly ? weatherData.hourly.time.slice(0, 4).map((time, index) => ({
              time,
              temperature_2m: weatherData.hourly.temperature_2m[index] || 0,
              precipitation: weatherData.hourly.precipitation[index] || 0,
              precipitationFormatted: weatherData.hourly.precipitation_formatted?.[index] || formatRainfall(weatherData.hourly.precipitation[index] || 0),
              weather_code: weatherData.hourly.weather_code[index]
            })) : []
          }
        }
      }
    } catch (error) {
      console.error(`Failed to fetch weather for ${pumpHouse.name}:`, error)
    }
    
    return {
      ...pumpHouse,
      weather: null
    }
  })

  try {
    const results = await Promise.all(weatherPromises)
    pumpHousesWithWeather.value = results
  } catch (error) {
    console.error('Failed to fetch weather data:', error)
    pumpHousesWithWeather.value = props.pumpHouses.map(pumpHouse => ({
      ...pumpHouse,
      weather: null
    }))
  } finally {
    isLoadingWeather.value = false
  }
}

onMounted(() => {
  fetchWeatherForPumpHouses()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 
 