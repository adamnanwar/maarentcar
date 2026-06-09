<script setup lang="ts">
defineProps<{
  icon: string
  title: string
  description: string
  color: 'blue' | 'purple' | 'orange' | 'green' | 'red' | 'yellow' | 'indigo'
}>()

const colorClass = (color: string) => `bg-feature-${color}`
</script>

<template>
  <div 
    :class="[colorClass(color), 'rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow duration-200']"
  >
    <div class="flex flex-col items-start space-y-3">
      <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
        <component :is="icon" class="h-8 w-8 text-white" />
      </div>
      <div>
        <h3 class="text-lg font-bold mb-1">{{ title }}</h3>
        <p class="text-sm text-white/90 leading-relaxed">{{ description }}</p>
      </div>
    </div>
  </div>
</template>
