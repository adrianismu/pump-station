<template>
  <Head title="Tambah Rumah Pompa" />
  
  <AdminLayout>
    <div class="container mx-auto p-6">
    <div class="flex items-center gap-2 mb-6">
      <Button variant="outline" size="icon" @click="$router?.back?.()">
        <ChevronLeft class="h-4 w-4" />
      </Button>
      <h1 class="text-2xl font-bold">Tambah Rumah Pompa Baru</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Informasi Rumah Pompa</CardTitle>
        <CardDescription>
          Masukkan informasi detail untuk rumah pompa baru.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submitForm" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informasi Dasar -->
            <div class="space-y-4">
              <div>
                <Label for="name">Nama Rumah Pompa</Label>
                <Input 
                  id="name" 
                  v-model="form.name" 
                  placeholder="Masukkan nama rumah pompa" 
                  :class="{ 'border-destructive': form.errors.name }"
                  required
                />
                <p v-if="form.errors.name" class="text-destructive text-sm mt-1">{{ form.errors.name }}</p>
              </div>

              <div>
                <Label for="address">Alamat</Label>
                <Textarea 
                  id="address" 
                  v-model="form.address" 
                  placeholder="Masukkan alamat lengkap" 
                  :class="{ 'border-destructive': form.errors.address }"
                  required
                />
                <p v-if="form.errors.address" class="text-destructive text-sm mt-1">{{ form.errors.address }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label for="lat">Latitude</Label>
                  <Input 
                    id="lat" 
                    v-model="form.lat" 
                    type="number" 
                    step="0.000000000001" 
                    placeholder="-7.257521212121234567" 
                    :class="{ 'border-destructive': form.errors.lat }"
                    required
                  />
                  <p v-if="form.errors.lat" class="text-destructive text-sm mt-1">{{ form.errors.lat }}</p>
                </div>
                <div>
                  <Label for="lng">Longitude</Label>
                  <Input 
                    id="lng" 
                    v-model="form.lng" 
                    type="number" 
                    step="0.000000000001" 
                    placeholder="112.752123456789012345" 
                    :class="{ 'border-destructive': form.errors.lng }"
                    required
                  />
                  <p v-if="form.errors.lng" class="text-destructive text-sm mt-1">{{ form.errors.lng }}</p>
                </div>
              </div>

              <div>
                <Label for="status">Status</Label>
                <Select 
                  v-model="form.status" 
                  :class="{ 'border-destructive': form.errors.status }"
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Aktif">Aktif</SelectItem>
                    <SelectItem value="Perlu Perhatian">Perlu Perhatian</SelectItem>
                    <SelectItem value="Tidak Aktif">Tidak Aktif</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-destructive text-sm mt-1">{{ form.errors.status }}</p>
              </div>

              <div>
                  <Label for="image">Upload Gambar Rumah Pompa</Label>
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
                      Upload foto rumah pompa (format: JPEG, PNG, JPG, GIF - maksimal 2MB)
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
            </div>

            <!-- Informasi Teknis -->
            <div class="space-y-4">
              <div>
                <Label for="capacity">Kapasitas</Label>
                <Input 
                  id="capacity" 
                  v-model="form.capacity" 
                  placeholder="3000 m³/jam" 
                  :class="{ 'border-destructive': form.errors.capacity }"
                  required
                />
                <p v-if="form.errors.capacity" class="text-destructive text-sm mt-1">{{ form.errors.capacity }}</p>
              </div>

              <div>
                <Label for="pump_count">Jumlah Pompa</Label>
                <Input 
                  id="pump_count" 
                  v-model="form.pump_count" 
                  type="number" 
                  min="1" 
                  placeholder="5" 
                  :class="{ 'border-destructive': form.errors.pump_count }"
                  required
                />
                <p v-if="form.errors.pump_count" class="text-destructive text-sm mt-1">{{ form.errors.pump_count }}</p>
              </div>

              <div>
                <Label for="active_pumps">Pompa Aktif</Label>
                <Input 
                  id="active_pumps" 
                  v-model="form.active_pumps" 
                  type="number" 
                  min="0" 
                  :max="form.pump_count || 1"
                  placeholder="0" 
                  :class="{ 'border-destructive': form.errors.active_pumps }"
                />
                <p v-if="form.errors.active_pumps" class="text-destructive text-sm mt-1">{{ form.errors.active_pumps }}</p>
                <p class="text-xs text-muted-foreground mt-1">Maksimal {{ form.pump_count || 1 }} pompa (sesuai jumlah pompa di atas)</p>
              </div>

              <div>
                <Label for="built_year">Tahun Dibangun</Label>
                <Input 
                  id="built_year" 
                  v-model="form.built_year" 
                  type="number" 
                  min="1900" 
                  max="2100" 
                  placeholder="2020" 
                  :class="{ 'border-destructive': form.errors.built_year }"
                  required
                />
                <p v-if="form.errors.built_year" class="text-destructive text-sm mt-1">{{ form.errors.built_year }}</p>
              </div>
            </div>
          </div>

          <!-- Informasi Kontak -->
          <div>
            <h3 class="text-lg font-medium mb-4">Informasi Kontak</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="manager_name">Nama Manajer</Label>
                <Input 
                  id="manager_name" 
                  v-model="form.manager_name" 
                  placeholder="Nama manajer" 
                  :class="{ 'border-destructive': form.errors.manager_name }"
                  required
                />
                <p v-if="form.errors.manager_name" class="text-destructive text-sm mt-1">{{ form.errors.manager_name }}</p>
              </div>

              <div>
                <Label for="contact_phone">Nomor Telepon</Label>
                <Input 
                  id="contact_phone" 
                  v-model="form.contact_phone" 
                  placeholder="08123456789" 
                  :class="{ 'border-destructive': form.errors.contact_phone }"
                  required
                />
                <p v-if="form.errors.contact_phone" class="text-destructive text-sm mt-1">{{ form.errors.contact_phone }}</p>
              </div>

              <div>
                <Label for="contact_email">Email</Label>
                <Input 
                  id="contact_email" 
                  v-model="form.contact_email" 
                  type="email" 
                  placeholder="email@example.com" 
                  :class="{ 'border-destructive': form.errors.contact_email }"
                  required
                />
                <p v-if="form.errors.contact_email" class="text-destructive text-sm mt-1">{{ form.errors.contact_email }}</p>
              </div>

              <div>
                <Label for="staff_count">Jumlah Staf</Label>
                <Input 
                  id="staff_count" 
                  v-model="form.staff_count" 
                  type="number" 
                  min="1" 
                  placeholder="10" 
                  :class="{ 'border-destructive': form.errors.staff_count }"
                  required
                />
                <p v-if="form.errors.staff_count" class="text-destructive text-sm mt-1">{{ form.errors.staff_count }}</p>
              </div>
            </div>
          </div>

          <!-- Preview Lokasi Map -->
          <div>
            <h3 class="text-lg font-medium mb-4">Lokasi pada Peta</h3>
            <div class="h-64 rounded-md overflow-hidden mb-2 border border-border">
              <div id="create-map" class="h-full w-full bg-muted"></div>
            </div>
            <p class="text-sm text-muted-foreground">Klik pada peta untuk mengubah lokasi atau masukkan koordinat secara manual.</p>
          </div>

          <div class="flex justify-end gap-4 pt-4">
            <Button type="button" variant="outline" @click="$router?.back?.()">
              Batal
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Rumah Pompa' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import { ChevronLeft, Image as ImageIcon } from "lucide-vue-next"
import L from "leaflet"
import "leaflet/dist/leaflet.css"
import Layout from "@/Layouts/AdminLayout.vue"
import { router } from '@inertiajs/vue3';

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/Components/ui/card"

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/Components/ui/select"

import { Button } from "@/Components/ui/button"
import { Input } from "@/Components/ui/input"
import { Label } from "@/Components/ui/label"
import { Textarea } from "@/Components/ui/textarea"

defineOptions({ layout: Layout })

// Form for creating pump houses
const form = useForm({
  name: "",
  address: "",
  lat: -7.2575, // Default to Surabaya
  lng: 112.7521,
  status: "Aktif",
  capacity: "3000 m³/jam",
  pump_count: 5,
  active_pumps: 0,
  image: null,
  built_year: new Date().getFullYear(),
  manager_name: "",
  contact_phone: "",
  contact_email: "",
  staff_count: 10,
})

let map = null
let marker = null

const imagePreview = ref(null)

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

const initMap = () => {
  if (document.getElementById("create-map")) {
    map = L.map("create-map").setView([form.lat, form.lng], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    marker = L.marker([form.lat, form.lng], { draggable: true })
      .addTo(map)
      .bindPopup("Lokasi Rumah Pompa")
      .openPopup();

    marker.on("dragend", function (event) {
      const position = marker.getLatLng();
      form.lat = position.lat;
      form.lng = position.lng;
    });

    map.on("click", function (e) {
      marker.setLatLng(e.latlng);
      form.lat = e.latlng.lat;
      form.lng = e.latlng.lng;
    });
  }
};

onMounted(() => {
  initMap();
});

// Watch for changes in lat/lng inputs and update marker
watch(
  () => [form.lat, form.lng],
  ([newLat, newLng]) => {
    if (map && marker && newLat && newLng) {
      marker.setLatLng([newLat, newLng]);
      map.panTo([newLat, newLng]);
    }
  }
);

const submitForm = () => {
  // Create FormData for file upload
  const formData = new FormData()
  
  // Add all form fields
  Object.keys(form.data()).forEach(key => {
    if (form[key] !== null && form[key] !== '') {
      formData.append(key, form[key])
    }
  })
  
  router.post(route('admin.database.store'), formData, {
    forceFormData: true,
    onError: (errors) => {
      form.errors = errors
    },
    onSuccess: () => {
      // Redirect to database page on success
      router.visit(route("admin.database"))
    }
  })
}
</script>

<style>
/* Ensure the map container has a background */
#create-map {
  background-color: #f0f0f0;
}
</style> 