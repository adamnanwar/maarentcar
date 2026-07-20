<script setup lang="ts">
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Award, Car, CheckCircle2, Eye, Flag, ShieldCheck, Users } from 'lucide-vue-next';

const coreValues = [
    { icon: ShieldCheck, title: 'Transparansi', desc: 'Harga jujur, komunikasi terbuka, dan komitmen melakukan hal yang benar untuk pelanggan.' },
    { icon: Car, title: 'Armada Terawat', desc: 'Proses perawatan dan pemeriksaan menyeluruh memastikan setiap mobil terasa seperti baru.' },
    { icon: Award, title: 'Layanan Ramah', desc: 'Dari pemesanan hingga pengembalian, tim kami bekerja dengan presisi dan keramahan.' },
];

const missionPoints = [
    'Menyediakan armada yang bersih dan terawat secara berkala.',
    'Memastikan pengiriman layanan yang tepat waktu dan mulus.',
    'Memberikan dukungan pelanggan yang tidak kenal kompromi.',
];
</script>

<template>
    <Head title="Tentang Kami" />
    <PublicLayout>
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-muted-foreground">Mendefinisikan Ulang Mobilitas Premium</p>
                    <h1 class="mt-2 text-3xl font-bold text-foreground sm:text-4xl">Tentang We Rent Car</h1>
                    <p class="mt-4 text-muted-foreground">
                        We Rent Car adalah platform rental mobil dan paket wisata lokal yang berbasis di Kota Batam. Kami hadir untuk memudahkan
                        wisatawan maupun pelanggan lokal dalam menyewa mobil, baik lepas kunci maupun dengan supir, serta menikmati paket wisata
                        siap pakai ke destinasi-destinasi favorit di Batam.
                    </p>
                    <p class="mt-4 text-muted-foreground">
                        Dengan armada yang terawat, harga transparan, dan proses booking yang mudah, kami berkomitmen menjadi mitra perjalanan
                        terpercaya Anda selama berada di Batam.
                    </p>
                </div>
                <div class="relative flex min-h-[280px] items-center justify-center">
                    <div class="absolute inset-0 scale-125 rounded-full bg-secondary opacity-70 blur-3xl" />
                    <div class="relative z-10 flex aspect-video w-full items-center justify-center rounded-2xl border border-border bg-card">
                        <Car class="h-20 w-20 text-primary" />
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div class="rounded-xl border border-border bg-secondary p-8">
                    <div class="flex items-center gap-2 text-primary">
                        <Eye class="h-5 w-5" />
                        <p class="font-semibold">Visi Kami</p>
                    </div>
                    <blockquote class="mt-4 border-l-2 border-primary pl-4 text-foreground italic">
                        "Menjadi mitra transportasi harian terbaik dengan standar kualitas premium."
                    </blockquote>
                    <p class="mt-3 text-sm text-muted-foreground">
                        Menjadi mitra transportasi utama yang menetapkan standar kualitas premium di setiap perjalanan.
                    </p>
                </div>
                <div class="rounded-xl border border-border bg-background p-8">
                    <div class="flex items-center gap-2 text-primary">
                        <Flag class="h-5 w-5" />
                        <p class="font-semibold">Misi Kami</p>
                    </div>
                    <ul class="mt-4 space-y-3">
                        <li v-for="point in missionPoints" :key="point" class="flex items-start gap-2 text-sm text-muted-foreground">
                            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                            {{ point }}
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="bg-card py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <p class="text-sm font-medium text-muted-foreground">Nilai-Nilai Inti</p>
                    <h2 class="mt-1 text-2xl font-bold text-foreground">Prinsip yang mendorong komitmen kami setiap hari</h2>
                </div>
                <div class="grid gap-6 sm:grid-cols-3">
                    <div v-for="value in coreValues" :key="value.title" class="rounded-xl border border-border p-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-secondary text-primary">
                            <component :is="value.icon" class="h-6 w-6" />
                        </div>
                        <p class="mt-4 font-semibold text-foreground">{{ value.title }}</p>
                        <p class="mt-2 text-sm text-muted-foreground">{{ value.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center gap-4 rounded-xl border border-border bg-secondary p-10 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-background text-primary">
                        <Users class="h-7 w-7" />
                    </div>
                    <p class="font-semibold text-foreground">Tim Profesional Siap Membantu</p>
                    <p class="max-w-md text-sm text-muted-foreground">
                        Didukung tim operasional dan layanan pelanggan berpengalaman yang siap memastikan perjalanan Anda berjalan lancar dari
                        awal hingga akhir.
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
