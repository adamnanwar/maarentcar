<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { confirm } from '@/composables/useConfirm';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { reactive } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    created_at: string;
}

const props = defineProps<{
    customers: Paginated<Customer>;
    filters: Record<string, string | number | null>;
}>();

const filters = reactive({ search: props.filters.search ?? '', status: props.filters.status ?? '' });
watchDebounced(filters, () => router.get('/admin/pengguna', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

function toggleStatus(customer: Customer) {
    router.patch(`/admin/pengguna/${customer.id}/status`);
}

async function destroy(customer: Customer) {
    if (await confirm({ title: `Hapus akun "${customer.name}"?`, variant: 'destructive', confirmText: 'Hapus' })) {
        router.delete(`/admin/pengguna/${customer.id}`);
    }
}
</script>

<template>
    <Head title="Manajemen Pengguna" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Akun Pelanggan</h1>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <Input v-model="filters.search" placeholder="Cari nama / email..." />
            <select v-model="filters.status" class="h-10 rounded-md border border-input bg-background px-3 text-sm">
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-background">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-secondary/40">
                    <tr>
                        <th class="p-3 font-medium">Nama</th>
                        <th class="p-3 font-medium">Email</th>
                        <th class="p-3 font-medium">Telepon</th>
                        <th class="p-3 font-medium">Status</th>
                        <th class="p-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="customer in customers.data" :key="customer.id">
                        <td class="p-3 font-medium">{{ customer.name }}</td>
                        <td class="p-3 text-muted-foreground">{{ customer.email }}</td>
                        <td class="p-3">{{ customer.phone ?? '-' }}</td>
                        <td class="p-3">
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">
                                {{ customer.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" @click="toggleStatus(customer)">
                                    {{ customer.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </Button>
                                <Button variant="outline" size="sm" class="text-destructive" @click="destroy(customer)">Hapus</Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!customers.data.length">
                        <td colspan="5" class="p-6 text-center text-muted-foreground">Belum ada akun pelanggan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="customers.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in customers.links"
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
