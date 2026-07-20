<script setup lang="ts">
import { Button } from '@/components/ui/button';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import RatingStars from '@/components/RatingStars.vue';
import { formatCurrency } from '@/lib/utils';
import { Head, Link } from '@inertiajs/vue3';
import { Award, Headphones, MapPin, Settings2, ShieldCheck, Star, Users } from 'lucide-vue-next';

interface VehicleImage {
    id: number;
    image_path: string;
    is_primary: boolean;
}

interface Vehicle {
    id: number;
    name: string;
    slug: string;
    seat_capacity: number;
    transmission: string;
    price_per_day: number;
    images: VehicleImage[];
    category: { name: string };
}

interface TourPackage {
    id: number;
    name: string;
    slug: string;
    duration_days: number;
    price: number;
    destinations: { id: number; name: string }[];
}

interface Destination {
    id: number;
    name: string;
    slug: string;
    category: string | null;
    image_path: string | null;
}

interface Testimonial {
    id: number;
    rating: number;
    comment: string | null;
    user: { name: string };
}

defineProps<{
    featuredVehicles: Vehicle[];
    featuredPackages: TourPackage[];
    popularDestinations: Destination[];
    testimonials: Testimonial[];
}>();

const trustPoints = [
    { icon: ShieldCheck, title: 'Armada Terawat', desc: 'Seluruh mobil rutin diperiksa dan dirawat sebelum disewakan.' },
    { icon: Award, title: 'Harga Transparan', desc: 'Tidak ada biaya tersembunyi, semua rincian jelas di awal.' },
    { icon: Star, title: 'Proses Mudah', desc: 'Booking online dalam beberapa langkah sederhana.' },
    { icon: Headphones, title: 'Dukungan Lokal', desc: 'Tim kami siap membantu Anda selama di Batam.' },
];

const steps = [
    { title: 'Pilih', desc: 'Pilih mobil atau paket wisata favorit Anda.' },
    { title: 'Booking', desc: 'Isi jadwal dan data pemesanan secara online.' },
    { title: 'Transfer', desc: 'Bayar via transfer bank dan upload bukti.' },
    { title: 'Jalan-jalan', desc: 'Nikmati perjalanan Anda di Batam!' },
];

function initials(name: string) {
    return name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}
</script>

<template>
    <Head title="Beranda" />
    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <h1 class="max-w-xl text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                        Jelajahi Batam, <span class="text-primary">Tanpa Ribet</span>
                    </h1>
                    <p class="mt-6 max-w-md text-lg text-muted-foreground">
                        Sewa mobil lepas kunci atau dengan supir, plus paket wisata lokal siap pakai. Satu platform untuk semua kebutuhan
                        perjalanan Anda di Batam.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link href="/mobil">
                            <Button size="lg">Cek Ketersediaan Armada</Button>
                        </Link>
                        <Link href="/paket-wisata">
                            <Button size="lg" variant="outline">Lihat Paket Wisata</Button>
                        </Link>
                    </div>
                    <div class="mt-12 flex max-w-md items-center gap-8 border-t border-border pt-6">
                        <div class="flex flex-col">
                            <span class="text-2xl font-bold text-primary">100%</span>
                            <span class="text-xs tracking-wide text-muted-foreground uppercase">Transfer Manual</span>
                        </div>
                        <div class="h-10 w-px bg-border" />
                        <div class="flex flex-col">
                            <span class="text-2xl font-bold text-primary">1x24 Jam</span>
                            <span class="text-xs tracking-wide text-muted-foreground uppercase">Verifikasi Cepat</span>
                        </div>
                        <div class="h-10 w-px bg-border" />
                        <div class="flex flex-col">
                            <span class="text-2xl font-bold text-primary">24/7</span>
                            <span class="text-xs tracking-wide text-muted-foreground uppercase">Dukungan</span>
                        </div>
                    </div>
                </div>
                <div class="relative flex min-h-[320px] items-center justify-center lg:min-h-[420px]">
                    <div class="absolute inset-0 scale-125 rounded-full bg-secondary opacity-70 blur-3xl" />
                    <div
                        v-if="featuredVehicles[0]?.images?.[0]"
                        class="relative z-10 aspect-video w-full max-w-lg overflow-hidden rounded-2xl border border-border bg-background"
                    >
                        <img
                            :src="featuredVehicles[0].images[0].image_path"
                            :alt="featuredVehicles[0].name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <h2 class="text-2xl font-bold text-foreground">Kenapa Pilih We Rent Car</h2>
                    <p class="mx-auto mt-2 max-w-2xl text-muted-foreground">
                        Dirancang untuk memenuhi standar wisatawan dan pelanggan korporat yang mengutamakan efisiensi dan transparansi.
                    </p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="point in trustPoints"
                        :key="point.title"
                        class="group rounded-xl border border-border p-8 transition-colors hover:border-primary"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-secondary text-primary">
                            <component :is="point.icon" class="h-6 w-6" />
                        </div>
                        <p class="mt-6 font-semibold text-foreground">{{ point.title }}</p>
                        <p class="mt-2 text-sm text-muted-foreground">{{ point.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between border-b border-border pb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground">Mobil Unggulan</h2>
                        <p class="mt-1 text-muted-foreground">Dirawat dengan teliti demi kenyamanan dan keamanan Anda.</p>
                    </div>
                    <Link href="/mobil" class="hidden text-sm font-medium text-primary hover:opacity-80 sm:block">Lihat semua &rarr;</Link>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="vehicle in featuredVehicles"
                        :key="vehicle.id"
                        :href="`/mobil/${vehicle.slug}`"
                        class="group flex flex-col overflow-hidden rounded-xl border border-border bg-background transition-colors hover:border-primary"
                    >
                        <div class="relative h-48 bg-secondary p-6">
                            <span class="absolute top-4 left-4 rounded-sm bg-primary px-2 py-1 text-xs font-medium text-primary-foreground">
                                Tersedia
                            </span>
                            <img
                                v-if="vehicle.images?.[0]"
                                :src="vehicle.images[0].image_path"
                                :alt="vehicle.name"
                                class="h-full w-full object-contain transition-transform group-hover:scale-105"
                            />
                            <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">Tidak ada foto</div>
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-foreground">{{ vehicle.name }}</p>
                                <span class="rounded-sm bg-secondary px-2 py-1 text-xs font-medium text-secondary-foreground">{{
                                    vehicle.category.name
                                }}</span>
                            </div>
                            <div class="mt-auto">
                                <div class="mt-4 flex items-center justify-between border-t border-border py-3 text-muted-foreground">
                                    <span class="flex items-center gap-1 text-xs"><Users class="h-4 w-4" /> {{ vehicle.seat_capacity }} kursi</span>
                                    <span class="flex items-center gap-1 text-xs"><Settings2 class="h-4 w-4" /> {{ vehicle.transmission }}</span>
                                </div>
                                <p class="font-bold text-primary">
                                    {{ formatCurrency(vehicle.price_per_day) }} <span class="text-xs font-normal text-muted-foreground">/hari</span>
                                </p>
                            </div>
                        </div>
                    </Link>
                    <p v-if="!featuredVehicles.length" class="text-sm text-muted-foreground">Belum ada mobil tersedia.</p>
                </div>
            </div>
        </section>

        <section class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex items-end justify-between border-b border-border pb-4">
                    <h2 class="text-2xl font-bold text-foreground">Paket Wisata Unggulan</h2>
                    <Link href="/paket-wisata" class="hidden text-sm font-medium text-primary hover:opacity-80 sm:block">Lihat semua &rarr;</Link>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="pkg in featuredPackages"
                        :key="pkg.id"
                        :href="`/paket-wisata/${pkg.slug}`"
                        class="rounded-xl border border-border bg-background p-6 transition-colors hover:border-primary"
                    >
                        <p class="font-semibold text-foreground">{{ pkg.name }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ pkg.duration_days }} hari &middot; {{ pkg.destinations.length }} destinasi</p>
                        <p class="mt-3 font-bold text-primary">{{ formatCurrency(pkg.price) }}</p>
                    </Link>
                    <p v-if="!featuredPackages.length" class="text-sm text-muted-foreground">Belum ada paket wisata tersedia.</p>
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-foreground">Destinasi Populer di Batam</h2>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="destination in popularDestinations"
                        :key="destination.id"
                        :href="`/destinasi/${destination.slug}`"
                        class="group relative flex h-40 items-end overflow-hidden rounded-xl border border-border p-4 text-white"
                    >
                        <img
                            v-if="destination.image_path"
                            :src="destination.image_path"
                            :alt="destination.name"
                            class="absolute inset-0 h-full w-full object-cover transition-transform group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                        <div class="relative flex items-center gap-2">
                            <MapPin class="h-4 w-4" />
                            <span class="font-semibold">{{ destination.name }}</span>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <section v-if="testimonials.length" class="bg-secondary py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <h2 class="text-2xl font-bold text-foreground">Dipercaya Pelanggan Kami</h2>
                    <p class="mx-auto mt-2 max-w-2xl text-muted-foreground">Cerita dari pelanggan yang sudah merasakan layanan kami.</p>
                </div>
                <div class="grid gap-6" :class="testimonials.length > 1 ? 'md:grid-cols-2' : 'md:grid-cols-1 max-w-xl mx-auto'">
                    <div v-for="testimonial in testimonials" :key="testimonial.id" class="rounded-xl border border-border bg-background p-8">
                        <RatingStars :rating="testimonial.rating" readonly size="sm" />
                        <p v-if="testimonial.comment" class="mt-4 text-foreground italic">"{{ testimonial.comment }}"</p>
                        <div class="mt-6 flex items-center gap-4 border-t border-border pt-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary font-bold text-primary">
                                {{ initials(testimonial.user.name) }}
                            </div>
                            <p class="font-medium text-foreground">{{ testimonial.user.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-center text-2xl font-bold text-foreground">Cara Kerja</h2>
                <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(step, index) in steps" :key="step.title" class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary font-bold text-primary-foreground">
                            {{ index + 1 }}
                        </div>
                        <p class="mt-4 font-semibold text-foreground">{{ step.title }}</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="mx-auto max-w-4xl rounded-xl border border-border bg-secondary px-4 py-12 text-center sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-foreground">Siap Menjelajahi Batam?</h2>
                <p class="mt-2 text-muted-foreground">Booking mobil atau paket wisata Anda sekarang.</p>
                <div class="mt-6 flex justify-center gap-3">
                    <Link href="/mobil">
                        <Button size="lg">Sewa Mobil</Button>
                    </Link>
                    <Link href="/paket-wisata">
                        <Button size="lg" variant="outline">Lihat Paket</Button>
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
