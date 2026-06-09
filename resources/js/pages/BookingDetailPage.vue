<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { bookingService } from '@/services/bookingService'
import { useMidtrans } from '@/composables/useMidtrans'
import type { Booking } from '@/types/models'
import {
  Calendar,
  Car,
  MapPin,
  User,
  CreditCard,
  Clock,
  AlertCircle,
  CheckCircle,
  XCircle,
  Loader2,
  ArrowLeft,
} from 'lucide-vue-next'

const props = defineProps<{
  id: string
}>()

const booking = ref<Booking | null>(null)
const loading = ref(true)
const cancelling = ref(false)
const error = ref<string | null>(null)

const { pay, isLoading: paymentLoading } = useMidtrans()

const statusConfig: Record<string, { label: string; color: string; icon: typeof CheckCircle }> = {
  PENDING_PAYMENT: { label: 'Menunggu Pembayaran', color: 'bg-yellow-100 text-yellow-800', icon: Clock },
  PAID: { label: 'Dibayar', color: 'bg-blue-100 text-blue-800', icon: CheckCircle },
  IN_PROGRESS: { label: 'Sedang Berjalan', color: 'bg-purple-100 text-purple-800', icon: Car },
  COMPLETED: { label: 'Selesai', color: 'bg-green-100 text-green-800', icon: CheckCircle },
  CANCELLED: { label: 'Dibatalkan', color: 'bg-red-100 text-red-800', icon: XCircle },
  EXPIRED: { label: 'Kedaluwarsa', color: 'bg-gray-100 text-gray-800', icon: AlertCircle },
}

const currentStatus = computed(() => {
  if (!booking.value) return statusConfig.PENDING_PAYMENT
  return statusConfig[booking.value.status] || statusConfig.PENDING_PAYMENT
})

const canPay = computed(() => booking.value?.status === 'PENDING_PAYMENT')
const canCancel = computed(() => booking.value?.status === 'PENDING_PAYMENT')

onMounted(async () => {
  await fetchBooking()
})

async function fetchBooking() {
  loading.value = true
  error.value = null
  try {
    booking.value = await bookingService.get(props.id)
  } catch (e) {
    error.value = 'Gagal memuat detail booking'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function handlePayment() {
  if (!booking.value) return

  try {
    // Get fresh booking with snap token
    const result = await bookingService.create({
      car_id: booking.value.car_id,
      start_date: booking.value.start_date,
      end_date: booking.value.end_date,
      pickup_location: booking.value.pickup_location,
      use_driver: booking.value.use_driver,
      notes: booking.value.notes,
    })

    const paymentResult = await pay(result.snap_token)

    if (paymentResult === 'success' || paymentResult === 'pending') {
      // Refresh booking to get updated status
      await fetchBooking()
    }
  } catch (e) {
    console.error('Payment error:', e)
  }
}

async function handleCancel() {
  if (!booking.value || !confirm('Apakah Anda yakin ingin membatalkan booking ini?')) return

  cancelling.value = true
  try {
    booking.value = await bookingService.cancel(booking.value.id)
  } catch (e) {
    error.value = 'Gagal membatalkan booking'
    console.error(e)
  } finally {
    cancelling.value = false
  }
}

function formatPrice(price: number) {
  return `Rp ${price.toLocaleString('id-ID')}`
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function formatDateTime(date: string) {
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <Head title="Detail Booking" />

  <MainLayout>
    <div class="max-w-4xl mx-auto px-4 py-6 lg:py-10">
      <!-- Back Button -->
      <Link href="/me/bookings" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-6">
        <ArrowLeft class="w-4 h-4 mr-2" />
        Kembali ke Daftar Booking
      </Link>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-20">
        <Loader2 class="w-10 h-10 mx-auto animate-spin text-[#362EED]" />
        <p class="mt-4 text-gray-500">Memuat detail booking...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-20">
        <AlertCircle class="w-16 h-16 mx-auto text-red-400" />
        <p class="mt-4 text-gray-500">{{ error }}</p>
        <Button @click="fetchBooking" class="mt-4">Coba Lagi</Button>
      </div>

      <!-- Booking Detail -->
      <div v-else-if="booking" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-[#060521]">{{ booking.order_id }}</h1>
            <p class="text-gray-500">Dibuat pada {{ formatDateTime(booking.created_at) }}</p>
          </div>
          <Badge :class="currentStatus.color" class="text-sm px-4 py-2">
            <component :is="currentStatus.icon" class="w-4 h-4 mr-2" />
            {{ currentStatus.label }}
          </Badge>
        </div>

        <!-- Car Info Card -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Car class="w-5 h-5" />
              Informasi Mobil
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex gap-4">
              <div class="w-32 h-24 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden">
                <img
                  v-if="booking.car?.thumbnail_url"
                  :src="booking.car.thumbnail_url"
                  :alt="booking.car?.name"
                  class="w-full h-full object-cover"
                />
                <Car v-else class="w-12 h-12 text-gray-300" />
              </div>
              <div>
                <h3 class="font-bold text-lg">{{ booking.car?.name }}</h3>
                <p class="text-gray-500">{{ booking.car?.brand }} • {{ booking.car?.type }}</p>
                <p class="text-gray-500">{{ booking.car?.transmission }} • {{ booking.car?.seats }} Kursi</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Booking Details Card -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Calendar class="w-5 h-5" />
              Detail Rental
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Tanggal Mulai</p>
                <p class="font-medium">{{ formatDate(booking.start_date) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Tanggal Selesai</p>
                <p class="font-medium">{{ formatDate(booking.end_date) }}</p>
              </div>
            </div>

            <div v-if="booking.pickup_location" class="flex items-start gap-2">
              <MapPin class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-sm text-gray-500">Lokasi Pickup</p>
                <p class="font-medium">{{ booking.pickup_location }}</p>
              </div>
            </div>

            <div class="flex items-start gap-2">
              <User class="w-5 h-5 text-gray-400 mt-0.5" />
              <div>
                <p class="text-sm text-gray-500">Tipe Rental</p>
                <p class="font-medium">{{ booking.use_driver ? 'Dengan Driver' : 'Lepas Kunci (Tanpa Driver)' }}</p>
              </div>
            </div>

            <div v-if="booking.notes" class="pt-4 border-t">
              <p class="text-sm text-gray-500">Catatan</p>
              <p class="font-medium">{{ booking.notes }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Payment Card -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <CreditCard class="w-5 h-5" />
              Pembayaran
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex justify-between items-center text-lg">
              <span class="text-gray-600">Total Harga</span>
              <span class="font-bold text-[#362EED]">{{ formatPrice(booking.total_price) }}</span>
            </div>

            <div v-if="booking.payment" class="text-sm text-gray-500">
              <p v-if="booking.payment.method">Metode: {{ booking.payment.method }}</p>
              <p>Status: {{ booking.payment.status }}</p>
            </div>

            <div v-if="canPay" class="pt-4 border-t">
              <div class="flex items-center gap-2 text-yellow-600 mb-4">
                <Clock class="w-5 h-5" />
                <span class="text-sm">Batas waktu pembayaran: {{ formatDateTime(booking.payment_deadline) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Action Buttons -->
        <div v-if="canPay || canCancel" class="flex flex-col sm:flex-row gap-3">
          <Button
            v-if="canPay"
            @click="handlePayment"
            :disabled="paymentLoading"
            class="flex-1 bg-[#362EED] hover:bg-[#2a24c4]"
            size="lg"
          >
            <Loader2 v-if="paymentLoading" class="w-4 h-4 mr-2 animate-spin" />
            <CreditCard v-else class="w-4 h-4 mr-2" />
            Bayar Sekarang
          </Button>

          <Button
            v-if="canCancel"
            @click="handleCancel"
            :disabled="cancelling"
            variant="outline"
            class="flex-1 border-red-300 text-red-600 hover:bg-red-50"
            size="lg"
          >
            <Loader2 v-if="cancelling" class="w-4 h-4 mr-2 animate-spin" />
            <XCircle v-else class="w-4 h-4 mr-2" />
            Batalkan Booking
          </Button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
