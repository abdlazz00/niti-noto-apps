<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { animate } from 'motion';

defineOptions({ layout: AppLayout });

const props = defineProps({
    stats:     Object,
    chartData: Array,
});

// Animated counter refs
const displayRevenue = ref(0);
const displayOrders  = ref(0);
const displayPending = ref(0);
const displayLaba    = ref(0);

function animateCounter(refVal, target, duration = 1400) {
    animate(0, target, {
        duration: duration / 1000,
        ease: [0.16, 1, 0.3, 1],
        onUpdate(v) { refVal.value = Math.round(v); },
    });
}

onMounted(() => {
    animateCounter(displayRevenue, props.stats.revenue_today);
    animateCounter(displayOrders,  props.stats.orders_today,    900);
    animateCounter(displayPending, props.stats.expense_pending, 800);
    animateCounter(displayLaba,    Math.max(0, props.stats.laba_bersih));
});

function formatPrice(v) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
}

// SVG bar chart helpers
const barWidth = 24;
const barGap   = 16;
const chartH   = 90;

const chartMax = computed(() => Math.max(...props.chartData.map(d => d.revenue), 1));
const svgW     = computed(() => props.chartData.length * (barWidth + barGap) - barGap);

function barH(revenue) { return Math.max(4, (revenue / chartMax.value) * chartH); }
function barY(revenue) { return chartH - barH(revenue); }

const statCards = computed(() => [
    { label: 'Revenue Hari Ini',  value: formatPrice(displayRevenue.value), icon: 'pi pi-wallet',              bg: 'bg-amber-50',  color: 'text-amber-500',  border: 'border-amber-100'  },
    { label: 'Total Order',       value: displayOrders.value,               icon: 'pi pi-shopping-bag',        bg: 'bg-blue-50',   color: 'text-blue-500',   border: 'border-blue-100'   },
    { label: 'Expense Pending',   value: displayPending.value,              icon: 'pi pi-exclamation-triangle', bg: 'bg-orange-50', color: 'text-orange-500', border: 'border-orange-100' },
    { label: 'Laba Bersih',       value: formatPrice(displayLaba.value),    icon: 'pi pi-chart-line',          bg: 'bg-green-50',  color: 'text-green-500',  border: 'border-green-100'  },
]);
</script>

<template>
    <Head title="Dashboard Owner" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Owner</h1>
            <p class="text-slate-500 text-sm mt-1">Ringkasan performa hari ini</p>
        </div>

        <!-- Stat cards with Motion.js counter animation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="bg-white rounded-2xl border shadow-sm p-5 hover:shadow-md transition-shadow"
                :class="card.border"
            >
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ card.label }}</span>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" :class="card.bg">
                        <i :class="[card.icon, card.color, 'text-sm']" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800 tabular-nums">{{ card.value }}</p>
            </div>
        </div>

        <!-- 7-day revenue SVG chart -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="mb-5">
                <h2 class="text-sm font-semibold text-slate-700">Revenue 7 Hari Terakhir</h2>
                <p class="text-xs text-slate-400 mt-0.5">Pendapatan per hari dari order selesai</p>
            </div>

            <div class="flex items-end gap-0 overflow-x-auto pb-1">
                <svg
                    :viewBox="`0 0 ${svgW} ${chartH + 28}`"
                    :width="svgW"
                    :height="chartH + 28"
                    class="overflow-visible shrink-0"
                >
                    <g v-for="(day, idx) in chartData" :key="idx">
                        <rect
                            :x="idx * (barWidth + barGap)"
                            :y="barY(day.revenue)"
                            :width="barWidth"
                            :height="barH(day.revenue)"
                            rx="6"
                            :fill="idx === chartData.length - 1 ? '#f59e0b' : '#fde68a'"
                            class="transition-all"
                        />
                        <text
                            :x="idx * (barWidth + barGap) + barWidth / 2"
                            :y="chartH + 18"
                            text-anchor="middle"
                            font-size="9"
                            fill="#94a3b8"
                        >{{ day.label }}</text>
                        <!-- Revenue label on hover via title -->
                        <title>{{ day.label }}: {{ formatPrice(day.revenue) }}</title>
                    </g>
                </svg>
            </div>
        </div>

        <!-- Quick links -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <Link
                :href="route('owner.expenses')"
                class="bg-white rounded-2xl border border-orange-100 shadow-sm p-4 flex items-center gap-3 hover:border-orange-300 transition-colors group"
            >
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                    <i class="pi pi-file text-orange-500 text-base" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700 group-hover:text-orange-600 transition-colors">Expense Pending</p>
                    <p class="text-xs text-slate-400">{{ stats.expense_pending }} menunggu review</p>
                </div>
            </Link>
            <Link
                :href="route('owner.menu-items.index')"
                class="bg-white rounded-2xl border border-amber-100 shadow-sm p-4 flex items-center gap-3 hover:border-amber-300 transition-colors group"
            >
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <i class="pi pi-list text-amber-500 text-base" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700 group-hover:text-amber-600 transition-colors">Kelola Menu</p>
                    <p class="text-xs text-slate-400">Tambah atau edit menu item</p>
                </div>
            </Link>
            <Link
                :href="route('owner.staff.index')"
                class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3 hover:border-slate-300 transition-colors group"
            >
                <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0">
                    <i class="pi pi-users text-slate-500 text-base" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700 group-hover:text-slate-600 transition-colors">Kelola Staff</p>
                    <p class="text-xs text-slate-400">Tambah atau edit akun staff</p>
                </div>
            </Link>
        </div>
    </div>
</template>
