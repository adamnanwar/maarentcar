<script setup lang="ts">
defineProps<{
  value: string
  label: string
  icon?: any
}>()
</script>

<template>
  <div class="text-center">
    <component v-if="icon" :is="icon" class="h-8 w-8 text-blue-600 mx-auto mb-2" />
    <div class="text-3xl font-bold text-slate-900 mb-1">{{ value }}</div>
    <div class="text-sm text-slate-600">{{ label }}</div>
  </div>
</template>
