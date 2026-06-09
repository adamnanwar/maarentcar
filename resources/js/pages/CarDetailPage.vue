<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { ArrowLeft, Car as CarIcon, Users, Gauge, Fuel, MapPin, ShieldCheck, Clock, Star, Check } from 'lucide-vue-next'
import { ref } from 'vue'

const props = defineProps<{
  id?: string
}>()

// Sample car data
const car = ref({
  id: props.id,
  name: 'Toyota Avanza',
  brand: 'Toyota',
  type: 'MPV',
  year: 2023,
  transmission: 'Manual',
  seats: 7,
  fuel: 'Bensin',
  price_per_day: 350000,
  with_driver_price_per_day: 500000,
  rating: 4.8,
  reviews: 156,
  description: 'Toyota Avanza adalah pilihan sempurna untuk perjalanan keluarga dengan kapasitas 7 penumpang. Mobil ini dikenal irit bahan bakar dan nyaman untuk perjalanan jarak jauh.',
  features: [
    'AC Double Blower',
    'Audio System',
    'Power Steering',
    'Power Window',
    'Central Lock',
    'Airbag',
    'ABS',
    'Velg Racing'
  ],
  images: []
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

const selectedOption = ref<'self' | 'driver'>('self')
</script>

<template>
  <Head :title="`${car.name} - MaaRentCar`" />
  
  <MainLayout>
    <div class="container py-8">
      <!-- Back Button -->
      <Link href="/cars" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Daftar Mobil
      </Link>
      
      <div class="grid gap-8 lg:grid-cols-2">
        <!-- Left: Images -->
        <div class="space-y-4">
          <!-- Main Image -->
          <div class="aspect-video rounded-2xl overflow-hidden bg-gradient-to-br from-blue-600/10 to-purple-600/10 border shadow-xl relative">
            <div class="absolute inset-0 flex items-center justify-center">
              <CarIcon class="h-32 w-32 text-blue-600/30" />
            </div>
            <!-- Badge -->
            <div class="absolute top-6 left-6 px-3 py-1.5 rounded-full bg-background/90 backdrop-blur-sm border shadow-lg">
              <span class="text-sm font-semibold">{{ car.year }}</span>
            </div>
          </div>
          
          <!-- Thumbnail Grid (Placeholder) -->
          <div class="grid grid-cols-4 gap-2">
            <div v-for="i in 4" :key="i" class="aspect-video rounded-lg bg-muted border"></div>
          </div>
        </div>
        
        <!-- Right: Details -->
        <div class="space-y-6">
          <!-- Header -->
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                {{ car.type }}
              </span>
              <div class="flex items-center gap-1">
                <Star class="h-4 w-4 text-yellow-500 fill-yellow-500" />
                <span class="font-semibold">{{ car.rating }}</span>
                <span class="text-sm text-muted-foreground">({{ car.reviews }} ulasan)</span>
              </div>
            </div>
            <h1 class="text-3xl font-bold">{{ car.name }}</h1>
            <p class="text-lg text-muted-foreground">{{ car.brand }} • {{ car.year }}</p>
          </div>
          
          <!-- Description -->
          <p class="text-muted-foreground leading-relaxed">
            {{ car.description }}
          </p>
          
          <!-- Specs -->
          <Card>
            <CardContent class="grid grid-cols-2 gap-4 p-6">
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-950">
                  <Gauge class="h-5 w-5 text-blue-600" />
                </div>
                <div>
                  <div class="text-sm text-muted-foreground">Transmisi</div>
                  <div class="font-semibold">{{ car.transmission }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-950">
                  <Users class="h-5 w-5 text-purple-600" />
                </div>
                <div>
                  <div class="text-sm text-muted-foreground">Kapasitas</div>
                  <div class="font-semibold">{{ car.seats }} Penumpang</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-950">
                  <Fuel class="h-5 w-5 text-green-600" />
                </div>
                <div>
                  <div class="text-sm text-muted-foreground">Bahan Bakar</div>
                  <div class="font-semibold">{{ car.fuel }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-950">
                  <MapPin class="h-5 w-5 text-orange-600" />
                </div>
                <div>
                  <div class="text-sm text-muted-foreground">Status</div>
                  <div class="font-semibold text-green-600">Tersedia</div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <!-- Features -->
          <div>
            <h3 class="font-semibold mb-3">Fitur & Kelengkapan</h3>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="feature in car.features" :key="feature" class="flex items-center gap-2 text-sm">
                <Check class="h-4 w-4 text-green-600" />
                <span>{{ feature }}</span>
              </div>
            </div>
          </div>
          
          <!-- Pricing Options -->
          <Card class="border-2 border-blue-200 dark:border-blue-800">
            <CardContent class="p-6 space-y-4">
              <h3 class="font-semibold">Pilih Opsi Sewa</h3>
              
              <div class="space-y-3">
                <button
                  @click="selectedOption = 'self'"
                  :class="[
                    'w-full p-4 rounded-lg border-2 text-left transition-all',
                    selectedOption === 'self' 
                      ? 'border-blue-600 bg-blue-50 dark:bg-blue-950' 
                      : 'border-border hover:border-blue-300'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="font-semibold">Lepas Kunci (Self Drive)</div>
                      <div class="text-sm text-muted-foreground">Mobil + BBM + Asuransi</div>
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        {{ formatPrice(car.price_per_day) }}
                      </div>
                      <div class="text-xs text-muted-foreground">/hari</div>
                    </div>
                  </div>
                </button>
                
                <button
                  @click="selectedOption = 'driver'"
                  :class="[
                    'w-full p-4 rounded-lg border-2 text-left transition-all',
                    selectedOption === 'driver' 
                      ? 'border-purple-600 bg-purple-50 dark:bg-purple-950' 
                      : 'border-border hover:border-purple-300'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="font-semibold">Dengan Sopir</div>
                      <div class="text-sm text-muted-foreground">Mobil + Sopir + BBM + Asuransi</div>
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        {{ formatPrice(car.with_driver_price_per_day) }}
                      </div>
                      <div class="text-xs text-muted-foreground">/hari</div>
                    </div>
                  </div>
                </button>
              </div>
              
              <div class="pt-4 space-y-3">
                <Button 
                  size="lg" 
                  class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg"
                >
                  <Clock class="mr-2 h-5 w-5" />
                  Booking Sekarang
                </Button>
                
                <div class="flex items-center gap-2 text-sm text-muted-foreground justify-center">
                  <ShieldCheck class="h-4 w-4 text-green-600" />
                  <span>Pembayaran Aman dengan Midtrans</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
      
      <!-- Additional Info -->
      <div class="mt-16 space-y-8">
        <h2 class="text-2xl font-bold">Syarat & Ketentuan Sewa</h2>
        <div class="grid gap-6 md:grid-cols-3">
          <Card>
            <CardContent class="p-6 space-y-3">
              <ShieldCheck class="h-8 w-8 text-blue-600" />
              <h3 class="font-semibold">Persyaratan</h3>
              <ul class="text-sm text-muted-foreground space-y-2">
                <li>• KTP & SIM A yang masih berlaku</li>
                <li>• Usia minimal 21 tahun</li>
                <li>• Deposit sesuai ketentuan</li>
              </ul>
            </CardContent>
          </Card>
          <Card>
            <CardContent class="p-6 space-y-3">
              <Clock class="h-8 w-8 text-purple-600" />
              <h3 class="font-semibold">Durasi Sewa</h3>
              <ul class="text-sm text-muted-foreground space-y-2">
                <li>• Minimal sewa 1 hari (24 jam)</li>
                <li>• Overtime dikenakan biaya tambahan</li>
                <li>• Diskon untuk sewa mingguan/bulanan</li>
              </ul>
            </CardContent>
          </Card>
          <Card>
            <CardContent class="p-6 space-y-3">
              <MapPin class="h-8 w-8 text-green-600" />
              <h3 class="font-semibold">Area Layanan</h3>
              <ul class="text-sm text-muted-foreground space-y-2">
                <li>• Pengantaran gratis area Jakarta</li>
                <li>• Layanan antar ke luar kota tersedia</li>
                <li>• Bebas keluar kota dengan pemberitahuan</li>
              </ul>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
