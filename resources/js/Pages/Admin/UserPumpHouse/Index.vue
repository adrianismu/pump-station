<template>
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manajemen Akses Petugas</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola akses petugas ke rumah pompa</p>
        </div>
        <div class="flex gap-3">
          <Button @click="showBulkAssignModal = true" variant="outline">
            <Users class="w-4 h-4 mr-2" />
            Assign Massal
          </Button>
          <Button @click="showCopyAccessModal = true">
            <Copy class="w-4 h-4 mr-2" />
            Salin Akses
          </Button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                <Users class="w-6 h-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Petugas</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ users.length }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Akses Aktif</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ activeAccessCount }}</p>
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
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ expiringAccessCount }}</p>
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
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ inactiveAccessCount }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Users List -->
      <Card>
        <CardHeader>
          <CardTitle>Daftar Petugas dan Akses</CardTitle>
          <CardDescription>
            Klik nama petugas untuk melihat detail dan mengelola akses
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="user in users" :key="user.id" class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <User class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ user.name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-4">
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ user.all_pump_houses.filter(ph => ph.pivot.is_active).length }} Akses Aktif
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                      dari {{ user.all_pump_houses.length }} total
                    </p>
                  </div>
                  <Button @click="$inertia.visit(route('admin.user-pump-house.show', user.id))" variant="outline" size="sm">
                    <Settings class="w-4 h-4 mr-2" />
                    Kelola
                  </Button>
                </div>
              </div>

              <!-- Access Summary -->
              <div class="mt-4 flex flex-wrap gap-2">
                <Badge 
                  v-for="pumpHouse in user.all_pump_houses.filter(ph => ph.pivot.is_active)" 
                  :key="pumpHouse.id"
                  :variant="getAccessLevelVariant(pumpHouse.pivot.access_level)"
                >
                  {{ pumpHouse.name }} ({{ pumpHouse.pivot.access_level }})
                </Badge>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Bulk Assign Modal -->
    <Dialog v-model:open="showBulkAssignModal">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Assign Akses Massal</DialogTitle>
          <DialogDescription>
            Berikan akses ke beberapa petugas sekaligus
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitBulkAssign" class="space-y-4">
          <div>
            <Label>Pilih Petugas</Label>
            <div class="mt-2 space-y-2 max-h-40 overflow-y-auto border rounded-md p-2">
              <div v-for="user in users" :key="user.id" class="flex items-center space-x-2">
                <input 
                  type="checkbox" 
                  :id="`user-${user.id}`"
                  v-model="bulkAssignForm.user_ids"
                  :value="user.id"
                  class="rounded"
                />
                <label :for="`user-${user.id}`" class="text-sm">{{ user.name }}</label>
              </div>
            </div>
          </div>

          <div>
            <Label>Pilih Rumah Pompa</Label>
            <div class="mt-2 space-y-2 max-h-40 overflow-y-auto border rounded-md p-2">
              <div v-for="pumpHouse in pumpHouses" :key="pumpHouse.id" class="flex items-center space-x-2">
                <input 
                  type="checkbox" 
                  :id="`pump-${pumpHouse.id}`"
                  v-model="bulkAssignForm.pump_house_ids"
                  :value="pumpHouse.id"
                  class="rounded"
                />
                <label :for="`pump-${pumpHouse.id}`" class="text-sm">{{ pumpHouse.name }}</label>
              </div>
            </div>
          </div>

          <div>
            <Label for="access_level">Level Akses</Label>
            <Select v-model="bulkAssignForm.access_level">
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
              v-model="bulkAssignForm.expires_at"
              :min="new Date().toISOString().split('T')[0]"
            />
          </div>

          <div>
            <Label for="notes">Catatan</Label>
            <textarea 
              v-model="bulkAssignForm.notes"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="3"
              placeholder="Catatan tambahan..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-2">
            <Button type="button" variant="outline" @click="showBulkAssignModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="bulkAssignForm.processing">
              {{ bulkAssignForm.processing ? 'Memproses...' : 'Assign' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Copy Access Modal -->
    <Dialog v-model:open="showCopyAccessModal">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Salin Akses</DialogTitle>
          <DialogDescription>
            Salin akses dari satu petugas ke petugas lain
          </DialogDescription>
        </DialogHeader>
        
        <form @submit.prevent="submitCopyAccess" class="space-y-4">
          <div>
            <Label>Petugas Sumber</Label>
            <Select v-model="copyAccessForm.source_user_id">
              <SelectTrigger>
                <SelectValue placeholder="Pilih petugas sumber" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="user in users" :key="user.id" :value="user.id.toString()">
                  {{ user.name }} ({{ user.all_pump_houses.length }} akses)
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label>Petugas Target</Label>
            <div class="mt-2 space-y-2 max-h-40 overflow-y-auto border rounded-md p-2">
              <div v-for="user in users" :key="user.id" class="flex items-center space-x-2">
                <input 
                  type="checkbox" 
                  :id="`target-${user.id}`"
                  v-model="copyAccessForm.target_user_ids"
                  :value="user.id"
                  :disabled="user.id == copyAccessForm.source_user_id"
                  class="rounded"
                />
                <label :for="`target-${user.id}`" class="text-sm" :class="{ 'text-gray-400': user.id == copyAccessForm.source_user_id }">
                  {{ user.name }}
                </label>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-2">
            <Button type="button" variant="outline" @click="showCopyAccessModal = false">
              Batal
            </Button>
            <Button type="submit" :disabled="copyAccessForm.processing">
              {{ copyAccessForm.processing ? 'Memproses...' : 'Salin Akses' }}
            </Button>
          </div>
        </form>
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
import { Users, User, Settings, CheckCircle, Clock, XCircle, Copy } from 'lucide-vue-next'

const props = defineProps({
  users: Array,
  pumpHouses: Array,
})

const showBulkAssignModal = ref(false)
const showCopyAccessModal = ref(false)

// Computed stats
const activeAccessCount = computed(() => {
  return props.users.reduce((total, user) => {
    return total + user.all_pump_houses.filter(ph => ph.pivot.is_active).length
  }, 0)
})

const expiringAccessCount = computed(() => {
  const thirtyDaysFromNow = new Date()
  thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30)
  
  return props.users.reduce((total, user) => {
    return total + user.all_pump_houses.filter(ph => {
      return ph.pivot.is_active && 
             ph.pivot.expires_at && 
             new Date(ph.pivot.expires_at) <= thirtyDaysFromNow
    }).length
  }, 0)
})

const inactiveAccessCount = computed(() => {
  return props.users.reduce((total, user) => {
    return total + user.all_pump_houses.filter(ph => !ph.pivot.is_active).length
  }, 0)
})

// Forms
const bulkAssignForm = useForm({
  user_ids: [],
  pump_house_ids: [],
  access_level: '',
  expires_at: '',
  notes: '',
})

const copyAccessForm = useForm({
  source_user_id: '',
  target_user_ids: [],
})

// Methods
const getAccessLevelVariant = (level) => {
  switch (level) {
    case 'write': return 'default'
    case 'read': return 'secondary'
    default: return 'outline'
  }
}

const submitBulkAssign = () => {
  bulkAssignForm.post(route('admin.user-pump-house.bulk-assign'), {
    onSuccess: () => {
      showBulkAssignModal.value = false
      bulkAssignForm.reset()
    }
  })
}

const submitCopyAccess = () => {
  copyAccessForm.post(route('admin.user-pump-house.copy-access'), {
    onSuccess: () => {
      showCopyAccessModal.value = false
      copyAccessForm.reset()
    }
  })
}
</script> 
 