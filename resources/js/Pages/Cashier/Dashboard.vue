<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { animate } from 'motion';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

defineOptions({ layout: AppLayout });

const props = defineProps({
    shift: Object,
    stats: Object,
});

const displayOrders    = ref(0);
const displayCompleted = ref(0);
const displayActive    = ref(0);
const displayRevenue   = ref(0);

function animateCounter(refVal, target, duration = 1200) {
    animate(0, target, {
        duration: duration / 1000,
        ease: [0.16, 1, 0.3, 1],
        onUpdate(v) { refVal.value = Math.round(v); },
    });
}

onMounted(() => {
    animateCounter(displayOrders,    props.stats.orders_today);
    animateCounter(displayCompleted, props.stats.completed_today, 900);
    animateCounter(displayActive,    props.stats.active_orders,   800);
    animateCounter(displayRevenue,   props.stats.revenue_shift);
});

function formatPrice(v) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
}
</script>

<template>
    <Head title="Dashboard Kasir" />

    <div class="space-y-6">

        <!-- Header + Shift control -->
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Dashboard Kasir</h1>
                <p class="text-slate-500 text-sm mt-1">Ringkasan shift hari ini</p>
            </div>

            <!-- Shift panel -->
            <div class="bg-white rounded-2xl border shadow-sm p-4 flex items-center gap-4 min-w-60"
                :class="shift ? 'border-green-100' : 'border-amber-100'">
                <div class="flex-1">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Status Shift</p>
                    <div v-if="shift" class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                        <span class="text-sm font-semibold text-green-700">Aktif sejak {{ shift.started_at }}</span>
                    </div>
                    <p v-else class="text-sm font-semibold text-amber-600">Belum ada shift aktif</p>
                </div>
                <Button
                    v-if="!shift"
                    label="Mulai"
                    icon="pi pi-play"
                    size="small"
                    severity="success"
                    @click="router.post(route('cashier.shift.start'))"
                />
                <Button
                    v-else
                    label="Tutup"
                    icon="pi pi-stop"
                    size="small"
                    severity="secondary"
                    @click="router.post(route('cashier.shift.end'))"
                />
            </div>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Order Hari Ini</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                        <i class="pi pi-shopping-bag text-blue-500 text-sm" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800 tabular-nums">{{ displayOrders }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Selesai Hari Ini</span>
                    <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center">
                        <i class="pi pi-check-circle text-green-500 text-sm" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800 tabular-nums">{{ displayCompleted }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Order Aktif</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                        <i class="pi pi-clock text-amber-500 text-sm" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800 tabular-nums">{{ displayActive }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Revenue Shift</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center">
                        <i class="pi pi-wallet text-purple-500 text-sm" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800 tabular-nums">{{ formatPrice(displayRevenue) }}</p>
            </div>
        </div>

        <!-- Quick links -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Link
                :href="route('cashier.pos')"
                class="bg-amber-500 rounded-2xl p-5 flex items-center gap-4 hover:bg-amber-600 transition-colors group"
            >
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                    <i class="pi pi-desktop text-white text-xl" />
                </div>
                <div>
                    <p class="font-bold text-white">Buka POS</p>
                    <p class="text-amber-100 text-sm mt-0.5">Input order manual dari kasir</p>
                </div>
                <i class="pi pi-arrow-right text-white ml-auto opacity-70 group-hover:translate-x-1 transition-transform" />
            </Link>
            <Link
                :href="route('cashier.orders')"
                class="bg-white rounded-2xl border border-slate-100 p-5 flex items-center gap-4 hover:border-amber-300 transition-colors group"
            >
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <i class="pi pi-inbox text-amber-500 text-xl" />
                </div>
                <div>
                    <p class="font-bold text-slate-800">Order Masuk</p>
                    <p class="text-slate-500 text-sm mt-0.5">{{ stats.active_orders }} order aktif perlu perhatian</p>
                </div>
                <i class="pi pi-arrow-right text-slate-400 ml-auto group-hover:text-amber-500 group-hover:translate-x-1 transition-all" />
            </Link>
        </div>
    </div>
</template>
