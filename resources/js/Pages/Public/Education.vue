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

      <!-- Featured Content -->
      <section v-if="featuredContent.length > 0" class="mb-16">
        <h2 class="text-2xl font-bold mb-8 text-center">Konten Unggulan</h2>
        <div class="grid gap-8 lg:grid-cols-2 max-w-6xl mx-auto">
          <Card 
            v-for="content in featuredContent" 
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
                class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center"
              >
                <component 
                  :is="getContentIcon(content.type)" 
                  class="h-16 w-16 text-white"
                />
              </div>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <Badge :variant="getBadgeVariant(content.type)">
                  {{ content.type }}
                </Badge>
                <span class="text-sm text-muted-foreground">
                  {{ formatDate(content.created_at) }}
                </span>
              </div>
              <h3 class="text-xl font-semibold mb-3 line-clamp-2">{{ content.title }}</h3>
              <p class="text-muted-foreground mb-4 line-clamp-3">
                {{ content.description || content.content.substring(0, 200) }}...
              </p>
              <Button variant="outline" size="sm">
                <ExternalLink class="mr-2 h-4 w-4" />
                Baca Selengkapnya
              </Button>
            </div>
          </Card>
        </div>
      </section>

      <!-- All Content -->
      <section>
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-2xl font-bold">Semua Konten Edukasi</h2>
          <div class="text-sm text-muted-foreground">
            {{ filteredContent.length }} konten ditemukan
          </div>
        </div>

        <div v-if="filteredContent.length > 0" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
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
        <div v-if="filteredContent.length > 0 && totalPages > 1" class="flex justify-center mt-12">
          <Pagination
            :items-per-page="itemsPerPage"
            :total="filteredContent.length"
            :sibling-count="1"
            show-edges
            :default-page="currentPage"
            v-model:page="currentPage"
          >
            <template v-slot:default="{ page }">
              <PaginationList v-if="page?.items" class="flex items-center gap-1">
                <PaginationFirst />
                <PaginationPrev />
                <template v-for="(item, index) in page.items" :key="index">
                  <PaginationListItem
                    v-if="item?.type === 'page'"
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
                    v-else
                    :index="index"
                  />
                </template>
                <PaginationNext />
                <PaginationLast />
              </PaginationList>
            </template>
          </Pagination>
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
import { ref, computed } from 'vue'
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
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/Components/ui/pagination'
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
})

const searchQuery = ref('')
const selectedType = ref('all')
const currentPage = ref(1)
const itemsPerPage = 9

// Featured content (first 2 items)
const featuredContent = computed(() => {
  if (!props.educationContent?.data) return []
  return props.educationContent.data.slice(0, 2)
})

// All content for filtering
const allContent = computed(() => {
  if (!props.educationContent?.data) return []
  return props.educationContent.data
})

// Filtered content based on search and type
const filteredContent = computed(() => {
  let filtered = allContent.value || []

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(content => 
      content?.title?.toLowerCase().includes(query) ||
      content?.content?.toLowerCase().includes(query)
    )
  }

  if (selectedType.value && selectedType.value !== 'all') {
    filtered = filtered.filter(content => content?.type === selectedType.value)
  }

  return filtered
})

// Paginated content
const totalPages = computed(() => Math.ceil(filteredContent.value.length / itemsPerPage))

const paginatedContent = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredContent.value.slice(start, start + itemsPerPage)
})

// Use composable untuk date formatting
const { formatDate } = useDateUtils()

// Helper functions
const getContentIcon = (type) => {
  if (!type) return BookOpen // Default jika type null
  
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
  if (!type) return 'default' // Default jika type null
  
  const lowerType = type.toLowerCase()
  const variantMap = {
    'artikel': 'default',
    'video': 'secondary',
    'panduan': 'outline',
    'infografis': 'destructive',
  }
  return variantMap[lowerType] || 'default'
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedType.value = 'all'
  currentPage.value = 1
}

// Reset page when filters change
const resetPage = () => {
  currentPage.value = 1
}

// Watch for filter changes
import { watch } from 'vue'
watch([searchQuery, selectedType], resetPage)
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
 