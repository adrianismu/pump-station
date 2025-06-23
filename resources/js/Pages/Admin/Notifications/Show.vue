<template>
    <div>
      <div class="flex items-center gap-2 mb-6 no-print">
        <Button variant="outline" size="icon" @click="goBack">
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <h1 class="text-2xl font-bold">Detail Notifikasi</h1>
      </div>
  
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Notification Header -->
          <Card>
            <CardHeader>
              <div class="flex justify-between items-start">
                <div>
                  <StatusBadge 
                    :level="alert.severity"
                    type="notification-severity"
                    class="mb-2"
                  />
                  <CardTitle class="text-2xl">{{ alert.title }}</CardTitle>
                  <CardDescription>
                    <div class="flex items-center gap-2 mt-1">
                      <MapPin class="h-4 w-4 text-muted-foreground" />
                      <span>{{ alert.pump_house?.name || 'Sistem Umum' }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                      <Calendar class="h-4 w-4 text-muted-foreground" />
                      <span>{{ formatDateTime(alert.created_at) }}</span>
                    </div>
                  </CardDescription>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <p class="text-sm mb-6">{{ alert.description }}</p>
            </CardContent>
          </Card>

          <!-- Status Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Current Water Level Card -->
            <Card class="status-card">
              <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">Ketinggian Saat Ini</CardTitle>
              </CardHeader>
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

            <!-- Water Status Card -->
            <Card class="status-card">
              <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">Status Air</CardTitle>
              </CardHeader>
              <CardContent>
                <div class="flex items-center gap-3">
                  <Droplets 
                    class="w-5 h-5"
                    :style="{ color: waterLevelStatusInfo.color }"
                  />
                  <div>
                    <div class="text-lg font-semibold" :style="{ color: waterLevelStatusInfo.color }">
                      {{ waterLevelStatusInfo.text }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                      {{ waterLevelStatusInfo.description }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Chart Section -->
          <ChartWithTimeFilter
            :data="waterLevelHistory"
            :thresholds="activeThresholds"
            :title="`Grafik Ketinggian Air - ${alert.pump_house?.name || 'Sistem'}`"
            :description="`Visualisasi data ketinggian air untuk ${alert.pump_house?.name || 'sistem'}`"
            :height="320"
            :show-threshold-toggle="true"
            filter-label="Periode"
            :time-filters="[
              { label: '24H', value: '24h' },
              { label: '7D', value: '7d' },
              { label: '1M', value: '1m' },
              { label: '2M', value: '2m' },
              { label: '3M', value: '3m' }
            ]"
          />
  
          <!-- Location Map -->
          <Card v-if="alert.pump_house?.lat && alert.pump_house?.lng">
            <CardHeader>
              <CardTitle>Lokasi</CardTitle>
              <CardDescription>{{ alert.pump_house?.location || alert.pump_house?.address }}</CardDescription>
            </CardHeader>
            <CardContent>
              <div class="h-[300px] rounded-md overflow-hidden mb-2">
                <div id="notification-map" class="h-full w-full bg-muted"></div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Notification Info Card -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Informasi Notifikasi</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Tingkat Keparahan:</span>
                  <StatusBadge 
                    :level="alert.severity"
                    type="notification-severity"
                  />
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Rumah Pompa:</span>
                  <span class="font-medium">{{ alert.pump_house?.name || 'Sistem Umum' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Waktu Dibuat:</span>
                  <span class="font-medium">{{ formatDateTime(alert.created_at) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Terakhir Update:</span>
                  <span class="font-medium">{{ formatDateTime(alert.updated_at) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Threshold Legend Card -->
          <Card v-if="activeThresholds.length > 0" class="threshold-legend">
            <CardHeader>
              <CardTitle class="text-lg">Legenda Threshold</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div 
                  v-for="threshold in activeThresholds" 
                  :key="threshold.id"
                  class="flex items-center gap-3 threshold-item"
                >
                  <div 
                    class="w-3 h-3 rounded-full"
                    :style="{ backgroundColor: threshold.color }"
                  ></div>
                  <div class="flex-1">
                    <div class="text-sm font-medium" :style="{ color: threshold.color }">
                      {{ threshold.label }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ threshold.water_level }}m - {{ threshold.description }}
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions Card -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Aksi Cepat</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Button 
                variant="outline" 
                size="sm" 
                class="w-full justify-start"
                @click="printNotification"
              >
                <Printer class="h-4 w-4 mr-2" />
                Cetak Notifikasi
              </Button>
              <Button 
                variant="outline" 
                size="sm" 
                class="w-full justify-start"
                @click="shareNotification"
              >
                <Share class="h-4 w-4 mr-2" />
                Bagikan
              </Button>
              <Button 
                v-if="alert.pump_house?.id"
                variant="outline" 
                size="sm" 
                class="w-full justify-start"
                @click="viewPumpHouseDetail"
              >
                <Home class="h-4 w-4 mr-2" />
                Detail Rumah Pompa
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from "vue"
  import { router, useForm } from "@inertiajs/vue3"
  import { useToast } from "@/Components/ui/toast"
  import L from "leaflet"
  import "leaflet/dist/leaflet.css"
  import Layout from "@/Layouts/AdminLayout.vue"
  import {
    ChevronLeft,
    MapPin,
    Calendar,
    MoreVertical,
    Edit,
    Printer,
    Trash,
    Droplets,
    Activity,
    Home,
    Clock,
    User,
    Share,
    Loader2
  } from "lucide-vue-next"
  
  import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
  } from "@/Components/ui/card"
  
  
  import { Button } from "@/Components/ui/button"
  import StatusBadge from '@/Components/ui/StatusBadge.vue'
  import { useDateUtils } from '@/composables/useDateUtils'
  import ChartWithTimeFilter from '@/Components/Charts/ChartWithTimeFilter.vue'
  import { useWaterLevelUtils } from '@/composables/useWaterLevelUtils'
  
  defineOptions({ layout: Layout })
  
  // Props from Inertia
  const props = defineProps({
    alert: {
      type: Object,
      required: true
    },
    waterLevelHistory: {
      type: Array,
      default: () => []
    },
    thresholds: {
      type: Array,
      default: () => []
    },
    globalThresholds: {
      type: Array,
      default: () => []
    }
  })
  
  const { toast } = useToast()
  
  // Use composables
  const { formatDate, formatDateTime, formatTimeAgo } = useDateUtils()
  const { getStatusWithThresholds } = useWaterLevelUtils()
  
  
  // Computed
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
  
  
  const goBack = () => {
    router.visit(route('admin.notifications.index'))
  }
  
  
  const shareNotification = () => {
    // Logic for sharing notification
    console.log('Sharing notification...')
  }
  
  const printNotification = () => {
    window.print()
  }
  
  const viewPumpHouseDetail = () => {
    if (props.alert.pump_house?.id) {
      router.visit(route('admin.database.show', props.alert.pump_house.id))
    }
  }
  
  // Initialize map if coordinates are available
  onMounted(() => {
    if (props.alert.pump_house?.lat && props.alert.pump_house?.lng) {
      // Initialize map logic here
      console.log('Initializing map for notification...')
    }
  })
  </script>
  
  <style>
  /* Ensure the map container has a background */
  #notification-map {
    background-color: #f0f0f0;
  }

  /* Print styles */
  @media print {
    .no-print {
      display: none !important;
    }
    
    .print-break {
      page-break-before: always;
    }
    
    /* Ensure cards print nicely */
    .card {
      break-inside: avoid;
      margin-bottom: 1rem;
    }
    
    /* Threshold legend styling for print */
    .threshold-legend {
      break-inside: avoid;
    }
  }

  /* Status cards styling */
  .status-card {
    transition: all 0.2s ease-in-out;
  }

  .status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  /* Threshold legend styling */
  .threshold-item {
    transition: all 0.2s ease-in-out;
    padding: 0.5rem;
    border-radius: 0.375rem;
  }

  .threshold-item:hover {
    background-color: rgba(0, 0, 0, 0.05);
  }

  /* Water level status styling */
  .water-level-indicator {
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% {
      opacity: 1;
    }
    50% {
      opacity: 0.7;
    }
  }
  </style>