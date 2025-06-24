// Composable untuk utility functions water level
export function useWaterLevelUtils() {
  
  // Status text berdasarkan level air
  const getStatusText = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'Level Kritis'
    if (numLevel >= 2.0) return 'Level Peringatan'
    return 'Level Normal'
  }

  // Badge variant untuk status water level
  const getStatusBadgeVariant = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'destructive'
    if (numLevel >= 2.0) return 'secondary'
    return 'default'
  }

  // Warna untuk indikator water level
  const getStatusColor = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return '#ef4444' // red-500
    if (numLevel >= 2.0) return '#eab308' // yellow-500
    return '#22c55e' // green-500
  }

  // CSS class untuk status indicator
  const getStatusIndicatorClass = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'bg-red-500'
    if (numLevel >= 2.0) return 'bg-yellow-500'
    return 'bg-green-500'
  }

  // CSS class untuk text color
  const getStatusTextClass = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'text-red-700'
    if (numLevel >= 2.0) return 'text-yellow-700'
    return 'text-green-700'
  }

  // Deskripsi lengkap status
  const getStatusDescription = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'Ketinggian air sangat tinggi, berpotensi banjir'
    if (numLevel >= 2.0) return 'Ketinggian air tinggi, perlu waspada'
    return 'Ketinggian air dalam batas normal'
  }

  // Format level dengan unit
  const formatWaterLevel = (level) => {
    return `${parseFloat(level).toFixed(2)} m`
  }

  // Check apakah level dalam kondisi emergency
  const isEmergencyLevel = (level) => {
    return parseFloat(level) >= 2.5
  }

  // Check apakah level dalam kondisi warning
  const isWarningLevel = (level) => {
    const numLevel = parseFloat(level)
    return numLevel >= 2.0 && numLevel < 2.5
  }

  // Check apakah level normal
  const isNormalLevel = (level) => {
    return parseFloat(level) < 2.0
  }

  // Status dengan threshold custom
  const getStatusWithThresholds = (level, thresholds) => {
    const numLevel = parseFloat(level)
    
    if (!thresholds || thresholds.length === 0) {
      return {
        text: getStatusText(level),
        label: getStatusText(level),
        variant: getStatusBadgeVariant(level),
        color: getStatusColor(level),
        description: 'Status berdasarkan threshold default'
      }
    }

    // Find the highest threshold that is exceeded
    const exceededThreshold = thresholds
      .filter(t => numLevel >= parseFloat(t.water_level))
      .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0]

    if (!exceededThreshold) {
      return {
        text: 'Normal',
        label: 'Normal',
        variant: 'default',
        color: '#22c55e',
        description: 'Ketinggian air dalam batas normal'
      }
    }

    const variantMap = {
      'low': 'default',
      'medium': 'secondary',
      'high': 'destructive',
      'critical': 'destructive'
    }

    return {
      text: exceededThreshold.label || exceededThreshold.name,
      label: exceededThreshold.label || exceededThreshold.name,
      variant: variantMap[exceededThreshold.severity] || 'default',
      color: exceededThreshold.color || '#22c55e',
      description: exceededThreshold.description || 'Status berdasarkan threshold'
    }
  }

  return {
    getStatusText,
    getStatusBadgeVariant,
    getStatusColor,
    getStatusIndicatorClass,
    getStatusTextClass,
    getStatusDescription,
    formatWaterLevel,
    isEmergencyLevel,
    isWarningLevel,
    isNormalLevel,
    getStatusWithThresholds
  }
} 