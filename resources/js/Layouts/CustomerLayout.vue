<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';

const page = usePage();
const table = computed(() => page.props.table ?? null);
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col max-w-md mx-auto relative">
        <!-- Header -->
        <header class="sticky top-0 z-20 bg-white border-b border-slate-100 px-4 py-3 flex items-center gap-3 shadow-sm">
            <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center shrink-0">
                <span class="text-white font-black text-xs">NN</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800 leading-tight">Niti Noto</p>
                <p v-if="table" class="text-xs text-slate-500 truncate">
                    Meja {{ table.number }} — {{ table.name }}
                </p>
                <p v-else class="text-xs text-amber-600 font-medium">Warung Kopi</p>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 pb-24">
            <slot />
        </main>

        <Toast position="top-center" />
    </div>
</template>
