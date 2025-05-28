<script setup>
import { cn } from '@/lib/utils';
import { Toggle, useForwardPropsEmits } from 'reka-ui';
import { computed } from 'vue';
import { toggleVariants } from '.';

const props = defineProps({
  defaultValue: { type: Boolean, required: false },
  modelValue: { type: [Boolean, null], required: false },
  disabled: { type: Boolean, required: false, default: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  name: { type: String, required: false },
  required: { type: Boolean, required: false },
  class: { type: null, required: false },
  variant: { type: null, required: false, default: 'default' },
  size: { type: null, required: false, default: 'default' },
});

const emits = defineEmits(['update:modelValue']);

const delegatedProps = computed(() => {
  const { class: _, size, variant, ...delegated } = props;

  return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <Toggle
    v-bind="forwarded"
    :class="cn(toggleVariants({ variant, size }), props.class)"
  >
    <slot />
  </Toggle>
</template>
