<template>
  <div class="py-12">
    <div class="container px-4 max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold mb-4">Laporan Publik</h1>
        <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
          Laporkan kondisi rumah pompa atau masalah terkait banjir di sekitar Anda. 
          Laporan Anda akan membantu petugas dalam monitoring dan pemeliharaan.
        </p>
      </div>

      <!-- Info Cards -->
      <div class="grid gap-6 md:grid-cols-3 mb-12">
        <Card class="p-6 text-center">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <Clock class="h-6 w-6 text-blue-600" />
          </div>
          <h3 class="font-semibold mb-2">Respon Cepat</h3>
          <p class="text-sm text-muted-foreground">
            Laporan akan ditanggapi dalam 24 jam
          </p>
        </Card>

        <Card class="p-6 text-center">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <Shield class="h-6 w-6 text-green-600" />
          </div>
          <h3 class="font-semibold mb-2">Data Aman</h3>
          <p class="text-sm text-muted-foreground">
            Informasi pribadi Anda terlindungi
          </p>
        </Card>

        <Card class="p-6 text-center">
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <Users class="h-6 w-6 text-orange-600" />
          </div>
          <h3 class="font-semibold mb-2">Untuk Masyarakat</h3>
          <p class="text-sm text-muted-foreground">
            Bantu menjaga keamanan lingkungan
          </p>
        </Card>
      </div>

      <!-- Report Form -->
      <Card class="p-8">
        <form @submit.prevent="submitReport" class="space-y-6">
          <!-- Pump House Selection -->
          <div class="space-y-2">
            <Label for="pump_house_id">Rumah Pompa <span class="text-red-500">*</span></Label>
            <Select v-model="form.pump_house_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Pilih rumah pompa terdekat" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="pumpHouse in pumpHouses" 
                  :key="pumpHouse.id" 
                  :value="pumpHouse.id.toString()"
                >
                  {{ pumpHouse.name }} - {{ pumpHouse.address }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p class="text-sm text-muted-foreground">
              Pilih rumah pompa yang paling dekat dengan lokasi masalah
            </p>
          </div>

          <!-- Reporter Information -->
          <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
              <Label for="reporter_name">Nama Lengkap <span class="text-red-500">*</span></Label>
              <Input 
                id="reporter_name"
                v-model="form.reporter_name" 
                placeholder="Masukkan nama lengkap Anda"
                required 
              />
            </div>

            <div class="space-y-2">
              <Label for="reporter_phone">Nomor Telepon <span class="text-red-500">*</span></Label>
              <Input 
                id="reporter_phone"
                v-model="form.reporter_phone" 
                placeholder="08xxxxxxxxxx"
                type="tel"
                required
              />
            </div>
          </div>

          <div class="space-y-2">
            <Label for="reporter_email">Email <span class="text-red-500">*</span></Label>
            <Input 
              id="reporter_email"
              v-model="form.reporter_email" 
              placeholder="email@example.com"
              type="email"
              required
            />
          </div>

          <!-- Report Details -->
          <div class="space-y-2">
            <Label for="title">Judul Laporan <span class="text-red-500">*</span></Label>
            <Input 
              id="title"
              v-model="form.title" 
              placeholder="Contoh: Pompa tidak berfungsi, Air meluap, dll"
              required 
            />
          </div>

          <div class="space-y-2">
            <Label for="images">Upload Gambar</Label>
            <Input 
              id="images"
              type="file"
              multiple
              accept="image/*"
              @change="handleImageUpload"
              class="file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
            />
            <p class="text-sm text-muted-foreground">
              Opsional - Upload foto kondisi (maksimal 5 gambar, masing-masing 2MB)
              <br />
              <span class="text-xs text-blue-600">💡 Tip: Pilih beberapa gambar sekaligus atau tambahkan satu per satu</span>
            </p>
            
            <!-- Image Preview -->
            <div v-if="imagePreview.length > 0" class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
              <div 
                v-for="(image, index) in imagePreview" 
                :key="index"
                class="relative group"
              >
                <img 
                  :src="image.url" 
                  :alt="`Preview ${index + 1}`"
                  class="w-full h-24 object-cover rounded-lg border"
                />
                <Button
                  type="button"
                  variant="destructive"
                  size="sm"
                  class="absolute top-1 right-1 h-6 w-6 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
                  @click="removeImage(index)"
                >
                  <X class="h-3 w-3" />
                </Button>
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="description">Deskripsi Masalah <span class="text-red-500">*</span></Label>
            <Textarea 
              id="description"
              v-model="form.description" 
              placeholder="Jelaskan kondisi yang Anda temukan secara detail..."
              rows="5"
              required 
            />
            <p class="text-sm text-muted-foreground">
              Semakin detail informasi yang Anda berikan, semakin mudah petugas menangani masalah
            </p>
          </div>

          <div class="space-y-2">
            <Label for="location_detail">Detail Lokasi</Label>
            <Textarea 
              id="location_detail"
              v-model="form.location_detail" 
              placeholder="Contoh: Dekat jembatan, di depan warung Pak Budi, sebelah masjid, dll"
              rows="3"
            />
            <p class="text-sm text-muted-foreground">
              Berikan patokan atau landmark untuk memudahkan petugas menemukan lokasi
            </p>
          </div>

          <!-- Submit Button -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <Button 
              type="submit" 
              size="lg" 
              :disabled="isSubmitting"
              class="flex-1"
            >
              <Send v-if="!isSubmitting" class="mr-2 h-5 w-5" />
              <Loader2 v-else class="mr-2 h-5 w-5 animate-spin" />
              {{ isSubmitting ? 'Mengirim Laporan...' : 'Kirim Laporan' }}
            </Button>
            
            <Button 
              type="button" 
              variant="outline" 
              size="lg"
              @click="resetForm"
              :disabled="isSubmitting"
            >
              <RotateCcw class="mr-2 h-5 w-5" />
              Reset Form
            </Button>
          </div>
        </form>
      </Card>

      <!-- Guidelines -->
      <Card class="mt-12 p-6 bg-muted/50">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <Info class="h-5 w-5 text-blue-600" />
          Panduan Pelaporan
        </h3>
        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <h4 class="font-medium mb-2">Yang Perlu Dilaporkan:</h4>
            <ul class="text-sm text-muted-foreground space-y-1">
              <li>• Pompa tidak berfungsi atau rusak</li>
              <li>• Air meluap dari saluran</li>
              <li>• Sampah menumpuk di area pompa</li>
              <li>• Kerusakan infrastruktur</li>
              <li>• Bau tidak sedap dari saluran</li>
            </ul>
          </div>
          <div>
            <h4 class="font-medium mb-2">Tips Pelaporan:</h4>
            <ul class="text-sm text-muted-foreground space-y-1">
              <li>• Berikan informasi sejelas mungkin</li>
              <li>• Sertakan patokan lokasi yang mudah ditemukan</li>
              <li>• Upload foto kondisi untuk memperjelas laporan</li>
              <li>• Cantumkan kontak untuk follow-up</li>
              <li>• Laporkan segera jika situasi darurat</li>
            </ul>
          </div>
        </div>
      </Card>

      <!-- Emergency Contact -->
      <Card class="mt-6 p-6 border-red-200 bg-red-50">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
            <Phone class="h-5 w-5 text-red-600" />
          </div>
          <div>
            <h3 class="font-semibold text-red-900 mb-2">Kontak Darurat</h3>
            <p class="text-sm text-red-700 mb-2">
              Untuk situasi darurat yang memerlukan penanganan segera:
            </p>
            <div class="flex flex-col sm:flex-row gap-2">
              <Button variant="destructive" size="sm" as="a" href="tel:112">
                <Phone class="mr-2 h-4 w-4" />
                112 (Darurat)
              </Button>
              <Button variant="outline" size="sm" as="a" href="tel:+6231-1234567">
                <Phone class="mr-2 h-4 w-4" />
                (031) 1234-567 (Dinas Terkait)
              </Button>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { useImageUtils } from '@/composables/useImageUtils'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select'
import { 
  Clock, 
  Shield, 
  Users, 
  Send, 
  RotateCcw, 
  Info, 
  Phone,
  Loader2,
  X
} from 'lucide-vue-next'

defineOptions({ layout: PublicLayout })

const props = defineProps({
  pumpHouses: Array,
})

// Use composables
const { validateImageFile } = useImageUtils()

const form = useForm({
  pump_house_id: '',
  reporter_name: '',
  reporter_phone: '',
  reporter_email: '',
  title: '',
  description: '',
  location_detail: '',
  images: []
})

const imagePreview = ref([])
const selectedImages = ref([])
const isSubmitting = ref(false)

const resetForm = () => {
  form.reset()
  imagePreview.value = []
  selectedImages.value = []
}

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  
  // Check total images including existing ones
  const totalImages = selectedImages.value.length + files.length
  if (totalImages > 5) {
    alert(`Maksimal 5 gambar yang dapat diupload. Anda sudah memiliki ${selectedImages.value.length} gambar, hanya bisa menambah ${5 - selectedImages.value.length} gambar lagi.`)
    return
  }
  
  files.forEach((file, index) => {
    // Use validation from composable
    const validation = validateImageFile(file, 2)
    if (!validation.valid) {
      alert(`Gambar ${file.name}: ${validation.error}`)
      return
    }
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value.push({
        url: e.target.result,
        file: file
      })
    }
    reader.readAsDataURL(file)
    
    selectedImages.value.push(file)
  })
  
  // Clear input to allow selecting same files again if needed
  event.target.value = ''
}

const removeImage = (index) => {
  imagePreview.value.splice(index, 1)
  selectedImages.value.splice(index, 1)
}

const submitReport = () => {
  isSubmitting.value = true
  
  // Validate form first
  if (!form.pump_house_id || !form.reporter_name || !form.title || !form.description) {
    alert('Mohon lengkapi semua field yang wajib diisi!')
    isSubmitting.value = false
    return
  }
  
  // Debug selected images
  console.log('Selected images before submit:', selectedImages.value)
  console.log('Number of selected images:', selectedImages.value.length)
  
  // Prepare form data for Inertia
  const formData = {
    pump_house_id: form.pump_house_id,
    reporter_name: form.reporter_name,
    reporter_phone: form.reporter_phone,
    reporter_email: form.reporter_email,
    title: form.title,
    description: form.description,
    location_detail: form.location_detail,
    images: selectedImages.value // Send the actual files
  }
  
  console.log('=== SENDING DATA ===')
  console.log('Form data:', formData)
  console.log('Images count:', formData.images.length)
  formData.images.forEach((image, index) => {
    console.log(`Image ${index}:`, image.name, image.size, image.type)
  })
  console.log('=== END DEBUG ===')
  
  // Use form.post with transform for file handling
  form.transform((data) => {
    const transformedData = { ...data }
    transformedData.images = selectedImages.value
    return transformedData
  }).post(route('public.submit-report'), {
    onSuccess: (page) => {
      console.log('✅ Report submitted successfully!')
      console.log('Response page:', page)
      resetForm()
    },
    onError: (errors) => {
      console.error('❌ Validation errors:', errors)
      
      // Show validation errors
      if (errors.images) {
        if (Array.isArray(errors.images)) {
          errors.images.forEach((error, index) => {
            if (error) alert(`Gambar ${index + 1}: ${error}`)
          })
        } else {
          alert('Error gambar: ' + errors.images)
        }
      }
      
      Object.keys(errors).forEach(key => {
        if (key !== 'images' && errors[key]) {
          alert(`${key}: ${errors[key]}`)
        }
      })
    },
    onFinish: () => {
      console.log('🏁 Request finished')
      isSubmitting.value = false
    }
  })
}
</script> 
 