<template>
  <div class="min-h-screen bg-background">
    <!-- Header -->
    <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div class="max-w-7xl mx-auto flex h-16 items-center justify-between px-4">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
          <Link :href="route('public.landing')" class="flex items-center space-x-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
              <Droplet class="h-5 w-5 text-primary-foreground" />
            </div>
            <span class="text-xl font-bold">Rumah Pompa</span>
          </Link>
        </div>

        <!-- Navigation -->
        <nav class="hidden md:flex items-center space-x-6">
          <Link 
            :href="route('public.landing')" 
            :class="[
              'text-sm font-medium transition-colors hover:text-primary',
              route().current('public.landing') ? 'text-primary' : 'text-muted-foreground'
            ]"
          >
            Beranda
          </Link>
          <Link 
            :href="route('public.reports')" 
            :class="[
              'text-sm font-medium transition-colors hover:text-primary',
              route().current('public.reports') ? 'text-primary' : 'text-muted-foreground'
            ]"
          >
            Laporan Publik
          </Link>
          <Link 
            :href="route('public.education')" 
            :class="[
              'text-sm font-medium transition-colors hover:text-primary',
              route().current('public.education*') ? 'text-primary' : 'text-muted-foreground'
            ]"
          >
            Edukasi
          </Link>
          <Link 
            :href="route('public.map')" 
            :class="[
              'text-sm font-medium transition-colors hover:text-primary',
              route().current('public.map') ? 'text-primary' : 'text-muted-foreground'
            ]"
          >
            Peta
          </Link>
        </nav>

        <!-- Login Button -->
        <div class="flex items-center space-x-4">
          <Button as="a" :href="route('login')" variant="default" size="sm">
            <LogIn class="mr-2 h-4 w-4" />
            Login
          </Button>
        </div>

        <!-- Mobile Menu Button -->
        <Button
          variant="ghost"
          size="sm"
          class="md:hidden"
          @click="mobileMenuOpen = !mobileMenuOpen"
        >
          <Menu class="h-5 w-5" />
        </Button>
      </div>

      <!-- Mobile Navigation -->
      <div v-if="mobileMenuOpen" class="md:hidden border-t bg-background">
        <nav class="max-w-7xl mx-auto px-4 py-4 space-y-2">
          <Link 
            :href="route('public.landing')" 
            :class="[
              'block px-3 py-2 text-sm font-medium rounded-md transition-colors',
              route().current('public.landing') 
                ? 'bg-primary text-primary-foreground' 
                : 'text-muted-foreground hover:text-primary hover:bg-muted'
            ]"
            @click="mobileMenuOpen = false"
          >
            Beranda
          </Link>
          <Link 
            :href="route('public.reports')" 
            :class="[
              'block px-3 py-2 text-sm font-medium rounded-md transition-colors',
              route().current('public.reports') 
                ? 'bg-primary text-primary-foreground' 
                : 'text-muted-foreground hover:text-primary hover:bg-muted'
            ]"
            @click="mobileMenuOpen = false"
          >
            Laporan Publik
          </Link>
          <Link 
            :href="route('public.education')" 
            :class="[
              'block px-3 py-2 text-sm font-medium rounded-md transition-colors',
              route().current('public.education*') 
                ? 'bg-primary text-primary-foreground' 
                : 'text-muted-foreground hover:text-primary hover:bg-muted'
            ]"
            @click="mobileMenuOpen = false"
          >
            Edukasi
          </Link>
          <Link 
            :href="route('public.map')" 
            :class="[
              'block px-3 py-2 text-sm font-medium rounded-md transition-colors',
              route().current('public.map') 
                ? 'bg-primary text-primary-foreground' 
                : 'text-muted-foreground hover:text-primary hover:bg-muted'
            ]"
            @click="mobileMenuOpen = false"
          >
            Peta
          </Link>
          <div class="pt-2 border-t">
            <Button as="a" :href="route('login')" variant="default" size="sm" class="w-full">
              <LogIn class="mr-2 h-4 w-4" />
              Login Admin
            </Button>
          </div>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/50">
      <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid gap-8 md:grid-cols-4">
          <!-- Logo & Description -->
          <div class="md:col-span-2">
            <div class="flex items-center space-x-2 mb-4">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
                <Droplet class="h-5 w-5 text-primary-foreground" />
              </div>
              <span class="text-xl font-bold">Sistem Rumah Pompa</span>
            </div>
            <p class="text-sm text-muted-foreground max-w-md">
              Platform monitoring dan pelaporan rumah pompa untuk pencegahan banjir. 
              Masyarakat dapat melaporkan kondisi, mengakses edukasi, dan melihat informasi real-time.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="font-semibold mb-4">Menu Utama</h3>
            <ul class="space-y-2 text-sm">
              <li>
                <Link :href="route('public.landing')" class="text-muted-foreground hover:text-primary transition-colors">
                  Beranda
                </Link>
              </li>
              <li>
                <Link :href="route('public.reports')" class="text-muted-foreground hover:text-primary transition-colors">
                  Laporan Publik
                </Link>
              </li>
              <li>
                <Link :href="route('public.education')" class="text-muted-foreground hover:text-primary transition-colors">
                  Edukasi
                </Link>
              </li>
              <li>
                <Link :href="route('public.map')" class="text-muted-foreground hover:text-primary transition-colors">
                  Peta Rumah Pompa
                </Link>
              </li>
            </ul>
          </div>

          <!-- Contact Info -->
          <div>
            <h3 class="font-semibold mb-4">Kontak Darurat</h3>
            <ul class="space-y-2 text-sm text-muted-foreground">
              <li class="flex items-center space-x-2">
                <Phone class="h-4 w-4" />
                <span>112 (Darurat)</span>
              </li>
              <li class="flex items-center space-x-2">
                <Mail class="h-4 w-4" />
                <span>info@rumahpompa.id</span>
              </li>
              <li class="flex items-center space-x-2">
                <MapPin class="h-4 w-4" />
                <span>Surabaya, Jawa Timur</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Copyright -->
        <div class="border-t mt-8 pt-8 text-center text-sm text-muted-foreground">
          <p>&copy; {{ new Date().getFullYear() }} Sistem Rumah Pompa Surabaya. Semua hak dilindungi.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { 
  Droplet, 
  LogIn, 
  Menu, 
  Phone, 
  Mail, 
  MapPin 
} from 'lucide-vue-next'

const mobileMenuOpen = ref(false)
</script> 
 