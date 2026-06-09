<script setup lang="ts">
import { Star } from 'lucide-vue-next'

defineProps<{
  name: string
  location: string
  review: string
  rating?: number
  avatarColor?: string
}>()

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const avatarBgColors = [
  'bg-blue-500',
  'bg-purple-500', 
  'bg-pink-500',
  'bg-green-500',
  'bg-orange-500',
  'bg-indigo-500'
]

const getRandomColor = () => {
  return avatarBgColors[Math.floor(Math.random() * avatarBgColors.length)]
}
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
    <!-- Star Rating -->
    <div class="flex gap-1 mb-3">
      <Star 
        v-for="i in (rating || 5)" 
        :key="i" 
        class="h-4 w-4 text-yellow-400 fill-yellow-400" 
      />
    </div>

    <!-- Review Text -->
    <p class="text-slate-700 text-sm leading-relaxed mb-4 italic">
      "{{ review }}"
    </p>

    <!-- Author -->
    <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
      <div :class="[avatarColor || getRandomColor(), 'h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm']">
        {{ getInitials(name) }}
      </div>
      <div>
        <div class="font-semibold text-slate-900 text-sm">{{ name }}</div>
        <div class="text-xs text-slate-500">{{ location }}</div>
      </div>
    </div>
  </div>
</template>
