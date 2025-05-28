<template>
  <Badge :variant="variant" :class="customClass">
    {{ text }}
  </Badge>
</template>

<script setup>
import { computed } from 'vue'
import { Badge } from '@/Components/ui/badge'
import { useWaterLevelUtils } from '@/composables/useWaterLevelUtils'

const props = defineProps({
  level: {
    type: [String, Number],
    required: true
  },
  thresholds: {
    type: Array,
    default: () => []
  },
  type: {
    type: String,
    default: 'water-level', // 'water-level', 'pump-status', 'report-status', 'notification-severity', 'notification-status'
    validator: (value) => ['water-level', 'pump-status', 'report-status', 'notification-severity', 'notification-status'].includes(value)
  },
  customClass: {
    type: String,
    default: ''
  }
})

const { getStatusWithThresholds, getStatusText, getStatusBadgeVariant } = useWaterLevelUtils()

const statusInfo = computed(() => {
  switch (props.type) {
    case 'water-level':
      if (props.thresholds.length > 0) {
        return getStatusWithThresholds(props.level, props.thresholds)
      }
      return {
        text: getStatusText(props.level),
        variant: getStatusBadgeVariant(props.level)
      }
    
    case 'pump-status':
      return getPumpStatusInfo(props.level)
    
    case 'report-status':
      return getReportStatusInfo(props.level)
    
    case 'notification-severity':
      return getNotificationSeverityInfo(props.level)
    
    case 'notification-status':
      return getNotificationStatusInfo(props.level)
    
    default:
      return {
        text: props.level,
        variant: 'default'
      }
  }
})

const text = computed(() => statusInfo.value.text)
const variant = computed(() => statusInfo.value.variant)

// Helper functions for different status types
const getPumpStatusInfo = (status) => {
  const statusMap = {
    'Aktif': { text: 'Aktif', variant: 'default' },
    'Perlu Perhatian': { text: 'Perlu Perhatian', variant: 'secondary' },
    'Tidak Aktif': { text: 'Tidak Aktif', variant: 'destructive' }
  }
  return statusMap[status] || { text: status, variant: 'default' }
}

const getReportStatusInfo = (status) => {
  const statusMap = {
    'Belum Ditanggapi': { text: 'Belum Ditanggapi', variant: 'destructive' },
    'Sedang Diproses': { text: 'Sedang Diproses', variant: 'secondary' },
    'Selesai': { text: 'Selesai', variant: 'default' }
  }
  return statusMap[status] || { text: status, variant: 'default' }
}

const getNotificationSeverityInfo = (severity) => {
  const severityMap = {
    'Kritis': { text: 'Kritis', variant: 'destructive' },
    'Peringatan': { text: 'Peringatan', variant: 'secondary' },
    'Informasi': { text: 'Informasi', variant: 'default' }
  }
  return severityMap[severity] || { text: severity, variant: 'default' }
}

const getNotificationStatusInfo = (status) => {
  const statusMap = {
    'Belum Ditangani': { text: 'Belum Ditangani', variant: 'destructive' },
    'Sedang Diproses': { text: 'Sedang Diproses', variant: 'secondary' },
    'Selesai': { text: 'Selesai', variant: 'default' },
    'Dalam Proses': { text: 'Dalam Proses', variant: 'secondary' },
    'Memerlukan Tindak Lanjut': { text: 'Memerlukan Tindak Lanjut', variant: 'secondary' }
  }
  return statusMap[status] || { text: status, variant: 'default' }
}
</script> 