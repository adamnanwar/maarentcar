<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { ArrowLeft, MapPin, Clock, Calendar, Car as CarIcon, CheckCircle } from 'lucide-vue-next'

interface Itinerary {
  id: string
  day_number: number
  activity: string
  time: string | null
}

interface TourPackage {
  id: string
  name: string
  price: number
  duration_days: number
  description: string
  thumbnail_url: string | null
  is_active: boolean
  itineraries: Itinerary[]
}

const props = defineProps<{
  package: TourPackage
}>()

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

// Group itineraries by day
const itinerariesByDay = () => {
  const grouped: Record<number, Itinerary[]> = {}
  props.package.itineraries.forEach(item => {
    if (!grouped[item.day_number]) {
      grouped[item.day_number] = []
    }
    grouped[item.day_number].push(item)
  })
  return grouped
}
</script>

<template>
  <Head :title="`${package.name} - Paket Wisata`" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Back Button -->
      <Link href="/packages" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#060521] mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Paket Wisata
      </Link>

      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Left: Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Hero Image -->
          <div class="aspect-video rounded-2xl overflow-hidden bg-gradient-to-br from-[#362EED]/20 to-[#6366f1]/20 relative">
            <img
              v-if="package.thumbnail_url"
              :src="package.thumbnail_url"
              :alt="package.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="absolute inset-0 flex items-center justify-center">
              <MapPin class="h-32 w-32 text-[#362EED]/30" />
            </div>
          </div>

          <!-- Package Info -->
          <div>
            <div class="flex items-center gap-3 mb-3">
              <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#362EED]/10 text-[#362EED] text-sm font-semibold">
                <Clock class="w-4 h-4" />
                {{ package.duration_days }} Hari
              </span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-bold text-[#060521] mb-4">{{ package.name }}</h1>
            <p class="text-gray-600 leading-relaxed">{{ package.description }}</p>
          </div>

          <!-- Itinerary -->
          <div v-if="Object.keys(itinerariesByDay()).length > 0">
            <h2 class="text-xl font-bold text-[#060521] mb-4">Rencana Perjalanan</h2>

            <div class="space-y-4">
              <div
                v-for="(activities, day) in itinerariesByDay()"
                :key="day"
                class="border rounded-xl overflow-hidden"
              >
                <div class="bg-[#362EED] text-white px-4 py-3">
                  <h3 class="font-semibold">Hari {{ day }}</h3>
                </div>
                <div class="p-4 space-y-3">
                  <div
                    v-for="activity in activities"
                    :key="activity.id"
                    class="flex items-start gap-3"
                  >
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mt-0.5">
                      <CheckCircle class="w-4 h-4 text-green-600" />
                    </div>
                    <div>
                      <p class="font-medium text-[#060521]">{{ activity.activity }}</p>
                      <p v-if="activity.time" class="text-sm text-gray-500">{{ activity.time }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- What's Included -->
          <Card>
            <CardContent class="p-6">
              <h3 class="font-bold text-[#060521] mb-4">Termasuk dalam Paket</h3>
              <div class="grid grid-cols-2 gap-3">
                <div class="flex items-center gap-2 text-sm">
                  <CheckCircle class="w-4 h-4 text-green-600" />
                  <span>Mobil + Driver</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <CheckCircle class="w-4 h-4 text-green-600" />
                  <span>BBM</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <CheckCircle class="w-4 h-4 text-green-600" />
                  <span>Tiket Wisata</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <CheckCircle class="w-4 h-4 text-green-600" />
                  <span>Guide Lokal</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Right: Booking Card -->
        <div class="lg:col-span-1">
          <Card class="sticky top-24 border-2 border-[#362EED]/20">
            <CardContent class="p-6 space-y-4">
              <div>
                <p class="text-sm text-gray-500 mb-1">Harga Paket</p>
                <p class="text-3xl font-bold text-[#362EED]">{{ formatPrice(package.price) }}</p>
                <p class="text-sm text-gray-500">per paket</p>
              </div>

              <div class="py-4 border-y space-y-3">
                <div class="flex items-center gap-3 text-sm">
                  <Clock class="w-5 h-5 text-gray-400" />
                  <span>Durasi: {{ package.duration_days }} hari</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                  <CarIcon class="w-5 h-5 text-gray-400" />
                  <span>Termasuk mobil + driver</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                  <Calendar class="w-5 h-5 text-gray-400" />
                  <span>Jadwal fleksibel</span>
                </div>
              </div>

              <Link href="/cars">
                <Button size="lg" class="w-full bg-[#362EED] hover:bg-[#2a24c4]">
                  <CarIcon class="mr-2 w-5 h-5" />
                  Pilih Mobil & Booking
                </Button>
              </Link>

              <p class="text-xs text-center text-gray-500">
                Pilih mobil terlebih dahulu, lalu tambahkan paket wisata ini saat booking
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
