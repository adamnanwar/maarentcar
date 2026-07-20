<script setup lang="ts" generic="T extends File | File[] | null">
import { FileText, Paperclip, UploadCloud, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: T;
        accept?: string;
        multiple?: boolean;
        label?: string;
        hint?: string;
        required?: boolean;
    }>(),
    {
        accept: undefined,
        multiple: false,
        label: 'Klik atau seret file ke sini untuk mengunggah',
        hint: undefined,
        required: false,
    },
);

const emit = defineEmits<{ 'update:modelValue': [value: T] }>();

const inputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);

const files = computed<File[]>(() => {
    if (!props.modelValue) return [];
    return Array.isArray(props.modelValue) ? props.modelValue : [props.modelValue];
});

const previews = ref<Record<string, string>>({});

function fileKey(file: File, index: number) {
    return `${file.name}-${file.size}-${index}`;
}

function rebuildPreviews() {
    Object.values(previews.value).forEach((url) => URL.revokeObjectURL(url));
    const next: Record<string, string> = {};
    files.value.forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            next[fileKey(file, index)] = URL.createObjectURL(file);
        }
    });
    previews.value = next;
}

watch(files, rebuildPreviews, { immediate: true });
onBeforeUnmount(() => Object.values(previews.value).forEach((url) => URL.revokeObjectURL(url)));

function syncInputFiles(fileArray: File[]) {
    if (!inputRef.value) return;
    const transfer = new DataTransfer();
    fileArray.forEach((file) => transfer.items.add(file));
    inputRef.value.files = transfer.files;
}

function applyFiles(fileList: FileList | File[] | null) {
    if (!fileList || !fileList.length) return;
    const incoming = Array.from(fileList);

    if (props.multiple) {
        emit('update:modelValue', incoming as T);
        syncInputFiles(incoming);
    } else {
        emit('update:modelValue', incoming[0] as T);
        syncInputFiles([incoming[0]]);
    }
}

function openPicker() {
    inputRef.value?.click();
}

function onChange(event: Event) {
    applyFiles((event.target as HTMLInputElement).files);
}

function onDrop(event: DragEvent) {
    isDragging.value = false;
    applyFiles(event.dataTransfer?.files ?? null);
}

function removeFile(index: number) {
    if (props.multiple) {
        const next = files.value.filter((_, i) => i !== index);
        emit('update:modelValue', next as T);
        syncInputFiles(next);
    } else {
        emit('update:modelValue', null as T);
        syncInputFiles([]);
    }
}

function formatSize(bytes: number) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<template>
    <div>
        <div
            class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed p-6 text-center transition-colors"
            :class="isDragging ? 'border-primary bg-secondary' : 'border-border hover:border-primary hover:bg-secondary/50'"
            @click="openPicker"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
        >
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-primary">
                <UploadCloud class="h-5 w-5" />
            </div>
            <p class="text-sm font-medium text-foreground">{{ label }}</p>
            <p v-if="hint" class="text-xs text-muted-foreground">{{ hint }}</p>
            <input
                ref="inputRef"
                type="file"
                class="sr-only"
                :accept="accept"
                :multiple="multiple"
                :required="required && !files.length"
                @click.stop
                @change="onChange"
            />
        </div>

        <ul v-if="files.length" class="mt-3 space-y-2">
            <li
                v-for="(file, index) in files"
                :key="fileKey(file, index)"
                class="flex items-center justify-between gap-3 rounded-lg border border-border bg-background px-3 py-2 text-sm"
            >
                <span class="flex min-w-0 items-center gap-2">
                    <img
                        v-if="previews[fileKey(file, index)]"
                        :src="previews[fileKey(file, index)]"
                        class="h-8 w-8 shrink-0 rounded object-cover"
                        :alt="file.name"
                    />
                    <span v-else class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-secondary text-primary">
                        <FileText class="h-4 w-4" />
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate font-medium text-foreground">{{ file.name }}</span>
                        <span class="text-xs text-muted-foreground">{{ formatSize(file.size) }}</span>
                    </span>
                </span>
                <button type="button" class="shrink-0 text-muted-foreground hover:text-destructive" @click.stop="removeFile(index)">
                    <X class="h-4 w-4" />
                </button>
            </li>
        </ul>
        <p v-else-if="!multiple" class="mt-2 flex items-center gap-1 text-xs text-muted-foreground">
            <Paperclip class="h-3 w-3" /> Belum ada file dipilih.
        </p>
    </div>
</template>
