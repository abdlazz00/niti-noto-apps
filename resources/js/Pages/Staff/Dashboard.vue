<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { animate } from 'motion';

defineOptions({ layout: AppLayout });

const props = defineProps({
    stats: Object,
});

const displayQueue     = ref(0);
const displayCompleted = ref(0);

function animateCounter(refVal, target, duration = 1200) {
    animate(0, target, {
        duration: duration / 1000,
        ease: [0.16, 1, 0.3, 1],
        onUpdate(v) { refVal.value = Math.round(v); },
    });
}

onMounted(() => {
    animateCounter(displayQueue,     props.stats.active_queue);
    animateCounter(displayCompleted, props.stats.completed_today, 900);
});
</script>

<template>
    <Head title="Dashboard Staff" />

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Staff</h1>
            <p class="text-slate-500 text-sm mt-1">Ringkasan antrian dapur hari ini</p>
        </div>

        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Antrian Aktif</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                        <i class="pi pi-list text-amber-500 text-sm" />
                    </div>
                </div>
                <p class="text-4xl font-black text-amber-600 tabular-nums">{{ displayQueue }}</p>
                <p class="text-xs text-slate-400 mt-1">order sedang diproses</p>
            </div>

            <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Selesai Hari Ini</span>
                    <div class="w-8 h-8 rounded-xl bg-green-50 flex items-center justify-center">
                        <i class="pi pi-check-circle text-green-500 text-sm" />
                    </div>
                </div>
                <p class="text-4xl font-black text-green-600 tabular-nums">{{ displayCompleted }}</p>
                <p class="text-xs text-slate-400 mt-1">order telah diselesaikan</p>
            </div>
        </div>

        <!-- Queue shortcut -->
        <Link
            :href="route('staff.queue')"
            class="bg-amber-500 rounded-2xl p-6 flex items-center gap-5 hover:bg-amber-600 transition-colors group block"
        >
            <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="pi pi-list text-white text-2xl" />
            </div>
            <div class="flex-1">
                <p class="font-black text-white text-lg">Buka Antrian Dapur</p>
                <p class="text-amber-100 text-sm mt-0.5">
                    <span v-if="stats.active_queue > 0">{{ stats.active_queue }} order menunggu diproses</span>
                    <span v-else>Tidak ada order aktif saat ini</span>
                </p>
            </div>
            <i class="pi pi-arrow-right text-white text-xl opacity-70 group-hover:translate-x-2 transition-transform" />
        </Link>
    </div>
</template>
