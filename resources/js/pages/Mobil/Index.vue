<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatCurrency, paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { CalendarClock, CarFront, Settings2, Users, X } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';

interface Vehicle {
    id: number;
    name: string;
    slug: string;
    seat_capacity: number;
    transmission: string;
    price_per_day: number;
    category: { id: number; name: string };
    images: { id: number; image_path: string }[];
    is_available: boolean;
}

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    vehicles: Paginated<Vehicle>;
    categories: Category[];
    filters: Record<string, string | number | null>;
    availableCount: number;
    availabilityDate: string;
}>();

const filters = reactive({
    search: props.filters.search ?? '',
    category_id: props.filters.category_id ?? '',
    transmission: props.filters.transmission ?? '',
    seats: props.filters.seats ?? '',
    date: props.filters.date ?? '',
    time_start: props.filters.time_start ?? '',
    time_end: props.filters.time_end ?? '',
});

const showAvailabilityFilter = ref(Boolean(props.filters.date));

watchDebounced(
    filters,
    () => {
        router.get('/mobil', filters, { preserveState: true, preserveScroll: true, replace: true });
    },
    { debounce: 400, deep: true },
);

function selectCategory(id: number | string) {
    filters.category_id = id;
}

function resetAvailabilityFilter() {
    filters.date = '';
    filters.time_start = '';
    filters.time_end = '';
}

const availabilityLabel = computed(() => {
    const [year, month, day] = props.availabilityDate.split('-');
    const formatted = `${day}/${month}/${year}`;
    return filters.date ? `pada ${formatted}` : 'hari ini';
});
</script>

<template>
    <Head title="Katalog Mobil" />
    <PublicLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Pilih Armada Anda</h1>
                    <p class="mt-1 text-muted-foreground">Armada premium untuk setiap perjalanan. Pilih mobil sesuai kebutuhan Anda.</p>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-primary/30 bg-secondary px-4 py-3">
                    <CarFront class="h-5 w-5 text-primary" />
                    <div class="text-sm">
                        <p class="font-bold text-primary">Tersedia: {{ availableCount }} Unit</p>
                        <p class="text-xs text-muted-foreground">{{ availabilityLabel }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                    :class="filters.category_id === '' ? 'border-primary bg-primary text-primary-foreground' : 'border-border bg-background text-muted-foreground hover:border-primary hover:text-primary'"
                    @click="selectCategory('')"
                >
                    Semua Kategori
                </button>
                <button
                    v-for="category in categories"
                    :key="category.id"
                    type="button"
                    class="rounded-full border px-4 py-2 text-sm font-medium transition-colors"
                    :class="
                        String(filters.category_id) === String(category.id)
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border bg-background text-muted-foreground hover:border-primary hover:text-primary'
                    "
                    @click="selectCategory(category.id)"
                >
                    {{ category.name }}
                </button>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                <Input v-model="filters.search" placeholder="Cari nama mobil..." />
                <select v-model="filters.transmission" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                    <option value="">Semua Transmisi</option>
                    <option value="manual">Manual</option>
                    <option value="automatic">Automatic</option>
                </select>
                <select v-model="filters.seats" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                    <option value="">Semua Kapasitas</option>
                    <option value="5">Min. 5 kursi</option>
                    <option value="7">Min. 7 kursi</option>
                    <option value="12">Min. 12 kursi</option>
                </select>
                <Button
                    type="button"
                    variant="outline"
                    class="h-10 gap-1.5"
                    :class="filters.date ? 'border-primary text-primary' : ''"
                    @click="showAvailabilityFilter = !showAvailabilityFilter"
                >
                    <CalendarClock class="h-4 w-4" />
                    Filter Ketersediaan Unit
                </Button>
            </div>

            <div v-if="filters.date" class="mt-2 flex">
                <button
                    type="button"
                    class="flex items-center gap-1 rounded-full bg-secondary px-3 py-1.5 text-xs font-medium text-muted-foreground hover:text-destructive"
                    @click="resetAvailabilityFilter"
                >
                    {{ availabilityLabel }}
                    <X class="h-3 w-3" />
                </button>
            </div>

            <div v-if="showAvailabilityFilter" class="mt-3 grid gap-3 rounded-xl border border-border bg-background p-4 sm:grid-cols-3">
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Tanggal</label>
                    <input v-model="filters.date" type="date" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm" />
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Jam Mulai (opsional)</label>
                    <input v-model="filters.time_start" type="time" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm" />
                </div>
                <div>
                    <label class="text-xs font-medium text-muted-foreground">Jam Selesai (opsional)</label>
                    <input v-model="filters.time_end" type="time" class="mt-1 h-10 w-full rounded-md border border-input bg-background px-3 text-sm" />
                </div>
                <p class="text-xs text-muted-foreground sm:col-span-3">
                    Pilih tanggal untuk menampilkan hanya mobil yang benar-benar tersedia pada rentang waktu tersebut. Jam bersifat opsional -
                    tanpa jam, sistem mengecek ketersediaan untuk sepanjang hari itu.
                </p>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="vehicle in vehicles.data"
                    :key="vehicle.id"
                    :href="`/mobil/${vehicle.slug}`"
                    class="group flex flex-col overflow-hidden rounded-xl border border-border bg-background transition-colors"
                    :class="
                        vehicle.is_available
                            ? 'hover:border-primary'
                            : 'pointer-events-none grayscale opacity-60'
                    "
                >
                    <div class="relative h-48 bg-secondary p-6">
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
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-bold text-primary">
                                    {{ formatCurrency(vehicle.price_per_day) }}
                                    <span class="text-xs font-normal text-muted-foreground">/hari</span>
                                </p>
                                <span
                                    class="rounded-sm px-2 py-1 text-xs font-medium"
                                    :class="vehicle.is_available ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                                >
                                    {{ vehicle.is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <p v-if="!vehicles.data.length" class="mt-8 text-center text-muted-foreground">Tidak ada mobil yang sesuai dengan pencarian Anda.</p>

            <div v-if="vehicles.last_page > 1" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link
                    v-for="link in vehicles.links"
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
