// Composable untuk utility functions gambar
export function useImageUtils() {
  
  // Parse images dari JSON string atau array
  const parseImages = (imagesData) => {
    if (!imagesData) return []
    
    try {
      // Jika sudah array, return langsung
      if (Array.isArray(imagesData)) {
        return imagesData
      }
      
      // Jika string JSON, parse
      if (typeof imagesData === 'string') {
        return JSON.parse(imagesData)
      }
      
      return []
    } catch (error) {
      console.warn('Failed to parse images data:', error)
      return []
    }
  }

  // Validasi file gambar
  const validateImageFile = (file, maxSizeMB = 2) => {
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
    
    if (!validTypes.includes(file.type)) {
      return {
        valid: false,
        error: `Format file tidak didukung. Gunakan: ${validTypes.join(', ')}`
      }
    }
    
    const maxSizeBytes = maxSizeMB * 1024 * 1024
    if (file.size > maxSizeBytes) {
      return {
        valid: false,
        error: `Ukuran file terlalu besar. Maksimal ${maxSizeMB}MB`
      }
    }
    
    return { valid: true }
  }

  // Generate preview URL dari file
  const generatePreviewUrl = (file) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = (e) => resolve(e.target.result)
      reader.onerror = reject
      reader.readAsDataURL(file)
    })
  }

  // Format file size ke human readable
  const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  }

  // Extract file extension
  const getFileExtension = (filename) => {
    return filename.split('.').pop().toLowerCase()
  }

  // Check if URL is Cloudinary
  const isCloudinaryUrl = (url) => {
    return url && url.includes('cloudinary.com')
  }

  // Extract Cloudinary public ID from URL
  const extractCloudinaryPublicId = (url) => {
    if (!isCloudinaryUrl(url)) return null
    
    const pattern = /\/v\d+\/(.+)\.[a-zA-Z]+$/
    const match = url.match(pattern)
    return match ? match[1] : null
  }

  return {
    parseImages,
    validateImageFile,
    generatePreviewUrl,
    formatFileSize,
    getFileExtension,
    isCloudinaryUrl,
    extractCloudinaryPublicId
  }
} 