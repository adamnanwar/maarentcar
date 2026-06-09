<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import {
  ArrowLeft,
  Car as CarIcon,
  User,
  Calendar,
  Phone,
  Mail,
  FileText,
  CheckCircle,
  XCircle,
  Clock,
  MapPin
} from 'lucide-vue-next'
import { ref } from 'vue'

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
    id: string
    name: string
    brand: string
    type: string
    transmission: string
    image_url: string | null
  }
  tour_package?: {
    name: string
    duration_days: number
  } | null
}

const props = defineProps<{
  booking: Booking
}>()

const form = useForm({
  decision: '' as 'approve' | 'reject' | '',
  admin_note: '',
})

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

const submit = (decision: 'approve' | 'reject') => {
  form.decision = decision
  form.post(`/admin/validations/${props.booking.id}`, {
    onSuccess: () => {
      // Redirect handled by controller
    },
  })
}

const previewImage = ref<string | null>(null)
const showPreview = (url: string) => {
  previewImage.value = url
}
const closePreview = () => {
  previewImage.value = null
}
</script>

<template>
  <Head :title="`Verifikasi ${booking.order_id}`" />

  <MainLayout>
    <div class="p-6 lg:p-8">
      <!-- Back Button -->
      <Link href="/admin/validations" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#060521] mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Daftar Verifikasi
      </Link>

      <!-- Header -->
      <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
          <p class="text-sm text-gray-500">Order ID</p>
          <h1 class="text-2xl font-bold text-[#060521]">{{ booking.order_id }}</h1>
        </div>
        <span class="px-4 py-2 rounded-full bg-amber-100 text-amber-700 font-medium flex items-center gap-2">
          <Clock class="w-4 h-4" />
          Menunggu Verifikasi
        </span>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Left: Customer & Booking Info -->
        <div class="space-y-6">
          <!-- Customer Info -->
          <Card>
            <CardHeader>
              <CardTitle>Informasi Pemesan</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex items-center gap-3">
                <User class="w-5 h-5 text-gray-400" />
                <span class="font-medium">{{ booking.user.name }}</span>
              </div>
              <div class="flex items-center gap-3">
                <Mail class="w-5 h-5 text-gray-400" />
                <span>{{ booking.user.email }}</span>
              </div>
              <div class="flex items-center gap-3">
                <Phone class="w-5 h-5 text-gray-400" />
                <span>{{ booking.user.phone }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Car Info -->
          <Card>
            <CardHeader>
              <CardTitle>Detail Mobil</CardTitle>
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
                  <p class="text-sm text-gray-500">{{ booking.car.transmission }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Tour Package -->
          <Card v-if="booking.tour_package">
            <CardHeader>
              <CardTitle>Paket Wisata</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-[#362EED]/10 flex items-center justify-center">
                  <MapPin class="w-5 h-5 text-[#362EED]" />
                </div>
                <div>
                  <p class="font-bold text-[#060521]">{{ booking.tour_package.name }}</p>
                  <p class="text-sm text-gray-500">{{ booking.tour_package.duration_days }} hari</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Booking Period -->
          <Card>
            <CardHeader>
              <CardTitle>Periode Sewa</CardTitle>
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
              <div class="pt-3 border-t">
                <p class="text-sm text-gray-500">Total Harga</p>
                <p class="text-xl font-bold text-[#362EED]">{{ formatPrice(booking.total_price) }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Right: Documents & Action -->
        <div class="space-y-6">
          <!-- Documents -->
          <Card>
            <CardHeader>
              <CardTitle>Dokumen Identitas</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <!-- KTP -->
              <div>
                <Label class="mb-2 block">KTP</Label>
                <div
                  @click="showPreview(booking.ktp_image_url)"
                  class="cursor-pointer rounded-lg border-2 border-dashed hover:border-[#362EED] transition-colors overflow-hidden"
                >
                  <img
                    :src="booking.ktp_image_url"
                    alt="KTP"
                    class="w-full h-48 object-cover"
                  />
                </div>
                <a
                  :href="booking.ktp_image_url"
                  target="_blank"
                  class="inline-flex items-center gap-1 text-sm text-[#362EED] hover:underline mt-2"
                >
                  <FileText class="w-4 h-4" />
                  Buka di tab baru
                </a>
              </div>

              <!-- SIM -->
              <div v-if="booking.sim_image_url">
                <Label class="mb-2 block">SIM A</Label>
                <div
                  @click="showPreview(booking.sim_image_url)"
                  class="cursor-pointer rounded-lg border-2 border-dashed hover:border-[#362EED] transition-colors overflow-hidden"
                >
                  <img
                    :src="booking.sim_image_url"
                    alt="SIM"
                    class="w-full h-48 object-cover"
                  />
                </div>
                <a
                  :href="booking.sim_image_url"
                  target="_blank"
                  class="inline-flex items-center gap-1 text-sm text-[#362EED] hover:underline mt-2"
                >
                  <FileText class="w-4 h-4" />
                  Buka di tab baru
                </a>
              </div>
              <div v-else class="p-4 rounded-lg bg-gray-50 text-center text-gray-500">
                <p class="text-sm">Tanpa SIM (booking dengan driver)</p>
              </div>
            </CardContent>
          </Card>

          <!-- Decision Form -->
          <Card class="border-2">
            <CardHeader>
              <CardTitle>Keputusan Verifikasi</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label for="admin_note">Catatan (opsional)</Label>
                <textarea
                  id="admin_note"
                  v-model="form.admin_note"
                  rows="3"
                  class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#362EED] resize-none"
                  placeholder="Tambahkan catatan untuk customer..."
                />
              </div>

              <div class="flex gap-3">
                <Button
                  @click="submit('approve')"
                  :disabled="form.processing"
                  class="flex-1 bg-green-600 hover:bg-green-700"
                >
                  <CheckCircle class="w-4 h-4 mr-2" />
                  {{ form.processing ? 'Memproses...' : 'Approve' }}
                </Button>
                <Button
                  @click="submit('reject')"
                  :disabled="form.processing"
                  variant="outline"
                  class="flex-1 text-red-600 border-red-200 hover:bg-red-50"
                >
                  <XCircle class="w-4 h-4 mr-2" />
                  Reject
                </Button>
              </div>

              <p v-if="form.errors.decision" class="text-sm text-red-500">{{ form.errors.decision }}</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div
      v-if="previewImage"
      @click="closePreview"
      class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
    >
      <img
        :src="previewImage"
        class="max-w-full max-h-full object-contain rounded-lg"
        @click.stop
      />
      <button
        @click="closePreview"
        class="absolute top-4 right-4 text-white hover:text-gray-300"
      >
        <XCircle class="w-8 h-8" />
      </button>
    </div>
  </MainLayout>
</template>
