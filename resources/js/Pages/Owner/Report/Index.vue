<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { animate } from 'motion';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

defineOptions({ layout: AppLayout });

const props = defineProps({
    summary:    Object,
    chartData:  Array,
    topItems:   Array,
    byCategory: Array,
    shifts:     Array,
    comparison: Object,
    filters:    Object,
});

// Period filter
const activePeriod = ref(props.filters.period);
const customFrom   = ref(props.filters.from);
const customTo     = ref(props.filters.to);

function applyPeriod(period) {
    activePeriod.value = period;
    if (period !== 'custom') {
        router.get(route('owner.reports'), { period }, { preserveState: false });
    }
}

function applyCustom() {
    router.get(route('owner.reports'), { period: 'custom', from: customFrom.value, to: customTo.value }, { preserveState: false });
}

// Motion.js counter animation
const displayRevenue  = ref(0);
const displayExpenses = ref(0);
const displayLaba     = ref(0);

function animateCounter(refVal, target, delay = 0) {
    setTimeout(() => {
        animate(0, target, {
            duration: 1.4,
            ease: [0.16, 1, 0.3, 1],
            onUpdate(v) { refVal.value = Math.round(v); },
        });
    }, delay);
}

onMounted(() => {
    animateCounter(displayRevenue,  props.summary.revenue,     0);
    animateCounter(displayExpenses, props.summary.expenses,    200);
    animateCounter(displayLaba,     Math.max(0, props.summary.laba_bersih), 400);
});

function formatPrice(v) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
}

function pctClass(v) {
    if (v > 0)  return 'text-green-600 bg-green-50 border-green-100';
    if (v < 0)  return 'text-red-600 bg-red-50 border-red-100';
    return 'text-slate-500 bg-slate-50 border-slate-100';
}

function pctLabel(v) {
    if (v > 0) return `+${v}%`;
    if (v < 0) return `${v}%`;
    return '0%';
}

// SVG dual-line chart
const chartH   = 100;
const pointGap = computed(() => Math.max(24, Math.floor(560 / Math.max(props.chartData.length, 1))));
const svgW     = computed(() => (props.chartData.length - 1) * pointGap.value + 16);
const chartMax = computed(() => Math.max(...props.chartData.flatMap(d => [d.revenue, d.expenses]), 1));

function ptX(idx)     { return idx * pointGap.value + 8; }
function ptY(val)     { return chartH - (val / chartMax.value) * chartH; }
function polypoints(key) {
    return props.chartData.map((d, i) => `${ptX(i)},${ptY(d[key])}`).join(' ');
}

// Category max for bar widths
const catMax = computed(() => Math.max(...props.byCategory.map(c => c.total), 1));

const periodLabels = { today: 'Hari Ini', week: 'Minggu Ini', month: 'Bulan Ini', custom: 'Kustom' };
</script>

<template>
    <Head title="Laporan Keuangan" />

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Laporan Keuangan</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Periode: {{ new Date(filters.from).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) }}
                    –
                    {{ new Date(filters.to).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) }}
                </p>
            </div>
            <div class="flex gap-2">
                <a :href="route('owner.reports.pdf', { period: filters.period, from: filters.from, to: filters.to })" target="_blank">
                    <Button label="Export PDF" icon="pi pi-file-pdf" severity="danger" size="small" outlined />
                </a>
                <a :href="route('owner.reports.csv', { period: filters.period, from: filters.from, to: filters.to })">
                    <Button label="Export CSV" icon="pi pi-download" severity="secondary" size="small" outlined />
                </a>
            </div>
        </div>

        <!-- Period filter -->
        <div class="flex flex-wrap items-center gap-2">
            <button
                v-for="p in ['today', 'week', 'month', 'custom']"
                :key="p"
                class="px-4 py-1.5 rounded-full text-xs font-semibold transition-colors"
                :class="activePeriod === p
                    ? 'bg-amber-500 text-white'
                    : 'bg-white border border-slate-200 text-slate-600 hover:border-amber-300'"
                @click="applyPeriod(p)"
            >
                {{ periodLabels[p] }}
            </button>

            <!-- Custom date inputs -->
            <template v-if="activePeriod === 'custom'">
                <input
                    v-model="customFrom"
                    type="date"
                    class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 text-slate-600 focus:outline-none focus:border-amber-400"
                />
                <span class="text-slate-400 text-xs">s/d</span>
                <input
                    v-model="customTo"
                    type="date"
                    class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 text-slate-600 focus:outline-none focus:border-amber-400"
                />
                <Button label="Terapkan" size="small" @click="applyCustom" />
            </template>
        </div>

        <!-- Summary cards with Motion.js counter -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-amber-50 rounded-2xl border border-amber-100 p-5 shadow-sm">
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-2">Revenue</p>
                <p class="text-2xl font-black text-amber-700 tabular-nums">{{ formatPrice(displayRevenue) }}</p>
            </div>
            <div class="bg-red-50 rounded-2xl border border-red-100 p-5 shadow-sm">
                <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-2">Total Expense</p>
                <p class="text-2xl font-black text-red-600 tabular-nums">{{ formatPrice(displayExpenses) }}</p>
            </div>
            <div class="bg-green-50 rounded-2xl border border-green-100 p-5 shadow-sm">
                <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-2">Laba Bersih</p>
                <p class="text-2xl font-black text-green-700 tabular-nums">{{ formatPrice(displayLaba) }}</p>
            </div>
        </div>

        <!-- Period comparison -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">
                Perbandingan vs Periode Sebelumnya ({{ comparison.prev_period }})
            </p>
            <div class="grid grid-cols-3 gap-4">
                <div v-for="(item, key) in [
                    { label: 'Revenue', val: comparison.revenue_change },
                    { label: 'Expense', val: comparison.expense_change },
                    { label: 'Laba Bersih', val: comparison.laba_change },
                ]" :key="item.label" class="text-center">
                    <p class="text-xs text-slate-400 mb-1">{{ item.label }}</p>
                    <span
                        class="inline-block px-3 py-1 rounded-full border text-sm font-bold"
                        :class="pctClass(item.val)"
                    >
                        {{ pctLabel(item.val) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Dual-line SVG chart -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-700">Tren Revenue vs Expense</h2>
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-amber-400 inline-block"></span> Revenue</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-red-400 inline-block"></span> Expense</span>
                </div>
            </div>

            <div class="overflow-x-auto pb-2">
                <svg
                    :viewBox="`0 0 ${svgW} ${chartH + 20}`"
                    :width="svgW"
                    :height="chartH + 20"
                    class="overflow-visible shrink-0"
                >
                    <!-- Revenue line -->
                    <polyline
                        :points="polypoints('revenue')"
                        fill="none"
                        stroke="#f59e0b"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <!-- Expense line -->
                    <polyline
                        :points="polypoints('expenses')"
                        fill="none"
                        stroke="#f87171"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-dasharray="4 3"
                    />
                    <!-- Data points + labels -->
                    <g v-for="(d, i) in chartData" :key="i">
                        <circle :cx="ptX(i)" :cy="ptY(d.revenue)"  r="3" fill="#f59e0b" />
                        <circle :cx="ptX(i)" :cy="ptY(d.expenses)" r="2.5" fill="#f87171" />
                        <text
                            v-if="chartData.length <= 14 || i % 2 === 0"
                            :x="ptX(i)"
                            :y="chartH + 14"
                            text-anchor="middle"
                            font-size="8"
                            fill="#94a3b8"
                        >{{ d.date }}</text>
                    </g>
                </svg>
            </div>
        </div>

        <!-- Bottom 3-column grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Top menu items -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-50">
                    <h2 class="text-sm font-semibold text-slate-700">Top Menu Item</h2>
                </div>
                <div v-if="!topItems.length" class="p-8 text-center text-slate-400 text-sm">Belum ada data</div>
                <div v-else class="divide-y divide-slate-50">
                    <div v-for="(item, idx) in topItems" :key="idx" class="px-5 py-3 flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-amber-50 text-amber-600 text-xs font-bold flex items-center justify-center shrink-0">{{ idx + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ item.name }}</p>
                            <p class="text-xs text-slate-400">{{ item.category }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-semibold text-slate-700">{{ item.total_qty }} terjual</p>
                            <p class="text-xs text-slate-400">{{ formatPrice(item.total_revenue) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue by category -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-50">
                    <h2 class="text-sm font-semibold text-slate-700">Per Kategori</h2>
                </div>
                <div v-if="!byCategory.length" class="p-8 text-center text-slate-400 text-sm">Belum ada data</div>
                <div v-else class="px-5 py-4 space-y-3">
                    <div v-for="(cat, idx) in byCategory" :key="idx" class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-600 font-medium">{{ cat.category }}</span>
                            <span class="text-slate-400">{{ formatPrice(cat.total) }}</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-amber-400 rounded-full transition-all"
                                :style="{ width: `${(cat.total / catMax) * 100}%` }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift summary -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50">
                <h2 class="text-sm font-semibold text-slate-700">Ringkasan Shift</h2>
            </div>
            <div v-if="!shifts.length" class="p-8 text-center text-slate-400 text-sm">Tidak ada shift pada periode ini</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Kasir</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Mulai</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wide">Selesai</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wide">Order</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wide">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="(shift, idx) in shifts" :key="idx">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ shift.cashier }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ shift.started_at }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ shift.ended_at }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-700">{{ shift.orders }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-amber-600">{{ formatPrice(shift.revenue) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
