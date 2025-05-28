// Composable untuk utility functions status
export function useStatusUtils() {
  
  // Badge variant untuk status rumah pompa
  const getPumpHouseStatusVariant = (status) => {
    const statusMap = {
      'Aktif': 'success',
      'Perlu Perhatian': 'warning',
      'Tidak Aktif': 'destructive'
    }
    return statusMap[status] || 'default'
  }

  // Badge variant untuk status laporan
  const getReportStatusVariant = (status) => {
    const statusMap = {
      'Belum Ditanggapi': 'warning',
      'Sedang Diproses': 'default',
      'Selesai': 'success'
    }
    return statusMap[status] || 'default'
  }

  // Badge variant untuk severity alert
  const getAlertSeverityVariant = (severity) => {
    const severityMap = {
      'Kritis': 'destructive',
      'Peringatan': 'warning',
      'Informasi': 'default'
    }
    return severityMap[severity] || 'default'
  }

  // Badge variant untuk tipe konten edukasi
  const getEducationTypeVariant = (type) => {
    const variantMap = {
      'artikel': 'default',
      'video': 'secondary',
      'panduan': 'outline',
      'infografis': 'destructive',
    }
    return variantMap[type] || 'default'
  }

  // Class untuk severity alert
  const getAlertSeverityClass = (severity) => {
    const classMap = {
      'Kritis': 'bg-destructive/10 text-destructive border-destructive',
      'Peringatan': 'bg-warning/10 text-warning border-warning',
      'Informasi': 'bg-blue-50 text-blue-700 border-blue-200'
    }
    return classMap[severity] || ''
  }

  // Color untuk water level status
  const getWaterLevelStatusColor = (level) => {
    const colorMap = {
      'normal': 'bg-green-100 text-green-600',
      'low': 'bg-green-100 text-green-600',
      'medium': 'bg-yellow-100 text-yellow-600',
      'high': 'bg-red-100 text-red-600',
      'critical': 'bg-red-100 text-red-600',
    }
    return colorMap[level] || 'bg-green-100 text-green-600'
  }

  return {
    getPumpHouseStatusVariant,
    getReportStatusVariant,
    getAlertSeverityVariant,
    getEducationTypeVariant,
    getAlertSeverityClass,
    getWaterLevelStatusColor
  }
} 