import { ref, onMounted, watch } from 'vue'

export function useDarkMode() {
  const isDark = ref(false)

  // Check for saved theme preference or default to 'light' mode
  const getInitialTheme = () => {
    if (typeof window !== 'undefined') {
      const savedTheme = localStorage.getItem('theme')
      if (savedTheme) {
        return savedTheme === 'dark'
      }
      // Check system preference
      return window.matchMedia('(prefers-color-scheme: dark)').matches
    }
    return false
  }

  // Apply theme to document
  const applyTheme = (dark) => {
    if (typeof document !== 'undefined') {
      if (dark) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
      } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
      }
    }
  }

  // Toggle theme
  const toggleTheme = () => {
    isDark.value = !isDark.value
  }

  // Set theme explicitly
  const setTheme = (dark) => {
    isDark.value = dark
  }

  // Watch for changes and apply theme
  watch(isDark, (newValue) => {
    applyTheme(newValue)
  }, { immediate: false })

  // Initialize theme on mount
  onMounted(() => {
    isDark.value = getInitialTheme()
    applyTheme(isDark.value)

    // Listen for system theme changes
    if (typeof window !== 'undefined') {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      const handleChange = (e) => {
        // Only update if no manual preference is saved
        if (!localStorage.getItem('theme')) {
          isDark.value = e.matches
        }
      }
      
      mediaQuery.addEventListener('change', handleChange)
      
      // Cleanup
      return () => {
        mediaQuery.removeEventListener('change', handleChange)
      }
    }
  })

  return {
    isDark,
    toggleTheme,
    setTheme
  }
} 
 