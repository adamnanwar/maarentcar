<script setup lang="ts">
import type { AppPageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const page = usePage<AppPageProps>();
const isOpen = ref(false);
const rootRef = ref<HTMLElement | null>(null);

function toggle() {
    isOpen.value = !isOpen.value;
}

function markAllRead() {
    router.post('/notifications/mark-read', {}, { preserveScroll: true, preserveState: true });
}

function onClickOutside(event: MouseEvent) {
    if (rootRef.value && !rootRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
}

onMounted(() => document.addEventListener('click', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div ref="rootRef" class="relative">
        <button type="button" class="relative flex h-9 w-9 items-center justify-center rounded-full text-muted-foreground hover:bg-secondary hover:text-primary" @click="toggle">
            <Bell class="h-5 w-5" />
            <span
                v-if="page.props.notifications.length"
                class="absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-bold text-white"
            >
                {{ page.props.notifications.length > 9 ? '9+' : page.props.notifications.length }}
            </span>
        </button>

        <div v-if="isOpen" class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-border bg-background shadow-lg">
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <p class="text-sm font-semibold">Notifikasi</p>
                <button
                    v-if="page.props.notifications.length"
                    type="button"
                    class="text-xs font-medium text-primary hover:underline"
                    @click="markAllRead"
                >
                    Tandai semua dibaca
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <p v-if="!page.props.notifications.length" class="px-4 py-6 text-center text-sm text-muted-foreground">
                    Tidak ada notifikasi baru.
                </p>
                <component
                    :is="notification.url ? Link : 'div'"
                    v-for="notification in page.props.notifications"
                    :key="notification.id"
                    :href="notification.url ?? undefined"
                    class="block border-b border-border px-4 py-3 text-sm last:border-b-0 hover:bg-secondary/50"
                    @click="isOpen = false"
                >
                    <p class="text-foreground">{{ notification.message }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ notification.created_at }}</p>
                </component>
            </div>
        </div>
    </div>
</template>
