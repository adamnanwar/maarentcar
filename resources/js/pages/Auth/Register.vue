<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Daftar" />
    <PublicLayout>
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md flex-col justify-center px-4 py-16">
            <h1 class="text-2xl font-bold text-foreground">Buat Akun Baru</h1>
            <p class="mt-1 text-sm text-muted-foreground">Daftar untuk mulai memesan mobil & paket wisata.</p>

            <form class="mt-8 space-y-4 rounded-xl border border-border bg-background p-8" @submit.prevent="submit">
                <div>
                    <Label for="name">Nama Lengkap</Label>
                    <Input id="name" v-model="form.name" class="mt-1" autofocus required />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" class="mt-1" required />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</p>
                </div>

                <div>
                    <Label for="phone">Nomor Telepon</Label>
                    <Input id="phone" v-model="form.phone" class="mt-1" placeholder="08xxxxxxxxxx" required />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-destructive">{{ form.errors.phone }}</p>
                </div>

                <div>
                    <Label for="password">Kata Sandi</Label>
                    <Input id="password" v-model="form.password" type="password" class="mt-1" required />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-destructive">{{ form.errors.password }}</p>
                </div>

                <div>
                    <Label for="password_confirmation">Konfirmasi Kata Sandi</Label>
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1" required />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">Daftar</Button>
            </form>

            <p class="mt-6 text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <Link href="/login" class="font-medium text-primary hover:underline">Masuk di sini</Link>
            </p>
        </div>
    </PublicLayout>
</template>
