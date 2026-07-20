<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { confirm } from '@/composables/useConfirm';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/utils';
import type { AppPageProps, Paginated } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { reactive } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    vehicles_count: number;
}

const props = defineProps<{
    categories: Paginated<Category>;
    filters: Record<string, string | number | null>;
}>();

const page = usePage<AppPageProps>();
const isAdmin = page.props.auth.user?.role === 'admin';

const filters = reactive({ search: props.filters.search ?? '' });
watchDebounced(filters, () => router.get('/admin/kategori-mobil', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

async function destroy(category: Category) {
    if (await confirm({ title: `Hapus kategori "${category.name}"?`, variant: 'destructive', confirmText: 'Hapus' })) {
        router.delete(`/admin/kategori-mobil/${category.id}`);
    }
}
</script>

<template>
    <Head title="Kategori Mobil" />
    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-primary">Kategori Mobil</h1>
            <Link v-if="isAdmin" href="/admin/kategori-mobil/create">
                <Button><Plus class="mr-1 h-4 w-4" /> Tambah Kategori</Button>
            </Link>
        </div>

        <Input v-model="filters.search" placeholder="Cari kategori..." class="mt-4 max-w-sm" />

        <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-background">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-secondary/40">
                    <tr>
                        <th class="p-3 font-medium">Nama</th>
                        <th class="p-3 font-medium">Deskripsi</th>
                        <th class="p-3 font-medium">Jumlah Mobil</th>
                        <th v-if="isAdmin" class="p-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="category in categories.data" :key="category.id">
                        <td class="p-3 font-medium">{{ category.name }}</td>
                        <td class="max-w-xs truncate p-3 text-muted-foreground">{{ category.description }}</td>
                        <td class="p-3">{{ category.vehicles_count }}</td>
                        <td v-if="isAdmin" class="p-3">
                            <div class="flex gap-2">
                                <Link :href="`/admin/kategori-mobil/${category.id}/edit`">
                                    <Button variant="outline" size="icon"><Pencil class="h-4 w-4" /></Button>
                                </Link>
                                <Button variant="outline" size="icon" @click="destroy(category)"><Trash2 class="h-4 w-4" /></Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!categories.data.length">
                        <td colspan="4" class="p-6 text-center text-muted-foreground">Belum ada kategori mobil.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="categories.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in categories.links"
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
