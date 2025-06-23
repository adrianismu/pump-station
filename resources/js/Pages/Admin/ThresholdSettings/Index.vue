<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold">Pengaturan Threshold</h1>
          <p class="text-muted-foreground">Kelola batas peringatan ketinggian air</p>
        </div>
        <Link :href="route('admin.threshold-settings.create')">
          <Button>
            <Plus class="w-4 h-4 mr-2" />
            Tambah Threshold
          </Button>
        </Link>
      </div>

      <!-- Threshold Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card v-for="threshold in thresholds" :key="threshold.id" class="relative">
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div 
                  class="w-3 h-3 rounded-full"
                  :style="{ backgroundColor: threshold.color }"
                ></div>
                <CardTitle class="text-lg">{{ threshold.label }}</CardTitle>
              </div>
              <Badge 
                :variant="threshold.is_active ? 'default' : 'secondary'"
                class="text-xs"
              >
                {{ threshold.is_active ? 'Aktif' : 'Nonaktif' }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="text-center">
              <div class="text-2xl font-bold" :style="{ color: threshold.color }">
                {{ threshold.water_level }}m
              </div>
              <p class="text-sm text-muted-foreground">Batas Ketinggian</p>
            </div>
            
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-muted-foreground">Tingkat:</span>
                <Badge 
                  :variant="getSeverityVariant(threshold.severity)"
                  class="text-xs"
                >
                  {{ getSeverityLabel(threshold.severity) }}
                </Badge>
              </div>
              <div class="text-xs text-muted-foreground">
                {{ threshold.description }}
              </div>
            </div>

            <div class="flex gap-2 pt-2">
              <Link :href="route('admin.threshold-settings.edit', threshold.id)" class="flex-1">
                <Button variant="outline" size="sm" class="w-full">
                  <Edit class="w-3 h-3 mr-1" />
                  Edit
                </Button>
              </Link>
              <Button 
                variant="outline" 
                size="sm" 
                @click="deleteThreshold(threshold)"
                class="text-destructive hover:text-destructive"
              >
                <Trash2 class="w-3 h-3" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Info Card -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Info class="w-5 h-5" />
            Informasi Threshold
          </CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h4 class="font-medium mb-2">Cara Kerja Threshold</h4>
              <ul class="text-sm text-muted-foreground space-y-1">
                <li>• Sistem akan memeriksa ketinggian air terhadap threshold yang aktif</li>
                <li>• Notifikasi akan muncul jika ketinggian air melebihi batas yang ditentukan</li>
                <li>• Threshold dengan nilai tertinggi yang terlampaui akan digunakan</li>
                <li>• Alert otomatis akan dibuat untuk threshold yang terlampaui</li>
              </ul>
            </div>
            <div>
              <h4 class="font-medium mb-2">Tingkat Severity</h4>
              <div class="space-y-2">
                <div class="flex items-center gap-2 text-sm">
                  <Badge variant="secondary">Low</Badge>
                  <span class="text-muted-foreground">Informasi umum</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <Badge variant="default">Medium</Badge>
                  <span class="text-muted-foreground">Perlu perhatian</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <Badge variant="destructive">High</Badge>
                  <span class="text-muted-foreground">Tindakan segera</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <Badge variant="destructive">Critical</Badge>
                  <span class="text-muted-foreground">Darurat</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Plus, Edit, Trash2, Info } from 'lucide-vue-next';

const props = defineProps({
  thresholds: Array,
});

const getSeverityVariant = (severity) => {
  switch (severity) {
    case 'low': return 'secondary';
    case 'medium': return 'default';
    case 'high': return 'destructive';
    case 'critical': return 'destructive';
    default: return 'secondary';
  }
};

const getSeverityLabel = (severity) => {
  switch (severity) {
    case 'low': return 'Rendah';
    case 'medium': return 'Sedang';
    case 'high': return 'Tinggi';
    case 'critical': return 'Kritis';
    default: return severity;
  }
};

const deleteThreshold = (threshold) => {
  if (confirm(`Apakah Anda yakin ingin menghapus threshold "${threshold.label}"?`)) {
    router.delete(route('admin.threshold-settings.destroy', threshold.id));
  }
};
</script> 
 