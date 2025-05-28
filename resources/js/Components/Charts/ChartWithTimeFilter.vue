<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <CardTitle class="flex items-center gap-2">
          <component :is="titleIcon" class="w-5 h-5" />
          {{ title }}
        </CardTitle>
        
        <!-- Time Range Filter -->
        <div class="flex items-center gap-4">
          <TimeFilter
            v-model="selectedTimeFilter"
            :filters="timeFilters"
            :label="filterLabel"
          />
          
          <Button 
            v-if="showThresholdToggle"
            variant="outline" 
            size="sm" 
            @click="toggleThresholdLines"
          >
            <Settings class="w-4 h-4 mr-2" />
            {{ showThresholdLines ? 'Sembunyikan' : 'Tampilkan' }} Threshold
          </Button>
        </div>
      </div>
      
      <div class="flex items-center gap-2 mt-2">
        <CardDescription>
          {{ description }}
        </CardDescription>
        <div class="flex items-center gap-2 ml-4">
          <span class="text-sm text-muted-foreground">Data:</span>
          <span class="text-sm">{{ getTimeRangeText() }}</span>
        </div>
      </div>
    </CardHeader>
    
    <CardContent>
      <!-- Loading state untuk data yang sedang difilter -->
      <div v-if="isLoadingFilteredData" class="flex items-center justify-center" :style="{ height: `${height}px` }">
        <Loader2 class="h-8 w-8 animate-spin text-primary" />
        <span class="ml-2 text-sm text-muted-foreground">Memuat data...</span>
      </div>
      
      <!-- Chart dengan data yang difilter -->
      <div v-else>
        <div :style="{ height: `${height}px` }" class="w-full">
          <WaterLevelChart
            :data="filteredData"
            :thresholds="thresholds"
            :show-thresholds="showThresholdLines"
            :height="height"
          />
        </div>
        
        <!-- Chart Info -->
        <div class="flex items-center justify-between text-xs text-muted-foreground mt-4">
          <div class="flex items-center gap-4">
            <span>{{ filteredData.length }} data points</span>
            <span v-if="filteredData.length === 0" class="text-amber-600">
              Tidak ada data untuk periode ini
            </span>
          </div>
          <span>{{ getTimeRangeText() }}</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Settings, Loader2, TrendingUp } from 'lucide-vue-next'
import TimeFilter from '@/Components/ui/TimeFilter.vue'
import WaterLevelChart from '@/Components/Charts/WaterLevelChart.vue'
import { useTimeFilter } from '@/composables/useTimeFilter'

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  thresholds: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Grafik Ketinggian Air'
  },
  description: {
    type: String,
    default: 'Visualisasi data ketinggian air'
  },
  titleIcon: {
    type: Object,
    default: () => TrendingUp
  },
  height: {
    type: Number,
    default: 320
  },
  showThresholdToggle: {
    type: Boolean,
    default: true
  },
  filterLabel: {
    type: String,
    default: 'Periode'
  },
  timeFilters: {
    type: Array,
    default: () => [
      { label: '24H', value: '24h' },
      { label: '7D', value: '7d' },
      { label: '1M', value: '1m' },
      { label: '2M', value: '2m' },
      { label: '3M', value: '3m' }
    ]
  }
})

// Convert data to reactive ref for useTimeFilter
const dataRef = computed(() => props.data)

// Use time filter composable
const {
  selectedTimeFilter,
  isLoadingFilteredData,
  filteredData,
  getTimeRangeText
} = useTimeFilter(dataRef)

// Threshold toggle state
const showThresholdLines = ref(true)

const toggleThresholdLines = () => {
  showThresholdLines.value = !showThresholdLines.value
}
</script> 