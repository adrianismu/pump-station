<script setup>
import { cn } from '@/lib/utils';
import { toggleVariants } from '@/Components/ui/toggle';
import { ToggleGroupItem, useForwardProps } from 'reka-ui';
import { computed, inject } from 'vue';

const props = defineProps({
  value: { type: [String, Number, Object, null], required: true },
  disabled: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
  variant: { type: null, required: false },
  size: { type: null, required: false },
});

const context = inject('toggleGroup');

const delegatedProps = computed(() => {
  const { class: _, variant, size, ...delegated } = props;
  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);
</script>

<template>
  <ToggleGroupItem
    v-bind="forwardedProps"
    :class="
      cn(
        toggleVariants({
          variant: context?.variant || variant,
          size: context?.size || size,
        }),
        props.class,
      )
    "
  >
    <slot />
  </ToggleGroupItem>
</template>
