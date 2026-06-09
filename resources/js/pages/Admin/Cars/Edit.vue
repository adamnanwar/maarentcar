<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Upload, Car as CarIcon } from 'lucide-vue-next'
import { ref } from 'vue'

interface Car {
  id: string
  name: string
  brand: string
  type: string
  transmission: string
  seats: number
  price_per_day: number
  image_url: string | null
  is_available: boolean
}

const props = defineProps<{
  car: Car
}>()

const form = useForm({
  name: props.car.name,
  brand: props.car.brand,
  type: props.car.type,
  transmission: props.car.transmission,
  seats: props.car.seats,
  price_per_day: props.car.price_per_day,
  is_available: props.car.is_available,
  image: null as File | null,
})

const imagePreview = ref<string | null>(props.car.image_url)

const handleImageUpload = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.image = target.files[0]
    imagePreview.value = URL.createObjectURL(target.files[0])
  }
}

const submit = () => {
  form.post(`/admin/cars/${props.car.id}`, {
    forceFormData: true,
    _method: 'PUT',
  } as any)
}

const types = ['SUV', 'MPV', 'Sedan', 'Hatchback', 'Luxury']
const transmissions = ['Manual', 'Automatic']
</script>

<template>
  <Head title="Edit Mobil - Admin" />

  <MainLayout>
    <div class="p-6 lg:p-8 max-w-3xl mx-auto">
      <!-- Back Button -->
      <Link href="/admin/cars" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#060521] mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Daftar Mobil
      </Link>

      <Card>
        <CardHeader>
          <CardTitle>Edit Mobil</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Image Upload -->
            <div>
              <Label>Foto Mobil</Label>
              <div class="mt-2">
                <div class="border-2 border-dashed rounded-xl p-6 text-center">
                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    id="image"
                    @change="handleImageUpload"
                  />
                  <label for="image" class="cursor-pointer">
                    <div v-if="imagePreview" class="mb-4">
                      <img :src="imagePreview" class="h-40 mx-auto rounded-lg object-cover" />
                    </div>
                    <div v-else class="mb-4">
                      <CarIcon class="w-16 h-16 mx-auto text-gray-300" />
                    </div>
                    <Button type="button" variant="outline">
                      <Upload class="w-4 h-4 mr-2" />
                      {{ imagePreview ? 'Ganti Foto' : 'Upload Foto' }}
                    </Button>
                  </label>
                </div>
                <p v-if="form.errors.image" class="text-sm text-red-500 mt-1">{{ form.errors.image }}</p>
              </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <!-- Name -->
              <div class="space-y-2">
                <Label for="name">Nama Mobil *</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  placeholder="Contoh: Toyota Avanza"
                  required
                />
                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
              </div>

              <!-- Brand -->
              <div class="space-y-2">
                <Label for="brand">Merek *</Label>
                <Input
                  id="brand"
                  v-model="form.brand"
                  placeholder="Contoh: Toyota"
                  required
                />
                <p v-if="form.errors.brand" class="text-sm text-red-500">{{ form.errors.brand }}</p>
              </div>

              <!-- Type -->
              <div class="space-y-2">
                <Label for="type">Tipe *</Label>
                <select
                  id="type"
                  v-model="form.type"
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#362EED]"
                  required
                >
                  <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
                </select>
                <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
              </div>

              <!-- Transmission -->
              <div class="space-y-2">
                <Label for="transmission">Transmisi *</Label>
                <select
                  id="transmission"
                  v-model="form.transmission"
                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#362EED]"
                  required
                >
                  <option v-for="trans in transmissions" :key="trans" :value="trans">{{ trans }}</option>
                </select>
                <p v-if="form.errors.transmission" class="text-sm text-red-500">{{ form.errors.transmission }}</p>
              </div>

              <!-- Seats -->
              <div class="space-y-2">
                <Label for="seats">Jumlah Kursi *</Label>
                <Input
                  id="seats"
                  type="number"
                  v-model.number="form.seats"
                  min="2"
                  max="12"
                  required
                />
                <p v-if="form.errors.seats" class="text-sm text-red-500">{{ form.errors.seats }}</p>
              </div>

              <!-- Price -->
              <div class="space-y-2">
                <Label for="price_per_day">Harga per Hari (Rp) *</Label>
                <Input
                  id="price_per_day"
                  type="number"
                  v-model.number="form.price_per_day"
                  min="0"
                  step="50000"
                  required
                />
                <p v-if="form.errors.price_per_day" class="text-sm text-red-500">{{ form.errors.price_per_day }}</p>
              </div>
            </div>

            <!-- Availability -->
            <div class="flex items-center gap-2">
              <input
                id="is_available"
                type="checkbox"
                v-model="form.is_available"
                class="rounded border-gray-300 text-[#362EED] focus:ring-[#362EED]"
              />
              <Label for="is_available" class="font-normal cursor-pointer">Tersedia untuk disewa</Label>
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-4">
              <Link href="/admin/cars" class="flex-1">
                <Button type="button" variant="outline" class="w-full">Batal</Button>
              </Link>
              <Button
                type="submit"
                class="flex-1 bg-[#362EED] hover:bg-[#2a24c4]"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Menyimpan...' : 'Update Mobil' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
