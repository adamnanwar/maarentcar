<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    stats: {
        total_vehicles: number;
        active_vehicles: number;
        total_destinations: number;
        total_packages: number;
        bookings_menunggu_verifikasi: number;
        bookings_berlangsung: number;
    };
}>();

const cards = (stats: any) => [
    { label: 'Total Mobil', value: stats.total_vehicles },
    { label: 'Mobil Tersedia', value: stats.active_vehicles },
    { label: 'Destinasi', value: stats.total_destinations },
    { label: 'Paket Wisata', value: stats.total_packages },
    { label: 'Menunggu Verifikasi', value: stats.bookings_menunggu_verifikasi },
    { label: 'Sedang Berlangsung', value: stats.bookings_berlangsung },
];
</script>

<template>
    <Head title="Dashboard Admin" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Dashboard</h1>
        <p class="mt-1 text-muted-foreground">Ringkasan operasional We Rent Car.</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="card in cards(stats)" :key="card.label" class="rounded-xl border border-border bg-background p-5">
                <p class="text-sm text-muted-foreground">{{ card.label }}</p>
                <p class="mt-1 text-2xl font-bold text-primary">{{ card.value }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
