<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { MapPin, Search, Calendar, ArrowRight, Clock } from 'lucide-vue-next'
import { ref, watch } from 'vue'
import debounce from 'lodash/debounce'

interface TourPackage {
  id: string
  name: string
  price: number
  duration_days: number
  description: string
  thumbnail_url: string | null
  is_active: boolean
}

interface PaginatedPackages {
  data: TourPackage[]
  links: any[]
  current_page: number
  last_page: number
  total: number
}

const props = defineProps<{
  packages: PaginatedPackages
  filters: {
    search?: string
  }
}>()

const search = ref(props.filters.search || '')

const formatPrice = (price: number) => {
  return `Rp ${price.toLocaleString('id-ID')}`
}

const applyFilters = debounce(() => {
  router.get('/packages', {
    search: search.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch([search], applyFilters)
</script>

<template>
  <Head title="Paket Wisata - MaaRentCar" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl lg:text-3xl font-bold text-[#060521] mb-2">Paket Wisata</h1>
        <p class="text-gray-500">Jelajahi destinasi menarik dengan paket wisata lengkap</p>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-xl border p-4 mb-6">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
          <Input
            v-model="search"
            type="text"
            placeholder="Cari paket wisata..."
            class="pl-10"
          />
        </div>
      </div>

      <!-- Results Info -->
      <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">
          Menampilkan {{ packages.data.length }} dari {{ packages.total }} paket
        </p>
      </div>

      <!-- Package Grid -->
      <div v-if="packages.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="pkg in packages.data"
          :key="pkg.id"
          :href="`/packages/${pkg.id}`"
          class="group rounded-xl border border-gray-200 bg-white overflow-hidden transition-all hover:border-[#362EED] hover:shadow-lg"
        >
          <!-- Image -->
          <div class="h-48 lg:h-56 bg-gradient-to-br from-[#362EED]/20 to-[#6366f1]/20 flex items-center justify-center overflow-hidden relative">
            <img
              v-if="pkg.thumbnail_url"
              :src="pkg.thumbnail_url"
              :alt="pkg.name"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            />
            <MapPin v-else class="w-20 h-20 text-[#362EED]/30" />

            <!-- Duration Badge -->
            <div class="absolute top-4 left-4">
              <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-white/90 backdrop-blur-sm text-sm font-semibold shadow">
                <Clock class="w-4 h-4 text-[#362EED]" />
                {{ pkg.duration_days }} Hari
              </span>
            </div>
          </div>

          <!-- Info -->
          <div class="p-5">
            <h3 class="font-bold text-lg text-[#060521] mb-2 group-hover:text-[#362EED] transition-colors">
              {{ pkg.name }}
            </h3>
            <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ pkg.description }}</p>

            <div class="flex items-center justify-between pt-4 border-t">
              <div>
                <p class="text-xs text-gray-400">Mulai dari</p>
                <p class="font-bold text-xl text-[#362EED]">{{ formatPrice(pkg.price) }}</p>
              </div>
              <Button size="sm" variant="outline" class="group-hover:bg-[#362EED] group-hover:text-white group-hover:border-[#362EED]">
                Lihat Detail
                <ArrowRight class="w-4 h-4 ml-1" />
              </Button>
            </div>
          </div>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <MapPin class="w-20 h-20 mx-auto text-gray-300 mb-4" />
        <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada paket ditemukan</h3>
        <p class="text-gray-500 mb-4">Coba ubah kata kunci pencarian Anda</p>
        <Button @click="search = ''" variant="outline">
          Reset Pencarian
        </Button>
      </div>

      <!-- Pagination -->
      <div v-if="packages.last_page > 1" class="flex justify-center gap-2 mt-8">
        <template v-for="link in packages.links" :key="link.label">
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
