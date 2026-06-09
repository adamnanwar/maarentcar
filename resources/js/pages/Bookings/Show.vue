<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  ArrowLeft,
  Car as CarIcon,
  Calendar,
  Clock,
  CheckCircle,
  XCircle,
  CreditCard,
  MapPin,
  FileText,
  AlertCircle,
  Phone,
  User
} from 'lucide-vue-next'
import { ref, watch, computed } from 'vue'

declare global {
  interface Window {
    snap: {
      pay: (token: string, options: {
        onSuccess?: () => void
        onPending?: () => void
        onError?: () => void
        onClose?: () => void
      }) => void
    }
  }
}

interface Booking {
  id: string
  order_id: string
  status: string
  payment_status: string
  start_date: string
  end_date: string
  total_price: number
  ktp_image_url: string
  sim_image_url: string | null
  admin_note: string | null
  snap_token: string | null
  created_at: string
  car: {
    id: string
    name: string
    brand: string
    type: string
    transmission: string
    seats: number
    price_per_day: number
    image_url: string | null
  }
  tour_package?: {
    id: string
    name: string
    price: number
    duration_days: number
  } | null
  user: {
    name: string
    email: string
    phone: string
  }
}

const props = defineProps<{
  booking: Booking
}>()

const page = usePage<{ flash: { snap_token?: string } }>()
const processing = ref(false)

// Watch for snap_token from flash and open Midtrans popup
watch(() => page.props.flash?.snap_token, (snapToken) => {
  if (snapToken) {
    window.snap.pay(snapToken, {
      onSuccess: () => {
        router.reload()
      },
      onPending: () => {
        router.reload()
      },
      onError: () => {
        alert('Pembayaran gagal')
        processing.value = false
      },
      onClose: () => {
        processing.value = false
      }
    })
  }
}, { immediate: true })

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatDateTime = (date: string) => {
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStatusConfig = (status: string) => {
  const configs: Record<string, { label: string; color: string; bgColor: string; icon: any; description: string }> = {
    PENDING_VERIFICATION: {
      label: 'Menunggu Verifikasi',
      color: 'text-amber-700',
      bgColor: 'bg-amber-100',
      icon: Clock,
      description: 'Dokumen Anda sedang diverifikasi oleh admin. Mohon tunggu 1x24 jam.',
    },
    PENDING_PAYMENT: {
      label: 'Menunggu Pembayaran',
      color: 'text-blue-700',
      bgColor: 'bg-blue-100',
      icon: CreditCard,
      description: 'Dokumen telah diverifikasi. Silakan lakukan pembayaran untuk mengkonfirmasi booking.',
    },
    PAID: {
      label: 'Sudah Dibayar',
      color: 'text-green-700',
      bgColor: 'bg-green-100',
      icon: CheckCircle,
      description: 'Pembayaran berhasil. Mobil siap digunakan pada tanggal yang ditentukan.',
    },
    COMPLETED: {
      label: 'Selesai',
      color: 'text-gray-700',
      bgColor: 'bg-gray-100',
      icon: CheckCircle,
      description: 'Booking telah selesai. Terima kasih telah menggunakan layanan kami.',
    },
    CANCELLED: {
      label: 'Dibatalkan',
      color: 'text-red-700',
      bgColor: 'bg-red-100',
      icon: XCircle,
      description: 'Booking ini telah dibatalkan.',
    },
    REJECTED: {
      label: 'Ditolak',
      color: 'text-red-700',
      bgColor: 'bg-red-100',
      icon: XCircle,
      description: 'Dokumen Anda tidak memenuhi syarat. Silakan ajukan booking baru dengan dokumen yang valid.',
    },
  }
  return configs[status] || configs.PENDING_VERIFICATION
}

const canPay = () => {
  return props.booking.status === 'PENDING_PAYMENT'
}

const canCancel = () => {
  return ['PENDING_VERIFICATION', 'PENDING_PAYMENT'].includes(props.booking.status)
}

const handlePayment = () => {
  processing.value = true
  router.post(`/bookings/${props.booking.id}/pay`, {}, {
    preserveScroll: true,
  })
}

const handleCancel = () => {
  if (confirm('Apakah Anda yakin ingin membatalkan booking ini?')) {
    router.post(`/bookings/${props.booking.id}/cancel`)
  }
}

const getDaysCount = () => {
  const start = new Date(props.booking.start_date)
  const end = new Date(props.booking.end_date)
  return Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24))
}
</script>

<template>
  <Head :title="`Booking ${booking.order_id}`" />

  <MainLayout>
    <div class="max-w-4xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Back Button -->
      <Link href="/dashboard" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#060521] mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Dashboard
      </Link>

      <!-- Status Banner -->
      <Card :class="['mb-6', getStatusConfig(booking.status).bgColor]">
        <CardContent class="p-4">
          <div class="flex items-start gap-3">
            <component
              :is="getStatusConfig(booking.status).icon"
              :class="['w-6 h-6 flex-shrink-0', getStatusConfig(booking.status).color]"
            />
            <div>
              <h3 :class="['font-semibold', getStatusConfig(booking.status).color]">
                {{ getStatusConfig(booking.status).label }}
              </h3>
              <p class="text-sm text-gray-600">{{ getStatusConfig(booking.status).description }}</p>
              <p v-if="booking.admin_note" class="text-sm mt-2 p-2 bg-white/50 rounded">
                <strong>Catatan Admin:</strong> {{ booking.admin_note }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Order ID & Actions -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
          <p class="text-sm text-gray-500">Order ID</p>
          <p class="text-xl font-bold text-[#060521]">{{ booking.order_id }}</p>
          <p class="text-sm text-gray-500">{{ formatDateTime(booking.created_at) }}</p>
        </div>
        <div class="flex gap-2">
          <Button
            v-if="canPay()"
            @click="handlePayment"
            :disabled="processing"
            class="bg-[#362EED] hover:bg-[#2a24c4]"
          >
            <CreditCard class="w-4 h-4 mr-2" />
            {{ processing ? 'Memproses...' : 'Bayar Sekarang' }}
          </Button>
          <Button
            v-if="canCancel()"
            @click="handleCancel"
            variant="outline"
            class="text-red-600 border-red-200 hover:bg-red-50"
          >
            <XCircle class="w-4 h-4 mr-2" />
            Batalkan
          </Button>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <!-- Car Details -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Detail Mobil</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex gap-4">
              <div class="w-24 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                <img
                  v-if="booking.car.image_url"
                  :src="booking.car.image_url"
                  :alt="booking.car.name"
                  class="w-full h-full object-cover"
                />
                <CarIcon v-else class="w-8 h-8 text-gray-300" />
              </div>
              <div>
                <h3 class="font-bold text-[#060521]">{{ booking.car.name }}</h3>
                <p class="text-sm text-gray-500">{{ booking.car.brand }} • {{ booking.car.type }}</p>
                <p class="text-sm text-gray-500">{{ booking.car.transmission }} • {{ booking.car.seats }} kursi</p>
                <p class="text-sm font-semibold text-[#362EED] mt-1">
                  {{ formatPrice(booking.car.price_per_day) }}/hari
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Rental Period -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Periode Sewa</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex items-center gap-3">
              <Calendar class="w-5 h-5 text-gray-400" />
              <div>
                <p class="text-sm text-gray-500">Tanggal Mulai</p>
                <p class="font-medium">{{ formatDate(booking.start_date) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <Calendar class="w-5 h-5 text-gray-400" />
              <div>
                <p class="text-sm text-gray-500">Tanggal Selesai</p>
                <p class="font-medium">{{ formatDate(booking.end_date) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <Clock class="w-5 h-5 text-gray-400" />
              <div>
                <p class="text-sm text-gray-500">Durasi</p>
                <p class="font-medium">{{ getDaysCount() }} hari</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Tour Package (if any) -->
        <Card v-if="booking.tour_package">
          <CardHeader>
            <CardTitle class="text-lg">Paket Wisata</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-[#362EED]/10 flex items-center justify-center">
                <MapPin class="w-5 h-5 text-[#362EED]" />
              </div>
              <div>
                <h3 class="font-bold text-[#060521]">{{ booking.tour_package.name }}</h3>
                <p class="text-sm text-gray-500">{{ booking.tour_package.duration_days }} hari</p>
                <p class="text-sm font-semibold text-[#362EED]">
                  {{ formatPrice(booking.tour_package.price) }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Documents -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg">Dokumen</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div>
              <p class="text-sm text-gray-500 mb-2">KTP</p>
              <a
                :href="booking.ktp_image_url"
                target="_blank"
                class="inline-flex items-center gap-2 text-[#362EED] hover:underline text-sm"
              >
                <FileText class="w-4 h-4" />
                Lihat KTP
              </a>
            </div>
            <div v-if="booking.sim_image_url">
              <p class="text-sm text-gray-500 mb-2">SIM A</p>
              <a
                :href="booking.sim_image_url"
                target="_blank"
                class="inline-flex items-center gap-2 text-[#362EED] hover:underline text-sm"
              >
                <FileText class="w-4 h-4" />
                Lihat SIM
              </a>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Price Summary -->
      <Card class="mt-6">
        <CardHeader>
          <CardTitle class="text-lg">Rincian Biaya</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">
                {{ booking.car.name }} x {{ getDaysCount() }} hari
              </span>
              <span>{{ formatPrice(booking.car.price_per_day * getDaysCount()) }}</span>
            </div>
            <div v-if="booking.tour_package" class="flex justify-between">
              <span class="text-gray-500">{{ booking.tour_package.name }}</span>
              <span>{{ formatPrice(booking.tour_package.price) }}</span>
            </div>
            <div class="flex justify-between pt-3 border-t font-bold text-lg">
              <span>Total</span>
              <span class="text-[#362EED]">{{ formatPrice(booking.total_price) }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Contact Info -->
      <Card class="mt-6">
        <CardHeader>
          <CardTitle class="text-lg">Informasi Pemesan</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div class="flex items-center gap-3">
              <User class="w-5 h-5 text-gray-400" />
              <span>{{ booking.user.name }}</span>
            </div>
            <div class="flex items-center gap-3">
              <Phone class="w-5 h-5 text-gray-400" />
              <span>{{ booking.user.phone }}</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
