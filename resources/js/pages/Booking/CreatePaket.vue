<script setup lang="ts">
import AddressFields from '@/components/booking/AddressFields.vue';
import StepIndicator from '@/components/booking/StepIndicator.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { confirm } from '@/composables/useConfirm';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { AppPageProps } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface TourPackage {
    id: number;
    slug: string;
    name: string;
    price: number;
    duration_days: number;
    seat_capacity: number;
    destinations: { id: number; name: string }[];
}

interface ExistingPackageBooking {
    booking_code: string;
    package_name: string | null;
}

const props = defineProps<{ package: TourPackage; existingPackageBooking: ExistingPackageBooking | null }>();
const page = usePage<AppPageProps>();

onMounted(async () => {
    if (!props.existingPackageBooking) return;

    const proceed = await confirm({
        title: 'Anda Sudah Memiliki Pesanan Paket Wisata',
        description: `Anda sudah melakukan pemesanan paket wisata ${props.existingPackageBooking.package_name ?? ''}. Apakah Anda yakin ingin lanjut memesan paket wisata ini?`,
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
    });

    if (!proceed) {
        router.visit(`/paket-wisata/${props.package.slug}`);
    }
});

const steps = ['Tanggal', 'Alamat Penjemputan', 'Catatan', 'Ringkasan & Bayar'];
const step = ref(1);

const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    booking_type: 'paket_wisata',
    package_id: props.package.id,
    start_datetime: '',
    delivery_method: 'driver_pickup',
    recipient_name: page.props.auth.user?.name ?? '',
    address_phone: page.props.auth.user?.phone ?? '',
    full_address: '',
    district: '',
    subdistrict: '',
    landmark: '',
    passenger_count: null as number | null,
    notes: '',
});

function next() {
    step.value = Math.min(4, step.value + 1);
}
function back() {
    step.value = Math.max(1, step.value - 1);
}

function submit() {
    form.post('/booking');
}
</script>

<template>
    <Head title="Booking Paket Wisata" />
    <PublicLayout>
        <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Pesan {{ package.name }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">Paket {{ package.duration_days }} hari, sudah termasuk mobil & supir.</p>

            <div class="mt-8">
                <StepIndicator :steps="steps" :current="step" />
            </div>

            <div class="mt-8 rounded-xl border border-border bg-background p-6">
                <div v-if="step === 1" class="space-y-4">
                    <h2 class="font-semibold">Langkah 1: Tanggal Keberangkatan</h2>
                    <div>
                        <Label for="start_datetime">Tanggal Keberangkatan</Label>
                        <Input id="start_datetime" v-model="form.start_datetime" type="date" :min="today" class="mt-1" required />
                        <p v-if="form.errors.start_datetime" class="mt-1 text-sm text-destructive">{{ form.errors.start_datetime }}</p>
                    </div>
                    <div>
                        <Label for="passenger_count">Jumlah Peserta</Label>
                        <input
                            id="passenger_count"
                            v-model.number="form.passenger_count"
                            type="number"
                            :max="package.seat_capacity"
                            class="mt-1 h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">Maksimal {{ package.seat_capacity }} orang.</p>
                    </div>
                </div>

                <div v-else-if="step === 2" class="space-y-4">
                    <h2 class="font-semibold">Langkah 2: Alamat Penjemputan</h2>
                    <p class="text-sm text-muted-foreground">Supir kami akan menjemput Anda di alamat berikut.</p>
                    <AddressFields v-model="form" :errors="form.errors" />
                </div>

                <div v-else-if="step === 3" class="space-y-4">
                    <h2 class="font-semibold">Langkah 3: Catatan Tambahan</h2>
                    <div>
                        <Label for="notes">Catatan (opsional)</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="4"
                            placeholder="Mis. request tujuan tambahan di luar paket"
                            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <h2 class="font-semibold">Langkah 4: Ringkasan & Bayar</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>{{ package.name }} ({{ package.duration_days }} hari)</span><span>{{ formatCurrency(package.price) }}</span></div>
                        <div class="flex justify-between border-t border-border pt-2 font-bold text-primary">
                            <span>Total</span><span>{{ formatCurrency(package.price) }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Setelah pesanan dibuat, Anda akan diarahkan ke halaman pembayaran untuk transfer manual. Anda memiliki waktu 1 jam untuk
                        menyelesaikan pembayaran, jika melewati batas waktu tersebut pesanan akan otomatis dibatalkan.
                    </p>
                </div>

                <div class="mt-6 flex justify-between">
                    <Button v-if="step > 1" type="button" variant="outline" @click="back">Kembali</Button>
                    <span v-else></span>
                    <Button v-if="step < 4" type="button" :disabled="step === 1 && !form.start_datetime" @click="next">Lanjut</Button>
                    <Button v-else type="button" :disabled="form.processing" @click="submit">Buat Pesanan</Button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
