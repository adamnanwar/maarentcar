<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogFooter,
} from '@/components/ui/dialog'
import {
  ArrowLeft,
  Car as CarIcon,
  Users,
  Gauge,
  Fuel,
  MapPin,
  ShieldCheck,
  Clock,
  Star,
  Check,
  Calendar,
  Upload,
  AlertCircle
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

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

interface TourPackage {
  id: string
  name: string
  price: number
  duration_days: number
  description: string
}

const props = defineProps<{
  car: Car
  packages: TourPackage[]
}>()

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

const bookingDialogOpen = ref(false)
const selectedOption = ref<'self' | 'driver'>('driver')
const selectedPackage = ref<string | null>(null)

const form = useForm({
  car_id: props.car.id,
  tour_package_id: null as string | null,
  start_date: '',
  end_date: '',
  use_driver: true,
  ktp_image: null as File | null,
  sim_image: null as File | null,
})

const ktpPreview = ref<string | null>(null)
const simPreview = ref<string | null>(null)

const handleKtpUpload = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.ktp_image = target.files[0]
    ktpPreview.value = URL.createObjectURL(target.files[0])
  }
}

const handleSimUpload = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.sim_image = target.files[0]
    simPreview.value = URL.createObjectURL(target.files[0])
  }
}

const rentalDays = computed(() => {
  if (!form.start_date || !form.end_date) return 0
  const start = new Date(form.start_date)
  const end = new Date(form.end_date)
  const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const selectedPackageData = computed(() => {
  if (!selectedPackage.value) return null
  return props.packages.find(p => p.id === selectedPackage.value)
})

const totalPrice = computed(() => {
  let total = props.car.price_per_day * rentalDays.value
  if (selectedPackageData.value) {
    total += selectedPackageData.value.price
  }
  return total
})

const minDate = computed(() => {
  const today = new Date()
  today.setDate(today.getDate() + 1)
  return today.toISOString().split('T')[0]
})

const submitBooking = () => {
  form.tour_package_id = selectedPackage.value
  form.use_driver = selectedOption.value === 'driver'

  form.post('/bookings', {
    forceFormData: true,
    onSuccess: () => {
      bookingDialogOpen.value = false
    },
  })
}
</script>

<template>
  <Head :title="`${car.name} - MaaRentCar`" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Back Button -->
      <Link href="/cars" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#060521] mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Daftar Mobil
      </Link>

      <div class="grid gap-8 lg:grid-cols-2">
        <!-- Left: Image -->
        <div class="space-y-4">
          <div class="aspect-video rounded-2xl overflow-hidden bg-gray-100 border shadow-lg relative">
            <img
              v-if="car.image_url"
              :src="car.image_url"
              :alt="car.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="absolute inset-0 flex items-center justify-center">
              <CarIcon class="h-32 w-32 text-gray-300" />
            </div>
            <!-- Availability Badge -->
            <div class="absolute top-4 right-4">
              <span
                :class="[
                  'px-3 py-1.5 rounded-full text-sm font-semibold shadow-lg',
                  car.is_available
                    ? 'bg-green-500 text-white'
                    : 'bg-red-500 text-white'
                ]"
              >
                {{ car.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Right: Details -->
        <div class="space-y-6">
          <!-- Header -->
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 rounded-full bg-[#362EED]/10 text-[#362EED] text-xs font-semibold">
                {{ car.type }}
              </span>
            </div>
            <h1 class="text-3xl font-bold text-[#060521]">{{ car.name }}</h1>
            <p class="text-lg text-gray-500">{{ car.brand }}</p>
          </div>

          <!-- Specs -->
          <Card>
            <CardContent class="grid grid-cols-2 gap-4 p-6">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#362EED]/10">
                  <Gauge class="h-5 w-5 text-[#362EED]" />
                </div>
                <div>
                  <div class="text-sm text-gray-500">Transmisi</div>
                  <div class="font-semibold text-[#060521]">{{ car.transmission }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                  <Users class="h-5 w-5 text-purple-600" />
                </div>
                <div>
                  <div class="text-sm text-gray-500">Kapasitas</div>
                  <div class="font-semibold text-[#060521]">{{ car.seats }} Penumpang</div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Pricing -->
          <Card class="border-2 border-[#362EED]/20">
            <CardContent class="p-6">
              <div class="flex items-center justify-between mb-4">
                <span class="text-gray-500">Harga Sewa</span>
                <div class="text-right">
                  <div class="text-2xl font-bold text-[#362EED]">
                    {{ formatPrice(car.price_per_day) }}
                  </div>
                  <div class="text-sm text-gray-500">/hari</div>
                </div>
              </div>

              <Dialog v-model:open="bookingDialogOpen">
                <DialogTrigger asChild>
                  <Button
                    size="lg"
                    class="w-full bg-[#362EED] hover:bg-[#2a24c4]"
                    :disabled="!car.is_available"
                  >
                    <Calendar class="mr-2 h-5 w-5" />
                    {{ car.is_available ? 'Booking Sekarang' : 'Tidak Tersedia' }}
                  </Button>
                </DialogTrigger>

                <DialogContent class="max-w-lg max-h-[90vh] overflow-y-auto">
                  <DialogHeader>
                    <DialogTitle>Booking {{ car.name }}</DialogTitle>
                    <DialogDescription>
                      Lengkapi data booking dan upload dokumen yang diperlukan
                    </DialogDescription>
                  </DialogHeader>

                  <form @submit.prevent="submitBooking" class="space-y-4">
                    <!-- Rental Option -->
                    <div class="space-y-2">
                      <Label>Pilih Opsi Sewa</Label>
                      <div class="grid grid-cols-2 gap-3">
                        <button
                          type="button"
                          @click="selectedOption = 'driver'"
                          :class="[
                            'p-3 rounded-lg border-2 text-left transition-all',
                            selectedOption === 'driver'
                              ? 'border-[#362EED] bg-[#362EED]/5'
                              : 'border-gray-200 hover:border-gray-300'
                          ]"
                        >
                          <div class="font-semibold text-sm">Dengan Sopir</div>
                          <div class="text-xs text-gray-500">Termasuk driver</div>
                        </button>
                        <button
                          type="button"
                          @click="selectedOption = 'self'"
                          :class="[
                            'p-3 rounded-lg border-2 text-left transition-all',
                            selectedOption === 'self'
                              ? 'border-[#362EED] bg-[#362EED]/5'
                              : 'border-gray-200 hover:border-gray-300'
                          ]"
                        >
                          <div class="font-semibold text-sm">Lepas Kunci</div>
                          <div class="text-xs text-gray-500">Self drive</div>
                        </button>
                      </div>
                    </div>

                    <!-- Date Selection -->
                    <div class="grid grid-cols-2 gap-3">
                      <div class="space-y-2">
                        <Label for="start_date">Tanggal Mulai</Label>
                        <Input
                          id="start_date"
                          type="date"
                          v-model="form.start_date"
                          :min="minDate"
                          required
                        />
                      </div>
                      <div class="space-y-2">
                        <Label for="end_date">Tanggal Selesai</Label>
                        <Input
                          id="end_date"
                          type="date"
                          v-model="form.end_date"
                          :min="form.start_date || minDate"
                          required
                        />
                      </div>
                    </div>

                    <!-- Tour Package (Optional) -->
                    <div v-if="packages.length > 0" class="space-y-2">
                      <Label>Tambah Paket Wisata (Opsional)</Label>
                      <div class="space-y-2 max-h-40 overflow-y-auto">
                        <button
                          type="button"
                          @click="selectedPackage = null"
                          :class="[
                            'w-full p-3 rounded-lg border text-left transition-all',
                            !selectedPackage
                              ? 'border-[#362EED] bg-[#362EED]/5'
                              : 'border-gray-200 hover:border-gray-300'
                          ]"
                        >
                          <div class="font-semibold text-sm">Tanpa Paket Wisata</div>
                        </button>
                        <button
                          v-for="pkg in packages"
                          :key="pkg.id"
                          type="button"
                          @click="selectedPackage = pkg.id"
                          :class="[
                            'w-full p-3 rounded-lg border text-left transition-all',
                            selectedPackage === pkg.id
                              ? 'border-[#362EED] bg-[#362EED]/5'
                              : 'border-gray-200 hover:border-gray-300'
                          ]"
                        >
                          <div class="flex justify-between items-start">
                            <div>
                              <div class="font-semibold text-sm">{{ pkg.name }}</div>
                              <div class="text-xs text-gray-500">{{ pkg.duration_days }} hari</div>
                            </div>
                            <div class="text-sm font-bold text-[#362EED]">
                              +{{ formatPrice(pkg.price) }}
                            </div>
                          </div>
                        </button>
                      </div>
                    </div>

                    <!-- Document Upload -->
                    <div class="space-y-3 pt-2 border-t">
                      <div class="flex items-center gap-2 text-sm text-amber-600 bg-amber-50 p-3 rounded-lg">
                        <AlertCircle class="w-4 h-4 flex-shrink-0" />
                        <span>Upload KTP wajib. SIM A wajib jika memilih lepas kunci.</span>
                      </div>

                      <!-- KTP Upload -->
                      <div class="space-y-2">
                        <Label for="ktp">Upload KTP *</Label>
                        <div class="border-2 border-dashed rounded-lg p-4 text-center">
                          <input
                            id="ktp"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleKtpUpload"
                          />
                          <label for="ktp" class="cursor-pointer">
                            <div v-if="ktpPreview" class="mb-2">
                              <img :src="ktpPreview" class="h-20 mx-auto rounded" />
                            </div>
                            <div v-else>
                              <Upload class="w-8 h-8 mx-auto text-gray-400 mb-2" />
                            </div>
                            <p class="text-sm text-gray-500">
                              {{ ktpPreview ? 'Ganti foto KTP' : 'Klik untuk upload KTP' }}
                            </p>
                          </label>
                        </div>
                        <p v-if="form.errors.ktp_image" class="text-sm text-red-500">{{ form.errors.ktp_image }}</p>
                      </div>

                      <!-- SIM Upload (Required for self-drive) -->
                      <div v-if="selectedOption === 'self'" class="space-y-2">
                        <Label for="sim">Upload SIM A *</Label>
                        <div class="border-2 border-dashed rounded-lg p-4 text-center">
                          <input
                            id="sim"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleSimUpload"
                          />
                          <label for="sim" class="cursor-pointer">
                            <div v-if="simPreview" class="mb-2">
                              <img :src="simPreview" class="h-20 mx-auto rounded" />
                            </div>
                            <div v-else>
                              <Upload class="w-8 h-8 mx-auto text-gray-400 mb-2" />
                            </div>
                            <p class="text-sm text-gray-500">
                              {{ simPreview ? 'Ganti foto SIM' : 'Klik untuk upload SIM A' }}
                            </p>
                          </label>
                        </div>
                        <p v-if="form.errors.sim_image" class="text-sm text-red-500">{{ form.errors.sim_image }}</p>
                      </div>
                    </div>

                    <!-- Summary -->
                    <div v-if="rentalDays > 0" class="bg-gray-50 rounded-lg p-4 space-y-2">
                      <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ car.name }} x {{ rentalDays }} hari</span>
                        <span>{{ formatPrice(car.price_per_day * rentalDays) }}</span>
                      </div>
                      <div v-if="selectedPackageData" class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ selectedPackageData.name }}</span>
                        <span>{{ formatPrice(selectedPackageData.price) }}</span>
                      </div>
                      <div class="flex justify-between font-bold pt-2 border-t">
                        <span>Total</span>
                        <span class="text-[#362EED]">{{ formatPrice(totalPrice) }}</span>
                      </div>
                    </div>

                    <DialogFooter>
                      <Button
                        type="submit"
                        class="w-full bg-[#362EED] hover:bg-[#2a24c4]"
                        :disabled="form.processing || rentalDays <= 0"
                      >
                        {{ form.processing ? 'Memproses...' : 'Ajukan Booking' }}
                      </Button>
                    </DialogFooter>
                  </form>
                </DialogContent>
              </Dialog>

              <div class="flex items-center gap-2 text-sm text-gray-500 justify-center mt-4">
                <ShieldCheck class="h-4 w-4 text-green-600" />
                <span>Pembayaran Aman dengan Midtrans</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Terms Section -->
      <div class="mt-12 space-y-6">
        <h2 class="text-xl font-bold text-[#060521]">Syarat & Ketentuan</h2>
        <div class="grid gap-4 md:grid-cols-3">
          <Card>
            <CardContent class="p-6 space-y-3">
              <ShieldCheck class="h-8 w-8 text-[#362EED]" />
              <h3 class="font-semibold">Persyaratan</h3>
              <ul class="text-sm text-gray-500 space-y-2">
                <li>- KTP yang masih berlaku</li>
                <li>- SIM A (untuk lepas kunci)</li>
                <li>- Usia minimal 21 tahun</li>
              </ul>
            </CardContent>
          </Card>
          <Card>
            <CardContent class="p-6 space-y-3">
              <Clock class="h-8 w-8 text-purple-600" />
              <h3 class="font-semibold">Durasi Sewa</h3>
              <ul class="text-sm text-gray-500 space-y-2">
                <li>- Minimal sewa 1 hari (24 jam)</li>
                <li>- Overtime dikenakan biaya</li>
                <li>- Perpanjangan bisa diatur</li>
              </ul>
            </CardContent>
          </Card>
          <Card>
            <CardContent class="p-6 space-y-3">
              <MapPin class="h-8 w-8 text-green-600" />
              <h3 class="font-semibold">Area Layanan</h3>
              <ul class="text-sm text-gray-500 space-y-2">
                <li>- Pengantaran gratis area kota</li>
                <li>- Layanan antar luar kota</li>
                <li>- Bebas keluar kota (info)</li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
