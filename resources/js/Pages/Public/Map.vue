<template>

    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4">Peta Rumah Pompa</h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
          Lihat lokasi dan status real-time semua rumah pompa di wilayah Surabaya
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
        <Card class="p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <Home class="h-5 w-5 text-blue-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Total Rumah Pompa</p>
              <p class="text-2xl font-bold">{{ pumpHouses.length }}</p>
            </div>
          </div>
        </Card>

        <Card class="p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
              <CheckCircle class="h-5 w-5 text-green-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Status Normal</p>
              <p class="text-2xl font-bold text-green-600">{{ normalCount }}</p>
            </div>
          </div>
        </Card>

        <Card class="p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
              <AlertTriangle class="h-5 w-5 text-yellow-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Perlu Perhatian</p>
              <p class="text-2xl font-bold text-yellow-600">{{ warningCount }}</p>
            </div>
          </div>
        </Card>

        <Card class="p-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
              <AlertCircle class="h-5 w-5 text-red-600" />
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Status Kritis</p>
              <p class="text-2xl font-bold text-red-600">{{ criticalCount }}</p>
            </div>
          </div>
        </Card>
      </div>

      <!-- Map Container -->
      <div class="max-w-7xl mx-auto">
        <div class="grid gap-6 lg:grid-cols-4">
          <!-- Map -->
          <div class="lg:col-span-3">
            <Card class="overflow-hidden">
              <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold">Peta Lokasi Rumah Pompa</h2>
                  <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="refreshMap" :disabled="mapLoading">
                      <RotateCcw class="h-4 w-4 mr-2" :class="{ 'animate-spin': mapLoading }" />
                      {{ mapLoading ? 'Memuat...' : 'Refresh' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="centerMap" :disabled="!mapInitialized">
                      <MapPin class="h-4 w-4 mr-2" />
                      Pusat Peta
                    </Button>
                  </div>
                </div>
              </div>
              
              <!-- Map Error State -->
              <div v-if="mapError" class="h-[600px] w-full flex items-center justify-center bg-muted">
                <div class="text-center">
                  <AlertCircle class="h-16 w-16 text-destructive mx-auto mb-4" />
                  <h3 class="text-lg font-semibold mb-2">Gagal Memuat Peta</h3>
                  <p class="text-muted-foreground mb-4">{{ mapError }}</p>
                  <Button @click="initMap" variant="outline">
                    <RotateCcw class="h-4 w-4 mr-2" />
                    Coba Lagi
                  </Button>
                </div>
              </div>
              
              <!-- Map Loading State -->
              <div v-else-if="mapLoading" class="h-[600px] w-full flex items-center justify-center bg-muted">
                <div class="text-center">
                  <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                  <p class="text-muted-foreground">Memuat peta...</p>
                </div>
              </div>
              
              <!-- Map Container -->
              <div v-else id="map" class="h-[600px] w-full"></div>
            </Card>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Legend -->
            <Card class="p-4">
              <h3 class="font-semibold mb-4">Legenda Status</h3>
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                  <span class="text-sm">Normal</span>
                </div>
                <div class="flex items-center gap-3">
                  <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                  <span class="text-sm">Peringatan</span>
                </div>
                <div class="flex items-center gap-3">
                  <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                  <span class="text-sm">Kritis</span>
                </div>
                <div class="flex items-center gap-3">
                  <div class="w-4 h-4 bg-gray-400 rounded-full"></div>
                  <span class="text-sm">Tidak Ada Data</span>
                </div>
              </div>
            </Card>

            <!-- Selected Pump House Info -->
            <Card v-if="selectedPumpHouse" class="p-4">
              <h3 class="font-semibold mb-4">Informasi Detail</h3>
              <div class="space-y-3">
                <div>
                  <p class="text-sm text-muted-foreground">Nama</p>
                  <p class="font-medium">{{ selectedPumpHouse.name }}</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Alamat</p>
                  <p class="text-sm">{{ selectedPumpHouse.address }}</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Status Pompa</p>
                  <Badge :variant="selectedPumpHouse.status === 'Aktif' ? 'default' : 'secondary'">
                    {{ selectedPumpHouse.status }}
                  </Badge>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Jumlah Pompa</p>
                  <p class="font-medium">{{ selectedPumpHouse.pump_count }} unit</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Kapasitas</p>
                  <p class="font-medium">{{ selectedPumpHouse.capacity }} L/s</p>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground">Ketinggian Air</p>
                  <div class="flex items-center gap-2">
                    <Droplet class="h-4 w-4 text-blue-500" />
                    <span class="font-medium">{{ selectedPumpHouse.current_water_level }}m</span>
                    <Badge 
                      v-if="selectedPumpHouse.water_level_status"
                      :style="{ 
                        backgroundColor: selectedPumpHouse.water_level_status.color + '20', 
                        color: selectedPumpHouse.water_level_status.color,
                        borderColor: selectedPumpHouse.water_level_status.color 
                      }"
                      class="border text-xs"
                    >
                      {{ selectedPumpHouse.water_level_status.label }}
                    </Badge>
                  </div>
                </div>
                <div v-if="selectedPumpHouse.last_recorded">
                  <p class="text-sm text-muted-foreground">Terakhir Diperbarui</p>
                  <p class="text-sm">{{ formatTimeAgo(selectedPumpHouse.last_recorded) }}</p>
                </div>
              </div>
              
              <div class="mt-4 pt-4 border-t">
                <Button size="sm" variant="outline" as="a" :href="route('public.reports')" class="w-full">
                  <FileText class="h-4 w-4 mr-2" />
                  Laporkan Masalah
                </Button>
              </div>
            </Card>

            <!-- Quick Actions -->
            <Card class="p-4">
              <h3 class="font-semibold mb-4">Aksi Cepat</h3>
              <div class="space-y-2">
                <Button variant="outline" size="sm" as="a" :href="route('public.reports')" class="w-full justify-start">
                  <FileText class="h-4 w-4 mr-2" />
                  Buat Laporan
                </Button>
                <Button variant="outline" size="sm" as="a" :href="route('public.education')" class="w-full justify-start">
                  <BookOpen class="h-4 w-4 mr-2" />
                  Edukasi
                </Button>
                <Button variant="outline" size="sm" as="a" href="tel:112" class="w-full justify-start">
                  <Phone class="h-4 w-4 mr-2" />
                  Kontak Darurat
                </Button>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { 
  Home, 
  CheckCircle, 
  AlertTriangle, 
  AlertCircle, 
  RotateCcw, 
  MapPin, 
  Droplet,
  FileText,
  BookOpen,
  Phone
} from 'lucide-vue-next'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { useDateUtils } from '@/composables/useDateUtils'

defineOptions({ layout: PublicLayout })

// Fix untuk Leaflet default icons
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
})

const props = defineProps({
  pumpHouses: Array,
})

const selectedPumpHouse = ref(null)
const mapInitialized = ref(false)
const mapLoading = ref(false)
const mapError = ref(null)
let map = null
let markers = []

// Use composable untuk date formatting
const { formatTimeAgo } = useDateUtils()

// Computed stats
const normalCount = computed(() => 
  props.pumpHouses.filter(ph => ph.water_level_status?.level === 'normal').length
)

const warningCount = computed(() => 
  props.pumpHouses.filter(ph => 
    ph.water_level_status?.level === 'warning' || ph.water_level_status?.level === 'critical'
  ).length
)

const criticalCount = computed(() => 
  props.pumpHouses.filter(ph => ph.water_level_status?.level === 'emergency').length
)

// Initialize map
const initMap = () => {
  mapLoading.value = true
  mapError.value = null
  
  try {
    // Check if element exists
    const mapElement = document.getElementById('map')
    if (!mapElement) {
      throw new Error('Element peta tidak ditemukan')
    }

    // Initialize map centered on Surabaya
    map = L.map('map', {
      center: [-7.2575, 112.7521],
      zoom: 12,
      zoomControl: true,
      scrollWheelZoom: true,
      attributionControl: true
    })
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19,
      tileSize: 256,
      zoomOffset: 0
    }).addTo(map)
    
    // Add markers for each pump house
    addMarkers()
    
    mapInitialized.value = true
    mapLoading.value = false
  } catch (error) {
    console.error('Error initializing map:', error)
    mapError.value = 'Gagal memuat peta. Silakan refresh halaman.'
    mapLoading.value = false
  }
}

const addMarkers = () => {
  if (!map) return
  
  // Clear existing markers
  markers.forEach(marker => {
    try {
      map.removeLayer(marker)
    } catch (e) {
      console.warn('Error removing marker:', e)
    }
  })
  markers = []
  
  props.pumpHouses.forEach(pumpHouse => {
    if (pumpHouse.lat && pumpHouse.lng) {
      try {
        // Determine marker color based on water level status
        const color = getMarkerColor(pumpHouse.water_level_status?.level)
        
        // Create custom icon
        const icon = L.divIcon({
          className: 'custom-marker',
          html: `
            <div class="marker-content" style="
              width: 24px; 
              height: 24px; 
              background-color: ${color}; 
              border: 3px solid white; 
              border-radius: 50%; 
              box-shadow: 0 2px 6px rgba(0,0,0,0.3);
              position: relative;
              cursor: pointer;
            "></div>
          `,
          iconSize: [24, 24],
          iconAnchor: [12, 12],
          popupAnchor: [0, -12]
        })
        
        // Create marker
        const marker = L.marker([pumpHouse.lat, pumpHouse.lng], { icon })
          .addTo(map)
          .on('click', () => {
            selectedPumpHouse.value = pumpHouse
            // Center map on selected marker
            map.setView([pumpHouse.lat, pumpHouse.lng], Math.max(map.getZoom(), 14))
          })
        
        // Create popup content
        const popupContent = `
          <div class="pump-house-popup p-3 min-w-[250px]">
            <h3 class="font-semibold text-lg mb-2 text-gray-800">${pumpHouse.name}</h3>
            <p class="text-sm text-gray-600 mb-3">${pumpHouse.address}</p>
            
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Status Pompa:</span>
                <span class="px-2 py-1 text-xs rounded-full font-medium ${getStatusClass(pumpHouse.status)}">
                  ${pumpHouse.status}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Ketinggian Air:</span>
                <span class="font-medium text-blue-600">${pumpHouse.current_water_level || '0.00'}m</span>
              </div>
              
              ${pumpHouse.water_level_status ? `
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Status Air:</span>
                  <span class="px-2 py-1 text-xs rounded-full font-medium" style="background-color: ${color}20; color: ${color};">
                    ${pumpHouse.water_level_status.label}
                  </span>
                </div>
              ` : ''}
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Jumlah Pompa:</span>
                <span class="font-medium">${pumpHouse.pump_count || 0} unit</span>
              </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-gray-200">
              <button onclick="window.open('${route('public.reports')}', '_blank')" 
                      class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 px-3 rounded-md transition-colors">
                🏠 Laporkan Masalah
              </button>
            </div>
          </div>
        `
        
        marker.bindPopup(popupContent, {
          maxWidth: 300,
          closeButton: true,
          autoClose: false,
          closeOnEscapeKey: true
        })
        
        markers.push(marker)
      } catch (error) {
        console.error('Error creating marker for pump house:', pumpHouse.name, error)
      }
    }
  })
}

const getMarkerColor = (level) => {
  const colorMap = {
    'normal': '#22c55e',      // green
    'warning': '#f59e0b',     // yellow  
    'critical': '#ef4444',    // red
    'emergency': '#dc2626',   // dark red
  }
  return colorMap[level] || '#9ca3af' // gray default
}

const getStatusClass = (status) => {
  const statusMap = {
    'Aktif': 'bg-green-100 text-green-800',
    'Perlu Perhatian': 'bg-yellow-100 text-yellow-800',
    'Tidak Aktif': 'bg-red-100 text-red-800'
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const refreshMap = () => {
  if (map && mapInitialized.value) {
    try {
      map.invalidateSize()
      // Re-add markers with updated data
      addMarkers()
    } catch (error) {
      console.error('Error refreshing map:', error)
    }
  } else {
    // Reinitialize map if not initialized
    initMap()
  }
}

const centerMap = () => {
  if (map && mapInitialized.value) {
    try {
      map.setView([-7.2575, 112.7521], 12)
      selectedPumpHouse.value = null
    } catch (error) {
      console.error('Error centering map:', error)
    }
  }
}

onMounted(async () => {
  // Ensure DOM is ready
  await nextTick()
  
  // Add small delay to ensure container is properly sized
  setTimeout(() => {
    initMap()
  }, 100)
})
</script>

<style>
/* Map container styles */
#map {
  background-color: #f0f0f0;
  z-index: 1;
}

/* Custom marker styles */
.custom-marker {
  background: transparent !important;
  border: none !important;
}

.marker-content {
  transition: transform 0.2s ease-in-out;
}

.marker-content:hover {
  transform: scale(1.1);
}

/* Leaflet popup styles */
.leaflet-popup-content-wrapper {
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  border: none;
}

.leaflet-popup-content {
  margin: 0;
  font-family: inherit;
  line-height: 1.5;
}

.leaflet-popup-tip {
  background: white;
  border: none;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.pump-house-popup {
  max-width: 300px;
}

.pump-house-popup h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.pump-house-popup p {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.pump-house-popup .space-y-2 > * + * {
  margin-top: 0.5rem;
}

.pump-house-popup button {
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.375rem;
  transition: all 0.2s ease-in-out;
}

.pump-house-popup button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Leaflet control styles */
.leaflet-control-zoom a {
  color: #374151 !important;
  background-color: white !important;
  border: 1px solid #d1d5db !important;
}

.leaflet-control-zoom a:hover {
  background-color: #f9fafb !important;
  color: #1f2937 !important;
}

.leaflet-control-attribution {
  background: rgba(255, 255, 255, 0.9) !important;
  font-size: 11px !important;
}

/* Responsive styles */
@media (max-width: 768px) {
  #map {
    height: 400px !important;
  }
  
  .pump-house-popup {
    max-width: 250px;
  }
  
  .leaflet-popup-content-wrapper {
    border-radius: 8px;
  }
}

/* Loading animation */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Ensure map tiles load properly */
.leaflet-tile-pane {
  filter: none;
}

.leaflet-tile {
  max-width: none !important;
  max-height: none !important;
}
</style> 
 