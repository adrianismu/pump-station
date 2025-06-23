<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Database Rumah Pompa</h1>
    
    <div v-if="loading" class="flex justify-center items-center h-64">
      <Loader2 class="h-12 w-12 animate-spin text-primary" />
    </div>
    
    <template v-else>
      <Card class="mb-6">
        <CardContent class="pt-6">
          <div class="flex flex-col md:flex-row gap-4 justify-between">
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
              <div class="relative">
                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input 
                  type="text" 
                  placeholder="Cari rumah pompa..." 
                  v-model="searchQuery"
                  class="pl-8 w-full sm:w-[250px]"
                />
              </div>
              <Select v-model="statusFilter">
                <SelectTrigger class="w-[180px]">
                  <SelectValue placeholder="Pilih status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Status</SelectItem>
                  <SelectItem value="active">Aktif</SelectItem>
                  <SelectItem value="warning">Perlu Perhatian</SelectItem>
                  <SelectItem value="inactive">Tidak Aktif</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="flex gap-2">
              <Link :href="route('admin.database.create')" class="inline-flex">
                <Button>
                  <Plus class="mr-2 h-4 w-4" />
                  Tambah Rumah Pompa
                </Button>
              </Link>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>
                <div class="flex items-center cursor-pointer" @click="sortBy('name')">
                  Nama
                  <ChevronUp v-if="sortColumn === 'name' && sortDirection === 'asc'" class="ml-1 h-4 w-4" />
                  <ChevronDown v-else-if="sortColumn === 'name' && sortDirection === 'desc'" class="ml-1 h-4 w-4" />
                </div>
              </TableHead>
              <TableHead>Lokasi</TableHead>
              <TableHead>
                <div class="flex items-center cursor-pointer" @click="sortBy('capacity')">
                  Kapasitas
                  <ChevronUp v-if="sortColumn === 'capacity' && sortDirection === 'asc'" class="ml-1 h-4 w-4" />
                  <ChevronDown v-else-if="sortColumn === 'capacity' && sortDirection === 'desc'" class="ml-1 h-4 w-4" />
                </div>
              </TableHead>
              <TableHead>
                <div class="flex items-center cursor-pointer" @click="sortBy('status')">
                  Status
                  <ChevronUp v-if="sortColumn === 'status' && sortDirection === 'asc'" class="ml-1 h-4 w-4" />
                  <ChevronDown v-else-if="sortColumn === 'status' && sortDirection === 'desc'" class="ml-1 h-4 w-4" />
                </div>
              </TableHead>
              <TableHead>Jumlah Pompa</TableHead>
              <TableHead>Terakhir Diperbarui</TableHead>
              <TableHead class="text-right">Aksi</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="pumpHouse in filteredPumpHouses" :key="pumpHouse.id">
              <TableCell>
                <div class="flex items-center">
                  <div class="h-10 w-10 rounded-md bg-muted flex items-center justify-center mr-3">
                    <Home class="h-5 w-5 text-muted-foreground" />
                  </div>
                  <span>{{ pumpHouse.name }}</span>
                </div>
              </TableCell>
              <TableCell>{{ pumpHouse.address }}</TableCell>
              <TableCell>{{ pumpHouse.capacity }}</TableCell>
              <TableCell>
                <Badge :variant="getBadgeVariant(pumpHouse.status)">
                  {{ pumpHouse.status }}
                </Badge>
              </TableCell>
              <TableCell>{{ pumpHouse.pump_count }}</TableCell>
              <TableCell class="text-sm text-muted-foreground">{{ formatTimeAgo(pumpHouse.last_updated) }}</TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Link :href="route('admin.database.show', pumpHouse.id)">
                    <Button variant="ghost" size="icon">
                      <Eye class="h-4 w-4" />
                    </Button>
                  </Link>
                  <Link :href="route('admin.database.edit', pumpHouse.id)">
                    <Button variant="ghost" size="icon">
                      <Edit class="h-4 w-4" />
                    </Button>
                  </Link>
                  <Button variant="ghost" size="icon" @click="confirmDelete(pumpHouse)" class="text-destructive">
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="filteredPumpHouses.length === 0">
              <TableCell colspan="7" class="py-6 text-center text-muted-foreground">
                Tidak ada data rumah pompa yang ditemukan
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
        
        <div class="flex items-center justify-between px-4 py-3 border-t border-border">
          <div class="text-sm text-muted-foreground">
            Menampilkan {{ filteredPumpHouses.length }} dari {{ pumpHouses.length }} rumah pompa
          </div>
          <Pagination
            :items-per-page="itemsPerPage"
            :total="pumpHouses.length"
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
                      :variant="item.value === page.current ? 'default' : 'outline'"
                    >
                      {{ item.value }}
                    </Button>
                  </PaginationListItem>
                  <PaginationEllipsis
                    v-else-if="item.type === 'ellipsis'"
                    :index="index"
                  />
                </template>
                <PaginationNext />
                <PaginationLast />
              </PaginationList>
            </template>
          </Pagination>
        </div>
      </Card>
      
      <!-- Delete Confirmation Modal -->
      <AlertDialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Konfirmasi Hapus</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus rumah pompa "{{ pumpHouseToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showDeleteModal = false">Batal</AlertDialogCancel>
            <AlertDialogAction @click="deletePumpHouse" :disabled="deleting" class="bg-destructive text-destructive-foreground">
              {{ deleting ? 'Menghapus...' : 'Hapus' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { Link, useForm } from "@inertiajs/vue3"
import { router } from '@inertiajs/vue3';
import {
  Search,
  Plus,
  ChevronUp,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Home,
  Eye,
  Edit,
  Trash2,
  Loader2
} from "lucide-vue-next"

import {
  Card,
  CardContent,
} from "@/Components/ui/card"

import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/Components/ui/table"

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/Components/ui/alert-dialog"

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select"

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

import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Badge } from "@/Components/ui/badge"

import Layout from "@/Layouts/AdminLayout.vue"

defineOptions({ layout: Layout })

// Get pump houses from props (passed from the controller)
const props = defineProps({
  pumpHouses: Array,
})

const searchQuery = ref("")
const statusFilter = ref("all")
const sortColumn = ref("name")
const sortDirection = ref("asc")
const currentPage = ref(1)
const itemsPerPage = ref(10)
const loading = ref(false)
const error = ref(null)

// Delete confirmation
const showDeleteModal = ref(false)
const pumpHouseToDelete = ref(null)
const deleting = ref(false)

const filteredPumpHouses = computed(() => {
  let result = [...props.pumpHouses]

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter((ph) => ph.name.toLowerCase().includes(query) || ph.address.toLowerCase().includes(query))
  }

  // Apply status filter
  if (statusFilter.value !== "all") {
    const statusMap = {
      active: "Aktif",
      warning: "Perlu Perhatian",
      inactive: "Tidak Aktif",
    }
    result = result.filter((ph) => ph.status === statusMap[statusFilter.value])
  }

  // Apply sorting
  result.sort((a, b) => {
    let valueA = a[sortColumn.value]
    let valueB = b[sortColumn.value]

    // Handle capacity sorting (remove units and convert to number)
    if (sortColumn.value === "capacity") {
      valueA = parseInt(valueA.split(" ")[0])
      valueB = parseInt(valueB.split(" ")[0])
    }

    if (sortDirection.value === "asc") {
      return valueA > valueB ? 1 : -1
    } else {
      return valueA < valueB ? 1 : -1
    }
  })

  // Apply pagination
  const startIndex = (currentPage.value - 1) * itemsPerPage.value
  const endIndex = startIndex + itemsPerPage.value
  return result.slice(startIndex, endIndex)
})

const totalPages = computed(() => {
  return Math.ceil(props.pumpHouses.length / itemsPerPage.value)
})

const sortBy = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc"
  } else {
    sortColumn.value = column
    sortDirection.value = "asc"
  }
}

const getBadgeVariant = (status) => {
  if (status === 'Aktif') return 'success'
  if (status === 'Perlu Perhatian') return 'warning'
  if (status === 'Tidak Aktif') return 'destructive'
  return 'default'
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) {
    return `${diffInSeconds} detik yang lalu`
  }

  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) {
    return `${diffInMinutes} menit yang lalu`
  }

  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) {
    return `${diffInHours} jam yang lalu`
  }

  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 30) {
    return `${diffInDays} hari yang lalu`
  }

  const diffInMonths = Math.floor(diffInDays / 30)
  return `${diffInMonths} bulan yang lalu`
}

const confirmDelete = (pumpHouse) => {
  pumpHouseToDelete.value = pumpHouse
  showDeleteModal.value = true
}

const deletePumpHouse = () => {
  deleting.value = true

  router.delete(route("admin.database.destroy", pumpHouseToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      pumpHouseToDelete.value = null
    },
    onFinish: () => {
      deleting.value = false
    },
  })
}
</script>