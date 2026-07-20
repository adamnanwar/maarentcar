<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '' });

function submit() {
    form.post('/lupa-password');
}
</script>

<template>
    <Head title="Lupa Kata Sandi" />
    <PublicLayout>
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md flex-col justify-center px-4 py-16">
            <h1 class="text-2xl font-bold text-foreground">Lupa Kata Sandi</h1>
            <p class="mt-1 text-sm text-muted-foreground">Masukkan email Anda, kami akan kirimkan link untuk reset kata sandi.</p>

            <form class="mt-8 space-y-4 rounded-xl border border-border bg-background p-8" @submit.prevent="submit">
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" class="mt-1" autofocus required />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</p>
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">Kirim Link Reset</Button>
            </form>
        </div>
    </PublicLayout>
</template>
