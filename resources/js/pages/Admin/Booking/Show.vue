<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Payment {
    id: number;
    status: string;
    amount: number;
    created_at: string;
}

interface StatusLog {
    id: number;
    from_status: string | null;
    to_status: string;
    note: string | null;
    created_at: string;
    changedBy: { name: string } | null;
}

interface Booking {
    id: number;
    booking_code: string;
    booking_type: string;
    status: string;
    start_datetime: string;
    end_datetime: string;
    duration_days: number;
    with_driver: boolean;
    total_price: number;
    notes: string | null;
    internal_notes: string | null;
    pickup_address_snapshot: Record<string, string> | null;
    ktp_photo_path: string | null;
    user: { name: string; email: string; phone: string | null };
    vehicle: { name: string } | null;
    package: { name: string } | null;
    destinations: { id: number; price_at_booking: number; destination: { name: string } }[];
    payments: Payment[];
    statusLogs: StatusLog[];
}

const props = defineProps<{ booking: Booking }>();

const statusLabels: Record<string, string> = {
    menunggu_pembayaran: 'Menunggu Pembayaran',
    menunggu_verifikasi: 'Menunggu Verifikasi',
    dikonfirmasi: 'Dikonfirmasi',
    berlangsung: 'Sedang Berlangsung',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
    dibatalkan: 'Dibatalkan',
};

const paymentStatusLabels: Record<string, string> = {
    menunggu: 'Menunggu Verifikasi',
    terverifikasi: 'Terverifikasi',
    ditolak: 'Ditolak',
};

const allowedTransitions: Record<string, string[]> = {
    dikonfirmasi: ['berlangsung', 'dibatalkan'],
    berlangsung: ['selesai'],
    menunggu_pembayaran: ['dibatalkan'],
    menunggu_verifikasi: ['dibatalkan'],
};

const availableStatuses = computed(() => allowedTransitions[props.booking.status] ?? []);

const form = useForm({
    status: '',
    internal_notes: props.booking.internal_notes ?? '',
});

function submit() {
    form.patch(`/admin/booking/${props.booking.id}/status`, { onSuccess: () => (form.status = '') });
}
</script>

<template>
    <Head :title="`Booking ${booking.booking_code}`" />
    <AdminLayout>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-primary">{{ booking.booking_code }}</h1>
                <p class="text-sm text-muted-foreground">{{ booking.vehicle?.name ?? booking.package?.name }}</p>
            </div>
            <span class="rounded-full bg-secondary px-3 py-1 text-sm font-medium">{{ statusLabels[booking.status] ?? booking.status }}</span>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-xl border border-border bg-background p-6">
                    <p class="font-semibold">Informasi Pelanggan</p>
                    <p class="mt-2 text-sm">{{ booking.user.name }} &middot; {{ booking.user.email }} &middot; {{ booking.user.phone }}</p>
                </div>

                <div class="rounded-xl border border-border bg-background p-6">
                    <p class="font-semibold">Detail Sewa</p>
                    <div class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                        <p>Mulai: {{ new Date(booking.start_datetime).toLocaleString('id-ID') }}</p>
                        <p>Selesai: {{ new Date(booking.end_datetime).toLocaleString('id-ID') }}</p>
                        <p>Durasi: {{ booking.duration_days }} hari</p>
                        <p v-if="booking.booking_type === 'mobil'">Layanan: {{ booking.with_driver ? 'Dengan Supir' : 'Lepas Kunci' }}</p>
                    </div>
                    <div v-if="booking.pickup_address_snapshot" class="mt-3 border-t border-border pt-3 text-sm">
                        <p class="font-medium">{{ booking.pickup_address_snapshot.recipient_name }} ({{ booking.pickup_address_snapshot.phone }})</p>
                        <p class="text-muted-foreground">{{ booking.pickup_address_snapshot.full_address }}</p>
                    </div>
                    <div v-if="booking.destinations.length" class="mt-3 border-t border-border pt-3 text-sm">
                        <p class="font-medium">Destinasi add-on:</p>
                        <p class="text-muted-foreground">{{ booking.destinations.map((d) => d.destination.name).join(', ') }}</p>
                    </div>
                    <p v-if="booking.notes" class="mt-3 border-t border-border pt-3 text-sm">
                        <span class="font-medium">Catatan pelanggan:</span> {{ booking.notes }}
                    </p>
                    <p class="mt-3 border-t border-border pt-3 font-bold text-primary">Total: {{ formatCurrency(booking.total_price) }}</p>
                </div>

                <div v-if="booking.ktp_photo_path" class="rounded-xl border border-border bg-background p-6">
                    <p class="font-semibold">KTP Jaminan</p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Foto KTP yang diunggah pelanggan sebagai jaminan. Pastikan KTP asli (hardcopy) ditahan saat pengambilan mobil dan
                        dikembalikan setelah booking selesai.
                    </p>
                    <a :href="`/booking/${booking.id}/ktp`" target="_blank" class="mt-2 inline-block text-sm text-primary hover:underline">Lihat KTP</a>
                </div>

                <div v-if="booking.payments.length" class="rounded-xl border border-border bg-background p-6">
                    <p class="font-semibold">Riwayat Pembayaran</p>
                    <ul class="mt-2 space-y-2 text-sm">
                        <li v-for="payment in booking.payments" :key="payment.id" class="flex items-center justify-between">
                            <span>{{ new Date(payment.created_at).toLocaleString('id-ID') }}</span>
                            <span class="flex items-center gap-3">
                                {{ paymentStatusLabels[payment.status] }}
                                <a :href="`/booking/${booking.id}/pembayaran/${payment.id}/bukti`" target="_blank" class="text-primary hover:underline">Lihat bukti</a>
                            </span>
                        </li>
                    </ul>
                </div>

                <div class="rounded-xl border border-border bg-background p-6">
                    <p class="font-semibold">Riwayat Status</p>
                    <ul class="mt-2 space-y-2 text-sm">
                        <li v-for="log in booking.statusLogs" :key="log.id" class="border-l-2 border-border pl-3">
                            <p class="font-medium">{{ statusLabels[log.to_status] ?? log.to_status }}</p>
                            <p class="text-xs text-muted-foreground">{{ new Date(log.created_at).toLocaleString('id-ID') }} &middot; {{ log.changedBy?.name ?? 'Sistem' }}</p>
                            <p v-if="log.note" class="text-xs text-muted-foreground">{{ log.note }}</p>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <div v-if="availableStatuses.length" class="rounded-xl border border-border bg-background p-6">
                    <p class="font-semibold">Ubah Status</p>
                    <form class="mt-3 space-y-3" @submit.prevent="submit">
                        <select v-model="form.status" class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm" required>
                            <option value="" disabled>Pilih status</option>
                            <option v-for="status in availableStatuses" :key="status" :value="status">{{ statusLabels[status] }}</option>
                        </select>
                        <textarea
                            v-model="form.internal_notes"
                            rows="3"
                            placeholder="Catatan internal (opsional)"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />
                        <Button type="submit" class="w-full" :disabled="form.processing || !form.status">Simpan</Button>
                    </form>
                </div>
                <div v-else class="rounded-xl border border-border bg-background p-6 text-sm text-muted-foreground">
                    Tidak ada aksi status yang tersedia untuk booking ini saat ini.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
