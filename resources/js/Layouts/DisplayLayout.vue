<template>
    <div class="min-h-screen bg-slate-900 text-white flex flex-col">
        <!-- Header bar -->
        <header class="bg-amber-500 px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <span class="text-white font-bold">NN</span>
                </div>
                <div>
                    <p class="font-bold text-white text-lg leading-tight">Niti Noto</p>
                    <p class="text-amber-100 text-xs">Sistem Antrian</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-white/80 text-sm">{{ currentTime }}</p>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 flex flex-col">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const currentTime = ref('');

const updateTime = () => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

let timer;
onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});
onUnmounted(() => clearInterval(timer));
</script>
