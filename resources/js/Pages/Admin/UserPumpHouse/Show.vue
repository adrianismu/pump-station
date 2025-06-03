<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Detail Akses Petugas</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola akses {{ user.name }} ke rumah pompa</p>
        </div>
        <div class="flex gap-3">
          <Button @click="$inertia.visit(route('admin.user-pump-house.index'))" variant="outline">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Kembali
          </Button>
          <Button @click="showAssignModal = true" v-if="availablePumpHouses.length > 0">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Akses
          </Button>
        </div>
      </div>

      <!-- User Info Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
              <User class="w-8 h-8 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
              <CardTitle>{{ user.name }}</CardTitle>
              <CardDescription>{{ user.email }}</CardDescription>
              <Badge :variant="user.role === 'admin' ? 'destructive' : 'default'" class="mt-2">
                {{ user.role.toUpperCase() }}
              </Badge>
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- Access Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Akses Aktif</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ activeAccess.length }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                <Clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Akan Expire</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ expiringAccess.length }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                <XCircle class="w-6 h-6 text-red-600 dark:text-red-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tidak Aktif</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ inactiveAccess.length }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Access List -->
      <Card>
        <CardHeader>
          <CardTitle>Daftar Akses Rumah Pompa</CardTitle>
          <CardDescription>
            Kelola akses petugas ke setiap rumah pompa
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-if="user.all_pump_houses.length === 0" class="text-center py-8">
              <Building class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-500 dark:text-gray-400">Belum ada akses ke rumah pompa</p>
              <Button @click="showAssignModal = true" class="mt-4" v-if="availablePumpHouses.length > 0">
                <Plus class="w-4 h-4 mr-2" />
                Tambah Akses Pertama
              </Button>
            </div>

            <div v-else class="space-y-4">
              <div 
                v-for="pumpHouse in user.all_pump_houses" 
                :key="pumpHouse.id" 
                class="border rounded-lg p-4"
                :class="pumpHouse.pivot.is_active ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900'"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                      <Building class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-900 dark:text-white">{{ pumpHouse.name }}</h3>
                      <p class="text-sm text-gray-600 dark:text-gray-400">{{ pumpHouse.address }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-4">
                    <div class="text-right">
                      <Badge :variant="getAccessLevelVariant(pumpHouse.pivot.access_level)">
                        {{ pumpHouse.pivot.access_level.toUpperCase() }}
                      </Badge>
                      <Badge 
                        :variant="pumpHouse.pivot.is_active ? 'default' : 'secondary'" 
                        class="ml-2"
                      >
                        {{ pumpHouse.pivot.is_active ? 'Aktif' : 'Tidak Aktif' }}
                      </Badge>
                    </div>
                    
                    <div class="flex space-x-2">
                      <Button 
                        @click="editAccess(pumpHouse)" 
                        variant="outline" 
                        size="sm"
                      >
                        <Edit class="w-4 h-4" />
                      </Button>
                      <Button 
                        @click="confirmRevoke(pumpHouse)" 
                        variant="destructive" 
                        size="sm"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                </div>

                <!-- Access Details -->
                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">Diberikan</p>
                    <p class="font-medium">{{ formatDate(pumpHouse.pivot.assigned_at) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-600 dark:text-gray-400">Expire</p>
                    <p class="font-medium">
                      {{ pumpHouse.pivot.expires_at ? formatDate(pumpHouse.pivot.expires_at) : 'Tidak ada' }}
                    </p>
                  </div>
                  <div class="col-span-2">
                    <p class="text-gray-600 dark:text-gray-400">Catatan</p>
                    <p class="font-medium">{{ pumpHouse.pivot.notes || 'Tidak ada catatan' }}</p>
                  </div>
                </div>

                <!-- Expiry Warning -->
                <div v-if="isExpiringSoon(pumpHouse.pivot.expires_at)" class="mt-3">
                  <Alert>
                    <AlertTriangle class="h-4 w-4" />
                    <AlertTitle>Peringatan</AlertTitle>
                    <AlertDescription>
                      Akses akan berakhir dalam {{ getDaysUntilExpiry(pumpHouse.pivot.expires_at) }} hari
                    </AlertDescription>
                  </Alert>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Assign Modal -->
    <Dialog v-model:open="showAssignModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Tambah Akses Rumah Pompa</DialogTitle>
          <DialogDescription>
            Berikan akses {{ user.name }} ke rumah pompa baru
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitAssign" class="space-y-4">
          <div>
            <Label for="pump_house_id">Rumah Pompa</Label>
            <Select v-model="assignForm.pump_house_id">
              <SelectTrigger>
                <SelectValue placeholder="Pilih rumah pompa" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="pumpHouse in availablePumpHouses" 
                  :key="pumpHouse.id" 
                  :value="pumpHouse.id.toString()"
                >
                  {{ pumpHouse.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <div v-if="assignForm.errors.pump_house_id" class="text-red-500 text-sm mt-1">
              {{ assignForm.errors.pump_house_id }}
            </div>
          </div>

          <div>
            <Label for="access_level">Level Akses</Label>
            <Select v-model="assignForm.access_level">
              <SelectTrigger>
                <SelectValue placeholder="Pilih level akses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="read">Read - Hanya Lihat</SelectItem>
                <SelectItem value="write">Write - Lihat & Edit</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label for="expires_at">Tanggal Expire (Opsional)</Label>
            <Input 
              type="date" 
              v-model="assignForm.expires_at"
              :min="new Date().toISOString().split('T')[0]"
            />
          </div>

          <div>
            <Label for="notes">Catatan</Label>
            <textarea 
              v-model="assignForm.notes"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
              placeholder="Catatan tambahan..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-2">
            <Button type="button" variant="outline" @click="showAssignModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="assignForm.processing">
              {{ assignForm.processing ? 'Memproses...' : 'Tambah Akses' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Modal -->
    <Dialog v-model:open="showEditModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Edit Akses</DialogTitle>
          <DialogDescription>
            Edit akses {{ user.name }} ke {{ editingPumpHouse?.name }}
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <Label for="access_level">Level Akses</Label>
            <Select v-model="editForm.access_level">
              <SelectTrigger>
                <SelectValue placeholder="Pilih level akses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="read">Read - Hanya Lihat</SelectItem>
                <SelectItem value="write">Write - Lihat & Edit</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label>Status</Label>
            <div class="flex items-center space-x-2 mt-2">
              <input 
                type="checkbox" 
                id="is_active"
                v-model="editForm.is_active"
                class="rounded"
              />
              <label for="is_active" class="text-sm">Akses Aktif</label>
            </div>
          </div>

          <div>
            <Label for="expires_at">Tanggal Expire</Label>
            <Input 
              type="date" 
              v-model="editForm.expires_at"
            />
          </div>

          <div>
            <Label for="notes">Catatan</Label>
            <textarea 
              v-model="editForm.notes"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
              placeholder="Catatan tambahan..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-2">
            <Button type="button" variant="outline" @click="showEditModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="editForm.processing">
              {{ editForm.processing ? 'Memproses...' : 'Update' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Revoke Confirmation Modal -->
    <Dialog v-model:open="showRevokeModal">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle>Konfirmasi Hapus Akses</DialogTitle>
          <DialogDescription>
            Apakah Anda yakin ingin menghapus akses {{ user.name }} ke {{ revokingPumpHouse?.name }}?
            Tindakan ini tidak dapat dibatalkan.
          </DialogDescription>
        </DialogHeader>
        
        <div class="flex justify-end space-x-2">
          <Button variant="outline" @click="showRevokeModal = false">
            Batal
          </Button>
          <Button variant="destructive" @click="submitRevoke" :disabled="revokeForm.processing">
            {{ revokeForm.processing ? 'Menghapus...' : 'Hapus Akses' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/Components/ui/dialog'
import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert'
import { 
  User, 
  Building, 
  ArrowLeft, 
  Plus, 
  Edit, 
  Trash2, 
  CheckCircle, 
  Clock, 
  XCircle,
  AlertTriangle
} from 'lucide-vue-next'

const props = defineProps({
  user: Object,
  availablePumpHouses: Array,
})

const showAssignModal = ref(false)
const showEditModal = ref(false)
const showRevokeModal = ref(false)
const editingPumpHouse = ref(null)
const revokingPumpHouse = ref(null)

// Computed properties
const activeAccess = computed(() => {
  return props.user.all_pump_houses.filter(ph => ph.pivot.is_active)
})

const inactiveAccess = computed(() => {
  return props.user.all_pump_houses.filter(ph => !ph.pivot.is_active)
})

const expiringAccess = computed(() => {
  const thirtyDaysFromNow = new Date()
  thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30)
  
  return props.user.all_pump_houses.filter(ph => {
    return ph.pivot.is_active && 
           ph.pivot.expires_at && 
           new Date(ph.pivot.expires_at) <= thirtyDaysFromNow
  })
})

// Forms
const assignForm = useForm({
  pump_house_id: '',
  access_level: 'read',
  expires_at: '',
  notes: '',
})

const editForm = useForm({
  access_level: '',
  is_active: true,
  expires_at: '',
  notes: '',
})

const revokeForm = useForm({})

// Methods
const getAccessLevelVariant = (level) => {
  switch (level) {
    case 'write': return 'default'
    case 'read': return 'secondary'
    default: return 'outline'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID')
}

const isExpiringSoon = (expiresAt) => {
  if (!expiresAt) return false
  const expiry = new Date(expiresAt)
  const thirtyDaysFromNow = new Date()
  thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30)
  return expiry <= thirtyDaysFromNow && expiry > new Date()
}

const getDaysUntilExpiry = (expiresAt) => {
  if (!expiresAt) return 0
  const expiry = new Date(expiresAt)
  const today = new Date()
  const diffTime = expiry - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(0, diffDays)
}

const submitAssign = () => {
  assignForm.post(route('admin.user-pump-house.assign', props.user.id), {
    onSuccess: () => {
      showAssignModal.value = false
      assignForm.reset()
    }
  })
}

const editAccess = (pumpHouse) => {
  editingPumpHouse.value = pumpHouse
  editForm.access_level = pumpHouse.pivot.access_level
  editForm.is_active = pumpHouse.pivot.is_active
  editForm.expires_at = pumpHouse.pivot.expires_at ? pumpHouse.pivot.expires_at.split('T')[0] : ''
  editForm.notes = pumpHouse.pivot.notes || ''
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(route('admin.user-pump-house.update', [props.user.id, editingPumpHouse.value.id]), {
    onSuccess: () => {
      showEditModal.value = false
      editForm.reset()
      editingPumpHouse.value = null
    }
  })
}

const confirmRevoke = (pumpHouse) => {
  revokingPumpHouse.value = pumpHouse
  showRevokeModal.value = true
}

const submitRevoke = () => {
  revokeForm.delete(route('admin.user-pump-house.revoke', [props.user.id, revokingPumpHouse.value.id]), {
    onSuccess: () => {
      showRevokeModal.value = false
      revokingPumpHouse.value = null
    }
  })
}
</script> 
 