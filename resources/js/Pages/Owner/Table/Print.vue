<script setup>
import { Head } from '@inertiajs/vue3';
import QRCanvas from '@/Components/Owner/Table/QRCanvas.vue';

// No layout — standalone print page
defineOptions({ layout: null });

const props = defineProps({
    tables:   Array,
    printAll: Boolean,
});

function print() {
    window.print();
}

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head :title="printAll ? 'Print Semua QR' : `QR Meja ${tables[0]?.number}`" />

    <!-- Screen controls (hidden on print) -->
    <div class="no-print bg-slate-800 text-white px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button
                @click="goBack"
                class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors text-sm"
            >
                <i class="pi pi-arrow-left" /> Kembali
            </button>
            <span class="text-slate-500">|</span>
            <span class="font-semibold">
                {{ printAll ? `Print Semua QR (${tables.length} meja)` : `QR Meja ${tables[0]?.number}` }}
            </span>
        </div>
        <button
            @click="print"
            class="flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
        >
            <i class="pi pi-print" /> Print
        </button>
    </div>

    <!-- Print content -->
    <div class="print-page bg-white min-h-screen p-8">
        <div class="grid gap-6" :class="printAll ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-1 max-w-xs mx-auto'">
            <div
                v-for="table in tables"
                :key="table.id"
                class="qr-card border-2 border-slate-200 rounded-2xl p-6 flex flex-col items-center gap-4 break-inside-avoid"
            >
                <!-- Warung branding -->
                <div class="text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Niti Noto</p>
                    <p class="text-xs text-slate-400">Scan untuk pesan</p>
                </div>

                <!-- QR Code -->
                <div class="p-2 border border-slate-100 rounded-xl">
                    <QRCanvas :url="table.qr_url" :size="180" />
                </div>

                <!-- Table info -->
                <div class="text-center">
                    <p class="text-3xl font-black text-slate-800">{{ table.number }}</p>
                    <p class="text-sm font-medium text-slate-600 mt-0.5">{{ table.name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    .print-page {
        padding: 1cm;
    }

    .qr-card {
        page-break-inside: avoid;
    }

    @page {
        margin: 1cm;
        size: A4;
    }
}
</style>
