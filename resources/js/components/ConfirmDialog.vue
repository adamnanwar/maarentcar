<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { resolveConfirm, useConfirmDialogState } from '@/composables/useConfirm';

const { isOpen, options } = useConfirmDialogState();

function onUpdateOpen(value: boolean) {
    if (!value) {
        resolveConfirm(false);
    }
}
</script>

<template>
    <Dialog :open="isOpen" @update:open="onUpdateOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ options.title }}</DialogTitle>
                <DialogDescription v-if="options.description">{{ options.description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="resolveConfirm(false)">{{ options.cancelText ?? 'Batal' }}</Button>
                <Button :variant="options.variant === 'destructive' ? 'destructive' : 'default'" @click="resolveConfirm(true)">
                    {{ options.confirmText ?? 'Ya, lanjutkan' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
