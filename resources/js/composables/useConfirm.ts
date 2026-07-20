import { ref } from 'vue';

interface ConfirmOptions {
    title: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

const isOpen = ref(false);
const options = ref<ConfirmOptions>({ title: '' });
let resolvePromise: ((value: boolean) => void) | null = null;

export function useConfirmDialogState() {
    return { isOpen, options };
}

export function confirm(opts: ConfirmOptions | string): Promise<boolean> {
    options.value = typeof opts === 'string' ? { title: opts } : opts;
    isOpen.value = true;

    return new Promise((resolve) => {
        resolvePromise = resolve;
    });
}

export function resolveConfirm(result: boolean) {
    isOpen.value = false;
    resolvePromise?.(result);
    resolvePromise = null;
}
