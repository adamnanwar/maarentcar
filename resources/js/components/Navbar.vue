<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Menu, X } from 'lucide-vue-next'
import { ref } from 'vue'

const mobileMenuOpen = ref(false)
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/60">
    <div class="container">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo -->
        <Link href="/" class="flex items-center space-x-3 group">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white font-bold text-sm shadow-sm">
            MR
          </div>
          <span class="hidden font-bold text-lg sm:inline-block text-foreground">
            MaaRentCar
          </span>
        </Link>
        
        <!-- Desktop Nav -->
        <nav class="hidden md:flex items-center gap-x-6 text-sm font-medium">
          <Link href="/" class="text-muted-foreground hover:text-foreground transition-colors">Beranda</Link>
          <Link href="/cars" class="text-muted-foreground hover:text-foreground transition-colors">Daftar Mobil</Link>
          <Link href="/about" class="text-muted-foreground hover:text-foreground transition-colors">Tentang Kami</Link>
        </nav>
        
        <!-- Desktop Auth Buttons -->
        <div class="hidden md:flex items-center gap-x-3">
          <Link href="/auth/login">
            <Button variant="ghost">Masuk</Button>
          </Link>
          <Link href="/auth/register">
            <Button class="shadow-sm">
              Daftar
            </Button>
          </Link>
        </div>
        
        <!-- Mobile Menu Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-md hover:bg-accent">
          <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
          <X v-else class="h-5 w-5" />
        </button>
      </div>
    </div>
    
    <!-- Mobile Menu -->
    <div v-if="mobileMenuOpen" class="md:hidden border-t bg-white">
      <nav class="container flex flex-col space-y-3 py-4">
        <Link href="/" class="text-sm font-medium text-muted-foreground hover:text-foreground px-2 py-1.5 rounded-md hover:bg-accent">
          Beranda
        </Link>
        <Link href="/cars" class="text-sm font-medium text-muted-foreground hover:text-foreground px-2 py-1.5 rounded-md hover:bg-accent">
          Daftar Mobil
        </Link>
        <Link href="/about" class="text-sm font-medium text-muted-foreground hover:text-foreground px-2 py-1.5 rounded-md hover:bg-accent">
          Tentang Kami
        </Link>
        <div class="flex flex-col space-y-2 pt-3 border-t">
          <Link href="/auth/login">
            <Button variant="outline" class="w-full">Masuk</Button>
          </Link>
          <Link href="/auth/register">
            <Button class="w-full">Daftar</Button>
          </Link>
        </div>
      </nav>
    </div>
  </header>
</template>
