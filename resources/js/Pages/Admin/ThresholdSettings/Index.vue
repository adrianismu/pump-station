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
              <div class="text-xs text-muted-foreground line-clamp-2">
                {{ threshold.description || 'Tidak ada deskripsi' }}
              </div>
            </div>

            <div class="flex gap-2 pt-2">
              <Link :href="route('admin.threshold-settings.show', threshold.id)" class="flex-1">
                <Button variant="outline" size="sm" class="w-full">
                  <Eye class="w-3 h-3 mr-1" />
                  Detail
                </Button>
              </Link>
              <Link :href="route('admin.threshold-settings.edit', threshold.id)">
                <Button variant="outline" size="sm">
                  <Edit class="w-3 h-3" />
                </Button>
              </Link>
              <Button 
                variant="outline" 
                size="sm" 
                @click="openDeleteDialog(threshold)"
                class="text-destructive hover:text-destructive"
              >
                <Trash2 class="w-3 h-3" />
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <Card v-if="thresholds.length === 0">
        <CardContent class="text-center py-12">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-muted flex items-center justify-center">
            <Settings class="w-8 h-8 text-muted-foreground" />
          </div>
          <h3 class="text-lg font-medium mb-2">Belum Ada Threshold</h3>
          <p class="text-muted-foreground mb-4">
            Tambahkan pengaturan threshold pertama untuk mulai monitoring ketinggian air.
          </p>
          <Link :href="route('admin.threshold-settings.create')">
            <Button>
              <Plus class="w-4 h-4 mr-2" />
              Tambah Threshold Pertama
            </Button>
          </Link>
        </CardContent>
      </Card>

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

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="showDeleteDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Hapus Pengaturan Threshold</AlertDialogTitle>
          <AlertDialogDescription>
            Apakah Anda yakin ingin menghapus threshold "{{ selectedThreshold?.label }}"?
            <br><br>
            <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan dan akan mempengaruhi sistem monitoring serta notifikasi otomatis.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showDeleteDialog = false">
            Batal
          </AlertDialogCancel>
          <AlertDialogAction 
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            @click="deleteThreshold"
          >
            <Trash2 class="w-4 h-4 mr-2" />
            Hapus Threshold
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { 
  AlertDialog, 
  AlertDialogAction, 
  AlertDialogCancel, 
  AlertDialogContent, 
  AlertDialogDescription, 
  AlertDialogFooter, 
  AlertDialogHeader, 
  AlertDialogTitle 
} from '@/Components/ui/alert-dialog';
import { Plus, Edit, Trash2, Info, Eye, Settings } from 'lucide-vue-next';

const props = defineProps({
  thresholds: Array,
});

const showDeleteDialog = ref(false);
const selectedThreshold = ref(null);

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

const openDeleteDialog = (threshold) => {
  selectedThreshold.value = threshold;
  showDeleteDialog.value = true;
};

const deleteThreshold = () => {
  if (selectedThreshold.value) {
    router.delete(route('admin.threshold-settings.destroy', selectedThreshold.value.id));
    showDeleteDialog.value = false;
    selectedThreshold.value = null;
  }
};
</script> 
 