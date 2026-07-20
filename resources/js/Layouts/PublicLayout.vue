<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import type { AppPageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage<AppPageProps>();
const mobileOpen = ref(false);

const navItems = [
    { label: 'Beranda', href: '/' },
    { label: 'Mobil', href: '/mobil' },
    { label: 'Paket Wisata', href: '/paket-wisata' },
    { label: 'Destinasi', href: '/destinasi' },
    { label: 'Tentang Kami', href: '/tentang-kami' },
];

const currentPath = computed(() => window.location.pathname);

function isActive(href: string) {
    return href === '/' ? currentPath.value === '/' : currentPath.value.startsWith(href);
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
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="text-lg font-bold text-primary">We Rent Car</Link>

                <nav class="hidden items-center gap-6 md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="border-b-2 pb-1 text-sm font-medium transition-colors"
                        :class="isActive(item.href) ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-primary'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <template v-if="page.props.auth.user">
                        <Link href="/dashboard" class="text-sm font-medium text-muted-foreground hover:text-primary">Dashboard</Link>
                        <Button variant="outline" size="sm" @click="logout">Keluar</Button>
                    </template>
                    <template v-else>
                        <Link href="/login">
                            <Button variant="outline" size="sm">Masuk</Button>
                        </Link>
                        <Link href="/register">
                            <Button size="sm">Daftar</Button>
                        </Link>
                    </template>
                </div>

                <button class="md:hidden" @click="mobileOpen = !mobileOpen">
                    <Menu v-if="!mobileOpen" class="h-6 w-6" />
                    <X v-else class="h-6 w-6" />
                </button>
            </div>

            <div v-if="mobileOpen" class="border-t border-border px-4 pb-4 md:hidden">
                <nav class="flex flex-col gap-3 pt-3">
                    <Link v-for="item in navItems" :key="item.href" :href="item.href" class="text-sm font-medium" @click="mobileOpen = false">
                        {{ item.label }}
                    </Link>
                    <template v-if="page.props.auth.user">
                        <Link href="/dashboard" class="text-sm font-medium">Dashboard</Link>
                        <button class="text-left text-sm font-medium text-destructive" @click="logout">Keluar</button>
                    </template>
                    <template v-else>
                        <Link href="/login" class="text-sm font-medium">Masuk</Link>
                        <Link href="/register" class="text-sm font-medium text-primary">Daftar</Link>
                    </template>
                </nav>
            </div>
        </header>

        <div
            v-if="flashMessage"
            class="fixed top-20 right-4 z-50 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"
            :class="flashMessage.type === 'success' ? 'bg-success' : 'bg-destructive'"
        >
            {{ flashMessage.text }}
        </div>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-border bg-secondary text-foreground">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-16 sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <p class="text-lg font-bold text-primary">We Rent Car</p>
                    <p class="mt-2 max-w-xs text-sm text-muted-foreground">Rental mobil & paket wisata lokal terpercaya di Kota Batam.</p>
                </div>
                <div>
                    <p class="mb-2 font-semibold">Tautan</p>
                    <ul class="space-y-1 text-sm text-muted-foreground">
                        <li><Link href="/mobil" class="hover:text-primary">Mobil</Link></li>
                        <li><Link href="/paket-wisata" class="hover:text-primary">Paket Wisata</Link></li>
                        <li><Link href="/destinasi" class="hover:text-primary">Destinasi</Link></li>
                        <li><Link href="/faq" class="hover:text-primary">FAQ</Link></li>
                    </ul>
                </div>
                <div>
                    <p class="mb-2 font-semibold">Kontak</p>
                    <ul class="space-y-1 text-sm text-muted-foreground">
                        <li><Link href="/kontak" class="hover:text-primary">Hubungi Kami</Link></li>
                        <li><Link href="/tentang-kami" class="hover:text-primary">Tentang Kami</Link></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-border py-4 text-center text-xs text-muted-foreground">
                &copy; {{ new Date().getFullYear() }} We Rent Car Batam. Seluruh hak cipta dilindungi.
            </div>
        </footer>

        <ConfirmDialog />
    </div>
</template>
