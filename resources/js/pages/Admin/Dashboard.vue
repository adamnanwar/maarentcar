<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
  Car as CarIcon,
  Users,
  Calendar,
  CreditCard,
  TrendingUp,
  Clock,
  CheckCircle,
  MapPin,
  ArrowRight
} from 'lucide-vue-next'

interface RecentBooking {
  id: string
  order_id: string
  status: string
  total_price: number
  created_at: string
  user: {
    name: string
  }
  car: {
    name: string
  }
}

const props = defineProps<{
  stats: {
    total_bookings: number
    pending_verification: number
    pending_payment: number
    total_revenue: number
    total_cars: number
    active_cars: number
    total_packages: number
    active_packages: number
    total_customers: number
  }
  recentBookings: RecentBooking[]
}>()

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStatusBadge = (status: string) => {
  const badges: Record<string, { label: string; class: string }> = {
    PENDING_VERIFICATION: { label: 'Verifikasi', class: 'bg-amber-100 text-amber-700' },
    PENDING_PAYMENT: { label: 'Pembayaran', class: 'bg-blue-100 text-blue-700' },
    PAID: { label: 'Dibayar', class: 'bg-green-100 text-green-700' },
    COMPLETED: { label: 'Selesai', class: 'bg-gray-100 text-gray-700' },
    CANCELLED: { label: 'Batal', class: 'bg-red-100 text-red-700' },
    REJECTED: { label: 'Ditolak', class: 'bg-red-100 text-red-700' },
  }
  return badges[status] || badges.PENDING_VERIFICATION
}
</script>

<template>
  <Head title="Admin Dashboard - MaaRentCar" />

  <MainLayout>
    <div class="p-6 lg:p-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-[#060521]">Dashboard Admin</h1>
        <p class="text-gray-500">Selamat datang di panel administrasi MaaRentCar</p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-[#362EED]/10 flex items-center justify-center">
                <Calendar class="w-5 h-5 text-[#362EED]" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.total_bookings }}</p>
                <p class="text-xs text-gray-500">Total Booking</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-amber-200 bg-amber-50">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                <Clock class="w-5 h-5 text-amber-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-amber-700">{{ stats.pending_verification }}</p>
                <p class="text-xs text-amber-600">Perlu Verifikasi</p>
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
                <p class="text-xs text-gray-500">Menunggu Bayar</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <TrendingUp class="w-5 h-5 text-green-600" />
              </div>
              <div>
                <p class="text-lg font-bold text-[#060521]">{{ formatPrice(stats.total_revenue) }}</p>
                <p class="text-xs text-gray-500">Total Revenue</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                <CarIcon class="w-5 h-5 text-purple-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.total_cars }}</p>
                <p class="text-xs text-gray-500">Total Mobil</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-pink-100 flex items-center justify-center">
                <MapPin class="w-5 h-5 text-pink-600" />
              </div>
              <div>
                <p class="text-2xl font-bold text-[#060521]">{{ stats.total_packages }}</p>
                <p class="text-xs text-gray-500">Paket Wisata</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions & Recent Bookings -->
      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <Card>
          <CardHeader>
            <CardTitle>Aksi Cepat</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Link href="/admin/validations" class="block">
              <Button variant="outline" class="w-full justify-between">
                <span class="flex items-center gap-2">
                  <Clock class="w-4 h-4" />
                  Verifikasi Dokumen
                </span>
                <span v-if="stats.pending_verification > 0" class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                  {{ stats.pending_verification }}
                </span>
              </Button>
            </Link>
            <Link href="/admin/cars" class="block">
              <Button variant="outline" class="w-full justify-start gap-2">
                <CarIcon class="w-4 h-4" />
                Kelola Mobil
              </Button>
            </Link>
            <Link href="/admin/packages" class="block">
              <Button variant="outline" class="w-full justify-start gap-2">
                <MapPin class="w-4 h-4" />
                Kelola Paket Wisata
              </Button>
            </Link>
            <Link href="/admin/bookings" class="block">
              <Button variant="outline" class="w-full justify-start gap-2">
                <Calendar class="w-4 h-4" />
                Semua Booking
              </Button>
            </Link>
          </CardContent>
        </Card>

        <!-- Recent Bookings -->
        <Card class="lg:col-span-2">
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle>Booking Terbaru</CardTitle>
            <Link href="/admin/bookings">
              <Button variant="ghost" size="sm">
                Lihat Semua
                <ArrowRight class="w-4 h-4 ml-1" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent>
            <div v-if="recentBookings.length > 0" class="space-y-3">
              <div
                v-for="booking in recentBookings"
                :key="booking.id"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-gray-50"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <CarIcon class="w-5 h-5 text-gray-400" />
                  </div>
                  <div>
                    <p class="font-medium text-sm">{{ booking.order_id }}</p>
                    <p class="text-xs text-gray-500">{{ booking.user.name }} • {{ booking.car.name }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusBadge(booking.status).class]">
                    {{ getStatusBadge(booking.status).label }}
                  </span>
                  <p class="text-xs text-gray-500 mt-1">{{ formatDateTime(booking.created_at) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              <Calendar class="w-12 h-12 mx-auto text-gray-300 mb-2" />
              <p>Belum ada booking</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </MainLayout>
</template>
