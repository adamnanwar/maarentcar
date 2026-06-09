<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Card, CardContent } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Badge } from '@/components/ui/badge'
import { Gauge, Star } from 'lucide-vue-next'

defineProps<{
  car: {
    id: number
    name: string
    brand: string
    price: number
    transmission: string
    rating?: number
    image?: string
    isPopular?: boolean
  }
}>()

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price)
}
</script>

<template>
  <Link :href="`/cars/${car.id}`">
    <Card class="hover:border-primary transition-all border border-border overflow-hidden">
      <!-- Car Image -->
      <div class="aspect-[4/3] bg-gray-100 relative">
        <img 
          v-if="car.image" 
          :src="car.image" 
          :alt="car.name"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
          <Gauge class="w-16 h-16" />
        </div>
        
        <Badge 
          v-if="car.isPopular" 
          class="absolute top-3 left-3 bg-accent hover:bg-accent"
        >
          Popular
        </Badge>
      </div>

      <CardContent class="p-5 space-y-3">
        <!-- Car Info -->
        <div>
          <h3 class="font-bold text-foreground truncate">{{ car.name }}</h3>
          <p class="text-xs text-muted-foreground">{{ car.brand }}</p>
          <p class="text-primary font-semibold mt-1">{{ formatPrice(car.price) }}/hari</p>
        </div>

        <Separator />

        <!-- Specs -->
        <div class="flex items-center justify-between text-sm">
          <div class="flex items-center gap-1 text-foreground">
            <Gauge class="w-4 h-4" />
            <span>{{ car.transmission }}</span>
          </div>
          <div class="flex items-center gap-1 text-foreground">
            <span>{{ car.rating || 4.5 }}/5</span>
            <Star class="w-4 h-4 fill-yellow-400 text-yellow-400" />
          </div>
        </div>
      </CardContent>
    </Card>
  </Link>
</template>
