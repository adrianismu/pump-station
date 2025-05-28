<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="outline" size="icon">
        <Sun
          class="h-[1.2rem] w-[1.2rem] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0"
        />
        <Moon
          class="absolute h-[1.2rem] w-[1.2rem] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100"
        />
        <span class="sr-only">Toggle theme</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuItem @click="setTheme(false)">
        <Sun class="mr-2 h-4 w-4" />
        <span>Terang</span>
      </DropdownMenuItem>
      <DropdownMenuItem @click="setTheme(true)">
        <Moon class="mr-2 h-4 w-4" />
        <span>Gelap</span>
      </DropdownMenuItem>
      <DropdownMenuItem @click="setSystemTheme">
        <Monitor class="mr-2 h-4 w-4" />
        <span>Sistem</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<script setup>
import { Sun, Moon, Monitor } from 'lucide-vue-next'
import { Button } from '@/Components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import { useDarkMode } from '@/composables/useDarkMode'

const { setTheme } = useDarkMode()

const setSystemTheme = () => {
  // Remove manual preference to follow system
  localStorage.removeItem('theme')
  
  // Set based on current system preference
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  setTheme(systemPrefersDark)
}
</script> 
 