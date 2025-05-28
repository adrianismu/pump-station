# Dark Mode Implementation

## 🌙 Implementasi Dark Mode dengan Shadcn-Vue

Sistem dark mode telah diimplementasikan menggunakan pendekatan **CSS Variables** sesuai dengan dokumentasi [shadcn-vue](https://www.shadcn-vue.com/docs/dark-mode.html).

## 📁 File yang Ditambahkan/Dimodifikasi

### 1. **Composable untuk Dark Mode**
- `resources/js/composables/useDarkMode.js`
- Mengelola state dark mode dengan localStorage
- Auto-detect system preference
- Reactive theme switching

### 2. **Komponen Theme Toggle**
- `resources/js/Components/ThemeToggle.vue`
- Dropdown menu dengan 3 pilihan: Terang, Gelap, Sistem
- Animasi smooth transition untuk icon
- Menggunakan Lucide icons (Sun, Moon, Monitor)

### 3. **CSS Variables**
- `resources/css/app.css` - Updated dengan dark mode variables
- Semua warna menggunakan HSL format tanpa color space function
- Support untuk chart colors dan additional colors

### 4. **Tailwind Configuration**
- `tailwind.config.js` - Dark mode: `['class']`
- Extended colors untuk success, warning, info
- Chart colors untuk light dan dark mode

### 5. **AdminLayout Integration**
- `resources/js/Layouts/AdminLayout.vue`
- Theme toggle ditambahkan di header sebelah notifikasi
- Import ThemeToggle component

## 🎨 CSS Variables yang Tersedia

### Light Mode
```css
:root {
  --background: 0 0% 100%;
  --foreground: 240 10% 3.9%;
  --primary: 240 5.9% 10%;
  --secondary: 240 4.8% 95.9%;
  --success: 142 76% 36%;
  --warning: 38 92% 50%;
  --info: 199 89% 48%;
  /* ... dan lainnya */
}
```

### Dark Mode
```css
.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  --primary: 210 40% 98%;
  --secondary: 217.2 32.6% 17.5%;
  
  /* Sidebar colors yang selaras */
  --sidebar-background: 222.2 84% 4.9%; /* = background */
  --sidebar-foreground: 210 40% 98%;    /* = foreground */
  --sidebar-border: 217.2 32.6% 17.5%;  /* = border */
  /* ... dan lainnya */
}
```

## 🚀 Cara Penggunaan

### 1. **Menggunakan Composable**
```vue
<script setup>
import { useDarkMode } from '@/composables/useDarkMode'

const { isDark, toggleTheme, setTheme } = useDarkMode()

// Toggle theme
const handleToggle = () => {
  toggleTheme()
}

// Set specific theme
const setLightMode = () => {
  setTheme(false)
}

const setDarkMode = () => {
  setTheme(true)
}
</script>
```

### 2. **Menggunakan CSS Classes**
```vue
<template>
  <!-- Akan otomatis berubah sesuai theme -->
  <div class="bg-background text-foreground">
    <div class="bg-card text-card-foreground p-4 rounded-lg">
      <h1 class="text-primary">Judul</h1>
      <p class="text-muted-foreground">Deskripsi</p>
    </div>
  </div>
</template>
```

### 3. **Conditional Styling**
```vue
<template>
  <div class="bg-white dark:bg-gray-900 text-black dark:text-white">
    Content yang berubah sesuai theme
  </div>
</template>
```

### 4. **Utility Classes Baru**
```vue
<template>
  <!-- Smooth transitions -->
  <div class="dark-mode-transition bg-background text-foreground">
    Content dengan transisi smooth
  </div>
  
  <!-- Sidebar styling yang konsisten -->
  <div class="sidebar-consistent">
    Sidebar content
  </div>
  
  <!-- Main content styling yang konsisten -->
  <div class="main-content-consistent">
    Main content
  </div>
</template>
```

## 🎯 Fitur yang Tersedia

- ✅ **Auto-detect system preference**
- ✅ **Persistent theme selection** (localStorage)
- ✅ **Smooth transitions** untuk semua komponen
- ✅ **3 pilihan theme**: Light, Dark, System
- ✅ **Responsive design** untuk semua device
- ✅ **Accessible** dengan proper ARIA labels
- ✅ **Chart support** dengan warna yang sesuai theme
- ✅ **Consistent sidebar colors** yang selaras dengan main content
- ✅ **Smooth transitions** untuk semua perubahan warna

## 🔧 Kustomisasi

### Menambah Warna Baru
1. Tambahkan CSS variable di `resources/css/app.css`:
```css
:root {
  --custom-color: 200 50% 60%;
}

.dark {
  --custom-color: 200 70% 40%;
}
```

2. Tambahkan ke `tailwind.config.js`:
```js
colors: {
  'custom': 'hsl(var(--custom-color))',
}
```

3. Gunakan di komponen:
```vue
<div class="bg-custom text-white">Custom color</div>
```

## 📱 Browser Support

- ✅ Chrome 76+
- ✅ Firefox 67+
- ✅ Safari 12.1+
- ✅ Edge 79+

## 🐛 Troubleshooting

### Theme tidak tersimpan
- Pastikan localStorage tersedia
- Check browser privacy settings

### Warna tidak berubah
- Pastikan menggunakan CSS variables: `bg-background` bukan `bg-white`
- Check apakah class `dark` ditambahkan ke `<html>`

### Sidebar dan main content tidak selaras
- ✅ **FIXED**: Sidebar sekarang menggunakan warna yang sama dengan main content
- Sidebar background: `--sidebar-background` = `--background`
- Sidebar foreground: `--sidebar-foreground` = `--foreground`

### Transition tidak smooth
- ✅ **FIXED**: Ditambahkan smooth transitions untuk semua komponen
- Transition duration: 300ms dengan ease-in-out
- Mencakup background, border, dan text colors

## 📚 Referensi

- [Shadcn-Vue Dark Mode](https://www.shadcn-vue.com/docs/dark-mode.html)
- [Shadcn-Vue Theming](https://www.shadcn-vue.com/docs/theming.html)
- [Tailwind CSS Dark Mode](https://tailwindcss.com/docs/dark-mode) 
 