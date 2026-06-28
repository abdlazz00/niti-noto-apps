<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useCart } from '@/composables/useCart';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';

defineOptions({ layout: CustomerLayout });

const props = defineProps({ table: Object });

const { items, totalPrice, clear } = useCart(props.table.qr_code);
const notes = ref('');
const processing = ref(false);

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}

function goBack() {
    window.history.back();
}

function submit() {
    if (!items.value.length) return;
    processing.value = true;

    router.post(
        route('order.store', props.table.qr_code),
        {
            items: items.value.map(i => ({ id: i.id, qty: i.qty })),
            notes: notes.value || null,
        },
        {
            onSuccess: () => clear(),
            onFinish: () => { processing.value = false; },
        },
    );
}
</script>

<template>
    <Head :title="`Konfirmasi Pesanan — Meja ${table.number}`" />

    <div class="p-4 space-y-4">
        <!-- Header -->
        <div class="flex items-center gap-3 py-2">
            <button
                class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center"
                @click="goBack"
            >
                <i class="pi pi-arrow-left text-slate-600 text-sm" />
            </button>
            <div>
                <h1 class="font-bold text-slate-800 text-lg">Konfirmasi Pesanan</h1>
                <p class="text-xs text-slate-500">Meja {{ table.number }} — {{ table.name }}</p>
            </div>
        </div>

        <!-- Empty cart -->
        <div v-if="!items.length" class="text-center py-16 text-slate-400">
            <i class="pi pi-shopping-cart text-4xl mb-3 block" />
            <p class="text-sm">Keranjang kosong</p>
            <button class="mt-4 text-amber-500 font-semibold text-sm" @click="goBack">
                Kembali ke Menu
            </button>
        </div>

        <template v-else>
            <!-- Order items -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-50">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Rincian Pesanan</p>
                </div>
                <div class="divide-y divide-slate-50">
                    <div v-for="item in items" :key="item.id" class="px-4 py-3 flex items-center gap-3">
                        <!-- Thumbnail -->
                        <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <i class="pi pi-image text-slate-300 text-sm" />
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate">{{ item.name }}</p>
                            <p class="text-xs text-slate-400">{{ item.qty }} × {{ formatPrice(item.price) }}</p>
                        </div>

                        <span class="font-semibold text-slate-800 text-sm shrink-0">
                            {{ formatPrice(item.price * item.qty) }}
                        </span>
                    </div>
                </div>

                <!-- Total -->
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <span class="font-semibold text-slate-600">Total</span>
                    <span class="font-bold text-slate-800 text-lg">{{ formatPrice(totalPrice) }}</span>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-2">
                <label class="block text-sm font-medium text-slate-700">
                    Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <Textarea
                    v-model="notes"
                    rows="3"
                    class="w-full"
                    placeholder="Contoh: tidak pedas, tanpa es, kurang manis..."
                    autoResize
                />
            </div>

            <!-- Action buttons -->
            <div class="space-y-2 pt-2">
                <Button
                    label="Konfirmasi Pesanan"
                    icon="pi pi-check-circle"
                    class="w-full"
                    severity="warn"
                    size="large"
                    :loading="processing"
                    @click="submit"
                />
                <button
                    class="w-full py-3 text-slate-500 text-sm font-medium"
                    @click="goBack"
                >
                    Kembali ke Menu
                </button>
            </div>
        </template>
    </div>
</template>
