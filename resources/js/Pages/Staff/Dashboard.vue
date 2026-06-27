<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const statCards = [
    { label: 'Antrian Saat Ini', value: '0', icon: 'pi pi-list', bg: 'bg-amber-50', color: 'text-amber-500' },
    { label: 'Selesai Hari Ini', value: '0', icon: 'pi pi-check', bg: 'bg-green-50', color: 'text-green-500' },
];
</script>

<template>
    <Head title="Dashboard Staff" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Antrian Order</h1>
            <p class="text-slate-500 text-sm mt-1">Status pesanan yang perlu diproses</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
            <h2 class="text-base font-semibold text-slate-700 mb-4">Order Diterima</h2>
            <div class="h-32 flex items-center justify-center text-slate-400 text-sm bg-slate-50 rounded-xl">
                Antrian order akan ditampilkan di Sprint 10
            </div>
        </div>
    </div>
</template>
