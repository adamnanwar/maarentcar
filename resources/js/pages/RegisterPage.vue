<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { UserPlus, Mail, Lock, User, Phone } from 'lucide-vue-next'

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const handleSubmit = () => {
  // Will be implemented with backend
  console.log('Register:', form.data())
}
</script>

<template>
  <Head title="Daftar Akun Baru - MaaRentCar" />
  
  <MainLayout>
    <div class="container flex items-center justify-center min-h-[calc(100vh-4rem)] py-12">
      <Card class="w-full max-w-md shadow-2xl">
        <CardHeader class="space-y-4 text-center pb-8">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-600 to-pink-600 shadow-lg">
            <UserPlus class="h-8 w-8 text-white" />
          </div>
          <div class="space-y-2">
            <CardTitle class="text-2xl">Buat Akun Baru</CardTitle>
            <CardDescription class="text-base">
              Daftar gratis dan mulai sewa mobil impian Anda
            </CardDescription>
          </div>
        </CardHeader>
        
        <CardContent>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="space-y-2">
              <label for="name" class="text-sm font-medium">Nama Lengkap</label>
              <div class="relative">
                <User class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
                <Input 
                  id="name" 
                  v-model="form.name"
                  type="text" 
                  placeholder="John Doe"
                  class="pl-10"
                  required
                />
              </div>
            </div>
            
            <div class="space-y-2">
              <label for="email" class="text-sm font-medium">Email</label>
              <div class="relative">
                <Mail class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
                <Input 
                  id="email" 
                  v-model="form.email"
                  type="email" 
                  placeholder="nama@email.com"
                  class="pl-10"
                  required
                />
              </div>
            </div>
            
            <div class="space-y-2">
              <label for="phone" class="text-sm font-medium">Nomor Telepon (Opsional)</label>
              <div class="relative">
                <Phone class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
                <Input 
                  id="phone" 
                  v-model="form.phone"
                  type="tel" 
                  placeholder="08123456789"
                  class="pl-10"
                />
              </div>
            </div>
            
            <div class="space-y-2">
              <label for="password" class="text-sm font-medium">Password</label>
              <div class="relative">
                <Lock class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
                <Input 
                  id="password" 
                  v-model="form.password"
                  type="password" 
                  placeholder="••••••••"
                  class="pl-10"
                  required
                />
              </div>
            </div>
            
            <div class="space-y-2">
              <label for="password_confirmation" class="text-sm font-medium">Konfirmasi Password</label>
              <div class="relative">
                <Lock class="absolute left-3 top-2.5 h-5 w-5 text-muted-foreground" />
                <Input 
                  id="password_confirmation" 
                  v-model="form.password_confirmation"
                  type="password" 
                  placeholder="••••••••"
                  class="pl-10"
                  required
                />
              </div>
            </div>
            
            <Button 
              type="submit" 
              class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 shadow-lg"
              size="lg"
            >
              Daftar Sekarang
            </Button>
          </form>
          
          <p class="text-center text-sm text-muted-foreground mt-6">
            Sudah punya akun?
            <Link href="/auth/login" class="font-medium text-purple-600 hover:underline ml-1">
              Masuk di sini
            </Link>
          </p>
          
          <p class="text-xs text-center text-muted-foreground mt-4">
            Dengan mendaftar, Anda menyetujui 
            <a href="#" class="underline hover:text-foreground">Syarat & Ketentuan</a> 
            dan 
            <a href="#" class="underline hover:text-foreground">Kebijakan Privasi</a> kami.
          </p>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
