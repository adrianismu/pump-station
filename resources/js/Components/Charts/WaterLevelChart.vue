<template>
  <div class="w-full">
    <Line
      v-if="chartData && chartData.datasets.length > 0"
      :data="chartData"
      :options="chartOptions"
      :height="height"
    />
    <div v-else class="flex items-center justify-center h-64 text-muted-foreground">
      <div class="text-center">
        <BarChart3 class="w-12 h-12 mx-auto mb-2" />
        <p>Tidak ada data untuk ditampilkan</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, ref } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line } from 'vue-chartjs';
import { BarChart3 } from 'lucide-vue-next';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  thresholds: {
    type: Array,
    default: () => []
  },
  height: {
    type: Number,
    default: 100
  },
  showThresholds: {
    type: Boolean,
    default: true
  },
  timeRange: {
    type: String,
    default: '7d' // 7d, 30d, 90d
  }
});

// Process chart data
const chartData = computed(() => {
  if (!props.data || props.data.length === 0) {
    return null;
  }

  // Sort data by date
  const sortedData = [...props.data].sort((a, b) => 
    new Date(a.recorded_at) - new Date(b.recorded_at)
  );

  // Prepare labels and data points
  const labels = sortedData.map(item => {
    const date = new Date(item.recorded_at);
    return date.toLocaleDateString('id-ID', {
      day: '2-digit',
      month: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    });
  });

  const waterLevels = sortedData.map(item => parseFloat(item.water_level));

  // Create gradient for water level line
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, 400);
  gradient.addColorStop(0, 'rgba(59, 130, 246, 0.8)');
  gradient.addColorStop(0.5, 'rgba(59, 130, 246, 0.4)');
  gradient.addColorStop(1, 'rgba(59, 130, 246, 0.1)');

  const datasets = [
    {
      label: 'Ketinggian Air',
      data: waterLevels,
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: gradient,
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: 'rgb(59, 130, 246)',
      pointBorderColor: '#ffffff',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
    }
  ];

  // Add threshold lines if enabled
  if (props.showThresholds && props.thresholds.length > 0) {
    props.thresholds.forEach(threshold => {
      if (threshold.is_active) {
        const thresholdData = new Array(labels.length).fill(parseFloat(threshold.water_level));
        
        datasets.push({
          label: threshold.label,
          data: thresholdData,
          borderColor: threshold.color,
          backgroundColor: threshold.color + '20',
          borderWidth: 2,
          borderDash: [5, 5],
          fill: false,
          pointRadius: 0,
          pointHoverRadius: 0,
        });
      }
    });
  }

  return {
    labels,
    datasets
  };
});

// Chart options
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index',
  },
  plugins: {
    title: {
      display: true,
      text: 'Grafik Ketinggian Air',
      font: {
        size: 16,
        weight: 'bold'
      },
      color: '#374151'
    },
    legend: {
      display: true,
      position: 'top',
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#ffffff',
      bodyColor: '#ffffff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      cornerRadius: 8,
      displayColors: true,
      callbacks: {
        title: function(context) {
          return 'Waktu: ' + context[0].label;
        },
        label: function(context) {
          if (context.datasetIndex === 0) {
            return `Ketinggian Air: ${context.parsed.y}m`;
          } else {
            return `${context.dataset.label}: ${context.parsed.y}m`;
          }
        },
        afterBody: function(context) {
          if (context[0].datasetIndex === 0) {
            const waterLevel = context[0].parsed.y;
            const threshold = getThresholdForLevel(waterLevel);
            if (threshold) {
              return [`Status: ${threshold.label}`, `Tingkat: ${threshold.severity}`];
            }
          }
          return [];
        }
      }
    }
  },
  scales: {
    x: {
      display: true,
      title: {
        display: true,
        text: 'Waktu',
        font: {
          size: 14,
          weight: 'bold'
        }
      },
      grid: {
        display: true,
        color: 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        maxTicksLimit: 10,
        font: {
          size: 11
        }
      }
    },
    y: {
      display: true,
      title: {
        display: true,
        text: 'Ketinggian Air (meter)',
        font: {
          size: 14,
          weight: 'bold'
        }
      },
      grid: {
        display: true,
        color: 'rgba(0, 0, 0, 0.1)'
      },
      beginAtZero: true,
      suggestedMax: Math.max(
        ...props.data.map(d => parseFloat(d.water_level)),
        ...props.thresholds.map(t => parseFloat(t.water_level))
      ) + 0.5,
      ticks: {
        callback: function(value) {
          return value + 'm';
        },
        font: {
          size: 11
        }
      }
    }
  },
  elements: {
    point: {
      hoverBackgroundColor: '#ffffff',
      hoverBorderWidth: 3
    }
  }
}));

// Helper function to get threshold for water level
const getThresholdForLevel = (waterLevel) => {
  return props.thresholds
    .filter(t => waterLevel >= parseFloat(t.water_level))
    .sort((a, b) => parseFloat(b.water_level) - parseFloat(a.water_level))[0];
};
</script>

<style scoped>
/* Custom styles for chart container */
.chart-container {
  position: relative;
  width: 100%;
}
</style> 
 