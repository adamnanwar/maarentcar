<script setup lang="ts">
defineProps<{ steps: string[]; current: number }>();
</script>

<template>
    <div class="flex items-center justify-between">
        <template v-for="(label, index) in steps" :key="label">
            <div class="flex flex-col items-center gap-1">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
                    :class="index + 1 <= current ? 'bg-primary text-primary-foreground' : 'bg-secondary text-muted-foreground'"
                >
                    {{ index + 1 }}
                </div>
                <span class="hidden text-xs text-muted-foreground sm:block">{{ label }}</span>
            </div>
            <div v-if="index < steps.length - 1" class="mx-2 h-0.5 flex-1" :class="index + 1 < current ? 'bg-primary' : 'bg-secondary'" />
        </template>
    </div>
    <p class="mt-2 text-center text-sm text-muted-foreground sm:hidden">Langkah {{ current }} dari {{ steps.length }}: {{ steps[current - 1] }}</p>
</template>
