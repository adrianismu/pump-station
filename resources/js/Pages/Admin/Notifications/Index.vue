<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Notifikasi & Peringatan</h1>

    <!-- Filter & Actions -->
    <Card class="mb-6">
      <CardContent class="pt-6">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
          <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                type="text"
                placeholder="Cari notifikasi..."
                v-model="searchQuery"
                class="pl-8 w-full sm:w-[250px]"
              />
            </div>
            <Select v-model="severityFilter">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Pilih tingkat" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Semua Tingkat</SelectItem>
                <SelectItem value="critical">Darurat</SelectItem>
                <SelectItem value="high">Kritis</SelectItem>
                <SelectItem value="medium">Peringatan</SelectItem>
                <SelectItem value="low">Normal</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Daftar Notifikasi -->
    <div class="space-y-4">
      <Card
        v-for="alert in paginatedAlerts"
        :key="alert.id"
      >
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="md:w-2/3">
              <div class="flex items-center gap-2 mb-2">
                <StatusBadge 
                  :level="alert.severity"
                  type="notification-severity"
                />
                <span class="text-xs text-muted-foreground">{{ formatDateTime(alert.timestamp) }}</span>
              </div>
              <h3 class="text-lg font-semibold mb-1">{{ alert.title }}</h3>
              <p class="text-sm text-muted-foreground mb-2 flex items-center gap-1">
                <MapPin class="h-4 w-4" />
                {{ alert.location }}
              </p>
              <p class="text-sm mb-4">{{ alert.description }}</p>
            </div>
            
            <div class="md:w-1/3 border-t md:border-t-0 md:border-l border-border pt-4 md:pt-0 md:pl-4">
              <h4 class="font-medium mb-2">Informasi Sistem</h4>
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <Home class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ alert.pump_house?.name || 'Sistem Umum' }}</span>
                </div>
                <div class="space-y-1">
                  <div class="flex items-center gap-2">
                    <Clock class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm">{{ formatTimeAgo(alert.timestamp) }}</span>
                  </div>
                  <div class="text-xs text-muted-foreground ml-6">
                    {{ formatDateTime(alert.timestamp) }}
                  </div>
                </div>
              </div>
              
              <Separator class="my-4" />
              <div class="space-y-2">
                <Button @click="viewDetail(alert.id)" class="w-full justify-start">
                  <Eye class="mr-2 h-4 w-4" /> Lihat Detail
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Tidak ada notifikasi ditemukan -->
      <Card v-if="paginatedAlerts.length === 0">
        <CardContent class="p-8 text-center">
          <BellOff class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
          <h3 class="text-lg font-semibold mb-1">Tidak ada notifikasi ditemukan</h3>
          <p class="text-sm text-muted-foreground">Coba ubah filter pencarian Anda</p>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-4 py-3">
        <div class="text-sm text-muted-foreground">
          Menampilkan
          {{
            filteredAlerts.length === 0
              ? 0
              : (currentPage - 1) * itemsPerPage + 1
          }}
          -
          {{
            Math.min(currentPage * itemsPerPage, filteredAlerts.length)
          }}
          dari {{ filteredAlerts.length }} notifikasi
        </div>
        <Pagination
          :items-per-page="itemsPerPage"
          :total="filteredAlerts.length"
          :sibling-count="1"
          show-edges
          :default-page="currentPage"
          v-model:page="currentPage"
        >
          <template #default="{ page }">
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
                    :variant="item.value === page.selected ? 'default' : 'outline'"
                  >
                    {{ item.value }}
                  </Button>
                </PaginationListItem>
                <PaginationEllipsis v-else :key="item.type" :index="index" />
              </template>
              <PaginationNext />
              <PaginationLast />
            </PaginationList>
          </template>
        </Pagination>
      </div>
    </div>

    <!-- Create Alert Modal -->
    <Dialog v-model:open="showCreateModal">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Buat Notifikasi Baru</DialogTitle>
          <DialogDescription>
            Buat notifikasi atau peringatan baru untuk sistem monitoring
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="space-y-2">
            <Label for="title">Judul Notifikasi</Label>
            <Input id="title" v-model="form.title" placeholder="Masukkan judul notifikasi" />
            <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
          </div>
          
          <div class="space-y-2">
            <Label for="pump_house">Rumah Pompa</Label>
            <Select v-model="form.pump_house_id">
              <SelectTrigger id="pump_house">
                <SelectValue placeholder="Pilih Rumah Pompa" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="pumpHouse in pumpHouses" :key="pumpHouse.id" :value="pumpHouse.id">
                  {{ pumpHouse.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.pump_house_id" class="text-sm text-destructive">{{ form.errors.pump_house_id }}</p>
          </div>
          
          <div class="space-y-2">
            <Label>Tingkat Keparahan</Label>
            <RadioGroup v-model="form.severity">
              <div class="flex gap-4">
                <div class="flex items-center space-x-2">
                  <RadioGroupItem value="low" id="low" />
                  <Label for="low">Normal</Label>
                </div>
                <div class="flex items-center space-x-2">
                  <RadioGroupItem value="medium" id="medium" />
                  <Label for="medium">Peringatan</Label>
                </div>
                <div class="flex items-center space-x-2">
                  <RadioGroupItem value="high" id="high" />
                  <Label for="high">Kritis</Label>
                </div>
                <div class="flex items-center space-x-2">
                  <RadioGroupItem value="critical" id="critical" />
                  <Label for="critical">Darurat</Label>
                </div>
              </div>
            </RadioGroup>
            <p v-if="form.errors.severity" class="text-sm text-destructive">{{ form.errors.severity }}</p>
          </div>
          
          <div class="space-y-2">
            <Label for="description">Deskripsi</Label>
            <Textarea 
              id="description" 
              v-model="form.description" 
              placeholder="Jelaskan detail notifikasi..." 
              :rows="3"
            />
            <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="showCreateModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="form.processing">
              <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
              {{ form.processing ? 'Menyimpan...' : 'Buat Notifikasi' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
  Bell, 
  AlertTriangle, 
  CheckCircle,
  Search, 
  Download, 
  Plus,
  MapPin,
  Droplets,
  Activity,
  CloudRain,
  Home,
  Clock,
  AlertCircle,
  MessageSquare,
  Eye,
  BellOff,
  Loader2
} from 'lucide-vue-next'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog'
import { Separator } from '@/Components/ui/separator'
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
import StatusBadge from '@/Components/ui/StatusBadge.vue'
import { useDateUtils } from '@/composables/useDateUtils'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  alerts: Array,
  pumpHouses: Array
})

// Use composables
const { formatDate, formatTime, formatDateTime, formatTimeAgo } = useDateUtils()

// State
const searchQuery = ref('')
const severityFilter = ref('all')
const statusFilter = ref('all')
const showCreateModal = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10

// Form
const form = useForm({
  title: '',
  pump_house_id: '',
  severity: 'medium',
  description: ''
})

// Computed
const pendingCount = computed(() => {
  return props.alerts.filter(alert => !isCompleted(alert)).length
})

const completedCount = computed(() => {
  return props.alerts.filter(alert => isCompleted(alert)).length
})

const completedPercentage = computed(() => {
  if (props.alerts.length === 0) return 0
  return Math.round((completedCount.value / props.alerts.length) * 100)
})

const filteredAlerts = computed(() => {
  let filtered = props.alerts

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(alert => 
      alert.title.toLowerCase().includes(query) ||
      alert.description.toLowerCase().includes(query) ||
      alert.location.toLowerCase().includes(query)
    )
  }

  // Filter by severity
  if (severityFilter.value !== 'all') {
    filtered = filtered.filter(alert => 
      alert.severity === severityFilter.value
    )
  }

  // Filter by status
  if (statusFilter.value !== 'all') {
    if (statusFilter.value === 'pending') {
      filtered = filtered.filter(alert => !isCompleted(alert))
    } else if (statusFilter.value === 'completed') {
      filtered = filtered.filter(alert => isCompleted(alert))
    }
  }

  return filtered
})

const paginatedAlerts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredAlerts.value.slice(start, start + itemsPerPage)
})

// Methods

const isCompleted = (alert) => {
  return alert.actions && alert.actions.some(action => action.status === 'Selesai')
}

const viewDetail = (alertId) => {
  router.visit(route('admin.notifications.show', alertId))
}

const submitForm = () => {
  form.post(route('admin.notifications.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}
</script>