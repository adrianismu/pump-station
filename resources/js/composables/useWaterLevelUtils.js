// Composable untuk utility functions water level
export function useWaterLevelUtils() {
  
  // Status text berdasarkan level air
  const getStatusText = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'Kritis'
    if (numLevel >= 2.0) return 'Peringatan'
    return 'Normal'
  }

  // Badge variant berdasarkan level air
  const getStatusBadgeVariant = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return 'destructive'
    if (numLevel >= 2.0) return 'secondary'
    return 'default'
  }

  // Status color berdasarkan level air
  const getStatusColor = (level) => {
    const numLevel = parseFloat(level)
    if (numLevel >= 2.5) return '#ef4444' // red
    if (numLevel >= 2.0) return '#f59e0b' // yellow
    return '#10b981' // green
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
        color: '#10b981',
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
      color: exceededThreshold.color || '#10b981',
      description: exceededThreshold.description || 'Status berdasarkan threshold'
    }
  }

  return {
    getStatusText,
    getStatusBadgeVariant,
    getStatusColor,
    getStatusWithThresholds
  }
} 