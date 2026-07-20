<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Staff {
    id: number;
    name: string;
    email: string;
    phone: string | null;
}

const props = defineProps<{ staff: Staff }>();

const form = useForm({
    name: props.staff.name,
    email: props.staff.email,
    phone: props.staff.phone ?? '',
    password: '',
});

function submit() {
    form.put(`/admin/staff/${props.staff.id}`);
}
</script>

<template>
    <Head title="Ubah Staff" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Ubah Staff</h1>

        <form class="mt-6 max-w-lg space-y-4 rounded-xl border border-border bg-background p-6" @submit.prevent="submit">
            <div>
                <Label for="name">Nama</Label>
                <Input id="name" v-model="form.name" class="mt-1" required autofocus />
                <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
            </div>

            <div>
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" class="mt-1" required />
                <p v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</p>
            </div>

            <div>
                <Label for="phone">Telepon</Label>
                <Input id="phone" v-model="form.phone" class="mt-1" />
                <p v-if="form.errors.phone" class="mt-1 text-sm text-destructive">{{ form.errors.phone }}</p>
            </div>

            <div>
                <Label for="password">Kata Sandi Baru (opsional)</Label>
                <Input id="password" v-model="form.password" type="password" class="mt-1" placeholder="Kosongkan jika tidak diubah" />
                <p v-if="form.errors.password" class="mt-1 text-sm text-destructive">{{ form.errors.password }}</p>
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Simpan</Button>
                <a href="/admin/staff"><Button type="button" variant="outline">Batal</Button></a>
            </div>
        </form>
    </AdminLayout>
</template>
