<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FileUpload from '@/components/FileUpload.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Vehicle {
    id: number;
    name: string;
}

interface Destination {
    id: number;
    name: string;
}

defineProps<{ vehicles: Vehicle[]; destinations: Destination[] }>();

const form = useForm({
    vehicle_id: '',
    name: '',
    seat_capacity: 6,
    duration_days: 1,
    driver_included: true as boolean,
    price: 0,
    description: '',
    is_active: true as boolean,
    image: null as File | null,
    destination_ids: [] as number[],
});

function submit() {
    form.post('/admin/paket-wisata', { forceFormData: true });
}
</script>

<template>
    <Head title="Tambah Paket Wisata" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Tambah Paket Wisata</h1>

        <form class="mt-6 max-w-2xl space-y-4 rounded-xl border border-border bg-background p-6" @submit.prevent="submit">
            <div>
                <Label for="name">Nama Paket</Label>
                <Input id="name" v-model="form.name" class="mt-1" required autofocus />
                <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <Label for="vehicle_id">Mobil Representatif</Label>
                    <select id="vehicle_id" v-model="form.vehicle_id" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm">
                        <option value="">Sesuai kategori</option>
                        <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id">{{ vehicle.name }}</option>
                    </select>
                </div>
                <div>
                    <Label for="seat_capacity">Kapasitas Kursi</Label>
                    <Input id="seat_capacity" v-model.number="form.seat_capacity" type="number" class="mt-1" required />
                </div>
                <div>
                    <Label for="duration_days">Durasi (hari)</Label>
                    <Input id="duration_days" v-model.number="form.duration_days" type="number" class="mt-1" required />
                </div>
            </div>

            <div>
                <Label for="price">Harga Paket (Rp)</Label>
                <Input id="price" v-model.number="form.price" type="number" class="mt-1" required />
                <p v-if="form.errors.price" class="mt-1 text-sm text-destructive">{{ form.errors.price }}</p>
            </div>

            <div>
                <Label for="description">Deskripsi</Label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
            </div>

            <div>
                <Label>Destinasi yang Termasuk</Label>
                <div class="mt-2 grid max-h-48 grid-cols-2 gap-2 overflow-y-auto rounded-md border border-input p-3">
                    <label v-for="destination in destinations" :key="destination.id" class="flex items-center gap-2 text-sm">
                        <input v-model="form.destination_ids" type="checkbox" :value="destination.id" class="rounded border-input" />
                        {{ destination.name }}
                    </label>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input" />
                    Tampilkan paket ini secara publik
                </label>
            </div>

            <div>
                <Label for="image">Foto Paket</Label>
                <FileUpload
                    v-model="form.image"
                    accept="image/*"
                    label="Klik atau seret foto paket ke sini"
                    hint="Format PNG/JPG, maksimal 4MB."
                    class="mt-1"
                />
                <p v-if="form.errors.image" class="mt-1 text-sm text-destructive">{{ form.errors.image }}</p>
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Simpan</Button>
                <a href="/admin/paket-wisata"><Button type="button" variant="outline">Batal</Button></a>
            </div>
        </form>
    </AdminLayout>
</template>
