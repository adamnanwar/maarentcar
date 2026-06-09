<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { ref } from 'vue'

defineProps<{
  cars?: any[]
}>()

// Sample car data - will come from backend later
const sampleCars = ref([
  {
    id: 1,
    name: 'Toyota Avanza',
    brand: 'Toyota',
    type: 'MPV',
    transmission: 'Manual',
    price_per_day: 350000,
    rating: 4.8,
    image: null,
  },
  {
    id: 2,
    name: 'Honda BR-V',
    brand: 'Honda',
    type: 'SUV',
    transmission: 'Automatic',
    price_per_day: 450000,
    rating: 4.9,
    image: null,
  },
  {
    id: 3,
    name: 'Mitsubishi Xpander',
    brand: 'Mitsubishi',
    type: 'MPV',
    transmission: 'Manual',
    price_per_day: 400000,
    rating: 4.7,
    image: null,
  },
  {
    id: 4,
    name: 'Toyota Innova Reborn',
    brand: 'Toyota',
    type: 'MPV',
    transmission: 'Automatic',
    price_per_day: 500000,
    rating: 4.8,
    image: null,
  },
  {
    id: 5,
    name: 'Honda CR-V',
    brand: 'Honda',
    type: 'SUV',
    transmission: 'Automatic',
    price_per_day: 600000,
    rating: 4.9,
    image: null,
  },
  {
    id: 6,
    name: 'Daihatsu Terios',
    brand: 'Daihatsu',
    type: 'SUV',
    transmission: 'Manual',
    price_per_day: 350000,
    rating: 4.6,
    image: null,
  },
])

const filters = ['Semua', 'SUV', 'MPV', 'Sedan', 'Hatchback']

const formatPrice = (price: number) => {
  return `Rp ${price.toLocaleString('id-ID')}`
}
</script>

<template>
  <Head title="Daftar Mobil - MaaRentCar" />
  
  <MainLayout>
    <div class="px-4">
      <!-- Header -->
      <h1 class="mb-3 font-poppins font-bold text-[#060521]">Semua Mobil</h1>
      
      <!-- Filter Pills (Horizontal Scroll) -->
      <div class="mb-4 flex gap-x-2 overflow-x-auto scrollbar-hide pb-2">
        <button 
          v-for="filter in filters" 
          :key="filter"
          class="whitespace-nowrap rounded-[50px] bg-[#FFFFFF] border border-[#EFF2F7] px-[14px] py-[10px] font-poppins font-semibold text-sm transition-all hover:border-[#362EED]"
        >
          {{ filter }}
        </button>
      </div>

      <!-- Car List (Vertical - matching "Newest Arrival" style) -->
      <div class="w-full space-y-3">
        <Link 
          v-for="car in sampleCars" 
          :key="car.id"
          :href="`/cars/${car.id}`"
          class="block w-full rounded-[20px] border border-[#EFF2F7] bg-[#FFFFFF] px-[14px] py-[10px] transition-all duration-300 hover:border-[#362EED]"
        >
          <div class="flex w-full items-center gap-x-[14px]">
            <!-- Image -->
            <div class="h-[100px] w-[130px] rounded bg-gray-100 flex items-center justify-center flex-shrink-0">
              <img 
                v-if="car.image" 
                :src="car.image" 
                :alt="car.name"
                class="h-full w-full object-cover rounded"
              />
              <svg v-else class="w-12 h-12 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" />
              </svg>
            </div>
            
            <!-- Info -->
            <div class="w-full space-y-[14px]">
              <div class="space-y-1">
                <h1 class="line-clamp-1 font-poppins font-bold text-[#060521]">{{ car.name }}</h1>
                <p class="font-poppins text-xs text-[#828FA1]">{{ car.brand }} • {{ car.type }}</p>
                <p class="font-poppins text-sm font-semibold leading-[21px] text-[#362EED]">
                  {{ formatPrice(car.price_per_day) }}/hari
                </p>
              </div>
              <div class="flex items-center justify-between">
                <p class="font-poppins text-sm font-semibold leading-[21px] text-[#060521]">{{ car.transmission }}</p>
                <div class="flex items-center gap-x-[2px]">
                  <p class="font-poppins text-sm font-semibold leading-[21px] text-[#060521]">{{ car.rating }}/5</p>
                  <span class="text-yellow-400">⭐</span>
                </div>
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </MainLayout>
</template>
