<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { ArrowRight, Car as CarIcon, Users, Shield, Clock, MapPin, Calendar } from 'lucide-vue-next'

interface Car {
  id: string
  name: string
  brand: string
  type: string
  transmission: string
  seats: number
  price_per_day: number
  image_url: string | null
  is_available: boolean
}

interface TourPackage {
  id: string
  name: string
  price: number
  duration_days: number
  description: string
  thumbnail_url: string | null
  is_active: boolean
}

const props = defineProps<{
  featuredCars: Car[]
  featuredPackages: TourPackage[]
}>()

const features = [
  { icon: CarIcon, title: 'Armada Lengkap', desc: 'Pilihan mobil dari berbagai merek dan tipe' },
  { icon: Shield, title: 'Terjamin Aman', desc: 'Semua mobil terawat dan berasuransi' },
  { icon: Users, title: 'Driver Berpengalaman', desc: 'Tersedia layanan dengan driver profesional' },
  { icon: Clock, title: 'Proses Cepat', desc: 'Booking mudah dan konfirmasi instan' },
]

const categories = [
  { name: 'SUV', link: '/cars?type=SUV', icon: '🚙' },
  { name: 'Sedan', link: '/cars?type=Sedan', icon: '🚗' },
  { name: 'MPV', link: '/cars?type=MPV', icon: '🚐' },
  { name: 'Hatchback', link: '/cars?type=Hatchback', icon: '🚘' },
]

const formatPrice = (price: number) => {
  return `Rp ${price.toLocaleString('id-ID')}`
}
</script>

<template>
  <Head title="MaaRentCar - Sewa Mobil & Paket Wisata" />

  <MainLayout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-[#362EED] to-[#6366f1] text-white">
      <div class="px-4 py-12 lg:px-8 lg:py-20">
        <div class="max-w-7xl mx-auto lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
          <div class="space-y-6">
            <h1 class="text-3xl lg:text-5xl font-bold leading-tight">
              Sewa Mobil & Paket Wisata Terpercaya
            </h1>
            <p class="text-lg text-white/90 max-w-lg">
              Nikmati perjalanan dengan armada mobil berkualitas. Tersedia rental mobil dan paket wisata menarik dengan driver profesional.
            </p>
            <div class="flex flex-wrap gap-3">
              <Link href="/cars">
                <Button size="lg" class="bg-white text-[#362EED] hover:bg-gray-100">
                  <CarIcon class="mr-2 w-4 h-4" />
                  Lihat Mobil
                </Button>
              </Link>
              <Link href="/packages">
                <Button size="lg" variant="outline" class="border-white text-white hover:bg-white/10">
                  <MapPin class="mr-2 w-4 h-4" />
                  Paket Wisata
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

    <!-- Features Section -->
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

    <!-- Featured Cars Section -->
    <section class="py-6 lg:py-10">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between px-4 lg:px-8 mb-4">
          <h2 class="font-bold text-xl text-[#060521]">Mobil Populer</h2>
          <Link href="/cars" class="text-[#362EED] text-sm font-medium hover:underline flex items-center gap-1">
            Lihat Semua
            <ArrowRight class="w-4 h-4" />
          </Link>
        </div>

        <div v-if="featuredCars.length > 0" class="lg:px-8">
          <div class="flex lg:grid lg:grid-cols-4 gap-4 overflow-x-auto lg:overflow-visible px-4 lg:px-0 scrollbar-hide">
            <Link
              v-for="car in featuredCars"
              :key="car.id"
              :href="`/cars/${car.id}`"
              class="relative w-[240px] lg:w-auto flex-shrink-0 rounded-xl border border-gray-200 bg-white p-4 transition-all hover:border-[#362EED] hover:shadow-lg"
            >
              <div class="h-32 lg:h-40 mb-3 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                <img
                  v-if="car.image_url"
                  :src="car.image_url"
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

        <div v-else class="text-center py-12 px-4">
          <CarIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
          <p class="text-gray-500">Belum ada mobil tersedia</p>
        </div>
      </div>
    </section>

    <!-- Tour Packages Section -->
    <section v-if="featuredPackages.length > 0" class="py-6 lg:py-10 bg-[#F9FAFB]">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between px-4 lg:px-8 mb-4">
          <h2 class="font-bold text-xl text-[#060521]">Paket Wisata Populer</h2>
          <Link href="/packages" class="text-[#362EED] text-sm font-medium hover:underline flex items-center gap-1">
            Lihat Semua
            <ArrowRight class="w-4 h-4" />
          </Link>
        </div>

        <div class="lg:px-8">
          <div class="flex lg:grid lg:grid-cols-3 gap-4 overflow-x-auto lg:overflow-visible px-4 lg:px-0 scrollbar-hide">
            <Link
              v-for="pkg in featuredPackages"
              :key="pkg.id"
              :href="`/packages/${pkg.id}`"
              class="relative w-[280px] lg:w-auto flex-shrink-0 rounded-xl border border-gray-200 bg-white overflow-hidden transition-all hover:border-[#362EED] hover:shadow-lg"
            >
              <div class="h-40 lg:h-48 bg-gradient-to-br from-[#362EED]/20 to-[#6366f1]/20 flex items-center justify-center overflow-hidden">
                <img
                  v-if="pkg.thumbnail_url"
                  :src="pkg.thumbnail_url"
                  :alt="pkg.name"
                  class="w-full h-full object-cover"
                />
                <MapPin v-else class="w-16 h-16 text-[#362EED]/30" />
              </div>

              <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                  <span class="px-2 py-1 rounded-full bg-[#362EED]/10 text-[#362EED] text-xs font-semibold">
                    {{ pkg.duration_days }} Hari
                  </span>
                </div>
                <h3 class="font-bold text-[#060521] mb-2">{{ pkg.name }}</h3>
                <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ pkg.description }}</p>
                <p class="font-bold text-[#362EED]">{{ formatPrice(pkg.price) }}</p>
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
        <div class="flex flex-wrap justify-center gap-4">
          <Link href="/cars">
            <Button size="lg" class="bg-[#362EED] hover:bg-[#2a24c4]">
              <CarIcon class="mr-2 w-4 h-4" />
              Sewa Mobil
            </Button>
          </Link>
          <Link href="/packages">
            <Button size="lg" variant="outline" class="border-white text-white hover:bg-white/10">
              <Calendar class="mr-2 w-4 h-4" />
              Lihat Paket Wisata
            </Button>
          </Link>
        </div>
      </div>
    </section>
  </MainLayout>
</template>
