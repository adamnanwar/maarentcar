<script setup lang="ts">
import { AlarmClock } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{ dueAt: string }>();
const emit = defineEmits<{ expired: [] }>();

const remainingMs = ref(new Date(props.dueAt).getTime() - Date.now());
let timer: ReturnType<typeof setInterval> | null = null;

const isExpired = computed(() => remainingMs.value <= 0);

const formatted = computed(() => {
    const totalSeconds = Math.max(0, Math.floor(remainingMs.value / 1000));
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

onMounted(() => {
    timer = setInterval(() => {
        remainingMs.value = new Date(props.dueAt).getTime() - Date.now();
        if (remainingMs.value <= 0 && timer) {
            clearInterval(timer);
            timer = null;
            emit('expired');
        }
    }, 1000);
});

onBeforeUnmount(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div v-if="!isExpired" class="flex items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 p-4">
        <AlarmClock class="h-5 w-5 shrink-0 text-amber-600" />
        <div class="flex-1 text-sm">
            <p class="font-medium text-amber-900">Segera selesaikan pembayaran dalam waktu 1 jam setelah pesanan dibuat.</p>
            <p class="mt-0.5 text-amber-700">Jika bukti transfer belum diunggah sampai waktu habis, pesanan ini akan otomatis dibatalkan.</p>
        </div>
        <div class="shrink-0 rounded-md bg-amber-600 px-3 py-1.5 text-center font-mono text-lg font-bold text-white">
            {{ formatted }}
        </div>
    </div>
</template>
