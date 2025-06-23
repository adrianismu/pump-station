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
    
    return new Date(dateString).toLocaleDateString('id-ID', finalOptions)
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
    
    return new Date(dateString).toLocaleString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  // Format "time ago" dalam bahasa Indonesia
  const formatTimeAgo = (dateString) => {
    if (!dateString) return "Tidak ada data"
    
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now - date) / 1000)

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
    if (diffInDays < 30) {
      return `${diffInDays} hari yang lalu`
    }

    const diffInMonths = Math.floor(diffInDays / 30)
    return `${diffInMonths} bulan yang lalu`
  }

  return {
    formatDate,
    formatTime,
    formatDateTime,
    formatTimeAgo
  }
} 