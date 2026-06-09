<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ArrowLeft, Upload, MapPin, Plus, Trash2 } from 'lucide-vue-next'
import { ref } from 'vue'

interface Itinerary {
  id?: string
  day_number: number
  activity: string
  time: string
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

const form = useForm({
  name: props.package.name,
  price: props.package.price,
  duration_days: props.package.duration_days,
  description: props.package.description || '',
  is_active: props.package.is_active,
  thumbnail: null as File | null,
  itineraries: props.package.itineraries.map(i => ({
    day_number: i.day_number,
    activity: i.activity,
    time: i.time || '',
  })) as Itinerary[],
})

const imagePreview = ref<string | null>(props.package.thumbnail_url)

const handleImageUpload = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    form.thumbnail = target.files[0]
    imagePreview.value = URL.createObjectURL(target.files[0])
  }
}

const addItinerary = () => {
  const nextDay = form.itineraries.length > 0
    ? Math.max(...form.itineraries.map(i => i.day_number)) + 1
    : 1
  form.itineraries.push({
    day_number: nextDay > form.duration_days ? form.duration_days : nextDay,
    activity: '',
    time: '',
  })
}

const removeItinerary = (index: number) => {
  form.itineraries.splice(index, 1)
}

const submit = () => {
  form.post(`/admin/packages/${props.package.id}`, {
    forceFormData: true,
    _method: 'PUT',
  } as any)
}
</script>

<template>
  <Head title="Edit Paket Wisata - Admin" />

  <MainLayout>
    <div class="p-6 lg:p-8 max-w-3xl mx-auto">
      <!-- Back Button -->
      <Link href="/admin/packages" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-[#060521] mb-6 group">
        <ArrowLeft class="h-4 w-4 group-hover:-translate-x-1 transition-transform" />
        Kembali ke Daftar Paket
      </Link>

      <Card>
        <CardHeader>
          <CardTitle>Edit Paket Wisata</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Image Upload -->
            <div>
              <Label>Thumbnail Paket</Label>
              <div class="mt-2">
                <div class="border-2 border-dashed rounded-xl p-6 text-center">
                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    id="thumbnail"
                    @change="handleImageUpload"
                  />
                  <label for="thumbnail" class="cursor-pointer">
                    <div v-if="imagePreview" class="mb-4">
                      <img :src="imagePreview" class="h-40 mx-auto rounded-lg object-cover" />
                    </div>
                    <div v-else class="mb-4">
                      <MapPin class="w-16 h-16 mx-auto text-gray-300" />
                    </div>
                    <Button type="button" variant="outline">
                      <Upload class="w-4 h-4 mr-2" />
                      {{ imagePreview ? 'Ganti Foto' : 'Upload Foto' }}
                    </Button>
                  </label>
                </div>
                <p v-if="form.errors.thumbnail" class="text-sm text-red-500 mt-1">{{ form.errors.thumbnail }}</p>
              </div>
            </div>

            <!-- Name -->
            <div class="space-y-2">
              <Label for="name">Nama Paket *</Label>
              <Input
                id="name"
                v-model="form.name"
                placeholder="Contoh: Paket Wisata Bromo 3D2N"
                required
              />
              <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
              <!-- Price -->
              <div class="space-y-2">
                <Label for="price">Harga Paket (Rp) *</Label>
                <Input
                  id="price"
                  type="number"
                  v-model.number="form.price"
                  min="0"
                  step="50000"
                  required
                />
                <p v-if="form.errors.price" class="text-sm text-red-500">{{ form.errors.price }}</p>
              </div>

              <!-- Duration -->
              <div class="space-y-2">
                <Label for="duration_days">Durasi (Hari) *</Label>
                <Input
                  id="duration_days"
                  type="number"
                  v-model.number="form.duration_days"
                  min="1"
                  max="30"
                  required
                />
                <p v-if="form.errors.duration_days" class="text-sm text-red-500">{{ form.errors.duration_days }}</p>
              </div>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">Deskripsi</Label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#362EED] resize-none"
                placeholder="Jelaskan tentang paket wisata ini..."
              />
              <p v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</p>
            </div>

            <!-- Itineraries -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <Label>Rencana Perjalanan</Label>
                <Button type="button" variant="outline" size="sm" @click="addItinerary">
                  <Plus class="w-4 h-4 mr-1" />
                  Tambah Aktivitas
                </Button>
              </div>

              <div v-if="form.itineraries.length > 0" class="space-y-3">
                <div
                  v-for="(itinerary, index) in form.itineraries"
                  :key="index"
                  class="p-4 border rounded-lg space-y-3"
                >
                  <div class="flex items-center justify-between">
                    <span class="font-medium text-sm">Aktivitas {{ index + 1 }}</span>
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      @click="removeItinerary(index)"
                      class="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>

                  <div class="grid grid-cols-4 gap-3">
                    <div class="space-y-1">
                      <Label class="text-xs">Hari ke-</Label>
                      <Input
                        type="number"
                        v-model.number="itinerary.day_number"
                        min="1"
                        :max="form.duration_days"
                      />
                    </div>
                    <div class="space-y-1">
                      <Label class="text-xs">Waktu</Label>
                      <Input
                        v-model="itinerary.time"
                        placeholder="08:00"
                      />
                    </div>
                    <div class="col-span-2 space-y-1">
                      <Label class="text-xs">Aktivitas</Label>
                      <Input
                        v-model="itinerary.activity"
                        placeholder="Nama aktivitas..."
                      />
                    </div>
                  </div>
                </div>
              </div>

              <p v-else class="text-sm text-gray-500 text-center py-4 border-2 border-dashed rounded-lg">
                Belum ada aktivitas. Klik "Tambah Aktivitas" untuk menambahkan rencana perjalanan.
              </p>
            </div>

            <!-- Active -->
            <div class="flex items-center gap-2">
              <input
                id="is_active"
                type="checkbox"
                v-model="form.is_active"
                class="rounded border-gray-300 text-[#362EED] focus:ring-[#362EED]"
              />
              <Label for="is_active" class="font-normal cursor-pointer">Aktifkan paket wisata</Label>
            </div>

            <!-- Submit -->
            <div class="flex gap-3 pt-4">
              <Link href="/admin/packages" class="flex-1">
                <Button type="button" variant="outline" class="w-full">Batal</Button>
              </Link>
              <Button
                type="submit"
                class="flex-1 bg-[#362EED] hover:bg-[#2a24c4]"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Menyimpan...' : 'Update Paket' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </MainLayout>
</template>
