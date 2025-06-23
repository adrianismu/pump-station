<template>
  <div class="py-20">
    <div class="container px-4 max-w-2xl mx-auto text-center">
      <!-- Success Icon -->
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8">
        <CheckCircle class="h-12 w-12 text-green-600" />
      </div>

      <!-- Success Message -->
      <h1 class="text-4xl font-bold mb-4">Laporan Berhasil Dikirim!</h1>
      <p class="text-xl text-muted-foreground mb-8">
        Terima kasih atas laporan Anda. Tim kami akan segera menindaklanjuti dan memberikan respon dalam 24 jam.
      </p>

      <!-- Info Cards -->
      <div class="grid gap-6 md:grid-cols-2 mb-12">
        <Card class="p-6">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <Clock class="h-6 w-6 text-blue-600" />
          </div>
          <h3 class="font-semibold mb-2">Waktu Respon</h3>
          <p class="text-sm text-muted-foreground">
            Laporan akan ditanggapi maksimal dalam 24 jam kerja
          </p>
        </Card>

        <Card class="p-6">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
            <Bell class="h-6 w-6 text-green-600" />
          </div>
          <h3 class="font-semibold mb-2">Notifikasi</h3>
          <p class="text-sm text-muted-foreground">
            Anda akan dihubungi jika diperlukan informasi tambahan
          </p>
        </Card>
      </div>

      <!-- Next Steps -->
      <Card class="p-8 mb-8 text-left">
        <h3 class="font-semibold mb-4 text-center">Langkah Selanjutnya</h3>
        <div class="space-y-4">
          <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-sm font-semibold text-primary">1</span>
            </div>
            <div>
              <h4 class="font-medium mb-1">Verifikasi Laporan</h4>
              <p class="text-sm text-muted-foreground">
                Tim akan memverifikasi dan mengkategorikan laporan Anda
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-sm font-semibold text-primary">2</span>
            </div>
            <div>
              <h4 class="font-medium mb-1">Penugasan Petugas</h4>
              <p class="text-sm text-muted-foreground">
                Petugas lapangan akan ditugaskan untuk menangani masalah
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
              <span class="text-sm font-semibold text-primary">3</span>
            </div>
            <div>
              <h4 class="font-medium mb-1">Tindak Lanjut</h4>
              <p class="text-sm text-muted-foreground">
                Anda akan dihubungi untuk update status penanganan
              </p>
            </div>
          </div>
        </div>
      </Card>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <Button size="lg" as="a" :href="route('public.landing')" class="text-lg px-8">
          <Home class="mr-2 h-5 w-5" />
          Kembali ke Beranda
        </Button>
        
        <Button size="lg" variant="outline" as="a" :href="route('public.reports')" class="text-lg px-8">
          <FileText class="mr-2 h-5 w-5" />
          Buat Laporan Lain
        </Button>
      </div>

      <!-- Additional Info -->
      <div class="mt-12 p-6 bg-muted/50 rounded-lg">
        <h3 class="font-semibold mb-4">Informasi Tambahan</h3>
        <div class="grid gap-4 md:grid-cols-2 text-sm">
          <div>
            <h4 class="font-medium mb-2">Untuk Situasi Darurat:</h4>
            <p class="text-muted-foreground mb-2">
              Jika situasi memerlukan penanganan segera, hubungi:
            </p>
            <div class="flex flex-col gap-1">
              <Button variant="destructive" size="sm" as="a" href="tel:112" class="w-fit">
                <Phone class="mr-2 h-4 w-4" />
                112 (Darurat)
              </Button>
            </div>
          </div>
          
          <div>
            <h4 class="font-medium mb-2">Pantau Status:</h4>
            <p class="text-muted-foreground mb-2">
              Lihat perkembangan kondisi rumah pompa di:
            </p>
            <Button variant="outline" size="sm" as="a" :href="route('public.map')" class="w-fit">
              <Map class="mr-2 h-4 w-4" />
              Peta Monitoring
            </Button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui/button'
import { Card } from '@/Components/ui/card'
import { 
  CheckCircle, 
  Clock, 
  Bell, 
  Home, 
  FileText, 
  Phone, 
  Map 
} from 'lucide-vue-next'

defineOptions({ layout: PublicLayout })
</script> 
 