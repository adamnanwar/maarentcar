<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { carService } from '@/services/carService'
import type { Car } from '@/types/models'
import {
  Plus,
  Pencil,
  Trash2,
  Loader2,
  ArrowLeft,
  Search,
  Car as CarIcon,
  Upload,
  X,
} from 'lucide-vue-next'

const cars = ref<Car[]>([])
const loading = ref(true)
const searchQuery = ref('')

// Dialog states
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const showDeleteDialog = ref(false)
const formLoading = ref(false)
const formError = ref<string | null>(null)

// Form data
const editingCar = ref<Car | null>(null)
const deletingCar = ref<Car | null>(null)

const defaultFormData = {
  name: '',
  brand: '',
  type: 'SUV' as string,
  transmission: 'Automatic' as string,
  seats: 5,
  price_per_day: 0,
  with_driver_price_per_day: 0,
  is_active: true,
}

const formData = ref({ ...defaultFormData })
const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const carTypes = ['SUV', 'Sedan', 'MPV', 'Hatchback', 'Luxury']
const transmissions = ['Automatic', 'Manual']

const filteredCars = computed(() => {
  if (!searchQuery.value) return cars.value
  const query = searchQuery.value.toLowerCase()
  return cars.value.filter(
    (c) =>
      c.name.toLowerCase().includes(query) ||
      c.brand?.toLowerCase().includes(query) ||
      c.type?.toLowerCase().includes(query)
  )
})

onMounted(async () => {
  await fetchCars()
})

async function fetchCars() {
  loading.value = true
  try {
    cars.value = await carService.list({ active_only: false })
  } catch (e) {
    console.error('Failed to fetch cars:', e)
  } finally {
    loading.value = false
  }
}

function formatPrice(price: number) {
  return `Rp ${price.toLocaleString('id-ID')}`
}

function openCreateDialog() {
  formData.value = { ...defaultFormData }
  imageFile.value = null
  imagePreview.value = null
  formError.value = null
  showCreateDialog.value = true
}

function openEditDialog(car: Car) {
  editingCar.value = car
  formData.value = {
    name: car.name,
    brand: car.brand || '',
    type: car.type || 'SUV',
    transmission: car.transmission || 'Automatic',
    seats: car.seats || 5,
    price_per_day: car.price_per_day,
    with_driver_price_per_day: car.with_driver_price_per_day || 0,
    is_active: car.is_active,
  }
  imageFile.value = null
  imagePreview.value = car.thumbnail_url || null
  formError.value = null
  showEditDialog.value = true
}

function openDeleteDialog(car: Car) {
  deletingCar.value = car
  showDeleteDialog.value = true
}

function handleImageChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

function removeImage() {
  imageFile.value = null
  imagePreview.value = editingCar.value?.thumbnail_url || null
}

async function handleCreate() {
  formLoading.value = true
  formError.value = null

  try {
    await carService.create({
      ...formData.value,
      image: imageFile.value || undefined,
    })
    showCreateDialog.value = false
    await fetchCars()
  } catch (e: unknown) {
    if (e instanceof Error) {
      formError.value = e.message
    } else {
      formError.value = 'Gagal menambah mobil'
    }
  } finally {
    formLoading.value = false
  }
}

async function handleUpdate() {
  if (!editingCar.value) return

  formLoading.value = true
  formError.value = null

  try {
    await carService.update(editingCar.value.id, {
      ...formData.value,
      image: imageFile.value || undefined,
    })
    showEditDialog.value = false
    editingCar.value = null
    await fetchCars()
  } catch (e: unknown) {
    if (e instanceof Error) {
      formError.value = e.message
    } else {
      formError.value = 'Gagal mengupdate mobil'
    }
  } finally {
    formLoading.value = false
  }
}

async function handleDelete() {
  if (!deletingCar.value) return

  formLoading.value = true
  formError.value = null

  try {
    await carService.delete(deletingCar.value.id)
    showDeleteDialog.value = false
    deletingCar.value = null
    await fetchCars()
  } catch (e: unknown) {
    if (e instanceof Error) {
      formError.value = e.message
    } else {
      formError.value = 'Gagal menghapus mobil'
    }
  } finally {
    formLoading.value = false
  }
}
</script>

<template>
  <Head title="Kelola Mobil - Admin" />

  <MainLayout>
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-6 lg:py-10">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
          <Link href="/admin">
            <Button variant="ghost" size="icon">
              <ArrowLeft class="w-5 h-5" />
            </Button>
          </Link>
          <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-[#060521]">Kelola Mobil</h1>
            <p class="text-gray-500">{{ cars.length }} mobil terdaftar</p>
          </div>
        </div>
        <Button @click="openCreateDialog" class="bg-[#362EED] hover:bg-[#362EED]/90">
          <Plus class="w-4 h-4 mr-2" />
          Tambah Mobil
        </Button>
      </div>

      <!-- Search -->
      <div class="relative mb-6">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
        <Input
          v-model="searchQuery"
          placeholder="Cari mobil..."
          class="pl-10"
        />
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-20">
        <Loader2 class="w-10 h-10 mx-auto animate-spin text-[#362EED]" />
        <p class="mt-4 text-gray-500">Memuat data...</p>
      </div>

      <!-- Cars Table -->
      <Card v-else>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b bg-gray-50">
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Mobil</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Tipe</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Transmisi</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Kursi</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Harga/Hari</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                  <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="car in filteredCars" :key="car.id" class="border-b last:border-0 hover:bg-gray-50">
                  <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-16 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                        <img
                          v-if="car.thumbnail_url"
                          :src="car.thumbnail_url"
                          :alt="car.name"
                          class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                          <CarIcon class="w-6 h-6 text-gray-400" />
                        </div>
                      </div>
                      <div>
                        <p class="font-medium text-[#060521]">{{ car.name }}</p>
                        <p class="text-sm text-gray-500">{{ car.brand }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4">{{ car.type || '-' }}</td>
                  <td class="px-4 py-4">{{ car.transmission || '-' }}</td>
                  <td class="px-4 py-4">{{ car.seats || '-' }}</td>
                  <td class="px-4 py-4 font-medium">{{ formatPrice(car.price_per_day) }}</td>
                  <td class="px-4 py-4">
                    <Badge :class="car.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                      {{ car.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center justify-end gap-2">
                      <Button variant="ghost" size="icon" @click="openEditDialog(car)">
                        <Pencil class="w-4 h-4" />
                      </Button>
                      <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700" @click="openDeleteDialog(car)">
                        <Trash2 class="w-4 h-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredCars.length === 0">
                  <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                    <CarIcon class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                    <p>Tidak ada mobil ditemukan</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Create Dialog -->
    <Dialog v-model:open="showCreateDialog">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Tambah Mobil Baru</DialogTitle>
          <DialogDescription>Isi data mobil yang akan ditambahkan</DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleCreate" class="space-y-4">
          <div v-if="formError" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm">
            {{ formError }}
          </div>

          <!-- Image Upload -->
          <div class="space-y-2">
            <Label>Foto Mobil</Label>
            <div class="flex items-center gap-4">
              <div class="w-32 h-24 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 relative">
                <img
                  v-if="imagePreview"
                  :src="imagePreview"
                  alt="Preview"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <CarIcon class="w-8 h-8 text-gray-400" />
                </div>
                <button
                  v-if="imagePreview"
                  type="button"
                  @click="removeImage"
                  class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                >
                  <X class="w-4 h-4" />
                </button>
              </div>
              <label class="cursor-pointer">
                <input type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                <div class="flex items-center gap-2 px-4 py-2 border rounded-lg hover:bg-gray-50">
                  <Upload class="w-4 h-4" />
                  <span>Upload Foto</span>
                </div>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="name">Nama Mobil</Label>
              <Input id="name" v-model="formData.name" required />
            </div>
            <div class="space-y-2">
              <Label for="brand">Merek</Label>
              <Input id="brand" v-model="formData.brand" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="space-y-2">
              <Label for="type">Tipe</Label>
              <Select v-model="formData.type">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="t in carTypes" :key="t" :value="t">{{ t }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="transmission">Transmisi</Label>
              <Select v-model="formData.transmission">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="t in transmissions" :key="t" :value="t">{{ t }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="seats">Jumlah Kursi</Label>
              <Input id="seats" v-model="formData.seats" type="number" min="2" max="12" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="price">Harga per Hari (Lepas Kunci)</Label>
              <Input id="price" v-model="formData.price_per_day" type="number" min="0" required />
            </div>
            <div class="space-y-2">
              <Label for="driver_price">Harga per Hari (Dengan Supir)</Label>
              <Input id="driver_price" v-model="formData.with_driver_price_per_day" type="number" min="0" />
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" v-model="formData.is_active" class="rounded" />
            <Label for="is_active">Aktif (tersedia untuk disewa)</Label>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="showCreateDialog = false">Batal</Button>
            <Button type="submit" :disabled="formLoading" class="bg-[#362EED] hover:bg-[#362EED]/90">
              <Loader2 v-if="formLoading" class="w-4 h-4 mr-2 animate-spin" />
              Simpan
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Edit Dialog -->
    <Dialog v-model:open="showEditDialog">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Edit Mobil</DialogTitle>
          <DialogDescription>Ubah data mobil {{ editingCar?.name }}</DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleUpdate" class="space-y-4">
          <div v-if="formError" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm">
            {{ formError }}
          </div>

          <!-- Image Upload -->
          <div class="space-y-2">
            <Label>Foto Mobil</Label>
            <div class="flex items-center gap-4">
              <div class="w-32 h-24 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 relative">
                <img
                  v-if="imagePreview"
                  :src="imagePreview"
                  alt="Preview"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <CarIcon class="w-8 h-8 text-gray-400" />
                </div>
                <button
                  v-if="imageFile"
                  type="button"
                  @click="removeImage"
                  class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center"
                >
                  <X class="w-4 h-4" />
                </button>
              </div>
              <label class="cursor-pointer">
                <input type="file" accept="image/*" class="hidden" @change="handleImageChange" />
                <div class="flex items-center gap-2 px-4 py-2 border rounded-lg hover:bg-gray-50">
                  <Upload class="w-4 h-4" />
                  <span>Ganti Foto</span>
                </div>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit-name">Nama Mobil</Label>
              <Input id="edit-name" v-model="formData.name" required />
            </div>
            <div class="space-y-2">
              <Label for="edit-brand">Merek</Label>
              <Input id="edit-brand" v-model="formData.brand" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="space-y-2">
              <Label for="edit-type">Tipe</Label>
              <Select v-model="formData.type">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="t in carTypes" :key="t" :value="t">{{ t }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="edit-transmission">Transmisi</Label>
              <Select v-model="formData.transmission">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="t in transmissions" :key="t" :value="t">{{ t }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="edit-seats">Jumlah Kursi</Label>
              <Input id="edit-seats" v-model="formData.seats" type="number" min="2" max="12" required />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="edit-price">Harga per Hari (Lepas Kunci)</Label>
              <Input id="edit-price" v-model="formData.price_per_day" type="number" min="0" required />
            </div>
            <div class="space-y-2">
              <Label for="edit-driver_price">Harga per Hari (Dengan Supir)</Label>
              <Input id="edit-driver_price" v-model="formData.with_driver_price_per_day" type="number" min="0" />
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input type="checkbox" id="edit-is_active" v-model="formData.is_active" class="rounded" />
            <Label for="edit-is_active">Aktif (tersedia untuk disewa)</Label>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="showEditDialog = false">Batal</Button>
            <Button type="submit" :disabled="formLoading" class="bg-[#362EED] hover:bg-[#362EED]/90">
              <Loader2 v-if="formLoading" class="w-4 h-4 mr-2 animate-spin" />
              Update
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Hapus Mobil</DialogTitle>
          <DialogDescription>
            Apakah Anda yakin ingin menghapus mobil "{{ deletingCar?.name }}"?
            Tindakan ini tidak dapat dibatalkan.
          </DialogDescription>
        </DialogHeader>

        <div v-if="formError" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm">
          {{ formError }}
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="showDeleteDialog = false">Batal</Button>
          <Button
            type="button"
            variant="destructive"
            :disabled="formLoading"
            @click="handleDelete"
          >
            <Loader2 v-if="formLoading" class="w-4 h-4 mr-2 animate-spin" />
            Hapus
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </MainLayout>
</template>
