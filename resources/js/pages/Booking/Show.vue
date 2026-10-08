<script setup lang="ts">
import { Button } from '@/components/ui/button';
import FileUpload from '@/components/FileUpload.vue';
import PaymentCountdown from '@/components/booking/PaymentCountdown.vue';
import { confirm } from '@/composables/useConfirm';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { FileText, IdCard } from 'lucide-vue-next';
import { computed } from 'vue';

interface Payment {
    id: number;
    amount: number;
    status: string;
    created_at: string;
}

interface StatusLog {
    id: number;
    from_status: string | null;
    to_status: string;
    note: string | null;
    created_at: string;
}

interface BookingDestination {
    id: number;
    price_at_booking: number;
    destination: { id: number; name: string };
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
    delivery_method: string;
    pickup_address_snapshot: Record<string, string> | null;
    ktp_photo_path: string | null;
    payment_due_at: string | null;
    passenger_count: number | null;
    base_price: number;
    driver_fee: number;
    delivery_fee: number;
    addon_total: number;
    total_price: number;
    notes: string | null;
    vehicle: { name: string; images: { image_path: string }[] } | null;
    package: { name: string } | null;
    destinations: BookingDestination[];
    payments: Payment[];
    statusLogs: StatusLog[];
    review: { id: number; rating: number; comment: string | null } | null;
}

const props = defineProps<{
    booking: Booking;
    bankInfo: { bank_name: string | null; bank_account_number: string | null; bank_account_name: string | null };
}>();

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

const canCancel = computed(() => ['menunggu_pembayaran', 'menunggu_verifikasi'].includes(props.booking.status));
const canUploadProof = computed(() => props.booking.status === 'menunggu_pembayaran');

const form = useForm({
    bank_sender_name: '',
    bank_sender_account: '',
    proof: null as File | null,
});

function submitProof() {
    form.post(`/booking/${props.booking.id}/pembayaran`, { forceFormData: true, onSuccess: () => form.reset() });
}

async function cancelBooking() {
    if (await confirm({ title: 'Batalkan booking ini?', description: 'Tindakan ini tidak dapat dibatalkan.', variant: 'destructive', confirmText: 'Batalkan' })) {
        router.post(`/booking/${props.booking.id}/batalkan`);
    }
}

function onPaymentExpired() {
    router.reload({ only: ['booking'] });
}
</script>

<template>
    <Head :title="`Booking ${booking.booking_code}`" />
    <PublicLayout>
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">{{ booking.vehicle?.name ?? booking.package?.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ booking.booking_code }}</p>
                </div>
                <span class="rounded-full bg-secondary px-3 py-1 text-sm font-medium">{{ statusLabels[booking.status] ?? booking.status }}</span>
            </div>

            <div class="mt-6 grid gap-4 rounded-xl border border-border bg-background p-6 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-muted-foreground">Mulai</p>
                    <p class="font-medium">{{ new Date(booking.start_datetime).toLocaleString('id-ID') }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Selesai</p>
                    <p class="font-medium">{{ new Date(booking.end_datetime).toLocaleString('id-ID') }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Durasi</p>
                    <p class="font-medium">{{ booking.duration_days }} hari</p>
                </div>
                <div v-if="booking.booking_type === 'mobil'">
                    <p class="text-xs text-muted-foreground">Layanan</p>
                    <p class="font-medium">{{ booking.with_driver ? 'Dengan Supir' : 'Lepas Kunci' }}</p>
                </div>
            </div>

            <div v-if="booking.pickup_address_snapshot" class="mt-4 rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Alamat Penjemputan / Pengantaran</p>
                <p class="mt-2 text-sm">{{ booking.pickup_address_snapshot.recipient_name }} &middot; {{ booking.pickup_address_snapshot.phone }}</p>
                <p class="text-sm text-muted-foreground">
                    {{ booking.pickup_address_snapshot.full_address }}
                    <template v-if="booking.pickup_address_snapshot.district">, {{ booking.pickup_address_snapshot.district }}</template>
                </p>
            </div>

            <div v-if="booking.destinations.length" class="mt-4 rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Destinasi Add-on</p>
                <ul class="mt-2 space-y-1 text-sm">
                    <li v-for="d in booking.destinations" :key="d.id" class="flex justify-between">
                        <span>{{ d.destination.name }}</span><span>{{ formatCurrency(d.price_at_booking) }}</span>
                    </li>
                </ul>
            </div>

            <div class="mt-4 rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Rincian Biaya</p>
                <div class="mt-2 space-y-1 text-sm">
                    <div class="flex justify-between"><span>Harga sewa</span><span>{{ formatCurrency(booking.base_price) }}</span></div>
                    <div v-if="Number(booking.driver_fee) > 0" class="flex justify-between"><span>Biaya supir</span><span>{{ formatCurrency(booking.driver_fee) }}</span></div>
                    <div v-if="Number(booking.delivery_fee) > 0" class="flex justify-between"><span>Biaya antar</span><span>{{ formatCurrency(booking.delivery_fee) }}</span></div>
                    <div v-if="Number(booking.addon_total) > 0" class="flex justify-between"><span>Add-on destinasi</span><span>{{ formatCurrency(booking.addon_total) }}</span></div>
                    <div class="flex justify-between border-t border-border pt-2 font-bold text-primary"><span>Total</span><span>{{ formatCurrency(booking.total_price) }}</span></div>
                </div>
            </div>

            <div v-if="canUploadProof && booking.payment_due_at" class="mt-4">
                <PaymentCountdown :due-at="booking.payment_due_at" @expired="onPaymentExpired" />
            </div>

            <div v-if="booking.ktp_photo_path" class="mt-4 rounded-xl border border-border bg-background p-6">
                <p class="flex items-center gap-2 font-semibold"><IdCard class="h-4 w-4 text-primary" /> KTP Jaminan</p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Foto KTP Anda tersimpan sebagai jaminan untuk pesanan ini. KTP asli akan ditahan sementara oleh kami saat pengambilan mobil
                    dan dikembalikan setelah masa sewa selesai.
                </p>
                <a :href="`/booking/${booking.id}/ktp`" target="_blank" class="mt-2 inline-block text-sm text-primary hover:underline">Lihat KTP</a>
            </div>

            <div v-if="canUploadProof" class="mt-4 rounded-xl border border-primary/30 bg-secondary p-6">
                <p class="font-semibold">Informasi Transfer</p>
                <p class="mt-2 text-sm">{{ bankInfo.bank_name }}</p>
                <p class="text-lg font-bold text-primary">{{ bankInfo.bank_account_number }}</p>
                <p class="text-sm text-muted-foreground">a.n. {{ bankInfo.bank_account_name }}</p>

                <form class="mt-4 space-y-3" @submit.prevent="submitProof">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <input v-model="form.bank_sender_name" placeholder="Nama pengirim (opsional)" class="h-10 rounded-md border border-input bg-background px-3 text-sm" />
                        <input v-model="form.bank_sender_account" placeholder="No. rekening pengirim (opsional)" class="h-10 rounded-md border border-input bg-background px-3 text-sm" />
                    </div>
                    <div>
                        <FileUpload
                            v-model="form.proof"
                            accept="image/*,.pdf"
                            required
                            label="Klik atau seret bukti transfer ke sini"
                            hint="Format JPG, PNG, atau PDF. Maksimal 5MB."
                        />
                        <p v-if="form.errors.proof" class="mt-1 text-sm text-destructive">{{ form.errors.proof }}</p>
                    </div>
                    <Button type="submit" :disabled="form.processing">Unggah Bukti Transfer</Button>
                </form>
            </div>

            <div v-if="booking.payments.length" class="mt-4 rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Riwayat Pembayaran</p>
                <ul class="mt-2 space-y-2">
                    <li v-for="payment in booking.payments" :key="payment.id" class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2"><FileText class="h-4 w-4" /> {{ new Date(payment.created_at).toLocaleString('id-ID') }}</span>
                        <span class="flex items-center gap-3">
                            <span>{{ paymentStatusLabels[payment.status] }}</span>
                            <a :href="`/booking/${booking.id}/pembayaran/${payment.id}/bukti`" target="_blank" class="text-primary hover:underline">Lihat bukti</a>
                        </span>
                    </li>
                </ul>
            </div>

            <div v-if="booking.notes" class="mt-4 rounded-xl border border-border bg-background p-6">
                <p class="font-semibold">Catatan</p>
                <p class="mt-1 text-sm text-muted-foreground">{{ booking.notes }}</p>
            </div>

            <div v-if="booking.status === 'selesai' && !booking.review" class="mt-4 rounded-xl border border-primary/30 bg-secondary p-6">
                <p class="font-semibold">Bagaimana pengalaman Anda?</p>
                <p class="mt-1 text-sm text-muted-foreground">Bantu pelanggan lain dengan menulis ulasan.</p>
                <Link :href="`/booking/${booking.id}/ulasan/tulis`">
                    <Button class="mt-3">Tulis Ulasan</Button>
                </Link>
            </div>

            <div v-if="canCancel" class="mt-6">
                <Button variant="outline" class="text-destructive" @click="cancelBooking">Batalkan Booking</Button>
            </div>
        </div>
    </PublicLayout>
</template>
