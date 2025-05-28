<template>
    <div>
      <div class="flex items-center gap-2 mb-6">
        <Button variant="outline" size="icon" @click="$router?.back?.()">
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <h1 class="text-2xl font-bold">Detail Konten Edukasi</h1>
      </div>
  
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Content Header -->
          <Card class="overflow-hidden">
            <div class="relative h-64">
              <img :src="educationContent.image" alt="Content Thumbnail" class="h-full w-full object-cover" />
              <div class="absolute top-4 right-4">
                <Badge :variant="getTypeVariant(educationContent.type)">
                  {{ educationContent.type }}
                </Badge>
              </div>
            </div>
            <CardContent class="p-6">
              <div class="flex items-center gap-2 mb-2">
                <Calendar class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm text-muted-foreground">{{ formatDate(educationContent.date) }}</span>
                <div class="flex-1"></div>
                <div class="flex items-center gap-1">
                  <Eye class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm text-muted-foreground">{{ educationContent.views }} pembaca</span>
                </div>
              </div>
              <h2 class="text-2xl font-semibold mb-4">{{ educationContent.title }}</h2>
              <p class="text-muted-foreground mb-4">{{ educationContent.description }}</p>
            </CardContent>
          </Card>
  
          <!-- Content Body -->
          <Card>
            <CardHeader>
              <CardTitle>Konten</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="educationContent.type === 'Video'" class="mb-6">
                <div class="aspect-video bg-muted rounded-md flex items-center justify-center mb-4">
                  <iframe 
                    v-if="getYoutubeEmbedUrl(educationContent.video_url)" 
                    :src="getYoutubeEmbedUrl(educationContent.video_url)" 
                    class="w-full h-full rounded-md" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                  ></iframe>
                  <div v-else class="text-center p-8">
                    <Video class="h-12 w-12 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground">Video tidak tersedia</p>
                  </div>
                </div>
              </div>
  
              <div v-if="educationContent.type === 'Infografis'" class="mb-6">
                <div class="bg-muted rounded-md flex items-center justify-center mb-4">
                  <img 
                    :src="educationContent.infographic_url || educationContent.image" 
                    alt="Infografis" 
                    class="max-w-full rounded-md"
                  />
                </div>
              </div>
  
              <div class="prose max-w-none">
                <div v-html="formattedContent"></div>
              </div>
            </CardContent>
          </Card>
  
          <!-- Related Content -->
          <Card>
            <CardHeader>
              <CardTitle>Konten Terkait</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="relatedContent.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div 
                  v-for="content in relatedContent" 
                  :key="content.id" 
                  class="flex gap-3 p-3 rounded-md hover:bg-muted/50 transition cursor-pointer"
                  @click="navigateToContent(content.id)"
                >
                  <div class="h-16 w-16 rounded-md overflow-hidden shrink-0">
                    <img :src="content.image" alt="Related Content" class="h-full w-full object-cover" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <Badge :variant="getTypeVariant(content.type)" class="mb-1">{{ content.type }}</Badge>
                    <h4 class="font-medium text-sm line-clamp-2">{{ content.title }}</h4>
                    <p class="text-xs text-muted-foreground mt-1">{{ formatDate(content.date) }}</p>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-muted-foreground">
                <FileQuestion class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                <p class="text-sm">Tidak ada konten terkait</p>
              </div>
            </CardContent>
          </Card>
        </div>
  
        <!-- Sidebar -->
        <div>
          <!-- Quick Actions -->
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Tindakan Cepat</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Button class="w-full justify-start" @click="openEditModal">
                <Edit class="mr-2 h-4 w-4" />
                Edit Konten
              </Button>
              <Button variant="outline" class="w-full justify-start" @click="openDeleteDialog">
                <Trash2 class="mr-2 h-4 w-4" />
                Hapus Konten
              </Button>
              <Button variant="outline" class="w-full justify-start">
                <Share class="mr-2 h-4 w-4" />
                Bagikan
              </Button>
            </CardContent>
          </Card>
  
          <!-- Content Info -->
          <Card class="mb-6">
            <CardHeader>
              <CardTitle>Informasi Konten</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div>
                  <p class="text-sm text-muted-foreground mb-1">Tanggal Publikasi</p>
                  <div class="flex items-center gap-2">
                    <Calendar class="h-4 w-4 text-muted-foreground" />
                    <p class="font-medium">{{ formatDate(educationContent.date) }}</p>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground mb-1">Tipe Konten</p>
                  <div class="flex items-center gap-2">
                    <FileText v-if="educationContent.type === 'Artikel'" class="h-4 w-4 text-muted-foreground" />
                    <Video v-else-if="educationContent.type === 'Video'" class="h-4 w-4 text-muted-foreground" />
                    <ImageIcon v-else-if="educationContent.type === 'Infografis'" class="h-4 w-4 text-muted-foreground" />
                    <p class="font-medium">{{ educationContent.type }}</p>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground mb-1">Jumlah Pembaca</p>
                  <div class="flex items-center gap-2">
                    <Eye class="h-4 w-4 text-muted-foreground" />
                    <p class="font-medium">{{ educationContent.views }} pembaca</p>
                  </div>
                </div>
                <div>
                  <p class="text-sm text-muted-foreground mb-1">Terakhir Diperbarui</p>
                  <div class="flex items-center gap-2">
                    <Clock class="h-4 w-4 text-muted-foreground" />
                    <p class="font-medium">{{ formatDate(educationContent.updated_at || educationContent.date) }}</p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
  
          <!-- Tags -->
          <Card>
            <CardHeader>
              <CardTitle>Tag</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex flex-wrap gap-2">
                <Badge variant="outline" v-for="(tag, index) in tags" :key="index">
                  {{ tag }}
                </Badge>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
  
      <!-- Edit Modal -->
      <Dialog :open="showEditModal" @update:open="showEditModal = $event">
        <DialogContent class="sm:max-w-[600px]">
          <DialogHeader>
            <DialogTitle>Edit Konten Edukasi</DialogTitle>
          </DialogHeader>
          
          <form @submit.prevent="submitEditForm">
            <div class="grid gap-4 py-4">
              <div class="grid gap-2">
                <Label for="title">Judul</Label>
                <Input id="title" v-model="form.title" required />
              </div>
              
              <div class="grid gap-2">
                <Label for="description">Deskripsi</Label>
                <Textarea id="description" v-model="form.description" required />
              </div>
              
              <div class="grid gap-2">
                <Label for="type">Tipe Konten</Label>
                <Select v-model="form.type">
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih tipe konten" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Artikel">Artikel</SelectItem>
                    <SelectItem value="Video">Video</SelectItem>
                    <SelectItem value="Infografis">Infografis</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="grid gap-2">
                <Label for="image">URL Gambar</Label>
                <Input id="image" v-model="form.image" type="url" required />
              </div>
              
              <div v-if="form.type === 'Video'" class="grid gap-2">
                <Label for="video_url">URL Video</Label>
                <Input id="video_url" v-model="form.video_url" type="url" placeholder="https://www.youtube.com/watch?v=..." />
              </div>
              
              <div v-if="form.type === 'Infografis'" class="grid gap-2">
                <Label for="infographic_url">URL Infografis</Label>
                <Input id="infographic_url" v-model="form.infographic_url" type="url" placeholder="https://example.com/infographic.jpg" />
              </div>
              
              <div class="grid gap-2">
                <Label for="content">Konten</Label>
                <Textarea id="content" v-model="form.content" rows="6" required />
              </div>
              
              <div class="grid gap-2">
                <Label for="tags">Tag (pisahkan dengan koma)</Label>
                <Input id="tags" v-model="form.tags" placeholder="banjir, pompa air, edukasi" />
              </div>
            </div>
            
            <DialogFooter>
              <Button type="button" variant="outline" @click="showEditModal = false">
                Batal
              </Button>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
  
      <!-- Delete Confirmation Dialog -->
      <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Konfirmasi Hapus</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus konten "{{ educationContent.title }}"? Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="showDeleteDialog = false">Batal</AlertDialogCancel>
            <AlertDialogAction @click="deleteContent" :disabled="isDeleting" class="bg-destructive text-destructive-foreground">
              {{ isDeleting ? 'Menghapus...' : 'Hapus' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from "vue";
  import { useForm } from "@inertiajs/vue3";
  import { router, usePage } from '@inertiajs/vue3';
  import {
    ChevronLeft,
    Calendar,
    Eye,
    Edit,
    Trash2,
    FileQuestion,
    FileText,
    Video,
    Clock,
    Share,
    ImageIcon,
  } from "lucide-vue-next";
  import Layout from "@/Layouts/AdminLayout.vue";
  
  import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
  } from "@/Components/ui/card";
  
  import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
  } from "@/Components/ui/dialog";
  
  import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
  } from "@/Components/ui/alert-dialog";
  
  import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
  } from "@/Components/ui/select";
  
  import { Button } from "@/Components/ui/button";
  import { Input } from "@/Components/ui/input";
  import { Textarea } from "@/Components/ui/textarea";
  import { Label } from "@/Components/ui/label";
  import { Badge } from "@/Components/ui/badge";
  
  defineOptions({ layout: Layout });
  
  const props = defineProps({
    educationContent: {
      type: Object,
      required: true,
    },
    relatedContents: {
      type: Array,
      default: () => [],
    },
  });
  
  // State
  const showEditModal = ref(false);
  const showDeleteDialog = ref(false);
  const isDeleting = ref(false);
  
  // Form for editing education content
  const form = useForm({
    id: props.educationContent.id,
    title: props.educationContent.title,
    description: props.educationContent.description,
    type: props.educationContent.type,
    image: props.educationContent.image,
    content: props.educationContent.content,
    video_url: props.educationContent.video_url || '',
    infographic_url: props.educationContent.infographic_url || '',
    tags: (props.educationContent.tags || []).join(', '),
  });
  
  // Computed properties
  const formattedContent = computed(() => {
    // Convert line breaks to paragraphs
    return props.educationContent.content
      .split('\n')
      .filter(paragraph => paragraph.trim() !== '')
      .map(paragraph => `<p>${paragraph}</p>`)
      .join('');
  });
  
  const tags = computed(() => {
    return props.educationContent.tags || ['banjir', 'edukasi', 'pompa air'];
  });
  
  const relatedContent = computed(() => {
    return props.relatedContents.slice(0, 4);
  });
  
  const route = usePage().props.route;
  
  // Methods
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  };
  
  const getTypeVariant = (type) => {
    if (type === 'Artikel') return 'default';
    if (type === 'Video') return 'destructive';
    if (type === 'Infografis') return 'warning';
    return 'default';
  };
  
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
  
  const navigateToContent = (id) => {
    router.visit(route('admin.education.show', id));
  };
  
  const openEditModal = () => {
    showEditModal.value = true;
  };
  
  const submitEditForm = () => {
    // Convert comma-separated tags to array before submitting
    const formData = { ...form };
    formData.tags = form.tags.split(',').map(tag => tag.trim()).filter(tag => tag);
    
    form.put(route('admin.education.update', props.educationContent.id), {
      onSuccess: () => {
        showEditModal.value = false;
      },
    });
  };
  
  const openDeleteDialog = () => {
    showDeleteDialog.value = true;
  };
  
  const deleteContent = () => {
    isDeleting.value = true;
    
    router.delete(route('admin.education.destroy', props.educationContent.id), {
      onSuccess: () => {
        router.visit(route('admin.education'));
      },
      onFinish: () => {
        isDeleting.value = false;
      },
    });
  };
  </script>
  
  <style>
  .prose p {
    margin-bottom: 1rem;
  }
  </style>