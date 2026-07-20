<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { confirm } from '@/composables/useConfirm';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency, paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Payment {
    id: number;
    amount: number;
    status: string;
    bank_sender_name: string | null;
    bank_sender_account: string | null;
    created_at: string;
    booking: {
        id: number;
        booking_code: string;
        user: { name: string };
        vehicle: { name: string } | null;
        package: { name: string } | null;
    };
}

defineProps<{
    payments: Paginated<Payment>;
    filters: Record<string, string | number | null>;
}>();

const rejectingId = ref<number | null>(null);
const rejectForm = useForm({ reason: '' });

async function verify(payment: Payment) {
    if (await confirm({ title: `Verifikasi pembayaran untuk booking ${payment.booking.booking_code}?`, confirmText: 'Verifikasi' })) {
        router.post(`/admin/pembayaran/${payment.id}/verifikasi`);
    }
}

function openReject(payment: Payment) {
    rejectingId.value = payment.id;
    rejectForm.reset();
}

function submitReject() {
    if (!rejectingId.value) return;
    rejectForm.post(`/admin/pembayaran/${rejectingId.value}/tolak`, {
        onSuccess: () => (rejectingId.value = null),
    });
}
</script>

<template>
    <Head title="Verifikasi Pembayaran" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Verifikasi Pembayaran</h1>
        <p class="mt-1 text-muted-foreground">Antrian bukti transfer yang menunggu diverifikasi.</p>

        <div class="mt-6 space-y-4">
            <div v-for="payment in payments.data" :key="payment.id" class="rounded-xl border border-border bg-background p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold">{{ payment.booking.booking_code }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ payment.booking.user.name }} &middot; {{ payment.booking.vehicle?.name ?? payment.booking.package?.name }}
                        </p>
                        <p class="mt-1 text-sm">Jumlah: <span class="font-medium">{{ formatCurrency(payment.amount) }}</span></p>
                        <p v-if="payment.bank_sender_name" class="text-sm text-muted-foreground">
                            Pengirim: {{ payment.bank_sender_name }} ({{ payment.bank_sender_account }})
                        </p>
                        <p class="text-xs text-muted-foreground">{{ new Date(payment.created_at).toLocaleString('id-ID') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <a
                            :href="`/booking/${payment.booking.id}/pembayaran/${payment.id}/bukti`"
                            target="_blank"
                            class="text-sm font-medium text-primary hover:underline"
                        >
                            Lihat Bukti Transfer
                        </a>
                        <div v-if="payment.status === 'menunggu'" class="flex gap-2">
                            <Button size="sm" @click="verify(payment)">Verifikasi</Button>
                            <Button size="sm" variant="outline" class="text-destructive" @click="openReject(payment)">Tolak</Button>
                        </div>
                        <span v-else class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">{{ payment.status }}</span>
                    </div>
                </div>

                <form v-if="rejectingId === payment.id" class="mt-4 space-y-2 border-t border-border pt-4" @submit.prevent="submitReject">
                    <textarea
                        v-model="rejectForm.reason"
                        rows="2"
                        placeholder="Alasan penolakan (wajib diisi, mis. nominal tidak sesuai)"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        required
                    />
                    <p v-if="rejectForm.errors.reason" class="text-sm text-destructive">{{ rejectForm.errors.reason }}</p>
                    <div class="flex gap-2">
                        <Button type="submit" size="sm" variant="outline" class="text-destructive" :disabled="rejectForm.processing">
                            Kirim Penolakan
                        </Button>
                        <Button type="button" size="sm" variant="ghost" @click="rejectingId = null">Batal</Button>
                    </div>
                </form>
            </div>

            <p v-if="!payments.data.length" class="rounded-xl border border-border bg-background p-6 text-center text-muted-foreground">
                Tidak ada pembayaran yang menunggu verifikasi.
            </p>
        </div>

        <div v-if="payments.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in payments.links"
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
