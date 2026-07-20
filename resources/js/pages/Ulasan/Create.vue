<script setup lang="ts">
import { Button } from '@/components/ui/button';
import RatingStars from '@/components/RatingStars.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

interface Booking {
    id: number;
    booking_code: string;
    vehicle: { name: string } | null;
    package: { name: string } | null;
}

const props = defineProps<{ booking: Booking }>();

const form = useForm({
    rating: 5,
    comment: '',
});

function submit() {
    form.post(`/booking/${props.booking.id}/ulasan`);
}
</script>

<template>
    <Head title="Tulis Ulasan" />
    <PublicLayout>
        <div class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-foreground">Tulis Ulasan</h1>
            <p class="mt-1 text-muted-foreground">{{ booking.vehicle?.name ?? booking.package?.name }} &middot; {{ booking.booking_code }}</p>

            <form class="mt-6 space-y-4 rounded-xl border border-border bg-background p-6" @submit.prevent="submit">
                <div>
                    <p class="text-sm font-medium">Rating</p>
                    <RatingStars v-model:rating="form.rating" size="lg" class="mt-2" />
                    <p v-if="form.errors.rating" class="mt-1 text-sm text-destructive">{{ form.errors.rating }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium">Komentar (opsional)</p>
                    <textarea
                        v-model="form.comment"
                        rows="4"
                        class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        placeholder="Bagaimana pengalaman Anda?"
                    />
                    <p v-if="form.errors.comment" class="mt-1 text-sm text-destructive">{{ form.errors.comment }}</p>
                </div>

                <Button type="submit" :disabled="form.processing">Kirim Ulasan</Button>
            </form>
        </div>
    </PublicLayout>
</template>
