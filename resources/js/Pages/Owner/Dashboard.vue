<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

const statCards = [
    { label: 'Revenue Hari Ini', value: 'Rp 0', icon: 'pi pi-wallet', bg: 'bg-amber-50', color: 'text-amber-500' },
    { label: 'Total Order', value: '0', icon: 'pi pi-shopping-cart', bg: 'bg-blue-50', color: 'text-blue-500' },
    { label: 'Expense Pending', value: '0', icon: 'pi pi-clock', bg: 'bg-orange-50', color: 'text-orange-500' },
    { label: 'Staff Aktif', value: '0', icon: 'pi pi-users', bg: 'bg-green-50', color: 'text-green-500' },
];
</script>

<template>
    <Head title="Dashboard Owner" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
            <p class="text-slate-500 text-sm mt-1">Selamat datang, Owner!</p>
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
            <h2 class="text-base font-semibold text-slate-700 mb-4">Tren Revenue 7 Hari</h2>
            <div class="h-48 flex items-center justify-center text-slate-400 text-sm bg-slate-50 rounded-xl">
                Grafik akan ditampilkan di Sprint 12
            </div>
        </div>
    </div>
</template>
