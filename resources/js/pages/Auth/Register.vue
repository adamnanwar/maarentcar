<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { UserPlus, Mail, Lock, Eye, EyeOff, User, Phone } from 'lucide-vue-next'
import { ref } from 'vue'

const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/register', {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <Head title="Daftar - MaaRentCar" />

  <MainLayout>
    <div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
      <Card class="w-full max-w-md">
        <CardHeader class="text-center">
          <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#362EED] flex items-center justify-center">
            <UserPlus class="w-8 h-8 text-white" />
          </div>
          <CardTitle class="text-2xl">Buat Akun Baru</CardTitle>
          <CardDescription>Daftar untuk mulai booking mobil</CardDescription>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submit" class="space-y-4">
            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">Nama Lengkap</Label>
              <div class="relative">
                <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Input
                  id="name"
                  type="text"
                  v-model="form.name"
                  placeholder="Nama lengkap Anda"
                  class="pl-10"
                  required
                  autofocus
                />
              </div>
              <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

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
                />
              </div>
              <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Phone -->
            <div class="space-y-2">
              <Label for="phone">No. Telepon</Label>
              <div class="relative">
                <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Input
                  id="phone"
                  type="tel"
                  v-model="form.phone"
                  placeholder="08123456789"
                  class="pl-10"
                  required
                />
              </div>
              <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
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
                  placeholder="Minimal 8 karakter"
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

            <!-- Password Confirmation -->
            <div class="space-y-2">
              <Label for="password_confirmation">Konfirmasi Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <Input
                  id="password_confirmation"
                  :type="showPasswordConfirm ? 'text' : 'password'"
                  v-model="form.password_confirmation"
                  placeholder="Ulangi password"
                  class="pl-10 pr-10"
                  required
                />
                <button
                  type="button"
                  @click="showPasswordConfirm = !showPasswordConfirm"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                >
                  <EyeOff v-if="showPasswordConfirm" class="w-5 h-5" />
                  <Eye v-else class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Submit -->
            <Button
              type="submit"
              class="w-full bg-[#362EED] hover:bg-[#2a24c4]"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Memproses...' : 'Daftar' }}
            </Button>

            <!-- Login Link -->
            <p class="text-center text-sm text-gray-500">
              Sudah punya akun?
              <Link href="/login" class="text-[#362EED] hover:underline font-medium">
                Masuk di sini
              </Link>
            </p>
          </form>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
