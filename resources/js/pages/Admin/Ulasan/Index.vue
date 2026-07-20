<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import RatingStars from '@/components/RatingStars.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive } from 'vue';

interface Review {
    id: number;
    rating: number;
    comment: string | null;
    is_hidden: boolean;
    created_at: string;
    user: { name: string };
    vehicle: { name: string } | null;
    package: { name: string } | null;
}

const props = defineProps<{
    reviews: Paginated<Review>;
    filters: Record<string, string | number | null>;
}>();

const filters = reactive({
    search: props.filters.search ?? '',
    rating: props.filters.rating ?? '',
    status: props.filters.status ?? '',
});

watchDebounced(filters, () => router.get('/admin/ulasan', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

function toggle(review: Review) {
    router.patch(`/admin/ulasan/${review.id}/toggle`);
}
</script>

<template>
    <Head title="Ulasan" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Ulasan Pelanggan</h1>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <Input v-model="filters.search" placeholder="Cari nama pelanggan / komentar..." />
            <select v-model="filters.rating" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">Semua Rating</option>
                <option v-for="value in [5, 4, 3, 2, 1]" :key="value" :value="value">{{ value }} Bintang</option>
            </select>
            <select v-model="filters.status" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">Semua Status</option>
                <option value="visible">Tampil</option>
                <option value="hidden">Disembunyikan</option>
            </select>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-background">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-secondary/40">
                    <tr>
                        <th class="p-3 font-medium">Pelanggan</th>
                        <th class="p-3 font-medium">Item</th>
                        <th class="p-3 font-medium">Rating</th>
                        <th class="p-3 font-medium">Komentar</th>
                        <th class="p-3 font-medium">Status</th>
                        <th class="p-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="review in reviews.data" :key="review.id">
                        <td class="p-3 font-medium">{{ review.user.name }}</td>
                        <td class="p-3 text-muted-foreground">{{ review.vehicle?.name ?? review.package?.name }}</td>
                        <td class="p-3"><RatingStars :rating="review.rating" readonly size="sm" /></td>
                        <td class="p-3 max-w-xs truncate text-muted-foreground">{{ review.comment ?? '-' }}</td>
                        <td class="p-3">
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">
                                {{ review.is_hidden ? 'Disembunyikan' : 'Tampil' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <Button variant="outline" size="sm" @click="toggle(review)">
                                {{ review.is_hidden ? 'Tampilkan' : 'Sembunyikan' }}
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="!reviews.data.length">
                        <td colspan="6" class="p-6 text-center text-muted-foreground">Belum ada ulasan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="reviews.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in reviews.links"
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
