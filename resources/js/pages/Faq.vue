<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

const faqs = [
    {
        q: 'Bagaimana cara melakukan pemesanan?',
        a: 'Buat akun terlebih dahulu, lalu pilih mobil atau paket wisata yang Anda inginkan dan ikuti langkah-langkah pemesanan.',
    },
    {
        q: 'Metode pembayaran apa yang tersedia?',
        a: 'Saat ini kami hanya menerima transfer bank manual. Setelah transfer, unggah bukti pembayaran dan tim kami akan memverifikasi dalam 1x24 jam.',
    },
    {
        q: 'Apakah saya bisa membatalkan pesanan?',
        a: 'Pembatalan dapat dilakukan sebelum pesanan dikonfirmasi oleh admin. Setelah dikonfirmasi, silakan hubungi kami langsung.',
    },
    {
        q: 'Apakah tersedia sewa mobil dengan supir?',
        a: 'Ya, Anda bisa memilih opsi lepas kunci atau dengan supir saat melakukan pemesanan mobil.',
    },
];
</script>

<template>
    <Head title="FAQ" />
    <PublicLayout>
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-foreground">Pertanyaan yang Sering Diajukan</h1>
            <div class="mt-8 space-y-4">
                <div v-for="faq in faqs" :key="faq.q" class="rounded-xl border border-border bg-background p-6 transition-colors hover:border-primary">
                    <p class="font-semibold text-foreground">{{ faq.q }}</p>
                    <p class="mt-2 text-muted-foreground">{{ faq.a }}</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
