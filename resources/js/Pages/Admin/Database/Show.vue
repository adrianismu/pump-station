<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold">{{ pumpHouse.name }}</h1>
        <p class="text-muted-foreground mt-2">{{ pumpHouse.address }}</p>
      </div>
      <div class="flex items-center gap-4">
        <Button variant="outline" as="a" :href="route('admin.map')">
          <ArrowLeft class="mr-2 h-4 w-4" />
          Kembali ke Peta
        </Button>
      </div>
    </div>

    <!-- Status Alert -->
    <Alert v-if="pumpHouse.status === 'Tidak Aktif'" variant="destructive">
      <AlertTriangle class="h-4 w-4" />
      <AlertTitle>Rumah Pompa Tidak Aktif</AlertTitle>
      <AlertDescription>
        Rumah pompa ini sedang dalam status tidak aktif. Beberapa fitur mungkin terbatas.
      </AlertDescription>
    </Alert>

    <!-- Status Alert for Perlu Perhatian -->
    <Alert v-if="pumpHouse.status === 'Perlu Perhatian'" variant="warning">
      <AlertTriangle class="h-4 w-4" />
      <AlertTitle>Rumah Pompa Perlu Perhatian</AlertTitle>
      <AlertDescription>
        Rumah pompa ini memerlukan perhatian khusus. Mohon segera lakukan pemeriksaan.
      </AlertDescription>
    </Alert>

    <!-- Main Content -->
    <div class="grid gap-8 lg:grid-cols-3">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Pump House Header -->
        <Card class="overflow-hidden">
          <div class="relative h-64">
            <img :src="pumpHouse.image || '/images/default-pump-house.jpg'" alt="Pump House" class="h-full w-full object-cover" />
            <div class="absolute top-4 right-4">
              <Badge :variant="pumpHouse.status === 'Aktif' ? 'default' : 'secondary'">
                {{ pumpHouse.status }}
              </Badge>
            </div>
          </div>
          <CardContent class="p-6">
            <h2 class="text-2xl font-semibold mb-2">{{ pumpHouse.name }}</h2>
            <p class="text-sm text-muted-foreground mb-4">{{ pumpHouse.address }}</p>
            <Separator class="my-4" />
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div class="bg-muted/50 p-3 rounded-md">
                <p class="text-xs text-muted-foreground mb-1">Kapasitas</p>
                <p class="font-medium">{{ pumpHouse.capacity }}</p>
              </div>
              <div class="bg-muted/50 p-3 rounded-md">
                <p class="text-xs text-muted-foreground mb-1">Jumlah Pompa</p>
                <p class="font-medium">{{ pumpHouse.pump_count }} unit</p>
              </div>
                              <div class="bg-muted/50 p-3 rounded-md">
                  <p class="text-xs text-muted-foreground mb-1">Pompa Aktif</p>
                  <p class="font-medium">{{ pumpHouse.active_pumps || 0 }}/{{ pumpHouse.pump_count || 1 }}</p>
              </div>
              <div class="bg-muted/50 p-3 rounded-md">
                <p class="text-xs text-muted-foreground mb-1">Tahun Dibangun</p>
                <p class="font-medium">{{ pumpHouse.built_year }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Water Level Chart -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle class="flex items-center gap-2">
                <Droplet class="h-5 w-5" />
                Grafik Ketinggian Air
              </CardTitle>
              
              <!-- Time Range Filter -->
              <div class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground mr-2">Periode:</span>
                <div class="flex border rounded-md p-1">
                  <button
                    v-for="filter in timeFilters"
                    :key="filter.value"
                    @click="selectedTimeFilter = filter.value"
                    :class="[
                      'px-3 py-1 text-xs font-medium rounded transition-colors',
                      selectedTimeFilter === filter.value
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                    ]"
                  >
                    {{ filter.label }}
                  </button>
                </div>
              </div>
            </div>
            
            <div class="flex items-center gap-2 mt-2">
              <div class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground">Ketinggian saat ini:</span>
                <Badge :variant="getCurrentWaterLevelStatus().variant" class="text-sm">
                  {{ currentWaterLevel }}m - {{ getCurrentWaterLevelStatus().label }}
                </Badge>
              </div>
              <div class="flex items-center gap-2 ml-4">
                <span class="text-sm text-muted-foreground">Data terakhir:</span>
                <span class="text-sm">{{ lastUpdate }}</span>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="waterLevelHistory && waterLevelHistory.length > 0">
              <!-- Loading state untuk data yang sedang difilter -->
              <div v-if="isLoadingFilteredData" class="flex items-center justify-center h-64">
                <Loader2 class="h-8 w-8 animate-spin text-primary" />
                <span class="ml-2 text-sm text-muted-foreground">Memuat data...</span>
              </div>
              
              <!-- Chart dengan data yang difilter -->
              <div v-else>
                <WaterLevelChart 
                  :data="filteredWaterLevelData" 
                  :thresholds="activeThresholds"
                  :height="300"
                  class="mb-4"
                />
                
                <!-- Chart Info -->
                <div class="flex items-center justify-between text-xs text-muted-foreground mb-4">
                  <div class="flex items-center gap-4">
                    <span>{{ filteredWaterLevelData.length }} data points</span>
                    <span v-if="filteredWaterLevelData.length === 0" class="text-amber-600">
                      Tidak ada data untuk periode ini
                    </span>
                  </div>
                  <span>{{ getTimeRangeText() }}</span>
                </div>
              </div>
              
              <!-- Actions -->
              <div class="flex gap-2 pt-4 border-t">
                <Link 
                  :href="route('admin.water-level.create', { pump_house_id: pumpHouse.id })"
                  class="flex-1"
                >
                  <Button class="w-full">
                    <Plus class="h-4 w-4 mr-2" />
                    Input Data Ketinggian Air
                  </Button>
                </Link>
                <Link :href="route('admin.water-level.index', { pump_house_id: pumpHouse.id })">
                  <Button variant="outline">
                    <BarChart3 class="h-4 w-4 mr-2" />
                    Lihat Semua Data
                  </Button>
                </Link>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <BarChart3 class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
              <h3 class="text-lg font-semibold mb-2">Belum Ada Data Ketinggian Air</h3>
              <p class="text-muted-foreground mb-4">
                Mulai input data ketinggian air untuk melihat grafik dan analisis.
              </p>
              <Link :href="route('admin.water-level.create', { pump_house_id: pumpHouse.id })">
                <Button>
                  <Plus class="h-4 w-4 mr-2" />
                  Input Data Pertama
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>

        <!-- Pump Status -->
        <Card>
          <CardHeader>
            <CardTitle>Status Pompa dan Ketinggian Air</CardTitle>
          </CardHeader>
          <CardContent>
            <!-- Status Cards -->
            <div class="grid gap-4 md:grid-cols-3 mb-6">
              <div class="border rounded-lg p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <Droplet class="h-5 w-5 text-blue-600" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-muted-foreground">Ketinggian Air</p>
                    <p class="text-2xl font-bold">{{ currentWaterLevel }}m</p>
                    <p class="text-xs text-muted-foreground">Saat ini</p>
                  </div>
                </div>
              </div>

              <div class="border rounded-lg p-4">
                <div class="flex items-center gap-3">
                  <div 
                    :class="[
                      'w-10 h-10 rounded-lg flex items-center justify-center',
                      getStatusColor(waterLevelStatus?.level)
                    ]"
                  >
                    <AlertCircle class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-muted-foreground">Status Air</p>
                    <p class="text-lg font-semibold">{{ waterLevelStatus?.label || 'Normal' }}</p>
                    <p class="text-xs text-muted-foreground">{{ waterLevelStatus?.description || 'Ketinggian normal' }}</p>
                  </div>
                </div>
              </div>

              <div class="border rounded-lg p-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <Power class="h-5 w-5 text-green-600" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-muted-foreground">Status Pompa</p>
                    <p class="text-2xl font-bold">{{ pumpHouse.active_pumps || 0 }}/{{ pumpHouse.pump_count || 1 }}</p>
                    <p class="text-xs text-muted-foreground">{{ getPumpStatusText() }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pump Control -->
            <div class="border rounded-lg p-4">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h4 class="font-medium">Kontrol Pompa</h4>
                  <p class="text-sm text-muted-foreground">Kelola status pompa aktif secara real-time</p>
                </div>
              </div>
              
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                  <Button 
                    variant="outline" 
                    size="sm"
                    @click="decrementPumps"
                    :disabled="(pumpHouse.active_pumps || 0) <= 0 || pumpStatusForm.processing"
                  >
                    <span class="text-lg font-mono">-</span>
                  </Button>
                  
                  <div class="text-center px-4">
                    <p class="text-2xl font-bold">{{ pumpHouse.active_pumps || 0 }}</p>
                    <p class="text-xs text-muted-foreground">Aktif</p>
                  </div>
                  
                  <Button 
                    variant="outline" 
                    size="sm"
                    @click="incrementPumps"
                    :disabled="(pumpHouse.active_pumps || 0) >= (pumpHouse.pump_count || 1) || pumpStatusForm.processing"
                  >
                    <span class="text-lg font-mono">+</span>
                  </Button>
                </div>
                
                <div class="text-sm text-muted-foreground">
                  dari {{ pumpHouse.pump_count || 1 }} pompa total
                </div>
                
                <div v-if="pumpStatusForm.processing" class="flex items-center gap-2 text-sm text-muted-foreground">
                  <Loader2 class="h-4 w-4 animate-spin" />
                  Memperbarui...
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- WEATHER CARD -->
        <Card>
          <CardHeader>
            <CardTitle>Cuaca Sekitar Rumah Pompa</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="weatherLoading" class="flex items-center justify-center h-40">
              <Loader2 class="h-8 w-8 animate-spin text-primary" />
            </div>
            <div v-else-if="weatherError" class="flex items-center justify-center h-40 text-destructive">
              {{ weatherError }}
            </div>
            <div v-else-if="getCurrentWeather" class="mb-4 flex gap-4 items-center">
              <component
                :is="getWeatherComponent(getCurrentWeather.weatherIcon)"
                class="w-16 h-16 text-primary shrink-0"
              />
              <div>
                <div class="flex items-end gap-2">
                  <span class="text-4xl font-bold">{{ Math.round(getCurrentWeather.temperature) }}&deg;C</span>
                  <span class="text-muted-foreground text-lg">{{ getCurrentWeather.weatherDescription }}</span>
                </div>
                <div class="flex gap-4 mt-2 text-sm text-muted-foreground">
                  <span><Wind class="inline w-4 h-4 mr-1" />{{ getCurrentWeather.windSpeed || 0 }} km/j</span>
                  <span><Droplet class="inline w-4 h-4 mr-1" />{{ getCurrentWeather.humidity }}&#37;</span>
                </div>
                <div v-if="getCurrentWeather.precipitation !== undefined" class="flex gap-2 mt-1 text-xs text-muted-foreground">
                  <span><CloudRain class="inline w-3 h-3 mr-1" /> {{ getCurrentWeather.precipitation_formatted || (getCurrentWeather.precipitation + ' mm') || '0 mm' }}</span>
                  <span v-if="getCurrentWeather.rain"><span>Hujan: {{ getCurrentWeather.rain }} mm</span></span>
                </div>
              </div>
            </div>
            <div v-else class="text-muted-foreground text-center py-8">Tidak ada data cuaca tersedia.</div>

            <!-- Prakiraan 3 Hari -->
            <template v-if="getDailyForecast && getDailyForecast.length">
              <h4 class="font-semibold mt-5 mb-2">Prakiraan 3 Hari</h4>
              <div class="space-y-2">
                <div
                  v-for="(item, idx) in getDailyForecast.slice(0, 3)"
                  :key="idx"
                  class="flex items-center gap-3 p-2 rounded-md hover:bg-muted/50 transition"
                >
                  <component
                    :is="getWeatherComponent(item.weatherIcon)"
                    class="w-9 h-9 text-primary"
                  />
                  <div class="flex-1">
                    <div class="font-medium">{{ formatDay(item.date) }}</div>
                    <div class="text-xs text-muted-foreground">{{ item.weatherDescription }}</div>
                  </div>
                  <div class="flex flex-col text-xs text-right font-mono">
                    <span>Max: {{ Math.round(item.maxTemp) }}&deg;C</span>
                    <span>Min: {{ Math.round(item.minTemp) }}&deg;C</span>
                  </div>
                </div>
              </div>
            </template>
          </CardContent>
        </Card>

        <!-- Maintenance History -->
        <!-- <Card>
          <CardHeader>
            <CardTitle>Riwayat Pemeliharaan</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="pumpHouse.maintenanceHistory && pumpHouse.maintenanceHistory.length">
              <div class="relative pl-6 pb-2">
                <div class="absolute top-0 left-0 h-full w-px bg-border"></div>
                <div
                  v-for="(event, index) in pumpHouse.maintenanceHistory"
                  :key="index"
                  class="relative mb-6"
                >
                  <div class="absolute -left-6 top-0 flex items-center justify-center w-4 h-4 rounded-full bg-primary z-10"></div>
                  <div class="mb-1 flex items-center justify-between">
                    <p class="text-sm font-medium">{{ event.title }}</p>
                    <p class="text-xs text-muted-foreground">{{ formatDate(event.date) }}</p>
                  </div>
                  <p class="text-sm text-muted-foreground">{{ event.description }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Oleh: {{ event.by }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-muted-foreground py-8">Tidak ada riwayat pemeliharaan.</div>
          </CardContent>
        </Card> -->
      </div>

      <!-- Right Column -->
      <div class="space-y-6">
        <!-- Location Info -->
        <Card>
          <CardHeader>
            <CardTitle>Informasi Lokasi</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <Label class="text-sm font-medium">Alamat</Label>
              <p class="text-sm text-muted-foreground mt-1">{{ pumpHouse.address }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label class="text-sm font-medium">Latitude</Label>
                <p class="text-sm text-muted-foreground mt-1">{{ pumpHouse.lat }}</p>
              </div>
              <div>
                <Label class="text-sm font-medium">Longitude</Label>
                <p class="text-sm text-muted-foreground mt-1">{{ pumpHouse.lng }}</p>
              </div>
            </div>
            
            <!-- Map -->
            <div class="mt-4">
              <Label class="text-sm font-medium mb-2 block">Lokasi di Peta</Label>
              <div class="h-48 rounded-md overflow-hidden">
                <div id="detail-map" class="h-full w-full bg-muted"></div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Quick Actions -->
        <Card>
          <CardHeader>
            <CardTitle>Aksi Cepat</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button 
              as="a" 
              :href="route('admin.database.edit', pumpHouse.id)" 
              class="w-full justify-start"
            >
              <Edit class="mr-2 h-4 w-4" />
              Edit Informasi
            </Button>
            <Button 
              variant="outline" 
              as="a" 
              :href="route('admin.water-level.create', { pump_house_id: pumpHouse.id })" 
              class="w-full justify-start"
            >
              <Plus class="mr-2 h-4 w-4" />
              Input Ketinggian Air
            </Button>
            <Button 
              variant="outline" 
              as="a" 
              :href="route('admin.water-level.history', pumpHouse.id)" 
              class="w-full justify-start"
            >
              <History class="mr-2 h-4 w-4" />
              Lihat Riwayat Lengkap
            </Button>
            <Button 
              variant="outline" 
              as="a" 
              :href="route('admin.pump-house-thresholds.edit', pumpHouse.id)" 
              class="w-full justify-start"
            >
              <Settings class="mr-2 h-4 w-4" />
              Atur Threshold
            </Button>
          </CardContent>
        </Card>

 
      </div>
    </div>
  </div>


</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'
import { Separator } from '@/Components/ui/separator'
import {
  ArrowLeft,
  Droplet,
  Plus,
  BarChart3,
  MapPin,
  AlertCircle,
  Sun,
  Cloud,
  CloudSun,
  CloudFog,
  CloudDrizzle,
  CloudRain,
  CloudSnow,
  CloudLightning,
  Loader2,
  Power,
  Edit,
  AlertTriangle,
  History,
  Settings,
  Wind
} from 'lucide-vue-next'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix untuk Leaflet default icons
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
})

import { getWeatherData, getWeatherDataForPumpHouse, getWeatherDescription, formatRainfall, getRainfallIntensity } from '@/services/weatherService'
import WaterLevelChart from '@/Components/Charts/WaterLevelChart.vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { useToast } from '@/Components/ui/toast'
import { useDateUtils } from '@/composables/useDateUtils'
import { useStatusUtils } from '@/composables/useStatusUtils'
import { useIconMapping } from '@/composables/useIconMapping'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  pumpHouse: {
    type: Object,
    required: true
  },
  waterLevelHistory: {
    type: Array,
    default: () => []
  },
  activeThresholds: {
    type: Array,
    default: () => []
  },
  canEdit: {
    type: Boolean,
    default: false
  }
})

const { pumpHouse, canEdit } = props

// Use composables
const { formatTimeAgo, formatDateTime } = useDateUtils()
const { getPumpHouseStatusVariant } = useStatusUtils()
const { getWeatherIcon: getWeatherComponent } = useIconMapping()

// Toast for notifications
const { toast } = useToast()

// Forms
const showEditModal = ref(false)
const editForm = useForm({
  name: pumpHouse.name,
  capacity: pumpHouse.capacity,
  address: pumpHouse.address,
  pump_count: pumpHouse.pump_count,
  built_year: pumpHouse.built_year,
  status: pumpHouse.status,
  lat: pumpHouse.lat,
  lng: pumpHouse.lng,
  description: pumpHouse.description || ''
})

const toggleForm = useForm({})

// Pump status form
const pumpStatusForm = useForm({
  active_pumps: pumpHouse.active_pumps || 0
})

// Submit functions
const submitEdit = () => {
  editForm.put(route('admin.database.update', pumpHouse.id), {
    onSuccess: () => {
      showEditModal.value = false
    }
  })
}

const toggleStatus = () => {
  const newStatus = pumpHouse.status === 'Aktif' ? 'Tidak Aktif' : 'Aktif'
  
  toggleForm.put(route('admin.database.toggle-status', pumpHouse.id), {
    data: {
      status: newStatus
    },
    preserveState: false
  })
}

// Pump status functions
const incrementPumps = () => {
  const currentActive = pumpHouse.active_pumps || 0
  const totalPumps = pumpHouse.pump_count || 1
  
  if (currentActive < totalPumps) {
    updatePumpStatus(currentActive + 1)
  }
}

const decrementPumps = () => {
  const currentActive = pumpHouse.active_pumps || 0
  
  if (currentActive > 0) {
    updatePumpStatus(currentActive - 1)
  }
}

const updatePumpStatus = (newActivePumps) => {
  pumpStatusForm.active_pumps = newActivePumps
  
  pumpStatusForm.put(route('admin.database.update-pump-status', pumpHouse.id), {
    onSuccess: (page) => {
      // Update the local pump house data
      pumpHouse.active_pumps = newActivePumps
      // Reset form processing state
      pumpStatusForm.reset('active_pumps')
      pumpStatusForm.active_pumps = newActivePumps
      
      // Show success toast
      toast({
        title: "Berhasil",
        description: "Status pompa berhasil diperbarui",
      })
    },
    onError: (errors) => {
      console.error('Error updating pump status:', errors)
      // Reset form processing state on error
      pumpStatusForm.active_pumps = pumpHouse.active_pumps || 0
      
      // Show error toast
      const errorMessage = errors.active_pumps || 'Terjadi kesalahan saat memperbarui status pompa'
      toast({
        title: "Error",
        description: errorMessage,
        variant: "destructive",
      })
    },
    preserveState: true,
    preserveScroll: true
  })
}

const weatherData = ref(null)
const weatherLoading = ref(true)
const weatherError = ref(null)

// Time filter state
const selectedTimeFilter = ref('7d')
const isLoadingFilteredData = ref(false)

// Time filter options
const timeFilters = [
  { label: '24H', value: '24h' },
  { label: '7D', value: '7d' },
  { label: '1M', value: '1m' }
]

const currentWaterLevel = computed(() => {
  if (props.waterLevelHistory && props.waterLevelHistory.length > 0) {
    return parseFloat(props.waterLevelHistory[0].water_level).toFixed(2)
  }
  return '0.00'
})

const activeThresholds = computed(() => {
  return props.activeThresholds || []
})

const getCurrentWaterLevelStatus = () => {
  const waterLevel = parseFloat(currentWaterLevel.value)
  const thresholds = activeThresholds.value
  
  if (thresholds.length === 0) {
    return { label: 'Normal', variant: 'default' }
  }
  
  // Find the highest threshold that is exceeded
  const exceededThreshold = thresholds
    .filter(t => waterLevel >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0]
  
  if (!exceededThreshold) {
    return { label: 'Normal', variant: 'default' }
  }
  
  const variantMap = {
    'low': 'default',
    'medium': 'secondary', 
    'high': 'destructive',
    'critical': 'destructive'
  }
  
  return {
    label: exceededThreshold.label || exceededThreshold.name,
    variant: variantMap[exceededThreshold.severity] || 'default'
  }
}

// Filtered water level data based on selected time range
const filteredWaterLevelData = computed(() => {
  if (!props.waterLevelHistory || props.waterLevelHistory.length === 0) {
    return []
  }
  
  const now = new Date()
  let cutoffDate
  
  switch (selectedTimeFilter.value) {
    case '24h':
      cutoffDate = new Date(now.getTime() - 24 * 60 * 60 * 1000)
      break
    case '7d':
      cutoffDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
      break
    case '1m':
      cutoffDate = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
      break
    default:
      cutoffDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
  }
  
  return props.waterLevelHistory.filter(record => {
    const recordDate = new Date(record.recorded_at)
    return recordDate >= cutoffDate
  })
})

// Get time range text for display
const getTimeRangeText = () => {
  const filter = timeFilters.find(f => f.value === selectedTimeFilter.value)
  return `Data ${filter?.label || '7D'} terakhir`
}

// Watch for time filter changes to show loading state
watch(selectedTimeFilter, (newValue, oldValue) => {
  if (newValue !== oldValue) {
    // Simulate loading state for smooth UX
    isLoadingFilteredData.value = true
    setTimeout(() => {
      isLoadingFilteredData.value = false
    }, 300)
  }
})

onMounted(() => {
  // Initialize map
  console.log('Initializing map for pump house:', pumpHouse.name, 'at coordinates:', pumpHouse.lat, pumpHouse.lng);
  
  try {
    // Check if map container exists
    const mapContainer = document.getElementById("detail-map");
    if (!mapContainer) {
      console.error('Map container not found');
      return;
    }
    
    console.log('Map container found, dimensions:', mapContainer.offsetWidth, 'x', mapContainer.offsetHeight);
    
  const map = L.map("detail-map").setView([pumpHouse.lat, pumpHouse.lng], 15)

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map)

    // Add marker for the pump house with explicit icon configuration
    const marker = L.marker([pumpHouse.lat, pumpHouse.lng], {
      icon: L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
      })
    })
    .addTo(map)
    .bindPopup(`<b>${pumpHouse.name}</b><br>${pumpHouse.status}`)

    console.log('Marker added successfully');
    
    // Force map to refresh after a short delay
    setTimeout(() => {
      map.invalidateSize();
      console.log('Map refreshed');
    }, 100);

  } catch (error) {
    console.error('Error initializing map:', error);
  }

  // Fetch weather data
  fetchWeatherData()
})

// Alias untuk consistency dengan composables
const formatDate = formatDateTime

const fetchWeatherData = async () => {
  weatherLoading.value = true
  weatherError.value = null
  
  try {
    // Use general weather endpoint which doesn't require auth
    const data = await getWeatherData(pumpHouse.lat, pumpHouse.lng)
    
    if (!data) {
      throw new Error("No weather data received")
    }
    
    weatherData.value = data
  } catch (error) {
    console.error("Error fetching weather data:", error)
    weatherError.value = "Gagal memuat data cuaca"
  } finally {
    weatherLoading.value = false
  }
}

const getCurrentWeather = computed(() => {
  if (!weatherData.value || !weatherData.value.current) return null
  
  const current = weatherData.value.current
  return {
    temperature: current.temperature_2m || 0,
    humidity: current.relative_humidity_2m || 0,
    precipitation: current.precipitation || 0,
    precipitation_formatted: current.precipitation_formatted || formatRainfall(current.precipitation || 0),
    precipitation_intensity: current.precipitation_intensity || getRainfallIntensity(current.precipitation || 0),
    rain: current.rain || 0,
    rain_formatted: current.rain_formatted || formatRainfall(current.rain || 0),
    weatherCode: current.weather_code,
    windSpeed: current.wind_speed_10m || 0,
    // Use backend-provided descriptions and icons or fallback to local functions
    weatherDescription: current.weather_description || getWeatherDescription(current.weather_code),
    weatherIcon: current.weather_icon || 'Cloud',
    // Include flood risk from backend if available
    floodRisk: weatherData.value.flood_risk
  }
})

const getDailyForecast = computed(() => {
  if (!weatherData.value || !weatherData.value.daily) return []
  
  return weatherData.value.daily.time.map((time, index) => {
    const weatherCode = weatherData.value.daily.weather_code[index]
    return {
      date: new Date(time),
      maxTemp: weatherData.value.daily.temperature_2m_max[index] || 0,
      minTemp: weatherData.value.daily.temperature_2m_min[index] || 0,
      precipitationSum: weatherData.value.daily.precipitation_sum[index] || 0,
      precipitationProbability: weatherData.value.daily.precipitation_probability_max[index] || 0,
      weatherCode: weatherCode,
      // Use backend-provided descriptions and icons arrays or fallback to local functions
      weatherDescription: weatherData.value.daily.weather_descriptions?.[index] || getWeatherDescription(weatherCode),
      weatherIcon: weatherData.value.daily.weather_icons?.[index] || 'Cloud'
    }
  })
})

const formatDay = (date) => {
  return date.toLocaleDateString('id-ID', { weekday: 'long' })
}

// Use composable for status variant
const getStatusVariant = getPumpHouseStatusVariant

const lastUpdate = computed(() => {
  const latest = props.waterLevelHistory?.[0]
  if (!latest) return 'Tidak ada data'
  
  return formatTimeAgo(latest.recorded_at)
})

// Water level status computed property
const waterLevelStatus = computed(() => {
  const waterLevel = parseFloat(currentWaterLevel.value)
  const thresholds = activeThresholds.value
  
  if (thresholds.length === 0) {
    return {
      level: 'normal',
      label: 'Normal',
      description: 'Ketinggian air dalam batas normal'
    }
  }
  
  // Find the highest threshold that is exceeded
  const exceededThreshold = thresholds
    .filter(t => waterLevel >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0]
  
  if (!exceededThreshold) {
    return {
      level: 'normal',
      label: 'Normal',
      description: 'Ketinggian air dalam batas normal'
    }
  }
  
  return {
    level: exceededThreshold.severity || 'medium',
    label: exceededThreshold.label || exceededThreshold.name,
    description: exceededThreshold.description || 'Status berdasarkan threshold'
  }
})

// Status color function
const getStatusColor = (level) => {
  const colorMap = {
    'normal': 'bg-green-100 text-green-600',
    'low': 'bg-green-100 text-green-600',
    'medium': 'bg-yellow-100 text-yellow-600',
    'high': 'bg-red-100 text-red-600',
    'critical': 'bg-red-100 text-red-600',
  }
  return colorMap[level] || 'bg-green-100 text-green-600'
}

// Pump status text function
const getPumpStatusText = () => {
  const activePumps = pumpHouse.active_pumps || 0
  const totalPumps = pumpHouse.pump_count || 1
  
  if (activePumps === 0) {
    return 'Semua pompa tidak aktif'
  } else if (activePumps === totalPumps) {
    return 'Semua pompa aktif'
  } else {
    return `${activePumps} dari ${totalPumps} pompa aktif`
  }
}
</script>

<style scoped>
/* Map container styles */
#detail-map {
  background-color: #f0f0f0;
  min-height: 200px;
  z-index: 1;
}

/* Ensure map tiles load properly */
.leaflet-tile-pane {
  filter: none;
}

.leaflet-tile {
  max-width: none !important;
  max-height: none !important;
}

/* Leaflet marker styles */
.leaflet-marker-icon {
  background-image: url('https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png') !important;
  background-size: 25px 41px !important;
  background-repeat: no-repeat !important;
}

.leaflet-marker-shadow {
  background-image: url('https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png') !important;
  background-size: 41px 41px !important;
  background-repeat: no-repeat !important;
}

/* Leaflet popup styles */
.leaflet-popup-content-wrapper {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.leaflet-popup-content {
  margin: 8px 12px;
  font-family: inherit;
  line-height: 1.4;
}

.leaflet-popup-tip {
  background: white;
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style> 