<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Laporan Publik</h1>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <Card>
        <CardContent class="pt-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-muted-foreground">Total Laporan</p>
              <h2 class="text-3xl font-bold">{{ props.reports.length }}</h2>
            </div>
            <div class="bg-primary/10 p-2 rounded-full">
              <FileText class="w-5 h-5 text-primary" />
            </div>
          </div>
          <div class="mt-2 text-xs text-muted-foreground">
            <span>Bulan ini</span>
          </div>
        </CardContent>
      </Card>
      
      <Card>
        <CardContent class="pt-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-muted-foreground">Belum Ditanggapi</p>
              <h2 class="text-3xl font-bold">{{ pendingCount }}</h2>
            </div>
            <div class="bg-warning/10 p-2 rounded-full">
              <Clock class="w-5 h-5 text-warning" />
            </div>
          </div>
          <div class="mt-2 text-xs text-muted-foreground">
            <span>Perlu perhatian segera</span>
          </div>
        </CardContent>
      </Card>
      
      <Card>
        <CardContent class="pt-6">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-muted-foreground">Selesai Ditangani</p>
              <h2 class="text-3xl font-bold">{{ completedCount }}</h2>
            </div>
            <div class="bg-success/10 p-2 rounded-full">
              <CheckCircle class="w-5 h-5 text-success" />
            </div>
          </div>
          <div class="mt-2 text-xs text-muted-foreground">
            <span>{{ completedPercentage }}% dari total laporan</span>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filter & Actions -->
    <Card class="mb-6">
      <CardContent class="pt-6">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
          <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                type="text"
                placeholder="Cari laporan..."
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
                <SelectItem value="pending">Belum Ditanggapi</SelectItem>
                <SelectItem value="processing">Sedang Diproses</SelectItem>
                <SelectItem value="completed">Selesai</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex gap-2">
            <Button variant="outline">
              <Download class="mr-2 h-4 w-4" />
              Export
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Daftar Laporan -->
    <div class="space-y-4">
      <Card
        v-for="report in paginatedReports"
        :key="report.id"
      >
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="md:w-2/3">
              <div class="flex items-center gap-2 mb-2">
                <Badge :variant="getStatusVariant(report.status)">
                  {{ report.status }}
                </Badge>
                <span class="text-xs text-muted-foreground">{{ formatDate(report.created_at) }}</span>
              </div>
              <h3 class="text-lg font-semibold mb-1">{{ report.title }}</h3>
              <p class="text-sm text-muted-foreground mb-2">{{ report.location }}</p>
              <p class="text-sm mb-4">{{ report.description }}</p>
              <div v-if="parseImages(report.images).length > 0" class="flex flex-wrap gap-2">
                <div
                  v-for="(image, index) in parseImages(report.images)"
                  :key="index"
                  class="relative h-16 w-16 rounded-md overflow-hidden border border-border"
                >
                  <img :src="image" alt="Report Image" class="h-full w-full object-cover" />
                </div>
              </div>
              <div v-else class="text-sm text-muted-foreground">
                Tidak ada gambar terlampir
              </div>
            </div>
            <div class="md:w-1/3 border-t md:border-t-0 md:border-l border-border pt-4 md:pt-0 md:pl-4">
              <h4 class="font-medium mb-2">Informasi Pelapor</h4>
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <User class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ report.reporter_name }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <Phone class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ report.reporter_phone }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <Mail class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ report.reporter_email }}</span>
                </div>
              </div>
              <Separator class="my-4" />
              <div class="flex justify-between mb-2">
                <h4 class="font-medium">Tindakan</h4>
                <Button v-if="report.status !== 'Selesai'" variant="link" class="h-auto p-0">Ubah Status</Button>
              </div>
              <div class="space-y-2">
                <Button class="w-full justify-start" @click="viewDetail(report.id)">
                  <MessageSquare class="mr-2 h-4 w-4" /> Tanggapi Laporan
                </Button>
                <Button @click="viewDetail(report.id)" variant="outline" class="w-full justify-start">
                  <Eye class="mr-2 h-4 w-4" /> Lihat Detail
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Tidak ada laporan ditemukan -->
      <Card v-if="paginatedReports.length === 0">
        <CardContent class="p-8 text-center">
          <FileQuestion class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
          <h3 class="text-lg font-semibold mb-1">Tidak ada laporan ditemukan</h3>
          <p class="text-sm text-muted-foreground">Coba ubah filter pencarian Anda</p>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-4 py-3">
        <div class="text-sm text-muted-foreground">
          Menampilkan
          {{
            filteredReports.length === 0
              ? 0
              : (currentPage - 1) * itemsPerPage + 1
          }}
          -
          {{
            Math.min(currentPage * itemsPerPage, filteredReports.length)
          }}
          dari {{ filteredReports.length }} laporan
        </div>
        <Pagination
          :items-per-page="itemsPerPage"
          :total="filteredReports.length"
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
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import {
  FileText,
  Clock,
  CheckCircle,
  Search,
  Download,
  User,
  Phone,
  Mail,
  MessageSquare,
  Eye,
  FileQuestion,
  ChevronLeft,
  ChevronRight,
} from "lucide-vue-next"
import Layout from "@/Layouts/AdminLayout.vue"

import { router } from '@inertiajs/vue3'

import {
  Card,
  CardContent,
} from "@/Components/ui/card"

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
import { Separator } from "@/Components/ui/separator"

// Untuk layout InertiaJS, bisa diaktifkan jika pakai Inertia
defineOptions({ layout: Layout })

const props = defineProps({
  reports: Array,
})

const searchQuery = ref("")
const statusFilter = ref("all")
const currentPage = ref(1)
const itemsPerPage = 5

// Filter laporan sesuai pencarian dan status
const filteredReports = computed(() => {
  let result = [...props.reports]
  // Pencarian
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(
      (report) =>
        report.title.toLowerCase().includes(query) ||
        report.location.toLowerCase().includes(query) ||
        report.description.toLowerCase().includes(query)
    )
  }
  // Filter status
  if (statusFilter.value !== "all") {
    const statusMap = {
      pending: "Belum Ditanggapi",
      processing: "Sedang Diproses",
      completed: "Selesai",
    }
    result = result.filter((report) => report.status === statusMap[statusFilter.value])
  }
  return result
})

// Pagination laporan
const paginatedReports = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage
  const endIndex = startIndex + itemsPerPage
  return filteredReports.value.slice(startIndex, endIndex)
})

// Statistik
const pendingCount = computed(() => {
  return props.reports.filter((report) => report.status === "Belum Ditanggapi").length
})
const completedCount = computed(() => {
  return props.reports.filter((report) => report.status === "Selesai").length
})
const completedPercentage = computed(() => {
  if (props.reports.length === 0) return 0
  return Math.round((completedCount.value / props.reports.length) * 100)
})

// Get badge variant based on status
const getStatusVariant = (status) => {
  if (status === 'Belum Ditanggapi') return 'warning'
  if (status === 'Sedang Diproses') return 'default'
  if (status === 'Selesai') return 'success'
  return 'default'
}

// Parsing gambar dari string JSON atau array
const parseImages = (imagesJson) => {
  if (!imagesJson) return []
  try {
    return typeof imagesJson === "string" ? JSON.parse(imagesJson) : imagesJson
  } catch {
    return []
  }
}

// Format tanggal ke Indonesia
const formatDate = (dateString) => {
  if (!dateString) return "Tidak ada data"
  const date = new Date(dateString)
  return date.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

const viewDetail = (reportId) => {
  router.visit(`/admin/reports/${reportId}`);
}
</script>