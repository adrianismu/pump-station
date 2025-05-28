<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <div class="mx-auto h-24 w-24 text-red-500 mb-4">
          <ShieldX class="w-full h-full" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Akses Ditolak
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
          Anda tidak memiliki izin untuk mengakses halaman ini
        </p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="space-y-6">
          <div class="text-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Kemungkinan Penyebab:
            </h2>
            <ul class="text-left text-gray-600 dark:text-gray-400 space-y-2">
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                Anda tidak memiliki akses ke rumah pompa ini
              </li>
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                Level akses Anda tidak mencukupi untuk operasi ini
              </li>
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                Akses Anda telah berakhir atau dinonaktifkan
              </li>
              <li class="flex items-start">
                <span class="text-red-500 mr-2">•</span>
                Halaman ini khusus untuk administrator
              </li>
            </ul>
          </div>

          <div class="space-y-4">
            <Button 
              @click="goBack" 
              class="w-full"
              variant="default"
            >
              <ArrowLeft class="w-4 h-4 mr-2" />
              Kembali ke Halaman Sebelumnya
            </Button>
            
            <Button 
              @click="goToDashboard" 
              class="w-full"
              variant="outline"
            >
              <Home class="w-4 h-4 mr-2" />
              Ke Dashboard
            </Button>
          </div>

          <div class="text-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Butuh bantuan? Hubungi administrator sistem
            </p>
            <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
              admin@rumahpompa.id
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { ShieldX, ArrowLeft, Home } from 'lucide-vue-next'

const goBack = () => {
  window.history.back()
}

const goToDashboard = () => {
  router.visit(route('admin.dashboard'))
}
</script> 
 