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

interface TourPackage {
    id: number;
    name: string;
    duration_days: number;
    price: number;
    is_active: boolean;
    destinations: { id: number; name: string }[];
}

const props = defineProps<{
    packages: Paginated<TourPackage>;
    filters: Record<string, string | number | null>;
}>();

const page = usePage<AppPageProps>();
const isAdmin = page.props.auth.user?.role === 'admin';

const filters = reactive({ search: props.filters.search ?? '' });
watchDebounced(filters, () => router.get('/admin/paket-wisata', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

async function destroy(pkg: TourPackage) {
    if (await confirm({ title: `Hapus paket "${pkg.name}"?`, variant: 'destructive', confirmText: 'Hapus' })) {
        router.delete(`/admin/paket-wisata/${pkg.id}`);
    }
}
</script>

<template>
    <Head title="Paket Wisata" />
    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-primary">Paket Wisata</h1>
            <Link v-if="isAdmin" href="/admin/paket-wisata/create">
                <Button><Plus class="mr-1 h-4 w-4" /> Tambah Paket</Button>
            </Link>
        </div>

        <Input v-model="filters.search" placeholder="Cari paket wisata..." class="mt-4 max-w-sm" />

        <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-background">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-secondary/40">
                    <tr>
                        <th class="p-3 font-medium">Nama Paket</th>
                        <th class="p-3 font-medium">Durasi</th>
                        <th class="p-3 font-medium">Destinasi</th>
                        <th class="p-3 font-medium">Harga</th>
                        <th class="p-3 font-medium">Status</th>
                        <th v-if="isAdmin" class="p-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="pkg in packages.data" :key="pkg.id">
                        <td class="p-3 font-medium">{{ pkg.name }}</td>
                        <td class="p-3">{{ pkg.duration_days }} hari</td>
                        <td class="p-3 text-muted-foreground">{{ pkg.destinations.map((d) => d.name).join(', ') || '-' }}</td>
                        <td class="p-3">{{ formatCurrency(pkg.price) }}</td>
                        <td class="p-3">
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">{{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td v-if="isAdmin" class="p-3">
                            <div class="flex gap-2">
                                <Link :href="`/admin/paket-wisata/${pkg.id}/edit`">
                                    <Button variant="outline" size="icon"><Pencil class="h-4 w-4" /></Button>
                                </Link>
                                <Button variant="outline" size="icon" @click="destroy(pkg)"><Trash2 class="h-4 w-4" /></Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!packages.data.length">
                        <td colspan="6" class="p-6 text-center text-muted-foreground">Belum ada paket wisata.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="packages.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in packages.links"
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
