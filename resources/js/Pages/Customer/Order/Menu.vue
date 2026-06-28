<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Cart from '@/Components/Customer/Cart.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useCart } from '@/composables/useCart';

defineOptions({ layout: CustomerLayout });

const props = defineProps({
    table:      Object,
    categories: Array,
    items:      Array,
});

const { items: cartItems, totalQty, totalPrice, add, setQty, getItemQty } = useCart(props.table.qr_code);

const activeCategory = ref(null);
const cartOpen = ref(false);

const filteredItems = computed(() => {
    if (!activeCategory.value) return props.items;
    return props.items.filter(i => i.category_id === activeCategory.value);
});

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}

function goToCheckout() {
    router.visit(route('order.checkout', props.table.qr_code));
}
</script>

<template>
    <Head :title="`Menu — Meja ${table.number}`" />

    <!-- Category tabs -->
    <div class="sticky top-[57px] z-10 bg-white border-b border-slate-100">
        <div class="flex gap-2 px-4 py-3 overflow-x-auto no-scrollbar">
            <button
                class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                :class="!activeCategory
                    ? 'bg-amber-500 text-white'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                @click="activeCategory = null"
            >
                Semua
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                class="shrink-0 px-4 py-1.5 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                :class="activeCategory === cat.id
                    ? 'bg-amber-500 text-white'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                @click="activeCategory = cat.id"
            >
                {{ cat.name }}
            </button>
        </div>
    </div>

    <!-- Menu grid -->
    <div class="p-4">
        <div v-if="!filteredItems.length" class="text-center py-16 text-slate-400">
            <i class="pi pi-list text-4xl mb-3 block" />
            <p class="text-sm">Tidak ada menu di kategori ini</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div
                v-for="item in filteredItems"
                :key="item.id"
                class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm flex flex-col"
            >
                <!-- Image -->
                <div class="aspect-square bg-slate-100 overflow-hidden relative">
                    <img
                        v-if="item.image"
                        :src="`/storage/${item.image}`"
                        :alt="item.name"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <i class="pi pi-image text-slate-200 text-3xl" />
                    </div>
                </div>

                <!-- Info -->
                <div class="p-3 flex flex-col flex-1">
                    <p class="text-sm font-semibold text-slate-800 leading-tight line-clamp-2">{{ item.name }}</p>
                    <p class="text-amber-600 font-bold text-sm mt-1">{{ formatPrice(item.price) }}</p>

                    <!-- Add / Qty control -->
                    <div class="mt-auto pt-3">
                        <button
                            v-if="getItemQty(item.id) === 0"
                            class="w-full bg-amber-500 hover:bg-amber-400 text-white rounded-xl py-2 text-sm font-semibold transition-colors flex items-center justify-center gap-1"
                            @click="add(item)"
                        >
                            <i class="pi pi-plus text-xs" /> Tambah
                        </button>
                        <div v-else class="flex items-center justify-between bg-slate-100 rounded-xl px-2 py-1">
                            <button
                                class="w-7 h-7 rounded-full bg-white shadow-sm flex items-center justify-center"
                                @click="setQty(item.id, getItemQty(item.id) - 1)"
                            >
                                <i class="pi pi-minus text-xs text-slate-600" />
                            </button>
                            <span class="font-bold text-slate-800 text-sm">{{ getItemQty(item.id) }}</span>
                            <button
                                class="w-7 h-7 rounded-full bg-amber-500 flex items-center justify-center"
                                @click="add(item)"
                            >
                                <i class="pi pi-plus text-xs text-white" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating cart button -->
    <Transition name="cart-btn">
        <div
            v-if="totalQty > 0"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-30 w-full max-w-sm px-4"
        >
            <button
                class="w-full bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white rounded-2xl py-4 px-5 flex items-center justify-between shadow-lg shadow-amber-500/30 transition-colors"
                @click="cartOpen = true"
            >
                <div class="flex items-center gap-2">
                    <span class="bg-white/20 rounded-lg px-2 py-0.5 font-bold text-sm">{{ totalQty }}</span>
                    <span class="font-semibold">Lihat Keranjang</span>
                </div>
                <span class="font-bold">
                    {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(totalPrice) }}
                </span>
            </button>
        </div>
    </Transition>

    <!-- Cart drawer -->
    <Cart
        v-model:visible="cartOpen"
        :items="cartItems"
        :total-price="totalPrice"
        :qr-code="table.qr_code"
        @setQty="setQty"
        @checkout="goToCheckout"
    />
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.cart-btn-enter-active,
.cart-btn-leave-active {
    transition: all 0.3s ease;
}
.cart-btn-enter-from,
.cart-btn-leave-to {
    opacity: 0;
    transform: translate(-50%, 1rem);
}
</style>
