<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { bookingService } from '@/services/bookingService'
import { carService } from '@/services/carService'
import type { Booking, Car } from '@/types/models'
import {
  Car as CarIcon,
  Calendar,
  CreditCard,
  TrendingUp,
  ArrowRight,
  Loader2,
} from 'lucide-vue-next'

const bookings = ref<Booking[]>([])
const cars = ref<Car[]>([])
const loading = ref(true)

const stats = computed(() => {
  const totalRevenue = bookings.value
    .filter((b) => b.status === 'PAID' || b.status === 'IN_PROGRESS' || b.status === 'COMPLETED')
    .reduce((sum, b) => sum + b.total_price, 0)

  return {
    totalCars: cars.value.length,
    activeCars: cars.value.filter((c) => c.is_active).length,
    totalBookings: bookings.value.length,
    pendingBookings: bookings.value.filter((b) => b.status === 'PENDING_PAYMENT').length,
    activeBookings: bookings.value.filter((b) => b.status === 'IN_PROGRESS').length,
    totalRevenue,
  }
})

const recentBookings = computed(() => bookings.value.slice(0, 5))

onMounted(async () => {
  try {
    const [bookingsData, carsData] = await Promise.all([
      bookingService.listAll(),
      carService.list({ active_only: false }),
    ])
    bookings.value = bookingsData
    cars.value = carsData
  } catch (e) {
    console.error('Failed to fetch data:', e)
  } finally {
    loading.value = false
  }
})

function formatPrice(price: number) {
  return `Rp ${price.toLocaleString('id-ID')}`
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const statusColors: Record<string, string> = {
  PENDING_PAYMENT: 'bg-yellow-100 text-yellow-800',
  PAID: 'bg-blue-100 text-blue-800',
  IN_PROGRESS: 'bg-purple-100 text-purple-800',
  COMPLETED: 'bg-green-100 text-green-800',
  CANCELLED: 'bg-red-100 text-red-800',
  EXPIRED: 'bg-gray-100 text-gray-800',
}
</script>

<template>
  <Head title="Admin Dashboard" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <h1 class="text-2xl lg:text-3xl font-bold text-[#060521] mb-8">Admin Dashboard</h1>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-20">
        <Loader2 class="w-10 h-10 mx-auto animate-spin text-[#362EED]" />
        <p class="mt-4 text-gray-500">Memuat data...</p>
      </div>

      <template v-else>
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <Card>
            <CardContent class="pt-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Total Mobil</p>
                  <p class="text-2xl font-bold text-[#060521]">{{ stats.totalCars }}</p>
                  <p class="text-xs text-green-600">{{ stats.activeCars }} aktif</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-[#362EED]/10 flex items-center justify-center">
                  <CarIcon class="w-6 h-6 text-[#362EED]" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="pt-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Total Booking</p>
                  <p class="text-2xl font-bold text-[#060521]">{{ stats.totalBookings }}</p>
                  <p class="text-xs text-yellow-600">{{ stats.pendingBookings }} pending</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-[#362EED]/10 flex items-center justify-center">
                  <Calendar class="w-6 h-6 text-[#362EED]" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="pt-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Sedang Berjalan</p>
                  <p class="text-2xl font-bold text-[#060521]">{{ stats.activeBookings }}</p>
                  <p class="text-xs text-purple-600">booking aktif</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-[#362EED]/10 flex items-center justify-center">
                  <CarIcon class="w-6 h-6 text-[#362EED]" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="pt-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-500">Total Pendapatan</p>
                  <p class="text-xl font-bold text-[#060521]">{{ formatPrice(stats.totalRevenue) }}</p>
                  <p class="text-xs text-green-600">dari booking berhasil</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-[#362EED]/10 flex items-center justify-center">
                  <TrendingUp class="w-6 h-6 text-[#362EED]" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
          <Link href="/admin/cars">
            <Card class="hover:border-[#362EED] transition-colors cursor-pointer">
              <CardContent class="pt-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#362EED] flex items-center justify-center">
                      <CarIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <h3 class="font-bold text-[#060521]">Kelola Mobil</h3>
                      <p class="text-sm text-gray-500">Tambah, edit, atau hapus mobil</p>
                    </div>
                  </div>
                  <ArrowRight class="w-5 h-5 text-gray-400" />
                </div>
              </CardContent>
            </Card>
          </Link>

          <Link href="/admin/bookings">
            <Card class="hover:border-[#362EED] transition-colors cursor-pointer">
              <CardContent class="pt-6">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#362EED] flex items-center justify-center">
                      <Calendar class="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <h3 class="font-bold text-[#060521]">Kelola Booking</h3>
                      <p class="text-sm text-gray-500">Lihat dan update status booking</p>
                    </div>
                  </div>
                  <ArrowRight class="w-5 h-5 text-gray-400" />
                </div>
              </CardContent>
            </Card>
          </Link>
        </div>

        <!-- Recent Bookings -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Booking Terbaru</CardTitle>
              <Link href="/admin/bookings">
                <Button variant="ghost" size="sm">
                  Lihat Semua
                  <ArrowRight class="w-4 h-4 ml-2" />
                </Button>
              </Link>
            </div>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b text-left">
                    <th class="pb-3 font-medium text-gray-500">Order ID</th>
                    <th class="pb-3 font-medium text-gray-500">Customer</th>
                    <th class="pb-3 font-medium text-gray-500">Mobil</th>
                    <th class="pb-3 font-medium text-gray-500">Tanggal</th>
                    <th class="pb-3 font-medium text-gray-500">Status</th>
                    <th class="pb-3 font-medium text-gray-500 text-right">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="booking in recentBookings" :key="booking.id" class="border-b last:border-0">
                    <td class="py-3 font-medium">{{ booking.order_id }}</td>
                    <td class="py-3">{{ booking.user?.name || '-' }}</td>
                    <td class="py-3">{{ booking.car?.name || '-' }}</td>
                    <td class="py-3">{{ formatDate(booking.start_date) }}</td>
                    <td class="py-3">
                      <Badge :class="statusColors[booking.status]">
                        {{ booking.status.replace('_', ' ') }}
                      </Badge>
                    </td>
                    <td class="py-3 text-right font-medium">{{ formatPrice(booking.total_price) }}</td>
                  </tr>
                  <tr v-if="recentBookings.length === 0">
                    <td colspan="6" class="py-8 text-center text-gray-500">Belum ada booking</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </template>
    </div>
  </MainLayout>
</template>
