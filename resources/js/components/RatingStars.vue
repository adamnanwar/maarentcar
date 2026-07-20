<script setup lang="ts">
import { Star } from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        rating: number;
        readonly?: boolean;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { readonly: false, size: 'md' },
);

const emit = defineEmits<{ 'update:rating': [value: number] }>();

const sizeClasses: Record<string, string> = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-7 w-7',
};

function select(value: number) {
    if (!props.readonly) {
        emit('update:rating', value);
    }
}
</script>

<template>
    <div class="flex items-center gap-1">
        <button
            v-for="value in 5"
            :key="value"
            type="button"
            :disabled="readonly"
            :class="[readonly ? 'cursor-default' : 'cursor-pointer', sizeClasses[size]]"
            @click="select(value)"
        >
            <Star :class="[sizeClasses[size], value <= rating ? 'fill-primary text-primary' : 'text-muted-foreground']" />
        </button>
    </div>
</template>
