<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

interface DailyRevenue {
    date: string;
    total: number;
}

interface TopItem {
    name: string;
    total: number;
}

const props = defineProps<{
    from: string;
    to: string;
    stats: {
        total_revenue: number;
        total_bookings: number;
        active_vehicles: number;
        vehicles_berlangsung: number;
    };
    dailyRevenue: DailyRevenue[];
    bookingsByStatus: Record<string, number>;
    topVehicles: TopItem[];
    topPackages: TopItem[];
}>();

const filters = reactive({ from: props.from, to: props.to });

function applyFilter() {
    router.get('/admin/laporan', filters, { preserveState: true });
}

const cards = computed(() => [
    { label: 'Total Pendapatan', value: formatCurrency(props.stats.total_revenue) },
    { label: 'Total Booking', value: props.stats.total_bookings },
    { label: 'Mobil Aktif', value: props.stats.active_vehicles },
    { label: 'Mobil Sedang Disewa', value: props.stats.vehicles_berlangsung },
]);

const maxRevenue = computed(() => Math.max(1, ...props.dailyRevenue.map((d) => d.total)));

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
    <Head title="Laporan" />
    <AdminLayout>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-primary">Laporan</h1>
            <a :href="`/admin/laporan/ekspor?from=${filters.from}&to=${filters.to}`">
                <Button variant="outline">Ekspor CSV</Button>
            </a>
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs text-muted-foreground">Dari</label>
                <input v-model="filters.from" type="date" class="mt-1 block h-10 rounded-md border border-input bg-background px-3 text-sm" />
            </div>
            <div>
                <label class="text-xs text-muted-foreground">Sampai</label>
                <input v-model="filters.to" type="date" class="mt-1 block h-10 rounded-md border border-input bg-background px-3 text-sm" />
            </div>
            <Button @click="applyFilter">Terapkan</Button>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="card in cards" :key="card.label" class="rounded-xl border border-border bg-background p-5">
                <p class="text-sm text-muted-foreground">{{ card.label }}</p>
                <p class="mt-1 text-2xl font-bold text-primary">{{ card.value }}</p>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-border bg-background p-6">
            <p class="font-semibold">Pendapatan Harian</p>
            <div v-if="dailyRevenue.length" class="mt-4 space-y-2">
                <div v-for="row in dailyRevenue" :key="row.date" class="flex items-center gap-3 text-sm">
                    <span class="w-24 shrink-0 text-muted-foreground">{{ row.date }}</span>
                    <div class="h-4 flex-1 rounded bg-secondary/40">
                        <div class="h-4 rounded bg-brand-gold" :style="{ width: `${(row.total / maxRevenue) * 100}%` }" />
                    </div>
                    <span class="w-32 shrink-0 text-right font-medium">{{ formatCurrency(row.total) }}</span>
                </div>
            </div>
            <p v-else class="mt-2 text-sm text-muted-foreground">Tidak ada pendapatan pada rentang ini.</p>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Booking per Status</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li v-for="(total, status) in bookingsByStatus" :key="status" class="flex justify-between">
                        <span>{{ statusLabels[status] ?? status }}</span><span class="font-medium">{{ total }}</span>
                    </li>
                    <li v-if="!Object.keys(bookingsByStatus).length" class="text-muted-foreground">Tidak ada booking pada rentang ini.</li>
                </ul>
            </div>

            <div class="rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Mobil Terpopuler</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li v-for="item in topVehicles" :key="item.name" class="flex justify-between">
                        <span>{{ item.name }}</span><span class="font-medium">{{ item.total }} booking</span>
                    </li>
                    <li v-if="!topVehicles.length" class="text-muted-foreground">Belum ada data.</li>
                </ul>
            </div>

            <div class="rounded-xl border border-border bg-background p-6 lg:col-span-2">
                <p class="font-semibold">Paket Wisata Terpopuler</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li v-for="item in topPackages" :key="item.name" class="flex justify-between">
                        <span>{{ item.name }}</span><span class="font-medium">{{ item.total }} booking</span>
                    </li>
                    <li v-if="!topPackages.length" class="text-muted-foreground">Belum ada data.</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
