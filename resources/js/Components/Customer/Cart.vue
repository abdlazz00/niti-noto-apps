<script setup>
import { computed } from 'vue';
import Drawer from 'primevue/drawer';
import Button from 'primevue/button';

const props = defineProps({
    visible:    { type: Boolean, default: false },
    items:      { type: Array, default: () => [] },
    totalPrice: { type: Number, default: 0 },
    qrCode:     { type: String, required: true },
});

const emit = defineEmits(['update:visible', 'setQty', 'remove', 'checkout']);

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}

function close() {
    emit('update:visible', false);
}

function checkout() {
    close();
    emit('checkout');
}
</script>

<template>
    <Drawer
        :visible="visible"
        position="bottom"
        :style="{ height: 'auto', maxHeight: '80vh', borderRadius: '1.5rem 1.5rem 0 0' }"
        @update:visible="emit('update:visible', $event)"
        pt:root:class="max-w-md mx-auto left-0 right-0"
        pt:header:class="px-5 pt-5 pb-2"
        pt:content:class="px-5 pb-6"
    >
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="font-bold text-slate-800 text-lg">Keranjang</h2>
                <span v-if="items.length" class="text-xs text-slate-500">{{ items.length }} item</span>
            </div>
        </template>

        <!-- Empty state -->
        <div v-if="!items.length" class="py-12 text-center text-slate-400">
            <i class="pi pi-shopping-cart text-4xl mb-3 block" />
            <p class="text-sm">Keranjang masih kosong</p>
            <p class="text-xs mt-1">Tambahkan menu favoritmu!</p>
        </div>

        <!-- Cart items -->
        <div v-else class="space-y-4">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex items-center gap-3"
            >
                <!-- Thumbnail -->
                <div class="w-14 h-14 rounded-xl bg-slate-100 overflow-hidden shrink-0">
                    <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.name" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <i class="pi pi-image text-slate-300" />
                    </div>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-800 text-sm truncate">{{ item.name }}</p>
                    <p class="text-amber-600 font-semibold text-sm">{{ formatPrice(item.price) }}</p>
                </div>

                <!-- Qty control -->
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors"
                        @click="emit('setQty', item.id, item.qty - 1)"
                    >
                        <i class="pi pi-minus text-xs text-slate-600" />
                    </button>
                    <span class="w-5 text-center font-bold text-slate-800 text-sm">{{ item.qty }}</span>
                    <button
                        class="w-7 h-7 rounded-full bg-amber-500 hover:bg-amber-400 flex items-center justify-center transition-colors"
                        @click="emit('setQty', item.id, item.qty + 1)"
                    >
                        <i class="pi pi-plus text-xs text-white" />
                    </button>
                </div>
            </div>

            <!-- Divider + Total -->
            <div class="border-t border-slate-100 pt-4 flex items-center justify-between">
                <span class="font-medium text-slate-600 text-sm">Total</span>
                <span class="font-bold text-slate-800 text-lg">{{ formatPrice(totalPrice) }}</span>
            </div>

            <!-- Checkout button -->
            <Button
                label="Pesan Sekarang"
                icon="pi pi-arrow-right"
                iconPos="right"
                class="w-full"
                severity="warn"
                size="large"
                @click="checkout"
            />
        </div>
    </Drawer>
</template>
