<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Car as CarIcon, Search, Filter, X } from 'lucide-vue-next'
import { ref, watch } from 'vue'
import debounce from 'lodash/debounce'

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

interface PaginatedCars {
  data: Car[]
  links: any[]
  current_page: number
  last_page: number
  total: number
}

const props = defineProps<{
  cars: PaginatedCars
  filters: {
    search?: string
    type?: string
    transmission?: string
    min_price?: number
    max_price?: number
  }
}>()

const typeFilters = ['Semua', 'SUV', 'MPV', 'Sedan', 'Hatchback']
const transmissionFilters = ['Semua', 'Manual', 'Automatic']

const search = ref(props.filters.search || '')
const selectedType = ref(props.filters.type || 'Semua')
const selectedTransmission = ref(props.filters.transmission || 'Semua')

const formatPrice = (price: number) => {
  return `Rp ${price.toLocaleString('id-ID')}`
}

const applyFilters = debounce(() => {
  router.get('/cars', {
    search: search.value || undefined,
    type: selectedType.value !== 'Semua' ? selectedType.value : undefined,
    transmission: selectedTransmission.value !== 'Semua' ? selectedTransmission.value : undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch([search], applyFilters)

const selectType = (type: string) => {
  selectedType.value = type
  applyFilters()
}

const selectTransmission = (transmission: string) => {
  selectedTransmission.value = transmission
  applyFilters()
}

const clearFilters = () => {
  search.value = ''
  selectedType.value = 'Semua'
  selectedTransmission.value = 'Semua'
  router.get('/cars')
}

const hasActiveFilters = () => {
  return search.value || selectedType.value !== 'Semua' || selectedTransmission.value !== 'Semua'
}
</script>

<template>
  <Head title="Daftar Mobil - MaaRentCar" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl lg:text-3xl font-bold text-[#060521] mb-2">Katalog Mobil</h1>
        <p class="text-gray-500">Temukan mobil yang sesuai dengan kebutuhan perjalanan Anda</p>
      </div>

      <!-- Search & Filters -->
      <div class="bg-white rounded-xl border p-4 mb-6 space-y-4">
        <!-- Search -->
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <Input
            v-model="search"
            type="text"
            placeholder="Cari mobil..."
            class="pl-10"
          />
        </div>

        <!-- Type Filter -->
        <div>
          <p class="text-sm font-medium text-gray-700 mb-2">Tipe Mobil</p>
          <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1">
            <button
              v-for="type in typeFilters"
              :key="type"
              @click="selectType(type)"
              :class="[
                'whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition-all',
                selectedType === type
                  ? 'bg-[#362EED] text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
            >
              {{ type }}
            </button>
          </div>
        </div>

        <!-- Transmission Filter -->
        <div>
          <p class="text-sm font-medium text-gray-700 mb-2">Transmisi</p>
          <div class="flex gap-2">
            <button
              v-for="transmission in transmissionFilters"
              :key="transmission"
              @click="selectTransmission(transmission)"
              :class="[
                'whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition-all',
                selectedTransmission === transmission
                  ? 'bg-[#362EED] text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              ]"
            >
              {{ transmission }}
            </button>
          </div>
        </div>

        <!-- Clear Filters -->
        <div v-if="hasActiveFilters()" class="pt-2 border-t">
          <button
            @click="clearFilters"
            class="flex items-center gap-2 text-sm text-red-600 hover:text-red-700"
          >
            <X class="w-4 h-4" />
            Hapus semua filter
          </button>
        </div>
      </div>

      <!-- Results Info -->
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">
          Menampilkan {{ cars.data.length }} dari {{ cars.total }} mobil
        </p>
      </div>

      <!-- Car Grid -->
      <div v-if="cars.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <Link
          v-for="car in cars.data"
          :key="car.id"
          :href="`/cars/${car.id}`"
          class="rounded-xl border border-gray-200 bg-white overflow-hidden transition-all hover:border-[#362EED] hover:shadow-lg"
        >
          <!-- Image -->
          <div class="h-40 lg:h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
            <img
              v-if="car.image_url"
              :src="car.image_url"
              :alt="car.name"
              class="w-full h-full object-cover"
            />
            <CarIcon v-else class="w-16 h-16 text-gray-300" />
          </div>

          <!-- Info -->
          <div class="p-4">
            <div class="flex items-start justify-between mb-2">
              <div>
                <h3 class="font-bold text-[#060521] truncate">{{ car.name }}</h3>
                <p class="text-sm text-gray-500">{{ car.brand }}</p>
              </div>
              <span class="px-2 py-1 rounded-full bg-[#362EED]/10 text-[#362EED] text-xs font-semibold">
                {{ car.type }}
              </span>
            </div>

            <p class="font-bold text-[#362EED] text-lg mb-3">{{ formatPrice(car.price_per_day) }}/hari</p>

            <div class="flex items-center justify-between text-sm text-gray-600 pt-3 border-t">
              <span>{{ car.transmission }}</span>
              <span>{{ car.seats }} Kursi</span>
            </div>

            <div class="mt-3">
              <span
                :class="[
                  'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                  car.is_available
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
                ]"
              >
                <span :class="['w-1.5 h-1.5 rounded-full', car.is_available ? 'bg-green-500' : 'bg-red-500']"></span>
                {{ car.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
              </span>
            </div>
          </div>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <CarIcon class="w-20 h-20 mx-auto text-gray-300 mb-4" />
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada mobil ditemukan</h3>
        <p class="text-gray-500 mb-4">Coba ubah filter pencarian Anda</p>
        <Button @click="clearFilters" variant="outline">
          Hapus Filter
        </Button>
      </div>

      <!-- Pagination -->
      <div v-if="cars.last_page > 1" class="flex justify-center gap-2 mt-8">
        <template v-for="link in cars.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all',
              link.active
                ? 'bg-[#362EED] text-white'
                : 'bg-white border hover:border-[#362EED]'
            ]"
            v-html="link.label"
          />
          <span
            v-else
            class="px-4 py-2 text-sm text-gray-400"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </MainLayout>
</template>
