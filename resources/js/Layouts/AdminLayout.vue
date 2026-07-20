<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import type { AppPageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    CalendarCheck,
    Car,
    CreditCard,
    LayoutDashboard,
    MapPinned,
    Menu,
    Package,
    Settings,
    ShieldCheck,
    Star,
    Tags,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage<AppPageProps>();
const mobileOpen = ref(false);

const baseNavItems = [
    { label: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { label: 'Kategori Mobil', href: '/admin/kategori-mobil', icon: Tags },
    { label: 'Kelola Mobil', href: '/admin/mobil', icon: Car },
    { label: 'Destinasi', href: '/admin/destinasi', icon: MapPinned },
    { label: 'Paket Wisata', href: '/admin/paket-wisata', icon: Package },
    { label: 'Semua Booking', href: '/admin/booking', icon: CalendarCheck },
    { label: 'Verifikasi Pembayaran', href: '/admin/pembayaran', icon: CreditCard },
];

const adminOnlyNavItems = [
    { label: 'Ulasan', href: '/admin/ulasan', icon: Star },
    { label: 'Pengguna', href: '/admin/pengguna', icon: Users },
    { label: 'Staff', href: '/admin/staff', icon: ShieldCheck },
    { label: 'Laporan', href: '/admin/laporan', icon: BarChart3 },
    { label: 'Pengaturan', href: '/admin/pengaturan', icon: Settings },
];

const navItems = computed(() => (page.props.auth.user?.role === 'admin' ? [...baseNavItems, ...adminOnlyNavItems] : baseNavItems));

const currentPath = computed(() => window.location.pathname);

function isActive(href: string) {
    return href === '/admin' ? currentPath.value === '/admin' : currentPath.value.startsWith(href);
}

const flashMessage = ref<{ type: 'success' | 'error'; text: string } | null>(null);

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            flashMessage.value = { type: 'success', text: flash.success };
        } else if (flash?.error) {
            flashMessage.value = { type: 'error', text: flash.error };
        }
        if (flash?.success || flash?.error) {
            setTimeout(() => (flashMessage.value = null), 5000);
        }
    },
    { immediate: true, deep: true },
);

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="flex min-h-screen bg-secondary/40">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-brand-navy text-white md:flex">
            <div class="flex h-16 items-center px-6 text-lg font-bold">We Rent Car</div>
            <nav class="flex-1 space-y-1 px-3">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors"
                    :class="isActive(item.href) ? 'bg-white/10 text-brand-gold' : 'text-white/70 hover:bg-white/5 hover:text-white'"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </Link>
            </nav>
            <div class="border-t border-white/10 p-4">
                <p class="text-sm font-medium">{{ page.props.auth.user?.name }}</p>
                <p class="text-xs text-white/50 capitalize">{{ page.props.auth.user?.role }}</p>
                <Button variant="outline" size="sm" class="mt-3 w-full border-white/20 bg-transparent text-white hover:bg-white/10" @click="logout">
                    Keluar
                </Button>
            </div>
        </aside>

        <div class="flex flex-1 flex-col md:pl-64">
            <header class="flex h-16 items-center justify-between border-b border-border bg-background px-4 md:hidden">
                <span class="text-lg font-bold text-primary">We Rent Car</span>
                <button @click="mobileOpen = !mobileOpen">
                    <Menu v-if="!mobileOpen" class="h-6 w-6" />
                    <X v-else class="h-6 w-6" />
                </button>
            </header>

            <div v-if="mobileOpen" class="space-y-1 bg-brand-navy p-3 text-white md:hidden">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium"
                    @click="mobileOpen = false"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.label }}
                </Link>
                <button class="w-full px-3 py-2 text-left text-sm font-medium text-red-300" @click="logout">Keluar</button>
            </div>

            <div
                v-if="flashMessage"
                class="fixed top-4 right-4 z-50 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"
                :class="flashMessage.type === 'success' ? 'bg-success' : 'bg-destructive'"
            >
                {{ flashMessage.text }}
            </div>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <ConfirmDialog />
    </div>
</template>
