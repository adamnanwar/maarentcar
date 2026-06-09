<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'

</script>

<template>
  <Head title="Booking Saya" />
  
  <MainLayout>
    <div class="container py-8">
      <h1 class="text-3xl font-bold mb-6">Booking Saya</h1>
      <div class="rounded-md border p-8 text-center text-muted-foreground">
          Belum ada booking.
          <br>
          <Link href="/cars" class="mt-4 inline-block">
             <Button variant="outline">Cari Mobil</Button>
          </Link>
      </div>
    </div>
  </MainLayout>
</template>
