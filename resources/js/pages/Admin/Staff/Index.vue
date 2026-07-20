<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { confirm } from '@/composables/useConfirm';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { paginationLabel } from '@/lib/utils';
import type { Paginated } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Pencil, Plus } from 'lucide-vue-next';
import { reactive } from 'vue';

interface Staff {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
}

interface Permission {
    id: number;
    module: string;
    action: string;
    label: string;
}

const props = defineProps<{
    staff: Paginated<Staff>;
    filters: Record<string, string | number | null>;
    permissions: Record<string, Permission[]>;
    staffPermissionIds: number[];
}>();

const filters = reactive({ search: props.filters.search ?? '', status: props.filters.status ?? '' });
watchDebounced(filters, () => router.get('/admin/staff', filters, { preserveState: true, replace: true }), { debounce: 400, deep: true });

function toggleStatus(member: Staff) {
    router.patch(`/admin/staff/${member.id}/status`);
}

async function destroy(member: Staff) {
    if (await confirm({ title: `Hapus akun staff "${member.name}"?`, variant: 'destructive', confirmText: 'Hapus' })) {
        router.delete(`/admin/staff/${member.id}`);
    }
}

const permissionForm = useForm({
    permission_ids: [...props.staffPermissionIds] as number[],
});

function savePermissions() {
    permissionForm.put('/admin/staff-permissions', { preserveScroll: true });
}
</script>

<template>
    <Head title="Manajemen Staff" />
    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-primary">Akun Staff</h1>
            <Link href="/admin/staff/create">
                <Button><Plus class="mr-1 h-4 w-4" /> Tambah Staff</Button>
            </Link>
        </div>

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
                    <tr v-for="member in staff.data" :key="member.id">
                        <td class="p-3 font-medium">{{ member.name }}</td>
                        <td class="p-3 text-muted-foreground">{{ member.email }}</td>
                        <td class="p-3">{{ member.phone ?? '-' }}</td>
                        <td class="p-3">
                            <span class="rounded-full bg-secondary px-2 py-1 text-xs font-medium">
                                {{ member.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex gap-2">
                                <Link :href="`/admin/staff/${member.id}/edit`">
                                    <Button variant="outline" size="icon"><Pencil class="h-4 w-4" /></Button>
                                </Link>
                                <Button variant="outline" size="sm" @click="toggleStatus(member)">
                                    {{ member.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </Button>
                                <Button variant="outline" size="sm" class="text-destructive" @click="destroy(member)">Hapus</Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!staff.data.length">
                        <td colspan="5" class="p-6 text-center text-muted-foreground">Belum ada akun staff.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="staff.last_page > 1" class="mt-6 flex flex-wrap gap-2">
            <Link
                v-for="link in staff.links"
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

        <div class="mt-8 rounded-xl border border-border bg-background p-6">
            <h2 class="font-semibold text-primary">Permission Role Staff</h2>
            <p class="mt-1 text-sm text-muted-foreground">Permission ini berlaku untuk semua akun staff.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="(items, module) in permissions" :key="module">
                    <p class="text-sm font-medium capitalize">{{ String(module).replace('_', ' ') }}</p>
                    <div class="mt-2 space-y-1">
                        <label v-for="permission in items" :key="permission.id" class="flex items-center gap-2 text-sm">
                            <input v-model="permissionForm.permission_ids" type="checkbox" :value="permission.id" class="rounded border-input" />
                            {{ permission.label }}
                        </label>
                    </div>
                </div>
            </div>

            <Button class="mt-6" :disabled="permissionForm.processing" @click="savePermissions">Simpan Permission</Button>
        </div>
    </AdminLayout>
</template>
