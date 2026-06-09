<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { LogIn, Mail, Lock } from 'lucide-vue-next'

const form = useForm({
  email: '',
  password: '',
})

const handleSubmit = () => {
  // Will be implemented with backend
  console.log('Login:', form.data())
}
</script>

<template>
  <Head title="Masuk Akun - MaaRentCar" />
  
  <MainLayout>
    <div class="container flex items-center justify-center min-h-[calc(100vh-4rem)] py-12">
      <Card class="w-full max-w-md shadow-2xl">
        <CardHeader class="space-y-4 text-center pb-8">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 shadow-lg">
            <LogIn class="h-8 w-8 text-white" />
          </div>
          <div class="space-y-2">
            <CardTitle class="text-2xl">Selamat Datang Kembali!</CardTitle>
            <CardDescription class="text-base">
              Masuk untuk melanjutkan booking mobil Anda
            </CardDescription>
          </div>
        </CardHeader>
        
        <CardContent>
          <form @submit.prevent="handleSubmit" class="space-y-4">
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
              <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-medium">Password</label>
                <a href="#" class="text-sm text-blue-600 hover:underline">Lupa password?</a>
              </div>
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
            
            <Button 
              type="submit" 
              class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg"
              size="lg"
            >
              Masuk Sekarang
            </Button>
          </form>
          
          <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
              <span class="bg-background px-2 text-muted-foreground">
                Atau
              </span>
            </div>
          </div>
          
          <p class="text-center text-sm text-muted-foreground">
            Belum punya akun?
            <Link href="/auth/register" class="font-medium text-blue-600 hover:underline ml-1">
              Daftar Gratis
            </Link>
          </p>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
