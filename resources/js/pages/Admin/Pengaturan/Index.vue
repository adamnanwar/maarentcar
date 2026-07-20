<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    sections: Record<string, Record<string, string>>;
    values: Record<string, string>;
}>();

const initial: Record<string, string> = {};
for (const fields of Object.values(props.sections)) {
    for (const key of Object.keys(fields)) {
        initial[key] = props.values[key] ?? '';
    }
}

const form = useForm(initial);

const longFieldKeys = ['homepage_hero_subtitle', 'cancellation_policy'];

function submit() {
    form.put('/admin/pengaturan');
}
</script>

<template>
    <Head title="Pengaturan Sistem" />
    <AdminLayout>
        <h1 class="text-2xl font-bold text-primary">Pengaturan Sistem</h1>

        <form class="mt-6 max-w-2xl space-y-8" @submit.prevent="submit">
            <div v-for="(fields, section) in sections" :key="section" class="rounded-xl border border-border bg-background p-6">
                <h2 class="font-semibold text-primary">{{ section }}</h2>
                <div class="mt-4 space-y-4">
                    <div v-for="(label, key) in fields" :key="key">
                        <Label :for="key">{{ label }}</Label>
                        <textarea
                            v-if="longFieldKeys.includes(key)"
                            :id="key"
                            v-model="form[key]"
                            rows="3"
                            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />
                        <Input v-else :id="key" v-model="form[key]" class="mt-1" />
                        <p v-if="form.errors[key]" class="mt-1 text-sm text-destructive">{{ form.errors[key] }}</p>
                    </div>
                </div>
            </div>

            <Button type="submit" :disabled="form.processing">Simpan Pengaturan</Button>
        </form>
    </AdminLayout>
</template>
