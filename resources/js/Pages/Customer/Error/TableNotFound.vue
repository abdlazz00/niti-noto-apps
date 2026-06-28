<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import Button from 'primevue/button';

defineOptions({ layout: CustomerLayout });
</script>

<template>
    <Head title="Meja Tidak Ditemukan" />

    <div class="px-6 py-12 flex flex-col items-center justify-center text-center space-y-6">
        <!-- SVG Illustration of QR/Table not found -->
        <div class="w-40 h-40 bg-amber-50 rounded-full flex items-center justify-center border border-amber-100/50 shadow-inner">
            <svg class="w-20 h-20 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <div class="space-y-2">
            <h2 class="text-lg font-bold text-slate-800">Meja Tidak Ditemukan atau Tidak Aktif</h2>
            <p class="text-xs text-slate-500 leading-relaxed max-w-xs mx-auto">
                QR Code meja yang Anda scan tidak valid, sudah kadaluarsa, atau meja sedang dinonaktifkan oleh kasir.
            </p>
        </div>

        <div class="w-full max-w-xs pt-4 space-y-3">
            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100 text-left space-y-2">
                <p class="text-xs font-bold text-amber-800 uppercase tracking-wide">Butuh Bantuan?</p>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Silakan hubungi kasir atau tanyakan staff pelayan kami di area warung kopi untuk memesan secara manual atau meminta bantuan setup meja Anda.
                </p>
            </div>
            
            <a href="/login" class="block w-full">
                <Button label="Login Staff/Kasir" severity="secondary" outlined class="w-full text-xs font-semibold py-2 rounded-xl" />
            </a>
        </div>
    </div>
</template>
