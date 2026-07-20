<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface UserData {
    name: string;
    email: string;
    phone: string | null;
}

const props = defineProps<{ user: UserData }>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submitProfile() {
    profileForm.put('/profil');
}

function submitPassword() {
    passwordForm.put('/profil/password', {
        onSuccess: () => passwordForm.reset(),
    });
}
</script>

<template>
    <Head title="Profil Saya" />
    <PublicLayout>
        <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Profil Saya</h1>

            <form class="mt-8 space-y-4 rounded-xl border border-border p-6" @submit.prevent="submitProfile">
                <h2 class="font-semibold">Data Diri</h2>
                <div>
                    <Label for="name">Nama Lengkap</Label>
                    <Input id="name" v-model="profileForm.name" class="mt-1" required />
                    <p v-if="profileForm.errors.name" class="mt-1 text-sm text-destructive">{{ profileForm.errors.name }}</p>
                </div>
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="profileForm.email" type="email" class="mt-1" required />
                    <p v-if="profileForm.errors.email" class="mt-1 text-sm text-destructive">{{ profileForm.errors.email }}</p>
                </div>
                <div>
                    <Label for="phone">Nomor Telepon</Label>
                    <Input id="phone" v-model="profileForm.phone" class="mt-1" />
                    <p v-if="profileForm.errors.phone" class="mt-1 text-sm text-destructive">{{ profileForm.errors.phone }}</p>
                </div>
                <Button type="submit" :disabled="profileForm.processing">Simpan Perubahan</Button>
            </form>

            <form class="mt-6 space-y-4 rounded-xl border border-border p-6" @submit.prevent="submitPassword">
                <h2 class="font-semibold">Ganti Kata Sandi</h2>
                <div>
                    <Label for="current_password">Kata Sandi Saat Ini</Label>
                    <Input id="current_password" v-model="passwordForm.current_password" type="password" class="mt-1" required />
                    <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-destructive">
                        {{ passwordForm.errors.current_password }}
                    </p>
                </div>
                <div>
                    <Label for="password">Kata Sandi Baru</Label>
                    <Input id="password" v-model="passwordForm.password" type="password" class="mt-1" required />
                    <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-destructive">{{ passwordForm.errors.password }}</p>
                </div>
                <div>
                    <Label for="password_confirmation">Konfirmasi Kata Sandi Baru</Label>
                    <Input id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" class="mt-1" required />
                </div>
                <Button type="submit" :disabled="passwordForm.processing">Ubah Kata Sandi</Button>
            </form>
        </div>
    </PublicLayout>
</template>
