<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Detail Threshold</h1>
          <p class="text-muted-foreground">
            Pengaturan threshold untuk {{ pumpHouse.name }}
            <Badge v-if="!isAdmin" variant="outline" class="ml-2">
              {{ canWrite ? 'Akses Tulis' : 'Akses Baca' }}
            </Badge>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" as-child>
            <Link :href="route('admin.pump-house-thresholds.index')">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Link>
          </Button>
          <!-- Conditional Edit Button -->
          <Button v-if="canWrite" as-child>
            <Link :href="route('admin.pump-house-thresholds.edit', pumpHouse.id)">
              <Settings class="w-4 h-4 mr-2" />
              Edit Threshold
            </Link>
          </Button>
          <Button v-else variant="outline" disabled :title="'Anda tidak memiliki akses untuk mengedit threshold rumah pompa ini'">
            <Settings class="w-4 h-4 mr-2" />
            Baca Saja
          </Button>
        </div>
      </div>

      <!-- Pump House Info -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <MapPin class="w-5 h-5" />
            Informasi Rumah Pompa
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Nama</Label>
              <p class="text-sm font-semibold">{{ pumpHouse.name }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Lokasi</Label>
              <p class="text-sm">{{ pumpHouse.location }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Kapasitas</Label>
              <p class="text-sm">{{ pumpHouse.capacity }} L/s</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Status</Label>
              <Badge :variant="pumpHouse.status === 'active' ? 'default' : 'secondary'">
                {{ pumpHouse.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
              </Badge>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Koordinat</Label>
              <p class="text-sm">{{ pumpHouse.latitude }}, {{ pumpHouse.longitude }}</p>
            </div>
            <div>
              <Label class="text-sm font-medium text-muted-foreground">Ketinggian Air Saat Ini</Label>
              <p class="text-sm font-semibold" :class="getCurrentWaterLevelClass()">
                {{ currentWaterLevel }}m
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Current Water Level Status -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Activity class="w-5 h-5" />
            Status Ketinggian Air
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <!-- Current Status Alert -->
            <Alert v-if="currentThreshold" :class="getAlertClass(currentThreshold.severity)">
              <AlertCircle class="h-4 w-4" />
              <AlertTitle>{{ currentThreshold.label }}</AlertTitle>
              <AlertDescription>
                Ketinggian air saat ini {{ currentWaterLevel }}m. {{ currentThreshold.description }}
              </AlertDescription>
            </Alert>

            <!-- Water Level Progress -->
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span>Ketinggian Air</span>
                <span class="font-medium">{{ currentWaterLevel }}m</span>
              </div>
              <div class="relative h-4 bg-muted rounded-full overflow-hidden">
                <div 
                  class="h-full transition-all duration-500"
                  :class="getProgressBarClass()"
                  :style="{ width: getProgressPercentage() + '%' }"
                ></div>
              </div>
              <div class="flex justify-between text-xs text-muted-foreground">
                <span>0m</span>
                <span>{{ maxThreshold }}m</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Threshold Settings -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Settings class="w-5 h-5" />
            Pengaturan Threshold
          </CardTitle>
          <CardDescription>
            Threshold khusus untuk rumah pompa ini
            <span v-if="!hasCustomThresholds" class="text-warning"> (Menggunakan threshold global)</span>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="!hasCustomThresholds" class="text-center py-8">
            <Settings class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
            <h3 class="text-lg font-semibold mb-2">Menggunakan Threshold Global</h3>
            <p class="text-muted-foreground mb-4">
              Rumah pompa ini belum memiliki threshold khusus dan menggunakan pengaturan global.
            </p>
            <!-- Conditional Create Button -->
            <Button v-if="canWrite" as-child>
              <Link :href="route('admin.pump-house-thresholds.edit', pumpHouse.id)">
                <Plus class="w-4 h-4 mr-2" />
                Buat Threshold Khusus
              </Link>
            </Button>
            <div v-else class="text-center">
              <p class="text-sm text-muted-foreground">
                Anda tidak memiliki akses untuk membuat threshold khusus
              </p>
            </div>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="threshold in thresholds" 
              :key="threshold.id"
              class="flex items-center justify-between p-4 border rounded-lg"
              :class="getBorderClass(threshold.severity)"
            >
              <div class="flex items-center gap-3">
                <div 
                  class="w-4 h-4 rounded-full"
                  :style="{ backgroundColor: threshold.color }"
                ></div>
                <div>
                  <h4 class="font-medium">{{ threshold.label }}</h4>
                  <p class="text-sm text-muted-foreground">{{ threshold.description }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold">{{ threshold.water_level }}m</p>
                <Badge :variant="getSeverityVariant(threshold.severity)">
                  {{ threshold.severity }}
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Chart Section -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle class="flex items-center gap-2">
                <TrendingUp class="w-5 h-5" />
                Grafik Ketinggian Air
              </CardTitle>
              <CardDescription>
                Visualisasi data ketinggian air untuk {{ pumpHouse.name }}
              </CardDescription>
            </div>
            <div class="flex items-center gap-2">
              <Button variant="outline" size="sm" @click="toggleThresholdLines">
                <Settings class="w-4 h-4 mr-2" />
                {{ showThresholdLines ? 'Sembunyikan' : 'Tampilkan' }} Threshold
              </Button>
              <Select v-model="chartTimeRange" @update:model-value="updateChartRange">
                <SelectTrigger class="w-24">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="7d">7 Hari</SelectItem>
                  <SelectItem value="30d">30 Hari</SelectItem>
                  <SelectItem value="90d">90 Hari</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div class="h-80 w-full">
            <WaterLevelChart
              :data="chartData"
              :thresholds="activeThresholds"
              :show-thresholds="showThresholdLines"
              :time-range="chartTimeRange"
              :height="320"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Recent Water Level History -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div>
              <CardTitle class="flex items-center gap-2">
                <BarChart3 class="w-5 h-5" />
                Riwayat Ketinggian Air
              </CardTitle>
              <CardDescription>
                Data ketinggian air untuk {{ pumpHouse.name }}
              </CardDescription>
            </div>
            <div class="flex items-center gap-2">
              <Button variant="outline" size="sm" @click="refreshHistory">
                <RefreshCw class="w-4 h-4 mr-2" />
                Refresh
              </Button>
              <Button variant="outline" size="sm" as-child>
                <Link :href="route('admin.water-level.history', pumpHouse.id)">
                  <ExternalLink class="w-4 h-4 mr-2" />
                  Lihat Lengkap
                </Link>
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="isLoadingHistory" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <p class="mt-2 text-sm text-muted-foreground">Memuat data...</p>
          </div>
          
          <div v-else-if="recentHistory.length === 0" class="text-center py-8">
            <BarChart3 class="w-12 h-12 text-muted-foreground mx-auto mb-4" />
            <h3 class="text-lg font-semibold mb-2">Belum Ada Data</h3>
            <p class="text-muted-foreground mb-4">Belum ada data riwayat ketinggian air untuk rumah pompa ini</p>
            <Button as-child>
              <Link :href="route('admin.water-level.create')">
                <Plus class="w-4 h-4 mr-2" />
                Tambah Data Pertama
              </Link>
            </Button>
          </div>
          
          <div v-else class="space-y-4">
            <!-- Filter dan Sorting -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Label class="text-sm font-medium">Tampilkan:</Label>
                <Select v-model="historyLimit" @update:model-value="updateHistoryLimit">
                  <SelectTrigger class="w-20">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="10">10</SelectItem>
                    <SelectItem value="25">25</SelectItem>
                    <SelectItem value="50">50</SelectItem>
                  </SelectContent>
                </Select>
                <span class="text-sm text-muted-foreground">data terakhir</span>
              </div>
              
              <div class="flex items-center gap-2">
                <Label class="text-sm font-medium">Urutkan:</Label>
                <Button 
                  variant="outline" 
                  size="sm"
                  @click="toggleSortOrder"
                  class="flex items-center gap-1"
                >
                  <Calendar class="w-4 h-4" />
                  Waktu
                  <ArrowUpDown class="w-3 h-3" />
                </Button>
              </div>
            </div>

            <!-- Tabel Riwayat -->
            <div class="border rounded-lg overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-[100px]">No</TableHead>
                    <TableHead>Waktu Pencatatan</TableHead>
                    <TableHead class="text-center">Ketinggian Air</TableHead>
                    <TableHead class="text-center">Status</TableHead>
                    <TableHead class="text-center">Threshold</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow 
                    v-for="(record, index) in sortedHistory" 
                    :key="record.id"
                    class="hover:bg-muted/50"
                  >
                    <TableCell class="font-medium">
                      {{ index + 1 }}
                    </TableCell>
                    <TableCell>
                      <div class="space-y-1">
                        <p class="font-medium">{{ formatDate(record.recorded_at) }}</p>
                        <p class="text-xs text-muted-foreground">{{ formatTime(record.recorded_at) }}</p>
                      </div>
                    </TableCell>
                    <TableCell class="text-center">
                      <div class="flex items-center justify-center gap-2">
                        <Droplet 
                          class="w-4 h-4" 
                          :class="getWaterLevelIconClass(record.water_level)"
                        />
                        <span class="font-semibold" :class="getWaterLevelTextClass(record.water_level)">
                          {{ record.water_level }}m
                        </span>
                      </div>
                    </TableCell>
                    <TableCell class="text-center">
                      <Badge :variant="getWaterLevelVariant(record.water_level)">
                        {{ getWaterLevelStatus(record.water_level) }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-center">
                      <div class="flex items-center justify-center gap-2">
                        <div 
                          class="w-3 h-3 rounded-full"
                          :style="{ backgroundColor: getThresholdColor(record.water_level) }"
                        ></div>
                        <span class="text-sm">{{ getThresholdName(record.water_level) }}</span>
                      </div>
                    </TableCell>
                    <TableCell class="text-right">
                      <div class="flex items-center justify-end gap-1">
                        <Button variant="ghost" size="sm" @click="viewDetails(record)">
                          <Eye class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="sm" as-child>
                          <Link :href="route('admin.water-level.edit', record.id)">
                            <Edit class="w-4 h-4" />
                          </Link>
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <!-- Pagination Info -->
            <div class="flex items-center justify-between text-sm text-muted-foreground">
              <p>
                Menampilkan {{ Math.min(historyLimit, recentHistory.length) }} dari {{ totalHistoryCount }} data
              </p>
              <p>
                Data terakhir: {{ recentHistory.length > 0 ? formatDate(recentHistory[0].recorded_at) : '-' }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { 
  ArrowLeft, 
  Settings, 
  MapPin, 
  Activity, 
  AlertCircle, 
  Plus,
  BarChart3,
  RefreshCw,
  ExternalLink,
  Calendar,
  ArrowUpDown,
  Droplet,
  Eye,
  Edit,
  TrendingUp
} from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Label } from '@/Components/ui/label';
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import WaterLevelChart from '@/Components/Charts/WaterLevelChart.vue';

const props = defineProps({
  pumpHouse: {
    type: Object,
    required: true
  },
  thresholds: {
    type: Array,
    default: () => []
  },
  currentWaterLevel: {
    type: [String, Number],
    default: 0
  },
  recentHistory: {
    type: Array,
    default: () => []
  },
  globalThresholds: {
    type: Array,
    default: () => []
  },
  totalHistoryCount: {
    type: Number,
    default: 0
  },
  isAdmin: {
    type: Boolean,
    default: false
  },
  canWrite: {
    type: Boolean,
    default: false
  }
});

// State
const isLoadingHistory = ref(false);
const historyLimit = ref(10);
const sortOrder = ref('desc'); // 'asc' or 'desc'
const showThresholdLines = ref(true);
const chartTimeRange = ref('30d');

// Computed properties
const hasCustomThresholds = computed(() => props.thresholds.length > 0);

const activeThresholds = computed(() => {
  return hasCustomThresholds.value ? props.thresholds : props.globalThresholds;
});

const currentThreshold = computed(() => {
  const waterLevel = parseFloat(props.currentWaterLevel);
  return activeThresholds.value
    .filter(t => waterLevel >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
});

const maxThreshold = computed(() => {
  if (activeThresholds.value.length === 0) return 5;
  return Math.max(...activeThresholds.value.map(t => parseFloat(t.water_level))) + 1;
});

const sortedHistory = computed(() => {
  const history = [...props.recentHistory];
  return history.sort((a, b) => {
    const dateA = new Date(a.recorded_at);
    const dateB = new Date(b.recorded_at);
    return sortOrder.value === 'desc' ? dateB - dateA : dateA - dateB;
  });
});

const chartData = computed(() => {
  if (!props.recentHistory || props.recentHistory.length === 0) {
    return [];
  }

  // Filter data based on time range
  const now = new Date();
  let cutoffDate;
  
  switch (chartTimeRange.value) {
    case '7d':
      cutoffDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
      break;
    case '30d':
      cutoffDate = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
      break;
    case '90d':
      cutoffDate = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000);
      break;
    default:
      cutoffDate = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
  }

  return props.recentHistory.filter(item => 
    new Date(item.recorded_at) >= cutoffDate
  );
});

// Helper functions
const getCurrentWaterLevelClass = () => {
  if (!currentThreshold.value) return 'text-green-600';
  
  const severity = currentThreshold.value.severity;
  if (severity === 'critical') return 'text-red-600';
  if (severity === 'high') return 'text-orange-600';
  if (severity === 'medium') return 'text-yellow-600';
  return 'text-green-600';
};

const getAlertClass = (severity) => {
  if (severity === 'critical') return 'border-red-200 bg-red-50';
  if (severity === 'high') return 'border-orange-200 bg-orange-50';
  if (severity === 'medium') return 'border-yellow-200 bg-yellow-50';
  return 'border-green-200 bg-green-50';
};

const getBorderClass = (severity) => {
  if (severity === 'critical') return 'border-red-200';
  if (severity === 'high') return 'border-orange-200';
  if (severity === 'medium') return 'border-yellow-200';
  return 'border-green-200';
};

const getSeverityVariant = (severity) => {
  if (severity === 'critical') return 'destructive';
  if (severity === 'high') return 'destructive';
  if (severity === 'medium') return 'secondary';
  return 'default';
};

const getProgressBarClass = () => {
  if (!currentThreshold.value) return 'bg-green-500';
  
  const severity = currentThreshold.value.severity;
  if (severity === 'critical') return 'bg-red-500';
  if (severity === 'high') return 'bg-orange-500';
  if (severity === 'medium') return 'bg-yellow-500';
  return 'bg-green-500';
};

const getProgressPercentage = () => {
  const waterLevel = parseFloat(props.currentWaterLevel);
  const max = maxThreshold.value;
  return Math.min((waterLevel / max) * 100, 100);
};

const getWaterLevelVariant = (waterLevel) => {
  const level = parseFloat(waterLevel);
  const threshold = activeThresholds.value
    .filter(t => level >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
  
  if (!threshold) return 'default';
  return getSeverityVariant(threshold.severity);
};

const getWaterLevelStatus = (waterLevel) => {
  const level = parseFloat(waterLevel);
  const threshold = activeThresholds.value
    .filter(t => level >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
  
  return threshold ? threshold.label : 'Normal';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getWaterLevelIconClass = (waterLevel) => {
  const level = parseFloat(waterLevel);
  const threshold = activeThresholds.value
    .filter(t => level >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
  
  if (!threshold) return 'text-green-500';
  
  const severity = threshold.severity;
  if (severity === 'critical') return 'text-red-500';
  if (severity === 'high') return 'text-orange-500';
  if (severity === 'medium') return 'text-yellow-500';
  return 'text-green-500';
};

const getWaterLevelTextClass = (waterLevel) => {
  const level = parseFloat(waterLevel);
  const threshold = activeThresholds.value
    .filter(t => level >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
  
  if (!threshold) return 'text-green-600';
  
  const severity = threshold.severity;
  if (severity === 'critical') return 'text-red-600';
  if (severity === 'high') return 'text-orange-600';
  if (severity === 'medium') return 'text-yellow-600';
  return 'text-green-600';
};

const getThresholdColor = (waterLevel) => {
  const level = parseFloat(waterLevel);
  const threshold = activeThresholds.value
    .filter(t => level >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
  
  return threshold ? threshold.color : '#22c55e';
};

const getThresholdName = (waterLevel) => {
  const level = parseFloat(waterLevel);
  const threshold = activeThresholds.value
    .filter(t => level >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
  
  return threshold ? threshold.name : 'Normal';
};

// Actions
const refreshHistory = () => {
  isLoadingHistory.value = true;
  router.reload({
    only: ['recentHistory', 'currentWaterLevel', 'totalHistoryCount'],
    onFinish: () => {
      isLoadingHistory.value = false;
    }
  });
};

const updateHistoryLimit = (newLimit) => {
  historyLimit.value = parseInt(newLimit);
  refreshHistory();
};

const toggleSortOrder = () => {
  sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc';
};

const viewDetails = (record) => {
  router.visit(route('admin.water-level.show', record.id));
};

// Chart functions
const toggleThresholdLines = () => {
  showThresholdLines.value = !showThresholdLines.value;
};

const updateChartRange = (newRange) => {
  chartTimeRange.value = newRange;
};
</script> 
 