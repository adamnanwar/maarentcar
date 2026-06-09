<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Car as CarIcon,
  Calendar,
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle,
  CreditCard,
  ArrowRight,
  FileText
} from 'lucide-vue-next'

interface Booking {
  id: string
  order_id: string
  status: string
  payment_status: string
  start_date: string
  end_date: string
  total_price: number
  created_at: string
  car: {
    id: string
    name: string
    brand: string
    image_url: string | null
  }
  tour_package?: {
    id: string
    name: string
  } | null
}

const props = defineProps<{
  bookings: Booking[]
  stats: {
    total: number
    pending_verification: number
    pending_payment: number
    paid: number
    completed: number
  }
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

const getStatusConfig = (status: string) => {
  const configs: Record<string, { label: string; color: string; bgColor: string; icon: any }> = {
    PENDING_VERIFICATION: {
      label: 'Menunggu Verifikasi',
      color: 'text-amber-700',
      bgColor: 'bg-amber-100',
      icon: Clock,
    },
    PENDING_PAYMENT: {
      label: 'Menunggu Pembayaran',
      color: 'text-blue-700',
      bgColor: 'bg-blue-100',
      icon: CreditCard,
    },
    PAID: {
      label: 'Sudah Dibayar',
      color: 'text-green-700',
      bgColor: 'bg-green-100',
      icon: CheckCircle,
    },
    COMPLETED: {
      label: 'Selesai',
      color: 'text-gray-700',
      bgColor: 'bg-gray-100',
      icon: CheckCircle,
    },
    CANCELLED: {
      label: 'Dibatalkan',
      color: 'text-red-700',
      bgColor: 'bg-red-100',
      icon: XCircle,
    },
    REJECTED: {
      label: 'Ditolak',
      color: 'text-red-700',
      bgColor: 'bg-red-100',
      icon: XCircle,
    },
  }
  return configs[status] || configs.PENDING_VERIFICATION
}
</script>

<template>
  <Head title="Dashboard - MaaRentCar" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-[#060521] mb-2">Dashboard</h1>
        <p class="text-gray-500">Kelola booking dan pantau status pesanan Anda</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-[#362EED]/10 flex items-center justify-center">
                <FileText class="w-5 h-5 text-[#362EED]" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.total }}</p>
                <p class="text-xs text-gray-500">Total Booking</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                <Clock class="w-5 h-5 text-amber-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.pending_verification }}</p>
                <p class="text-xs text-gray-500">Verifikasi</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <CreditCard class="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.pending_payment }}</p>
                <p class="text-xs text-gray-500">Pembayaran</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <CheckCircle class="w-5 h-5 text-green-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.paid }}</p>
                <p class="text-xs text-gray-500">Dibayar</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                <CheckCircle class="w-5 h-5 text-gray-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.completed }}</p>
                <p class="text-xs text-gray-500">Selesai</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Bookings List -->
      <Card>
        <CardHeader class="flex flex-row items-center justify-between">
          <CardTitle>Booking Saya</CardTitle>
          <Link href="/cars">
            <Button size="sm" class="bg-[#362EED] hover:bg-[#2a24c4]">
              <CarIcon class="w-4 h-4 mr-2" />
              Booking Baru
            </Button>
          </Link>
        </CardHeader>
        <CardContent>
          <div v-if="bookings.length > 0" class="space-y-4">
            <Link
              v-for="booking in bookings"
              :key="booking.id"
              :href="`/bookings/${booking.id}`"
              class="block p-4 rounded-xl border hover:border-[#362EED] hover:shadow-md transition-all"
            >
              <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <!-- Car Image -->
                <div class="w-full lg:w-32 h-24 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                  <img
                    v-if="booking.car.image_url"
                    :src="booking.car.image_url"
                    :alt="booking.car.name"
                    class="w-full h-full object-cover"
                  />
                  <CarIcon v-else class="w-10 h-10 text-gray-300" />
                </div>

                <!-- Booking Info -->
                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div>
                      <p class="text-xs text-gray-500 mb-1">{{ booking.order_id }}</p>
                      <h3 class="font-bold text-[#060521]">{{ booking.car.name }}</h3>
                      <p class="text-sm text-gray-500">{{ booking.car.brand }}</p>
                    </div>
                    <span
                      :class="[
                        'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium',
                        getStatusConfig(booking.status).bgColor,
                        getStatusConfig(booking.status).color
                      ]"
                    >
                      <component :is="getStatusConfig(booking.status).icon" class="w-3 h-3" />
                      {{ getStatusConfig(booking.status).label }}
                    </span>
                  </div>

                  <div v-if="booking.tour_package" class="mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-[#362EED]/10 text-[#362EED] text-xs font-medium">
                      + {{ booking.tour_package.name }}
                    </span>
                  </div>

                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                      <Calendar class="w-4 h-4" />
                      {{ formatDate(booking.start_date) }} - {{ formatDate(booking.end_date) }}
                    </span>
                    <span class="font-semibold text-[#362EED]">
                      {{ formatPrice(booking.total_price) }}
                    </span>
                  </div>
                </div>

                <!-- Arrow -->
                <div class="hidden lg:flex items-center">
                  <ArrowRight class="w-5 h-5 text-gray-400" />
                </div>
              </div>
            </Link>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <CarIcon class="w-16 h-16 mx-auto text-gray-300 mb-4" />
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada booking</h3>
            <p class="text-gray-500 mb-4">Mulai perjalanan Anda dengan menyewa mobil</p>
            <Link href="/cars">
              <Button class="bg-[#362EED] hover:bg-[#2a24c4]">
                <CarIcon class="w-4 h-4 mr-2" />
                Lihat Mobil
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
