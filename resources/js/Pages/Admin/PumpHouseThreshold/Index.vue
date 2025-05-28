<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">Threshold per Rumah Pompa</h1>
          <p class="text-muted-foreground">Kelola batas peringatan ketinggian air untuk setiap rumah pompa</p>
        </div>
      </div>

      <!-- Pump Houses Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
                <p class="text-xs text-muted-foreground">{{ pumpHouse.last_updated ? formatDate(pumpHouse.last_updated) : 'Belum ada data' }}</p>
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
              <Link :href="route('admin.pump-house-thresholds.edit', pumpHouse.id)" class="flex-1">
                <Button size="sm" class="w-full">
                  <Settings class="w-3 h-3 mr-1" />
                  Atur
                </Button>
              </Link>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Info Card -->
      <Card>
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
              <h4 class="font-medium mb-2">Cara Penggunaan</h4>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li>• Klik "Atur" untuk mengkonfigurasi threshold khusus</li>
                <li>• Gunakan "Salin dari Default" untuk memulai dengan template</li>
                <li>• Sesuaikan nilai threshold sesuai kebutuhan lokasi</li>
                <li>• Sistem akan otomatis menggunakan threshold khusus jika tersedia</li>
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
});

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

const getCurrentWaterLevel = (pumpHouse) => {
  if (!pumpHouse.water_level) return 'N/A';
  const level = parseFloat(pumpHouse.water_level.toString().replace(' meter', ''));
  return isNaN(level) ? 'N/A' : `${level}m`;
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
  if (!pumpHouse.water_level) return 'N/A';
  
  const currentLevel = parseFloat(pumpHouse.water_level.toString().replace(' meter', ''));
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
</script> 
 