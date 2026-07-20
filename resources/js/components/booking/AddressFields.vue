<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const model = defineModel<{
    recipient_name: string;
    address_phone: string;
    full_address: string;
    district: string;
    subdistrict: string;
    landmark: string;
}>({ required: true });

defineProps<{ errors?: Record<string, string> }>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <Label for="recipient_name">Nama Penerima</Label>
                <Input id="recipient_name" v-model="model.recipient_name" class="mt-1" required />
                <p v-if="errors?.recipient_name" class="mt-1 text-sm text-destructive">{{ errors.recipient_name }}</p>
            </div>
            <div>
                <Label for="address_phone">Nomor Telepon</Label>
                <Input id="address_phone" v-model="model.address_phone" class="mt-1" required />
                <p v-if="errors?.address_phone" class="mt-1 text-sm text-destructive">{{ errors.address_phone }}</p>
            </div>
        </div>
        <div>
            <Label for="full_address">Alamat Lengkap</Label>
            <textarea
                id="full_address"
                v-model="model.full_address"
                rows="2"
                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                required
            />
            <p v-if="errors?.full_address" class="mt-1 text-sm text-destructive">{{ errors.full_address }}</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <Label for="district">Kecamatan</Label>
                <Input id="district" v-model="model.district" class="mt-1" />
            </div>
            <div>
                <Label for="subdistrict">Kelurahan</Label>
                <Input id="subdistrict" v-model="model.subdistrict" class="mt-1" />
            </div>
            <div>
                <Label for="landmark">Patokan</Label>
                <Input id="landmark" v-model="model.landmark" class="mt-1" placeholder="Opsional" />
            </div>
        </div>
    </div>
</template>
