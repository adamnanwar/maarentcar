<script setup lang="ts">
import AddressFields from '@/components/booking/AddressFields.vue';
import StepIndicator from '@/components/booking/StepIndicator.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import FileUpload from '@/components/FileUpload.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { AppPageProps } from '@/types';
import { IdCard } from 'lucide-vue-next';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Vehicle {
    id: number;
    name: string;
    price_per_day: number;
    driver_fee_per_day: number;
    base_delivery_fee: number;
    seat_capacity: number;
    images: { image_path: string }[];
}

interface Destination {
    id: number;
    name: string;
    addon_price: number;
}

const props = defineProps<{ vehicle: Vehicle; destinations: Destination[] }>();
const page = usePage<AppPageProps>();

const steps = ['Jadwal', 'Jenis Layanan', 'Upload KTP', 'Konfirmasi', 'Ringkasan & Bayar'];
const step = ref(1);

const today = new Date().toISOString().slice(0, 16);

const form = useForm({
    booking_type: 'mobil',
    vehicle_id: props.vehicle.id,
    start_datetime: '',
    end_datetime: '',
    with_driver: false as boolean,
    delivery_method: 'pickup_at_office',
    destination_ids: [] as number[],
    recipient_name: page.props.auth.user?.name ?? '',
    address_phone: page.props.auth.user?.phone ?? '',
    full_address: '',
    district: '',
    subdistrict: '',
    landmark: '',
    passenger_count: null as number | null,
    notes: '',
    ktp_photo: null as File | null,
});

const durationDays = computed(() => {
    if (!form.start_datetime || !form.end_datetime) return 0;
    const start = new Date(form.start_datetime);
    const end = new Date(form.end_datetime);
    const hours = (end.getTime() - start.getTime()) / 1000 / 60 / 60;
    return hours > 0 ? Math.max(1, Math.ceil(hours / 24)) : 0;
});

const basePrice = computed(() => props.vehicle.price_per_day * durationDays.value);
const driverFee = computed(() => (form.with_driver ? props.vehicle.driver_fee_per_day * durationDays.value : 0));
const deliveryFee = computed(() => (form.delivery_method === 'delivered_to_address' ? props.vehicle.base_delivery_fee : 0));
const addonTotal = computed(() =>
    form.with_driver
        ? props.destinations.filter((d) => form.destination_ids.includes(d.id)).reduce((sum, d) => sum + Number(d.addon_price), 0)
        : 0,
);
const totalPrice = computed(() => basePrice.value + driverFee.value + deliveryFee.value + addonTotal.value);

function onDriverToggle(withDriver: boolean) {
    form.with_driver = withDriver;
    form.delivery_method = withDriver ? 'driver_pickup' : 'pickup_at_office';
    if (!withDriver) form.destination_ids = [];
}

function next() {
    step.value = Math.min(5, step.value + 1);
}
function back() {
    step.value = Math.max(1, step.value - 1);
}

const fieldStep: Record<string, number> = {
    start_datetime: 1,
    end_datetime: 1,
    with_driver: 2,
    delivery_method: 2,
    destination_ids: 2,
    recipient_name: 2,
    address_phone: 2,
    full_address: 2,
    district: 2,
    subdistrict: 2,
    landmark: 2,
    ktp_photo: 3,
    passenger_count: 4,
    notes: 4,
};

function submit() {
    form.post('/booking', {
        forceFormData: true,
        onError: (errors) => {
            // Form adalah wizard multi-langkah dengan step tersimpan di state lokal,
            // jadi redirect-dengan-error dari Inertia tidak otomatis membawa pengguna
            // kembali ke langkah yang bermasalah. Tanpa ini, pesan error di langkah
            // sebelumnya (mis. KTP tidak valid, tanggal bentrok) tidak akan terlihat
            // sama sekali dan tombol "Buat Pesanan" terasa seperti macet.
            const firstErrorField = Object.keys(errors)[0];
            const targetStep = fieldStep[firstErrorField];
            if (targetStep) step.value = targetStep;
        },
    });
}

const requiresAddress = computed(() => form.delivery_method !== 'pickup_at_office');
</script>

<template>
    <Head title="Booking Mobil" />
    <PublicLayout>
        <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Sewa {{ vehicle.name }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">Lengkapi langkah berikut untuk membuat pesanan.</p>

            <div class="mt-8">
                <StepIndicator :steps="steps" :current="step" />
            </div>

            <div class="mt-8 rounded-xl border border-border bg-background p-6">
                <!-- Step 1: Jadwal -->
                <div v-if="step === 1" class="space-y-4">
                    <h2 class="font-semibold">Langkah 1: Jadwal Sewa</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <Label for="start_datetime">Tanggal & Jam Mulai</Label>
                            <Input id="start_datetime" v-model="form.start_datetime" type="datetime-local" :min="today" class="mt-1" required />
                            <p v-if="form.errors.start_datetime" class="mt-1 text-sm text-destructive">{{ form.errors.start_datetime }}</p>
                        </div>
                        <div>
                            <Label for="end_datetime">Tanggal & Jam Selesai</Label>
                            <Input
                                id="end_datetime"
                                v-model="form.end_datetime"
                                type="datetime-local"
                                :min="form.start_datetime || today"
                                class="mt-1"
                                required
                            />
                            <p v-if="form.errors.end_datetime" class="mt-1 text-sm text-destructive">{{ form.errors.end_datetime }}</p>
                        </div>
                    </div>
                    <p v-if="durationDays > 0" class="text-sm text-muted-foreground">
                        Durasi sewa: <span class="font-medium text-primary">{{ durationDays }} hari</span> &middot; Subtotal:
                        <span class="font-medium text-primary">{{ formatCurrency(basePrice) }}</span>
                    </p>
                </div>

                <!-- Step 2: Jenis Layanan -->
                <div v-else-if="step === 2" class="space-y-4">
                    <h2 class="font-semibold">Langkah 2: Jenis Layanan</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            class="rounded-lg border p-4 text-left"
                            :class="!form.with_driver ? 'border-primary bg-primary/5' : 'border-border'"
                            @click="onDriverToggle(false)"
                        >
                            <p class="font-medium">Lepas Kunci</p>
                            <p class="text-sm text-muted-foreground">Anda mengemudi sendiri.</p>
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border p-4 text-left"
                            :class="form.with_driver ? 'border-primary bg-primary/5' : 'border-border'"
                            @click="onDriverToggle(true)"
                        >
                            <p class="font-medium">Dengan Supir</p>
                            <p class="text-sm text-muted-foreground">+ {{ formatCurrency(vehicle.driver_fee_per_day) }}/hari</p>
                        </button>
                    </div>

                    <div v-if="!form.with_driver" class="space-y-2">
                        <Label>Metode Pengambilan</Label>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-lg border border-border p-3 text-sm">
                                <input v-model="form.delivery_method" type="radio" value="pickup_at_office" />
                                Ambil di lokasi rental (gratis)
                            </label>
                            <label class="flex items-center gap-2 rounded-lg border border-border p-3 text-sm">
                                <input v-model="form.delivery_method" type="radio" value="delivered_to_address" />
                                Diantar ke alamat (+{{ formatCurrency(vehicle.base_delivery_fee) }})
                            </label>
                        </div>
                    </div>

                    <div v-if="requiresAddress">
                        <p class="mb-2 text-sm font-medium">{{ form.with_driver ? 'Alamat Penjemputan' : 'Alamat Pengantaran' }}</p>
                        <AddressFields v-model="form" :errors="form.errors" />
                    </div>

                    <div v-if="form.with_driver && destinations.length" class="space-y-2">
                        <Label>Tambah Destinasi Wisata (opsional)</Label>
                        <div class="grid max-h-48 grid-cols-1 gap-2 overflow-y-auto rounded-md border border-input p-3 sm:grid-cols-2">
                            <label v-for="destination in destinations" :key="destination.id" class="flex items-center gap-2 text-sm">
                                <input v-model="form.destination_ids" type="checkbox" :value="destination.id" class="rounded border-input" />
                                {{ destination.name }} (+{{ formatCurrency(destination.addon_price) }})
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Upload KTP -->
                <div v-else-if="step === 3" class="space-y-4">
                    <h2 class="font-semibold">Langkah 3: Upload KTP</h2>
                    <div class="flex items-start gap-3 rounded-lg border border-primary/30 bg-secondary p-4">
                        <IdCard class="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                        <div class="text-sm text-muted-foreground">
                            <p class="font-medium text-foreground">KTP digunakan sebagai jaminan sewa.</p>
                            <p class="mt-1">
                                Foto KTP yang Anda unggah akan kami simpan sebagai jaminan selama masa sewa berlangsung. Saat pengambilan mobil,
                                KTP asli (hardcopy) akan ditahan sementara oleh pihak kami dan dikembalikan saat mobil selesai disewa. Jangan lupa
                                membawa KTP asli Anda ya!
                            </p>
                        </div>
                    </div>
                    <div>
                        <FileUpload
                            v-model="form.ktp_photo"
                            accept="image/*,.pdf"
                            required
                            label="Klik atau seret foto KTP ke sini"
                            hint="Format JPG, PNG, atau PDF. Maksimal 5MB. Pastikan foto jelas dan tidak buram."
                        />
                        <p v-if="form.errors.ktp_photo" class="mt-1 text-sm text-destructive">{{ form.errors.ktp_photo }}</p>
                    </div>
                </div>

                <!-- Step 4: Konfirmasi -->
                <div v-else-if="step === 4" class="space-y-4">
                    <h2 class="font-semibold">Langkah 4: Konfirmasi Data Diri</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <Label>Nama</Label>
                            <Input :model-value="page.props.auth.user?.name" class="mt-1" disabled />
                        </div>
                        <div>
                            <Label>Nomor Telepon</Label>
                            <Input :model-value="page.props.auth.user?.phone ?? '-'" class="mt-1" disabled />
                        </div>
                    </div>
                    <div>
                        <Label for="passenger_count">Jumlah Penumpang (opsional)</Label>
                        <input
                            id="passenger_count"
                            v-model.number="form.passenger_count"
                            type="number"
                            class="mt-1 h-9 w-full rounded-md border border-input bg-background px-3 text-sm"
                        />
                    </div>
                    <div>
                        <Label for="notes">Catatan Tambahan (opsional)</Label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />
                    </div>
                </div>

                <!-- Step 5: Ringkasan & Bayar -->
                <div v-else class="space-y-4">
                    <h2 class="font-semibold">Langkah 5: Ringkasan & Bayar</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Sewa mobil ({{ durationDays }} hari)</span><span>{{ formatCurrency(basePrice) }}</span></div>
                        <div v-if="driverFee > 0" class="flex justify-between"><span>Biaya supir</span><span>{{ formatCurrency(driverFee) }}</span></div>
                        <div v-if="deliveryFee > 0" class="flex justify-between"><span>Biaya antar</span><span>{{ formatCurrency(deliveryFee) }}</span></div>
                        <div v-if="addonTotal > 0" class="flex justify-between"><span>Add-on destinasi</span><span>{{ formatCurrency(addonTotal) }}</span></div>
                        <div class="flex justify-between border-t border-border pt-2 font-bold text-primary">
                            <span>Total</span><span>{{ formatCurrency(totalPrice) }}</span>
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
                    <Button
                        v-if="step < 5"
                        type="button"
                        :disabled="(step === 1 && durationDays === 0) || (step === 3 && !form.ktp_photo)"
                        @click="next"
                    >
                        Lanjut
                    </Button>
                    <Button v-else type="button" :disabled="form.processing" @click="submit">Buat Pesanan</Button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
