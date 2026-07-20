<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

interface Booking {
    id: number;
    booking_code: string;
    booking_type: string;
    status: string;
    total_price: number;
    start_datetime: string;
    vehicle: { name: string } | null;
    package: { name: string } | null;
    review: { id: number } | null;
}

defineProps<{ bookings: Paginated<Booking> }>();

const statusLabels: Record<string, string> = {
    menunggu_pembayaran: 'Menunggu Pembayaran',
    menunggu_verifikasi: 'Menunggu Verifikasi',
    dikonfirmasi: 'Dikonfirmasi',
    berlangsung: 'Sedang Berlangsung',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
    dibatalkan: 'Dibatalkan',
};

const statusColors: Record<string, string> = {
    menunggu_pembayaran: 'bg-amber-100 text-amber-800',
    menunggu_verifikasi: 'bg-blue-100 text-blue-800',
    dikonfirmasi: 'bg-emerald-100 text-emerald-800',
    berlangsung: 'bg-emerald-100 text-emerald-800',
    selesai: 'bg-secondary text-secondary-foreground',
    ditolak: 'bg-red-100 text-red-800',
    dibatalkan: 'bg-red-100 text-red-800',
};
</script>

<template>
    <Head title="Riwayat Booking" />
    <PublicLayout>
        <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Riwayat Booking</h1>

            <div v-if="bookings.data.length" class="mt-6 space-y-3">
                <div
                    v-for="booking in bookings.data"
                    :key="booking.id"
                    class="flex cursor-pointer items-center justify-between rounded-xl border border-border bg-background p-4 transition-colors hover:border-primary"
                    @click="router.get(`/booking/${booking.id}`)"
                >
                    <div>
                        <p class="font-medium">{{ booking.vehicle?.name ?? booking.package?.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ booking.booking_code }} &middot; {{ new Date(booking.start_datetime).toLocaleDateString('id-ID') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="statusColors[booking.status]">
                            {{ statusLabels[booking.status] ?? booking.status }}
                        </span>
                        <p class="mt-1 text-sm font-medium">{{ formatCurrency(booking.total_price) }}</p>
                        <Link
                            v-if="booking.status === 'selesai' && !booking.review"
                            :href="`/booking/${booking.id}/ulasan/tulis`"
                            class="mt-1 block text-xs font-medium text-primary hover:underline"
                            @click.stop
                        >
                            Tulis Ulasan
                        </Link>
                    </div>
                </div>
            </div>
            <p v-else class="mt-6 text-muted-foreground">
                Anda belum memiliki booking. Yuk
                <Link href="/mobil" class="font-medium text-primary hover:underline">sewa mobil</Link>
                atau
                <Link href="/paket-wisata" class="font-medium text-primary hover:underline">pesan paket wisata</Link>.
            </p>

            <div v-if="bookings.last_page > 1" class="mt-8 flex flex-wrap gap-2">
                <Link
                    v-for="link in bookings.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    class="rounded-md px-3 py-1.5 text-sm"
                    :class="[
                        link.active ? 'bg-primary text-primary-foreground' : 'bg-secondary hover:bg-secondary/70',
                        !link.url && 'pointer-events-none opacity-40',
                    ]"
                    >{{ paginationLabel(link.label) }}</Link
                >
            </div>
        </div>
    </PublicLayout>
</template>
