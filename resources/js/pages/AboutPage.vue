<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Target, Users, Heart, Award } from 'lucide-vue-next'
</script>

<template>
  <Head title="Tentang Kami - MaaRentCar" />
  
  <MainLayout>
    <!-- Header Section -->
    <section class="bg-gradient-to-r from-purple-600 to-pink-600 text-white py-16">
      <div class="container max-w-screen-xl text-center">
        <h1 class="text-4xl md:text-5xl font-bold">
          Tentang MaaRentCar
        </h1>
        <p class="mt-4 text-lg opacity-90 max-w-2xl mx-auto">
          Partner terpercaya untuk semua kebutuhan transportasi Anda
        </p>
      </div>
    </section>

    <!-- Story Section -->
    <section class="container py-16 md:py-24">
      <div class="grid gap-12 lg:grid-cols-2 items-center">
        <div class="space-y-6">
          <h2 class="text-3xl font-bold md:text-4xl">
            Cerita <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Kami</span>
          </h2>
          <div class="prose dark:prose-invert max-w-none space-y-4 text-muted-foreground">
            <p class="text-lg leading-relaxed">
              MaaRentCar didirikan dengan visi untuk menyediakan layanan rental mobil yang berkualitas, 
              terpercaya, dan terjangkau untuk semua kalangan. Kami memahami bahwa setiap perjalanan 
              memiliki makna dan tujuan yang berbeda.
            </p>
            <p class="text-lg leading-relaxed">
              Dengan pengalaman lebih dari 5 tahun di industri rental mobil, kami telah melayani 
              ribuan pelanggan yang puas. Komitmen kami adalah memberikan pengalaman berkendara 
              yang aman, nyaman, dan tak terlupakan.
            </p>
            <p class="text-lg leading-relaxed">
              Setiap mobil dalam armada kami dipilih dengan cermat dan dirawat secara berkala 
              untuk memastikan kondisi terbaik. Tim profesional kami siap membantu Anda 24/7 
              untuk memastikan perjalanan Anda berjalan lancar.
            </p>
          </div>
        </div>
        
        <div class="relative">
          <div class="aspect-square rounded-3xl bg-gradient-to-br from-blue-600/20 to-purple-600/20 backdrop-blur-sm border shadow-2xl overflow-hidden">
            <div class="flex h-full items-center justify-center">
              <Award class="h-32 w-32 text-blue-600/40" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="container py-16 md:py-24">
      <div class="text-center mb-12 space-y-4">
        <h2 class="text-3xl font-bold md:text-4xl">
          Nilai-Nilai <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Kami</span>
        </h2>
        <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
          Prinsip yang kami pegang teguh dalam setiap layanan
        </p>
      </div>
      
      <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
        <Card class="text-center hover:shadow-xl transition-shadow">
          <CardContent class="p-8 space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-600">
              <Target class="h-8 w-8 text-white" />
            </div>
            <h3 class="text-xl font-bold">Profesional</h3>
            <p class="text-muted-foreground">
              Layanan profesional dari tim yang berpengalaman dan terlatih
            </p>
          </CardContent>
        </Card>
        
        <Card class="text-center hover:shadow-xl transition-shadow">
          <CardContent class="p-8 space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600">
              <Heart class="h-8 w-8 text-white" />
            </div>
            <h3 class="text-xl font-bold">Kepuasan Pelanggan</h3>
            <p class="text-muted-foreground">
              Kepuasan Anda adalah prioritas utama kami
            </p>
          </CardContent>
        </Card>
        
        <Card class="text-center hover:shadow-xl transition-shadow">
          <CardContent class="p-8 space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-green-600 to-emerald-600">
              <Award class="h-8 w-8 text-white" />
            </div>
            <h3 class="text-xl font-bold">Kualitas Terjamin</h3>
            <p class="text-muted-foreground">
              Unit mobil terawat dan dalam kondisi prima
            </p>
          </CardContent>
        </Card>
        
        <Card class="text-center hover:shadow-xl transition-shadow">
          <CardContent class="p-8 space-y-4">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-600 to-red-600">
              <Users class="h-8 w-8 text-white" />
            </div>
            <h3 class="text-xl font-bold">Terpercaya</h3>
            <p class="text-muted-foreground">
              Dipercaya oleh ribuan pelanggan di seluruh Indonesia
            </p>
          </CardContent>
        </Card>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="container py-16 md:py-24">
      <div class="rounded-3xl bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 p-8 md:p-12">
        <div class="grid gap-8 md:grid-cols-4 text-white text-center">
          <div>
            <div class="text-4xl font-bold mb-2">5+</div>
            <div class="text-white/90">Tahun Pengalaman</div>
          </div>
          <div>
            <div class="text-4xl font-bold mb-2">50+</div>
            <div class="text-white/90">Unit Mobil</div>
          </div>
          <div>
            <div class="text-4xl font-bold mb-2">1000+</div>
            <div class="text-white/90">Pelanggan Puas</div>
          </div>
          <div>
            <div class="text-4xl font-bold mb-2">4.9/5</div>
            <div class="text-white/90">Rating Pelanggan</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="container py-16 md:py-24">
      <div class="text-center space-y-4">
        <h2 class="text-3xl font-bold md:text-4xl">
          Hubungi <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Kami</span>
        </h2>
        <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
          Punya pertanyaan? Tim kami siap membantu Anda
        </p>
        <div class="pt-8 space-y-4">
          <div class="text-lg">
            <span class="font-semibold">Email:</span> 
            <a href="mailto:info@maarentcar.com" class="text-blue-600 hover:underline ml-2">
              info@maarentcar.com
            </a>
          </div>
          <div class="text-lg">
            <span class="font-semibold">WhatsApp:</span> 
            <a href="https://wa.me/6281234567890" class="text-blue-600 hover:underline ml-2">
              +62 812-3456-7890
            </a>
          </div>
          <div class="text-lg">
            <span class="font-semibold">Jam Operasional:</span> 
            <span class="ml-2">24/7 (Customer Support)</span>
          </div>
        </div>
      </div>
    </section>
  </MainLayout>
</template>
