// Composable untuk utility functions status
export function useStatusUtils() {
  
  // Badge variant untuk status rumah pompa
  const getPumpHouseStatusVariant = (status) => {
    const statusMap = {
      'Aktif': 'default',
      'Perlu Perhatian': 'secondary',
      'Tidak Aktif': 'destructive'
    }
    return statusMap[status] || 'default'
  }

  // Badge variant untuk status laporan - STANDARDIZED
  const getReportStatusVariant = (status) => {
    const statusMap = {
      'Belum Ditanggapi': 'destructive',
      'Sedang Diproses': 'secondary',
      'Selesai': 'default'
    }
    return statusMap[status] || 'default'
  }

  // Badge variant untuk severity alert
  const getAlertSeverityVariant = (severity) => {
    const severityMap = {
      'Kritis': 'destructive',
      'Peringatan': 'secondary',
      'Informasi': 'default',
      'Normal': 'default'
    }
    return severityMap[severity] || 'default'
  }

  // Badge variant untuk tipe konten edukasi
  const getEducationTypeVariant = (type) => {
    const variantMap = {
      'Artikel': 'default',
      'Video': 'secondary',
      'Infografis': 'destructive',
    }
    return variantMap[type] || 'default'
  }

  // Badge variant untuk severity threshold
  const getSeverityVariant = (severity) => {
    const severityMap = {
      'low': 'default',
      'medium': 'secondary', 
      'high': 'destructive',
      'critical': 'destructive'
    }
    return severityMap[severity] || 'default'
  }

  // Label untuk severity threshold
  const getSeverityLabel = (severity) => {
    const labelMap = {
      'low': 'Normal',
      'medium': 'Peringatan',
      'high': 'Kritis', 
      'critical': 'Darurat'
    }
    return labelMap[severity] || severity
  }

  // Color untuk severity threshold
  const getSeverityColor = (severity) => {
    const colorMap = {
      'low': 'bg-green-500',
      'medium': 'bg-yellow-500',
      'high': 'bg-red-500',
      'critical': 'bg-red-600'
    }
    return colorMap[severity] || 'bg-gray-500'
  }

  // Class untuk severity alert
  const getAlertSeverityClass = (severity) => {
    const classMap = {
      'Kritis': 'bg-destructive/10 text-destructive border-destructive',
      'Peringatan': 'bg-yellow-50 text-yellow-700 border-yellow-200',
      'Informasi': 'bg-blue-50 text-blue-700 border-blue-200',
      'Normal': 'bg-green-50 text-green-700 border-green-200'
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

  // Standardized variant untuk notification status
  const getNotificationStatusVariant = (status) => {
    const statusMap = {
      'Belum Ditangani': 'destructive',
      'Sedang Diproses': 'secondary', 
      'Selesai': 'default',
      'Dalam Proses': 'secondary',
      'Memerlukan Tindak Lanjut': 'secondary'
    }
    return statusMap[status] || 'default'
  }

  // Badge variant untuk rainfall amount
  const getRainfallBadgeVariant = (rainfall) => {
    if (rainfall > 20) return 'destructive'
    if (rainfall > 10) return 'secondary'
    if (rainfall > 4) return 'outline'
    return 'default'
  }

  // Badge variant untuk flood risk level
  const getFloodRiskVariant = (risk) => {
    const variantMap = {
      'Tinggi': 'destructive',
      'Sedang': 'secondary', 
      'Rendah': 'default'
    }
    return variantMap[risk] || 'default'
  }

  return {
    getPumpHouseStatusVariant,
    getReportStatusVariant,
    getAlertSeverityVariant,
    getEducationTypeVariant,
    getSeverityVariant,
    getSeverityLabel,
    getSeverityColor,
    getAlertSeverityClass,
    getWaterLevelStatusColor,
    getNotificationStatusVariant,
    getRainfallBadgeVariant,
    getFloodRiskVariant
  }
} 