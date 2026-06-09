<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { carService } from '@/services/carService'
import type { Car } from '@/types/models'
import { ArrowRight, Car as CarIcon, Users, Shield, Clock } from 'lucide-vue-next'

const cars = ref<Car[]>([])
const loading = ref(true)

// Categories for horizontal scroll
const categories = [
  { name: 'SUV', link: '/cars?type=SUV', icon: '🚙' },
  { name: 'Sedan', link: '/cars?type=Sedan', icon: '🚗' },
  { name: 'MPV', link: '/cars?type=MPV', icon: '🚐' },
  { name: 'Hatchback', link: '/cars?type=Hatchback', icon: '🚘' },
  { name: 'Luxury', link: '/cars?type=Luxury', icon: '✨' },
]

const features = [
  { icon: CarIcon, title: 'Armada Lengkap', desc: 'Pilihan mobil dari berbagai merek dan tipe' },
  { icon: Shield, title: 'Terjamin Aman', desc: 'Semua mobil terawat dan berasuransi' },
  { icon: Users, title: 'Driver Berpengalaman', desc: 'Tersedia layanan dengan driver profesional' },
  { icon: Clock, title: 'Proses Cepat', desc: 'Booking mudah dan konfirmasi instan' },
]

// Computed properties
const popularCars = computed(() => cars.value.slice(0, 4))
const newestCars = computed(() => cars.value.slice(0, 6))

onMounted(async () => {
  try {
    cars.value = await carService.list({ active_only: true })
  } catch (e) {
    console.error('Failed to fetch cars:', e)
  } finally {
    loading.value = false
  }
})

const formatPrice = (price: number) => {
  return `Rp ${price.toLocaleString('id-ID')}`
}
</script>

<template>
  <Head title="MaaRentCar - Sewa Mobil Terpercaya" />

  <MainLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-[#362EED] to-[#6366f1] text-white">
      <div class="px-4 py-12 lg:px-8 lg:py-20">
        <div class="max-w-7xl mx-auto lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
          <div class="space-y-6">
            <h1 class="text-3xl lg:text-5xl font-bold leading-tight">
              Sewa Mobil Mudah & Terpercaya
            </h1>
            <p class="text-lg text-white/90 max-w-lg">
              Nikmati perjalanan dengan armada mobil berkualitas. Tersedia dengan atau tanpa driver profesional.
            </p>
            <div class="flex flex-wrap gap-3">
              <Link href="/cars">
                <Button size="lg" class="bg-white text-[#362EED] hover:bg-gray-100">
                  Lihat Semua Mobil
                  <ArrowRight class="ml-2 w-4 h-4" />
                </Button>
              </Link>
            </div>
          </div>
          <div class="hidden lg:block">
            <div class="relative">
              <div class="w-full h-80 bg-white/10 rounded-2xl flex items-center justify-center">
                <CarIcon class="w-48 h-48 text-white/30" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section (Desktop) -->
    <section class="hidden lg:block bg-white py-12 border-b">
      <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-4 gap-8">
          <div v-for="feature in features" :key="feature.title" class="text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-[#362EED]/10 flex items-center justify-center">
              <component :is="feature.icon" class="w-7 h-7 text-[#362EED]" />
            </div>
            <h3 class="font-bold text-[#060521] mb-1">{{ feature.title }}</h3>
            <p class="text-sm text-gray-500">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Category Section -->
    <section class="py-6 lg:py-10 bg-[#F9FAFB]">
      <div class="max-w-7xl mx-auto">
        <h2 class="px-4 lg:px-8 font-bold text-xl text-[#060521] mb-4">Kategori Mobil</h2>
        <div class="flex lg:justify-center gap-3 overflow-x-auto px-4 lg:px-8 scrollbar-hide">
          <Link
            v-for="category in categories"
            :key="category.name"
            :href="category.link"
            class="flex items-center gap-2 whitespace-nowrap rounded-full bg-white px-5 py-3 border border-transparent transition-all hover:border-[#362EED] hover:shadow-md"
          >
            <span class="text-xl">{{ category.icon }}</span>
            <span class="font-semibold">{{ category.name }}</span>
          </Link>
        </div>
      </div>
    </section>

    <!-- Popular Cars Section -->
    <section class="py-6 lg:py-10">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between px-4 lg:px-8 mb-4">
          <h2 class="font-bold text-xl text-[#060521]">Mobil Populer</h2>
          <Link href="/cars" class="text-[#362EED] text-sm font-medium hover:underline">
            Lihat Semua
          </Link>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="px-4 lg:px-8">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="i in 4" :key="i" class="animate-pulse">
              <div class="bg-gray-200 h-40 rounded-xl mb-3"></div>
              <div class="bg-gray-200 h-4 rounded w-3/4 mb-2"></div>
              <div class="bg-gray-200 h-4 rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <!-- Mobile: Horizontal Scroll | Desktop: Grid -->
        <div v-else class="lg:px-8">
          <div class="flex lg:grid lg:grid-cols-4 gap-4 overflow-x-auto lg:overflow-visible px-4 lg:px-0 scrollbar-hide">
            <Link
              v-for="car in popularCars"
              :key="car.id"
              :href="`/cars/${car.id}`"
              class="relative w-[240px] lg:w-auto flex-shrink-0 rounded-xl border border-gray-200 bg-white p-4 transition-all hover:border-[#362EED] hover:shadow-lg"
            >
              <!-- Car Image -->
              <div class="h-32 lg:h-40 mb-3 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                <img
                  v-if="car.thumbnail_url"
                  :src="car.thumbnail_url"
                  :alt="car.name"
                  class="w-full h-full object-cover"
                />
                <CarIcon v-else class="w-16 h-16 text-gray-300" />
              </div>

              <h3 class="font-bold text-[#060521] truncate mb-1">{{ car.name }}</h3>
              <p class="text-sm text-gray-500 mb-2">{{ car.brand }} • {{ car.type }}</p>
              <p class="font-bold text-[#362EED]">{{ formatPrice(car.price_per_day) }}/hari</p>

              <hr class="my-3 border-gray-100" />

              <div class="flex items-center justify-between text-sm text-gray-600">
                <span>{{ car.transmission }}</span>
                <span>{{ car.seats }} Kursi</span>
              </div>
            </Link>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && cars.length === 0" class="text-center py-12 px-4">
          <CarIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
          <p class="text-gray-500">Belum ada mobil tersedia</p>
        </div>
      </div>
    </section>

    <!-- Newest Cars Section (Desktop: Grid) -->
    <section v-if="newestCars.length > 0" class="py-6 lg:py-10 bg-[#F9FAFB]">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between px-4 lg:px-8 mb-4">
          <h2 class="font-bold text-xl text-[#060521]">Semua Mobil</h2>
          <Link href="/cars" class="text-[#362EED] text-sm font-medium hover:underline">
            Lihat Semua
          </Link>
        </div>

        <div class="px-4 lg:px-8">
          <!-- Mobile: Vertical List | Desktop: Grid -->
          <div class="space-y-3 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-4">
            <Link
              v-for="car in newestCars"
              :key="car.id"
              :href="`/cars/${car.id}`"
              class="flex lg:flex-col items-center lg:items-stretch gap-4 lg:gap-0 rounded-xl border border-gray-200 bg-white p-3 lg:p-4 transition-all hover:border-[#362EED] hover:shadow-lg"
            >
              <!-- Image -->
              <div class="w-28 h-24 lg:w-full lg:h-40 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                <img
                  v-if="car.thumbnail_url"
                  :src="car.thumbnail_url"
                  :alt="car.name"
                  class="w-full h-full object-cover"
                />
                <CarIcon v-else class="w-12 h-12 text-gray-300" />
              </div>

              <!-- Info -->
              <div class="flex-1 lg:mt-3">
                <h3 class="font-bold text-[#060521] truncate">{{ car.name }}</h3>
                <p class="text-sm text-gray-500">{{ car.brand }} • {{ car.transmission }}</p>
                <p class="font-bold text-[#362EED] mt-1">{{ formatPrice(car.price_per_day) }}/hari</p>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 lg:py-20 bg-[#060521] text-white">
      <div class="max-w-4xl mx-auto text-center px-4">
        <h2 class="text-2xl lg:text-4xl font-bold mb-4">Siap Untuk Perjalanan?</h2>
        <p class="text-white/80 mb-8 max-w-xl mx-auto">
          Booking sekarang dan nikmati pengalaman berkendara yang nyaman dengan armada terbaik kami.
        </p>
        <Link href="/cars">
          <Button size="lg" class="bg-[#362EED] hover:bg-[#2a24c4]">
            Mulai Booking
            <ArrowRight class="ml-2 w-4 h-4" />
          </Button>
        </Link>
      </div>
    </section>
  </MainLayout>
</template>
