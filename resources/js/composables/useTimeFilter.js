import { ref, computed, watch } from 'vue'

// Composable untuk time filter logic
export function useTimeFilter(data, options = {}) {
  const {
    defaultFilter = '7d',
    dateField = 'recorded_at',
    loadingDelay = 300
  } = options

  // State
  const selectedTimeFilter = ref(defaultFilter)
  const isLoadingFilteredData = ref(false)

  // Default time filter options
  const defaultTimeFilters = [
    { label: '24H', value: '24h' },
    { label: '7D', value: '7d' },
    { label: '1M', value: '1m' },
    { label: '2M', value: '2m' },
    { label: '3M', value: '3m' }
  ]

  // Get cutoff date based on filter
  const getCutoffDate = (filter) => {
    const now = new Date()
    
    switch (filter) {
      case '24h':
        return new Date(now.getTime() - 24 * 60 * 60 * 1000)
      case '7d':
        return new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
      case '1m':
        return new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
      case '2m':
        return new Date(now.getTime() - 60 * 24 * 60 * 60 * 1000)
      case '3m':
        return new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000)
      case '6m':
        return new Date(now.getTime() - 180 * 24 * 60 * 60 * 1000)
      default:
        return new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
    }
  }

  // Filtered data based on selected time range
  const filteredData = computed(() => {
    if (!data.value || data.value.length === 0) {
      return []
    }

    const cutoffDate = getCutoffDate(selectedTimeFilter.value)
    
    return data.value.filter(item => {
      const itemDate = new Date(item[dateField])
      return itemDate >= cutoffDate
    })
  })

  // Get time range text for display
  const getTimeRangeText = (filters = defaultTimeFilters) => {
    const filter = filters.find(f => f.value === selectedTimeFilter.value)
    return `Data ${filter?.label || '7D'} terakhir`
  }

  // Watch for time filter changes to show loading state
  watch(selectedTimeFilter, (newValue, oldValue) => {
    if (newValue !== oldValue && loadingDelay > 0) {
      // Simulate loading state for smooth UX
      isLoadingFilteredData.value = true
      setTimeout(() => {
        isLoadingFilteredData.value = false
      }, loadingDelay)
    }
  })

  return {
    selectedTimeFilter,
    isLoadingFilteredData,
    filteredData,
    defaultTimeFilters,
    getTimeRangeText,
    getCutoffDate
  }
} 