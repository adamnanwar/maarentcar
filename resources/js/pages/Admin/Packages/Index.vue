<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { MapPin, Plus, Search, Edit, Trash2, Eye, Clock } from 'lucide-vue-next'
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
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

const applyFilters = debounce(() => {
  router.get('/admin/packages', {
    search: search.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch([search], applyFilters)

const deletePackage = (pkg: TourPackage) => {
  if (confirm(`Apakah Anda yakin ingin menonaktifkan paket "${pkg.name}"?`)) {
    router.delete(`/admin/packages/${pkg.id}`)
  }
}
</script>

<template>
  <Head title="Kelola Paket Wisata - Admin" />

  <MainLayout>
    <div class="p-6 lg:p-8">
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-[#060521]">Kelola Paket Wisata</h1>
          <p class="text-gray-500">{{ packages.total }} paket terdaftar</p>
        </div>
        <Link href="/admin/packages/create">
          <Button class="bg-[#362EED] hover:bg-[#2a24c4]">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Paket
          </Button>
        </Link>
      </div>

      <!-- Search -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <Input
              v-model="search"
              type="text"
              placeholder="Cari paket wisata..."
              class="pl-10"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Packages Grid -->
      <div v-if="packages.data.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="pkg in packages.data" :key="pkg.id" class="overflow-hidden">
          <!-- Image -->
          <div class="h-40 bg-gradient-to-br from-[#362EED]/20 to-[#6366f1]/20 flex items-center justify-center overflow-hidden relative">
            <img
              v-if="pkg.thumbnail_url"
              :src="pkg.thumbnail_url"
              :alt="pkg.name"
              class="w-full h-full object-cover"
            />
            <MapPin v-else class="w-16 h-16 text-[#362EED]/30" />

            <!-- Status Badge -->
            <div class="absolute top-3 right-3">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  pkg.is_active
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
                ]"
              >
                {{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </div>
          </div>

          <CardContent class="p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
              <h3 class="font-bold text-[#060521] line-clamp-1">{{ pkg.name }}</h3>
              <span class="flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-xs font-medium text-gray-600 whitespace-nowrap">
                <Clock class="w-3 h-3" />
                {{ pkg.duration_days }} hari
              </span>
            </div>

            <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ pkg.description }}</p>

            <div class="flex items-center justify-between pt-3 border-t">
              <span class="font-bold text-[#362EED]">{{ formatPrice(pkg.price) }}</span>
              <div class="flex items-center gap-1">
                <Link :href="`/packages/${pkg.id}`" target="_blank">
                  <Button variant="ghost" size="sm">
                    <Eye class="w-4 h-4" />
                  </Button>
                </Link>
                <Link :href="`/admin/packages/${pkg.id}/edit`">
                  <Button variant="ghost" size="sm">
                    <Edit class="w-4 h-4" />
                  </Button>
                </Link>
                <Button
                  variant="ghost"
                  size="sm"
                  @click="deletePackage(pkg)"
                  class="text-red-600 hover:text-red-700 hover:bg-red-50"
                >
                  <Trash2 class="w-4 h-4" />
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <Card v-else>
        <CardContent class="py-12 text-center">
          <MapPin class="w-16 h-16 mx-auto text-gray-300 mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada paket ditemukan</h3>
          <p class="text-gray-500 mb-4">Mulai dengan menambahkan paket wisata baru</p>
          <Link href="/admin/packages/create">
            <Button class="bg-[#362EED] hover:bg-[#2a24c4]">
              <Plus class="w-4 h-4 mr-2" />
              Tambah Paket
            </Button>
          </Link>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="packages.last_page > 1" class="flex justify-center gap-2 mt-6">
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
        </template>
      </div>
    </div>
  </MainLayout>
</template>
