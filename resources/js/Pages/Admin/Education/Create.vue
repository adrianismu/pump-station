<template>
    <div>
      <div class="flex items-center gap-2 mb-6">
        <Button variant="outline" size="icon" @click="$router?.back?.()">
          <ChevronLeft class="h-4 w-4" />
        </Button>
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
                  <Label for="image">URL Gambar Thumbnail</Label>
                  <Input 
                    id="image" 
                    v-model="form.image" 
                    type="url" 
                    placeholder="https://example.com/image.jpg" 
                    :class="{ 'border-destructive': form.errors.image }"
                    required
                  />
                  <p v-if="form.errors.image" class="text-destructive text-sm mt-1">{{ form.errors.image }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Masukkan URL gambar untuk thumbnail konten</p>
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
                  <Label for="infographic_url">URL Infografis</Label>
                  <Input 
                    id="infographic_url" 
                    v-model="form.infographic_url" 
                    type="url" 
                    placeholder="https://example.com/infographic.jpg" 
                    :class="{ 'border-destructive': form.errors.infographic_url }"
                  />
                  <p v-if="form.errors.infographic_url" class="text-destructive text-sm mt-1">{{ form.errors.infographic_url }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Masukkan URL gambar infografis (opsional)</p>
                </div>
              </div>
  
              <!-- Content and Tags -->
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
  
                <div>
                  <Label for="tags">Tag (pisahkan dengan koma)</Label>
                  <Input 
                    id="tags" 
                    v-model="form.tags" 
                    placeholder="banjir, pompa air, edukasi" 
                    :class="{ 'border-destructive': form.errors.tags }"
                  />
                  <p v-if="form.errors.tags" class="text-destructive text-sm mt-1">{{ form.errors.tags }}</p>
                  <p class="text-xs text-muted-foreground mt-1">Tambahkan tag untuk memudahkan pencarian</p>
                </div>
  
                <!-- <div>
                  <Label for="published">Status Publikasi</Label>
                  <Select 
                    v-model="form.published" 
                    :class="{ 'border-destructive': form.errors.published }"
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Pilih status publikasi" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem :value="true">Publikasikan Sekarang</SelectItem>
                      <SelectItem :value="false">Simpan sebagai Draft</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.published" class="text-destructive text-sm mt-1">{{ form.errors.published }}</p>
                </div> -->
              </div>
            </div>
  
            <!-- Preview -->
            <div>
              <h3 class="text-lg font-medium mb-4">Preview</h3>
              <div class="border border-border rounded-md p-4">
                <div v-if="form.image" class="mb-4">
                  <p class="text-sm text-muted-foreground mb-2">Thumbnail:</p>
                  <img :src="form.image" alt="Preview" class="h-48 w-full object-cover rounded-md" />
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
                
                <div v-if="form.type === 'Infografis' && form.infographic_url" class="mb-4">
                  <p class="text-sm text-muted-foreground mb-2">Infografis:</p>
                  <img :src="form.infographic_url" alt="Infografis Preview" class="max-w-full rounded-md" />
                </div>
                
                <div v-if="form.title" class="mb-2">
                  <p class="text-sm text-muted-foreground mb-1">Judul:</p>
                  <h4 class="text-lg font-semibold">{{ form.title }}</h4>
                </div>
                
                <div v-if="form.description" class="mb-2">
                  <p class="text-sm text-muted-foreground mb-1">Deskripsi:</p>
                  <p>{{ form.description }}</p>
                </div>
                
                <div v-if="form.tags" class="mb-2">
                  <p class="text-sm text-muted-foreground mb-1">Tag:</p>
                  <div class="flex flex-wrap gap-2">
                    <Badge variant="outline" v-for="(tag, index) in form.tags.split(',').map(t => t.trim()).filter(t => t)" :key="index">
                      {{ tag }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
  
            <div class="flex justify-end gap-4 pt-4">
              <Button type="button" variant="outline" @click="$router?.back?.()">
                Batal
              </Button>
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
  import { ref, computed } from "vue";
  import { useForm } from "@inertiajs/vue3";
  import { router } from '@inertiajs/vue3';
  import { ChevronLeft, Video } from "lucide-vue-next";
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
  import { Badge } from "@/Components/ui/badge";
  
  defineOptions({ layout: Layout });
  
  // Form for creating education content
  const form = useForm({
    title: "",
    description: "",
    type: "Artikel",
    image: "",
    content: "",
    video_url: "",
    infographic_url: "",
    tags: "",
    published: true,
  });
  
  const isVideo = computed(() => form.type === 'Video');
  const isInfografis = computed(() => form.type === 'Infografis');
  
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
    // Create a new form data object to avoid modifying the original form
    const formData = { ...form };
    
    // Convert comma-separated tags string to an array
    // This is the key fix - we're setting the tags property directly on the form object
    form.tags = form.tags.split(',')
      .map(tag => tag.trim())
      .filter(tag => tag);
    
    form.post(route('admin.education.store'), {
      onSuccess: () => {
        router.visit(route('admin.education'));
      },
    });
  };
  </script>
  
  <style>
  /* Add any custom styles here */
  </style>