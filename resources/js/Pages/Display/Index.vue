<script setup>
import DisplayLayout from '@/Layouts/DisplayLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

defineOptions({ layout: DisplayLayout });

const props = defineProps({ readyOrders: Array });

const orders = ref([...props.readyOrders]);

let echoChannel = null;

onMounted(() => {
    if (window.Echo) {
        echoChannel = window.Echo.channel('orders');

        echoChannel.listen('OrderReadyForPickup', (e) => {
            const exists = orders.value.find(o => o.id === e.id);
            if (!exists) {
                orders.value.push({
                    id:           e.id,
                    order_number: e.order_number,
                    table_number: e.table_number,
                });
            }
        });

        echoChannel.listen('OrderCompleted', (e) => {
            orders.value = orders.value.filter(o => o.id !== e.id);
        });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave('orders');
    }
});
</script>

<template>
    <Head title="Display Antrian — Niti Noto" />

    <div class="flex-1 p-8">
        <!-- Empty state -->
        <div v-if="!orders.length" class="h-full flex flex-col items-center justify-center text-slate-500">
            <i class="pi pi-check-circle text-6xl mb-4 text-slate-600" />
            <p class="text-2xl font-bold text-slate-400">Belum Ada Pesanan Siap</p>
            <p class="text-slate-500 mt-2">Pesanan yang siap diambil akan muncul di sini</p>
        </div>

        <!-- Ready orders grid -->
        <div v-else>
            <p class="text-amber-400 text-sm font-semibold uppercase tracking-widest mb-6">
                Pesanan Siap Diambil — {{ orders.length }} order
            </p>

            <TransitionGroup
                name="order-card"
                tag="div"
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4"
            >
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-amber-500 rounded-2xl p-6 flex flex-col items-center justify-center text-center min-h-40 shadow-lg shadow-amber-900/30"
                >
                    <p class="text-white/70 text-xs font-semibold uppercase tracking-widest mb-2">
                        Meja {{ order.table_number }}
                    </p>
                    <p class="text-white font-black leading-none" style="font-size: clamp(1.8rem, 4vw, 3rem)">
                        {{ order.order_number }}
                    </p>
                </div>
            </TransitionGroup>
        </div>
    </div>
</template>

<style scoped>
.order-card-enter-active {
    transition: all 0.5s ease;
}
.order-card-leave-active {
    transition: all 0.4s ease;
}
.order-card-enter-from {
    opacity: 0;
    transform: scale(0.8);
}
.order-card-leave-to {
    opacity: 0;
    transform: scale(0.8);
}
</style>
