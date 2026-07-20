<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FileUpload from '@/components/FileUpload.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
}

defineProps<{ categories: Category[] }>();

const form = useForm({
    category_id: '',
    name: '',
    brand: '',
    model: '',
    year: new Date().getFullYear(),
    plate_number: '',
    transmission: 'manual',
    fuel_type: 'bensin',
    seat_capacity: 5,
    price_per_day: 0,
    driver_fee_per_day: 0,
    base_delivery_fee: 0,
    description: '',
    status: 'tersedia',
    is_active: true as boolean,
    images: [] as File[],
});

function submit() {
    form.post('/admin/mobil', { forceFormData: true });
}
</script>

<template>
    <Head title="Tambah Mobil" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Tambah Mobil</h1>

        <form class="mt-6 max-w-3xl space-y-6 rounded-xl border border-border bg-background p-6" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <Label for="category_id">Kategori</Label>
                    <select id="category_id" v-model="form.category_id" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm" required>
                        <option value="" disabled>Pilih kategori</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-destructive">{{ form.errors.category_id }}</p>
                </div>
                <div>
                    <Label for="name">Nama Mobil</Label>
                    <Input id="name" v-model="form.name" class="mt-1" required />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
                </div>
                <div>
                    <Label for="brand">Merek</Label>
                    <Input id="brand" v-model="form.brand" class="mt-1" />
                </div>
                <div>
                    <Label for="model">Model</Label>
                    <Input id="model" v-model="form.model" class="mt-1" />
                </div>
                <div>
                    <Label for="year">Tahun</Label>
                    <Input id="year" v-model.number="form.year" type="number" class="mt-1" />
                </div>
                <div>
                    <Label for="plate_number">Plat Nomor (internal)</Label>
                    <Input id="plate_number" v-model="form.plate_number" class="mt-1" required />
                    <p v-if="form.errors.plate_number" class="mt-1 text-sm text-destructive">{{ form.errors.plate_number }}</p>
                </div>
                <div>
                    <Label for="transmission">Transmisi</Label>
                    <select id="transmission" v-model="form.transmission" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm">
                        <option value="manual">Manual</option>
                        <option value="automatic">Automatic</option>
                    </select>
                </div>
                <div>
                    <Label for="fuel_type">Bahan Bakar</Label>
                    <select id="fuel_type" v-model="form.fuel_type" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm">
                        <option value="bensin">Bensin</option>
                        <option value="diesel">Diesel</option>
                        <option value="listrik">Listrik</option>
                    </select>
                </div>
                <div>
                    <Label for="seat_capacity">Kapasitas Kursi</Label>
                    <Input id="seat_capacity" v-model.number="form.seat_capacity" type="number" class="mt-1" required />
                </div>
                <div>
                    <Label for="status">Status</Label>
                    <select id="status" v-model="form.status" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm">
                        <option value="tersedia">Tersedia</option>
                        <option value="perawatan">Perawatan</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
                <div>
                    <Label for="price_per_day">Harga Sewa/Hari (Rp)</Label>
                    <Input id="price_per_day" v-model.number="form.price_per_day" type="number" class="mt-1" required />
                    <p v-if="form.errors.price_per_day" class="mt-1 text-sm text-destructive">{{ form.errors.price_per_day }}</p>
                </div>
                <div>
                    <Label for="driver_fee_per_day">Biaya Supir/Hari (Rp)</Label>
                    <Input id="driver_fee_per_day" v-model.number="form.driver_fee_per_day" type="number" class="mt-1" />
                </div>
                <div>
                    <Label for="base_delivery_fee">Biaya Antar Dasar (Rp)</Label>
                    <Input id="base_delivery_fee" v-model.number="form.base_delivery_fee" type="number" class="mt-1" />
                </div>
            </div>

            <div>
                <Label for="description">Deskripsi</Label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input" />
                    Tampilkan mobil ini di katalog publik
                </label>
            </div>

            <div>
                <Label for="images">Foto Mobil</Label>
                <FileUpload
                    v-model="form.images"
                    accept="image/*"
                    multiple
                    label="Klik atau seret beberapa foto mobil ke sini"
                    hint="Format PNG/JPG, maksimal 4MB per foto."
                    class="mt-1"
                />
                <p v-if="form.errors.images" class="mt-1 text-sm text-destructive">{{ form.errors.images }}</p>
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Simpan</Button>
                <a href="/admin/mobil"><Button type="button" variant="outline">Batal</Button></a>
            </div>
        </form>
    </AdminLayout>
</template>
