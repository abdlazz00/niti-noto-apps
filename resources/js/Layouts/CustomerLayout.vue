<template>
    <div class="min-h-screen bg-white flex flex-col max-w-md mx-auto">
        <!-- Header -->
        <header class="sticky top-0 z-20 bg-white border-b border-slate-100 px-4 py-3 flex items-center gap-3">
            <div class="w-7 h-7 rounded-md bg-amber-500 flex items-center justify-center">
                <span class="text-white font-bold text-xs">NN</span>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">Niti Noto</p>
                <p v-if="subtitle" class="text-xs text-slate-500">{{ subtitle }}</p>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Bottom safe area -->
        <div class="h-safe-bottom" />

        <Toast position="top-center" />
    </div>
</template>

<script setup>
import Toast from 'primevue/toast';

defineProps({
    subtitle: {
        type: String,
        default: '',
    },
});
</script>
