<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">Atur Threshold - {{ pumpHouse.name }}</h1>
          <p class="text-muted-foreground">{{ pumpHouse.address }}</p>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" @click="copyFromDefault" :disabled="processing">
            <Copy class="w-4 h-4 mr-2" />
            Salin dari Default
          </Button>
          <Button variant="outline" @click="resetToDefault" :disabled="processing">
            <RotateCcw class="w-4 h-4 mr-2" />
            Reset ke Default
          </Button>
        </div>
      </div>

      <!-- Current Water Level Info -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Droplet class="w-5 h-5" />
            Status Ketinggian Air Saat Ini
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="flex items-center justify-between p-4 bg-muted rounded-lg">
            <div>
              <p class="text-lg font-semibold">{{ currentWaterLevel }}</p>
              <p class="text-sm text-muted-foreground">
                {{ pumpHouse.last_updated ? formatDate(pumpHouse.last_updated) : 'Belum ada data' }}
              </p>
            </div>
            <Badge :variant="getCurrentLevelVariant()" class="text-sm px-3 py-1">
              {{ getCurrentLevelStatus() }}
            </Badge>
          </div>
        </CardContent>
      </Card>

      <!-- Threshold Settings Form -->
      <form @submit.prevent="submit">
        <Card>
          <CardHeader>
            <CardTitle>Pengaturan Threshold</CardTitle>
            <p class="text-sm text-muted-foreground">
              Atur batas peringatan ketinggian air khusus untuk rumah pompa ini
            </p>
          </CardHeader>
          <CardContent class="space-y-6">
            <div 
              v-for="(threshold, index) in form.thresholds" 
              :key="index"
              class="p-4 border rounded-lg space-y-4"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div 
                    class="w-4 h-4 rounded-full border-2"
                    :style="{ backgroundColor: threshold.color }"
                  ></div>
                  <h4 class="font-medium">{{ threshold.label || `Threshold ${index + 1}` }}</h4>
                </div>
                <Button 
                  type="button"
                  variant="outline" 
                  size="sm" 
                  @click="removeThreshold(index)"
                  v-if="form.thresholds.length > 1"
                  class="text-destructive hover:text-destructive"
                >
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                  <Label :for="`name-${index}`">Nama</Label>
                  <Input 
                    :id="`name-${index}`"
                    v-model="threshold.name" 
                    placeholder="warning, critical, etc"
                    required
                  />
                </div>
                
                <div>
                  <Label :for="`label-${index}`">Label</Label>
                  <Input 
                    :id="`label-${index}`"
                    v-model="threshold.label" 
                    placeholder="Peringatan, Kritis, etc"
                    required
                  />
                </div>
                
                <div>
                  <Label :for="`water-level-${index}`">Ketinggian Air (meter)</Label>
                  <Input 
                    :id="`water-level-${index}`"
                    v-model="threshold.water_level" 
                    type="number" 
                    step="0.01" 
                    min="0" 
                    max="10"
                    placeholder="2.00"
                    required
                  />
                </div>
                
                <div>
                  <Label :for="`color-${index}`">Warna</Label>
                  <div class="flex gap-2">
                    <Input 
                      :id="`color-${index}`"
                      v-model="threshold.color" 
                      type="color"
                      class="w-12 h-10 p-1"
                    />
                    <Input 
                      v-model="threshold.color" 
                      placeholder="#ff0000"
                      class="flex-1"
                    />
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label :for="`severity-${index}`">Tingkat Keparahan</Label>
                  <Select v-model="threshold.severity">
                    <SelectTrigger>
                      <SelectValue placeholder="Pilih severity" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="low">Rendah</SelectItem>
                      <SelectItem value="medium">Sedang</SelectItem>
                      <SelectItem value="high">Tinggi</SelectItem>
                      <SelectItem value="critical">Kritis</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                
                <div class="flex items-center space-x-2 pt-6">
                  <input 
                    :id="`active-${index}`"
                    type="checkbox" 
                    v-model="threshold.is_active"
                    class="rounded"
                  />
                  <Label :for="`active-${index}`">Aktif</Label>
                </div>
              </div>

              <div>
                <Label :for="`description-${index}`">Deskripsi</Label>
                <textarea 
                  :id="`description-${index}`"
                  v-model="threshold.description"
                  class="w-full p-2 border rounded-md"
                  rows="2"
                  placeholder="Deskripsi tindakan yang diperlukan..."
                ></textarea>
              </div>
            </div>

            <!-- Add Threshold Button -->
            <Button 
              type="button" 
              variant="outline" 
              @click="addThreshold"
              class="w-full"
            >
              <Plus class="w-4 h-4 mr-2" />
              Tambah Threshold
            </Button>
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="flex justify-between">
          <Link :href="route('admin.pump-house-thresholds.index')">
            <Button variant="outline">
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali
            </Button>
          </Link>
          
          <div class="flex gap-2">
            <Link :href="route('admin.pump-house-thresholds.show', pumpHouse.id)">
              <Button variant="outline">
                <Eye class="w-4 h-4 mr-2" />
                Preview
              </Button>
            </Link>
            <Button type="submit" :disabled="processing">
              <Save class="w-4 h-4 mr-2" />
              {{ processing ? 'Menyimpan...' : 'Simpan' }}
            </Button>
          </div>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { 
  Plus, 
  Trash2, 
  Save, 
  ArrowLeft, 
  Eye, 
  Copy, 
  RotateCcw, 
  Droplet 
} from 'lucide-vue-next';

const props = defineProps({
  pumpHouse: Object,
});

const processing = ref(false);

const form = useForm({
  thresholds: props.pumpHouse.threshold_settings?.length > 0 
    ? props.pumpHouse.threshold_settings.map(t => ({ ...t }))
    : [
        {
          name: 'warning',
          label: 'Peringatan',
          water_level: 2.00,
          color: '#f59e0b',
          severity: 'medium',
          is_active: true,
          description: 'Ketinggian air mulai tinggi, perlu waspada dan monitoring lebih ketat.'
        }
      ]
});

const addThreshold = () => {
  form.thresholds.push({
    name: '',
    label: '',
    water_level: 0,
    color: '#6b7280',
    severity: 'medium',
    is_active: true,
    description: ''
  });
};

const removeThreshold = (index) => {
  if (form.thresholds.length > 1) {
    form.thresholds.splice(index, 1);
  }
};

const submit = () => {
  form.put(route('admin.pump-house-thresholds.update', props.pumpHouse.id));
};

const copyFromDefault = () => {
  processing.value = true;
  router.post(route('admin.pump-house-thresholds.copy-default', props.pumpHouse.id), {}, {
    onFinish: () => {
      processing.value = false;
    }
  });
};

const resetToDefault = () => {
  if (confirm('Apakah Anda yakin ingin mereset threshold ke pengaturan default? Semua pengaturan khusus akan hilang.')) {
    processing.value = true;
    router.post(route('admin.pump-house-thresholds.reset-default', props.pumpHouse.id), {}, {
      onFinish: () => {
        processing.value = false;
      }
    });
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const currentWaterLevel = computed(() => {
  if (!props.pumpHouse.current_water_level && (!props.pumpHouse.water_level_history || props.pumpHouse.water_level_history.length === 0)) return 'N/A';
  
  let level;
  if (props.pumpHouse.current_water_level !== null && props.pumpHouse.current_water_level !== undefined) {
    level = parseFloat(props.pumpHouse.current_water_level);
  } else {
    level = parseFloat(props.pumpHouse.water_level_history[0].water_level);
  }
  
  return isNaN(level) ? 'N/A' : `${level.toFixed(2)} meter`;
});

const currentLevelForComparison = computed(() => {
  if (!props.pumpHouse.current_water_level && (!props.pumpHouse.water_level_history || props.pumpHouse.water_level_history.length === 0)) return null;
  
  let level;
  if (props.pumpHouse.current_water_level !== null && props.pumpHouse.current_water_level !== undefined) {
    level = parseFloat(props.pumpHouse.current_water_level);
  } else {
    level = parseFloat(props.pumpHouse.water_level_history[0].water_level);
  }
  
  return isNaN(level) ? null : level;
});

const getCurrentLevelStatus = () => {
  if (!currentLevelForComparison.value) return 'N/A';
  
  // Check against current thresholds
  const exceededThreshold = form.thresholds
    .filter(t => t.is_active && currentLevelForComparison.value >= t.water_level)
    .sort((a, b) => b.water_level - a.water_level)[0];
  
  return exceededThreshold ? exceededThreshold.label : 'Normal';
};

const getCurrentLevelVariant = () => {
  const status = getCurrentLevelStatus();
  switch (status) {
    case 'Normal': return 'secondary';
    case 'Peringatan': return 'default';
    case 'Kritis': return 'destructive';
    case 'Darurat': return 'destructive';
    default: return 'secondary';
  }
};
</script> 
 