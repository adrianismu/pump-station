<template>
    <div>
      <div class="flex items-center gap-2 mb-6 no-print">
        <Button variant="outline" size="icon" @click="goBack">
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <h1 class="text-2xl font-bold">Detail Laporan</h1>
      </div>
  
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Report Header -->
          <Card>
            <CardHeader>
              <div class="flex justify-between items-start">
                <div>
                  <Badge :variant="getReportStatusVariant(report.status)" class="mb-2">
                    {{ report.status }}
                  </Badge>
                  <CardTitle class="text-2xl">{{ report.title }}</CardTitle>
                  <CardDescription>
                    <div class="flex items-center gap-2 mt-1">
                      <MapPin class="h-4 w-4 text-muted-foreground" />
                      <span>{{ report.location }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                      <Calendar class="h-4 w-4 text-muted-foreground" />
                      <span>{{ formatDateTime(report.created_at) }}</span>
                    </div>
                  </CardDescription>
                </div>
                <DropdownMenu class="no-print">
                  <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon">
                      <MoreVertical class="h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="openStatusDialog">
                      <Edit class="mr-2 h-4 w-4" />
                      <span>Ubah Status</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="printReport">
                      <Printer class="mr-2 h-4 w-4" />
                      <span>Cetak Laporan</span>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem class="text-destructive focus:text-destructive" @click="openDeleteDialog">
                      <Trash class="mr-2 h-4 w-4" />
                      <span>Hapus Laporan</span>
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </CardHeader>
            <CardContent>
              <p class="text-sm mb-6">{{ report.description }}</p>
  
              <!-- Report Images -->
              <div v-if="report.images && report.images.length > 0">
                <h3 class="text-sm font-medium mb-2">Gambar Terlampir</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                  <div 
                    v-for="(image, index) in parseImages(report.images)" 
                    :key="index" 
                    class="relative aspect-square rounded-md overflow-hidden border border-border cursor-pointer"
                    @click="openImageViewer(index)"
                  >
                    <img :src="image" alt="Report Image" class="h-full w-full object-cover" />
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
  
          <!-- Response Form -->
          <Card class="no-print">
            <CardHeader>
              <CardTitle>Tanggapi Laporan</CardTitle>
              <CardDescription>
                Tambahkan tanggapan resmi untuk laporan ini
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form @submit.prevent="submitResponse">
                <div class="space-y-4">
                  <div class="grid gap-2">
                    <Label for="response">Tanggapan</Label>
                    <Textarea 
                      id="response" 
                      v-model="responseForm.content" 
                      placeholder="Tulis tanggapan Anda di sini..." 
                      :rows="4"
                      required
                    />
                  </div>
                  <div class="grid gap-2">
                    <Label for="status">Ubah Status</Label>
                    <Select v-model="responseForm.status">
                      <SelectTrigger id="status">
                        <SelectValue placeholder="Pilih status" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Belum Ditanggapi">Belum Ditanggapi</SelectItem>
                        <SelectItem value="Sedang Diproses">Sedang Diproses</SelectItem>
                        <SelectItem value="Selesai">Selesai</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  <div class="flex justify-end">
                    <Button type="submit" :disabled="submitting">
                      <MessageSquare v-if="!submitting" class="mr-2 h-4 w-4" />
                      <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                      {{ submitting ? 'Mengirim...' : 'Kirim Tanggapan' }}
                    </Button>
                  </div>
                </div>
              </form>
            </CardContent>
          </Card>
  
          <!-- Response History -->
          <Card>
            <CardHeader>
              <CardTitle>Riwayat Tanggapan</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="report.responses && report.responses.length > 0">
                <div class="relative pl-6 pb-2">
                  <div class="absolute top-0 left-0 h-full w-px bg-border"></div>
                  <div
                    v-for="(response, index) in report.responses"
                    :key="index"
                    class="relative mb-6"
                  >
                    <div class="absolute -left-6 top-0 flex items-center justify-center w-4 h-4 rounded-full bg-primary z-10"></div>
                    <div class="mb-1 flex items-center justify-between">
                      <p class="text-sm font-medium">{{ response.user ? response.user.name : 'System' }}</p>
                      <p class="text-xs text-muted-foreground">{{ formatDateTime(response.created_at) }}</p>
                    </div>
                    <p class="text-sm mb-2">{{ response.content }}</p>
                    <Badge v-if="response.status_change" :variant="getReportStatusVariant(response.status_change)">
                      Status diubah menjadi: {{ response.status_change }}
                    </Badge>
                  </div>
                </div>
              </div>
              <div v-else class="text-center text-muted-foreground py-8">
                <MessageSquareOff class="h-12 w-12 mx-auto mb-2" />
                <p>Belum ada tanggapan untuk laporan ini</p>
              </div>
            </CardContent>
          </Card>
        </div>
  
        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Status Card -->
          <Card class="no-print">
            <CardHeader>
              <CardTitle>Status Laporan</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-muted-foreground">Status Saat Ini:</span>
                  <Badge :variant="getReportStatusVariant(report.status)">{{ report.status }}</Badge>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-muted-foreground">Tanggal Laporan:</span>
                  <span class="text-sm">{{ formatDateTime(report.created_at) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-muted-foreground">Terakhir Diperbarui:</span>
                  <span class="text-sm">{{ formatDateTime(report.updated_at) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-muted-foreground">Prioritas:</span>
                  <Badge variant="outline" class="bg-warning/10 text-warning border-warning">
                    {{ report.priority || 'Normal' }}
                  </Badge>
                </div>
              </div>
  
              <Separator class="my-4" />
  
              <Button class="w-full" @click="openStatusDialog">
                <Edit class="mr-2 h-4 w-4" />
                Ubah Status
              </Button>
            </CardContent>
          </Card>
  
          <!-- Reporter Information -->
          <Card>
            <CardHeader>
              <CardTitle>Informasi Pelapor</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <User class="h-4 w-4 text-muted-foreground" />
                  <div>
                    <p class="text-sm font-medium">{{ report.reporter_name }}</p>
                    <p class="text-xs text-muted-foreground">Pelapor</p>
                  </div>
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
  
              <Button variant="outline" class="w-full">
                <Phone class="mr-2 h-4 w-4" />
                Hubungi Pelapor
              </Button>
            </CardContent>
          </Card>
  
          <!-- Location Map -->
          <Card v-if="report.latitude && report.longitude">
            <CardHeader>
              <CardTitle>Lokasi</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="h-48 rounded-md overflow-hidden mb-2">
                <div id="report-map" class="h-full w-full bg-muted"></div>
              </div>
              <p class="text-sm">{{ report.location }}</p>
            </CardContent>
          </Card>
  
          <!-- Related Reports -->
          <Card>
            <CardHeader>
              <CardTitle>Laporan Terkait</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="relatedReports.length > 0">
                <div v-for="(relatedReport, index) in relatedReports" :key="index" class="flex items-start gap-3 p-2 rounded-md hover:bg-muted">
                  <div :class="{
                    'h-8 w-8 rounded-full flex items-center justify-center': true,
                    'bg-warning/10 text-warning': relatedReport.status === 'Belum Ditanggapi',
                    'bg-primary/10 text-primary': relatedReport.status === 'Sedang Diproses',
                    'bg-success/10 text-success': relatedReport.status === 'Selesai'
                  }">
                    <FileText class="h-4 w-4" />
                  </div>
                  <div>
                    <Link :href="`/admin/reports/${relatedReport.id}`" class="hover:underline">
                      <p class="text-sm font-medium line-clamp-1">{{ relatedReport.title }}</p>
                      <p class="text-xs text-muted-foreground">{{ formatDateTime(relatedReport.created_at) }}</p>
                    </Link>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-4 text-muted-foreground">
                <FileQuestion class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                <p class="text-sm">Tidak ada laporan terkait</p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
  
      <!-- Status Change Dialog -->
      <Dialog :open="showStatusDialog" @update:open="showStatusDialog = $event" class="no-print">
        <DialogContent class="sm:max-w-[425px]">
          <DialogHeader>
            <DialogTitle>Ubah Status Laporan</DialogTitle>
            <DialogDescription>
              Pilih status baru untuk laporan ini. Perubahan status akan dicatat dalam riwayat tanggapan.
            </DialogDescription>
          </DialogHeader>
          <div class="grid gap-4 py-4">
            <div class="grid gap-2">
              <Label for="status-change">Status</Label>
              <Select v-model="statusForm.status">
                <SelectTrigger id="status-change">
                  <SelectValue placeholder="Pilih status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Belum Ditanggapi">Belum Ditanggapi</SelectItem>
                  <SelectItem value="Sedang Diproses">Sedang Diproses</SelectItem>
                  <SelectItem value="Selesai">Selesai</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="grid gap-2">
              <Label for="status-note">Catatan (Opsional)</Label>
              <Textarea 
                id="status-note" 
                v-model="statusForm.note" 
                placeholder="Tambahkan catatan tentang perubahan status ini..." 
                :rows="3"
              />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" @click="showStatusDialog = false">Batal</Button>
            <Button @click="updateStatus" :disabled="statusUpdating">
              <Loader2 v-if="statusUpdating" class="mr-2 h-4 w-4 animate-spin" />
              {{ statusUpdating ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
  
      <!-- Delete Confirmation Dialog -->
      <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event" class="no-print">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Laporan</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showDeleteDialog = false">Batal</AlertDialogCancel>
            <AlertDialogAction @click="deleteReport" class="bg-destructive text-destructive-foreground">
              Hapus
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
  
      <!-- Image Viewer Dialog -->
      <Dialog :open="showImageViewer" @update:open="showImageViewer = $event" class="max-w-screen-lg">
        <DialogContent class="sm:max-w-[800px] p-0 overflow-hidden">
          <div class="relative">
            <img 
              v-if="currentImage" 
              :src="currentImage" 
              alt="Report Image" 
              class="w-full h-auto max-h-[80vh] object-contain bg-black"
            />
            <Button 
              variant="ghost" 
              size="icon" 
              class="absolute top-2 right-2 bg-black/50 hover:bg-black/70 text-white rounded-full" 
              @click="showImageViewer = false"
            >
              <X class="h-4 w-4" />
            </Button>
            <div class="absolute bottom-0 left-0 right-0 flex justify-between p-4 bg-black/50">
              <Button 
                variant="ghost" 
                size="icon" 
                class="text-white hover:bg-black/30" 
                @click="prevImage" 
                :disabled="currentImageIndex === 0"
              >
                <ChevronLeft class="h-6 w-6" />
              </Button>
              <span class="text-white">{{ currentImageIndex + 1 }} / {{ parseImages(report.images).length }}</span>
              <Button 
                variant="ghost" 
                size="icon" 
                class="text-white hover:bg-black/30" 
                @click="nextImage" 
                :disabled="currentImageIndex === (parseImages(report.images).length - 1)"
              >
                <ChevronRight class="h-6 w-6" />
              </Button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from "vue"
  import { Link, useForm } from "@inertiajs/vue3"
  import { router } from "@inertiajs/vue3"
  import { useToast } from "@/Components/ui/toast"
  import L from "leaflet"
  import "leaflet/dist/leaflet.css"
  import Layout from "@/Layouts/AdminLayout.vue"
  import {
    ChevronLeft,
    Calendar,
    MapPin,
    User,
    Phone,
    Mail,
    MessageSquare,
    MessageSquareOff,
    Edit,
    Printer,
    Trash,
    FileText,
    FileQuestion,
    MoreVertical,
    Loader2,
    X,
    ChevronRight
  } from "lucide-vue-next"
  
  import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
  } from "@/Components/ui/card"
  
  import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
  } from "@/Components/ui/dialog"
  
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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
  } from "@/Components/ui/dropdown-menu"
  
  import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
  } from "@/Components/ui/select"
  
  import { Button } from "@/Components/ui/button"
  import { Textarea } from "@/Components/ui/textarea"
  import { Label } from "@/Components/ui/label"
  import { Badge } from "@/Components/ui/badge"
  import { Separator } from "@/Components/ui/separator"
  import { useDateUtils } from "@/composables/useDateUtils"
  import { useStatusUtils } from "@/composables/useStatusUtils"
  
  defineOptions({ layout: Layout })
  
  // Props from Inertia
  const props = defineProps({
    report: Object,
    relatedReports: Array,
  })
  
  // State variables
  const { toast } = useToast()
  
  // Form state
  const responseForm = useForm({
    content: '',
    status: props.report.status
  })
  
  const statusForm = useForm({
    status: props.report.status,
    note: ''
  })
  
  const submitting = ref(false)
  const statusUpdating = ref(false)
  
  // Dialog state
  const showStatusDialog = ref(false)
  const showDeleteDialog = ref(false)
  const showImageViewer = ref(false)
  const currentImageIndex = ref(0)
  const currentImage = computed(() => {
    const images = parseImages(props.report.images)
    if (!images || images.length === 0) return null
    return images[currentImageIndex.value]
  })
  
  // Initialize map
  const mapInitialized = ref(false);
  
  const initializeMap = () => {
      if (props.report.latitude && props.report.longitude) {
          const lat = props.report.latitude;
          const lng = props.report.longitude;
  
          // Wait for the DOM to be ready
          setTimeout(() => {
              const mapElement = document.getElementById('report-map');
              if (!mapElement) return;
  
              const map = L.map('report-map').setView([lat, lng], 15);
  
              L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              }).addTo(map);
  
              L.marker([lat, lng]).addTo(map)
                  .bindPopup(props.report.location)
                  .openPopup();
  
              mapInitialized.value = true;
          }, 100);
      }
  };
  
  onMounted(() => {
      initializeMap();
  });
  
  // Navigation
  const goBack = () => {
    router.visit('/admin/reports');
  };
  
  // Submit response
  const submitResponse = () => {
    if (!responseForm.content) {
      toast({
        title: "Error",
        description: "Silakan isi tanggapan Anda",
        variant: "destructive"
      })
      return
    }
    
    submitting.value = true
    
    // Use Inertia to submit the form
    router.post(`/admin/reports/${props.report.id}/responses`, responseForm, {
      onSuccess: () => {
        toast({
          title: "Berhasil",
          description: "Tanggapan Anda telah dikirim",
        })
        responseForm.reset('content')
        submitting.value = false
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Gagal mengirim tanggapan. Silakan coba lagi nanti.",
          variant: "destructive"
        })
        submitting.value = false
      }
    })
  }
  
  // Update status
  const updateStatus = () => {
    if (!statusForm.status) {
      toast({
        title: "Error",
        description: "Silakan pilih status",
        variant: "destructive"
      })
      return
    }
    
    statusUpdating.value = true
    
    // Use Inertia to update the status
    router.put(`/admin/reports/${props.report.id}`, statusForm, {
      onSuccess: () => {
        toast({
          title: "Berhasil",
          description: "Status laporan telah diperbarui",
        })
        showStatusDialog.value = false
        statusUpdating.value = false
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Gagal memperbarui status. Silakan coba lagi nanti.",
          variant: "destructive"
        })
        statusUpdating.value = false
      }
    })
  }
  
  // Delete report
  const deleteReport = () => {
    // Use Inertia to delete the report
    router.delete(`/admin/reports/${props.report.id}`, {
      onSuccess: () => {
        toast({
          title: "Berhasil",
          description: "Laporan telah dihapus",
        })
        router.visit('/admin/reports')
      },
      onError: () => {
        toast({
          title: "Error",
          description: "Gagal menghapus laporan. Silakan coba lagi nanti.",
          variant: "destructive"
        })
      }
    })
  }
  
  // Open status dialog
  const openStatusDialog = () => {
    statusForm.status = props.report.status
    statusForm.note = ''
    showStatusDialog.value = true
  }
  
  // Open delete dialog
  const openDeleteDialog = () => {
    showDeleteDialog.value = true
  }
  
  // Image viewer functions
  const openImageViewer = (index) => {
    currentImageIndex.value = index
    showImageViewer.value = true
  }
  
  const nextImage = () => {
    const images = parseImages(props.report.images)
    if (currentImageIndex.value < images.length - 1) {
      currentImageIndex.value++
    }
  }
  
  const prevImage = () => {
    if (currentImageIndex.value > 0) {
      currentImageIndex.value--
    }
  }
  
  // Print report
  const printReport = () => {
    window.print()
  }
  
  // Use composables untuk utility functions
  const { formatDateTime } = useDateUtils()
  const { getReportStatusVariant } = useStatusUtils()
  
  // Parse images from string JSON or array
  const parseImages = (imagesJson) => {
    if (!imagesJson) return []
    try {
      return typeof imagesJson === "string" ? JSON.parse(imagesJson) : imagesJson
    } catch {
      return []
    }
  }
  </script>
  
  <style>
  /* Ensure the map container has a background */
  #report-map {
    background-color: #f0f0f0;
  }
  
  @media print {
    .no-print {
      display: none;
    }
  }
  </style>