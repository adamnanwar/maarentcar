<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { LogIn, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next'
import { ref } from 'vue'

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => {
      form.reset('password')
    },
  })
}
</script>

<template>
  <Head title="Masuk - MaaRentCar" />

  <MainLayout>
    <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
      <Card class="w-full max-w-md">
        <CardHeader class="text-center">
          <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#362EED] flex items-center justify-center">
            <LogIn class="w-8 h-8 text-white" />
          </div>
          <CardTitle class="text-2xl">Selamat Datang</CardTitle>
          <CardDescription>Masuk ke akun MaaRentCar Anda</CardDescription>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submit" class="space-y-4">
            <!-- Email -->
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Input
                  id="email"
                  type="email"
                  v-model="form.email"
                  placeholder="nama@email.com"
                  class="pl-10"
                  required
                  autofocus
                />
              </div>
              <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Password -->
            <div class="space-y-2">
              <Label for="password">Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Input
                  id="password"
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  placeholder="Masukkan password"
                  class="pl-10 pr-10"
                  required
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <EyeOff v-if="showPassword" class="w-5 h-5" />
                  <Eye v-else class="w-5 h-5" />
                </button>
              </div>
              <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center gap-2">
              <input
                id="remember"
                type="checkbox"
                v-model="form.remember"
                class="rounded border-gray-300 text-[#362EED] focus:ring-[#362EED]"
              />
              <Label for="remember" class="text-sm font-normal cursor-pointer">Ingat saya</Label>
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              class="w-full bg-[#362EED] hover:bg-[#2a24c4]"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Memproses...' : 'Masuk' }}
            </Button>

            <!-- Register Link -->
            <p class="text-center text-sm text-gray-500">
              Belum punya akun?
              <Link href="/register" class="text-[#362EED] hover:underline font-medium">
                Daftar sekarang
              </Link>
            </p>
          </form>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
