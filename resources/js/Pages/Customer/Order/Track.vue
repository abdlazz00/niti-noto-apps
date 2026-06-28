<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: CustomerLayout });

const props = defineProps({ order: Object });

const statusSteps = [
    { key: 'menunggu',     label: 'Menunggu',      icon: 'pi-clock' },
    { key: 'diterima',     label: 'Diterima',       icon: 'pi-check' },
    { key: 'sedang_dibuat', label: 'Dibuat',        icon: 'pi-cog' },
    { key: 'siap_diambil', label: 'Siap Diambil',   icon: 'pi-bell' },
    { key: 'selesai',      label: 'Selesai',        icon: 'pi-check-circle' },
];

const currentStepIndex = computed(() =>
    statusSteps.findIndex(s => s.key === props.order.status)
);

function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
}
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="p-4 space-y-5">
        <!-- Order number header -->
        <div class="text-center pt-4 pb-2">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-50 mb-3">
                <i class="pi pi-receipt text-amber-500 text-2xl" />
            </div>
            <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Nomor Order</p>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ order.order_number }}</p>
            <p class="text-sm text-slate-500 mt-1">Meja {{ order.table.number }} — {{ order.table.name }}</p>
        </div>

        <!-- Status stepper -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-4">Status Pesanan</p>
            <div class="space-y-1">
                <div
                    v-for="(step, idx) in statusSteps"
                    :key="step.key"
                    class="flex items-center gap-3"
                >
                    <!-- Step indicator -->
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm transition-colors"
                            :class="{
                                'bg-amber-500 text-white shadow-md shadow-amber-200': idx === currentStepIndex,
                                'bg-emerald-500 text-white': idx < currentStepIndex,
                                'bg-slate-100 text-slate-400': idx > currentStepIndex,
                            }"
                        >
                            <i v-if="idx < currentStepIndex" class="pi pi-check text-xs" />
                            <i v-else :class="`pi ${step.icon} text-xs`" />
                        </div>
                        <div
                            v-if="idx < statusSteps.length - 1"
                            class="w-0.5 h-5 mt-0.5"
                            :class="idx < currentStepIndex ? 'bg-emerald-300' : 'bg-slate-100'"
                        />
                    </div>

                    <!-- Label -->
                    <p
                        class="text-sm font-medium"
                        :class="{
                            'text-amber-600 font-bold': idx === currentStepIndex,
                            'text-emerald-600': idx < currentStepIndex,
                            'text-slate-400': idx > currentStepIndex,
                        }"
                    >
                        {{ step.label }}
                        <span v-if="idx === currentStepIndex" class="ml-2 text-xs font-normal">← sekarang</span>
                    </p>
                </div>
            </div>

            <!-- Realtime note -->
            <p class="mt-4 text-xs text-slate-400 text-center">
                <i class="pi pi-info-circle mr-1" />
                Refresh halaman untuk update status terbaru
            </p>
        </div>

        <!-- Order items summary -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-50">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Rincian Pesanan</p>
            </div>
            <div class="divide-y divide-slate-50">
                <div v-for="(item, idx) in order.items" :key="idx" class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-slate-800 text-sm">{{ item.name }}</p>
                        <p class="text-xs text-slate-400">{{ item.qty }} × {{ formatPrice(item.price) }}</p>
                    </div>
                    <span class="font-semibold text-slate-700 text-sm">{{ formatPrice(item.subtotal) }}</span>
                </div>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50 flex justify-between">
                <span class="font-semibold text-slate-600 text-sm">Total</span>
                <span class="font-bold text-slate-800">{{ formatPrice(order.total) }}</span>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
            <p class="text-xs font-semibold text-amber-700 mb-1">Catatan</p>
            <p class="text-sm text-amber-800">{{ order.notes }}</p>
        </div>

        <!-- Back to menu -->
        <div class="text-center pt-2 pb-4">
            <Link
                :href="route('order.menu', order.table.qr_code)"
                class="text-amber-500 font-semibold text-sm"
            >
                <i class="pi pi-arrow-left mr-1 text-xs" /> Kembali ke Menu
            </Link>
        </div>
    </div>
</template>
