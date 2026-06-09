<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { bookingService } from '@/services/bookingService'
import type { Booking, BookingStatus } from '@/types/models'
import {
  ArrowLeft,
  Search,
  Calendar,
  Loader2,
  Eye,
  RefreshCw,
} from 'lucide-vue-next'

const bookings = ref<Booking[]>([])
const loading = ref(true)
const searchQuery = ref('')
const statusFilter = ref<string>('ALL')

// Dialog states
const showDetailDialog = ref(false)
const showStatusDialog = ref(false)
const formLoading = ref(false)
const formError = ref<string | null>(null)

const selectedBooking = ref<Booking | null>(null)
const newStatus = ref<BookingStatus>('PENDING_PAYMENT')

const statusOptions: { value: BookingStatus; label: string }[] = [
  { value: 'PENDING_PAYMENT', label: 'Pending Payment' },
  { value: 'PAID', label: 'Paid' },
  { value: 'IN_PROGRESS', label: 'In Progress' },
  { value: 'COMPLETED', label: 'Completed' },
  { value: 'CANCELLED', label: 'Cancelled' },
  { value: 'EXPIRED', label: 'Expired' },
]

const statusColors: Record<string, string> = {
  PENDING_PAYMENT: 'bg-yellow-100 text-yellow-800',
  PAID: 'bg-blue-100 text-blue-800',
  IN_PROGRESS: 'bg-purple-100 text-purple-800',
  COMPLETED: 'bg-green-100 text-green-800',
  CANCELLED: 'bg-red-100 text-red-800',
  EXPIRED: 'bg-gray-100 text-gray-800',
}

const filteredBookings = computed(() => {
  let result = bookings.value

  if (statusFilter.value !== 'ALL') {
    result = result.filter((b) => b.status === statusFilter.value)
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(
      (b) =>
        b.order_id.toLowerCase().includes(query) ||
        b.user?.name?.toLowerCase().includes(query) ||
        b.car?.name?.toLowerCase().includes(query)
    )
  }

  return result
})

const stats = computed(() => ({
  total: bookings.value.length,
  pending: bookings.value.filter((b) => b.status === 'PENDING_PAYMENT').length,
  paid: bookings.value.filter((b) => b.status === 'PAID').length,
  inProgress: bookings.value.filter((b) => b.status === 'IN_PROGRESS').length,
  completed: bookings.value.filter((b) => b.status === 'COMPLETED').length,
}))

onMounted(async () => {
  await fetchBookings()
})

async function fetchBookings() {
  loading.value = true
  try {
    bookings.value = await bookingService.listAll()
  } catch (e) {
    console.error('Failed to fetch bookings:', e)
  } finally {
    loading.value = false
  }
}

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

function formatDateTime(date: string) {
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function openDetailDialog(booking: Booking) {
  selectedBooking.value = booking
  showDetailDialog.value = true
}

function openStatusDialog(booking: Booking) {
  selectedBooking.value = booking
  newStatus.value = booking.status
  formError.value = null
  showStatusDialog.value = true
}

async function handleUpdateStatus() {
  if (!selectedBooking.value) return

  formLoading.value = true
  formError.value = null

  try {
    await bookingService.updateStatus(selectedBooking.value.id, newStatus.value)
    showStatusDialog.value = false
    await fetchBookings()
  } catch (e: unknown) {
    if (e instanceof Error) {
      formError.value = e.message
    } else {
      formError.value = 'Gagal mengupdate status'
    }
  } finally {
    formLoading.value = false
  }
}

function getStatusLabel(status: BookingStatus): string {
  const option = statusOptions.find((o) => o.value === status)
  return option?.label || status
}
</script>

<template>
  <Head title="Kelola Booking - Admin" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
          <Link href="/admin">
            <Button variant="ghost" size="icon">
              <ArrowLeft class="w-5 h-5" />
            </Button>
          </Link>
          <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-[#060521]">Kelola Booking</h1>
            <p class="text-gray-500">{{ stats.total }} total booking</p>
          </div>
        </div>
        <Button @click="fetchBookings" variant="outline">
          <RefreshCw class="w-4 h-4 mr-2" />
          Refresh
        </Button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <button
          @click="statusFilter = 'ALL'"
          :class="[
            'p-4 rounded-xl border text-left transition-colors',
            statusFilter === 'ALL' ? 'border-[#362EED] bg-[#362EED]/5' : 'border-gray-200 hover:border-gray-300',
          ]"
        >
          <p class="text-2xl font-bold text-[#060521]">{{ stats.total }}</p>
          <p class="text-sm text-gray-500">Total</p>
        </button>
        <button
          @click="statusFilter = 'PENDING_PAYMENT'"
          :class="[
            'p-4 rounded-xl border text-left transition-colors',
            statusFilter === 'PENDING_PAYMENT' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300',
          ]"
        >
          <p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p>
          <p class="text-sm text-gray-500">Pending</p>
        </button>
        <button
          @click="statusFilter = 'PAID'"
          :class="[
            'p-4 rounded-xl border text-left transition-colors',
            statusFilter === 'PAID' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300',
          ]"
        >
          <p class="text-2xl font-bold text-blue-600">{{ stats.paid }}</p>
          <p class="text-sm text-gray-500">Paid</p>
        </button>
        <button
          @click="statusFilter = 'IN_PROGRESS'"
          :class="[
            'p-4 rounded-xl border text-left transition-colors',
            statusFilter === 'IN_PROGRESS' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-gray-300',
          ]"
        >
          <p class="text-2xl font-bold text-purple-600">{{ stats.inProgress }}</p>
          <p class="text-sm text-gray-500">In Progress</p>
        </button>
        <button
          @click="statusFilter = 'COMPLETED'"
          :class="[
            'p-4 rounded-xl border text-left transition-colors',
            statusFilter === 'COMPLETED' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300',
          ]"
        >
          <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
          <p class="text-sm text-gray-500">Completed</p>
        </button>
      </div>

      <!-- Search -->
      <div class="relative mb-6">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <Input
          v-model="searchQuery"
          placeholder="Cari order ID, customer, atau mobil..."
          class="pl-10"
        />
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-20">
        <Loader2 class="w-10 h-10 mx-auto animate-spin text-[#362EED]" />
        <p class="mt-4 text-gray-500">Memuat data...</p>
      </div>

      <!-- Bookings Table -->
      <Card v-else>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Order ID</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Customer</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Mobil</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Tanggal Sewa</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Total</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                  <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="booking in filteredBookings" :key="booking.id" class="border-b last:border-0 hover:bg-gray-50">
                  <td class="px-4 py-4">
                    <p class="font-mono font-medium text-sm">{{ booking.order_id }}</p>
                    <p class="text-xs text-gray-500">{{ formatDateTime(booking.created_at) }}</p>
                  </td>
                  <td class="px-4 py-4">
                    <p class="font-medium">{{ booking.user?.name || '-' }}</p>
                    <p class="text-sm text-gray-500">{{ booking.user?.email || '-' }}</p>
                  </td>
                  <td class="px-4 py-4">
                    <p class="font-medium">{{ booking.car?.name || '-' }}</p>
                    <p class="text-sm text-gray-500">{{ booking.car?.license_plate || '-' }}</p>
                  </td>
                  <td class="px-4 py-4">
                    <p class="text-sm">{{ formatDate(booking.start_date) }}</p>
                    <p class="text-sm text-gray-500">s/d {{ formatDate(booking.end_date) }}</p>
                  </td>
                  <td class="px-4 py-4 font-medium">{{ formatPrice(booking.total_price) }}</td>
                  <td class="px-4 py-4">
                    <Badge :class="statusColors[booking.status]">
                      {{ getStatusLabel(booking.status) }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <Button variant="ghost" size="icon" @click="openDetailDialog(booking)">
                        <Eye class="w-4 h-4" />
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        @click="openStatusDialog(booking)"
                      >
                        Update Status
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredBookings.length === 0">
                  <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                    <Calendar class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                    <p>Tidak ada booking ditemukan</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Detail Dialog -->
    <Dialog v-model:open="showDetailDialog">
      <DialogContent class="max-w-lg">
        <DialogHeader>
          <DialogTitle>Detail Booking</DialogTitle>
          <DialogDescription>{{ selectedBooking?.order_id }}</DialogDescription>
        </DialogHeader>

        <div v-if="selectedBooking" class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-gray-500">Status</span>
            <Badge :class="statusColors[selectedBooking.status]">
              {{ getStatusLabel(selectedBooking.status) }}
            </Badge>
          </div>

          <div class="border-t pt-4">
            <h4 class="font-medium mb-2">Customer</h4>
            <p>{{ selectedBooking.user?.name }}</p>
            <p class="text-sm text-gray-500">{{ selectedBooking.user?.email }}</p>
            <p class="text-sm text-gray-500">{{ selectedBooking.user?.phone }}</p>
          </div>

          <div class="border-t pt-4">
            <h4 class="font-medium mb-2">Mobil</h4>
            <p>{{ selectedBooking.car?.name }}</p>
            <p class="text-sm text-gray-500">{{ selectedBooking.car?.brand }} {{ selectedBooking.car?.model }}</p>
            <p class="text-sm text-gray-500">{{ selectedBooking.car?.license_plate }}</p>
          </div>

          <div class="border-t pt-4">
            <h4 class="font-medium mb-2">Periode Sewa</h4>
            <p>{{ formatDate(selectedBooking.start_date) }} - {{ formatDate(selectedBooking.end_date) }}</p>
            <p class="text-sm text-gray-500">{{ selectedBooking.duration_days }} hari</p>
          </div>

          <div class="border-t pt-4">
            <h4 class="font-medium mb-2">Pembayaran</h4>
            <div class="flex justify-between">
              <span>Harga per hari</span>
              <span>{{ formatPrice(selectedBooking.car?.price_per_day || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Durasi</span>
              <span>{{ selectedBooking.duration_days }} hari</span>
            </div>
            <div class="flex justify-between font-bold text-lg mt-2 pt-2 border-t">
              <span>Total</span>
              <span>{{ formatPrice(selectedBooking.total_price) }}</span>
            </div>
          </div>

          <div v-if="selectedBooking.payment" class="border-t pt-4">
            <h4 class="font-medium mb-2">Info Pembayaran</h4>
            <p class="text-sm">Metode: {{ selectedBooking.payment.payment_type || '-' }}</p>
            <p class="text-sm text-gray-500">Dibayar: {{ selectedBooking.payment.paid_at ? formatDateTime(selectedBooking.payment.paid_at) : '-' }}</p>
          </div>

          <div class="border-t pt-4">
            <p class="text-xs text-gray-500">Dibuat: {{ formatDateTime(selectedBooking.created_at) }}</p>
            <p class="text-xs text-gray-500">Batas bayar: {{ selectedBooking.payment_deadline ? formatDateTime(selectedBooking.payment_deadline) : '-' }}</p>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showDetailDialog = false">Tutup</Button>
          <Button @click="openStatusDialog(selectedBooking!); showDetailDialog = false" class="bg-[#362EED] hover:bg-[#362EED]/90">
            Update Status
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Update Status Dialog -->
    <Dialog v-model:open="showStatusDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Update Status Booking</DialogTitle>
          <DialogDescription>{{ selectedBooking?.order_id }}</DialogDescription>
        </DialogHeader>

        <div class="space-y-4">
          <div v-if="formError" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm">
            {{ formError }}
          </div>

          <div class="space-y-2">
            <Label>Status Saat Ini</Label>
            <Badge :class="statusColors[selectedBooking?.status || 'PENDING_PAYMENT']">
              {{ getStatusLabel(selectedBooking?.status || 'PENDING_PAYMENT') }}
            </Badge>
          </div>

          <div class="space-y-2">
            <Label for="new-status">Status Baru</Label>
            <Select v-model="newStatus">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="option in statusOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="p-3 bg-yellow-50 text-yellow-800 rounded-lg text-sm">
            <p class="font-medium">Perhatian:</p>
            <p>Mengubah status akan mengirim email notifikasi ke customer.</p>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="showStatusDialog = false">Batal</Button>
          <Button
            type="button"
            :disabled="formLoading || newStatus === selectedBooking?.status"
            @click="handleUpdateStatus"
            class="bg-[#362EED] hover:bg-[#362EED]/90"
          >
            <Loader2 v-if="formLoading" class="w-4 h-4 mr-2 animate-spin" />
            Update Status
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </MainLayout>
</template>
