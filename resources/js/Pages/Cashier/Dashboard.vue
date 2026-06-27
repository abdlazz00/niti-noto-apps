<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const statCards = [
    { label: 'Shift Berjalan', value: '-', icon: 'pi pi-clock', bg: 'bg-amber-50', color: 'text-amber-500' },
    { label: 'Order Aktif', value: '0', icon: 'pi pi-shopping-bag', bg: 'bg-blue-50', color: 'text-blue-500' },
    { label: 'Siap Diambil', value: '0', icon: 'pi pi-check-circle', bg: 'bg-green-50', color: 'text-green-500' },
    { label: 'Revenue Shift', value: 'Rp 0', icon: 'pi pi-wallet', bg: 'bg-purple-50', color: 'text-purple-500' },
];
</script>

<template>
    <Head title="Dashboard Kasir" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Kasir</h1>
            <p class="text-slate-500 text-sm mt-1">Ringkasan shift hari ini</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="card in statCards" :key="card.label"
                class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ card.label }}</span>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" :class="card.bg">
                        <i :class="[card.icon, card.color, 'text-sm']" />
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-800">{{ card.value }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-700 mb-4">Order Masuk</h2>
            <div class="h-32 flex items-center justify-center text-slate-400 text-sm bg-slate-50 rounded-xl">
                List order akan ditampilkan di Sprint 9 (POS)
            </div>
        </div>
    </div>
</template>
