<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, Link } from '@inertiajs/vue3';

interface Destination {
    id: number;
    name: string;
    category: string | null;
    description: string | null;
    address: string | null;
    image_path: string | null;
    addon_price: number;
    tour_packages: { id: number; name: string; slug: string; price: number }[];
}

defineProps<{ destination: Destination }>();
</script>

<template>
    <Head :title="destination.name" />
    <PublicLayout>
        <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="aspect-[21/9] overflow-hidden rounded-xl bg-muted/20">
                <img v-if="destination.image_path" :src="destination.image_path" :alt="destination.name" class="h-full w-full object-cover" />
                <div v-else class="flex h-full items-center justify-center text-muted-foreground">Tidak ada foto</div>
            </div>

            <p v-if="destination.category" class="mt-6 text-xs font-medium tracking-wide text-primary uppercase">{{ destination.category }}</p>
            <h1 class="mt-1 text-3xl font-bold text-foreground">{{ destination.name }}</h1>
            <p v-if="destination.address" class="mt-2 text-sm text-muted-foreground">{{ destination.address }}</p>
            <p class="mt-6 text-muted-foreground">{{ destination.description }}</p>

            <div v-if="destination.tour_packages.length" class="mt-10">
                <h2 class="text-xl font-bold text-foreground">Paket Wisata Terkait</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <Link
                        v-for="pkg in destination.tour_packages"
                        :key="pkg.id"
                        :href="`/paket-wisata/${pkg.slug}`"
                        class="rounded-xl border border-border p-4 transition-colors hover:border-primary"
                    >
                        <p class="font-semibold">{{ pkg.name }}</p>
                        <p class="mt-1 font-bold text-primary">{{ formatCurrency(pkg.price) }}</p>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
