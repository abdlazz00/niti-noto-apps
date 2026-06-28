<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineOptions({ layout: AppLayout });

const props = defineProps({
    pending:     Array,
    readyOrders: Array,
});

const toast      = useToast();
const page       = usePage();
const activeTab  = ref('menunggu');

// Local reactive copies so realtime can mutate them
const pendingOrders = ref([...props.pending]);
const readyList     = ref([...props.readyOrders]);

watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.add({ severity: 'success', summary: flash.success, life: 3000 });
    if (flash?.error)   toast.add({ severity: 'error',   summary: flash.error,   life: 4000 });
}, { immediate: true });

// When Inertia reloads page props, sync local state
watch(() => props.pending,     val => { pendingOrders.value = [...val]; });
watch(() => props.readyOrders, val => { readyList.value = [...val]; });

// Realtime
let cashierChannel = null;
let ordersChannel  = null;

onMounted(() => {
    if (! window.Echo) return;

    // Private cashier channel — new orders from customers
    cashierChannel = window.Echo.private('cashier');
    cashierChannel.listen('NewOrderReceived', (e) => {
        const exists = pendingOrders.value.find(o => o.id === e.id);
        if (! exists) {
            // Add minimal order info from event; full data will come on next page visit
            pendingOrders.value.push({
                id:           e.id,
                order_number: e.order_number,
                table_number: e.table_number,
                table_name:   e.table_name,
                status:       'menunggu',
                total:        e.total,
                notes:        null,
                created_at:   new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                items:        [],
            });
        }
        toast.add({
            severity: 'info',
            summary: `Order baru! ${e.order_number}`,
            detail:  `Meja ${e.table_number} — ${e.table_name}`,
            life:    6000,
        });
        activeTab.value = 'menunggu';
    });

    // Public orders channel — staff marks siap_diambil
    ordersChannel = window.Echo.channel('orders');
    ordersChannel.listen('OrderReadyForPickup', (e) => {
        const exists = readyList.value.find(o => o.id === e.id);
        if (! exists) {
            readyList.value.push({
                id:           e.id,
                order_number: e.order_number,
                table_number: e.table_number,
                table_name:   '',
                status:       'siap_diambil',
                total:        0,
                notes:        null,
                created_at:   '',
                items:        [],
            });
        }
        toast.add({
            severity: 'success',
            summary:  `Siap diambil: ${e.order_number}`,
            detail:   `Meja ${e.table_number}`,
            life:     6000,
        });
        activeTab.value = 'siap_diambil';
    });

    ordersChannel.listen('OrderCompleted', (e) => {
        readyList.value = readyList.value.filter(o => o.id !== e.id);
    });
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('cashier');
        window.Echo.leave('orders');
    }
});

function confirmOrder(order) {
    router.patch(route('cashier.orders.confirm', order.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            pendingOrders.value = pendingOrders.value.filter(o => o.id !== order.id);
        },
    });
}

function completeOrder(order) {
    router.patch(route('cashier.orders.complete', order.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            readyList.value = readyList.value.filter(o => o.id !== order.id);
        },
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}
</script>

<template>
    <Head title="Order Masuk — Kasir" />
    <Toast />

    <div class="space-y-5">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Order Masuk</h1>
                <p class="text-sm text-slate-500 mt-0.5">Konfirmasi dan selesaikan order</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                <span class="text-xs text-slate-400">Live update aktif</span>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2">
            <button
                class="px-5 py-2 rounded-full text-sm font-semibold transition-colors flex items-center gap-2"
                :class="activeTab === 'menunggu'
                    ? 'bg-amber-500 text-white shadow-sm'
                    : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300'"
                @click="activeTab = 'menunggu'"
            >
                Menunggu Konfirmasi
                <span
                    v-if="pendingOrders.length"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold"
                    :class="activeTab === 'menunggu' ? 'bg-white text-amber-500' : 'bg-amber-100 text-amber-600'"
                >
                    {{ pendingOrders.length }}
                </span>
            </button>
            <button
                class="px-5 py-2 rounded-full text-sm font-semibold transition-colors flex items-center gap-2"
                :class="activeTab === 'siap_diambil'
                    ? 'bg-green-500 text-white shadow-sm'
                    : 'bg-white border border-slate-200 text-slate-600 hover:border-green-300'"
                @click="activeTab = 'siap_diambil'"
            >
                Siap Diambil
                <span
                    v-if="readyList.length"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold"
                    :class="activeTab === 'siap_diambil' ? 'bg-white text-green-500' : 'bg-green-100 text-green-600'"
                >
                    {{ readyList.length }}
                </span>
            </button>
        </div>

        <!-- Tab: Menunggu Konfirmasi -->
        <div v-if="activeTab === 'menunggu'">
            <div v-if="!pendingOrders.length" class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
                <i class="pi pi-inbox text-4xl mb-3 block" />
                <p class="font-medium">Tidak ada order menunggu</p>
            </div>

            <TransitionGroup name="order-card" tag="div" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div
                    v-for="order in pendingOrders"
                    :key="order.id"
                    class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden"
                >
                    <!-- Card header -->
                    <div class="bg-amber-50 px-4 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-black text-amber-700">{{ order.order_number }}</p>
                            <p class="text-xs text-amber-600">Meja {{ order.table_number }}{{ order.table_name ? ` — ${order.table_name}` : '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-amber-500">{{ order.created_at }}</p>
                            <Tag value="Menunggu" severity="warn" class="text-xs mt-1" />
                        </div>
                    </div>

                    <!-- Items -->
                    <div v-if="order.items.length" class="divide-y divide-slate-50">
                        <div v-for="(item, idx) in order.items" :key="idx" class="px-4 py-2 flex justify-between text-sm">
                            <span class="text-slate-700">{{ item.qty }}× {{ item.name }}</span>
                            <span class="text-slate-500">{{ formatPrice(item.subtotal) }}</span>
                        </div>
                    </div>
                    <div v-else class="px-4 py-3 text-sm text-slate-400 italic">
                        Detail item tersedia setelah refresh
                    </div>

                    <!-- Notes -->
                    <div v-if="order.notes" class="px-4 py-2 bg-amber-50/50">
                        <p class="text-xs text-amber-700"><span class="font-semibold">Catatan:</span> {{ order.notes }}</p>
                    </div>

                    <!-- Footer -->
                    <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-sm">{{ formatPrice(order.total) }}</span>
                        <Button
                            label="Konfirmasi"
                            icon="pi pi-check"
                            size="small"
                            @click="confirmOrder(order)"
                        />
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Tab: Siap Diambil -->
        <div v-if="activeTab === 'siap_diambil'">
            <div v-if="!readyList.length" class="bg-white rounded-2xl border border-slate-100 p-12 text-center text-slate-400">
                <i class="pi pi-check-circle text-4xl mb-3 block text-green-300" />
                <p class="font-medium">Tidak ada order siap diambil</p>
            </div>

            <TransitionGroup name="order-card" tag="div" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <div
                    v-for="order in readyList"
                    :key="order.id"
                    class="bg-white rounded-2xl border border-green-100 shadow-sm overflow-hidden"
                >
                    <!-- Card header -->
                    <div class="bg-green-50 px-4 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-black text-green-700">{{ order.order_number }}</p>
                            <p class="text-xs text-green-600">Meja {{ order.table_number }}{{ order.table_name ? ` — ${order.table_name}` : '' }}</p>
                        </div>
                        <Tag value="Siap Diambil" severity="success" class="text-xs" />
                    </div>

                    <!-- Items -->
                    <div v-if="order.items.length" class="divide-y divide-slate-50">
                        <div v-for="(item, idx) in order.items" :key="idx" class="px-4 py-2 flex justify-between text-sm">
                            <span class="text-slate-700">{{ item.qty }}× {{ item.name }}</span>
                            <span class="text-slate-500">{{ formatPrice(item.subtotal) }}</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-sm">{{ formatPrice(order.total) }}</span>
                        <Button
                            label="Selesai / Terima Bayar"
                            icon="pi pi-wallet"
                            size="small"
                            severity="success"
                            @click="completeOrder(order)"
                        />
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<style scoped>
.order-card-enter-active { transition: all 0.4s ease; }
.order-card-leave-active { transition: all 0.3s ease; }
.order-card-enter-from   { opacity: 0; transform: translateY(-12px); }
.order-card-leave-to     { opacity: 0; transform: scale(0.95); }
</style>
