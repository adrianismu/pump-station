<template>
  <AdminLayout>
    <div class="container mx-auto p-6">
      <div class="flex items-center gap-2 mb-6">
        <Button variant="outline" size="icon" @click="$router?.back?.()">
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <h1 class="text-2xl font-bold">Edit Profil</h1>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Informasi Profil</CardTitle>
          <CardDescription>
            Perbarui informasi profil Anda di sini.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Informasi Dasar -->
              <div class="space-y-4">
                <div>
                  <Label for="name">Nama Lengkap</Label>
                  <Input 
                    id="name" 
                    v-model="form.name" 
                    placeholder="Masukkan nama lengkap" 
                    :class="{ 'border-destructive': form.errors.name }"
                    required
                  />
                  <p v-if="form.errors.name" class="text-destructive text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                  <Label for="email">Email</Label>
                  <Input 
                    id="email" 
                    v-model="form.email" 
                    type="email"
                    placeholder="Masukkan email" 
                    :class="{ 'border-destructive': form.errors.email }"
                    required
                  />
                  <p v-if="form.errors.email" class="text-destructive text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                  <Label for="role">Role</Label>
                  <Input 
                    id="role" 
                    v-model="form.role" 
                    disabled
                    class="bg-muted"
                  />
                </div>
              </div>

              <!-- Foto Profil -->
    
            </div>

            <Separator />

            <!-- Ubah Password -->
            <div class="space-y-4">
              <h3 class="text-lg font-medium">Ubah Password</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="current_password">Password Saat Ini</Label>
                  <Input 
                    id="current_password" 
                    v-model="form.current_password" 
                    type="password"
                    placeholder="Masukkan password saat ini" 
                    :class="{ 'border-destructive': form.errors.current_password }"
                  />
                  <p v-if="form.errors.current_password" class="text-destructive text-sm mt-1">{{ form.errors.current_password }}</p>
                </div>

                <div>
                  <Label for="new_password">Password Baru</Label>
                  <Input 
                    id="new_password" 
                    v-model="form.new_password" 
                    type="password"
                    placeholder="Masukkan password baru" 
                    :class="{ 'border-destructive': form.errors.new_password }"
                  />
                  <p v-if="form.errors.new_password" class="text-destructive text-sm mt-1">{{ form.errors.new_password }}</p>
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-4">
              <Button 
                type="button" 
                variant="outline" 
                @click="$router?.back?.()"
              >
                Batal
              </Button>
              <Button 
                type="submit" 
                :disabled="form.processing"
              >
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Simpan Perubahan
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { 
  ChevronLeft,
  Loader2
} from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Separator } from '@/Components/ui/separator'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const previewImage = ref(null)
const fileInput = ref(null)

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  role: props.user.role,
  photo: null,
  current_password: '',
  new_password: ''
})

const handleImageUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.photo = file
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const submitForm = () => {
  form.put(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('current_password', 'new_password')
    }
  })
}
</script>
