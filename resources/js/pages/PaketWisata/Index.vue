<script setup lang="ts">
import { Input } from '@/components/ui/input';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency, paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive } from 'vue';

interface TourPackage {
    id: number;
    name: string;
    slug: string;
    duration_days: number;
    price: number;
    image_path: string | null;
    destinations: { id: number; name: string }[];
}

const props = defineProps<{
    packages: Paginated<TourPackage>;
    filters: Record<string, string | number | null>;
}>();

const filters = reactive({ search: props.filters.search ?? '' });

watchDebounced(
    filters,
    () => router.get('/paket-wisata', filters, { preserveState: true, preserveScroll: true, replace: true }),
    { debounce: 400, deep: true },
);
</script>

<template>
    <Head title="Paket Wisata" />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Paket Wisata Batam</h1>
            <p class="mt-1 text-muted-foreground">Paket siap pakai lengkap dengan mobil, supir, dan destinasi wisata.</p>

            <Input v-model="filters.search" placeholder="Cari paket wisata..." class="mt-6 max-w-sm" />

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="pkg in packages.data"
                    :key="pkg.id"
                    :href="`/paket-wisata/${pkg.slug}`"
                    class="overflow-hidden rounded-xl border border-border bg-background transition-colors hover:border-primary"
                >
                    <div class="aspect-video w-full overflow-hidden bg-secondary">
                        <img v-if="pkg.image_path" :src="pkg.image_path" :alt="pkg.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">Tidak ada foto</div>
                    </div>
                    <div class="p-6">
                        <p class="font-semibold text-foreground">{{ pkg.name }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ pkg.duration_days }} hari &middot; {{ pkg.destinations.length }} destinasi</p>
                        <p class="mt-3 font-bold text-primary">{{ formatCurrency(pkg.price) }}</p>
                    </div>
                </Link>
            </div>

            <p v-if="!packages.data.length" class="mt-8 text-center text-muted-foreground">Belum ada paket wisata yang sesuai.</p>

            <div v-if="packages.last_page > 1" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link
                    v-for="link in packages.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    preserve-scroll
                    class="rounded-md px-3 py-1.5 text-sm"
                    :class="[
                        link.active ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground hover:bg-secondary/70',
                        !link.url && 'pointer-events-none opacity-40',
                    ]"
                    >{{ paginationLabel(link.label) }}</Link
                >
            </div>
        </div>
    </PublicLayout>
</template>
