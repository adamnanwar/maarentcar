<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'

defineProps<{
  title: string
  description: string
  icon: any
  iconBgColor: string
}>()
</script>

<template>
  <Card class="text-center hover:border-primary transition-colors border border-border">
    <CardContent class="p-6 space-y-4">
      <div 
        :class="['w-16 h-16 mx-auto rounded-full flex items-center justify-center', iconBgColor]"
      >
        <component :is="icon" class="w-8 h-8 text-white" />
      </div>
      <h3 class="font-bold text-lg text-foreground">{{ title }}</h3>
      <p class="text-muted-foreground text-sm leading-relaxed">{{ description }}</p>
    </CardContent>
  </Card>
</template>
