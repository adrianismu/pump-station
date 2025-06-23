<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">Threshold per Rumah Pompa</h1>
          <p class="text-muted-foreground">
            {{ isAdmin ? 'Kelola batas peringatan ketinggian air untuk setiap rumah pompa' : 'Kelola batas peringatan ketinggian air untuk rumah pompa yang ditugaskan' }}
          </p>
        </div>
        <!-- Role Badge untuk petugas -->
        <Badge v-if="!isAdmin" variant="outline" class="text-sm">
          Petugas - {{ pumpHouses.length }} Rumah Pompa
        </Badge>
      </div>

      <!-- Empty State untuk petugas tanpa akses -->
      <div v-if="pumpHouses.length === 0" class="text-center py-16">
        <AlertCircle class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
        <h3 class="text-lg font-semibold mb-2">Tidak Ada Rumah Pompa yang Ditugaskan</h3>
        <p class="text-muted-foreground mb-4">
          Anda belum ditugaskan untuk mengelola rumah pompa manapun. 
          Hubungi administrator untuk mendapatkan akses.
        </p>
      </div>

      <!-- Pump Houses Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card v-for="pumpHouse in pumpHouses" :key="pumpHouse.id" class="relative">
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <CardTitle class="text-lg">{{ pumpHouse.name }}</CardTitle>
              <Badge 
                :variant="pumpHouse.threshold_settings?.length > 0 ? 'default' : 'secondary'"
                class="text-xs"
              >
                {{ pumpHouse.threshold_settings?.length > 0 ? 'Dikonfigurasi' : 'Default' }}
              </Badge>
            </div>
            <p class="text-sm text-muted-foreground">{{ pumpHouse.address }}</p>
          </CardHeader>
          
          <CardContent class="space-y-4">
            <!-- Current Water Level -->
            <div class="flex items-center justify-between p-3 bg-muted rounded-lg">
              <div>
                <p class="text-sm font-medium">Ketinggian Air Saat Ini</p>
                <p class="text-xs text-muted-foreground">{{ getLastUpdated(pumpHouse) }}</p>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold" :style="{ color: getCurrentLevelColor(pumpHouse) }">
                  {{ getCurrentWaterLevel(pumpHouse) }}
                </p>
                <Badge 
                  :variant="getCurrentLevelVariant(pumpHouse)"
                  class="text-xs"
                >
                  {{ getCurrentLevelStatus(pumpHouse) }}
                </Badge>
              </div>
            </div>

            <!-- Threshold Summary -->
            <div v-if="pumpHouse.threshold_settings?.length > 0" class="space-y-2">
              <p class="text-sm font-medium">Threshold yang Dikonfigurasi:</p>
              <div class="grid grid-cols-2 gap-2">
                <div 
                  v-for="threshold in pumpHouse.threshold_settings.slice(0, 4)" 
                  :key="threshold.id"
                  class="flex items-center gap-2 text-xs"
                >
                  <div 
                    class="w-2 h-2 rounded-full"
                    :style="{ backgroundColor: threshold.color }"
                  ></div>
                  <span class="truncate">{{ threshold.label }}: {{ threshold.water_level }}m</span>
                </div>
              </div>
              <p v-if="pumpHouse.threshold_settings.length > 4" class="text-xs text-muted-foreground">
                +{{ pumpHouse.threshold_settings.length - 4 }} threshold lainnya
              </p>
            </div>
            
            <div v-else class="text-center py-4">
              <AlertCircle class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
              <p class="text-sm text-muted-foreground">Menggunakan threshold default</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 pt-2">
              <Link :href="route('admin.pump-house-thresholds.show', pumpHouse.id)" class="flex-1">
                <Button variant="outline" size="sm" class="w-full">
                  <Eye class="w-3 h-3 mr-1" />
                  Lihat
                </Button>
              </Link>
              <!-- Tombol atur ditampilkan sesuai akses -->
              <Link 
                v-if="isAdmin || canEditPumpHouse(pumpHouse.id)"
                :href="route('admin.pump-house-thresholds.edit', pumpHouse.id)" 
                class="flex-1"
              >
                <Button size="sm" class="w-full">
                  <Settings class="w-3 h-3 mr-1" />
                  Atur
                </Button>
              </Link>
              <!-- Disabled button untuk read-only -->
              <Button 
                v-else
                size="sm" 
                variant="outline" 
                disabled 
                class="flex-1 opacity-50"
                :title="'Anda hanya memiliki akses baca untuk rumah pompa ini'"
              >
                <Settings class="w-3 h-3 mr-1" />
                Baca Saja
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Info Card -->
      <Card v-if="pumpHouses.length > 0">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Info class="w-5 h-5" />
            Informasi Threshold per Rumah Pompa
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h4 class="font-medium mb-2">Keunggulan Threshold Khusus</h4>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li>• Setiap rumah pompa dapat memiliki batas peringatan yang berbeda</li>
                <li>• Disesuaikan dengan kondisi geografis dan kapasitas masing-masing</li>
                <li>• Lebih akurat dalam memberikan peringatan dini</li>
                <li>• Dapat menggunakan threshold default sebagai template</li>
              </ul>
            </div>
            <div>
              <h4 class="font-medium mb-2">{{ isAdmin ? 'Cara Penggunaan' : 'Akses Petugas' }}</h4>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li v-if="isAdmin">• Klik "Atur" untuk mengkonfigurasi threshold khusus</li>
                <li v-if="isAdmin">• Gunakan "Salin dari Default" untuk memulai dengan template</li>
                <li v-if="isAdmin">• Sesuaikan nilai threshold sesuai kebutuhan lokasi</li>
                <li v-if="isAdmin">• Sistem akan otomatis menggunakan threshold khusus jika tersedia</li>
                <li v-if="!isAdmin">• Anda hanya dapat mengatur threshold untuk rumah pompa yang ditugaskan</li>
                <li v-if="!isAdmin">• Akses berbeda-beda tergantung level otoritas (baca/tulis)</li>
                <li v-if="!isAdmin">• Hubungi administrator untuk perubahan akses</li>
              </ul>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Eye, Settings, Info, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
  pumpHouses: Array,
  userRole: String,
  isAdmin: Boolean,
});

const getCurrentWaterLevel = (pumpHouse) => {
  // Use current_water_level from controller if available
  if (pumpHouse.current_water_level !== null && pumpHouse.current_water_level !== undefined) {
    const level = parseFloat(pumpHouse.current_water_level);
    return isNaN(level) ? 'N/A' : `${level.toFixed(2)}m`;
  }
  
  // Use water_level_history if available
  if (pumpHouse.water_level_history && pumpHouse.water_level_history.length > 0) {
    const level = parseFloat(pumpHouse.water_level_history[0].water_level);
    return isNaN(level) ? 'N/A' : `${level.toFixed(2)}m`;
  }
  
  return 'N/A';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getLastUpdated = (pumpHouse) => {
  // Use last_recorded_at from controller if available
  if (pumpHouse.last_recorded_at) {
    return formatDate(pumpHouse.last_recorded_at);
  }
  
  // Fallback to last_updated field
  if (pumpHouse.last_updated) {
    return formatDate(pumpHouse.last_updated);
  }
  
  return 'Belum ada data';
};

const getCurrentLevelColor = (pumpHouse) => {
  const status = getCurrentLevelStatus(pumpHouse);
  switch (status) {
    case 'Normal': return '#10b981';
    case 'Peringatan': return '#f59e0b';
    case 'Kritis': return '#ef4444';
    case 'Darurat': return '#dc2626';
    default: return '#6b7280';
  }
};

const getCurrentLevelVariant = (pumpHouse) => {
  const status = getCurrentLevelStatus(pumpHouse);
  switch (status) {
    case 'Normal': return 'secondary';
    case 'Peringatan': return 'default';
    case 'Kritis': return 'destructive';
    case 'Darurat': return 'destructive';
    default: return 'secondary';
  }
};

const getCurrentLevelStatus = (pumpHouse) => {
  let currentLevel;
  
  // Use current_water_level from controller if available
  if (pumpHouse.current_water_level !== null && pumpHouse.current_water_level !== undefined) {
    currentLevel = parseFloat(pumpHouse.current_water_level);
  } else if (pumpHouse.water_level_history && pumpHouse.water_level_history.length > 0) {
    currentLevel = parseFloat(pumpHouse.water_level_history[0].water_level);
  } else {
    return 'N/A';
  }
  
  if (isNaN(currentLevel)) return 'N/A';
  
  // Check against pump house specific thresholds if available
  if (pumpHouse.threshold_settings && pumpHouse.threshold_settings.length > 0) {
    const exceededThreshold = pumpHouse.threshold_settings
      .filter(t => t.is_active && currentLevel >= t.water_level)
      .sort((a, b) => b.water_level - a.water_level)[0];
    
    return exceededThreshold ? exceededThreshold.label : 'Normal';
  }
  
  // Fallback to default thresholds
  if (currentLevel >= 3.0) return 'Darurat';
  if (currentLevel >= 2.5) return 'Kritis';
  if (currentLevel >= 2.0) return 'Peringatan';
  return 'Normal';
};

// Fungsi untuk cek apakah petugas bisa edit pump house tertentu
// Ini akan diimplementasi berdasarkan data user akses yang dikirim dari controller
const canEditPumpHouse = (pumpHouseId) => {
  // Admin selalu bisa edit
  if (props.isAdmin) return true;
  
  // Cari pump house dan cek akses write
  const pumpHouse = props.pumpHouses.find(p => p.id === pumpHouseId);
  return pumpHouse?.user_access?.can_write || false;
};
</script> 
 