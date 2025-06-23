<template>
  <div class="py-12">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Breadcrumb -->
      <nav class="flex items-center space-x-2 text-sm text-muted-foreground mb-8">
        <Link :href="route('public.landing')" class="hover:text-primary">
          Beranda
        </Link>
        <ChevronRight class="h-4 w-4" />
        <Link :href="route('public.education')" class="hover:text-primary">
          Edukasi
        </Link>
        <ChevronRight class="h-4 w-4" />
        <span class="text-foreground">{{ content.title }}</span>
      </nav>

      <!-- Content Header -->
      <div class="mb-8 max-w-4xl mx-auto">
        <div class="flex items-center gap-3 mb-4">
          <Badge :variant="getBadgeVariant(content?.type)">
            {{ content?.type || 'Unknown' }}
          </Badge>
          <span class="text-sm text-muted-foreground">
            {{ formatDate(content?.created_at) }}
          </span>
        </div>
        
        <h1 class="text-4xl font-bold mb-4">{{ content?.title || 'Untitled' }}</h1>
        
        <!-- Share Buttons -->
        <div class="flex items-center gap-4">
          <span class="text-sm text-muted-foreground">Bagikan:</span>
          <div class="flex items-center gap-2">
            <Button 
              variant="outline" 
              size="sm"
              @click="shareContent('facebook')"
            >
              <Share2 class="h-4 w-4 mr-2" />
              Facebook
            </Button>
            <Button 
              variant="outline" 
              size="sm"
              @click="shareContent('twitter')"
            >
              <Share2 class="h-4 w-4 mr-2" />
              Twitter
            </Button>
            <Button 
              variant="outline" 
              size="sm"
              @click="shareContent('whatsapp')"
            >
              <Share2 class="h-4 w-4 mr-2" />
              WhatsApp
            </Button>
            <Button 
              variant="outline" 
              size="sm"
              @click="copyLink"
            >
              <Copy class="h-4 w-4 mr-2" />
              Salin Link
            </Button>
          </div>
        </div>
      </div>

      <!-- Content Body -->
      <div class="grid gap-8 lg:grid-cols-4 max-w-6xl mx-auto">
        <!-- Main Content -->
        <div class="lg:col-span-3">
          <Card class="p-8">
            <!-- Featured Image/Video/Icon -->
            <div class="aspect-video relative rounded-lg overflow-hidden mb-8">
              <!-- Video Content for Video Type -->
              <div v-if="content?.type === 'Video' && content?.video_url" class="w-full h-full">
                <iframe 
                  v-if="getYoutubeEmbedUrl(content.video_url)"
                  :src="getYoutubeEmbedUrl(content.video_url)" 
                  class="w-full h-full" 
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                  allowfullscreen
                ></iframe>
                <div v-else class="w-full h-full bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center">
                  <div class="text-center p-8">
                    <Video class="h-16 w-16 mx-auto text-red-600 mb-4" />
                    <p class="text-red-600 font-medium">Video tidak dapat dimuat</p>
                    <p class="text-sm text-red-500 mt-2">URL: {{ content.video_url }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Infographic Content for Infografis Type -->
              <div v-else-if="content?.type === 'Infografis' && content?.infographic_url" class="w-full h-full">
                <img 
                  :src="content.infographic_url"
                  :alt="content?.title || 'Infografis'"
                  class="w-full h-full object-contain bg-gray-50"
                  @error="$event.target.style.display = 'none'"
                />
              </div>
              
              <!-- Regular Image -->
              <img 
                v-else-if="content?.image && content.image.startsWith('http')"
                :src="content.image"
                :alt="content?.title || 'Content Image'"
                class="w-full h-full object-cover"
                @error="$event.target.style.display = 'none'"
              />
              <img 
                v-else-if="content?.image && !content.image.startsWith('http')"
                :src="`/storage/${content.image}`"
                :alt="content?.title || 'Content Image'"
                class="w-full h-full object-cover"
                @error="$event.target.style.display = 'none'"
              />
              
              <!-- Default Icon -->
              <div 
                v-else
                class="w-full h-full bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center"
              >
                <component 
                  :is="getContentIcon(content?.type)" 
                  class="h-24 w-24 text-blue-600"
                />
              </div>
            </div>

            <!-- Content Text -->
            <div class="prose prose-lg max-w-none">
              <div v-html="formatContent(content?.content || '')"></div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-8 border-t">
              <Button as="a" :href="route('public.reports')" size="lg">
                <FileText class="mr-2 h-5 w-5" />
                Buat Laporan
              </Button>
              <Button variant="outline" as="a" :href="route('public.map')" size="lg">
                <Map class="mr-2 h-5 w-5" />
                Lihat Peta Rumah Pompa
              </Button>
            </div>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Table of Contents (if content is long) -->
          <Card v-if="tableOfContents.length > 0" class="p-4">
            <h3 class="font-semibold mb-4">Daftar Isi</h3>
            <nav class="space-y-2">
              <a 
                v-for="item in tableOfContents" 
                :key="item.id"
                :href="`#${item.id}`"
                class="block text-sm text-muted-foreground hover:text-primary transition-colors"
              >
                {{ item.text }}
              </a>
            </nav>
          </Card>

          <!-- Quick Actions -->
          <Card class="p-4">
            <h3 class="font-semibold mb-4">Aksi Cepat</h3>
            <div class="space-y-2">
              <Button variant="outline" size="sm" as="a" :href="route('public.reports')" class="w-full justify-start">
                <FileText class="h-4 w-4 mr-2" />
                Buat Laporan
              </Button>
              <Button variant="outline" size="sm" as="a" :href="route('public.map')" class="w-full justify-start">
                <Map class="h-4 w-4 mr-2" />
                Lihat Peta
              </Button>
              <Button variant="outline" size="sm" as="a" :href="route('public.education')" class="w-full justify-start">
                <BookOpen class="h-4 w-4 mr-2" />
                Semua Edukasi
              </Button>
              <Button variant="outline" size="sm" as="a" href="tel:112" class="w-full justify-start">
                <Phone class="h-4 w-4 mr-2" />
                Kontak Darurat
              </Button>
            </div>
          </Card>

          <!-- Content Info -->
          <Card class="p-4">
            <h3 class="font-semibold mb-4">Informasi Konten</h3>
            <div class="space-y-3 text-sm">
              <div>
                <span class="text-muted-foreground">Tipe:</span>
                <Badge :variant="getBadgeVariant(content.type)" class="ml-2">
                  {{ content.type }}
                </Badge>
              </div>
              <div>
                <span class="text-muted-foreground">Dipublikasikan:</span>
                <span class="ml-2">{{ formatDate(content.created_at) }}</span>
              </div>
              <div>
                <span class="text-muted-foreground">Terakhir diperbarui:</span>
                <span class="ml-2">{{ formatDate(content.updated_at) }}</span>
              </div>
            </div>
          </Card>
        </div>
      </div>

      <!-- Related Content -->
      <section v-if="relatedContent && relatedContent.length > 0" class="mt-16">
        <h2 class="text-2xl font-bold mb-8 text-center">Konten Terkait</h2>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-6xl mx-auto">
          <Card 
            v-for="related in relatedContent" 
            :key="related?.id"
            class="overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
            @click="related?.id && $inertia.visit(route('public.education.detail', related.id))"
          >
            <!-- Image or Icon -->
            <div class="aspect-video relative">
              <img 
                v-if="related?.image && related.image.startsWith('http')"
                :src="related.image"
                :alt="related?.title || 'Image'"
                class="w-full h-full object-cover"
                @error="$event.target.style.display = 'none'"
              />
              <img 
                v-else-if="related?.image && !related.image.startsWith('http')"
                :src="`/storage/${related.image}`"
                :alt="related?.title || 'Image'"
                class="w-full h-full object-cover"
                @error="$event.target.style.display = 'none'"
              />
              <div 
                v-else
                class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center"
              >
                <component 
                  :is="getContentIcon(related?.type)" 
                  class="h-12 w-12 text-gray-600"
                />
              </div>
            </div>
            <div class="p-6">
              <div class="flex items-center gap-2 mb-3">
                <Badge :variant="getBadgeVariant(related?.type)" size="sm">
                  {{ related?.type || 'Unknown' }}
                </Badge>
              </div>
              <h3 class="font-semibold mb-2 line-clamp-2">{{ related?.title || 'Untitled' }}</h3>
              <p class="text-sm text-muted-foreground mb-4 line-clamp-3">
                {{ (related?.description || related?.content?.substring(0, 120) || '') }}...
              </p>
              <div class="flex items-center justify-between">
                <span class="text-xs text-muted-foreground">
                  {{ formatDate(related?.created_at) }}
                </span>
                <Button variant="ghost" size="sm">
                  <ArrowRight class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </Card>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'
import { Badge } from '@/Components/ui/badge'
import { 
  ChevronRight,
  Share2,
  Copy,
  FileText,
  Map,
  BookOpen,
  Phone,
  ArrowRight,
  ArrowLeft,
  Video,
  FileImage,
  Clipboard
} from 'lucide-vue-next'
import { useDateUtils } from '@/composables/useDateUtils'

defineOptions({ layout: PublicLayout })

const props = defineProps({
  content: Object,
  relatedContent: Array,
})

const tableOfContents = ref([])

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

const formatContent = (content) => {
  // Convert line breaks to paragraphs
  return content
    .split('\n\n')
    .map(paragraph => `<p class="mb-4">${paragraph.replace(/\n/g, '<br>')}</p>`)
    .join('')
}

const getYoutubeEmbedUrl = (url) => {
  if (!url) return null;
  
  // Extract video ID from YouTube URL
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
  const match = url.match(regExp);
  
  if (match && match[2].length === 11) {
    return `https://www.youtube.com/embed/${match[2]}`;
  }
  
  return null;
};

const shareContent = (platform) => {
  if (!props.content) return
  
  const url = window.location.href
  const title = props.content.title || 'Content'
  const text = `${title} - ${(props.content.content || '').substring(0, 100)}...`
  
  let shareUrl = ''
  
  switch (platform) {
    case 'facebook':
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`
      break
    case 'twitter':
      shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`
      break
    case 'whatsapp':
      shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`
      break
  }
  
  if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400')
  }
}

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href)
    alert('Link berhasil disalin!')
  } catch (err) {
    console.error('Failed to copy: ', err)
    alert('Gagal menyalin link')
  }
}

// Generate table of contents from content headings
const generateTableOfContents = () => {
  // This is a simple implementation - in a real app you might want to parse markdown or HTML
  if (!props.content?.content) return
  
  const content = props.content.content
  const headings = content.match(/^#{1,3}\s+(.+)$/gm)
  
  if (headings) {
    tableOfContents.value = headings.map((heading, index) => {
      const level = heading.match(/^#+/)[0].length
      const text = heading.replace(/^#+\s+/, '')
      const id = `heading-${index}`
      
      return { id, text, level }
    })
  }
}

onMounted(() => {
  generateTableOfContents()
})
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

.prose {
  color: inherit;
}

.prose p {
  margin-bottom: 1rem;
  line-height: 1.7;
}

.prose h1, .prose h2, .prose h3 {
  font-weight: 600;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.prose h1 {
  font-size: 2rem;
}

.prose h2 {
  font-size: 1.5rem;
}

.prose h3 {
  font-size: 1.25rem;
}

.prose ul, .prose ol {
  margin: 1rem 0;
  padding-left: 2rem;
}

.prose li {
  margin-bottom: 0.5rem;
}

.prose strong {
  font-weight: 600;
}

.prose em {
  font-style: italic;
}
</style> 
 