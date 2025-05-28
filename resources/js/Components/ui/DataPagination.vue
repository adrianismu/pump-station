<template>
  <div v-if="links && links.length > 0" class="flex justify-center mt-6">
    <div class="flex gap-2">
      <Button 
        v-for="link in links" 
        :key="link.label"
        variant="outline"
        size="sm"
        :disabled="!link.url"
        :class="{ 'bg-primary text-primary-foreground': link.active }"
        @click="visitPage(link.url)"
        v-html="link.label"
      />
    </div>
  </div>
</template>

<script setup>
import { Button } from '@/Components/ui/button'
import { router } from '@inertiajs/vue3'

defineProps({
  links: {
    type: Array,
    required: true
  }
})

const visitPage = (url) => {
  if (url) {
    router.visit(url, { preserveScroll: true })
  }
}
</script> 