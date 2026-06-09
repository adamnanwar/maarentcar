<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Car as CarIcon, Shield, Users, Clock, MapPin, Phone, Mail, Building } from 'lucide-vue-next'

const features = [
  {
    icon: CarIcon,
    title: 'Armada Berkualitas',
    description: 'Semua mobil kami terawat dengan baik dan rutin diservis untuk kenyamanan perjalanan Anda.',
  },
  {
    icon: Shield,
    title: 'Asuransi Lengkap',
    description: 'Setiap rental dilengkapi dengan asuransi untuk keamanan dan ketenangan pikiran Anda.',
  },
  {
    icon: Users,
    title: 'Driver Profesional',
    description: 'Tim driver kami berpengalaman, ramah, dan mengenal rute perjalanan dengan baik.',
  },
  {
    icon: Clock,
    title: 'Layanan 24 Jam',
    description: 'Kami siap melayani Anda kapan saja dengan layanan customer service 24 jam.',
  },
]
</script>

<template>
  <Head title="Tentang Kami - MaaRentCar" />

  <MainLayout>
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#362EED] to-[#6366f1] text-white py-16 lg:py-24">
      <div class="max-w-4xl mx-auto px-4 lg:px-8 text-center">
        <h1 class="text-3xl lg:text-5xl font-bold mb-4">Tentang MaaRentCar</h1>
        <p class="text-lg text-white/90 max-w-2xl mx-auto">
          Partner terpercaya untuk kebutuhan rental mobil dan paket wisata Anda. Melayani dengan sepenuh hati sejak 2020.
        </p>
      </div>
    </section>

    <!-- About Content -->
    <section class="py-12 lg:py-20">
      <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">
          <div>
            <h2 class="text-2xl lg:text-3xl font-bold text-[#060521] mb-4">
              Perjalanan Anda, Prioritas Kami
            </h2>
            <div class="space-y-4 text-gray-600">
              <p>
                MaaRentCar hadir sebagai solusi rental mobil yang mengutamakan kenyamanan dan kepuasan pelanggan. Kami memahami bahwa setiap perjalanan memiliki kebutuhan yang berbeda, itulah mengapa kami menyediakan berbagai pilihan mobil dan paket wisata yang dapat disesuaikan.
              </p>
              <p>
                Dengan armada yang terawat, driver profesional, dan layanan pelanggan yang responsif, kami berkomitmen untuk memberikan pengalaman rental mobil terbaik untuk Anda dan keluarga.
              </p>
              <p>
                Baik untuk keperluan bisnis, liburan keluarga, atau wisata bersama teman, MaaRentCar siap menemani perjalanan Anda dengan aman dan nyaman.
              </p>
            </div>
          </div>
          <div class="bg-gradient-to-br from-[#362EED]/10 to-[#6366f1]/10 rounded-2xl p-8 flex items-center justify-center">
            <CarIcon class="w-48 h-48 text-[#362EED]/30" />
          </div>
        </div>

        <!-- Features -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
          <Card v-for="feature in features" :key="feature.title" class="text-center">
            <CardContent class="p-6">
              <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-[#362EED]/10 flex items-center justify-center">
                <component :is="feature.icon" class="w-7 h-7 text-[#362EED]" />
              </div>
              <h3 class="font-bold text-[#060521] mb-2">{{ feature.title }}</h3>
              <p class="text-sm text-gray-500">{{ feature.description }}</p>
            </CardContent>
          </Card>
        </div>

        <!-- Contact Info -->
        <Card>
          <CardContent class="p-8">
            <h2 class="text-2xl font-bold text-[#060521] mb-6 text-center">Hubungi Kami</h2>
            <div class="grid md:grid-cols-3 gap-6">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#362EED]/10 flex items-center justify-center flex-shrink-0">
                  <MapPin class="w-6 h-6 text-[#362EED]" />
                </div>
                <div>
                  <p class="font-semibold text-[#060521]">Alamat</p>
                  <p class="text-sm text-gray-500">Jl. Contoh No. 123, Kota, Indonesia</p>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#362EED]/10 flex items-center justify-center flex-shrink-0">
                  <Phone class="w-6 h-6 text-[#362EED]" />
                </div>
                <div>
                  <p class="font-semibold text-[#060521]">Telepon</p>
                  <p class="text-sm text-gray-500">+62 812 3456 7890</p>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#362EED]/10 flex items-center justify-center flex-shrink-0">
                  <Mail class="w-6 h-6 text-[#362EED]" />
                </div>
                <div>
                  <p class="font-semibold text-[#060521]">Email</p>
                  <p class="text-sm text-gray-500">info@maarentcar.com</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </section>
  </MainLayout>
</template>
