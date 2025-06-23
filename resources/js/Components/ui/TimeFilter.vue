<template>
  <div class="flex items-center gap-2">
    <span class="text-sm text-muted-foreground mr-2">{{ label }}:</span>
    <div class="flex border rounded-md p-1">
      <button
        v-for="filter in filters"
        :key="filter.value"
        @click="$emit('update:modelValue', filter.value)"
        :class="[
          'px-3 py-1 text-xs font-medium rounded transition-colors',
          modelValue === filter.value
            ? 'bg-primary text-primary-foreground'
            : 'text-muted-foreground hover:text-foreground hover:bg-muted'
        ]"
      >
        {{ filter.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: 'Periode'
  },
  filters: {
    type: Array,
    default: () => [
      { label: '24H', value: '24h' },
      { label: '7D', value: '7d' },
      { label: '1M', value: '1m' }
    ]
  }
})

defineEmits(['update:modelValue'])
</script> 