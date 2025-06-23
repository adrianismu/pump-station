<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Edukasi Pencegahan Banjir</h1>
        <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
          Pelajari cara pencegahan banjir, peran rumah pompa, dan langkah-langkah mitigasi 
          untuk menjaga keamanan lingkungan Anda
        </p>
      </div>

      <!-- Search and Filter -->
      <div class="max-w-2xl mx-auto mb-12">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input 
              v-model="searchQuery"
              placeholder="Cari artikel, video, atau panduan..."
              class="pl-10"
            />
          </div>
          <Select v-model="selectedType">
            <SelectTrigger class="w-full sm:w-48">
              <SelectValue placeholder="Semua Tipe" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Tipe</SelectItem>
              <SelectItem value="artikel">Artikel</SelectItem>
              <SelectItem value="video">Video</SelectItem>
              <SelectItem value="panduan">Panduan</SelectItem>
              <SelectItem value="infografis">Infografis</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>


      <!-- All Content -->
      <section>
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold">Semua Konten Edukasi</h2>
          <div class="text-sm text-muted-foreground">
            {{ total }} konten ditemukan
          </div>
        </div>

        <div v-if="paginatedContent.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
          <Card 
            v-for="content in paginatedContent" 
            :key="content.id"
            class="overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
            @click="$inertia.visit(route('public.education.detail', content.id))"
          >
            <!-- Image or Icon -->
            <div class="aspect-video relative">
              <img 
                v-if="content.image && content.image.startsWith('http')"
                :src="content.image"
                :alt="content.title"
                class="w-full h-full object-cover"
                @error="$event.target.style.display = 'none'"
              />
              <img 
                v-else-if="content.image && !content.image.startsWith('http')"
                :src="`/storage/${content.image}`"
                :alt="content.title"
                class="w-full h-full object-cover"
                @error="$event.target.style.display = 'none'"
              />
              <div 
                v-else
                class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center"
              >
                <component 
                  :is="getContentIcon(content.type)" 
                  class="h-12 w-12 text-gray-600"
                />
              </div>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <Badge :variant="getBadgeVariant(content.type)" size="sm">
                  {{ content.type }}
                </Badge>
              </div>
              <h3 class="font-semibold mb-2 line-clamp-2">{{ content.title }}</h3>
              <p class="text-sm text-muted-foreground mb-4 line-clamp-3">
                {{ content.description || content.content.substring(0, 120) }}...
              </p>
              <div class="flex items-center justify-between">
                <span class="text-xs text-muted-foreground">
                  {{ formatDate(content.created_at) }}
                </span>
                <Button variant="ghost" size="sm">
                  <ArrowRight class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </Card>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
            <Search class="h-8 w-8 text-muted-foreground" />
          </div>
          <h3 class="text-lg font-semibold mb-2">Tidak ada konten ditemukan</h3>
          <p class="text-muted-foreground mb-4">
            Coba ubah kata kunci pencarian atau filter tipe konten
          </p>
          <Button variant="outline" @click="resetFilters">
            Reset Filter
          </Button>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center mt-12">
          <div class="flex items-center space-x-2">
            <!-- Previous Button -->
            <Button 
              variant="outline" 
              size="sm"
              :disabled="currentPage <= 1"
              @click="changePage(currentPage - 1)"
            >
              ← Sebelumnya
            </Button>
            
            <!-- Page Numbers -->
            <div class="flex items-center space-x-1">
              <template v-for="page in getPageNumbers()" :key="page">
                <Button
                  v-if="page !== '...'"
                  :variant="page === currentPage ? 'default' : 'outline'"
                  size="sm"
                  class="min-w-[40px]"
                  @click="changePage(page)"
                >
                  {{ page }}
                </Button>
                <span v-else class="px-2 text-muted-foreground">...</span>
              </template>
            </div>
            
            <!-- Next Button -->
            <Button 
              variant="outline" 
              size="sm"
              :disabled="currentPage >= totalPages"
              @click="changePage(currentPage + 1)"
            >
              Selanjutnya →
            </Button>
          </div>
        </div>
      </section>

      <!-- CTA Section -->
      <section class="mt-20">
        <Card class="p-12 bg-gradient-to-br from-blue-50 to-blue-100 max-w-4xl mx-auto text-center">
          <h2 class="text-3xl font-bold mb-4">Butuh Informasi Lebih Lanjut?</h2>
          <p class="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
            Jika Anda memiliki pertanyaan atau memerlukan bantuan terkait rumah pompa, 
            jangan ragu untuk menghubungi kami atau membuat laporan
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" as="a" :href="route('public.reports')" class="text-lg px-8">
              <FileText class="mr-2 h-5 w-5" />
              Buat Laporan
            </Button>
            <Button size="lg" variant="outline" as="a" :href="route('public.map')" class="text-lg px-8">
              <Map class="mr-2 h-5 w-5" />
              Lihat Peta
            </Button>
          </div>
        </Card>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Badge } from '@/Components/ui/badge'
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { 
  Search, 
  ExternalLink, 
  ArrowRight, 
  FileText, 
  Map,
  BookOpen,
  Video,
  FileImage,
  Clipboard
} from 'lucide-vue-next'
import { useDateUtils } from '@/composables/useDateUtils'

defineOptions({ layout: PublicLayout })

const props = defineProps({
  educationContent: Object, // Paginated data from Laravel
  filters: Object, // Current filters
})

// Search and filter state
const searchQuery = ref(props.filters?.search || '')
const selectedType = ref(props.filters?.type || 'all')



// All content for current page
const paginatedContent = computed(() => {
  if (!props.educationContent?.data) return []
  return props.educationContent.data
})

// Pagination info
const currentPage = computed(() => props.educationContent?.current_page || 1)
const totalPages = computed(() => props.educationContent?.last_page || 1)
const total = computed(() => props.educationContent?.total || 0)
const perPage = computed(() => props.educationContent?.per_page || 12)

// Use composable untuk date formatting
const { formatDate } = useDateUtils()

// Helper functions
const getContentIcon = (type) => {
  if (!type) return BookOpen
  
  const lowerType = type.toLowerCase()
  const iconMap = {
    'artikel': BookOpen,
    'video': Video,
    'panduan': Clipboard,
    'infografis': FileImage,
  }
  return iconMap[lowerType] || BookOpen
}

const getBadgeVariant = (type) => {
  if (!type) return 'default'
  
  const lowerType = type.toLowerCase()
  const variantMap = {
    'artikel': 'default',
    'video': 'destructive',
    'panduan': 'outline',
    'infografis': 'secondary',
  }
  return variantMap[lowerType] || 'default'
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedType.value = 'all'
  performSearch()
}

const performSearch = () => {
  const searchParams = new URLSearchParams()
  
  if (searchQuery.value) {
    searchParams.append('search', searchQuery.value)
  }
  
  if (selectedType.value && selectedType.value !== 'all') {
    searchParams.append('type', selectedType.value)
  }
  
  const url = route('public.education') + (searchParams.toString() ? '?' + searchParams.toString() : '')
  router.visit(url, { preserveState: true })
}

const changePage = (page) => {
  const searchParams = new URLSearchParams(window.location.search)
  searchParams.set('page', page)
  
  const url = route('public.education') + '?' + searchParams.toString()
  router.visit(url, { preserveState: true })
}

// Debounced search
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    performSearch()
  }, 500)
}

// Watch for changes
watch(searchQuery, debouncedSearch)
watch(selectedType, performSearch)

// Generate page numbers for pagination
const getPageNumbers = () => {
  const pages = []
  const current = currentPage.value
  const total = totalPages.value
  
  if (total <= 7) {
    // Show all pages if total is 7 or less
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Show first page
    pages.push(1)
    
    if (current > 4) {
      pages.push('...')
    }
    
    // Show pages around current
    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)
    
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
    
    if (current < total - 3) {
      pages.push('...')
    }
    
    // Show last page
    if (total > 1) {
      pages.push(total)
    }
  }
  
  return pages
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style> 
 