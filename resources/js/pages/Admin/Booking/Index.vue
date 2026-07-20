<script setup lang="ts">
import { Input } from '@/components/ui/input';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency, paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive } from 'vue';

interface Booking {
    id: number;
    booking_code: string;
    booking_type: string;
    status: string;
    total_price: number;
    start_datetime: string;
    user: { name: string };
    vehicle: { name: string } | null;
    package: { name: string } | null;
}

const props = defineProps<{
    bookings: Paginated<Booking>;
    filters: Record<string, string | number | null>;
}>();

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    booking_type: props.filters.booking_type ?? '',
});

watchDebounced(filters, () => router.get('/admin/booking', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

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
    <Head title="Semua Booking" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Semua Booking</h1>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <Input v-model="filters.search" placeholder="Cari kode booking / nama pelanggan..." />
            <select v-model="filters.status" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">Semua Status</option>
                <option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option>
            </select>
            <select v-model="filters.booking_type" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">Semua Jenis</option>
                <option value="mobil">Mobil</option>
                <option value="paket_wisata">Paket Wisata</option>
            </select>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-background">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-secondary/40">
                    <tr>
                        <th class="p-3 font-medium">Kode</th>
                        <th class="p-3 font-medium">Pelanggan</th>
                        <th class="p-3 font-medium">Item</th>
                        <th class="p-3 font-medium">Total</th>
                        <th class="p-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="booking in bookings.data" :key="booking.id" class="cursor-pointer hover:bg-secondary/20" @click="router.get(`/admin/booking/${booking.id}`)">
                        <td class="p-3 font-medium">{{ booking.booking_code }}</td>
                        <td class="p-3">{{ booking.user.name }}</td>
                        <td class="p-3 text-muted-foreground">{{ booking.vehicle?.name ?? booking.package?.name }}</td>
                        <td class="p-3">{{ formatCurrency(booking.total_price) }}</td>
                        <td class="p-3">
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">{{ statusLabels[booking.status] ?? booking.status }}</span>
                        </td>
                    </tr>
                    <tr v-if="!bookings.data.length">
                        <td colspan="5" class="p-6 text-center text-muted-foreground">Belum ada booking.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="bookings.last_page > 1" class="mt-6 flex flex-wrap gap-2">
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
    </AdminLayout>
</template>
