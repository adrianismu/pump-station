<script setup>
import { cn } from '@/lib/utils';
import { Search } from 'lucide-vue-next';
import { ListboxFilter, useForwardProps } from 'reka-ui';
import { computed } from 'vue';
import { useCommand } from '.';

defineOptions({
  inheritAttrs: false,
});

const props = defineProps({
  modelValue: { type: String, required: false },
  autoFocus: { type: Boolean, required: false },
  disabled: { type: Boolean, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  class: { type: null, required: false },
});

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});

const forwardedProps = useForwardProps(delegatedProps);

const { filterState } = useCommand();
</script>

<template>
  <div class="flex items-center border-b px-3" cmdk-input-wrapper>
    <Search class="mr-2 h-4 w-4 shrink-0 opacity-50" />
    <ListboxFilter
      v-bind="{ ...forwardedProps, ...$attrs }"
      v-model="filterState.search"
      auto-focus
      :class="
        cn(
          'flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50',
          props.class,
        )
      "
    />
  </div>
</template>
