<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { confirm } from '@/composables/useConfirm';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatCurrency, paginationLabel } from '@/lib/utils';
import type { AppPageProps, Paginated } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { reactive } from 'vue';

interface Vehicle {
    id: number;
    name: string;
    price_per_day: number;
    seat_capacity: number;
    status: string;
    is_active: boolean;
    category: { name: string };
    images: { id: number; image_path: string }[];
}

interface Category {
    id: number;
    name: string;
}

const props = defineProps<{
    vehicles: Paginated<Vehicle>;
    categories: Category[];
    filters: Record<string, string | number | null>;
}>();

const page = usePage<AppPageProps>();
const isAdmin = page.props.auth.user?.role === 'admin';

const filters = reactive({ search: props.filters.search ?? '', status: props.filters.status ?? '' });
watchDebounced(filters, () => router.get('/admin/mobil', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

const statusLabels: Record<string, string> = { tersedia: 'Tersedia', perawatan: 'Perawatan', nonaktif: 'Nonaktif' };

async function destroy(vehicle: Vehicle) {
    if (await confirm({ title: `Hapus mobil "${vehicle.name}"?`, variant: 'destructive', confirmText: 'Hapus' })) {
        router.delete(`/admin/mobil/${vehicle.id}`);
    }
}
</script>

<template>
    <Head title="Kelola Mobil" />
    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-primary">Kelola Mobil</h1>
            <Link v-if="isAdmin" href="/admin/mobil/create">
                <Button><Plus class="mr-1 h-4 w-4" /> Tambah Mobil</Button>
            </Link>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <Input v-model="filters.search" placeholder="Cari nama mobil..." />
            <select v-model="filters.status" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">Semua Status</option>
                <option value="tersedia">Tersedia</option>
                <option value="perawatan">Perawatan</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-background">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-secondary/40">
                    <tr>
                        <th class="p-3 font-medium">Mobil</th>
                        <th class="p-3 font-medium">Kategori</th>
                        <th class="p-3 font-medium">Harga/Hari</th>
                        <th class="p-3 font-medium">Status</th>
                        <th class="p-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="vehicle in vehicles.data" :key="vehicle.id">
                        <td class="flex items-center gap-3 p-3">
                            <div class="h-10 w-14 overflow-hidden rounded bg-muted/20">
                                <img v-if="vehicle.images?.[0]" :src="vehicle.images[0].image_path" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <p class="font-medium">{{ vehicle.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ vehicle.seat_capacity }} kursi</p>
                            </div>
                        </td>
                        <td class="p-3">{{ vehicle.category.name }}</td>
                        <td class="p-3">{{ formatCurrency(vehicle.price_per_day) }}</td>
                        <td class="p-3">
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">{{ statusLabels[vehicle.status] }}</span>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-2">
                                <Link :href="`/admin/mobil/${vehicle.id}/edit`">
                                    <Button variant="outline" size="icon"><Pencil class="h-4 w-4" /></Button>
                                </Link>
                                <Button v-if="isAdmin" variant="outline" size="icon" @click="destroy(vehicle)"><Trash2 class="h-4 w-4" /></Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!vehicles.data.length">
                        <td colspan="5" class="p-6 text-center text-muted-foreground">Belum ada mobil.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="vehicles.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in vehicles.links"
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
