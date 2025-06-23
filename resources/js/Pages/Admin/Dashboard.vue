<template>
  <div>
    <h1>
      <CardTitle class="mb-6"></CardTitle>
    </h1>

    <div v-if="loading" class="flex justify-center items-center h-64">
      <!-- <Spinner class="w-12 h-12 text-primary" /> -->
    </div>

    <template v-else>
      <!-- Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <Card>
          <CardHeader>
            <CardTitle>Total Rumah Pompa</CardTitle>
          </CardHeader>
          <CardContent class="flex justify-between items-center">
            <div>
              <h2 class="text-3xl font-bold">{{ dashboardData.totalPumpHouses }}</h2>
              <p class="text-xs text-muted-foreground flex items-center mt-2">
                <CheckCircle class="w-4 h-4 text-success mr-1" />
                {{ dashboardData.activePumpHouses }} sedang aktif
              </p>
            </div>
            <div class="bg-primary/10 p-2 rounded-full">
              <Home class="w-5 h-5 text-primary" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Pompa Aktif</CardTitle>
          </CardHeader>
          <CardContent class="flex justify-between items-center">
            <div>
              <h2 class="text-3xl font-bold">{{ dashboardData.activePumps }}</h2>
              <p class="text-xs text-muted-foreground flex items-center mt-2">
                <CheckCircle class="w-4 h-4 text-success mr-1" />
                {{ Math.round((dashboardData.activePumps / dashboardData.totalPumps) * 100) }}% dari total pompa
              </p>
            </div>
            <div class="bg-success/10 p-2 rounded-full">
              <Activity class="w-5 h-5 text-success" />
            </div>
          </CardContent>
        </Card>   

        <Card>
          <CardHeader>
            <CardTitle>Status Air Normal</CardTitle>
          </CardHeader>
          <CardContent class="flex justify-between items-center">
            <div>
              <h2 class="text-3xl font-bold text-success">{{ waterLevelStats.normal }}</h2>
              <p class="text-xs text-muted-foreground flex items-center mt-2">
                <CheckCircle class="w-4 h-4 text-success mr-1" />
                Ketinggian air normal
              </p>
            </div>
            <div class="bg-success/10 p-2 rounded-full">
              <Droplet class="w-5 h-5 text-success" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Status Peringatan</CardTitle>
          </CardHeader>
          <CardContent class="flex justify-between items-center">
            <div>
              <h2 class="text-3xl font-bold text-yellow-600">{{ waterLevelStats.warning + waterLevelStats.critical }}</h2>
              <p class="text-xs text-muted-foreground flex items-center mt-2">
                <AlertTriangle class="w-4 h-4 text-yellow-600 mr-1" />
                Perlu perhatian
              </p>
            </div>
            <div class="bg-yellow-100 p-2 rounded-full">
              <AlertTriangle class="w-5 h-5 text-yellow-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Status Darurat</CardTitle>
          </CardHeader>
          <CardContent class="flex justify-between items-center">
            <div>
              <h2 class="text-3xl font-bold text-red-600">{{ waterLevelStats.emergency }}</h2>
              <p class="text-xs text-muted-foreground flex items-center mt-2">
                <AlertCircle class="w-4 h-4 text-red-600 mr-1" />
                Tindakan segera
              </p>
            </div>
            <div class="bg-red-100 p-2 rounded-full">
              <AlertCircle class="w-5 h-5 text-red-600" />
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Flood Risk Card -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              <span>Monitoring Cuaca Rumah Pompa</span>
              <div class="flex gap-2">
                <Badge v-if="!weatherLoading" variant="outline">
                  {{ atRiskPumpHouses.length }} Total
                </Badge>
                <Badge v-if="!weatherLoading && highRiskCount > 0" variant="destructive">
                  {{ highRiskCount }} Risiko Tinggi
                </Badge>
                <Badge v-if="!weatherLoading && mediumRiskCount > 0" variant="secondary">
                  {{ mediumRiskCount }} Risiko Sedang
                </Badge>
              </div>
            </CardTitle>
            <p class="text-sm text-muted-foreground">Data cuaca real-time dengan prioritas pada lokasi berisiko banjir</p>
          </CardHeader>
          <CardContent class="space-y-4 min-h-[400px]">
            <div v-if="weatherLoading" class="flex items-center justify-center h-40 text-muted-foreground">
              <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-2"></div>
                <p>Mengambil data cuaca real-time...</p>
              </div>
            </div>
            <div v-else-if="weatherError" class="flex flex-col items-center justify-center h-40 text-destructive">
              <AlertCircle class="w-12 h-12 mb-2" />
              <p class="font-medium">{{ weatherError }}</p>
              <Button @click="fetchWeatherData" variant="outline" size="sm" class="mt-2">
                <Wind class="w-4 h-4 mr-2" />
                Coba Lagi
              </Button>
            </div>
            <template v-else>
              <div v-if="paginatedAtRiskPumpHouses.length > 0">
                <div class="flex flex-col gap-4 mb-4">
                  <div v-for="pump in paginatedAtRiskPumpHouses" :key="pump.id" class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6 border rounded-lg border-border p-4 hover:bg-muted/30 transition-colors"
                    :class="{
                      'border-red-200 bg-red-50/30 dark:border-red-800/50 dark:bg-red-900/10': pump.risk === 'Tinggi',
                      'border-yellow-200 bg-yellow-50/30 dark:border-yellow-800/50 dark:bg-yellow-900/10': pump.risk === 'Sedang',
                      'border-green-200 bg-green-50/30 dark:border-green-800/50 dark:bg-green-900/10': pump.risk === 'Rendah'
                    }"
                  >
                    <component :is="getWeatherComponent(pump.weatherIcon)" class="w-16 h-16 text-primary shrink-0 mx-auto sm:mx-0 " />
                    <div class="flex-1 w-full text-center sm:text-left">
                      <div class="flex gap-2 items-end">
                        <span class="text-3xl font-bold">{{ Math.round(pump.temperature) }}&deg;C</span>
                        <span class="text-muted-foreground text-lg">{{ pump.weatherDescription }}</span>
                      </div>
                      <div class="flex gap-4 mt-2 text-sm text-muted-foreground">
                        <span><Wind class="inline w-4 h-4 mr-1" />{{ pump.windSpeed || 0 }} km/j</span>
                        <span><Droplet class="inline w-4 h-4 mr-1" />{{ pump.humidity }}&#37;</span>
                      </div>
                      <div v-if="pump.precipitation !== undefined" class="flex gap-4 mt-2 text-xs">
                        <span class="text-muted-foreground">
                          <CloudRain class="inline w-3 h-3 mr-1" />{{ pump.precipitationFormatted || pump.precipitation?.toFixed(1) + ' mm' || '0 mm' }}
                        </span>
                        <span class="text-muted-foreground">{{ pump.rainfallIntensity }}</span>
                        <Badge 
                          :variant="getFloodRiskBadgeVariant(pump.risk)" 
                          class="px-2 py-0.5 font-semibold"
                        >
                          Risiko {{ pump.risk }}
                        </Badge>
                      </div>
                      <div class="mt-2 text-xs flex gap-2 items-center">
                        <div class="flex items-center gap-1">
                          <AlertTriangle v-if="pump.risk === 'Tinggi'" class="w-3 h-3 text-red-600" />
                          <AlertCircle v-else-if="pump.risk === 'Sedang'" class="w-3 h-3 text-yellow-600" />
                          <span><b>{{ pump.name }}</b></span>
                        </div>
                        <span class="text-muted-foreground">— {{ pump.address }}</span>
                      </div>
                      <div class="flex items-center justify-between mt-3 pt-2 border-t">
                        <div class="flex items-center gap-2 text-xs">
                          <span class="text-muted-foreground">Tinggi Air:</span>
                          <span class="font-medium">{{ pump.current_water_level || 0 }}m</span>
                          <Badge 
                            v-if="pump.water_level_status"
                            :style="{ backgroundColor: pump.water_level_status.color + '20', color: pump.water_level_status.color, borderColor: pump.water_level_status.color }"
                            class="border text-xs"
                          >
                            {{ pump.water_level_status.label }}
                          </Badge>
                        </div>
                        <span class="text-xs text-muted-foreground">
                          {{ pump.last_recorded ? formatTimeAgo(pump.last_recorded) : 'Tidak ada data' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Pagination Controls -->
                <div v-if="totalPages > 1" class="flex justify-center mt-6">
                  <Pagination
                    :items-per-page="itemsPerPage"
                    :total="atRiskPumpHouses.length"
                    :sibling-count="1"
                    show-edges
                    :default-page="currentPage"
                    v-model:page="currentPage"
                  >
                    <template v-slot:default="{ page }">
                      <PaginationList class="flex items-center gap-1">
                        <PaginationFirst />
                        <PaginationPrev />
                        <template v-for="(item, index) in page.items" :key="index">
                          <PaginationListItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            as-child
                          >
                            <Button
                              class="w-9 h-9 p-0"
                              :variant="item.value === page.current ? 'default' : 'outline'"
                            >
                              {{ item.value }}
                            </Button>
                          </PaginationListItem>
                          <PaginationEllipsis
                            v-else
                            :index="index"
                          />
                        </template>
                        <PaginationNext />
                        <PaginationLast />
                      </PaginationList>
                    </template>
                  </Pagination>
                </div>
                
                <!-- Sorting Info -->
                <div class="text-xs text-muted-foreground text-center mt-2">
                  <Info class="inline w-3 h-3 mr-1" />
                  Data diurutkan berdasarkan prioritas risiko banjir dan intensitas curah hujan
                </div>
              </div>
              <div v-else class="text-muted-foreground text-center py-8">
                <Cloud class="w-12 h-12 mx-auto mb-2 text-blue-500" />
                <p class="font-medium">Data cuaca tidak tersedia</p>
                <p class="text-sm mt-1">Belum ada data cuaca untuk rumah pompa</p>
              </div>
            </template>
          </CardContent>
        </Card>

        <!-- Public Reports Card -->
        <Card class="flex flex-col">
          <CardHeader>
            <CardTitle class="flex items-center justify-between">
              <span>Laporan Publik</span>
              <div class="flex gap-2">
                <Badge v-if="dashboardData.recentReports.length > 0" variant="outline" class="text-xs">
                  {{ dashboardData.recentReports.length }} Terbaru
                </Badge>
                <Badge v-if="pendingReportsCount > 0" class="text-xs bg-red-100 border border-red-300 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300 font-medium">
                  {{ pendingReportsCount }} Belum Ditanggapi
                </Badge>
              </div>
            </CardTitle>
            <p class="text-sm text-muted-foreground">Laporan dari masyarakat tentang kondisi rumah pompa</p>
          </CardHeader>
          <CardContent class="space-y-3 flex-1">
            <template v-if="dashboardData.recentReports.length > 0">
              <div 
                v-for="report in dashboardData.recentReports" 
                :key="report.id"
                :class="['rounded-md p-3 flex items-start gap-3', getReportCardClass(report.status)]"
              >
                <CheckCircle v-if="report.status === 'Ditanggapi'" class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" />
                <AlertTriangle v-else-if="report.status === 'Dalam Proses'" class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" />
                <AlertCircle v-else class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" />
                <div class="flex-1">
                  <p class="font-medium text-sm">{{ report.title }}</p>
                  <p class="text-xs text-muted-foreground">{{ report.pump_house?.name || 'Lokasi tidak diketahui' }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Pelapor: {{ report.reporter_name }}</p>
                  <p v-if="report.description" class="text-xs text-muted-foreground mt-1 line-clamp-2">
                    {{ report.description.length > 80 ? report.description.substring(0, 80) + '...' : report.description }}
                  </p>
                  <div class="flex items-center justify-between mt-2">
                    <Badge 
                      :class="getReportStatusClass(report.status)"
                      class="text-xs font-medium"
                    >
                      {{ report.status }}
                    </Badge>
                    <span class="text-xs text-muted-foreground">{{ formatTimeAgo(report.created_at) }}</span>
                  </div>
                </div>
              </div>
            </template>
            <template v-else>
              <div class="text-center py-6 text-muted-foreground">
                <FileText class="w-12 h-12 mx-auto mb-2" />
                <p class="font-medium">Tidak ada laporan terkini</p>
                <p class="text-sm mt-1">Belum ada laporan dari masyarakat</p>
              </div>
            </template>
          </CardContent>
          <CardFooter class="mt-auto pt-4 border-t border-border">
            <Link :href="route('admin.reports')" class="text-primary block text-center py-2 text-sm w-full hover:underline font-medium hover:text-primary/80 transition-colors">
              Lihat Semua Laporan
            </Link>
          </CardFooter>
        </Card>
      </div>
      
      <!-- Latest Pump Houses Table -->
      <Card class="mt-6">
        <CardHeader>
          <CardTitle>Rumah Pompa Terbaru</CardTitle>
          <Link :href="route('admin.database')" class="text-primary text-sm">Lihat Semua</Link>
        </CardHeader>
        <CardContent class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Nama</TableHead>
                <TableHead>Lokasi</TableHead>
                <TableHead>Ketinggian Air</TableHead>
                <TableHead>Status Air</TableHead>
                <TableHead>Status Pompa</TableHead>
                <TableHead>Terakhir Diperbarui</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="pumpHouse in paginatedPumpHouses" :key="pumpHouse.id" class="hover:bg-muted/50">
                <TableCell>
                  <Link :href="route('admin.database.show', pumpHouse.id)" class="hover:text-primary">
                    {{ pumpHouse.name }}
                  </Link>
                </TableCell>
                <TableCell>{{ pumpHouse.address }}</TableCell>
                <TableCell>
                  <div class="flex items-center gap-2">
                    <Droplet class="w-4 h-4 text-blue-500" />
                    <span class="font-medium">{{ pumpHouse.current_water_level || 0 }}m</span>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge 
                    v-if="pumpHouse.water_level_status"
                    :style="{ backgroundColor: pumpHouse.water_level_status.color + '20', color: pumpHouse.water_level_status.color, borderColor: pumpHouse.water_level_status.color }"
                    class="border"
                  >
                    {{ pumpHouse.water_level_status.label }}
                  </Badge>
                  <Badge v-else variant="secondary">
                    Tidak ada data
                  </Badge>
                </TableCell>
                <TableCell>
                  <Badge :variant="getPumpHouseStatusVariant(pumpHouse.status)">
                    {{ pumpHouse.status }}
                  </Badge>
                </TableCell>
                <TableCell class="text-sm text-muted-foreground">
                  {{ pumpHouse.last_recorded ? formatTimeAgo(pumpHouse.last_recorded) : formatTimeAgo(pumpHouse.last_updated) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
        <CardFooter class="flex flex-col gap-4">
          <!-- Pagination Info -->
          <div class="text-sm text-muted-foreground text-center">
            Menampilkan
            {{ pumpHouses.length === 0 ? 0 : (currentPageLatest - 1) * itemsPerPageLatest + 1 }} -
            {{ Math.min(currentPageLatest * itemsPerPageLatest, pumpHouses.length) }} dari {{ pumpHouses.length }} rumah pompa
          </div>

          <!-- Pagination Controls -->
          <div v-if="totalPagesLatest > 1" class="flex justify-center">
            <Pagination
              :items-per-page="itemsPerPageLatest"
              :total="pumpHouses.length"
              :sibling-count="1"
              show-edges
              :default-page="currentPageLatest"
              v-model:page="currentPageLatest"
            >
              <template v-slot:default="{ page }">
                <PaginationList class="flex items-center gap-1">
                  <PaginationFirst />
                  <PaginationPrev />
                  <template v-for="(item, index) in page.items" :key="index">
                    <PaginationListItem 
                      v-if="item.type === 'page'" 
                      :value="item.value" 
                      as-child
                    >
                      <Button 
                        class="w-9 h-9 p-0" 
                        :variant="item.value === page.current ? 'default' : 'outline'"
                      >
                        {{ item.value }}
                      </Button>
                    </PaginationListItem>
                    <PaginationEllipsis v-else :index="index" />
                  </template>
                  <PaginationNext />
                  <PaginationLast />
                </PaginationList>
              </template>
            </Pagination>
          </div>
        </CardFooter>
      </Card>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue"
import { Link } from "@inertiajs/vue3"

import { 
  Home, Activity, AlertTriangle, TrendingUp, CheckCircle, AlertCircle, Info,
  Wind, Droplet, CloudRain, Sun, Cloud, CloudSun, CloudFog, CloudDrizzle, CloudSnow, CloudLightning, FileText
} from "lucide-vue-next"

import {
  Card,
  CardHeader,
  CardContent,
  CardFooter,
  CardTitle,
} from "@/Components/ui/card"

import {
  Table,
  TableHead,
  TableBody,
  TableRow,
  TableCell,
  TableHeader,
} from "@/Components/ui/table"

import { Badge } from "@/Components/ui/badge"

import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/Components/ui/pagination'

import { Button } from '@/Components/ui/button'

import Layout from "@/Layouts/AdminLayout.vue"
import { getWeatherData, getRainfallIntensity, getWeatherDescription, getWeatherIcon, calculateFloodRisk, getFloodRiskVariant } from "@/services/weatherService"
import { useDateUtils } from "@/composables/useDateUtils"
import { useStatusUtils } from "@/composables/useStatusUtils"
import { useIconMapping } from "@/composables/useIconMapping"

defineOptions({ layout: Layout })

const props = defineProps({
  dashboardData: Object,
  pumpHouses: Array,
  waterLevelStats: Object,
})

const loading = ref(false)
const weatherLoading = ref(true)
const weatherError = ref(null)
const weatherDataMap = ref({})

// Use composables untuk utility functions
const { formatTimeAgo } = useDateUtils()
const { getPumpHouseStatusVariant } = useStatusUtils()
const { getWeatherIcon: getWeatherIconName } = useIconMapping()

// Computed properties
const pendingReportsCount = computed(() => {
  if (!props.dashboardData?.recentReports) return 0
  return props.dashboardData.recentReports.filter(report => report.status === "Belum Ditanggapi").length
})

const atRiskPumpHouses = computed(() => {
  const allPumpHousesWithWeather = props.pumpHouses.map(ph => {
    const weather = weatherDataMap.value[ph.id] || {}
    const precipitation = typeof weather.precipitation === 'number' ? weather.precipitation : 0
    const weatherCode = weather.weather_code
    // Use backend flood risk if available, otherwise calculate locally
    const risk = weather.flood_risk || calculateFloodRisk(precipitation, weatherCode)
    const rainfallIntensity = getRainfallIntensity(precipitation)
    
    return {
      ...ph,
      temperature: weather.temperature_2m || 0,
      humidity: weather.relative_humidity_2m || 0,
      precipitation,
      precipitationFormatted: weather.precipitation_formatted || formatRainfall(precipitation),
      windSpeed: weather.wind_speed_10m || 0,
      // Use backend-provided descriptions and icons or fallback to local functions
      weatherDescription: weather.weather_description || (weatherCode ? getWeatherDescription(weatherCode) : "-"),
      weatherIcon: weather.weather_icon || getWeatherIcon(weatherCode) || "Cloud",
      risk,
      rainfallIntensity: weather.precipitation_intensity || getRainfallIntensity(precipitation),
      // Add priority score for sorting
      riskPriority: risk === 'Tinggi' ? 3 : risk === 'Sedang' ? 2 : 1
    }
  })
  
  // Sort by risk priority (high risk first), then by precipitation amount
  return allPumpHousesWithWeather.sort((a, b) => {
    if (a.riskPriority !== b.riskPriority) {
      return b.riskPriority - a.riskPriority // Higher priority first
    }
    return b.precipitation - a.precipitation // Higher precipitation first within same risk level
  })
})

// Count of different risk levels
const highRiskCount = computed(() => 
  atRiskPumpHouses.value.filter(ph => ph.risk === 'Tinggi').length
)

const mediumRiskCount = computed(() => 
  atRiskPumpHouses.value.filter(ph => ph.risk === 'Sedang').length
)

// Pagination for at risk pump houses
const currentPage = ref(1)
const itemsPerPage = 4
const totalPages = computed(() => Math.max(1, Math.ceil(atRiskPumpHouses.value.length / itemsPerPage)))

const paginatedAtRiskPumpHouses = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return atRiskPumpHouses.value.slice(start, start + itemsPerPage)
})

// Pagination for latest pump houses
const currentPageLatest = ref(1)
const itemsPerPageLatest = 7
const totalPagesLatest = computed(() => Math.max(1, Math.ceil(props.pumpHouses.length / itemsPerPageLatest)))

const paginatedPumpHouses = computed(() => {
  const start = (currentPageLatest.value - 1) * itemsPerPageLatest
  return props.pumpHouses.slice(start, start + itemsPerPageLatest)
})

// Utility functions
const getWeatherComponent = (iconName) => {
  return getWeatherIconName(iconName)
}

const getFloodRiskBadgeVariant = (risk) => {
  return getFloodRiskVariant(risk)
}

const getReportStatusClass = (status) => {
  switch (status) {
    case 'Ditanggapi':
      return 'bg-green-100 border border-green-300 text-green-800 dark:bg-green-900/20 dark:border-green-700 dark:text-green-300'
    case 'Dalam Proses':
      return 'bg-yellow-100 border border-yellow-300 text-yellow-800 dark:bg-yellow-900/20 dark:border-yellow-700 dark:text-yellow-300'
    case 'Belum Ditanggapi':
      return 'bg-red-100 border border-red-300 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300'
    default:
      return 'bg-gray-100 border border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300'
  }
}

const getReportCardClass = (status) => {
  switch (status) {
    case 'Ditanggapi':
      return 'bg-green-50/70 border border-green-200 dark:bg-green-900/10 dark:border-green-800/50'
    case 'Dalam Proses':
      return 'bg-yellow-50/70 border border-yellow-200 dark:bg-yellow-900/10 dark:border-yellow-800/50'
    case 'Belum Ditanggapi':
      return 'bg-red-50/70 border border-red-200 dark:bg-red-900/10 dark:border-red-800/50'
    default:
      return 'bg-gray-50/70 border border-gray-200 dark:bg-gray-800/30 dark:border-gray-700/50'
  }
}

// Weather data fetching
const fetchWeatherData = async () => {
  weatherLoading.value = true
  weatherError.value = null
  
  try {
    const results = await Promise.all(
      props.pumpHouses.map(async (ph) => {
        try {
          if (ph.lat && ph.lng) {
            const res = await getWeatherData(ph.lat, ph.lng)
            // Combine current weather data with backend enhancements
            const currentWeather = res?.current
            if (currentWeather) {
              // Include flood_risk from the main response data
              currentWeather.flood_risk = res?.flood_risk
            }
            return { id: ph.id, weather: currentWeather }
          }
        } catch {}
        return { id: ph.id, weather: null }
      })
    )
    
    const obj = {}
    results.forEach(({ id, weather }) => { obj[id] = weather })
    weatherDataMap.value = obj
  } catch {
    weatherError.value = "Gagal memuat data cuaca rumah pompa"
  } finally {
    weatherLoading.value = false
  }
}

// Real-time Updates Implementation Example
const autoRefresh = ref(true)
const refreshInterval = ref(null)

// Auto-refresh setiap 30 detik
const startAutoRefresh = () => {
  if (autoRefresh.value) {
    refreshInterval.value = setInterval(() => {
      fetchWeatherData()
      // Refresh dashboard data juga bisa ditambahkan
    }, 30000) // 30 seconds
  }
}

const stopAutoRefresh = () => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
    refreshInterval.value = null
  }
}

onMounted(() => {
  fetchWeatherData()
  startAutoRefresh()
})

onUnmounted(() => {
  stopAutoRefresh()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
