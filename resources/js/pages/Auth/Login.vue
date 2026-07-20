<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Masuk" />
    <PublicLayout>
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md flex-col justify-center px-4 py-16">
            <h1 class="text-2xl font-bold text-foreground">Masuk ke Akun Anda</h1>
            <p class="mt-1 text-sm text-muted-foreground">Kelola booking mobil dan paket wisata Anda.</p>

            <form class="mt-8 space-y-4 rounded-xl border border-border bg-background p-8" @submit.prevent="submit">
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" class="mt-1" autofocus required />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</p>
                </div>

                <div>
                    <Label for="password">Kata Sandi</Label>
                    <Input id="password" v-model="form.password" type="password" class="mt-1" required />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-destructive">{{ form.errors.password }}</p>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input v-model="form.remember" type="checkbox" class="rounded border-input" />
                        Ingat saya
                    </label>
                    <Link href="/lupa-password" class="font-medium text-primary hover:underline">Lupa kata sandi?</Link>
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">Masuk</Button>
            </form>

            <p class="mt-6 text-center text-sm text-muted-foreground">
                Belum punya akun?
                <Link href="/register" class="font-medium text-primary hover:underline">Daftar sekarang</Link>
            </p>
        </div>
    </PublicLayout>
</template>
