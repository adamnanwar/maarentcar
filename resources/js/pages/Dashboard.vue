<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { AppPageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';

interface Booking {
    id: number;
    booking_code: string;
    booking_type: string;
    status: string;
    total_price: number;
    start_datetime: string;
    vehicle: { name: string } | null;
    package: { name: string } | null;
}

defineProps<{
    recentBookings: Booking[];
    stats: { total_bookings: number; active_bookings: number; completed_bookings: number };
}>();

const page = usePage<AppPageProps>();

const statusLabels: Record<string, string> = {
    menunggu_pembayaran: 'Menunggu Pembayaran',
    menunggu_verifikasi: 'Menunggu Verifikasi',
    dikonfirmasi: 'Dikonfirmasi',
    berlangsung: 'Sedang Berlangsung',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
    dibatalkan: 'Dibatalkan',
};
</script>

<template>
    <Head title="Dashboard" />
    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-primary">Halo, {{ page.props.auth.user?.name }} 👋</h1>
            <p class="mt-1 text-muted-foreground">Berikut ringkasan aktivitas booking Anda.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-border p-5">
                    <p class="text-sm text-muted-foreground">Total Booking</p>
                    <p class="mt-1 text-2xl font-bold text-primary">{{ stats.total_bookings }}</p>
                </div>
                <div class="rounded-xl border border-border p-5">
                    <p class="text-sm text-muted-foreground">Booking Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-primary">{{ stats.active_bookings }}</p>
                </div>
                <div class="rounded-xl border border-border p-5">
                    <p class="text-sm text-muted-foreground">Selesai</p>
                    <p class="mt-1 text-2xl font-bold text-primary">{{ stats.completed_bookings }}</p>
                </div>
            </div>

            <div class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-primary">Booking Terbaru</h2>
                    <Link v-if="recentBookings.length" href="/booking" class="text-sm font-medium text-primary hover:underline">Lihat semua &rarr;</Link>
                </div>
                <div v-if="recentBookings.length" class="mt-4 divide-y divide-border rounded-xl border border-border">
                    <Link
                        v-for="booking in recentBookings"
                        :key="booking.id"
                        :href="`/booking/${booking.id}`"
                        class="flex items-center justify-between p-4 hover:bg-secondary/40"
                    >
                        <div>
                            <p class="font-medium">{{ booking.vehicle?.name ?? booking.package?.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ booking.booking_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">{{ statusLabels[booking.status] ?? booking.status }}</p>
                            <p class="text-sm text-muted-foreground">{{ formatCurrency(booking.total_price) }}</p>
                        </div>
                    </Link>
                </div>
                <p v-else class="mt-4 text-muted-foreground">
                    Anda belum memiliki booking. Yuk mulai
                    <Link href="/mobil" class="font-medium text-primary hover:underline">sewa mobil</Link>
                    atau
                    <Link href="/paket-wisata" class="font-medium text-primary hover:underline">pesan paket wisata</Link>.
                </p>
            </div>
        </div>
    </PublicLayout>
</template>
