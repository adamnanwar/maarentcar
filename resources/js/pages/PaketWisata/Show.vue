<script setup lang="ts">
import { Button } from '@/components/ui/button';
import RatingStars from '@/components/RatingStars.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { AppPageProps, Review } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, MapPin, Users } from 'lucide-vue-next';

interface TourPackage {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    duration_days: number;
    seat_capacity: number;
    price: number;
    image_path: string | null;
    vehicle: { name: string } | null;
    destinations: { id: number; name: string; description: string | null }[];
}

defineProps<{ package: TourPackage; reviews: Review[]; reviewsAvg: number }>();
const page = usePage<AppPageProps>();
</script>

<template>
    <Head :title="package.name" />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-2">
                <div class="aspect-video overflow-hidden rounded-xl bg-muted/20">
                    <img v-if="package.image_path" :src="package.image_path" :alt="package.name" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full items-center justify-center text-muted-foreground">Tidak ada foto</div>
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-foreground">{{ package.name }}</h1>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-muted-foreground">
                        <span class="flex items-center gap-1"><CalendarDays class="h-4 w-4" /> {{ package.duration_days }} hari</span>
                        <span class="flex items-center gap-1"><Users class="h-4 w-4" /> {{ package.seat_capacity }} kursi</span>
                    </div>

                    <p class="mt-6 text-muted-foreground">{{ package.description }}</p>

                    <div class="mt-6">
                        <p class="font-semibold">Destinasi yang termasuk:</p>
                        <ul class="mt-2 space-y-1">
                            <li v-for="destination in package.destinations" :key="destination.id" class="flex items-center gap-2 text-sm">
                                <MapPin class="h-4 w-4 text-primary" /> {{ destination.name }}
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 rounded-xl border border-border p-6">
                        <p class="text-3xl font-bold text-primary">{{ formatCurrency(package.price) }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">Sudah termasuk mobil & supir</p>

                        <Link v-if="page.props.auth.user" :href="`/booking/paket-wisata/${package.slug}/baru`">
                            <Button size="lg" class="mt-6 w-full">Pesan Paket Ini</Button>
                        </Link>
                        <Link v-else href="/login">
                            <Button size="lg" class="mt-6 w-full">Masuk untuk Pesan Paket Ini</Button>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="mt-12">
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-foreground">Ulasan Pelanggan</h2>
                    <div v-if="reviews.length" class="flex items-center gap-2">
                        <RatingStars :rating="Math.round(reviewsAvg)" readonly size="sm" />
                        <span class="text-sm text-muted-foreground">{{ reviewsAvg }} ({{ reviews.length }} ulasan)</span>
                    </div>
                </div>
                <p v-if="!reviews.length" class="mt-2 text-sm text-muted-foreground">Belum ada ulasan untuk paket ini.</p>
                <div v-else class="mt-4 space-y-4">
                    <div v-for="review in reviews" :key="review.id" class="rounded-xl border border-border bg-background p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-medium">{{ review.user?.name }}</p>
                            <RatingStars :rating="review.rating" readonly size="sm" />
                        </div>
                        <p v-if="review.comment" class="mt-2 text-sm text-muted-foreground">{{ review.comment }}</p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
