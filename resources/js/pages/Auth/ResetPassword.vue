<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{ token: string; email: string }>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password');
}
</script>

<template>
    <Head title="Reset Kata Sandi" />
    <PublicLayout>
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md flex-col justify-center px-4 py-16">
            <h1 class="text-2xl font-bold text-foreground">Buat Kata Sandi Baru</h1>

            <form class="mt-8 space-y-4 rounded-xl border border-border bg-background p-8" @submit.prevent="submit">
                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" v-model="form.email" type="email" class="mt-1" required />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-destructive">{{ form.errors.email }}</p>
                </div>

                <div>
                    <Label for="password">Kata Sandi Baru</Label>
                    <Input id="password" v-model="form.password" type="password" class="mt-1" autofocus required />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-destructive">{{ form.errors.password }}</p>
                </div>

                <div>
                    <Label for="password_confirmation">Konfirmasi Kata Sandi</Label>
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1" required />
                </div>

                <Button type="submit" class="w-full" :disabled="form.processing">Ubah Kata Sandi</Button>
            </form>
        </div>
    </PublicLayout>
</template>
