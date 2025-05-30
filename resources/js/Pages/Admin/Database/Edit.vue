<template>
  <div>
    <div class="flex items-center gap-2 mb-6">
      <Button variant="outline" size="icon" @click="$router?.back?.()">
        <ChevronLeft class="h-4 w-4" />
      </Button>
      <h1 class="text-2xl font-bold">Edit Rumah Pompa</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Edit Informasi Rumah Pompa</CardTitle>
        <CardDescription>
          Perbarui informasi untuk rumah pompa "{{ pumpHouse.name }}".
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
                <Label for="image">URL Gambar</Label>
                <Input 
                  id="image" 
                  v-model="form.image" 
                  type="url" 
                  placeholder="https://example.com/image.jpg" 
                  :class="{ 'border-destructive': form.errors.image }"
                  required
                />
                <p v-if="form.errors.image" class="text-destructive text-sm mt-1">{{ form.errors.image }}</p>
                <p class="text-xs text-muted-foreground mt-1">Masukkan URL gambar untuk rumah pompa</p>
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
              <div id="edit-map" class="h-full w-full bg-muted"></div>
            </div>
            <p class="text-sm text-muted-foreground">Klik pada peta untuk mengubah lokasi atau masukkan koordinat secara manual.</p>
          </div>

          <div class="flex justify-end gap-4 pt-4">
            <Button type="button" variant="outline" @click="$router?.back?.()">
              Batal
            </Button>
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useForm } from "@inertiajs/vue3"
import { ChevronLeft } from "lucide-vue-next"
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

// Get pump house from props (passed from the controller)
const props = defineProps({
  pumpHouse: Object,
})

// Form for editing pump houses
const form = useForm({
  name: props.pumpHouse.name,
  address: props.pumpHouse.address,
  lat: props.pumpHouse.lat,
  lng: props.pumpHouse.lng,
  status: props.pumpHouse.status,
  capacity: props.pumpHouse.capacity,
  pump_count: props.pumpHouse.pump_count,
  image: props.pumpHouse.image,
  built_year: props.pumpHouse.built_year,
  manager_name: props.pumpHouse.manager_name,
  contact_phone: props.pumpHouse.contact_phone,
  contact_email: props.pumpHouse.contact_email,
  staff_count: props.pumpHouse.staff_count,
})

let map = null
let marker = null

onMounted(() => {
  // Initialize map
  if (typeof L !== 'undefined') {
    map = L.map("edit-map").setView([form.lat, form.lng], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    // Add marker for the pump house
    marker = L.marker([form.lat, form.lng], { draggable: true })
      .addTo(map)
      .bindPopup(`<b>${form.name}</b><br>${form.status}`)
      .openPopup();

    // Update lat/lng when marker is dragged
    marker.on('dragend', function(event) {
      const position = marker.getLatLng();
      form.lat = position.lat;
      form.lng = position.lng;
    });

    // Update marker when clicking on map
    map.on('click', function(e) {
      marker.setLatLng(e.latlng);
      form.lat = e.latlng.lat;
      form.lng = e.latlng.lng;
    });
  }
})

// Watch for changes in lat/lng inputs and update marker
watch(() => [form.lat, form.lng], ([newLat, newLng]) => {
  if (map && marker && newLat && newLng && typeof L !== 'undefined') {
    marker.setLatLng([newLat, newLng])
    map.panTo([newLat, newLng])
  }
})

const submitForm = () => {
  form.put(route("admin.database.update", props.pumpHouse.id), {
    onSuccess: () => {
      // Redirect to database page on success
      window.location.href = route("admin.database")
    },
  })
}
</script>

<style>
/* Ensure the map container has a background */
#edit-map {
  background-color: #f0f0f0;
}
</style>