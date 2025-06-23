<template>
    <div>
      <div class="flex items-center gap-2 mb-6">
        <Link :href="route('admin.education')">
          <Button variant="outline" size="icon">
          <ChevronLeft class="h-4 w-4" />
        </Button>
        </Link>
        <h1 class="text-2xl font-bold">Tambah Konten Edukasi Baru</h1>
      </div>
  
      <Card>
        <CardHeader>
          <CardTitle>Informasi Konten</CardTitle>
          <CardDescription>
            Masukkan informasi detail untuk konten edukasi baru.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Basic Information -->
              <div class="space-y-4">
                <div>
                  <Label for="title">Judul</Label>
                  <Input 
                    id="title" 
                    v-model="form.title" 
                    placeholder="Masukkan judul konten" 
                    :class="{ 'border-destructive': form.errors.title }"
                    required
                  />
                  <p v-if="form.errors.title" class="text-destructive text-sm mt-1">{{ form.errors.title }}</p>
                </div>
  
                <div>
                  <Label for="description">Deskripsi Singkat</Label>
                  <Textarea 
                    id="description" 
                    v-model="form.description" 
                    placeholder="Masukkan deskripsi singkat" 
                    :class="{ 'border-destructive': form.errors.description }"
                    required
                  />
                  <p v-if="form.errors.description" class="text-destructive text-sm mt-1">{{ form.errors.description }}</p>
                </div>
  
                <div>
                  <Label for="type">Tipe Konten</Label>
                  <Select 
                    v-model="form.type" 
                    :class="{ 'border-destructive': form.errors.type }"
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Pilih tipe konten" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Artikel">Artikel</SelectItem>
                      <SelectItem value="Video">Video</SelectItem>
                      <SelectItem value="Infografis">Infografis</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.type" class="text-destructive text-sm mt-1">{{ form.errors.type }}</p>
                </div>
  
                <div>
                  <Label for="image">Upload Gambar Thumbnail</Label>
                  <div class="space-y-4">
                  <Input 
                    id="image" 
                      type="file"
                      accept="image/*"
                      @change="handleImageUpload"
                    :class="{ 'border-destructive': form.errors.image }"
                      class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                    required
                  />
                    <p v-if="form.errors.image" class="text-destructive text-sm">{{ form.errors.image }}</p>
                    <p class="text-xs text-muted-foreground">
                      Upload gambar thumbnail konten (format: JPEG, PNG, JPG, GIF - maksimal 2MB)
                    </p>
                    
                    <!-- Image Preview -->
                    <div v-if="imagePreview" class="relative inline-block">
                      <img 
                        :src="imagePreview" 
                        alt="Preview Gambar" 
                        class="h-32 w-48 object-cover rounded-lg border"
                      />
                      <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="absolute top-1 right-1 h-6 w-6 p-0"
                        @click="removeImage"
                      >
                        ×
                      </Button>
                    </div>
                    
                    <!-- Placeholder when no image -->
                    <div v-else class="h-32 w-48 bg-muted rounded-lg border-2 border-dashed border-muted-foreground/25 flex items-center justify-center">
                      <div class="text-center">
                        <ImageIcon class="h-8 w-8 mx-auto text-muted-foreground/50 mb-2" />
                        <p class="text-sm text-muted-foreground">Preview gambar akan muncul di sini</p>
                      </div>
                    </div>
                  </div>
                </div>
  
                <div v-if="isVideo">
                  <Label for="video_url">URL Video</Label>
                  <Input 
                    id="video_url" 
                    v-model="form.video_url" 
                    type="url" 
                    placeholder="https://www.youtube.com/watch?v=..." 
                    :class="{ 'border-destructive': form.errors.video_url }"
                  />
                  <p v-if="form.errors.video_url" class="text-destructive text-sm mt-1">{{ form.errors.video_url }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Masukkan URL YouTube untuk konten video</p>
                </div>
  
                <div v-if="isInfografis">
                  <Label for="infographic">Upload File Infografis</Label>
                  <div class="space-y-4">
                  <Input 
                      id="infographic" 
                      type="file"
                      accept="image/*,.pdf"
                      @change="handleInfographicUpload"
                      :class="{ 'border-destructive': form.errors.infographic }"
                      class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                  />
                    <p v-if="form.errors.infographic" class="text-destructive text-sm">{{ form.errors.infographic }}</p>
                    <p class="text-xs text-muted-foreground">
                      Upload file infografis (format: JPEG, PNG, JPG, GIF, PDF - maksimal 5MB) - Opsional
                    </p>
                    
                    <!-- Infographic Preview -->
                    <div v-if="infographicPreview" class="relative inline-block">
                      <div v-if="infographicPreview.type === 'image'">
                        <img 
                          :src="infographicPreview.url" 
                          alt="Preview Infografis" 
                          class="h-32 w-48 object-cover rounded-lg border"
                        />
                      </div>
                      <div v-else class="h-32 w-48 bg-muted rounded-lg border flex items-center justify-center">
                        <div class="text-center">
                          <svg class="h-8 w-8 mx-auto text-muted-foreground/50 mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 18h12V6h-4V2H4v16zm-2 1V1a1 1 0 011-1h8.414a1 1 0 01.707.293l4.586 4.586A1 1 0 0117 6v13a1 1 0 01-1 1H3a1 1 0 01-1-1z"/>
                          </svg>
                          <p class="text-sm text-muted-foreground">{{ infographicPreview.name }}</p>
                        </div>
                      </div>
                      <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        class="absolute top-1 right-1 h-6 w-6 p-0"
                        @click="removeInfographic"
                      >
                        ×
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
  
              <!-- Content -->
              <div class="space-y-4">
                <div>
                  <Label for="content">Konten</Label>
                  <Textarea 
                    id="content" 
                    v-model="form.content" 
                    placeholder="Masukkan konten lengkap" 
                    rows="10"
                    :class="{ 'border-destructive': form.errors.content }"
                    required
                  />
                  <p v-if="form.errors.content" class="text-destructive text-sm mt-1">{{ form.errors.content }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Untuk artikel, masukkan konten lengkap. Untuk video dan infografis, masukkan deskripsi lengkap.</p>
                </div>
              </div>
            </div>
  
            <!-- Preview -->
            <div>
              <h3 class="text-lg font-medium mb-4">Preview</h3>
              <div class="border border-border rounded-md p-4">
                <div v-if="imagePreview" class="mb-4">
                  <p class="text-sm text-muted-foreground mb-2">Thumbnail:</p>
                  <img :src="imagePreview" alt="Preview" class="h-48 w-full object-cover rounded-md" />
                </div>
                
                <div v-if="form.type === 'Video' && getYoutubeEmbedUrl(form.video_url)" class="mb-4">
                  <p class="text-sm text-muted-foreground mb-2">Video:</p>
                  <div class="aspect-video bg-muted rounded-md">
                    <iframe 
                      :src="getYoutubeEmbedUrl(form.video_url)" 
                      class="w-full h-full rounded-md" 
                      frameborder="0" 
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                      allowfullscreen
                    ></iframe>
                  </div>
                </div>
                
                <div v-if="form.type === 'Infografis' && infographicPreview" class="mb-4">
                  <p class="text-sm text-muted-foreground mb-2">Infografis:</p>
                  <div v-if="infographicPreview.type === 'image'">
                    <img :src="infographicPreview.url" alt="Infografis Preview" class="max-w-full rounded-md" />
                  </div>
                  <div v-else class="p-4 bg-muted rounded-md">
                    <p class="text-sm">File: {{ infographicPreview.name }}</p>
                  </div>
                </div>
                
                <div v-if="form.title" class="mb-2">
                  <p class="text-sm text-muted-foreground mb-1">Judul:</p>
                  <h4 class="text-lg font-semibold">{{ form.title }}</h4>
                </div>
                
                <div v-if="form.description" class="mb-2">
                  <p class="text-sm text-muted-foreground mb-1">Deskripsi:</p>
                  <p>{{ form.description }}</p>
                </div>
              </div>
            </div>
  
            <div class="flex justify-end gap-4 pt-4">
              <Link :href="route('admin.education')">
                <Button type="button" variant="outline">
                Batal
              </Button>
              </Link>
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : (form.published ? 'Publikasikan Konten' : 'Simpan sebagai Draft') }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, reactive } from "vue";
  import { router, Link } from '@inertiajs/vue3';
  import { ChevronLeft, Video, Image as ImageIcon } from "lucide-vue-next";
  import Layout from "@/Layouts/AdminLayout.vue";
  
  import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
  } from "@/Components/ui/card";
  
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
  
  defineOptions({ layout: Layout });
  
  // Form for creating education content
  const form = reactive({
    title: "",
    description: "",
    type: "Artikel",
    image: null,
    content: "",
    video_url: "",
    infographic: null,
    published: true,
    errors: {},
    processing: false
  });
  
  const imagePreview = ref(null)
  const infographicPreview = ref(null)
  
  const isVideo = computed(() => form.type === 'Video');
  const isInfografis = computed(() => form.type === 'Infografis');
  
  const handleImageUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
      form.image = file
      
      // Create preview
      const reader = new FileReader()
      reader.onload = (e) => {
        imagePreview.value = e.target.result
      }
      reader.readAsDataURL(file)
    }
  }
  
  const removeImage = () => {
    form.image = null
    imagePreview.value = null
    // Reset file input
    const fileInput = document.getElementById('image')
    if (fileInput) {
      fileInput.value = ''
    }
  }
  
  const handleInfographicUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
      form.infographic = file
      
      // Create preview
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          infographicPreview.value = {
            type: 'image',
            url: e.target.result,
            name: file.name
          }
        }
        reader.readAsDataURL(file)
      } else {
        infographicPreview.value = {
          type: 'file',
          name: file.name
        }
      }
    }
  }
  
  const removeInfographic = () => {
    form.infographic = null
    infographicPreview.value = null
    // Reset file input
    const fileInput = document.getElementById('infographic')
    if (fileInput) {
      fileInput.value = ''
    }
  }
  
  // Methods
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
  
  const submitForm = () => {
    form.processing = true
    
    // Create FormData for file upload
    const formData = new FormData()
    
    // Add all form fields with proper type conversion
    Object.keys(form).forEach(key => {
      if (key !== 'errors' && key !== 'processing') {
        let value = form[key]
        
        // Skip null, undefined, or empty string values except for published
        if (key === 'published') {
          // Convert published to proper boolean
          formData.append(key, value ? '1' : '0')
        } else if (value !== null && value !== '' && value !== undefined) {
          formData.append(key, value)
        }
      }
    })
    
    // Debug form data
    console.log('Form data being sent:')
    for (let [key, value] of formData.entries()) {
      console.log(key, value)
    }
    
    router.post(route('admin.education.store'), formData, {
      forceFormData: true,
      onError: (errors) => {
        console.error('Validation errors:', errors)
        form.errors = errors
        form.processing = false
      },
      onSuccess: () => {
        console.log('Success!')
        form.processing = false
      },
      onFinish: () => {
        form.processing = false
      }
    })
  };
  </script>
  
  <style>
  /* Add any custom styles here */
  </style>