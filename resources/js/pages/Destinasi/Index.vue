<script setup lang="ts">
import { Input } from '@/components/ui/input';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive } from 'vue';

interface Destination {
    id: number;
    name: string;
    slug: string;
    category: string | null;
    image_path: string | null;
}

const props = defineProps<{
    destinations: Paginated<Destination>;
    filters: Record<string, string | number | null>;
}>();

const filters = reactive({ search: props.filters.search ?? '' });

watchDebounced(
    filters,
    () => router.get('/destinasi', filters, { preserveState: true, preserveScroll: true, replace: true }),
    { debounce: 400, deep: true },
);
</script>

<template>
    <Head title="Destinasi Wisata" />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Destinasi Wisata Batam</h1>
            <p class="mt-1 text-muted-foreground">Temukan tempat wisata favorit yang bisa Anda kunjungi bersama paket kami.</p>

            <Input v-model="filters.search" placeholder="Cari destinasi..." class="mt-6 max-w-sm" />

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="destination in destinations.data"
                    :key="destination.id"
                    :href="`/destinasi/${destination.slug}`"
                    class="group relative flex h-48 items-end overflow-hidden rounded-xl border border-border p-4 text-white"
                >
                    <img
                        v-if="destination.image_path"
                        :src="destination.image_path"
                        :alt="destination.name"
                        class="absolute inset-0 h-full w-full object-cover transition-transform group-hover:scale-105"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                    <div class="relative">
                        <p v-if="destination.category" class="text-xs tracking-wide text-white/80 uppercase">{{ destination.category }}</p>
                        <p class="font-semibold">{{ destination.name }}</p>
                    </div>
                </Link>
            </div>

            <p v-if="!destinations.data.length" class="mt-8 text-center text-muted-foreground">Belum ada destinasi yang sesuai.</p>

            <div v-if="destinations.last_page > 1" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link
                    v-for="link in destinations.links"
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
