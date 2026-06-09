<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Clock, Car as CarIcon, User, Calendar, ArrowRight, FileText } from 'lucide-vue-next'

interface Booking {
  id: string
  order_id: string
  status: string
  start_date: string
  end_date: string
  total_price: number
  ktp_image_url: string
  sim_image_url: string | null
  created_at: string
  user: {
    name: string
    email: string
    phone: string
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
}>()

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

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <Head title="Verifikasi Dokumen - Admin" />

  <MainLayout>
    <div class="p-6 lg:p-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#060521] mb-1">Verifikasi Dokumen</h1>
        <p class="text-gray-500">Booking yang menunggu verifikasi dokumen KTP/SIM</p>
      </div>

      <!-- Stats -->
      <Card class="mb-6 border-amber-200 bg-amber-50">
        <CardContent class="p-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
              <Clock class="w-6 h-6 text-amber-600" />
            </div>
            <div>
              <p class="text-3xl font-bold text-amber-700">{{ bookings.total }}</p>
              <p class="text-sm text-amber-600">Booking menunggu verifikasi</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Bookings List -->
      <div v-if="bookings.data.length > 0" class="space-y-4">
        <Card v-for="booking in bookings.data" :key="booking.id">
          <CardContent class="p-4 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
              <!-- Booking Info -->
              <div class="flex-1">
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <p class="text-sm text-gray-500">{{ booking.order_id }}</p>
                    <h3 class="font-bold text-[#060521]">{{ booking.car.name }}</h3>
                    <p class="text-sm text-gray-500">{{ booking.car.brand }}</p>
                  </div>
                  <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
                    Menunggu Verifikasi
                  </span>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                  <div class="flex items-center gap-2">
                    <User class="w-4 h-4 text-gray-400" />
                    <span>{{ booking.user.name }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <Calendar class="w-4 h-4 text-gray-400" />
                    <span>{{ formatDate(booking.start_date) }}</span>
                  </div>
                  <div>
                    <span class="font-semibold text-[#362EED]">{{ formatPrice(booking.total_price) }}</span>
                  </div>
                  <div class="text-gray-500">
                    {{ formatDateTime(booking.created_at) }}
                  </div>
                </div>

                <!-- Document Links -->
                <div class="flex items-center gap-4 mt-3 pt-3 border-t">
                  <a
                    :href="booking.ktp_image_url"
                    target="_blank"
                    class="flex items-center gap-1 text-sm text-[#362EED] hover:underline"
                  >
                    <FileText class="w-4 h-4" />
                    Lihat KTP
                  </a>
                  <a
                    v-if="booking.sim_image_url"
                    :href="booking.sim_image_url"
                    target="_blank"
                    class="flex items-center gap-1 text-sm text-[#362EED] hover:underline"
                  >
                    <FileText class="w-4 h-4" />
                    Lihat SIM
                  </a>
                  <span v-else class="text-sm text-gray-400">Tanpa SIM (dengan driver)</span>
                </div>
              </div>

              <!-- Action -->
              <div class="flex-shrink-0">
                <Link :href="`/admin/validations/${booking.id}`">
                  <Button class="bg-[#362EED] hover:bg-[#2a24c4]">
                    Verifikasi
                    <ArrowRight class="w-4 h-4 ml-2" />
                  </Button>
                </Link>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Empty State -->
      <Card v-else>
        <CardContent class="py-12 text-center">
          <Clock class="w-16 h-16 mx-auto text-gray-300 mb-4" />
          <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada booking yang perlu diverifikasi</h3>
          <p class="text-gray-500">Semua dokumen sudah diverifikasi</p>
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
