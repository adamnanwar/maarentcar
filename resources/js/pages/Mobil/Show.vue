<script setup lang="ts">
import { Button } from '@/components/ui/button';
import RatingStars from '@/components/RatingStars.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency } from '@/lib/utils';
import type { AppPageProps, Review } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Fuel, Gauge, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface Vehicle {
    id: number;
    name: string;
    slug: string;
    brand: string | null;
    model: string | null;
    year: number | null;
    transmission: string;
    fuel_type: string;
    seat_capacity: number;
    price_per_day: number;
    driver_fee_per_day: number;
    base_delivery_fee: number;
    description: string | null;
    category: { name: string };
    images: { id: number; image_path: string }[];
}

const props = defineProps<{ vehicle: Vehicle; reviews: Review[]; reviewsAvg: number }>();
const page = usePage<AppPageProps>();
const activeImage = ref(props.vehicle.images?.[0]?.image_path ?? null);
</script>

<template>
    <Head :title="vehicle.name" />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-2">
                <div>
                    <div class="aspect-video overflow-hidden rounded-xl bg-muted/20">
                        <img v-if="activeImage" :src="activeImage" :alt="vehicle.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center text-muted-foreground">Tidak ada foto</div>
                    </div>
                    <div v-if="vehicle.images.length > 1" class="mt-3 flex gap-2">
                        <button
                            v-for="image in vehicle.images"
                            :key="image.id"
                            class="h-16 w-24 overflow-hidden rounded-md border-2"
                            :class="activeImage === image.image_path ? 'border-primary' : 'border-transparent'"
                            @click="activeImage = image.image_path"
                        >
                            <img :src="image.image_path" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-muted-foreground">{{ vehicle.category.name }}</p>
                    <h1 class="mt-1 text-3xl font-bold text-foreground">{{ vehicle.name }}</h1>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-muted-foreground">
                        <span class="flex items-center gap-1"><Users class="h-4 w-4" /> {{ vehicle.seat_capacity }} kursi</span>
                        <span class="flex items-center gap-1"><Gauge class="h-4 w-4" /> {{ vehicle.transmission }}</span>
                        <span class="flex items-center gap-1"><Fuel class="h-4 w-4" /> {{ vehicle.fuel_type }}</span>
                    </div>

                    <p class="mt-6 text-muted-foreground">{{ vehicle.description }}</p>

                    <div class="mt-8 rounded-xl border border-border p-6">
                        <p class="text-3xl font-bold text-primary">
                            {{ formatCurrency(vehicle.price_per_day) }} <span class="text-sm font-normal text-muted-foreground">/hari</span>
                        </p>
                        <p v-if="Number(vehicle.driver_fee_per_day) > 0" class="mt-1 text-sm text-muted-foreground">
                            + {{ formatCurrency(vehicle.driver_fee_per_day) }}/hari jika dengan supir
                        </p>

                        <Link v-if="page.props.auth.user" :href="`/booking/mobil/${vehicle.slug}/baru`">
                            <Button size="lg" class="mt-6 w-full">Sewa Sekarang</Button>
                        </Link>
                        <Link v-else href="/login">
                            <Button size="lg" class="mt-6 w-full">Masuk untuk Sewa Mobil Ini</Button>
                        </Link>
                        <p class="mt-3 text-center text-xs text-muted-foreground">
                            Butuh bantuan? <a href="/kontak" class="text-primary hover:underline">Hubungi kami via WhatsApp</a>.
                        </p>
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
                <p v-if="!reviews.length" class="mt-2 text-sm text-muted-foreground">Belum ada ulasan untuk mobil ini.</p>
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
