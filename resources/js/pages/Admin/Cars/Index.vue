<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Car as CarIcon, Plus, Search, Edit, Trash2, Eye } from 'lucide-vue-next'
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
  router.get('/admin/cars', {
    search: search.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch([search], applyFilters)

const deleteCar = (car: Car) => {
  if (confirm(`Apakah Anda yakin ingin menonaktifkan mobil "${car.name}"?`)) {
    router.delete(`/admin/cars/${car.id}`)
  }
}
</script>

<template>
  <Head title="Kelola Mobil - Admin" />

  <MainLayout>
    <div class="p-6 lg:p-8">
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-[#060521]">Kelola Mobil</h1>
          <p class="text-gray-500">{{ cars.total }} mobil terdaftar</p>
        </div>
        <Link href="/admin/cars/create">
          <Button class="bg-[#362EED] hover:bg-[#2a24c4]">
            <Plus class="w-4 h-4 mr-2" />
            Tambah Mobil
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
              placeholder="Cari mobil..."
              class="pl-10"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Cars Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Mobil</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tipe</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Transmisi</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Harga/Hari</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="car in cars.data" :key="car.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-16 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                        <img
                          v-if="car.image_url"
                          :src="car.image_url"
                          :alt="car.name"
                          class="w-full h-full object-cover"
                        />
                        <CarIcon v-else class="w-6 h-6 text-gray-300" />
                      </div>
                      <div>
                        <p class="font-medium text-[#060521]">{{ car.name }}</p>
                        <p class="text-sm text-gray-500">{{ car.brand }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                      {{ car.type }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ car.transmission }}</td>
                  <td class="px-4 py-3 text-sm font-semibold text-[#362EED]">
                    {{ formatPrice(car.price_per_day) }}
                  </td>
                  <td class="px-4 py-3">
                    <span
                      :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        car.is_available
                          ? 'bg-green-100 text-green-700'
                          : 'bg-red-100 text-red-700'
                      ]"
                    >
                      {{ car.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                      <Link :href="`/cars/${car.id}`" target="_blank">
                        <Button variant="ghost" size="sm">
                          <Eye class="w-4 h-4" />
                        </Button>
                      </Link>
                      <Link :href="`/admin/cars/${car.id}/edit`">
                        <Button variant="ghost" size="sm">
                          <Edit class="w-4 h-4" />
                        </Button>
                      </Link>
                      <Button
                        variant="ghost"
                        size="sm"
                        @click="deleteCar(car)"
                        class="text-red-600 hover:text-red-700 hover:bg-red-50"
                      >
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="cars.data.length === 0">
                  <td colspan="6" class="px-4 py-12 text-center">
                    <CarIcon class="w-12 h-12 mx-auto text-gray-300 mb-2" />
                    <p class="text-gray-500">Tidak ada mobil ditemukan</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="cars.last_page > 1" class="flex justify-center gap-2 mt-6">
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
        </template>
      </div>
    </div>
  </MainLayout>
</template>
