<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FileUpload from '@/components/FileUpload.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Destination {
    id: number;
    name: string;
    category: string | null;
    description: string | null;
    address: string | null;
    addon_price: number;
    is_active: boolean;
    image_path: string | null;
}

const props = defineProps<{ destination: Destination }>();

const form = useForm({
    name: props.destination.name,
    category: props.destination.category ?? '',
    description: props.destination.description ?? '',
    address: props.destination.address ?? '',
    addon_price: Number(props.destination.addon_price),
    is_active: props.destination.is_active,
    image: null as File | null,
    _method: 'put',
});

function submit() {
    form.post(`/admin/destinasi/${props.destination.id}`, { forceFormData: true });
}
</script>

<template>
    <Head title="Ubah Destinasi" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Ubah Destinasi</h1>

        <form class="mt-6 max-w-2xl space-y-4 rounded-xl border border-border bg-background p-6" @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <Label for="name">Nama Destinasi</Label>
                    <Input id="name" v-model="form.name" class="mt-1" required autofocus />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
                </div>
                <div>
                    <Label for="category">Kategori</Label>
                    <Input id="category" v-model="form.category" class="mt-1" placeholder="pantai, kuliner, sejarah, dll." />
                </div>
                <div>
                    <Label for="addon_price">Biaya Add-on (Rp)</Label>
                    <Input id="addon_price" v-model.number="form.addon_price" type="number" class="mt-1" />
                </div>
            </div>

            <div>
                <Label for="address">Alamat</Label>
                <Input id="address" v-model="form.address" class="mt-1" />
            </div>

            <div>
                <Label for="description">Deskripsi</Label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.is_active" type="checkbox" class="rounded border-input" />
                    Tampilkan destinasi ini secara publik
                </label>
            </div>

            <div v-if="destination.image_path">
                <Label>Foto Saat Ini</Label>
                <img :src="destination.image_path" class="mt-2 h-32 w-48 rounded-md border border-border object-cover" />
            </div>

            <div>
                <Label for="image">Ganti Foto</Label>
                <FileUpload
                    v-model="form.image"
                    accept="image/*"
                    label="Klik atau seret foto baru ke sini"
                    hint="Format PNG/JPG, maksimal 4MB."
                    class="mt-1"
                />
                <p v-if="form.errors.image" class="mt-1 text-sm text-destructive">{{ form.errors.image }}</p>
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
                <a href="/admin/destinasi"><Button type="button" variant="outline">Batal</Button></a>
            </div>
        </form>
    </AdminLayout>
</template>
