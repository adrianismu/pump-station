// Composable untuk utility functions tanggal
export function useDateUtils() {
  
  // Format tanggal ke bahasa Indonesia
  const formatDate = (dateString, options = {}) => {
    if (!dateString) return "Tidak ada data"
    
    const defaultOptions = {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    }
    
    const finalOptions = { ...defaultOptions, ...options }
    
    try {
      return new Date(dateString).toLocaleDateString('id-ID', finalOptions)
    } catch (error) {
      console.warn('Error formatting date:', error)
      return new Date(dateString).toLocaleDateString()
    }
  }

  // Format waktu
  const formatTime = (dateString) => {
    if (!dateString) return "Tidak ada data"
    
    return new Date(dateString).toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  // Format tanggal dan waktu lengkap
  const formatDateTime = (dateString) => {
    if (!dateString) return "Tidak ada data"
    
    try {
      return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    } catch (error) {
      console.warn('Error formatting datetime:', error)
      // Fallback to manual formatting
      const date = new Date(dateString)
      const day = date.getDate().toString().padStart(2, '0')
      const month = date.getMonth() + 1
      const year = date.getFullYear()
      const hours = date.getHours().toString().padStart(2, '0')
      const minutes = date.getMinutes().toString().padStart(2, '0')
      
      const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ]
      
      return `${day} ${monthNames[month - 1]} ${year}, ${hours}:${minutes}`
    }
  }

  // Format "time ago" dalam bahasa Indonesia
  const formatTimeAgo = (dateString) => {
    if (!dateString) return "Tidak ada data"
    
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now - date) / 1000)

    if (diffInSeconds < 10) {
      return "Baru saja"
    }

    if (diffInSeconds < 60) {
      return `${diffInSeconds} detik yang lalu`
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60)
    if (diffInMinutes < 60) {
      return `${diffInMinutes} menit yang lalu`
    }

    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) {
      return `${diffInHours} jam yang lalu`
    }

    const diffInDays = Math.floor(diffInHours / 24)
    if (diffInDays === 1) {
      return "Kemarin"
    }
    
    if (diffInDays < 7) {
      return `${diffInDays} hari yang lalu`
    }

    const diffInWeeks = Math.floor(diffInDays / 7)
    if (diffInWeeks < 4) {
      return `${diffInWeeks} minggu yang lalu`
    }

    const diffInMonths = Math.floor(diffInDays / 30)
    if (diffInMonths < 12) {
      return `${diffInMonths} bulan yang lalu`
    }

    const diffInYears = Math.floor(diffInDays / 365)
    return `${diffInYears} tahun yang lalu`
  }

  return {
    formatDate,
    formatTime,
    formatDateTime,
    formatTimeAgo
  }
} 