<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FileUpload from '@/components/FileUpload.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    description: '',
    icon: null as File | null,
});

function submit() {
    form.post('/admin/kategori-mobil');
}
</script>

<template>
    <Head title="Tambah Kategori Mobil" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Tambah Kategori Mobil</h1>

        <form class="mt-6 max-w-xl space-y-4 rounded-xl border border-border bg-background p-6" @submit.prevent="submit">
            <div>
                <Label for="name">Nama Kategori</Label>
                <Input id="name" v-model="form.name" class="mt-1" required autofocus />
                <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <div>
                <Label for="description">Deskripsi</Label>
                <textarea id="description" v-model="form.description" rows="3" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                <p v-if="form.errors.description" class="mt-1 text-sm text-destructive">{{ form.errors.description }}</p>
            </div>

            <div>
                <Label for="icon">Ikon (opsional)</Label>
                <FileUpload
                    v-model="form.icon"
                    accept="image/*"
                    label="Klik atau seret ikon kategori ke sini"
                    hint="Format PNG/JPG, maksimal 2MB."
                    class="mt-1"
                />
                <p v-if="form.errors.icon" class="mt-1 text-sm text-destructive">{{ form.errors.icon }}</p>
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Simpan</Button>
                <a href="/admin/kategori-mobil"><Button type="button" variant="outline">Batal</Button></a>
            </div>
        </form>
    </AdminLayout>
</template>
