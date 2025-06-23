<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Konten Edukasi</h1>
    
    <Card class="mb-6">
      <CardContent class="pt-6">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
          <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input 
                type="text" 
                placeholder="Cari konten edukasi..." 
                v-model="searchQuery"
                class="pl-8 w-full sm:w-[250px]"
              />
            </div>
            <Select v-model="typeFilter">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Pilih tipe" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">Semua Tipe</SelectItem>
                <SelectItem value="article">Artikel</SelectItem>
                <SelectItem value="video">Video</SelectItem>
                <SelectItem value="infographic">Infografis</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div class="flex gap-2">
            <Link :href="route('admin.education.create')" class="inline-flex">
              <Button>
                <Plus class="mr-2 h-4 w-4" />
                Tambah Konten
              </Button>
            </Link>
          </div>
        </div>
      </CardContent>
    </Card>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="content in paginatedContent" :key="content.id" class="overflow-hidden">
        <div class="relative h-48">
          <img :src="content.image" alt="Content Thumbnail" class="h-full w-full object-cover" />
          <div class="absolute top-2 right-2">
            <Badge :variant="getTypeVariant(content.type)">
              {{ content.type }}
            </Badge>
          </div>
        </div>
        
        <CardContent class="p-4">
          <div class="flex items-center gap-2 mb-2">
            <Calendar class="h-4 w-4 text-muted-foreground" />
            <span class="text-xs text-muted-foreground">{{ formatDate(content.date) }}</span>
          </div>
          
          <Link :href="route('admin.education.show', content.id)">
            <h3 class="text-lg font-semibold mb-2 hover:text-primary transition-colors">{{ content.title }}</h3>
          </Link>
          <p class="text-sm text-muted-foreground mb-4 line-clamp-3">{{ content.description }}</p>
          
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
              <Badge :variant="content.published ? 'default' : 'secondary'">
                {{ content.published ? 'Dipublikasikan' : 'Draft' }}
              </Badge>
            </div>
            
            <div class="flex gap-2">
              <Link :href="route('admin.education.show', content.id)">
                <Button variant="ghost" size="icon">
                  <Eye class="h-4 w-4" />
                </Button>
              </Link>
              <Link :href="route('admin.education.edit', content.id)">
                <Button variant="ghost" size="icon">
                  <Edit class="h-4 w-4" />
                </Button>
              </Link>
              <Button variant="ghost" size="icon" @click="confirmDelete(content)" class="text-destructive">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card v-if="paginatedContent.length === 0" class="col-span-full p-8 text-center">
        <CardContent>
          <FileQuestion class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
          <h3 class="text-lg font-semibold mb-1">Tidak ada konten ditemukan</h3>
          <p class="text-sm text-muted-foreground">Coba ubah filter pencarian Anda</p>
        </CardContent>
      </Card>
    </div>
    
    <div class="flex items-center justify-between px-4 py-6">
      <div class="text-sm text-muted-foreground">
        Menampilkan {{ paginatedContent.length }} dari {{ filteredContent.length }} konten
      </div>
      <Pagination
        :items-per-page="itemsPerPage"
        :total="filteredContent.length"
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
    
    <!-- Delete Confirmation Dialog -->
    <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Konfirmasi Hapus</AlertDialogTitle>
          <AlertDialogDescription>
            Apakah Anda yakin ingin menghapus konten "{{ contentToDelete?.title }}"? Tindakan ini tidak dapat dibatalkan.
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
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  BookOpen,
  Users,
  FileText,
  Search,
  Plus,
  Calendar,
  Eye,
  Edit,
  Trash2,
  FileQuestion,
  TrendingUp,
} from "lucide-vue-next";
import Layout from "@/Layouts/AdminLayout.vue";

import {
  Card,
  CardContent,
} from "@/Components/ui/card";

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

import {
  Pagination,
  PaginationEllipsis,
  PaginationFirst,
  PaginationLast,
  PaginationList,
  PaginationListItem,
  PaginationNext,
  PaginationPrev,
} from '@/Components/ui/pagination';

import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Badge } from "@/Components/ui/badge";

defineOptions({ layout: Layout });

const props = defineProps({
  educationContents: {
    type: Array,
    default: () => [], // Provide a default empty array
  },
});

const searchQuery = ref("");
const typeFilter = ref("all");
const currentPage = ref(1);
const itemsPerPage = ref(9);

// Delete confirmation
const showDeleteDialog = ref(false);
const contentToDelete = ref(null);
const isDeleting = ref(false);

// Computed property for filtered content based on search query and type filter
const filteredContent = computed(() => {
  if (!props.educationContents) return []; // Return empty if no contents

  let result = [...props.educationContents];

  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(
      (content) => content.title.toLowerCase().includes(query) || content.description.toLowerCase().includes(query),
    );
  }

  // Apply type filter
  if (typeFilter.value !== "all") {
    const typeMap = {
      article: "Artikel",
      video: "Video",
      infographic: "Infografis",
    };
    result = result.filter((content) => content.type === typeMap[typeFilter.value]);
  }

  return result;
});

// Apply pagination to filtered content
const paginatedContent = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage.value;
  const endIndex = startIndex + itemsPerPage.value;
  return filteredContent.value.slice(startIndex, endIndex);
});

// Get badge variant based on content type
const getTypeVariant = (type) => {
  if (type === 'Artikel') return 'default';
  if (type === 'Video') return 'destructive';
  if (type === 'Infografis') return 'secondary';
  return 'default';
};

// Format date utility function
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
};

// Delete content with confirmation dialog
const confirmDelete = (content) => {
  contentToDelete.value = content;
  showDeleteDialog.value = true;
};

// Confirm deletion after dialog
const deleteContent = () => {
  isDeleting.value = true;

  router.delete(route("admin.education.destroy", contentToDelete.value.id), {
    onSuccess: () => {
      showDeleteDialog.value = false;
      contentToDelete.value = null;
    },
    onFinish: () => {
      isDeleting.value = false;
    },
  });
};
</script> 