<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Mail, MapPin, Phone } from 'lucide-vue-next';
</script>

<template>
    <Head title="Kontak" />
    <PublicLayout>
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-foreground">Hubungi Kami</h1>
            <p class="mt-4 text-muted-foreground">Tim kami siap membantu pertanyaan seputar sewa mobil dan paket wisata Anda di Batam.</p>

            <div class="mt-8 space-y-4 rounded-xl border border-border bg-background p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary text-primary"><Phone class="h-5 w-5" /></div>
                    <span>+62 821-7082-9433 (WhatsApp)</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary text-primary"><Mail class="h-5 w-5" /></div>
                    <span>info@werentcar.com</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary text-primary"><MapPin class="h-5 w-5" /></div>
                    <span>Perum, Jl. Sarmen Raya Blk. B No.03, Tj. Buntung, Kec. Bengkong, Kota Batam, Kepulauan Riau 29457</span>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
