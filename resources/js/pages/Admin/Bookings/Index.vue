<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Calendar, Search, Car as CarIcon, User, Clock, CheckCircle, XCircle, CreditCard } from 'lucide-vue-next'
import { ref, watch } from 'vue'
import debounce from 'lodash/debounce'

interface Booking {
  id: string
  order_id: string
  status: string
  payment_status: string
  start_date: string
  end_date: string
  total_price: number
  created_at: string
  user: {
    name: string
    email: string
  }
  car: {
    name: string
    brand: string
  }
}

interface PaginatedBookings {
  data: Booking[]
  links: any[]
  current_page: number
  last_page: number
  total: number
}

const props = defineProps<{
  bookings: PaginatedBookings
  filters: {
    search?: string
    status?: string
  }
}>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'PENDING_VERIFICATION', label: 'Menunggu Verifikasi' },
  { value: 'PENDING_PAYMENT', label: 'Menunggu Pembayaran' },
  { value: 'PAID', label: 'Sudah Dibayar' },
  { value: 'COMPLETED', label: 'Selesai' },
  { value: 'CANCELLED', label: 'Dibatalkan' },
  { value: 'REJECTED', label: 'Ditolak' },
]

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const getStatusConfig = (bookingStatus: string) => {
  const configs: Record<string, { label: string; class: string; icon: any }> = {
    PENDING_VERIFICATION: { label: 'Verifikasi', class: 'bg-amber-100 text-amber-700', icon: Clock },
    PENDING_PAYMENT: { label: 'Pembayaran', class: 'bg-blue-100 text-blue-700', icon: CreditCard },
    PAID: { label: 'Dibayar', class: 'bg-green-100 text-green-700', icon: CheckCircle },
    COMPLETED: { label: 'Selesai', class: 'bg-gray-100 text-gray-700', icon: CheckCircle },
    CANCELLED: { label: 'Batal', class: 'bg-red-100 text-red-700', icon: XCircle },
    REJECTED: { label: 'Ditolak', class: 'bg-red-100 text-red-700', icon: XCircle },
  }
  return configs[bookingStatus] || configs.PENDING_VERIFICATION
}

const applyFilters = debounce(() => {
  router.get('/admin/bookings', {
    search: search.value || undefined,
    status: status.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

watch([search], applyFilters)

const onStatusChange = () => {
  applyFilters()
}
</script>

<template>
  <Head title="Semua Booking - Admin" />

  <MainLayout>
    <div class="p-6 lg:p-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#060521]">Semua Booking</h1>
        <p class="text-gray-500">{{ bookings.total }} total booking</p>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <Input
                v-model="search"
                type="text"
                placeholder="Cari order ID atau nama customer..."
                class="pl-10"
              />
            </div>
            <div class="w-full md:w-48">
              <select
                v-model="status"
                @change="onStatusChange"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#362EED]"
              >
                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Bookings Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Order ID</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Customer</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Mobil</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Tanggal</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Total</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="booking in bookings.data" :key="booking.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <p class="font-medium text-[#060521]">{{ booking.order_id }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(booking.created_at) }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                        <User class="w-4 h-4 text-gray-400" />
                      </div>
                      <div>
                        <p class="font-medium text-sm">{{ booking.user.name }}</p>
                        <p class="text-xs text-gray-500">{{ booking.user.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <p class="text-sm">{{ booking.car.name }}</p>
                    <p class="text-xs text-gray-500">{{ booking.car.brand }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <p class="text-sm">{{ formatDate(booking.start_date) }}</p>
                    <p class="text-xs text-gray-500">s/d {{ formatDate(booking.end_date) }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <p class="font-semibold text-[#362EED]">{{ formatPrice(booking.total_price) }}</p>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="['inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium', getStatusConfig(booking.status).class]">
                      <component :is="getStatusConfig(booking.status).icon" class="w-3 h-3" />
                      {{ getStatusConfig(booking.status).label }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <Link
                      v-if="booking.status === 'PENDING_VERIFICATION'"
                      :href="`/admin/validations/${booking.id}`"
                    >
                      <Button size="sm" class="bg-[#362EED] hover:bg-[#2a24c4]">
                        Verifikasi
                      </Button>
                    </Link>
                    <Link
                      v-else
                      :href="`/bookings/${booking.id}`"
                      target="_blank"
                    >
                      <Button size="sm" variant="outline">
                        Detail
                      </Button>
                    </Link>
                  </td>
                </tr>
                <tr v-if="bookings.data.length === 0">
                  <td colspan="7" class="px-4 py-12 text-center">
                    <Calendar class="w-12 h-12 mx-auto text-gray-300 mb-2" />
                    <p class="text-gray-500">Tidak ada booking ditemukan</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="bookings.last_page > 1" class="flex justify-center gap-2 mt-6">
        <template v-for="link in bookings.links" :key="link.label">
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
