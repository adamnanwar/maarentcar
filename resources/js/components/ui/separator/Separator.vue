<script setup lang="ts">
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    orientation?: 'horizontal' | 'vertical'
    decorative?: boolean
    class?: string
  }>(),
  {
    orientation: 'horizontal',
    decorative: true
  }
)
</script>

<template>
  <div
    :class="cn(
      'shrink-0 bg-border',
      props.orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
      props.class
    )"
    :role="props.decorative ? 'none' : 'separator'"
    :aria-orientation="props.orientation"
  />
</template>
