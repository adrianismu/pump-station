<script setup>
import { AspectRatio } from 'reka-ui';

const props = defineProps({
  ratio: { type: Number, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
});
</script>

<template>
  <AspectRatio v-bind="props">
    <slot />
  </AspectRatio>
</template>
